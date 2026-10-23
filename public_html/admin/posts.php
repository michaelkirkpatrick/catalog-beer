<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Admin Gate
if(!$userInfo->admin){
    header('location: /');
    exit;
}

/*
Posts: write /news and the API changelog (API: /post, helpers/posts.php).

Three screens:

  - /admin/posts: every post, drafts included, newest first.
  - /admin/posts/new?channel=news|api: a blank editor.
  - /admin/posts/<id>: one post's editor. Save, Preview (renders the body
    through the public template without saving), and Delete behind a
    confirmation step.

The body is Markdown, rendered by renderPostBody() in helpers/posts.php with
raw HTML allowed, so check it in Preview. Reads by id and the drafts list go
out under the signed-in admin's key (API::keyFor) and ask for drafts with
?status=all: the API returns a draft only to an admin who asks, so a read
without it — every public page — is a 404 on a draft. A refused list request
(not an admin) is shown as the API's own message, not as an empty table.
*/

require_once ROOT . '/classes/helpers/posts.php';
require_once ROOT . '/classes/helpers/alert.php';

$postID = isset($_GET['postID']) && preg_match('/^[-0-9a-f]{36}$/', $_GET['postID']) ? $_GET['postID'] : '';
$isNew = isset($_GET['new']);
$editing = $postID !== '' || $isNew;

$fields = array('channel', 'title', 'slug', 'version', 'breaking', 'summary', 'body', 'status', 'published_at');
$validState = array_fill_keys($fields, '');
$validMsg = array_fill_keys($fields, '');
$errorMsg = '';
$preview = null;
$confirmDelete = false;

// ----- Load -----
$post = null;
if($postID !== ''){
    $api = new API();
    $resp = json_decode((string)$api->request('GET', '/post/' . $postID . '?status=all', ''));
    if(!is_object($resp) || isset($resp->error)){
        serve404();
    }
    $post = $resp;
}

// Form values: the stored post, a blank one, or what was just submitted
$channel = $post->channel ?? (($_GET['channel'] ?? '') === 'api' ? 'api' : 'news');
$values = array(
    'title' => $post->title ?? '',
    'slug' => $post->slug ?? '',
    'version' => $post->version ?? '',
    'breaking' => !empty($post->breaking),
    'summary' => $post->summary ?? '',
    'body' => $post->body ?? '',
    'status' => $post->status ?? 'draft',
    // datetime-local wants local wall time, no zone
    'published_at' => isset($post->published_at) ? date('Y-m-d\TH:i:s', (int)$post->published_at) : '',
);

