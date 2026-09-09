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

Two things on one page, plus one route:

  - Waiting on you: the reviews with needs_decision and the leads with
    needs_decision (/brewer-lead, the review loop's queue of uncatalogued
    breweries), merged oldest first and stepped ONE at a time (?q=N) rather
    than stacked. A question is a piece of work, and the queue reads as a
    queue. Answering PATCHes /review/{id} or /brewer-lead/{id}; the next
    claim of that brewer or lead acts on it. A lead's own screen is
    /admin/leads/<id>; the rendering it shares with that page lives in
    classes/helpers/review-ui.php.
  - Review history: the most recent reviews, one row each. A row leads to
    /admin/reviews/<id>, which is also the link an agent or a log line hands
    you — one destination, not two ways of reading the same review.
  - /admin/reviews/<id>: one review in full, on its own screen. Notes,
    sources and every before/after, beside the same facts rail.

Everything is server-rendered — the stepper, the pager and the row links are
all URLs. The only scripts on the page are the shared live character count
and six lines that widen a row's click target to the whole row.

Layout is rv-* in styles-pages.css; the pieces it is built from are the
shared .cb- and .cbf- primitives.
*/

// Handle an answer
//
// Post/redirect/get, with the outcome in a one-shot session flash rather than
// the query string: the redirect lands on a bare /admin/reviews, so reloading
// it does not bring the banner back, and the queue re-reads from the top with
// the answered question gone. $flash['msg'] is plain text -- it is escaped at
// output, never on the way in.
require_once ROOT . '/classes/helpers/review-ui.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decision'], $_POST['lead_id'])){
    if(!csrf_verify()){
        $_SESSION['reviews_flash'] = array('type' => 'error', 'msg' => 'Your session expired before the answer was sent. Please try again.');
    }else{
        $_SESSION['reviews_flash'] = recordLeadDecision(new API(), trim($_POST['lead_id']), trim($_POST['decision']));
    }
    header('location: /admin/reviews');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decision'], $_POST['review_id'])){
    $reviewID = trim($_POST['review_id']);
    $decision = trim($_POST['decision']);
    if(!csrf_verify()){
        $_SESSION['reviews_flash'] = array('type' => 'error', 'msg' => 'Your session expired before the answer was sent. Please try again.');
    }elseif(!preg_match('/^[0-9a-f-]{36}$/', $reviewID) || $decision === ''){
        $_SESSION['reviews_flash'] = array('type' => 'error', 'msg' => 'An answer needs a review id and some text.');
    }else{
        $api = new API();
        $response = $api->request('PATCH', '/review/' . $reviewID, ['decision' => $decision]);
        $result = json_decode($response);
        if(isset($result->error) && $result->error){
            $_SESSION['reviews_flash'] = array('type' => 'error', 'msg' => $result->error_msg);
        }else{
            $brewerName = $result->brewer_name ?? '';
            $_SESSION['reviews_flash'] = array('type' => 'success', 'msg' => 'Decision recorded' . ($brewerName !== '' ? ' for ' . $brewerName : '') . '. The next claim of that brewer acts on it.');
        }
    }
    header('location: /admin/reviews');
    exit;
}

$api = new API();

// How much of a long review shows before you ask for the rest, and how deep
// the history page runs.
const RV_CHANGES_INLINE = 6;
const RV_HISTORY_PER_PAGE = 15;

// ----- Helpers, local to this page -----

// Outcomes are flat except 'deferred', which is the one that wants reading.
function outcomeTag($outcome){
    $class = ($outcome === 'deferred') ? 'cb-tag cb-tag--accent' : 'cb-tag';
    return '<span class="' . $class . '">' . h($outcome) . '</span>';
}

function brewerHref($review){
    return '/brewer/' . rawurlencode($review->brewer_id);
}

function brewerName($review){
    return !empty($review->brewer_name) ? $review->brewer_name : $review->brewer_id;
}

function reviewChanges($review){
    return is_array($review->changes ?? null) ? $review->changes : array();
}

function reviewSources($review){
    return is_array($review->sources ?? null) ? $review->sources : array();
}

