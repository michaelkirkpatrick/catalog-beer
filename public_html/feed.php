<?php
/*
/news/feed.xml and /changelog/feed.xml — RSS 2.0, the 20 newest posts.

<description> carries the summary and <content:encoded> the full body, with
root-relative links made absolute (a feed reader resolves them against its own
origin otherwise). The guid is the post's permalink: it must never change, or
every subscriber's reader shows the post again as new.

Last-Modified/ETag come from the newest post's last_modified, so a reader's
conditional poll costs one API call and a 304.
*/
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
require_once ROOT . '/classes/helpers/posts.php';

$channel = $_GET['channel'] ?? '';
if(!isset(POST_CHANNELS[$channel])){
    serve404();
}
$meta = POST_CHANNELS[$channel];
$list = fetchPosts($channel, 1, POSTS_PER_PAGE);
$posts = is_null($list) ? array() : $list->data;

$origin = siteOrigin();
$channelURL = $origin . $meta['path'];
$feedURL = $origin . postFeedPath($channel);
$description = $channel === 'api'
    ? 'Every release of the Catalog.beer API: what changed, what was fixed, and what might break your code.'
    : 'News and notable changes from the Catalog.beer project.';

// Conditional GET
$lastModified = 0;
foreach($posts as $post){
    $lastModified = max($lastModified, (int)$post->last_modified, (int)$post->published_at);
}
$etag = '"' . md5($channel . ':' . $lastModified . ':' . count($posts)) . '"';
header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=900');
header('ETag: ' . $etag);
if($lastModified > 0){
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
}
$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
$ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
if($ifNoneMatch === $etag || ($ifNoneMatch === '' && $ifModifiedSince !== false && $lastModified > 0 && $ifModifiedSince >= $lastModified)){
    http_response_code(304);
    exit();
}

// XML text: h() is htmlspecialchars with ENT_QUOTES, which is valid XML
// escaping too. A body goes in CDATA, so the one sequence that could end it
// early is split across two sections.
function cdata(string $html): string {
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $html) . ']]>';
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title><?php echo h($meta['feedTitle']); ?></title>
    <link><?php echo h($channelURL); ?></link>
    <atom:link href="<?php echo h($feedURL); ?>" rel="self" type="application/rss+xml" />
    <description><?php echo h($description); ?></description>
    <language>en-us</language>
<?php if($lastModified > 0){ ?>
    <lastBuildDate><?php echo gmdate(DATE_RSS, $lastModified); ?></lastBuildDate>
<?php } ?>
<?php foreach($posts as $post){
    $permalink = $origin . postPath($post);
    $title = $channel === 'api' ? 'v' . $post->version . ': ' . $post->title : $post->title;
    if($channel === 'api' && !empty($post->breaking)){
        $title .= ' (breaking)';
    }
?>
    <item>
        <title><?php echo h($title); ?></title>
        <link><?php echo h($permalink); ?></link>
        <guid isPermaLink="true"><?php echo h($permalink); ?></guid>
        <pubDate><?php echo gmdate(DATE_RSS, (int)$post->published_at); ?></pubDate>
<?php if(!empty($post->summary)){ ?>
        <description><?php echo h($post->summary); ?></description>
<?php } ?>
        <content:encoded><?php echo cdata(absolutizeLinks($post->body)); ?></content:encoded>
    </item>
<?php } ?>
</channel>
</rss>
