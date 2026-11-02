<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Revision history of a brewer, beer or location — administrators only. The
API's /{type}/{id}/history and /{type}/{id}/revert routes are admin-gated
themselves, so the gate here is cosmetic; the page is a reader and a form.

Newest first. Each revision shows what changed against the one before it,
with the before and after values side by side, and carries a "Restore this
version" form that POSTs its audit_id back through /revert. The API rebuilds
the record from the snapshot through the same PUT rules as any write, so a
restore can be refused for exactly the reasons a PUT would be; the refusal's
error_msg and valid_msg render here. A deleted record still has a history,
and restoring its last revision brings it back under the same id.

Routes: /brewer/{id}/history, /beer/{id}/history, /location/{id}/history
(.htaccess → history.php?type=&id=, QSA for the cursor).
--- */

$types = array(
    'brewer' => array('label' => 'Brewer', 'plural' => 'Brewers', 'list' => '/brewer'),
    'beer' => array('label' => 'Beer', 'plural' => 'Beers', 'list' => '/beer'),
    'location' => array('label' => 'Location', 'plural' => 'Locations', 'list' => '/brewer')
);
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';
if(!isset($types[$type]) || !preg_match('/^[-0-9a-f]{36}$/', $id)){
    serve404();
}
$entityURL = '/' . $type . '/' . $id;
$historyURL = $entityURL . '/history';

// Admin Gate — a non-admin lands on the record itself
if(empty($userInfo->admin)){
    header('location: ' . $entityURL);
    exit;
}

$api = new API();
$alert = new Alert();

// ----- Restore -----
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitRevert'])){
    if(!csrf_verify()){
        $alert->msg = 'Sorry, we were unable to verify your request. Please try again.';
    }else{
        $auditID = trim($_POST['audit_id'] ?? '');
        if(!ctype_digit($auditID)){
            $alert->msg = 'That revision could not be identified. Please try again.';
        }else{
            $body = array('audit_id' => intval($auditID));
            $note = trim($_POST['note'] ?? '');
            if($note !== ''){
                $body['note'] = $note;
            }
            $revertResp = $api->request('POST', $entityURL . '/revert', $body);
            $revertData = json_decode($revertResp);
            if(($api->httpcode == 200 || $api->httpcode == 201) && isset($revertData->id)){
                $_SESSION['history_flash'] = ($api->httpcode == 201)
                    ? 'The record has been restored under its original id.'
                    : 'The record has been restored to that revision.';
                trackEvent('Revision restored');
                header('location: ' . $historyURL);
                exit();
            }
            // Refused: the API says why, field by field when it can
            $msg = h($revertData->error_msg ?? 'Sorry, the record could not be restored right now. Please try again later.');
            if(!empty($revertData->valid_msg) && is_object($revertData->valid_msg)){
                $items = '';
                foreach($revertData->valid_msg as $field => $fieldMsg){
                    if(is_string($fieldMsg) && $fieldMsg !== ''){
                        $items .= '<li><strong>' . h($field) . '</strong>: ' . h($fieldMsg) . '</li>';
                    }elseif(is_object($fieldMsg)){
                        // The location's nested address block
                        foreach($fieldMsg as $sub => $subMsg){
                            if(is_string($subMsg) && $subMsg !== ''){
                                $items .= '<li><strong>' . h($field . '.' . $sub) . '</strong>: ' . h($subMsg) . '</li>';
                            }
                        }
                    }
                }
                if($items !== ''){
                    $msg .= '<ul class="hp-errlist">' . $items . '</ul>';
                }
            }
            $alert->msg = $msg;
        }
    }
}

// Flash from a successful restore
if(!empty($_SESSION['history_flash'])){
    $alert->msg = $_SESSION['history_flash'];
    $alert->type = 'success';
    unset($_SESSION['history_flash']);
}

// ----- The record (for its name); a deleted one still has a history -----
$entityResp = $api->request('GET', $entityURL, '');
$entity = json_decode($entityResp);
if($api->unavailable()){
    serve503();
}
$exists = ($api->httpcode == 200 && isset($entity->id));

// ----- The history page -----
$cursor = $_GET['cursor'] ?? '';
if(!preg_match('/^[A-Za-z0-9=]{1,24}$/', $cursor)){
    $cursor = '';
}
$histResp = $api->request('GET', $historyURL . '?count=50' . ($cursor !== '' ? '&cursor=' . urlencode($cursor) : ''), '');
$hist = json_decode($histResp);
if($api->unavailable()){
    serve503();
}
if($api->httpcode != 200 || !isset($hist->data)){
    if(!$exists){
        // Neither the record nor any history: nothing here
        serve404();
    }
    $revisions = array();
    if(empty($alert->msg)){
        $alert->msg = h($hist->error_msg ?? 'Sorry, the history could not be loaded right now.');
    }
}else{
    $revisions = $hist->data;
}