// A question routinely names a record by uuid ("is <uuid> the same beer as
// …"). Left as text that is 36 characters of noise you cannot act on; matched
// against this review's own changes it becomes a link to the record, and the
// full id stays in the title attribute either way.
function questionHtml($question, $changes){
    $question = (string)$question;
    $entityOf = array();
    foreach($changes as $c){
        if(!empty($c->id) && !empty($c->entity)){
            $entityOf[strtolower($c->id)] = $c->entity;
        }
    }

    $html = '';
    $offset = 0;
    $pattern = '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i';
    if(preg_match_all($pattern, $question, $matches, PREG_OFFSET_CAPTURE)){
        foreach($matches[0] as $match){
            list($id, $position) = $match;
            if($position > $offset){
                $html .= h(substr($question, $offset, $position - $offset));
            }
            $short = h(substr($id, 0, 8)) . '&#8230;';
            $entity = $entityOf[strtolower($id)] ?? '';
            if(in_array($entity, array('brewer', 'beer', 'location'), true)){
                $html .= '<a href="/' . $entity . '/' . rawurlencode($id) . '" title="' . h($id) . '"><code>' . $short . '</code></a>';
            }else{
                $html .= '<code title="' . h($id) . '">' . $short . '</code>';
            }
            $offset = $position + strlen($id);
        }
    }
    if($offset < strlen($question)){
        $html .= h(substr($question, $offset));
    }
    return $html;
}

// One side of a before/after pair. null is the common value here and reads as
// a value, not as a blank cell.
function changeValue($value){
    if(is_null($value)){
        return array('text' => 'null', 'class' => 'rv-null');
    }
    if(is_bool($value)){
        return array('text' => $value ? 'true' : 'false', 'class' => '');
    }
    if(is_scalar($value)){
        return array('text' => (string)$value, 'class' => '');
    }
    return array('text' => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'class' => '');
}

// $limit 0 shows every row.
function changesTable($changes, $limit = 0){
    $rows = ($limit > 0) ? array_slice($changes, 0, $limit) : $changes;

    $html = '<table class="rv-changes">';
    $html .= '<thead><tr><th>Entity</th><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>';
    foreach($rows as $c){
        $entity = $c->entity ?? '';
        $id = (string)($c->id ?? '');
        $short = h(substr($id, 0, 8));
        if(in_array($entity, array('brewer', 'beer', 'location'), true) && preg_match('/^[0-9a-f-]{36}$/', $id)){
            $record = '<a href="/' . $entity . '/' . rawurlencode($id) . '" title="' . h($id) . '">' . $short . '</a>';
        }else{
            $record = $short;
        }

        $before = changeValue($c->before ?? null);
        $after = changeValue($c->after ?? null);
        // Strike the old value only where a new one replaced it. A write that
        // filled an empty field has nothing to cross out.
        $beforeClass = $before['class'];
        if($beforeClass === '' && !is_null($c->after ?? null)){
            $beforeClass = 'rv-before';
        }
        $afterClass = $after['class'] !== '' ? $after['class'] : 'rv-after';

        $html .= '<tr>';
        $html .= '<td class="rv-ent">' . h($entity) . ' ' . $record . '</td>';
        $html .= '<td class="rv-fieldcell"><span class="rv-field">' . h($c->field ?? '') . '</span></td>';
        $html .= '<td><span class="' . $beforeClass . '">' . h($before['text']) . '</span></td>';
        $html .= '<td><span class="' . $afterClass . '">' . h($after['text']) . '</span></td>';
        $html .= '</tr>' . "\n";
    }
    $html .= '</tbody></table>';
    return $html;
}

