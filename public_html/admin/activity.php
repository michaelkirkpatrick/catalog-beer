<?php
// Admin › Activity: what the API's keys are doing, what the catalog's
// writers changed, and what the usage is worth. One request to
// GET /activity?period=month|3m; everything on the page is rendered from it.
// Design: Claude Design "Admin activity page redesign" (Nov 2026); styles in
// assets/css/admin-activity.css.
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Admin Gate
if(!$userInfo->admin){
    header('location: /');
    exit;
}

$period = (isset($_GET['period']) && $_GET['period'] === '3m') ? '3m' : 'month';

$api = new API();
$response = $api->request('GET', '/activity?period=' . $period, '');
$report = json_decode($response);
$ok = is_object($report) && empty($report->error) && isset($report->summary);

/* ----- formatting helpers ----- */
function actN($n){ return number_format((int)$n); }
function actMoney($cents){ return '$' . number_format(((int)$cents) / 100, 2); }
function actMonTs($ym){ $p = explode('-', $ym); return mktime(0, 0, 0, (int)$p[1], 1, (int)$p[0]); }
function actMon($ym){ return date('M', actMonTs($ym)); }
function actMonLong($ym){ return date('F Y', actMonTs($ym)); }
function actWhen($ts){ return date('M j H:i', (int)$ts); }
// "8 minutes" / "3 hours" / "2 days", with the span's first and last request
function actSpan($first, $last){
    if($first === null || $last === null){
        return array('—', '');
    }
    $d = (int)$last - (int)$first;
    if($d < 3600){
        $label = max(1, (int)round($d / 60)) . ' minute' . ($d >= 90 ? 's' : '');
    }elseif(date('Y-m-d', $first) === date('Y-m-d', $last)){
        $h = (int)round($d / 3600);
        $label = $h . ' hour' . ($h === 1 ? '' : 's');
    }else{
        $days = (int)((strtotime(date('Y-m-d', $last)) - strtotime(date('Y-m-d', $first))) / 86400) + 1;
        $label = $days . ' days';
    }
    return array($label, actWhen($first) . ' – ' . actWhen($last));
}
function actAgentLabel($agents){
    if(empty($agents)){
        return 'none';
    }
    $parts = array();
    foreach(array_slice($agents, 0, 2) as $a){
        $parts[] = $a->agent === null ? 'none' : preg_replace('/\s*\(.*$/', '', $a->agent);
    }
    return implode(' · ', $parts);
}
function actStatusLabel($status, $chargeDay, $lastYm){
    switch($status){
        case 'invoicing': return 'pending · invoice ' . actMon(date('Y-m', strtotime('+1 month', actMonTs($lastYm)))) . ' ' . $chargeDay;
        case 'rolling': return 'rolls forward · under the floor';
        case null: return '';
        default: return $status;
    }
}

