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
The lead queue: breweries the review loop met that the catalog does not hold,
waiting to be researched (API: /brewer-lead). A lead is a name, a place and
the page it was read on — not a record, and nothing here is published.

Two screens:

  - /admin/leads: the queue as a table, one filter at a time (?status=queued,
    claimed, closed, or questions), oldest first for open rows and most
    recently closed first for closed ones. A row leads to its lead.
  - /admin/leads/<id>: one lead in full — note, every page that named it,
    its question and decision, and the answer form when a question is open.
    Answering here lands back here; the same question also waits in the
    merged queue on /admin/reviews, and answering there works the same way.

Reads use the logged-in admin's own key, as /admin/reviews does. Rendering
shared with that page is in classes/helpers/review-ui.php.
*/

require_once ROOT . '/classes/helpers/review-ui.php';

// Handle an answer: post/redirect/get with a one-shot flash, back to the lead.
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decision'], $_POST['lead_id'])){
    $leadID = trim($_POST['lead_id']);
    if(!csrf_verify()){
        $_SESSION['leads_flash'] = array('type' => 'error', 'msg' => 'Your session expired before the answer was sent. Please try again.');
    }else{
        $_SESSION['leads_flash'] = recordLeadDecision(new API(), $leadID, trim($_POST['decision']));
    }
    header('location: ' . (preg_match('/^[0-9a-f-]{36}$/', $leadID) ? '/admin/leads/' . $leadID : '/admin/leads'));
    exit;
}

$api = new API();

const LD_PER_PAGE = 25;
const LD_FILTERS = array(
    'queued' => 'Queued',
    'claimed' => 'Claimed',
    'questions' => 'Questions',
    'closed' => 'Closed'
);

// ----- View state -----
$singleID = (isset($_GET['leadID']) && preg_match('/^[0-9a-f-]{36}$/', $_GET['leadID'])) ? $_GET['leadID'] : '';
$filter = (isset($_GET['status']) && isset(LD_FILTERS[$_GET['status']])) ? $_GET['status'] : 'queued';
$page = max(1, intval($_GET['page'] ?? 1));

$url = function($overrides = array()) use ($filter, $page){
    $params = array_merge(array('status' => $filter, 'page' => $page), $overrides);
    $query = array();
    if($params['status'] !== 'queued'){ $query['status'] = $params['status']; }
    if(intval($params['page']) > 1){ $query['page'] = intval($params['page']); }
    return '/admin/leads' . (empty($query) ? '' : '?' . http_build_query($query));
};

// ----- Data -----
$single = null;
$singleError = '';
$rows = array();
$rowsError = '';
$hasMore = false;

if($singleID !== ''){
    $response = $api->request('GET', '/brewer-lead/' . rawurlencode($singleID), '');
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        $singleError = $result->error_msg;
    }else{
        $single = $result;
    }
}else{
    // The API's cursor is base64 of a row offset (BrewerLead.class.php,
    // listLeads) and has_more comes from a LIMIT count+1 — the same contract
    // /admin/reviews relies on for its pager.
    $query = ($filter === 'questions') ? 'needs_decision=1' : 'status=' . $filter;
    $cursor = base64_encode((string)(($page - 1) * LD_PER_PAGE));
    $response = $api->request('GET', '/brewer-lead?' . $query . '&count=' . LD_PER_PAGE . '&cursor=' . rawurlencode($cursor), '');
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        $rowsError = $result->error_msg;
    }else{
        $rows = is_array($result->data ?? null) ? $result->data : array();
        $hasMore = !empty($result->has_more);
    }
}

