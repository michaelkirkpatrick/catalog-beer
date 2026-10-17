<?php
/*
Offline test for classes/helpers/posts.php — the shared rendering behind
/news, /changelog, their feeds and the admin preview.

    php tests/posts.php         # pass/fail per assertion, exit 1 on failure

Only the pure functions: no network, database or session. fetchPost() and
fetchPosts() need a live API and stay a manual staging walk, like the pages.
NOT DEPLOYED: deploy.sh excludes tests/ — keep it that way.
*/

if(php_sapi_name() !== 'cli'){ exit('CLI only'); }

define('ROOT', dirname(__DIR__) . '/public_html');
define('ENVIRONMENT', 'production');
date_default_timezone_set('America/Los_Angeles');

require_once(ROOT . '/classes/helpers/html.php');
require_once(ROOT . '/classes/helpers/posts.php');

$pass = 0;
$fail = 0;
function ok($label, $condition, $detail = ''){
    global $pass, $fail;
    if($condition){ $pass++; echo "  PASS $label\n"; }
    else{ $fail++; echo "  FAIL $label" . ($detail !== '' ? "\n       got: $detail" : '') . "\n"; }
}
function is($label, $expected, $actual){ ok($label, $expected === $actual, var_export($actual, true)); }

$release = (object)array('channel' => 'api', 'slug' => 'v5.4.0', 'title' => 'Verified records are <editable> & "open"',
    'summary' => 'Bob\'s <b>summary</b>', 'body' => '<p>See <a href="/api-docs#brewer">the docs</a> and <a href="https://example.com/x">elsewhere</a>.</p>',
    'version' => '5.4.0', 'breaking' => true, 'published_at' => 1791932931, 'last_modified' => 1792274400);
$news = (object)array('channel' => 'news', 'slug' => 'brewers-status', 'title' => 'Brewers', 'summary' => null,
    'body' => '<p>Hi</p>', 'version' => null, 'breaking' => false, 'published_at' => 1791932931, 'last_modified' => 1791932931);

echo "paths\n";
is('release path', '/changelog/v5.4.0', postPath($release));
is('news path', '/news/brewers-status', postPath($news));
is('api feed path', '/changelog/feed.xml', postFeedPath('api'));
is('news feed path', '/news/feed.xml', postFeedPath('news'));
is('date in Pacific time', 'October 13, 2026', postDate(1791932931));
is('no date for null', '', postDate(null));

echo "absolutizeLinks\n";
is('root-relative href made absolute, absolute left alone',
    '<p>See <a href="https://catalog.beer/api-docs#brewer">the docs</a> and <a href="https://example.com/x">elsewhere</a>.</p>',
    absolutizeLinks($release->body));
is('protocol-relative left alone', '<img src="//cdn.example/x.png">', absolutizeLinks('<img src="//cdn.example/x.png">'));
is('single-quoted src', "<img src='https://catalog.beer/images/a.png'>", absolutizeLinks("<img src='/images/a.png'>"));

echo "renderPost\n";
$list = renderPost($release, true);
$single = renderPost($release, false);
ok('title is escaped exactly once', str_contains($list, 'Verified records are &lt;editable&gt; &amp; &quot;open&quot;') && !str_contains($list, '&amp;lt;'));
ok('summary is escaped into the meta', str_contains($list, 'content="Bob&#039;s &lt;b&gt;summary&lt;/b&gt;"'));
ok('body is emitted raw, by contract', str_contains($list, $release->body));
ok('list form links an h2 to the post', str_contains($list, '<h2 class="nw-post__title" itemprop="headline"><a href="/changelog/v5.4.0"'));
ok('single form is an h1 with no link', str_contains($single, '<h1 class="cb-title nw-post__title" itemprop="headline">') && !str_contains($single, 'itemprop="url"'));
ok('release shows its version and the breaking tag', str_contains($list, '>v5.4.0<') && str_contains($list, '>Breaking<'));
ok('news shows neither', !str_contains(renderPost($news, true), 'nw-post__version') && !str_contains(renderPost($news, true), 'Breaking'));
ok('datePublished is ISO 8601', str_contains($list, 'datetime="2026-10-13T16:08:51-07:00"'));

echo "renderPostPager\n";
is('one page: no pager', '', renderPostPager('/news', 1, false));
ok('first page of several: Older only', str_contains(renderPostPager('/news', 1, true), '?page=2') && !str_contains(renderPostPager('/news', 1, true), 'Newer'));
ok('page 2 links Newer to the bare path', str_contains(renderPostPager('/news', 2, false), 'href="/news" rel="prev"'));

echo "\n$pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
