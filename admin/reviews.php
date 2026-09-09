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
The check-in for the brewer review loop. Reads the API's /review routes with
the logged-in admin's own key (the API refuses master keys there, so every
decision names a real account).

  - Open questions: reviews with needs_decision, oldest first, each with an
    answer box. Answering PATCHes /review/{id} and the next claim of that
    brewer acts on it.
  - One review in full (?review=<id>): notes, sources, every before/after.
  - Recent reviews: the last 30, one line each.
*/

// Handle an answer
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decision'], $_POST['review_id'])){
    if(!csrf_verify()){
        header('location: /admin/reviews.php?error=' . urlencode('Your session expired before the answer was sent. Please try again.'));
        exit;
    }
    $reviewID = trim($_POST['review_id']);
    $decision = trim($_POST['decision']);
    if(!preg_match('/^[0-9a-f-]{36}$/', $reviewID) || $decision === ''){
        header('location: /admin/reviews.php?error=' . urlencode('An answer needs a review id and some text.'));
        exit;
    }
    $api = new API();
    $response = $api->request('PATCH', '/review/' . $reviewID, ['decision' => $decision]);
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        header('location: /admin/reviews.php?error=' . urlencode($result->error_msg));
    }else{
        header('location: /admin/reviews.php?decided=' . urlencode($result->brewer_name ?? ''));
    }
    exit;
}

$api = new API();