// HTML Head
$htmlHead = new htmlHead('Leads');
$htmlHead->noindex();
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <main class="cb-page rv-page">
        <?php
        $nav->breadcrumbText = array('Admin', 'Leads');
        $nav->breadcrumbLink = array('/admin/');
        if($single){
            $nav->breadcrumbText[] = leadName($single);
            $nav->breadcrumbLink[] = '/admin/leads';
        }
        echo $nav->breadcrumbs();
        ?>
        <div class="rv-head">
            <div>
                <h1 class="cbf-h1">Leads</h1>
                <p class="cbf-lede">Breweries a review met that the catalog does not hold. Each is researched in its own pass before anything is created; a closed lead is the record that it was looked at.</p>
            </div>
            <a class="cb-chip" href="/admin/reviews">Reviews &#8594;</a>
        </div>

        <?php
        if(!empty($_SESSION['leads_flash'])){
            $flash = $_SESSION['leads_flash'];
            unset($_SESSION['leads_flash']);
            echo alertHtml($flash['msg'] ?? '', isset($flash['type']) && $flash['type'] === 'success');
        }

        // ================= One lead (/admin/leads/<id>) =================
        if($singleID !== ''){
            if($singleError !== ''){
                echo alertHtml($singleError);
                echo '<p><a class="cb-chip cb-chip--back" href="/admin/leads">&#8592; All leads</a></p>';
            }else{
                echo '<div class="rv-sechead"><span class="cb-label">One lead</span>';
                echo '<a class="cb-chip cb-chip--back" href="/admin/leads">&#8592; All leads</a></div>';
                echo '<section class="rv-review">';
                echo '<div>';
                echo leadHeader($single, false);
                if(!empty($single->needs_decision)){
                    echo '<p class="rv-question">' . h($single->question) . '</p>';
                    echo leadAnswerForm($single, '/admin/leads');
                    echo leadSupplement($single, false);
                }else{
                    echo leadSupplement($single, true);
                }
                echo '</div>';
                echo leadRail($single);
                echo '</section>';
            }
        }else{

        // ================= The queue =================
        ?>
        <div class="rv-sechead">
            <span class="cb-label"><?php echo h(LD_FILTERS[$filter]); ?><?php
                if($rowsError === '' && $page === 1 && !$hasMore){
                    echo '<span class="cb-count">' . number_format(count($rows)) . '</span>';
                }
            ?></span>
            <div class="rv-step">
                <?php
                foreach(LD_FILTERS as $key => $label){
                    if($key === $filter){
                        echo '<button type="button" class="cb-chip is-on" aria-current="page">' . h($label) . '</button>';
                    }else{
                        echo '<a class="cb-chip" href="' . h($url(array('status' => $key, 'page' => 1))) . '">' . h($label) . '</a>';
                    }
                }
                ?>
            </div>
        </div>
        <?php
        if($rowsError !== ''){
            echo alertHtml($rowsError);
        }elseif(empty($rows)){
            $empty = array(
                'queued' => 'The lead queue is empty.',
                'claimed' => 'Nothing is claimed right now.',
                'questions' => 'No lead is waiting on a decision.',
                'closed' => 'No lead has been closed yet.'
            );
            echo '<div class="rv-empty"><p>' . ($page > 1 ? 'Nothing further back than this.' : $empty[$filter]) . '</p></div>';
        }else{
        ?>
        <table class="rv-hist">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Lead</th>
                    <th class="rv-hide-sm">Place</th>
                    <th class="rv-hide-sm">Website</th>
                    <th>State</th>
                    <th class="rv-num rv-hide-sm">Pages</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($rows as $lead){
                    $rowURL = leadHref($lead);
                    $when = ($filter === 'closed') ? $lead->resolved_at : $lead->created_at;
                    echo '<tr class="rv-hist__row" data-href="' . h($rowURL) . '">';
                    echo '<td class="rv-when">' . reviewDate($when) . '</td>';
                    echo '<td class="rv-name"><a href="' . h($rowURL) . '">' . h(leadName($lead)) . '</a></td>';
                    echo '<td class="rv-when rv-hide-sm">' . h(leadPlace($lead) ?: '—') . '</td>';
                    echo '<td class="rv-when rv-hide-sm">' . h($lead->url ?: '—') . '</td>';
                    echo '<td>' . leadStateTag($lead) . '</td>';
                    echo '<td class="rv-num rv-hide-sm">' . number_format(is_array($lead->sources ?? null) ? count($lead->sources) : 0) . '</td>';
                    echo '</tr>' . "\n";
                }
                ?>
            </tbody>
        </table>
        <?php
            $firstRow = ($page - 1) * LD_PER_PAGE + 1;
            $lastRow = $firstRow + count($rows) - 1;
            $knownPages = $page + ($hasMore ? 1 : 0);
        ?>
        <div class="rv-pager">
            <span class="rv-pager__n"><?php echo number_format($firstRow) . '&#8211;' . number_format($lastRow); ?></span>
            <?php
            echo stepChip('&#8592; Previous', $page > 1 ? $url(array('page' => $page - 1)) : '');
            for($p = 1; $p <= $knownPages; $p++){
                if($p === $page){
                    echo '<button type="button" class="cb-chip is-on" aria-current="page">' . $p . '</button>';
                }else{
                    echo '<a class="cb-chip" href="' . h($url(array('page' => $p))) . '">' . $p . '</a>';
                }
            }
            echo stepChip('Next &#8594;', $hasMore ? $url(array('page' => $page + 1)) : '');
            ?>
        </div>
        <?php
        }
        } // end of the queue view
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

    // The whole row follows its link to the lead; the name is the real control.
    document.querySelectorAll('.rv-hist__row[data-href]').forEach(function(row){
        row.addEventListener('click', function(event){
            if(event.target.closest('a')){ return; }
            window.location = row.getAttribute('data-href');
        });
    });
    </script>
</body>
</html>