// A display name: the live record, else the newest snapshot that has one
function historyNameFromSnapshot($type, $snapshot){
    if(!is_object($snapshot)){
        return '';
    }
    if(!empty($snapshot->name)){
        return $snapshot->name;
    }
    if($type === 'location' && !empty($snapshot->locality)){
        return $snapshot->locality;
    }
    return '';
}
$name = '';
if($exists){
    $name = $entity->name ?? '';
    if($name === '' && $type === 'location'){
        $name = locationDisplayName($entity, $entity->brewer->name ?? '');
    }
}
if($name === ''){
    foreach($revisions as $rev){
        $name = historyNameFromSnapshot($type, $rev->snapshot ?? null);
        if($name !== ''){
            break;
        }
    }
}
if($name === ''){
    $name = $types[$type]['label'];
}
// The brewer, for the crumbs, when this is a beer or location
$parentName = '';
$parentID = '';
if($type !== 'brewer'){
    if($exists && isset($entity->brewer->id)){
        $parentID = $entity->brewer->id;
        $parentName = $entity->brewer->name ?? '';
    }else{
        foreach($revisions as $rev){
            if(!empty($rev->snapshot->brewerID)){
                $parentID = $rev->snapshot->brewerID;
                break;
            }
        }
    }
}

// ----- Rendering helpers -----
// "brewerVerified" -> "Brewer verified"; "line1" -> "Line1"; "sub_code" -> "Sub code"
function historyFieldLabel($field){
    $label = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', $field);
    $label = str_replace('_', ' ', $label);
    $label = preg_replace('/\bI D\b/', 'ID', $label);
    return ucfirst($label);
}
// A snapshot value as text. Escaped by the caller.
function historyValue($field, $value){
    if($value === null){
        return '';
    }
    if(in_array($field, array('cbVerified', 'brewerVerified'), true)){
        return $value ? 'Yes' : 'No';
    }
    if(in_array($field, array('lastModified', 'createdAt', 'validatedAt'), true) && is_numeric($value)){
        return date('M j, Y g:ia', intval($value));
    }
    if(is_array($value)){
        return implode(' / ', array_map('strval', $value));
    }
    if(is_object($value)){
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if(is_bool($value)){
        return $value ? 'Yes' : 'No';
    }
    return (string)$value;
}
// Columns that only say WHEN — out of the diff table, which is about WHAT
$hiddenFields = array('lastModified');
function historyActionLabel($rev){
    switch($rev->action){
        case 'create':
            return ($rev->source === 'backfill') ? 'Baseline' : 'Created';
        case 'update':
            return 'Updated';
        case 'delete':
            return 'Deleted';
        case 'revert':
            return 'Restored';
        default:
            return ucfirst($rev->action);
    }
}
function historyActor($rev){
    if(!empty($rev->actor) && !empty($rev->actor->name)){
        $who = $rev->actor->name;
        if(!empty($rev->actor->api_key_last4)){
            $who .= ' (key …' . $rev->actor->api_key_last4 . ')';
        }
        return $who;
    }
    if(!empty($rev->actor) && !empty($rev->actor->api_key_last4)){
        return 'Deleted account (key …' . $rev->actor->api_key_last4 . ')';
    }
    switch($rev->source){
        case 'backfill':
            return 'Baseline import';
        case 'webhook':
            return 'Stripe webhook';
        case 'cron':
            return 'Scheduled job';
        default:
            return ucfirst($rev->source);
    }
}

// HTML Head
$htmlHead = new htmlHead('History: ' . $name);
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar($types[$type]['plural'] === 'Locations' ? 'Brewers' : $types[$type]['plural']); ?>
    <div class="cb-page cb-page--form hp-page">
        <?php
        // Breadcrumbs: Home / Brewers / [Brewer /] Name / History
        $crumbText = array('Home', $types[$type]['plural'] === 'Locations' ? 'Brewers' : $types[$type]['plural']);
        $crumbLink = array('/', $types[$type]['list']);
        if($type !== 'brewer' && $parentID !== ''){
            $crumbText[] = ($parentName !== '') ? $parentName : 'Brewer';
            $crumbLink[] = '/brewer/' . $parentID;
        }
        $crumbText[] = $name;
        $crumbLink[] = $entityURL;
        $crumbText[] = 'History';
        $nav->breadcrumbText = $crumbText;
        $nav->breadcrumbLink = $crumbLink;
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <div class="cb-eyebrow"><?php echo h($types[$type]['label']); ?> &middot; revision history</div>
            <h1 class="cbf-h1"><?php echo h($name); ?><?php if(!$exists){ echo ' <span class="cb-tag hp-tag--deleted">Deleted</span>'; } ?></h1>
            <p class="cbf-lede">Every change to this record, newest first, with who made it and what moved. Restoring a version writes it back through the same rules as any edit<?php if($type === 'location'){ echo ', re-validating the address'; } ?>; the restore itself becomes the newest revision, so nothing is ever lost.</p>
        </div>
        <?php echo $alert->display(); ?>

        <?php if(empty($revisions)){ ?>
        <p class="cb-note">No revisions recorded yet.</p>
        <?php } ?>

        <?php
        $count = count($revisions);
        foreach($revisions as $i => $rev){
            $snapshot = $rev->snapshot ?? null;
            // The revision before this one that carries a snapshot — the
            // "before" column. A delete in between means a restore: all new.
            $previous = null;
            for($j = $i + 1; $j < $count; $j++){
                if(!empty($revisions[$j]->snapshot)){
                    $previous = $revisions[$j]->snapshot;
                    break;
                }
                if($revisions[$j]->action === 'delete'){
                    break;
                }
            }
            $isCurrent = ($i === 0 && $cursor === '' && $rev->action !== 'delete' && $exists);
            $changed = isset($rev->changed_fields) ? (array)$rev->changed_fields : array();
            $changed = array_values(array_diff($changed, $hiddenFields));
            $label = historyActionLabel($rev);
            $tagClass = 'cb-tag';
            if($rev->action === 'delete'){ $tagClass .= ' hp-tag--deleted'; }
            elseif($rev->action === 'revert'){ $tagClass .= ' cb-tag--accent'; }
            ?>
        <article class="hp-rev<?php echo $isCurrent ? ' hp-rev--current' : ''; ?>" id="rev-<?php echo (int)$rev->id; ?>">
            <header class="hp-rev__head">
                <span class="<?php echo $tagClass; ?>"><?php echo h($label); ?></span>
                <time datetime="<?php echo date('c', intval($rev->changed_at)); ?>"><?php echo date('M j, Y \a\t g:ia', intval($rev->changed_at)); ?></time>
                <span class="hp-rev__who">by <?php echo h(historyActor($rev)); ?></span>
                <?php if(!empty($rev->ip_address)){ echo '<span class="hp-rev__meta">' . h($rev->ip_address) . '</span>'; } ?>
                <span class="hp-rev__meta">#<?php echo (int)$rev->id; ?><?php if(!empty($rev->revert_of)){ echo ' &middot; restores #' . (int)$rev->revert_of; } ?></span>
                <?php if($isCurrent){ echo '<span class="hp-rev__current">current</span>'; } ?>
            </header>
            <?php if(!empty($rev->note)){ echo '<p class="hp-rev__note">' . h($rev->note) . '</p>'; } ?>
            <?php
            if($rev->action === 'delete'){
                echo '<p class="hp-rev__body">The record was deleted. Its last content is the revision below; restore that one to bring it back under the same id.</p>';
            }elseif($snapshot === null){
                echo '<p class="hp-rev__body">No snapshot was recorded for this revision.</p>';
            }elseif($previous !== null && !empty($changed)){
                // Before / after for each changed field
                echo '<table class="hp-diff"><thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>';
                foreach($changed as $field){
                    $before = historyValue($field, $previous->$field ?? null);
                    $after = historyValue($field, $snapshot->$field ?? null);
                    echo '<tr><th scope="row">' . h(historyFieldLabel($field)) . '</th>';
                    echo '<td class="hp-val hp-val--old">' . ($before === '' ? '<span class="hp-empty">empty</span>' : h($before)) . '</td>';
                    echo '<td class="hp-val hp-val--new">' . ($after === '' ? '<span class="hp-empty">empty</span>' : h($after)) . '</td></tr>';
                }
                echo '</tbody></table>';
            }elseif($previous !== null){
                echo '<p class="hp-rev__body">Only the timestamp moved.</p>';
            }else{
                // The first revision on record (or the oldest on this page):
                // the whole record as it stood
                echo '<table class="hp-diff hp-diff--full"><tbody>';
                foreach((array)$snapshot as $field => $value){
                    if(in_array($field, $hiddenFields, true) || $value === null || $value === ''){
                        continue;
                    }
                    echo '<tr><th scope="row">' . h(historyFieldLabel($field)) . '</th><td class="hp-val" colspan="2">' . h(historyValue($field, $value)) . '</td></tr>';
                }
                echo '</tbody></table>';
                if($i === $count - 1 && !empty($hist->has_more)){
                    echo '<p class="cbf-hint">Earlier revisions are on the next page; this one is shown in full because its predecessor is there.</p>';
                }
            }
            ?>
            <?php if($snapshot !== null && !$isCurrent){ ?>
            <form method="post" class="hp-restore" onsubmit="return confirm('Restore this version? The record is rewritten to match it and the restore is recorded as a new revision.');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="audit_id" value="<?php echo (int)$rev->id; ?>">
                <input type="text" class="cbf-input hp-restore__note" name="note" maxlength="200" placeholder="Why (optional, kept with the revision)" aria-label="Reason for restoring">
                <button type="submit" class="cbf-btn cbf-btn--ghost" name="submitRevert"><?php echo (!$exists && $i === 0) ? 'Restore this record' : 'Restore this version'; ?></button>
            </form>
            <?php } ?>
        </article>
        <?php } ?>

        <?php if(!empty($hist->has_more) && !empty($hist->next_cursor)){ ?>
        <div class="cbf-actions cbf-actions--bare">
            <a class="cbf-btn cbf-btn--ghost" href="<?php echo h($historyURL . '?cursor=' . urlencode($hist->next_cursor)); ?>">Older revisions &rarr;</a>
        </div>
        <?php } ?>
        <p class="cbf-hint"><a href="<?php echo h($entityURL); ?>">&larr; Back to the <?php echo strtolower(h($types[$type]['label'])); ?></a></p>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