// Helpers, local to this page
function reviewDate($ts){
    return $ts ? date('M j, Y', intval($ts)) : '—';
}
function outcomeBadge($outcome){
    $class = 'bg-secondary';
    switch($outcome){
        case 'updated':  $class = 'bg-success'; break;
        case 'created':  $class = 'bg-success'; break;
        case 'defunct':  $class = 'bg-dark'; break;
        case 'deferred': $class = 'bg-warning text-dark'; break;
        case 'skipped':  $class = 'bg-secondary'; break;
        case 'unchanged':$class = 'bg-light text-dark border'; break;
    }
    return '<span class="badge ' . $class . '">' . h($outcome) . '</span>';
}
function brewerLink($review){
    $name = !empty($review->brewer_name) ? $review->brewer_name : $review->brewer_id;
    return '<a href="/brewer/' . rawurlencode($review->brewer_id) . '">' . h($name) . '</a>';
}
function jsonCell($value){
    if(is_null($value)) return '<span class="text-muted">null</span>';
    if(is_bool($value)) return $value ? 'true' : 'false';
    if(is_scalar($value)) return h((string)$value);
    return h(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
function reviewDetail($review){
    // Notes, sources, changes — the part of a review a human actually reads
    $out = '';
    if(!empty($review->question) || !empty($review->decision)){
        $out .= '<div class="card mb-3"><div class="card-body">';
        $out .= '<div class="cb-eyebrow">Question</div><p class="cb-prose__text">' . h($review->question) . '</p>';
        if(!empty($review->decision)){
            $out .= '<div class="cb-eyebrow">Decision</div><p class="cb-prose__text mb-0">' . h($review->decision) . ' <span class="text-muted">— ' . reviewDate($review->decided_at) . '</span></p>';
        }
        $out .= '</div></div>';
    }
    $out .= '<h3 class="h5">Notes</h3>';
    $out .= '<p class="cb-prose__text">' . (!empty($review->notes) ? h($review->notes) : '<span class="text-muted">none</span>') . '</p>';
    $out .= '<h3 class="h5">Sources</h3>';
    if(!empty($review->sources)){
        $out .= '<ul>';
        foreach($review->sources as $src){
            $src = (string)$src;
            $url = strtok($src, ' ');
            if(preg_match('#^https?://#i', $url)){
                $out .= '<li><a href="' . h($url) . '" rel="noopener noreferrer" target="_blank">' . h($url) . '</a>' . h(substr($src, strlen($url))) . '</li>';
            }else{
                $out .= '<li>' . h($src) . '</li>';
            }
        }
        $out .= '</ul>';
    }else{
        $out .= '<p class="text-muted">none</p>';
    }
    $changes = is_array($review->changes) ? $review->changes : array();
    $out .= '<h3 class="h5">Changes <small class="text-muted">(' . count($changes) . ')</small></h3>';
    if(!empty($changes)){
        $table = new Table();
        $out .= $table->startTable(array('Entity', 'Id', 'Field', 'Before', 'After'));
        foreach($changes as $c){
            $entity = $c->entity ?? '';
            $id = (string)($c->id ?? '');
            $link = '';
            if(in_array($entity, array('brewer', 'beer', 'location')) && preg_match('/^[0-9a-f-]{36}$/', $id)){
                $link = '<a href="/' . $entity . '/' . rawurlencode($id) . '">' . h(substr($id, 0, 8)) . '</a>';
            }else{
                $link = h(substr($id, 0, 8));
            }
            $out .= '<tr><td>' . h($entity) . '</td><td>' . $link . '</td><td>' . h($c->field ?? '') . '</td>';
            $out .= '<td>' . jsonCell($c->before ?? null) . '</td><td>' . jsonCell($c->after ?? null) . '</td></tr>' . "\n";
        }
        $out .= $table->closeTable();
    }else{
        $out .= '<p class="text-muted">none recorded</p>';
    }
    return $out;
}

// HTML Head
$htmlHead = new htmlHead('Reviews');
$htmlHead->noindex();
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php
                $nav->breadcrumbText = array('Admin', 'Reviews');
                $nav->breadcrumbLink = array('/admin/');
                echo $nav->breadcrumbs();
                ?>
                <h1>Reviews</h1>
                <p class="text-muted">The brewer review loop's check-in. Answer what is waiting, read what changed, revert by hand from the before/after pairs if a write was wrong.</p>
                <?php
                if(isset($_GET['error'])){
                    echo '<div class="alert alert-danger">' . h($_GET['error']) . '</div>';
                }
                if(isset($_GET['decided'])){
                    echo '<div class="alert alert-success">Decision recorded' . ($_GET['decided'] !== '' ? ' for ' . h($_GET['decided']) : '') . '. The next claim of that brewer acts on it.</div>';
                }

                // ----- One review in full -----
                if(isset($_GET['review']) && preg_match('/^[0-9a-f-]{36}$/', $_GET['review'])){
                    $response = $api->request('GET', '/review/' . $_GET['review'], '');
                    $review = json_decode($response);
                    if(isset($review->error) && $review->error){
                        echo '<div class="alert alert-danger">' . h($review->error_msg) . '</div>';
                    }else{
                        echo '<div class="card mb-4"><div class="card-body">';
                        echo '<h2 class="h4">' . brewerLink($review) . ' ' . outcomeBadge($review->outcome) . '</h2>';
                        echo '<p class="text-muted mb-3">' . reviewDate($review->reviewed_at) . ' · url ' . h($review->url_verdict) . ' · brewer: ' . h($review->brewer_changed ?: '—') . ' · beers +' . intval($review->beers_added) . ' ~' . intval($review->beers_updated) . ' · locations +' . intval($review->locations_added) . ' ~' . intval($review->locations_updated) . ' −' . intval($review->locations_deleted) . ' · brief ' . h($review->brief_version ?: '—') . ' · <a href="/admin/reviews.php">all reviews</a></p>';
                        echo reviewDetail($review);
                        echo '</div></div>';
                    }
                }

                // ----- Open questions -----
                $response = $api->request('GET', '/review?needs_decision=1&count=100', '');
                $open = json_decode($response);
                echo '<h2>Waiting on you</h2>';
                if(isset($open->error) && $open->error){
                    echo '<div class="alert alert-danger">' . h($open->error_msg) . '</div>';
                }elseif(empty($open->data)){
                    echo '<p>Nothing is waiting on a decision.</p>';
                }else{
                    foreach($open->data as $review){
                        echo '<div class="card mb-4"><div class="card-body">';
                        echo '<h3 class="h5">' . brewerLink($review) . ' ' . outcomeBadge($review->outcome) . ' <small class="text-muted">' . reviewDate($review->reviewed_at) . '</small></h3>';
                        echo '<p class="cb-prose__text"><strong>' . h($review->question) . '</strong></p>';
                        echo '<form method="POST" class="mb-3">';
                        echo csrf_field();
                        echo '<input type="hidden" name="review_id" value="' . h($review->id) . '">';
                        echo '<div class="mb-2"><textarea name="decision" class="form-control" rows="2" maxlength="500" required placeholder="Your answer, in one or two sentences. The next review of this brewer reads it and acts on it."></textarea></div>';
                        echo '<button type="submit" class="btn btn-primary">Record decision</button> ';
                        echo '<a class="btn btn-link" href="/admin/reviews.php?review=' . h($review->id) . '#detail">Full review</a>';
                        echo '</form>';
                        echo '<details><summary class="text-muted">Notes, sources and changes</summary>' . reviewDetail($review) . '</details>';
                        echo '</div></div>';
                    }
                }

                // ----- Recent reviews -----
                $response = $api->request('GET', '/review?count=30', '');
                $recent = json_decode($response);
                echo '<h2>Recent reviews</h2>';
                if(isset($recent->error) && $recent->error){
                    echo '<div class="alert alert-danger">' . h($recent->error_msg) . '</div>';
                }elseif(empty($recent->data)){
                    echo '<p>No reviews yet.</p>';
                }else{
                    $table = new Table();
                    echo $table->startTable(array('When', 'Brewer', 'Outcome', 'URL', 'Beers', 'Locations', 'Changes', 'Brief', ''));
                    foreach($recent->data as $review){
                        $nChanges = is_array($review->changes) ? count($review->changes) : 0;
                        echo '<tr>';
                        echo '<td>' . reviewDate($review->reviewed_at) . '</td>';
                        echo '<td>' . brewerLink($review) . ($review->needs_decision ? ' <span class="badge bg-warning text-dark">question</span>' : '') . '</td>';
                        echo '<td>' . outcomeBadge($review->outcome) . '</td>';
                        echo '<td>' . h($review->url_verdict) . '</td>';
                        echo '<td>+' . intval($review->beers_added) . ' ~' . intval($review->beers_updated) . '</td>';
                        echo '<td>+' . intval($review->locations_added) . ' ~' . intval($review->locations_updated) . ' −' . intval($review->locations_deleted) . '</td>';
                        echo '<td>' . number_format($nChanges) . '</td>';
                        echo '<td><code>' . h($review->brief_version ?: '—') . '</code></td>';
                        echo '<td><a href="/admin/reviews.php?review=' . h($review->id) . '">Open</a></td>';
                        echo '</tr>' . "\n";
                    }
                    echo $table->closeTable();
                }
                ?>
            </div>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