// ----- Submit -----
if($editing && $_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!csrf_verify()){
        $errorMsg = 'Your session expired before the post was sent. Please try again.';
    }elseif(isset($_POST['delete']) && $post){
        $confirmDelete = true;
    }elseif(isset($_POST['confirm_delete']) && $post){
        $api = new API();
        $api->request('DELETE', '/post/' . $postID, '');
        if($api->httpcode === 204){
            $_SESSION['posts_flash'] = array('type' => 'success', 'msg' => 'Deleted "' . $post->title . '".');
            header('location: /admin/posts');
            exit();
        }
        $errorMsg = 'The post could not be deleted (HTTP ' . $api->httpcode . ').';
    }else{
        foreach(array('title', 'slug', 'version', 'summary', 'body', 'status', 'published_at') as $f){
            $values[$f] = trim((string)($_POST[$f] ?? ''));
        }
        $values['body'] = rtrim((string)($_POST['body'] ?? ''));
        $values['breaking'] = isset($_POST['breaking']);

        // A datetime-local value is local wall time; strtotime() reads it in
        // the site's zone (America/Los_Angeles, set in initialize.php).
        $publishedAt = null;
        if($values['published_at'] !== ''){
            $publishedAt = strtotime($values['published_at']);
            if($publishedAt === false){
                $validState['published_at'] = 'invalid';
                $validMsg['published_at'] = 'Not a date and time.';
                $publishedAt = null;
            }
        }

        if(isset($_POST['preview'])){
            $preview = (object)array('channel' => $channel, 'slug' => $values['slug'] ?: 'preview', 'title' => $values['title'],
                'summary' => $values['summary'], 'body' => $values['body'], 'version' => $values['version'], 'breaking' => $values['breaking'],
                'published_at' => $publishedAt ?? time(), 'last_modified' => time());
        }elseif($validState['published_at'] === ''){
            $data = array('title' => $values['title'], 'summary' => $values['summary'] === '' ? null : $values['summary'],
                'body' => $values['body'], 'status' => $values['status']);
            // A blank date on a published post means "now" (or "keep the one
            // it has"), so it is left out and the API decides. Only a draft
            // can have its date cleared, and the API refuses a null otherwise.
            if(!is_null($publishedAt) || $values['status'] === 'draft'){
                $data['published_at'] = $publishedAt;
            }
            if($values['slug'] !== ''){ $data['slug'] = $values['slug']; }
            if($channel === 'api'){
                $data['version'] = $values['version'];
                $data['breaking'] = $values['breaking'];
            }
            $api = new API();
            if($post){
                $resp = json_decode((string)$api->request('PATCH', '/post/' . $postID, $data), true);
            }else{
                $data['channel'] = $channel;
                $resp = json_decode((string)$api->request('POST', '/post', $data), true);
            }
            if(!is_array($resp) || isset($resp['error'])){
                $errorMsg = is_array($resp) ? ($resp['error_msg'] ?? 'The post could not be saved.') : 'The post could not be saved.';
                foreach(($resp['validation'] ?? array()) as $field => $msg){
                    if(isset($validState[$field])){
                        $validState[$field] = 'invalid';
                        $validMsg[$field] = $msg;
                    }
                }
            }else{
                $_SESSION['posts_flash'] = array('type' => 'success', 'msg' => ($resp['status'] === 'published' ? 'Published' : 'Saved draft') . ' "' . $resp['title'] . '".');
                header('location: /admin/posts/' . $resp['id']);
                exit();
            }
        }
    }
}

// ----- List -----
$rows = array();
$hasMore = false;
$page = postListPage();
if(!$editing){
    $api = new API();
    $resp = json_decode((string)$api->request('GET', '/post?status=all&count=' . POSTS_PER_PAGE . '&cursor=' . rawurlencode(base64_encode((string)(($page - 1) * POSTS_PER_PAGE))), ''));
    if(is_object($resp) && isset($resp->data) && is_array($resp->data)){
        $rows = $resp->data;
        $hasMore = !empty($resp->has_more);
    }elseif($api->unavailable()){
        $errorMsg = 'The API did not answer. Try again in a moment.';
    }else{
        // A refusal (not an admin on the API's side) or an unexpected shape.
        // Shown, not swallowed: until Oct 2026 this fell through to the empty
        // state and read as "no posts" while the API was saying 403.
        $errorMsg = 'The API refused the list (HTTP ' . (int)$api->httpcode . ')' . (is_object($resp) && !empty($resp->error_msg) ? ': ' . h($resp->error_msg) : '.');
    }
}