// Notes, sources and changes — the part of a review a human actually reads.
// $options: 'question' prepends the question and its decision, 'limit' caps the
// changes table, 'moreURL'/'moreLabel' render the link that lifts that cap.
function reviewSupplement($review, $options = array()){
    $changes = reviewChanges($review);
    $sources = reviewSources($review);

    $html = '<div class="rv-sup">';

    if(!empty($options['question']) && (!empty($review->question) || !empty($review->decision))){
        $html .= '<div>';
        if(!empty($review->question)){
            $html .= subhead('Question');
            $html .= '<p class="rv-notes">' . questionHtml($review->question, $changes) . '</p>';
        }
        if(!empty($review->decision)){
            $html .= subhead('Decision', null, !empty($review->question), true);
            $html .= '<p class="rv-decision">' . h($review->decision) . '<small>' . reviewDate($review->decided_at) . '</small></p>';
        }elseif(!empty($review->needs_decision)){
            $html .= '<p class="rv-none rv-none--stacked">waiting on you &#8212; <a href="/admin/reviews">answer it in the queue</a></p>';
        }
        $html .= '</div>';
    }

    $html .= '<div>';
    $html .= subhead('Notes');
    $html .= !empty($review->notes) ? '<p class="rv-notes">' . h($review->notes) . '</p>' : '<p class="rv-none">none</p>';
    $html .= '</div>';

    $html .= '<div>';
    $html .= subhead('Sources', count($sources));
    $html .= !empty($sources) ? sourcesHtml($sources) : '<p class="rv-none">none</p>';
    $html .= '</div>';

    $html .= '<div>';
    $html .= subhead($options['changesLabel'] ?? 'Changes', count($changes));
    if(!empty($changes)){
        $limit = intval($options['limit'] ?? 0);
        $html .= changesTable($changes, $limit);
        if($limit > 0 && count($changes) > $limit && !empty($options['moreURL'])){
            $html .= '<a class="cb-note" href="' . h($options['moreURL']) . '">' . h($options['moreLabel'] ?? 'Show all') . '</a>';
        }elseif(!empty($options['fewerURL'])){
            $html .= '<a class="cb-note" href="' . h($options['fewerURL']) . '">Show fewer</a>';
        }
    }else{
        $html .= '<p class="rv-none">none recorded</p>';
    }
    $html .= '</div>';

    $html .= '</div>';
    return $html;
}

// The rail beside a review: what the pass did, in counters.
function reviewRail($review){
    $html = '<aside class="cb-rail">';
    $html .= '<span class="cb-label">This review</span>';

    $facts = array(
        'Brewer' => '<a href="' . brewerHref($review) . '">' . h(brewerName($review)) . ' &#8594;</a>',
        'Outcome' => outcomeTag($review->outcome),
        'URL' => h($review->url_verdict),
        'Brewer field' => h($review->brewer_changed ?: '—'),
        'Beers' => '+' . intval($review->beers_added) . ' ~' . intval($review->beers_updated),
        'Locations' => '+' . intval($review->locations_added) . ' ~' . intval($review->locations_updated) . ' &#8722;' . intval($review->locations_deleted),
        'Brief' => h($review->brief_version ?: '—'),
        'Review id' => '<span title="' . h($review->id) . '">' . h(substr($review->id, 0, 8)) . '</span>'
    );
    foreach($facts as $key => $value){
        $html .= '<div class="cb-fact"><span class="cb-fact__k">' . h($key) . '</span><span class="cb-fact__v cb-fact__v--sm">' . $value . '</span></div>';
    }

    $html .= '<div class="cb-legend"><span>+ added</span><span>~ updated</span><span>&#8722; deleted</span></div>';
    $html .= '</aside>';
    return $html;
}

// ----- View state -----
//
// Every control on this page is a URL, so the whole view is these three
// parameters and one function that rebuilds a link from them.
$singleID = (isset($_GET['reviewID']) && preg_match('/^[0-9a-f-]{36}$/', $_GET['reviewID'])) ? $_GET['reviewID'] : '';
$queueIndex = max(1, intval($_GET['q'] ?? 1));
$showAllChanges = !empty($_GET['all']);
$historyPage = max(1, intval($_GET['page'] ?? 1));

$state = array('q' => $queueIndex, 'all' => $showAllChanges, 'page' => $historyPage);
$url = function($overrides = array()) use ($state){
    $params = array_merge($state, $overrides);
    $query = array();
    if(intval($params['q']) > 1){ $query['q'] = intval($params['q']); }
    if(!empty($params['all'])){ $query['all'] = 1; }
    if(intval($params['page']) > 1){ $query['page'] = intval($params['page']); }
    return '/admin/reviews' . (empty($query) ? '' : '?' . http_build_query($query));
};

// A history row is a link to its review.
function reviewHref($review){
    return '/admin/reviews/' . rawurlencode($review->id);
}

// ----- Data -----

$single = null;
$singleError = '';
$open = null;
$openError = '';
$history = array();
$historyError = '';
$historyHasMore = false;

