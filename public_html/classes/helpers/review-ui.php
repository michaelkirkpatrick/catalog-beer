<?php
/*
Rendering shared by the two check-in pages, admin/reviews.php and
admin/leads.php. Function helpers, not a class, on the pattern of the other
files in this directory; required explicitly by the two pages that use it
rather than from initialize.php, since nothing else on the site reads the
review loop.

Two families:

  - The primitives both pages build from: a date, a sub-heading, a source
    list, a stepper chip. These moved here from reviews.php unchanged.
  - The lead pieces: header, supplement and rail for one lead, and the
    small labels the queue table uses. A lead is a name, a place and the page
    it was read on (API: /brewer-lead), so its screen is mostly prose and
    links; there is no changes table because a lead writes nothing.

Every value here is escaped at output with h(); nothing is escaped on the way
in.
*/

// ----- Primitives -----

function reviewDate($ts){
    return $ts ? date('M j, Y', intval($ts)) : '&#8212;';
}

function subhead($label, $count = null, $stacked = false, $accent = false){
    $html = '<div class="rv-sup__h' . ($stacked ? ' rv-sup__h--stacked' : '') . '">';
    $html .= '<span class="cb-label' . ($accent ? ' cb-label--accent' : '') . '">' . h($label) . '</span>';
    if(!is_null($count)){
        $html .= '<span class="cb-count">' . number_format($count) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

function sourcesHtml($sources){
    $html = '<ul class="rv-sources">';
    foreach($sources as $source){
        $source = (string)$source;
        $url = strtok($source, ' ');
        $note = substr($source, strlen($url));
        if(preg_match('#^https?://#i', $url)){
            $html .= '<li><a href="' . h($url) . '" target="_blank" rel="noopener noreferrer">' . h($url) . '</a><span class="rv-sources__note">' . h($note) . '</span></li>';
        }else{
            $html .= '<li><span class="rv-sources__note">' . h($source) . '</span></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

// A chip that is a link when there is somewhere to go and an inert button when
// there is not — an <a> has no disabled state, and a dead href is worse.
function stepChip($label, $href){
    if($href === ''){
        return '<button type="button" class="cb-chip" disabled>' . $label . '</button>';
    }
    return '<a class="cb-chip" href="' . h($href) . '">' . $label . '</a>';
}

function alertHtml($msg, $isSuccess = false){
    $html = '<div class="cbf-alert' . ($isSuccess ? ' cbf-alert--ok" role="status"' : '" role="alert"') . '>';
    $html .= '<span class="cbf-alert__i" aria-hidden="true">' . ($isSuccess ? '&#10003;' : '!') . '</span>';
    $html .= '<div>' . h($msg) . '</div></div>';
    return $html;
}

// ----- Leads -----

function leadHref($lead){
    return '/admin/leads/' . rawurlencode($lead->id);
}

function leadName($lead){
    return !empty($lead->name) ? $lead->name : $lead->id;
}

// "Bend, OR" from what the source printed; '' when the lead carries only a URL.
function leadPlace($lead){
    $parts = array();
    if(!empty($lead->city)){ $parts[] = $lead->city; }
    if(!empty($lead->state_short)){ $parts[] = $lead->state_short; }
    return implode(', ', $parts);
}

// The url is stored as sent and never fetched by the API, so it may lack a
// scheme. Give it one for the href only; show it as stored.
function leadSiteHtml($lead){
    if(empty($lead->url)){
        return '<span class="rv-null">none</span>';
    }
    $href = preg_match('#^https?://#i', $lead->url) ? $lead->url : 'http://' . $lead->url;
    return '<a href="' . h($href) . '" target="_blank" rel="noopener noreferrer">' . h($lead->url) . '</a>';
}

// One word on where the lead is in its life, for a table cell or a rail. The
// wire status already folds an expired claim back to queued; this adds the
// three things a queued row can also be.
function leadState($lead){
    if($lead->status === 'closed'){
        return array('text' => str_replace('_', ' ', (string)$lead->resolution), 'accent' => false);
    }
    if(!empty($lead->needs_decision)){
        return array('text' => 'question waiting', 'accent' => true);
    }
    if(!empty($lead->decision)){
        return array('text' => 'decided', 'accent' => true);
    }
    if(!empty($lead->recheck_after) && intval($lead->recheck_after) > time()){
        return array('text' => 'deferred to ' . date('M Y', intval($lead->recheck_after)), 'accent' => false);
    }
    return array('text' => (string)$lead->status, 'accent' => false);
}

function leadStateTag($lead){
    $state = leadState($lead);
    return '<span class="cb-tag' . ($state['accent'] ? ' cb-tag--accent' : '') . '">' . h($state['text']) . '</span>';
}

// Name, then place and date, in the same shape as a review's brewer line.
function leadHeader($lead, $linkToLead = true){
    $html = '<div class="rv-brewer">';
    if($linkToLead){
        $html .= '<a class="rv-brewer__name" href="' . leadHref($lead) . '">' . h(leadName($lead)) . '</a>';
    }else{
        $html .= '<span class="rv-brewer__name">' . h(leadName($lead)) . '</span>';
    }
    $meta = 'queued ' . reviewDate($lead->created_at);
    $place = leadPlace($lead);
    if($place !== ''){ $meta = h($place) . ' &#183; ' . $meta; }
    $html .= '<span class="rv-brewer__meta">' . $meta . '</span></div>';
    return $html;
}

// Question and decision, note, and every page that named the lead.
// $withQuestion false leaves the question to the caller (the queue renders it
// large, above the answer form).
function leadSupplement($lead, $withQuestion = true){
    $sources = is_array($lead->sources ?? null) ? $lead->sources : array();

    $html = '<div class="rv-sup">';

    if($withQuestion && (!empty($lead->question) || !empty($lead->decision))){
        $html .= '<div>';
        if(!empty($lead->question)){
            $html .= subhead('Question');
            $html .= '<p class="rv-notes">' . h($lead->question) . '</p>';
        }
        if(!empty($lead->decision)){
            $html .= subhead('Decision', null, !empty($lead->question), true);
            $html .= '<p class="rv-decision">' . h($lead->decision) . '<small>' . reviewDate($lead->decided_at) . '</small></p>';
        }elseif(!empty($lead->needs_decision)){
            $html .= '<p class="rv-none rv-none--stacked">waiting on you &#8212; <a href="/admin/reviews">answer it in the queue</a></p>';
        }
        $html .= '</div>';
    }

    $html .= '<div>';
    $html .= subhead('Note');
    $html .= !empty($lead->note) ? '<p class="rv-notes">' . h($lead->note) . '</p>' : '<p class="rv-none">none</p>';
    $html .= '</div>';

    $html .= '<div>';
    $html .= subhead('Read on', count($sources));
    $html .= !empty($sources) ? sourcesHtml($sources) : '<p class="rv-none">none</p>';
    $html .= '</div>';

    $html .= '</div>';
    return $html;
}

// The rail beside a lead: where it is, who put it there, what happened to it.
function leadRail($lead){
    $html = '<aside class="cb-rail">';
    $html .= '<span class="cb-label">This lead</span>';

    $facts = array(
        'Status' => leadStateTag($lead),
        'Website' => leadSiteHtml($lead),
        'Place' => h(leadPlace($lead) ?: '—')
    );
    if(!empty($lead->brewer_id)){
        $facts['Brewer'] = '<a href="/brewer/' . rawurlencode($lead->brewer_id) . '">' . h($lead->brewer_name ?: $lead->brewer_id) . ' &#8594;</a>';
    }
    if($lead->status === 'closed'){
        $facts['Closed'] = reviewDate($lead->resolved_at);
    }
    if(!empty($lead->recheck_after)){
        $facts['Recheck'] = reviewDate($lead->recheck_after);
    }
    if(!empty($lead->claimed_by)){
        $facts['Claimed'] = 'until ' . date('H:i', intval($lead->claim_expires_at));
    }
    $facts['Queued'] = reviewDate($lead->created_at);
    $facts['Last seen'] = reviewDate($lead->last_seen_at);
    $facts['Lead id'] = '<span title="' . h($lead->id) . '">' . h(substr($lead->id, 0, 8)) . '</span>';

    foreach($facts as $key => $value){
        $html .= '<div class="cb-fact"><span class="cb-fact__k">' . h($key) . '</span><span class="cb-fact__v cb-fact__v--sm">' . $value . '</span></div>';
    }
    $html .= '</aside>';
    return $html;
}

// The answer form. Both pages post it; $action says where the redirect lands
// afterwards (the page that rendered it).
function leadAnswerForm($lead, $action){
    $html = '<form class="rv-answer" method="post" action="' . h($action) . '">';
    $html .= csrf_field();
    $html .= '<input type="hidden" name="lead_id" value="' . h($lead->id) . '">';
    $decision = new Textarea();
    $decision->name = 'decision';
    $decision->description = 'Your decision';
    $decision->hint = 'One or two sentences. Say what to do with the lead — create it, or which resolution to close it with.';
    $decision->required = true;
    $decision->markRequired = false;
    $decision->maxLength = 500;
    $decision->showCount = true;
    $decision->rows = 3;
    $html .= $decision->display();
    $html .= '<div class="cbf-actions"><button type="submit" class="cbf-btn">Record decision</button>';
    $html .= '<span class="cbf-actnote">The next lead claim acts on it first</span></div>';
    $html .= '</form>';
    return $html;
}

// Records a lead decision through the API. Returns the flash array the caller
// stores in the session: the same post/redirect/get both pages use.
function recordLeadDecision($api, $leadID, $decision){
    if(!preg_match('/^[0-9a-f-]{36}$/', $leadID) || $decision === ''){
        return array('type' => 'error', 'msg' => 'An answer needs a lead id and some text.');
    }
    $response = $api->request('PATCH', '/brewer-lead/' . $leadID, ['decision' => $decision]);
    $result = json_decode($response);
    if(isset($result->error) && $result->error){
        return array('type' => 'error', 'msg' => $result->error_msg);
    }
    $name = $result->name ?? '';
    return array('type' => 'success', 'msg' => 'Decision recorded' . ($name !== '' ? ' for the lead ' . $name : '') . '. The next lead claim acts on it first.');
}
?>