$pageTitle = $post ? 'Edit: ' . $post->title : ($isNew ? 'New ' . ($channel === 'api' ? 'release' : 'news post') : 'Posts');
$htmlHead = new htmlHead($pageTitle);
$htmlHead->noindex();
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <main class="cb-page rv-page">
        <?php
        $nav->breadcrumbText = array('Admin', 'Posts');
        $nav->breadcrumbLink = array('/admin/');
        if($editing){
            $nav->breadcrumbText[] = $post ? $post->title : 'New';
            $nav->breadcrumbLink[] = '/admin/posts';
        }
        echo $nav->breadcrumbs();

        if(!empty($_SESSION['posts_flash'])){
            $flash = $_SESSION['posts_flash'];
            unset($_SESSION['posts_flash']);
            echo alertHtml($flash['msg'] ?? '', isset($flash['type']) && $flash['type'] === 'success');
        }
        if($errorMsg !== ''){
            echo alertHtml($errorMsg);
        }

        if(!$editing){
        // ================= The list =================
        ?>
        <div class="rv-head">
            <div>
                <h1 class="cbf-h1">Posts</h1>
                <p class="cbf-lede">Announcements at <a href="/news">/news</a> and API releases at <a href="/changelog">/changelog</a>. Drafts are visible only here.</p>
            </div>
            <div class="np-new">
                <a class="cb-chip" href="/admin/posts/new?channel=news">New news post</a>
                <a class="cb-chip" href="/admin/posts/new?channel=api">New API release</a>
            </div>
        </div>
        <?php
        if(count($rows) === 0){
            echo '<div class="rv-empty"><p>' . ($page > 1 ? 'Nothing further back than this.' : 'No posts yet.') . '</p></div>';
        }else{
        ?>
        <table class="rv-hist">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Channel</th>
                    <th>Title</th>
                    <th>State</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($rows as $row){
                    $editURL = '/admin/posts/' . rawurlencode($row->id);
                    $label = $row->channel === 'api' ? 'v' . $row->version . ': ' . $row->title : $row->title;
                    echo '<tr class="rv-hist__row">';
                    echo '<td class="rv-when">' . h($row->published_at ? postDate($row->published_at) : '—') . '</td>';
                    echo '<td class="rv-when">' . h($row->channel === 'api' ? 'API' : 'News') . '</td>';
                    echo '<td class="rv-name"><a href="' . h($editURL) . '">' . h($label) . '</a></td>';
                    echo '<td>' . ($row->status === 'draft' ? '<span class="cb-tag cb-tag--accent">Draft</span>' : '<a class="cb-tag" href="' . h(postPath($row)) . '">Live</a>') . '</td>';
                    echo '</tr>' . "\n";
                }
                ?>
            </tbody>
        </table>
        <?php
            echo renderPostPager('/admin/posts', $page, $hasMore);
        }
        }else{
        // ================= The editor =================
        ?>
        <div class="rv-head">
            <div>
                <h1 class="cbf-h1"><?php echo $post ? 'Edit <em>' . h($post->title) . '</em>' : ($channel === 'api' ? 'New API release' : 'New news post'); ?></h1>
                <p class="cbf-lede"><?php echo $channel === 'api' ? 'Appears at /changelog and in its feed once published.' : 'Appears at /news and in its feed once published.'; ?><?php if($post && $post->status === 'published'){ echo ' Live at <a href="' . h(postPath($post)) . '">' . h(postPath($post)) . '</a>.'; } ?></p>
            </div>
            <a class="cb-chip cb-chip--back" href="/admin/posts">&#8592; All posts</a>
        </div>

        <?php if($confirmDelete){ ?>
        <form method="post" class="cbf-panel np-confirm">
            <?php echo csrf_field(); ?>
            <p>Delete <strong><?php echo h($post->title); ?></strong>? This cannot be undone, and anyone subscribed to the feed keeps the copy they already have.</p>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn cbf-btn--danger" name="confirm_delete">Delete post</button>
                <a class="cbf-btn cbf-btn--ghost" href="/admin/posts/<?php echo h($postID); ?>">Cancel</a>
            </div>
        </form>
        <?php } ?>

        <?php if($preview){ ?>
        <section class="np-preview" aria-label="Preview">
            <p class="cb-label cb-label--rule">Preview — not saved</p>
            <?php echo renderPost($preview, false); ?>
        </section>
        <?php } ?>

        <p class="cbf-legend"><span aria-hidden="true">*</span> Required</p>
        <form method="post" class="cbf-panel">
            <?php echo csrf_field(); ?>
            <div class="cbf-sec">
            <?php
            $f = new InputField();
            $f->name = 'title';
            $f->description = 'Title';
            $f->required = true;
            $f->maxLength = 200;
            $f->value = $values['title'];
            $f->validState = $validState['title'];
            $f->validMsg = $validMsg['title'];
            suppressAutofill($f);
            echo $f->display();

            if($channel === 'api'){
                $f = new InputField();
                $f->name = 'version';
                $f->description = 'Version';
                $f->required = true;
                $f->maxLength = 20;
                $f->placeholder = '5.4.0';
                $f->hint = 'The API release this describes. The URL becomes /changelog/v{version}.';
                $f->value = $values['version'];
                $f->validState = $validState['version'];
                $f->validMsg = $validMsg['version'];
                suppressAutofill($f);
                echo $f->display();
            }

            $f = new InputField();
            $f->name = 'slug';
            $f->description = 'Slug';
            $f->maxLength = 120;
            $f->hint = $channel === 'api' ? 'Leave blank for v{version}.' : 'Leave blank to build it from the title. Lowercase letters, digits and hyphens.';
            $f->value = $values['slug'];
            $f->validState = $validState['slug'];
            $f->validMsg = $validMsg['slug'];
            suppressAutofill($f);
            echo $f->display();

            $f = new InputField();
            $f->name = 'summary';
            $f->description = 'Summary';
            $f->maxLength = 500;
            $f->showCount = true;
            $f->hint = 'One or two sentences. The meta description and the feed preview.';
            $f->value = $values['summary'];
            $f->validState = $validState['summary'];
            $f->validMsg = $validMsg['summary'];
            suppressAutofill($f);
            echo $f->display();

            $t = new Textarea();
            $t->name = 'body';
            $t->description = 'Body';
            $t->required = true;
            $t->rows = 18;
            $t->hint = 'Markdown: paragraphs, ### headings, - lists, [links](/path), `code`, ``` fenced blocks, **bold**. Raw HTML also works. Check it with Preview.';
            $t->value = $values['body'];
            $t->validState = $validState['body'];
            $t->validMsg = $validMsg['body'];
            echo '<div class="np-body">' . $t->display() . '</div>';
            ?>
            </div>
            <div class="cbf-sec">
            <?php
            if($channel === 'api'){
                $c = new Checkbox();
                $c->validState = $validState['breaking'];
                $c->validMsg = $validMsg['breaking'];
                echo $c->display('breaking', 'Breaking: this release can break a working client', 'yes', $values['breaking'] ? 'yes' : '');
            }

            $d = new DropDown();
            $d->name = 'status';
            $d->label = 'Status';
            $d->showLabel = true;
            $d->values = array('draft', 'published');
            $d->descriptions = array('Draft', 'Published');
            $d->currentValue = $values['status'];
            $d->validState = $validState['status'];
            $d->validMsg = $validMsg['status'];
            echo $d->display();

            $f = new InputField();
            $f->name = 'published_at';
            $f->type = 'datetime-local';
            $f->description = 'Published';
            $f->hint = 'Pacific time. Leave blank to use the moment it is published; set it to back-date a release to its push.';
            $f->value = $values['published_at'];
            $f->validState = $validState['published_at'];
            $f->validMsg = $validMsg['published_at'];
            echo $f->display();
            ?>
            </div>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit">Save</button>
                <button type="submit" class="cbf-btn cbf-btn--ghost" name="preview">Preview</button>
                <?php if($post){ ?>
                <button type="submit" class="cbf-btn cbf-btn--ghost np-delete" name="delete">Delete&#8230;</button>
                <span class="cbf-actnote">Last edited <?php echo h(date('M j, Y g:ia', (int)$post->last_modified)); ?></span>
                <?php } ?>
            </div>
        </form>
        <?php } ?>
    </main>
    <?php echo $nav->footer(); ?>
</body>
</html>