// HTML Head
$htmlHead = new htmlHead('Activity');
$htmlHead->addStylesheet('/assets/css/admin-activity.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <main class="act-page">
        <?php
        $nav->breadcrumbText = array('Admin', 'Activity');
        $nav->breadcrumbLink = array('/admin/');
        echo $nav->breadcrumbs();
        ?>
        <header class="act-head">
            <div>
                <h1 class="act-h1">Activity</h1>
                <?php if($period === 'month'){ ?>
                <p class="act-lede">What the API's keys are doing. Reads come from the request log, writes from the audit log. <?php echo date('F Y'); ?>, through today.</p>
                <?php }else{ ?>
                <p class="act-lede">What the API's keys have been doing since <?php echo $ok ? date('F j', $report->since) : 'three months ago'; ?>. Reads from the request log, which keeps three months; writes from the audit log.</p>
                <?php } ?>
            </div>
            <div class="act-seg" role="tablist">
                <a class="<?php echo $period === 'month' ? 'is-on' : ''; ?>" href="/admin/activity.php">This month</a>
                <a class="<?php echo $period === '3m' ? 'is-on' : ''; ?>" href="/admin/activity.php?period=3m">Last 3 months</a>
            </div>
        </header>
        <hr class="act-rule">
<?php
if(!$ok){
    $msg = (is_object($report) && !empty($report->error_msg)) ? $report->error_msg : 'The activity report could not be loaded.';
    echo '<div class="act-alert">' . h($msg) . '</div>';
}else{
    $s = $report->summary;
    $b = $report->billing;
    $months = $report->months;
    $thisYm = $b->this_month->month;
    $lastYm = $b->last_month->month;
    $siteNote = array();
    foreach($report->site_keys as $sk){
        $siteNote[] = h($sk->name) . ' · …' . h($sk->api_key_last4) . ' (' . actN($sk->requests) . ')';
    }
    $newLabel = $period === 'month' ? 'this month' : 'since ' . date('M j', $report->since);

    // ----- At a glance -----
    $busy = 0;
    foreach($report->keys as $k){ if($k->requests >= 500){ $busy++; } }
    echo '<section class="act-glance">';
    echo '<div><div class="act-glance__k">External requests</div><div class="act-glance__v">' . actN($s->external_requests) . '</div><div class="act-glance__s">' . ($s->site_requests > 0 ? 'Plus ' . actN($s->site_requests) . ' from the site\'s own ' . (count($report->site_keys) === 1 ? 'key' : 'keys') : 'None from the site\'s own keys') . '</div></div>';
    $deletedNote = (!empty($s->keys_deleted)) ? actN($s->keys_deleted) . ' of them belong to deleted accounts' : actN($busy) . ' above the 500-request mark';
    echo '<div><div class="act-glance__k">Keys active</div><div class="act-glance__v">' . actN($s->keys_active) . '</div><div class="act-glance__s">' . h($deletedNote) . '</div></div>';
    echo '<div><div class="act-glance__k">New accounts</div><div class="act-glance__v' . ($s->new_accounts === 0 ? ' is-zero' : '') . '">' . actN($s->new_accounts) . '</div><div class="act-glance__s">Created ' . h($newLabel) . '</div></div>';
    $agentWrites = 0;
    foreach($report->write_activity->top_contributors as $c){ foreach($report->site_keys as $sk){ if($sk->user_id === $c->user_id){ $agentWrites += $c->total; } } }
    echo '<div><div class="act-glance__k">Catalog revisions</div><div class="act-glance__v">' . actN($s->revisions) . '</div><div class="act-glance__s">' . ($agentWrites > 0 ? actN($agentWrites) . ' by the site\'s own keys' : 'Creates, updates and deletes') . '</div></div>';
    echo '</section>';

    // ----- Requests per day (Chart.js) -----
    $series = array('labels' => array(), 'external' => array(), 'site' => array());
    foreach($s->daily as $d){
        $series['labels'][] = date('M j', strtotime($d->date));
        $series['external'][] = $d->external;
        $series['site'][] = $d->site;
    }
    echo '<div class="act-chart" id="actChart" data-series="' . h(json_encode($series)) . '">';
    echo '<canvas id="actChartCanvas" aria-label="Requests per day" role="img"></canvas>';
    echo '<div class="act-chart__fallback" id="actChartFallback" hidden>Requests per day: ';
    $parts = array();
    foreach($s->daily as $d){ $parts[] = date('M j', strtotime($d->date)) . ' ' . actN($d->external + $d->site); }
    echo h(implode(' · ', $parts)) . '</div></div>';

    // ----- Billing -----
    echo '<section class="act-section" id="billing">';
    echo '<div class="act-sechead"><h2>Billing</h2><p>$' . number_format($b->block_price_cents / 100, 0) . ' per ' . actN($b->block_size) . ' requests past the free tier, clamped to each key\'s cap. Notices go out on the 1st, cards are charged on the ' . (int)$b->charge_day . 'th, under ' . actMoney($b->invoice_floor_cents) . ' rolls forward.</p></div>';
    $tm = $b->this_month;
    $lm = $b->last_month;
    $cardKeys = 0;
    foreach($b->keys as $bk){ if($bk->billing_enabled){ $cardKeys++; } }
    $invoicingNames = array();
    $rollingNames = array();
    $paidCents = 0;
    foreach($lm->charges as $c){
        if($c->status === 'invoicing'){ $invoicingNames[] = h($c->name) . ' ' . actMoney($c->amount_cents); }
        elseif($c->status === 'rolling'){ $rollingNames[] = h($c->name) . ' ' . actMoney($c->amount_cents); }
        else{ $paidCents += $c->amount_cents; }
    }
    echo '<div class="act-bill">';
    echo '<div><div class="act-glance__k">Committed, ' . h(actMon($thisYm)) . '</div><div class="act-glance__v act-glance__v--sm' . ($tm->committed_cents === 0 ? ' is-zero' : '') . '">' . actMoney($tm->committed_cents) . '</div><div class="act-glance__s">' . ($tm->committed_cents === 0 ? 'No billing key has passed the free tier yet' : 'Forecast ' . actMoney($tm->forecast_cents) . ' at the current pace') . '</div></div>';
    if($lm->invoicing_cents > 0){
        echo '<div><div class="act-glance__k">Invoicing ' . h(actMon($thisYm)) . ' ' . (int)$b->charge_day . '</div><div class="act-glance__v act-glance__v--sm">' . actMoney($lm->invoicing_cents) . '</div><div class="act-glance__s">' . h(actMonLong($lastYm)) . ': ' . implode(', ', $invoicingNames) . '</div></div>';
    }else{
        echo '<div><div class="act-glance__k">' . h(actMon($lastYm)) . ' charges</div><div class="act-glance__v act-glance__v--sm' . ($lm->charged_cents === 0 ? ' is-zero' : '') . '">' . actMoney($lm->charged_cents) . '</div><div class="act-glance__s">' . ($lm->charged_cents === 0 ? 'Nothing past the free tier' : actMoney($paidCents) . ' invoiced' . ($lm->rolling_cents > 0 ? ', ' . actMoney($lm->rolling_cents) . ' rolling' : '')) . '</div></div>';
    }
    echo '<div><div class="act-glance__k">Rolling forward</div><div class="act-glance__v act-glance__v--sm' . ($lm->rolling_cents === 0 ? ' is-zero' : '') . '">' . actMoney($lm->rolling_cents) . '</div><div class="act-glance__s">' . (empty($rollingNames) ? 'Nothing under the ' . actMoney($b->invoice_floor_cents) . ' floor' : implode(', ', $rollingNames) . ', under the floor') . '</div></div>';
    $near = 0;
    foreach($b->ceiling as $c){ if($c->status === 'near'){ $near++; } }
    $refSub = array();
    if($period === '3m'){
        foreach($b->refused->by_month as $ym => $n){ $refSub[] = actN($n) . ' in ' . actMon($ym); }
        $refSub = implode(', ', $refSub);
    }else{
        $refSub = $b->refused->total > 0 ? actN($b->refused->keys) . ' key' . ($b->refused->keys === 1 ? '' : 's') . ' hit the ceiling' : ($near > 0 ? actN($near) . ' key' . ($near === 1 ? '' : 's') . ' within reach of it' : 'No key has reached the ceiling');
    }
    echo '<div><div class="act-glance__k">Refused at the free tier</div><div class="act-glance__v act-glance__v--sm' . ($b->refused->total === 0 ? ' is-zero' : '') . '">' . actN($b->refused->total) . '</div><div class="act-glance__s">' . h($refSub) . '</div></div>';
    echo '</div>';

    // Billing keys: a card on file, or a charge in the window
    if(empty($b->keys)){
        echo '<p class="act-empty">No key has a card on file.</p>';
    }else{
        echo '<div class="act-tg act-tg--bk act-tg--head"><span>Billing keys</span><span>Cap</span><span>' . h(actMon($lastYm)) . ' requests</span><span>' . h(actMon($lastYm)) . ' charge</span><span>' . h(actMon($thisYm)) . ' requests</span><span>' . h(actMon($thisYm)) . ' so far</span><span>' . h(actMon($thisYm)) . ' forecast</span></div>';
        foreach($b->keys as $bk){
            $byMonth = array();
            foreach($bk->months as $m){ $byMonth[$m->month] = $m; }
            $last = $byMonth[$lastYm] ?? null;
            $cur = $byMonth[$thisYm] ?? null;
            $earlier = array();
            foreach($bk->months as $m){
                if($m->month !== $lastYm && $m->month !== $thisYm){ $earlier[] = actMon($m->month) . ' ' . actN($m->requests) . ($m->charge_cents > 0 ? ' → ' . actMoney($m->charge_cents) : ''); }
            }
            $status = ($bk->billing_enabled ? 'card on file' : 'billing off') . ($bk->pending_cents > 0 ? ' · ' . actMoney($bk->pending_cents) . ' pending' : '');
            if(!empty($earlier)){ $status .= ' · ' . implode(' · ', $earlier); }
            $forecastNote = $bk->this_month->requests === 0 ? 'no requests this month' : 'on pace for ' . actN($bk->this_month->projected_requests) . ' requests';
            echo '<div>';
            echo '<div class="act-tg act-tg--bk act-tg--row"><span>' . h($bk->name) . '<small>…' . h($bk->api_key_last4) . '</small></span>';
            echo '<span>' . actMoney($bk->spend_cap_cents) . '</span>';
            echo '<span class="' . ($last === null || $last->requests === 0 ? 'is-dim' : ($last->requests > $bk->request_limit ? 'is-over' : '')) . '">' . actN($last->requests ?? 0) . '</span>';
            echo '<span>' . ($last !== null && $last->charge_cents > 0 ? actMoney($last->charge_cents) : '—') . '</span>';
            echo '<span class="' . ($cur === null || $cur->requests === 0 ? 'is-dim' : ($cur->requests > $bk->request_limit ? 'is-over' : '')) . '">' . actN($cur->requests ?? 0) . '</span>';
            echo '<span>' . actMoney($bk->this_month->committed_cents) . '</span>';
            echo '<span>' . actMoney($bk->this_month->forecast_cents) . '</span></div>';
            echo '<div class="act-tg act-tg--bk act-tg--sub"><span>' . h($status) . '</span><span></span><span></span><span>' . h($last !== null ? actStatusLabel($last->status, (int)$b->charge_day, $lastYm) : '') . '</span><span></span><span></span><span>' . h($forecastNote) . '</span></div>';
            echo '</div>';
        }
    }

    // Forecast + the free keys at the ceiling
    echo '<div class="act-forecast"><div><span class="cb-label">Forecast</span>';
    if($tm->committed_cents === 0){
        echo '<p>' . h(actMonLong($thisYm)) . ' so far commits ' . actMoney(0) . ': ' . ($cardKeys === 0 ? 'no key has a card on file' : 'none of the ' . actN($cardKeys) . ' key' . ($cardKeys === 1 ? '' : 's') . ' with a card has passed the free tier') . '. The forecast stays at ' . actMoney(0) . ' until one does.</p>';
    }else{
        echo '<p>' . h(actMonLong($thisYm)) . ' so far commits ' . actMoney($tm->committed_cents) . '. At the current pace the month ends at ' . actMoney($tm->forecast_cents) . ', each key clamped to its cap.</p>';
    }
    if($lm->invoicing_cents > 0){
        echo '<p>' . h(actMonLong($lastYm)) . '\'s ' . actMoney($lm->invoicing_cents) . ' invoices on ' . h(actMon($thisYm)) . ' ' . (int)$b->charge_day . ($lm->rolling_cents > 0 ? ', and ' . actMoney($lm->rolling_cents) . ' rolls forward under the ' . actMoney($b->invoice_floor_cents) . ' floor' : '') . '.</p>';
    }elseif($lm->charged_cents > 0){
        echo '<p>' . h(actMonLong($lastYm)) . ' recorded ' . actMoney($lm->charged_cents) . ($paidCents > 0 ? ', ' . actMoney($paidCents) . ' of it invoiced' : '') . ($lm->rolling_cents > 0 ? ', ' . actMoney($lm->rolling_cents) . ' rolling forward under the ' . actMoney($b->invoice_floor_cents) . ' floor' : '') . '.</p>';
    }
    $hit = count($b->ceiling) - $near;
    if($hit > 0 || $near > 0){
        $bits = array();
        if($hit > 0){ $bits[] = actN($hit) . ' free key' . ($hit === 1 ? '' : 's') . ' hit the ceiling' . ($period === 'month' ? ' this month' : ' in the period'); }
        if($near > 0){ $bits[] = actN($near) . ' ' . ($hit > 0 ? 'more ' : 'free key' . ($near === 1 ? ' is' : 's are') . ' ') . ($hit > 0 ? ($near === 1 ? 'is' : 'are') . ' ' : '') . 'within reach of it'; }
        echo '<p>' . h(ucfirst(implode(', and ', $bits))) . '. A free key is stopped at its limit plus the buffer and refused after that; a card turns the refusals into charges.</p>';
    }
    echo '</div><div><span class="cb-label">' . ($period === 'month' ? 'Free keys at the ceiling' : 'Free keys that hit the ceiling') . '</span>';
    if(empty($b->ceiling)){
        echo '<p class="act-empty">None ' . h($period === 'month' ? 'this month' : 'in the period') . '.</p>';
    }else{
        foreach(array_slice($b->ceiling, 0, 8) as $c){
            $sub = ($period === '3m' ? actMon($c->month) : '') . ($c->status === 'near' ? ($period === '3m' ? ' · ' : '') . 'near' : '');
            echo '<div class="act-ceiling"><span class="act-ceiling__n">' . h($c->name) . '<small>…' . h($c->api_key_last4) . ($sub !== '' ? ' · ' . h($sub) : '') . '</small></span><span class="act-num">' . actN($c->requests) . ' of ' . actN($c->request_limit) . '</span><span class="act-num act-num--muted">' . ($c->refused > 0 ? actN($c->refused) . ' refused' : '') . '</span></div>';
        }
        if(count($b->ceiling) > 8){ echo '<p class="act-empty">and ' . actN(count($b->ceiling) - 8) . ' more</p>'; }
    }
    echo '</div></div>';
    echo '</section>';

    // ----- Keys -----
    $keys = $report->keys;
    $shown = 25;
    echo '<section class="act-section" id="keys">';
    if($period === 'month'){
        echo '<div class="act-sechead"><h2>Keys this month</h2><p>One row per key, busiest first. Open a row to see what it fetched, when, and from where.</p></div>';
    }else{
        echo '<div class="act-sechead"><h2>Keys by month</h2><p>Requests per key per calendar month. A cell past the free tier is billable if the key has a card; a key with no card is stopped at its limit plus the buffer.</p></div>';
    }
    $notes = array();
    if(!empty($siteNote)){
        $notes[] = 'The site\'s own ' . (count($siteNote) === 1 ? 'key' : 'keys') . ', <span class="act-mono">' . implode(', ', $siteNote) . '</span>, ' . (count($siteNote) === 1 ? 'is' : 'are') . ' kept out of this list.';
    }
    if(!empty($s->keys_deleted)){
        $notes[] = actN($s->keys_deleted) . ' key' . ($s->keys_deleted === 1 ? '' : 's') . ' with traffic belong' . ($s->keys_deleted === 1 ? 's' : '') . ' to accounts since deleted and cannot be listed.';
    }
    if(!empty($notes)){
        echo '<p class="act-note">' . implode(' ', $notes) . '</p>';
    }
    if(empty($keys)){
        echo '<p class="act-empty">No requests ' . h($period === 'month' ? 'this month' : 'in the period') . '.</p>';
    }elseif($period === 'month'){
        $top = max(1, $keys[0]->requests);
        echo '<div class="act-cols"><div class="act-th">Account</div><div class="act-th">Key</div><div class="act-th">Requests</div><div class="act-th">Active</div><div class="act-th">Mix</div><div class="act-th">Client</div></div>';
        $sinceHour = $report->since - ($report->since % 3600);
        $hourCount = max(1, (int)floor(($report->until - $sinceHour) / 3600) + 1);
        foreach($keys as $i => $k){
            $near = !$k->billing_enabled && $k->requests >= (int)ceil($k->request_limit * 0.9);
            $open = ($i === 0 && !empty($k->detail));
            list($span, $spanSub) = actSpan($k->first_ts ?? null, $k->last_ts ?? null);
            echo '<div class="act-key' . ($open ? ' is-open' : '') . '"' . ($i >= $shown ? ' hidden' : '') . '>';
            echo '<div class="act-cols act-key__row' . (empty($k->detail) ? ' act-key__row--static' : '') . '"' . (empty($k->detail) ? '' : ' data-toggle="1"') . '>';
            echo '<div class="act-who"><span class="act-who__name">' . h($k->name) . '</span><span class="act-who__mail">' . h($k->email) . '</span>';
            if($k->is_new){ echo '<span class="act-tag">New</span>'; }
            if($k->billing_enabled){ echo '<span class="act-tag act-tag--ok">Billing</span>'; }
            elseif($k->refused > 0){ echo '<span class="act-tag act-tag--bad">Refused ' . actN($k->refused) . '</span>'; }
            elseif($near){ echo '<span class="act-tag act-tag--warn">Near limit</span>'; }
            echo '</div>';
            echo '<div class="act-num act-num--muted">…' . h($k->api_key_last4) . '</div>';
            echo '<div class="act-req"><div class="act-num">' . actN($k->requests) . '</div><div class="act-track"><div class="act-fill' . ($near ? ' act-fill--near' : '') . '" style="width:' . (int)round(100 * $k->requests / $top) . '%"></div></div></div>';
            echo '<div class="act-active">' . h($span) . '<small>' . h($spanSub) . '</small></div>';
            echo '<div class="act-mix">';
            if(!empty($k->detail)){
                $mixTotal = 0;
                foreach($k->mix as $fam => $n){ $mixTotal += $n; }
                foreach(array('list', 'detail', 'search', 'brewer_beer', 'write', 'other') as $fam){
                    $n = $k->mix->$fam ?? 0;
                    if($n > 0 && $mixTotal > 0){ echo '<span class="act-f-' . $fam . '" style="width:' . (100 * $n / $mixTotal) . '%" title="' . h(ucfirst(str_replace('_', ' ', $fam))) . ' · ' . actN($n) . '"></span>'; }
                }
            }
            echo '</div>';
            echo '<div class="act-agent" title="' . h(!empty($k->detail) ? implode(' · ', array_map(function($a){ return ($a->agent ?? 'none') . ' (' . $a->count . ')'; }, $k->agents)) : '') . '">' . h(!empty($k->detail) ? actAgentLabel($k->agents) : '') . '</div>';
            echo '</div>';

            if(!empty($k->detail)){
                $statusBits = array();
                foreach($k->statuses as $code => $n){ $statusBits[] = actN($n) . ' × ' . $code; }
                echo '<div class="act-detail"><div class="act-detail__main">';
                // Endpoints
                echo '<div><div class="act-sub"><span class="cb-label">Endpoints</span><small>' . actN($k->requests) . ' requests · ' . h(implode(', ', $statusBits)) . '</small></div>';
                $epMax = max(1, $k->endpoints[0]->count ?? 1);
                foreach(array_slice($k->endpoints, 0, 10) as $e){
                    $notOk = $e->count - $e->ok;
                    echo '<div class="act-ep"><div class="act-ep__m"><b>' . h($e->method) . '</b>' . h($e->endpoint) . ($notOk > 0 ? '<i>' . actN($notOk) . ' refused</i>' : '') . '</div><div class="act-track"><div class="act-fill" style="width:' . (int)round(100 * $e->count / $epMax) . '%"></div></div><div class="act-num">' . actN($e->count) . '</div><div class="act-num act-num--muted">' . ($e->avg_ms === null ? '—' : actN($e->avg_ms) . ' ms') . '</div></div>';
                }
                if(count($k->endpoints) > 10){ echo '<p class="act-empty">and ' . actN(count($k->endpoints) - 10) . ' more endpoints</p>'; }
                echo '</div>';
                // When: one bar per hour with traffic, placed on a grid of the month's hours
                $hourMax = 1;
                foreach($k->hours as $h => $n){ $hourMax = max($hourMax, $n); }
                echo '<div><div class="act-sub"><span class="cb-label">When</span><small>' . actN($k->active_days) . ' active day' . ($k->active_days === 1 ? '' : 's') . ' · busiest hour ' . actN($hourMax) . ' requests</small></div>';
                echo '<div class="act-hours" style="grid-template-columns:repeat(' . $hourCount . ',1fr)">';
                foreach($k->hours as $h => $n){
                    $col = (int)floor(((int)$h - $sinceHour) / 3600) + 1;
                    if($col < 1 || $col > $hourCount){ continue; }
                    echo '<span style="grid-column:' . $col . ';height:' . max(2, (int)round(56 * $n / $hourMax)) . 'px" title="' . h(date('M j H:00', (int)$h)) . ' · ' . actN($n) . '"></span>';
                }
                echo '</div><div class="act-days"><span>' . h(date('M j', $report->since)) . '</span><span>' . h(date('M j', $report->until)) . '</span></div></div>';
                // Searches
                if($k->searches->count > 0){
                    echo '<div><div class="act-sub"><span class="cb-label">Searches</span><small>' . actN($k->searches->count) . ' searches, ' . actN($k->searches->distinct) . ' distinct</small></div><div class="act-chips">';
                    foreach($k->searches->top as $term){ echo '<span class="act-chip">' . h($term) . '</span>'; }
                    $more = $k->searches->distinct - count($k->searches->top);
                    if($more > 0){ echo '<span class="act-chip act-chip--more">+' . actN($more) . ' more</span>'; }
                    echo '</div></div>';
                }
                echo '</div>';
                // Account rail
                $createdSub = '';
                if($k->created_at !== null && $k->first_ts !== null && $k->is_new){
                    $lead = (int)round(($k->first_ts - $k->created_at) / 60);
                    $createdSub = $lead < 60 ? $lead . ' minute' . ($lead === 1 ? '' : 's') . ' before its first request' : (int)round($lead / 60) . ' hours before its first request';
                }
                $agentFacts = array();
                foreach($k->agents as $a){ $agentFacts[] = ($a->agent ?? 'none') . ' (' . actN($a->count) . ')'; }
                echo '<aside class="act-rail"><span class="cb-label">Account</span>';
                echo '<div class="act-fact"><span class="act-fact__k">Created</span><span class="act-fact__v">' . ($k->created_at === null ? 'Before Oct 29, 2026' : h(date('M j, Y', $k->created_at))) . ($createdSub !== '' ? '<small>' . h($createdSub) . '</small>' : '') . '</span></div>';
                echo '<div class="act-fact"><span class="act-fact__k">Email</span><span class="act-fact__v">' . ($k->email_verified ? 'Verified' : 'Unverified') . '</span></div>';
                echo '<div class="act-fact"><span class="act-fact__k">Sign-up IP</span><span class="act-fact__v">' . ($k->signup_ip === null ? '—' : h($k->signup_ip)) . '</span></div>';
                echo '<div class="act-fact"><span class="act-fact__k">Request IPs</span><span class="act-fact__v">' . actN($k->ip_count) . '</span></div>';
                echo '<div class="act-fact"><span class="act-fact__k">Clients</span><span class="act-fact__v">' . h(implode(' · ', $agentFacts)) . '</span></div>';
                echo '<div class="act-fact"><span class="act-fact__k">Free tier</span><span class="act-fact__v">' . actN($k->this_month) . ' of ' . actN($k->request_limit) . '<small>' . ($k->billing_enabled ? 'Billing on · cap ' . actMoney($k->spend_cap_cents) : 'Billing off') . ($k->refused > 0 ? ' · ' . actN($k->refused) . ' refused' : '') . '</small></span></div>';
                echo '<div class="act-fact"><span class="act-fact__k">Writes</span><span class="act-fact__v">' . ($k->writes > 0 ? actN($k->writes) . ' revision' . ($k->writes === 1 ? '' : 's') : 'None') . '</span></div>';
                echo '<div class="act-rail-actions"><a class="cb-action" href="/admin/usage.php">Usage history →</a><a class="cb-action" href="mailto:' . h($k->email) . '">Email →</a></div>';
                echo '</aside></div>';
            }
            echo '</div>';
        }
        echo '<div class="act-legend"><span><i class="act-f-list"></i>List</span><span><i class="act-f-detail"></i>Detail</span><span><i class="act-f-search"></i>Search</span><span><i class="act-f-brewer_beer"></i>Brewer\'s beers</span><span><i class="act-f-write"></i>Writes</span><span><i class="act-f-other"></i>Other</span></div>';
        if(count($keys) > $shown){ echo '<button type="button" class="act-more" data-reveal="#keys .act-key[hidden]">Show the ' . actN(count($keys) - $shown) . ' other keys</button>'; }
    }else{
        $cols = 'minmax(0, 2fr) 3.6rem';
        foreach($months as $ym){ $cols .= ' 5.6rem'; }
        $cols .= ' 6rem';
        echo '<div class="act-tg act-tg--head" style="grid-template-columns:' . $cols . '"><span>Account</span><span>Key</span>';
        foreach($months as $ym){ echo '<span>' . h(actMon($ym)) . '</span>'; }
        echo '<span>Total</span></div>';
        foreach($keys as $i => $k){
            echo '<div class="act-tg act-tg--mkrow" style="grid-template-columns:' . $cols . '"' . ($i >= $shown ? ' hidden' : '') . '><span>' . h($k->name) . '<small>' . h($k->email) . '</small>' . ($k->billing_enabled ? '<span class="act-tag act-tag--ok">Billing</span>' : '') . ($k->refused > 0 ? '<span class="act-tag act-tag--bad">Refused ' . actN($k->refused) . '</span>' : '') . '</span><span class="is-dim">…' . h($k->api_key_last4) . '</span>';
            $stop = $k->request_limit + $k->request_buffer + 1;
            foreach($months as $ym){
                $n = $k->months->$ym ?? 0;
                $cls = $n === 0 ? 'is-dim' : ((!$k->billing_enabled && $n >= $stop) ? 'is-ceiling' : ($n > $k->request_limit ? 'is-over' : ''));
                echo '<span class="' . $cls . '">' . actN($n) . '</span>';
            }
            echo '<span>' . actN($k->requests) . '</span></div>';
        }
        echo '<div class="act-legend"><span><i class="act-f-brewer_beer"></i>Past the free tier</span><span><i class="act-f-write"></i>Stopped at the free ceiling</span></div>';
        if(count($keys) > $shown){ echo '<button type="button" class="act-more" data-reveal="#keys .act-tg--mkrow[hidden]">Show the ' . actN(count($keys) - $shown) . ' other keys</button>'; }
    }
    echo '</section>';

    // ----- Reads by endpoint -----
    echo '<section class="act-section" id="reads">';
    echo '<div class="act-sechead"><h2>Reads by endpoint</h2><p>Every key, the site\'s own included. ' . actN($report->read_traffic->total) . ' GET requests ' . h($period === 'month' ? 'this month' : 'in the period') . '. Average and slowest response in milliseconds; durations are recorded since Nov 2, 2026.</p></div>';
    $eps = $report->read_traffic->by_endpoint;
    if(empty($eps)){
        echo '<p class="act-empty">No reads.</p>';
    }else{
        $epTop = max(1, $eps[0]->count);
        foreach($eps as $i => $ep){
            echo '<div class="act-ep"' . ($i >= 12 ? ' hidden' : '') . '><div class="act-ep__m"><b>GET</b>' . h($ep->endpoint) . '</div><div class="act-track"><div class="act-fill" style="width:' . (int)round(100 * $ep->count / $epTop) . '%"></div></div><div class="act-num">' . actN($ep->count) . '</div><div class="act-num act-num--muted">' . ($ep->avg_ms === null ? '—' : actN($ep->avg_ms) . ' · ' . actN($ep->max_ms)) . '</div></div>';
        }
        if(count($eps) > 12){ echo '<button type="button" class="act-more" data-reveal="#reads .act-ep[hidden]">Show the ' . actN(count($eps) - 12) . ' other endpoints</button>'; }
    }
    echo '</section>';

    // ----- Write activity + recent revisions -----
    echo '<div class="act-grid2">';
    echo '<section class="act-section"><div class="act-sechead"><h2>Write activity</h2><p>From the audit log' . ($period === '3m' ? ', which began Nov 2, 2026' : '') . '. ' . ($report->test_entities_excluded > 0 ? actN($report->test_entities_excluded) . ' test-suite ' . ($report->test_entities_excluded === 1 ? 'entity' : 'entities') . ' excluded.' : '') . '</p></div>';
    $ws = $report->write_activity->summary;
    if(empty($ws)){
        echo '<p class="act-empty">No writes.</p>';
    }else{
        echo '<table class="act-table"><thead><tr><th>Resource</th><th>Created</th><th>Updated</th><th>Deleted</th><th>Total</th></tr></thead><tbody>';
        $tc = 0; $tu = 0; $td = 0;
        foreach($ws as $row){
            $tc += $row->created; $tu += $row->updated; $td += $row->deleted;
            echo '<tr><td>' . h(ucfirst($row->resource)) . '</td><td' . ($row->created === 0 ? ' class="is-dim"' : '') . '>' . actN($row->created) . '</td><td' . ($row->updated === 0 ? ' class="is-dim"' : '') . '>' . actN($row->updated) . '</td><td' . ($row->deleted === 0 ? ' class="is-dim"' : '') . '>' . actN($row->deleted) . '</td><td>' . actN($row->created + $row->updated + $row->deleted) . '</td></tr>';
        }
        echo '<tr class="is-total"><td>Total</td><td>' . actN($tc) . '</td><td>' . actN($tu) . '</td><td>' . actN($td) . '</td><td>' . actN($tc + $tu + $td) . '</td></tr></tbody></table>';
    }
    echo '<div class="act-sechead" style="margin-top:2rem"><h2>Top contributors</h2></div>';
    $tcs = $report->write_activity->top_contributors;
    if(empty($tcs)){
        echo '<p class="act-empty">No contributors.</p>';
    }else{
        echo '<table class="act-table"><thead><tr><th>Account</th><th>Created</th><th>Updated</th><th>Deleted</th><th>Total</th></tr></thead><tbody>';
        foreach($tcs as $c){
            $who = !empty($c->email) ? '<a href="mailto:' . h($c->email) . '">' . h($c->name) . '</a>' : h($c->name);
            echo '<tr><td>' . $who . '</td><td' . ($c->created === 0 ? ' class="is-dim"' : '') . '>' . actN($c->created) . '</td><td' . ($c->updated === 0 ? ' class="is-dim"' : '') . '>' . actN($c->updated) . '</td><td' . ($c->deleted === 0 ? ' class="is-dim"' : '') . '>' . actN($c->deleted) . '</td><td>' . actN($c->total) . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</section>';

    echo '<section class="act-section" id="recent"><div class="act-sechead"><h2>Recent revisions</h2><p>Newest first.</p></div>';
    $recent = $report->write_activity->recent;
    if(empty($recent)){
        echo '<p class="act-empty">No revisions.</p>';
    }else{
        $today = date('Y-m-d');
        foreach($recent as $i => $entry){
            $entityType = $entry->entity_type ?? '';
            $entityID = $entry->entity_id ?? '';
            switch($entry->action ?? ''){
                case 'create': $action = 'Created'; break;
                case 'update': $action = 'Updated'; break;
                case 'delete': $action = 'Deleted'; break;
                case 'revert': $action = 'Restored'; break;
                default: $action = ucfirst($entry->action ?? '');
            }
            $resourceName = $entry->resource_name ?? null;
            $deleted = (($entry->action ?? '') === 'delete');
            if($resourceName !== null && $entityID !== '' && !$deleted){
                $what = '<a href="/' . h($entityType) . '/' . h($entityID) . '">' . h($resourceName) . '</a>';
            }elseif($resourceName !== null){
                $what = h($resourceName);
            }else{
                $what = '<span class="act-mono">' . h($entityType . '/' . $entityID) . '</span>';
            }
            $ctx = '';
            if(!empty($entry->brewer_name) && $entityType !== 'brewer'){
                $ctx = ' <span>· ' . (!empty($entry->brewer_id) ? '<a href="/brewer/' . h($entry->brewer_id) . '">' . h($entry->brewer_name) . '</a>' : h($entry->brewer_name)) . '</span>';
            }
            $note = !empty($entry->note) ? ' <span>' . h($entry->note) . '</span>' : '';
            $when = (date('Y-m-d', $entry->timestamp) === $today ? 'Today ' . date('H:i', $entry->timestamp) : date('M j H:i', $entry->timestamp));
            $history = ($entityID !== '' && in_array($entityType, array('brewer', 'beer', 'location'), true)) ? '<a href="/' . h($entityType) . '/' . h($entityID) . '/history#rev-' . intval($entry->audit_id ?? 0) . '">#' . intval($entry->audit_id ?? 0) . '</a>' : '';
            echo '<div class="act-rev"' . ($i >= 20 ? ' hidden' : '') . '><span class="act-rev__t">' . h($when) . '</span><span class="act-rev__what"><span>' . h($action . ' ' . $entityType) . '</span> <b>' . $what . '</b>' . $ctx . $note . '</span><span class="act-rev__who">' . h($entry->user_name) . ($history !== '' ? ' ' . $history : '') . '</span></div>';
        }
        if(count($recent) > 20){ echo '<button type="button" class="act-more" data-reveal="#recent .act-rev[hidden]">Show the ' . actN(count($recent) - 20) . ' older revisions</button>'; }
    }
    echo '</section></div>';
}
?>
    </main>
    <?php echo $nav->footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script>
    (function(){
        // Open and close a key's drill-down
        document.querySelectorAll('.act-key__row[data-toggle]').forEach(function(row){
            row.addEventListener('click', function(e){
                if(e.target.closest('a')){ return; }
                row.parentElement.classList.toggle('is-open');
            });
        });
        // "Show the N other …" reveals the hidden rows
        document.querySelectorAll('.act-more[data-reveal]').forEach(function(btn){
            btn.addEventListener('click', function(){
                document.querySelectorAll(btn.getAttribute('data-reveal')).forEach(function(el){ el.hidden = false; });
                btn.remove();
            });
        });
        // Requests per day: external keys stacked over the site's own
        var box = document.getElementById('actChart');
        if(!box){ return; }
        var series = JSON.parse(box.getAttribute('data-series'));
        if(!window.Chart || !series.labels.length){
            document.getElementById('actChartCanvas').hidden = true;
            document.getElementById('actChartFallback').hidden = false;
            return;
        }
        var css = getComputedStyle(document.documentElement);
        var amber = css.getPropertyValue('--cb-amber').trim() || '#D49A3D';
        var paper3 = css.getPropertyValue('--cb-paper-3').trim() || '#E9E2D3';
        var muted = css.getPropertyValue('--cb-muted').trim() || '#6B6660';
        var line = css.getPropertyValue('--cb-line').trim() || '#E4DDCF';
        var mono = css.getPropertyValue('--cb-mono').trim() || 'ui-monospace, monospace';
        new Chart(document.getElementById('actChartCanvas'), {
            type: 'bar',
            data: { labels: series.labels, datasets: [
                { label: 'External keys', data: series.external, backgroundColor: amber, borderWidth: 0, stack: 'a' },
                { label: "The site's own keys", data: series.site, backgroundColor: paper3, borderWidth: 0, stack: 'a' }
            ] },
            options: {
                maintainAspectRatio: false, animation: false,
                plugins: { legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 9, boxHeight: 9, color: muted, font: { family: mono, size: 10 } } },
                           tooltip: { callbacks: { label: function(c){ return c.dataset.label + ': ' + c.parsed.y.toLocaleString('en-US'); } } } },
                scales: {
                    x: { stacked: true, grid: { display: false }, border: { color: line }, ticks: { color: muted, font: { family: mono, size: 10 }, maxTicksLimit: 12, maxRotation: 0 } },
                    y: { stacked: true, grid: { color: line }, border: { display: false }, ticks: { color: muted, font: { family: mono, size: 10 }, maxTicksLimit: 4, callback: function(v){ return v.toLocaleString('en-US'); } } }
                }
            }
        });
    })();
    </script>
</body>
</html>