if($singleID !== ''){
    $response = $api->request('GET', '/review/' . rawurlencode($singleID), '');
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        $singleError = $result->error_msg;
    }else{
        $single = $result;
    }
}else{
    // Open questions. count=100 is well inside the API's LIST_MAX and far past
    // any queue a human would let build up; the stepper walks the array.
    $response = $api->request('GET', '/review?needs_decision=1&count=100', '');
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        $openError = $result->error_msg;
    }else{
        $open = is_array($result->data ?? null) ? $result->data : array();
        foreach($open as $item){
            $item->kind = 'review';
            $item->askedAt = intval($item->reviewed_at);
        }
    }

    // Lead questions join the same queue. A lead's question is asked before
    // any brewer exists, so it has no review row to ride on; here it is one
    // more piece of work waiting on a human, ordered by when it was asked
    // among the rest.
    if($openError === ''){
        $response = $api->request('GET', '/brewer-lead?needs_decision=1&count=100', '');
        $result = json_decode($response);
        if(isset($result->error) && $result->error){
            $openError = $result->error_msg;
        }else{
            $leads = is_array($result->data ?? null) ? $result->data : array();
            foreach($leads as $lead){
                $lead->kind = 'lead';
                $lead->askedAt = intval($lead->created_at);
                $open[] = $lead;
            }
            usort($open, function($a, $b){ return $a->askedAt <=> $b->askedAt; });
        }
    }

    // History. The API's cursor is base64 of a row offset (Review.class.php,
    // listReviews) and has_more comes from a LIMIT count+1, so a page number
    // maps straight onto a cursor and there is no total to show. If that
    // encoding ever changes, this is the line that has to change with it.
    $cursor = base64_encode((string)(($historyPage - 1) * RV_HISTORY_PER_PAGE));
    $response = $api->request('GET', '/review?count=' . RV_HISTORY_PER_PAGE . '&cursor=' . rawurlencode($cursor), '');
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        $historyError = $result->error_msg;
    }else{
        $history = is_array($result->data ?? null) ? $result->data : array();
        $historyHasMore = !empty($result->has_more);
    }
}

