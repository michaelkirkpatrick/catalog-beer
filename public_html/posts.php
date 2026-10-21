<?php
/*
/news, /news/{slug}, /changelog, /changelog/{slug}

One page for both channels (helpers/posts.php): .htaccess passes channel=news
or channel=api, and slug= for a single post. The API changelog is the `api`
channel; a release's slug is v{version}, so /changelog/v5.4.0.
*/
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
require_once ROOT . '/classes/helpers/posts.php';

$channel = $_GET['channel'] ?? '';
if(!isset(POST_CHANNELS[$channel])){
    serve404();
}
$meta = POST_CHANNELS[$channel];
$slug = $_GET['slug'] ?? '';

if($slug !== ''){
    // ----- One post -----
    $post = fetchPost($channel, $slug);
    if(is_null($post)){
        serve404();
    }
    $pageTitle = ($channel === 'api' ? 'v' . $post->version . ': ' : '') . $post->title . ' — ' . $meta['title'];
    $htmlHead = new htmlHead($pageTitle);
    $htmlHead->addDescription($post->summary ?? '');
}else{
    // ----- The list -----
    $page = postListPage();
    $list = fetchPosts($channel, $page);
    if($page > 1 && (is_null($list) || count($list->data) === 0)){
        serve404();
    }
    $htmlHead = new htmlHead($meta['title']);
    $htmlHead->addDescription($channel === 'api'
        ? 'Every release of the Catalog.beer API: what changed, what was fixed, and what might break your code.'
        : 'News and notable changes from the Catalog.beer project, newest first.');
}
$htmlHead->addFeed(postFeedPath($channel), $meta['feedTitle']);
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page cb-page--narrow nw-page">
        <?php if($slug !== ''){ ?>
        <div class="cb-eyebrow" itemscope itemtype="https://schema.org/BreadcrumbList">
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="<?php echo h($meta['path']); ?>"><span itemprop="name"><?php echo h($meta['title']); ?></span></a><meta itemprop="position" content="1" /></span> &nbsp;/&nbsp;
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name" aria-current="page"><?php echo h($channel === 'api' ? 'v' . $post->version : $post->title); ?></span><meta itemprop="position" content="2" /></span>
        </div>
        <?php echo renderPost($post, false); ?>
        <p class="nw-foot"><a class="cb-action" href="<?php echo h($meta['path']); ?>">&larr; All <?php echo $channel === 'api' ? 'releases' : 'news'; ?></a></p>
        <?php }else{ ?>
        <header class="nw-head">
            <h1 class="cb-title nw-head__title"><?php echo h($meta['title']); ?></h1>
            <?php if($channel === 'api'){ ?>
            <p class="cb-lede">Every release of the <a href="/api-docs">Catalog.beer API</a>, newest first. Releases marked <span class="cb-tag cb-tag--accent">Breaking</span> change something a working client may depend on.</p>
            <?php }else{ ?>
            <p class="cb-lede">News and notable changes from the Catalog.beer project, newest first. Looking for what changed in the API? See the <a href="/changelog">API changelog</a>.</p>
            <?php } ?>
            <p class="nw-head__feed"><a class="cb-action" href="<?php echo h(postFeedPath($channel)); ?>">RSS feed</a><?php if($channel === 'api'){ ?> &nbsp;·&nbsp; <a class="cb-action" href="/api-docs#changelog">As JSON from the API</a><?php } ?></p>
        </header>
        <?php
        if(is_null($list) || count($list->data) === 0){
            echo '<p class="nw-empty">Nothing here yet.</p>';
        }else{
            foreach($list->data as $post){
                echo renderPost($post, true);
            }
            echo renderPostPager($meta['path'], $page, !empty($list->has_more));
        }
        ?>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
