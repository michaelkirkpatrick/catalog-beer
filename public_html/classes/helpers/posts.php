<?php
/**
 * Posts: shared by news.php, changelog.php, feed.php and admin/posts.php.
 * Required explicitly by those pages, not from initialize.php.
 *
 * A post comes from GET /post (API Post.class.php). Two channels: `news`
 * renders at /news/{slug}, `api` — the API changelog — at /changelog/{slug},
 * where the slug of a release is v{version}.
 *
 * The body is admin-authored HTML and is emitted raw: the third raw-HTML
 * contract on the site, beside Alert::$msg and the Checkbox label, and safe for
 * the same reason — only an admin key can write a post (the API enforces it).
 * Title, summary, slug and version are plain text; h() them at output.
 */

const POST_CHANNELS = array(
    'news' => array('path' => '/news', 'title' => 'News', 'feedTitle' => 'Catalog.beer News'),
    'api' => array('path' => '/changelog', 'title' => 'API Changelog', 'feedTitle' => 'Catalog.beer API Changelog'),
);

const POSTS_PER_PAGE = 20;

/** The site's origin for the current environment, no trailing slash. */
function siteOrigin(): string {
    return (defined('ENVIRONMENT') && ENVIRONMENT === 'staging') ? 'https://staging.catalog.beer' : 'https://catalog.beer';
}

/** "October 13, 2026" from a Unix timestamp, in the site's timezone. */
function postDate($timestamp): string {
    return is_numeric($timestamp) ? date('F j, Y', (int)$timestamp) : '';
}

/** Site path of a post: /news/{slug} or /changelog/{slug}. */
function postPath($post): string {
    return POST_CHANNELS[$post->channel]['path'] . '/' . rawurlencode($post->slug);
}

/** Feed path of a channel: /news/feed.xml or /changelog/feed.xml. */
function postFeedPath(string $channel): string {
    return POST_CHANNELS[$channel]['path'] . '/feed.xml';
}

/**
 * One page of published posts in a channel, newest first.
 *
 * Returns the decoded list object, or null when the API answered with an
 * error. An unreachable API serves the 503 page, like every catalog page; a 4xx
 * (including the API not knowing /post yet, mid-deploy) is null, and the page
 * shows its empty state instead of breaking.
 */
function fetchPosts(string $channel, int $page = 1, int $count = POSTS_PER_PAGE): ?object {
    $api = new API();
    $cursor = base64_encode((string)(($page - 1) * $count));
    $response = $api->request('GET', '/post?channel=' . rawurlencode($channel) . '&count=' . $count . '&cursor=' . rawurlencode($cursor), '');
    if($api->unavailable()){
        serve503();
    }
    $list = json_decode((string)$response);
    if(!is_object($list) || isset($list->error) || !isset($list->data) || !is_array($list->data)){
        return null;
    }
    return $list;
}

/**
 * One published post by channel and slug, or null when there is none (the
 * caller serves the 404).
 */
function fetchPost(string $channel, string $slug): ?object {
    if(!preg_match('/^[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?$/', $slug)){
        return null;
    }
    $api = new API();
    $response = $api->request('GET', '/post/' . $channel . '/' . $slug, '');
    if($api->unavailable()){
        serve503();
    }
    $post = json_decode((string)$response);
    // The API already hides drafts from the master key this page reads with;
    // checking status here too means a public page can never render one.
    if(!is_object($post) || isset($post->error) || ($post->object ?? '') !== 'post' || ($post->status ?? '') !== 'published'){
        return null;
    }
    return $post;
}

/**
 * Rewrite root-relative href/src attributes to absolute URLs. Feed readers
 * resolve a relative link against the feed or their own origin, not the site,
 * so "/api-docs" in a body would point nowhere once it left the page.
 */
function absolutizeLinks(string $html): string {
    $origin = siteOrigin();
    return preg_replace('/\b(href|src)=(["\'])\/(?!\/)/i', '$1=$2' . $origin . '/', $html);
}

/**
 * The <article> for one post, shared by the list and the single-post page so
 * a post reads the same in both. $linkTitle is the list form: the title is an
 * <h2> linking to the post. The single-post page passes false and gets an <h1>.
 */
function renderPost($post, bool $linkTitle): string {
    $isRelease = $post->channel === 'api';
    $html = '<article class="nw-post" itemscope itemtype="https://schema.org/BlogPosting">';
    $html .= '<header class="nw-post__head">';
    $html .= '<p class="nw-post__meta">';
    if($isRelease && !empty($post->version)){
        $html .= '<span class="nw-post__version">v' . h($post->version) . '</span>';
    }
    $html .= '<time datetime="' . h(date('c', (int)$post->published_at)) . '" itemprop="datePublished">' . h(postDate($post->published_at)) . '</time>';
    if($isRelease && !empty($post->breaking)){
        $html .= '<span class="cb-tag cb-tag--accent">Breaking</span>';
    }
    $html .= '</p>';
    if($linkTitle){
        $html .= '<h2 class="nw-post__title" itemprop="headline"><a href="' . h(postPath($post)) . '" itemprop="url">' . h($post->title) . '</a></h2>';
    }else{
        $html .= '<h1 class="cb-title nw-post__title" itemprop="headline">' . h($post->title) . '</h1>';
    }
    $html .= '</header>';
    $html .= '<meta itemprop="dateModified" content="' . h(date('c', (int)$post->last_modified)) . '">';
    if(!empty($post->summary)){
        $html .= '<meta itemprop="description" content="' . h($post->summary) . '">';
    }
    // Raw by contract — see the file docblock.
    $html .= '<div class="nw-post__body" itemprop="articleBody">' . $post->body . '</div>';
    $html .= '</article>';
    return $html;
}

/**
 * Prev/next links for a list page. The API reports has_more; page 1 has no
 * previous. Pages are ?page=N, the one query parameter htmlHead keeps in the
 * canonical.
 */
function renderPostPager(string $basePath, int $page, bool $hasMore): string {
    if($page <= 1 && !$hasMore){
        return '';
    }
    $html = '<nav class="nw-pager" aria-label="Pages">';
    if($page > 1){
        $prev = $page === 2 ? $basePath : $basePath . '?page=' . ($page - 1);
        $html .= '<a class="cb-btn cb-btn--ghost" href="' . h($prev) . '" rel="prev">Newer</a>';
    }
    if($hasMore){
        $html .= '<a class="cb-btn cb-btn--ghost" href="' . h($basePath . '?page=' . ($page + 1)) . '" rel="next">Older</a>';
    }
    $html .= '</nav>';
    return $html;
}

/** The ?page= of a list page: a positive integer, 1 when absent or malformed. */
function postListPage(): int {
    $page = $_GET['page'] ?? '1';
    return (ctype_digit((string)$page) && (int)$page >= 1) ? (int)$page : 1;
}