// HTML Head
$htmlHead = new htmlHead('Reviews');
$htmlHead->noindex();
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <main class="cb-page rv-page">
        <?php
        $nav->breadcrumbText = array('Admin', 'Reviews');
        $nav->breadcrumbLink = array('/admin/');
        if($single){
            $nav->breadcrumbText[] = brewerName($single);
            $nav->breadcrumbLink[] = '/admin/reviews';
        }
        echo $nav->breadcrumbs();
        ?>
        <div class="rv-head">
            <div>
                <h1 class="cbf-h1">Reviews</h1>
                <p class="cbf-lede">Claude reviews brewers; anything that needs a human decision waits here. Your answer is read on the next pass of that brewer.</p>
            </div>
            <a class="cb-chip" href="/admin/leads">Lead queue &#8594;</a>
        </div>

        <?php
        // Flash from the last answer, read once and cleared
        if(!empty($_SESSION['reviews_flash'])){
            $flash = $_SESSION['reviews_flash'];
            unset($_SESSION['reviews_flash']);
            echo alertHtml($flash['msg'] ?? '', isset($flash['type']) && $flash['type'] === 'success');
        }

        // ================= One review on its own screen (/admin/reviews/<id>) =================
        if($singleID !== ''){
            if($singleError !== ''){
                echo '<div class="cbf-alert" role="alert"><span class="cbf-alert__i" aria-hidden="true">!</span><div>' . h($singleError) . '</div></div>';
                echo '<p><a class="cb-chip cb-chip--back" href="/admin/reviews">&#8592; All reviews</a></p>';
            }else{
                echo '<div class="rv-sechead"><span class="cb-label">One review</span>';
                echo '<a class="cb-chip cb-chip--back" href="/admin/reviews">&#8592; All reviews</a></div>';
                echo '<section class="rv-review">';
                echo '<div>';
                echo '<div class="rv-brewer"><a class="rv-brewer__name" href="' . brewerHref($single) . '">' . h(brewerName($single)) . '</a>';
                echo '<span class="rv-brewer__meta">reviewed ' . reviewDate($single->reviewed_at) . '</span></div>';
                if(!empty($single->question)){
                    echo '<p class="rv-question">' . questionHtml($single->question, reviewChanges($single)) . '</p>';
                }
                echo reviewSupplement($single, array('question' => true, 'changesLabel' => 'Changes this pass'));
                echo '</div>';
                echo reviewRail($single);
                echo '</section>';
            }
        }else{

        // ================= Waiting on you =================
        $openCount = is_array($open) ? count($open) : 0;
        $index = min($queueIndex, max(1, $openCount));
        $current = $openCount > 0 ? $open[$index - 1] : null;
        ?>
        <div class="rv-sechead">
            <span class="cb-label">Waiting on you<?php if($openError === ''){ echo '<span class="cb-count">' . number_format($openCount) . '</span>'; } ?></span>
            <?php if($current){ ?>
            <div class="rv-step">
                <?php echo stepChip('&#8592; Previous', $index > 1 ? $url(array('q' => $index - 1, 'all' => false)) : ''); ?>
                <span class="rv-step__n"><?php echo $index . ' of ' . $openCount; ?></span>
                <?php echo stepChip('Next &#8594;', $index < $openCount ? $url(array('q' => $index + 1, 'all' => false)) : ''); ?>
            </div>
            <?php } ?>
        </div>

        <?php
        if($openError !== ''){
            echo '<div class="cbf-alert" role="alert"><span class="cbf-alert__i" aria-hidden="true">!</span><div>' . h($openError) . '</div></div>';
        }elseif(!$current){
            echo '<div class="rv-empty"><p>Nothing is waiting on a decision.</p></div>';
        }elseif($current->kind === 'lead'){
            // A lead's question: what the source said, and the page it was
            // read on. No changes table, since a lead writes nothing.
            echo '<section class="rv-review">';
            echo '<div>';
            echo '<div class="rv-kind"><span class="cb-tag cb-tag--accent">lead</span><span class="rv-brewer__meta">not yet in the catalog</span></div>';
            echo leadHeader($current);
            echo '<p class="rv-question">' . h($current->question) . '</p>';
            echo leadAnswerForm($current, '/admin/reviews');
            echo leadSupplement($current, false);
            echo '</div>';
            echo leadRail($current);
            echo '</section>';
        }else{
            $changes = reviewChanges($current);
            echo '<section class="rv-review">';
            echo '<div>';
            echo '<div class="rv-brewer"><a class="rv-brewer__name" href="' . brewerHref($current) . '">' . h(brewerName($current)) . '</a>';
            echo '<span class="rv-brewer__meta">reviewed ' . reviewDate($current->reviewed_at) . '</span></div>';
            echo '<p class="rv-question">' . questionHtml($current->question, $changes) . '</p>';
            ?>
            <form class="rv-answer" method="post" action="/admin/reviews">
                <?php
                echo csrf_field();
                echo '<input type="hidden" name="review_id" value="' . h($current->id) . '">';
                $decision = new Textarea();
                $decision->name = 'decision';
                $decision->description = 'Your decision';
                $decision->hint = 'One or two sentences. Say what to do, not why — the sources below already hold the why.';
                $decision->required = true;
                $decision->markRequired = false;
                $decision->maxLength = 500;
                $decision->showCount = true;
                $decision->rows = 3;
                echo $decision->display();
                ?>
                <div class="cbf-actions">
                    <button type="submit" class="cbf-btn">Record decision</button>
                    <span class="cbf-actnote">Records and returns to the top of the queue</span>
                </div>
            </form>
            <?php
            echo reviewSupplement($current, array(
                'changesLabel' => 'Changes this pass',
                'limit' => $showAllChanges ? 0 : RV_CHANGES_INLINE,
                'moreURL' => $url(array('q' => $index, 'all' => true)),
                'moreLabel' => 'Show all ' . number_format(count($changes)) . ' changes',
                'fewerURL' => ($showAllChanges && count($changes) > RV_CHANGES_INLINE) ? $url(array('q' => $index, 'all' => false)) : ''
            ));
            echo '</div>';
            echo reviewRail($current);
            echo '</section>';
        }
        ?>

        <!-- ================= Review history ================= -->
        <div class="rv-sechead rv-sechead--hist">
            <span class="cb-label">Review history<?php
                // No total to show: has_more comes from a LIMIT count+1, not a
                // COUNT. On a first page that is also the last, the rows are
                // the total; otherwise the pager carries the range instead.
                if($historyError === '' && $historyPage === 1 && !$historyHasMore){
                    echo '<span class="cb-count">' . number_format(count($history)) . '</span>';
                }
            ?></span>
            <span class="rv-brewer__meta">Tap a row for the full review</span>
        </div>
        <?php
        if($historyError !== ''){
            echo '<div class="cbf-alert" role="alert"><span class="cbf-alert__i" aria-hidden="true">!</span><div>' . h($historyError) . '</div></div>';
        }elseif(empty($history)){
            echo '<div class="rv-empty"><p>' . ($historyPage > 1 ? 'Nothing further back than this.' : 'No reviews yet.') . '</p></div>';
        }else{
        ?>
        <table class="rv-hist">
            <thead>
                <tr>
                    <th class="rv-ok"><span class="cb-sr-only">Decided</span></th>
                    <th>When</th>
                    <th>Brewer</th>
                    <th>Outcome</th>
                    <th class="rv-hide-sm">URL</th>
                    <th class="rv-num rv-hide-sm">Beers</th>
                    <th class="rv-num rv-hide-sm">Locations</th>
                    <th class="rv-num">Changes</th>
                    <th class="rv-hide-sm">Brief</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($history as $review){
                    // The whole row leads to /admin/reviews/<id>. The name is
                    // the anchor that carries it — a row is about its review, so
                    // its one link goes where the row goes; the brewer's own page
                    // is one click further on, from the review's rail.
                    $rowURL = reviewHref($review);
                    $waiting = !empty($review->needs_decision);
                    echo '<tr class="rv-hist__row" data-href="' . h($rowURL) . '">';
                    echo '<td class="rv-ok' . ($waiting ? ' rv-ok--pending' : '') . '" title="' . ($waiting ? 'Waiting on a decision' : 'Reviewed') . '">' . ($waiting ? '&#183;' : '&#10003;') . '</td>';
                    echo '<td class="rv-when">' . reviewDate($review->reviewed_at) . '</td>';
                    echo '<td class="rv-name"><a href="' . h($rowURL) . '">' . h(brewerName($review)) . '</a></td>';
                    echo '<td>' . outcomeTag($review->outcome) . '</td>';
                    echo '<td class="rv-when rv-hide-sm">' . h($review->url_verdict) . '</td>';
                    echo '<td class="rv-num rv-hide-sm">+' . intval($review->beers_added) . ' ~' . intval($review->beers_updated) . '</td>';
                    echo '<td class="rv-num rv-hide-sm">+' . intval($review->locations_added) . ' ~' . intval($review->locations_updated) . ' &#8722;' . intval($review->locations_deleted) . '</td>';
                    echo '<td class="rv-num">' . number_format(count(reviewChanges($review))) . '</td>';
                    echo '<td class="rv-when rv-hide-sm">' . h($review->brief_version ?: '—') . '</td>';
                    echo '</tr>' . "\n";
                }
                ?>
            </tbody>
        </table>
        <?php
            // Pager. Without a total there is no last page to count back from,
            // so the numbered chips run to as far as we know the list reaches.
            $firstRow = ($historyPage - 1) * RV_HISTORY_PER_PAGE + 1;
            $lastRow = $firstRow + count($history) - 1;
            $knownPages = $historyPage + ($historyHasMore ? 1 : 0);
        ?>
        <div class="rv-pager">
            <span class="rv-pager__n"><?php echo number_format($firstRow) . '&#8211;' . number_format($lastRow); ?></span>
            <?php
            echo stepChip('&#8592; Newer', $historyPage > 1 ? $url(array('page' => $historyPage - 1)) : '');
            for($p = 1; $p <= $knownPages; $p++){
                if($p === $historyPage){
                    echo '<button type="button" class="cb-chip is-on" aria-current="page">' . $p . '</button>';
                }else{
                    echo '<a class="cb-chip" href="' . h($url(array('page' => $p))) . '">' . $p . '</a>';
                }
            }
            echo stepChip('Older &#8594;', $historyHasMore ? $url(array('page' => $historyPage + 1)) : '');
            ?>
        </div>
        <?php
        }
        } // end of the queue + history view
        ?>
    </main>
    <?php echo $nav->footer(); ?>
    <script>
    // Live "n / max" count for fields that render a .cbf-count
    document.querySelectorAll('.cbf-count[data-count-for]').forEach(function(el){
        var field = document.getElementById(el.getAttribute('data-count-for'));
        if(!field){ return; }
        var max = field.maxLength > 0 ? field.maxLength : null;
        var update = function(){ el.textContent = field.value.length + (max ? ' / ' + max : ''); };
        field.addEventListener('input', update);
        update();
    });

    // The whole history row follows its link to the review. The brewer name is
    // the real control — this only widens the target for a mouse.
    document.querySelectorAll('.rv-hist__row[data-href]').forEach(function(row){
        row.addEventListener('click', function(event){
            if(event.target.closest('a')){ return; }
            window.location = row.getAttribute('data-href');
        });
    });
    </script>
</body>
</html>
