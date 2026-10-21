<?php
/**
 * Posts: shared by news.php, changelog.php, feed.php and admin/posts.php.
 * Required explicitly by those pages, not from initialize.php.
 *
 * A post comes from GET /post (API Post.class.php). Two channels: `news`
 * renders at /news/{slug}, `api` — the API changelog — at /changelog/{slug},
 * where the slug of a release is v{version}.
 *
 * The body is admin-authored Markdown, rendered by renderPostBody() through
 * Parsedown with raw HTML allowed, and the result is emitted as is: the third
 * raw-HTML contract on the site, beside Alert::$msg and the Checkbox label,
 * and safe for the same reason — only an admin key can write a post (the API
 * enforces it). Posts written before 2026-10-21 are plain HTML; Parsedown
 * passes HTML blocks through untouched, so they render exactly as they did.
 * Title, summary, slug and version are plain text; h() them at output.
 *
 * Parsedown is the one Markdown parser on the site and posts are its only
 * caller. User prose (descriptions, notes) stays plain text — see CLAUDE.md
 * § Output Escaping — so do not reach for it from anywhere else.
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
    // The API returns a draft only to an admin who sends ?status=all, which
    // this read never does; checking status here too means a public page can
    // never render one.
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
 * The body as HTML: Markdown through Parsedown (classes/Parsedown.class.php,
 * vendored 1.8.0, byte-identical). Raw HTML passes through, which is what keeps the pre-2026-10
 * HTML bodies rendering and leaves an escape hatch when Markdown falls short.
 * Bare URLs are not auto-linked: a changelog quotes paths and hostnames all
 * the time, and a link should be one because the author wrote one. Straight
 * quotes stay straight, as everywhere on the site.
 */
function renderPostBody(string $markdown): string {
    static $parsedown = null;
    if(is_null($parsedown)){
        $parsedown = new Parsedown();
        $parsedown->setUrlsLinked(false);
    }
    return $parsedown->text($markdown);
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
    // Rendered Markdown, emitted raw by contract — see the file docblock.
    $html .= '<div class="nw-post__body" itemprop="articleBody">' . renderPostBody((string)$post->body) . '</div>';
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
