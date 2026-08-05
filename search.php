<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Search results — server-rendered Algolia page ("/search?q=…").

The destination for the global nav search box. One `catalog` index holds four
record types (beer / brewer / location / style) discriminated by `type`;
the type tabs are the primary navigation and the structural fix for
mixed-type ranking (no customRanking — inside a tab, everything is one type).

Refinement model, deliberately simple for a no-JS page:
 - Facets live INSIDE a type tab. The All tab is query-only — a cross-type
   filter would silently drop the types that don't carry the attribute
   (styles have no geography, brewers have no ABV), which is worse than
   offering fewer knobs.
 - One value per facet group (clicking replaces; × removes).
 - Every piece of state is in the query string, so results are linkable and
   the back button works.

Counts are honest: Algolia computes facet counts AFTER filters, so the tab
counts and each refined group's alternatives come from companion queries in
the same multi-query request (minus the type filter / minus that group's own
refinement). One HTTP round trip total.

Zero results degrade in order: offer to clear refinements → retry with the
last query token dropped (labeled, never silent) → suggest a style via the
API's /style/search, whose alias+fulltext matching catches what Algolia's
typo tolerance can't.

Design system: composes .cb-* primitives; sr-* classes are page layout only
(styles-pages.css). SRM swatches/gradients are data-driven inline styles.
--- */

// Required Classes
$search = new Search();

// ----- Parse query-string state -----

$q = trim((string)($_GET['q'] ?? ''));
if(strlen($q) > 255){
    $q = substr($q, 0, 255);
}

$validTypes = array('beer', 'brewer', 'location', 'style');
$type = (isset($_GET['type']) && in_array($_GET['type'], $validTypes, true)) ? $_GET['type'] : '';

// Facet groups: which Algolia attribute each refines, and which tabs carry
// that attribute. A param outside its tabs is ignored, and tab links only
// carry the params their target understands — so switching tabs sheds
// refinements that would otherwise zero out the results.
$facetGroups = array(
    'family'  => array('attr'=>'style_family',  'label'=>'Style family', 'tabs'=>array('beer','style')),
    'bev'     => array('attr'=>'beverage_type', 'label'=>'Beverage',     'tabs'=>array('beer','style')),
    'state'   => array('attr'=>'states',        'label'=>'State',        'tabs'=>array('beer','brewer','location')),
    'city'    => array('attr'=>'cities',        'label'=>'City',         'tabs'=>array('beer','brewer','location')),
    'country' => array('attr'=>'countries',     'label'=>'Country',      'tabs'=>array('beer','brewer','location')),
    'brewer'  => array('attr'=>'brewer.name',   'label'=>'Brewer',       'tabs'=>array('beer','location')),
);

// ABV/IBU are filterOnly() attributes — range buckets, no counts available.
// Missing-attribute records are EXCLUDED from numeric filters, so the IBU
// group carries an explicit "listed IBU only" note in the rail.
$abvBuckets = array(
    'u5'  => array('label'=>'Under 5%', 'filters'=>array('abv<5')),
    '5-7' => array('label'=>'5–7%',     'filters'=>array('abv>=5','abv<7')),
    '7-9' => array('label'=>'7–9%',     'filters'=>array('abv>=7','abv<9')),
    '9p'  => array('label'=>'9%+',      'filters'=>array('abv>=9')),
);
$ibuBuckets = array(
    'u20'   => array('label'=>'Under 20', 'filters'=>array('ibu<20')),
    '20-40' => array('label'=>'20–40',    'filters'=>array('ibu>=20','ibu<40')),
    '40-60' => array('label'=>'40–60',    'filters'=>array('ibu>=40','ibu<60')),
    '60p'   => array('label'=>'60+',      'filters'=>array('ibu>=60')),
);

// Current state, one source of truth for URL building
$state = array('q'=>$q, 'type'=>$type, 'page'=>1);
foreach(array_keys($facetGroups) as $key){
    $state[$key] = isset($_GET[$key]) ? trim((string)$_GET[$key]) : '';
}
$state['abv'] = (isset($_GET['abv']) && isset($abvBuckets[$_GET['abv']])) ? $_GET['abv'] : '';
$state['ibu'] = (isset($_GET['ibu']) && isset($ibuBuckets[$_GET['ibu']])) ? $_GET['ibu'] : '';
$state['verified'] = isset($_GET['verified']) ? '1' : '';

$perPage = 25;
// Algolia's paginationLimitedTo defaults to 1,000 hits
$maxPages = intval(1000 / $perPage);
if(isset($_GET['page'])){
    $requested = filter_var($_GET['page'], FILTER_VALIDATE_INT);
    if($requested !== false && $requested >= 1 && $requested <= $maxPages){
        $state['page'] = $requested;
    }
}

// Which groups apply on the current tab, and which are actively refining
function srGroupApplies($group, $forType){
    return $forType !== '' && in_array($forType, $group['tabs'], true);
}
$activeGroups = array();
foreach($facetGroups as $key => $group){
    if($state[$key] !== '' && srGroupApplies($group, $type)){
        $activeGroups[$key] = $state[$key];
    }
}
$abvActive = ($type === 'beer' && $state['abv'] !== '');
$ibuActive = ($type === 'beer' && $state['ibu'] !== '');
$verifiedActive = ($type === 'beer' && $state['verified'] === '1');
$hasRefinements = (!empty($activeGroups) || $abvActive || $ibuActive || $verifiedActive);

// ----- URL builder -----
// Everything is a link; every link is the current state plus overrides.
// null/'' removes a param; page resets to 1 unless explicitly set.
function srURL(array $overrides = array()){
    global $state;
    $params = array_merge($state, array('page'=>1), $overrides);
    $qs = array();
    foreach($params as $key => $value){
        if($value === '' || $value === null){
            continue;
        }
        if($key === 'page' && intval($value) <= 1){
            continue;
        }
        $qs[$key] = $value;
    }
    $encoded = http_build_query($qs);
    return '/search' . ($encoded !== '' ? '?' . $encoded : '');
}

// Tab links carry only what the target tab understands
function srTabURL($targetType){
    global $facetGroups, $state;
    $overrides = array('type'=>$targetType);
    foreach($facetGroups as $key => $group){
        if($state[$key] !== '' && ($targetType === '' || !in_array($targetType, $group['tabs'], true))){
            $overrides[$key] = '';
        }
    }
    if($targetType !== 'beer'){
        $overrides['abv'] = '';
        $overrides['ibu'] = '';
        $overrides['verified'] = '';
    }
    return srURL($overrides);
}

// ----- Build the Algolia filter sets -----

// facetFilters for a given tab, optionally excluding one group (for the
// disjunctive companion queries)
function srFacetFilters($forType, $excludeGroup = null){
    global $facetGroups, $state;
    $filters = array();
    if($forType !== ''){
        $filters[] = array('type:' . $forType);
    }
    foreach($facetGroups as $key => $group){
        if($key !== $excludeGroup && $state[$key] !== '' && srGroupApplies($group, $forType)){
            $filters[] = array($group['attr'] . ':' . $state[$key]);
        }
    }
    if($forType === 'beer' && $state['verified'] === '1'){
        $filters[] = array('cb_verified:true');
    }
    return $filters;
}

function srNumericFilters($forType){
    global $abvBuckets, $ibuBuckets, $state;
    $filters = array();
    if($forType === 'beer'){
        if($state['abv'] !== ''){
            $filters = array_merge($filters, $abvBuckets[$state['abv']]['filters']);
        }
        if($state['ibu'] !== ''){
            $filters = array_merge($filters, $ibuBuckets[$state['ibu']]['filters']);
        }
    }
    return $filters;
}

// Facet attributes the current tab's rail renders (counts ride the main query)
$railAttrs = array();
foreach($facetGroups as $key => $group){
    if(srGroupApplies($group, $type)){
        $railAttrs[] = $group['attr'];
    }
}

// ----- Assemble the multi-query request -----

$queries = array();

// [0] Main query: hits + facet counts for the rail's unrefined groups
$main = array(
    'query'       => $q,
    'hitsPerPage' => $perPage,
    'page'        => $state['page'] - 1,
    'attributesToHighlight' => array(),
);
$mainFacetFilters = srFacetFilters($type);
$mainNumericFilters = srNumericFilters($type);
if(!empty($mainFacetFilters)){ $main['facetFilters'] = $mainFacetFilters; }
if(!empty($mainNumericFilters)){ $main['numericFilters'] = $mainNumericFilters; }
if(!empty($railAttrs)){
    $main['facets'] = $railAttrs;
    $main['maxValuesPerFacet'] = 100;
}
$queries[] = $main;

// [1] Tab counts. With no refinements one type-facet query covers every tab;
// with refinements each tab's count must reflect what clicking it yields
// (its own applicable filter subset), so it's one small query per tab.
$tabCountPlan = array();
if(!$hasRefinements){
    $tabCountPlan['*'] = count($queries);
    $queries[] = array(
        'query' => $q,
        'hitsPerPage' => 0,
        'attributesToHighlight' => array(),
        'facets' => array('type'),
    );
}else{
    foreach($validTypes as $tabType){
        $tabCountPlan[$tabType] = count($queries);
        $tabQuery = array(
            'query' => $q,
            'hitsPerPage' => 0,
            'attributesToHighlight' => array(),
        );
        $ff = srFacetFilters($tabType);
        $nf = srNumericFilters($tabType);
        if(!empty($ff)){ $tabQuery['facetFilters'] = $ff; }
        if(!empty($nf)){ $tabQuery['numericFilters'] = $nf; }
        $queries[] = $tabQuery;
    }
}

// [2..] Disjunctive companions: for each refined group, the counts of its
// alternatives — same query minus that group's own filter.
$disjunctivePlan = array();
foreach($activeGroups as $key => $value){
    $disjunctivePlan[$key] = count($queries);
    $dQuery = array(
        'query' => $q,
        'hitsPerPage' => 0,
        'attributesToHighlight' => array(),
        'facets' => array($facetGroups[$key]['attr']),
        'maxValuesPerFacet' => 100,
    );
    $ff = srFacetFilters($type, $key);
    $nf = srNumericFilters($type);
    if(!empty($ff)){ $dQuery['facetFilters'] = $ff; }
    if(!empty($nf)){ $dQuery['numericFilters'] = $nf; }
    $queries[] = $dQuery;
}

// ----- Run it -----
$results = $search->multiQuery($queries);
$searchDown = ($results === null);

$mainResult = $searchDown ? null : ($results[0] ?? null);
$nbHits = $mainResult['nbHits'] ?? 0;
$nbPages = min(intval($mainResult['nbPages'] ?? 0), $maxPages);
$hits = $mainResult['hits'] ?? array();

// Tab counts
$tabCounts = array();
if(!$searchDown){
    if(isset($tabCountPlan['*'])){
        $typeFacet = $results[$tabCountPlan['*']]['facets']['type'] ?? array();
        foreach($validTypes as $tabType){
            $tabCounts[$tabType] = intval($typeFacet[$tabType] ?? 0);
        }
    }else{
        foreach($validTypes as $tabType){
            $tabCounts[$tabType] = intval($results[$tabCountPlan[$tabType]]['nbHits'] ?? 0);
        }
    }
}

// Rail facet values: refined groups read from their companion query,
// unrefined groups from the main query.
function srFacetValues($key){
    global $results, $disjunctivePlan, $mainResult, $facetGroups;
    $attr = $facetGroups[$key]['attr'];
    if(isset($disjunctivePlan[$key])){
        return $results[$disjunctivePlan[$key]]['facets'][$attr] ?? array();
    }
    return $mainResult['facets'][$attr] ?? array();
}

// ----- Zero-result fallbacks -----

$fallbackNotice = '';
$styleSuggestions = array();
if(!$searchDown && $nbHits === 0 && $q !== ''){
    if(!$hasRefinements){
        // Retry with the last token dropped — labeled, never silent. (Kept off
        // Algolia's removeWordsIfNoResults because that drops the constraint
        // invisibly: Oregon beers for a Colorado search with no indication.)
        $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
        if(count($tokens) >= 2){
            $shortQ = implode(' ', array_slice($tokens, 0, -1));
            $fbQuery = array(
                'query' => $shortQ,
                'hitsPerPage' => $perPage,
                'attributesToHighlight' => array(),
            );
            if(!empty($mainFacetFilters)){ $fbQuery['facetFilters'] = $mainFacetFilters; }
            $fbResults = $search->multiQuery(array($fbQuery));
            if($fbResults !== null && intval($fbResults[0]['nbHits'] ?? 0) > 0){
                $fallbackNotice = 'No matches for &ldquo;' . htmlspecialchars($q, ENT_QUOTES) . '&rdquo;. Showing results for &ldquo;' . htmlspecialchars($shortQ, ENT_QUOTES) . '&rdquo;.';
                $mainResult = $fbResults[0];
                $nbHits = intval($mainResult['nbHits']);
                $nbPages = min(intval($mainResult['nbPages'] ?? 0), $maxPages);
                $hits = $mainResult['hits'] ?? array();
            }
        }
    }

    // Still nothing: ask the API's style search for a "did you mean the
    // style…" suggestion — its alias + fulltext matching catches abbreviations
    // and misspellings that Algolia's typo tolerance can't.
    if($nbHits === 0 && ($type === '' || $type === 'beer' || $type === 'style')){
        $api = new API();
        $suggestResp = $api->request('GET', '/style/search?q=' . urlencode($q) . '&count=3', '');
        if(!$api->unavailable()){
            $suggestData = json_decode($suggestResp);
            if(isset($suggestData->data) && is_array($suggestData->data)){
                $styleSuggestions = array_slice($suggestData->data, 0, 3);
            }
        }
    }
}

// ----- Rendering helpers -----

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES);
}

// Only ever link to hit-supplied URLs that are local paths
function srHitURL($hit){
    $url = (string)($hit['page_url'] ?? '');
    if($url === '' || $url[0] !== '/' || (strlen($url) > 1 && $url[1] === '/')){
        return '';
    }
    return $url;
}

// Snippets arrive with <em> highlight markup from Algolia; strip to plain
// text and escape — user-contributed content never lands unescaped.
function srSnippet($hit, $attr){
    $value = $hit['_snippetResult'][$attr]['value'] ?? '';
    if($value === ''){
        return '';
    }
    return h(strip_tags($value));
}

// State code → display name (facet values are the short codes the API
// derives from sub_code, e.g. "OR"; the long name reads better in the rail)
$stateNames = array(
    'AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California','CO'=>'Colorado',
    'CT'=>'Connecticut','DE'=>'Delaware','DC'=>'District of Columbia','FL'=>'Florida','GA'=>'Georgia',
    'HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky',
    'LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland','MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota',
    'MS'=>'Mississippi','MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire',
    'NJ'=>'New Jersey','NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota',
    'OH'=>'Ohio','OK'=>'Oklahoma','OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina',
    'SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia',
    'WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming'
);
function srFacetLabel($key, $value){
    global $stateNames;
    if($key === 'state' && isset($stateNames[$value])){
        return $stateNames[$value];
    }
    if($key === 'bev'){
        return ucfirst($value);
    }
    return $value;
}

// Type labels for tabs and mixed All-tab rows
$typeLabels = array('beer'=>'Beers', 'brewer'=>'Brewers', 'location'=>'Places', 'style'=>'Styles');
$typeLabelSingular = array('beer'=>'Beer', 'brewer'=>'Brewer', 'location'=>'Place', 'style'=>'Style');

// One result row. Each type leads with its name and carries the metadata a
// row can honestly promise (beer descriptions are ~0.2% populated — beers
// render around metadata, styles around prose).
function srRenderHit($hit){
    global $type, $typeLabelSingular;
    $hitType = (string)($hit['type'] ?? '');
    $url = srHitURL($hit);
    if($url === '' || $hitType === ''){
        return '';
    }
    $name = h($hit['name'] ?? '');
    if($name === ''){
        return '';
    }

    $html = '<a class="sr-hit" href="' . h($url) . '">';

    // Lead line: swatch/gradient + name (+ type tag on the mixed All tab)
    $html .= '<span class="sr-hit__lead">';
    if($hitType === 'beer'){
        $html .= '<span class="cb-swatch sr-swatch" style="background:' . SRM::hex($hit['srm'] ?? null) . ';"></span>';
    }elseif($hitType === 'style'){
        $min = $hit['srm_min'] ?? null;
        $max = $hit['srm_max'] ?? null;
        if(is_numeric($min) || is_numeric($max)){
            $lo = is_numeric($min) ? floatval($min) : floatval($max);
            $hi = is_numeric($max) ? floatval($max) : floatval($min);
            $html .= '<span class="sr-grad" style="background:' . SRM::gradient($lo, $hi) . ';"></span>';
        }
    }
    $html .= '<span class="sr-hit__name">' . $name . '</span>';
    if(($hit['cb_verified'] ?? false) === true || ($hit['brewer_verified'] ?? false) === true){
        $html .= '<span class="cb-tag cb-tag--accent">Verified</span>';
    }
    if(!empty($hit['beverage_type']) && $hit['beverage_type'] !== 'beer'){
        $html .= '<span class="cb-tag">' . h(ucfirst($hit['beverage_type'])) . '</span>';
    }
    if($type === ''){
        $html .= '<span class="sr-hit__type">' . h($typeLabelSingular[$hitType] ?? '') . '</span>';
    }
    $html .= '</span>';

    // Context line + right-hand value, by type
    $context = array();
    $value = '';
    if($hitType === 'beer'){
        if(!empty($hit['brewer']['name'])){ $context[] = h($hit['brewer']['name']); }
        if(!empty($hit['style_family'])){
            $context[] = h($hit['style_family']);
        }elseif(!empty($hit['style'])){
            $context[] = h($hit['style']);
        }
        $bits = array();
        if(isset($hit['abv']) && is_numeric($hit['abv'])){ $bits[] = rtrim(rtrim(number_format(floatval($hit['abv']), 1), '0'), '.') . '% ABV'; }
        if(isset($hit['ibu']) && is_numeric($hit['ibu'])){ $bits[] = intval($hit['ibu']) . ' IBU'; }
        $value = h(implode(' · ', $bits));
    }elseif($hitType === 'brewer'){
        // A brewer's subtitle is "City, ST" — but older index records fall back
        // to the short description when the brewer has no located taproom, and
        // that same prose renders as the snippet below. Compare against the
        // source attributes, not the snippet, which arrives ellipsised.
        $subtitle = (string)($hit['subtitle'] ?? '');
        if($subtitle !== '' && $subtitle !== (string)($hit['short_description'] ?? '') && $subtitle !== (string)($hit['description'] ?? '')){
            $context[] = h($subtitle);
        }
        $bits = array();
        if(isset($hit['beer_count']) && intval($hit['beer_count']) > 0){
            $bits[] = number_format(intval($hit['beer_count'])) . (intval($hit['beer_count']) === 1 ? ' beer' : ' beers');
        }
        if(isset($hit['location_count']) && intval($hit['location_count']) > 0){
            $bits[] = number_format(intval($hit['location_count'])) . (intval($hit['location_count']) === 1 ? ' location' : ' locations');
        }
        $value = h(implode(' · ', $bits));
    }elseif($hitType === 'location'){
        if(!empty($hit['brewer']['name'])){ $context[] = h($hit['brewer']['name']); }
        $place = array();
        if(!empty($hit['address']['city'])){ $place[] = $hit['address']['city']; }
        if(!empty($hit['address']['state_short'])){ $place[] = $hit['address']['state_short']; }
        if(!empty($place)){ $context[] = h(implode(', ', $place)); }
        // Not linked as tel: — the whole row is already a link to the location
        // page, and a nested anchor is invalid HTML. The number is here to
        // identify the taproom, not to be dialled from the results list.
        if(!empty($hit['address']['telephone'])){ $value = h(formatTelephone($hit['address']['telephone'])); }
    }elseif($hitType === 'style'){
        if(!empty($hit['subtitle'])){ $context[] = h($hit['subtitle']); }
        $bits = array();
        if(isset($hit['abv_min']) && isset($hit['abv_max'])){
            $bits[] = rtrim(rtrim(number_format(floatval($hit['abv_min']), 1), '0'), '.') . '–' . rtrim(rtrim(number_format(floatval($hit['abv_max']), 1), '0'), '.') . '% ABV';
        }
        if(isset($hit['beer_count']) && intval($hit['beer_count']) > 0){
            $bits[] = number_format(intval($hit['beer_count'])) . (intval($hit['beer_count']) === 1 ? ' beer' : ' beers');
        }
        $value = h(implode(' · ', $bits));
    }

    if(!empty($context) || $value !== ''){
        $html .= '<span class="sr-hit__sub">';
        $html .= '<span class="sr-hit__ctx">' . implode('<span class="sr-dot">·</span>', $context) . '</span>';
        if($value !== ''){
            $html .= '<span class="sr-hit__value">' . $value . '</span>';
        }
        $html .= '</span>';
    }

    // Prose snippet where a type can honestly promise one
    $snippet = '';
    if($hitType === 'style'){
        $snippet = srSnippet($hit, 'description');
    }elseif($hitType === 'brewer'){
        $snippet = srSnippet($hit, 'short_description');
        if($snippet === ''){ $snippet = srSnippet($hit, 'description'); }
    }
    if($snippet !== ''){
        $html .= '<span class="sr-hit__snippet">' . $snippet . '</span>';
    }

    $html .= '</a>';
    return $html;
}

// A rail group: header, values (top 8 + disclosure for the rest), counts.
// The active value renders highlighted with a remove link.
function srRenderGroup($key){
    global $facetGroups, $state;
    $group = $facetGroups[$key];
    $values = srFacetValues($key);
    $active = $state[$key];
    if(empty($values) && $active === ''){
        return '';
    }

    $html = '<div class="sr-group">';
    $html .= '<span class="cb-label cb-label--sm sr-group__h">' . h($group['label']) . '</span>';
    $html .= '<ul class="sr-facets">';

    // Active value pinned first with its remove link
    if($active !== ''){
        $html .= '<li><a class="sr-facet is-on" href="' . h(srURL(array($key=>''))) . '" title="Remove this filter">';
        $html .= '<span class="sr-facet__v">' . h(srFacetLabel($key, $active)) . '</span>';
        $html .= '<span class="sr-facet__x" aria-hidden="true">&times;</span>';
        $html .= '</a></li>';
    }

    $rendered = 0;
    $overflow = array();
    foreach($values as $value => $count){
        if((string)$value === $active){
            continue;
        }
        $item = '<li><a class="sr-facet" href="' . h(srURL(array($key=>(string)$value))) . '">';
        $item .= '<span class="sr-facet__v">' . h(srFacetLabel($key, (string)$value)) . '</span>';
        $item .= '<span class="cb-count cb-count--bare">' . number_format(intval($count)) . '</span>';
        $item .= '</a></li>';
        if($rendered < 8){
            $html .= $item;
            $rendered++;
        }else{
            $overflow[] = $item;
        }
    }
    $html .= '</ul>';

    if(!empty($overflow)){
        $html .= '<details class="sr-more"><summary>Show all (' . number_format($rendered + count($overflow) + ($active !== '' ? 1 : 0)) . ')</summary><ul class="sr-facets">' . implode('', $overflow) . '</ul></details>';
    }

    $html .= '</div>';
    return $html;
}

// A bucket group (ABV / IBU) — static ranges, no counts (filterOnly attrs)
function srRenderBuckets($key, $label, $buckets, $note = ''){
    global $state;
    $active = $state[$key];
    $html = '<div class="sr-group">';
    $html .= '<span class="cb-label cb-label--sm sr-group__h">' . h($label) . '</span>';
    $html .= '<ul class="sr-facets">';
    foreach($buckets as $bucketKey => $bucket){
        if($bucketKey === $active){
            $html .= '<li><a class="sr-facet is-on" href="' . h(srURL(array($key=>''))) . '" title="Remove this filter">';
            $html .= '<span class="sr-facet__v">' . h($bucket['label']) . '</span>';
            $html .= '<span class="sr-facet__x" aria-hidden="true">&times;</span>';
            $html .= '</a></li>';
        }else{
            $html .= '<li><a class="sr-facet" href="' . h(srURL(array($key=>$bucketKey))) . '">';
            $html .= '<span class="sr-facet__v">' . h($bucket['label']) . '</span>';
            $html .= '</a></li>';
        }
    }
    $html .= '</ul>';
    if($note !== ''){
        $html .= '<span class="sr-group__note">' . h($note) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

// ----- HTML Head -----
$pageTitle = ($q !== '') ? 'Search: ' . $q : 'Search';
$htmlHead = new htmlHead($pageTitle);
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
// Thin, derived, infinitely-parameterized — keep out of search engines
$htmlHead->noindex();
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page" style="padding-bottom:2.2rem;">
        <?php
        // Header: title + the page's own query form (GET, no JS)
        echo '<div class="sr-head">';
        echo '<h1 class="cb-title sr-title">Search</h1>';
        echo '<form class="sr-form" action="/search" method="get" role="search">';
        echo '<input class="cb-search sr-input" type="search" name="q" value="' . h($q) . '" placeholder="Search brewers, beers, places, and styles&hellip;" aria-label="Search Catalog.beer" autocomplete="off"' . ($q === '' ? ' autofocus' : '') . '>';
        if($type !== ''){
            echo '<input type="hidden" name="type" value="' . h($type) . '">';
        }
        echo '<button class="cb-btn cb-btn--primary" type="submit">Search</button>';
        echo '</form>';
        echo '</div>';

        if($searchDown){
            // Algolia unreachable — page shell still works, say so plainly
            $downAlert = new Alert();
            $downAlert->msg = 'Search is temporarily unavailable. Please try again in a moment.';
            $downAlert->type = 'warning';
            echo $downAlert->display();
        }else{
            // Type tabs — the primary navigation of the page
            echo '<nav class="sr-tabs" aria-label="Result type">';
            $allOn = ($type === '') ? ' is-on' : '';
            echo '<a class="sr-tab' . $allOn . '" href="' . h(srTabURL('')) . '">All</a>';
            foreach($typeLabels as $tabType => $tabLabel){
                $on = ($type === $tabType) ? ' is-on' : '';
                echo '<a class="sr-tab' . $on . '" href="' . h(srTabURL($tabType)) . '">' . h($tabLabel);
                echo '<span class="cb-count">' . number_format($tabCounts[$tabType] ?? 0) . '</span>';
                echo '</a>';
            }
            echo '</nav>';

            // Layout: results + (inside a tab) the refinement rail
            $hasRail = ($type !== '');
            echo '<div class="sr-layout' . ($hasRail ? ' sr-layout--rail' : '') . '">';

            // ----- Results column -----
            echo '<div class="sr-results">';

            // Result meta + active-refinement chips
            echo '<div class="sr-meta">';
            if($fallbackNotice !== ''){
                echo '<span class="sr-notice">' . $fallbackNotice . '</span>';
            }else{
                $countLabel = number_format($nbHits) . ' result' . ($nbHits === 1 ? '' : 's');
                if($q === '' && !$hasRefinements){
                    $countLabel = 'Browsing ' . number_format($nbHits) . ' record' . ($nbHits === 1 ? '' : 's');
                }
                echo '<span class="sr-count">' . $countLabel . '</span>';
            }
            if($hasRefinements){
                $clear = array('abv'=>'', 'ibu'=>'', 'verified'=>'');
                foreach(array_keys($activeGroups) as $key){ $clear[$key] = ''; }
                echo '<a class="sr-clear" href="' . h(srURL($clear)) . '">Clear filters</a>';
            }
            echo '</div>';

            // Hits
            if($nbHits > 0){
                echo '<div class="sr-list">';
                foreach($hits as $hit){
                    echo srRenderHit($hit);
                }
                echo '</div>';

                // Pager — preserves the whole query state
                if($nbPages > 1){
                    echo '<nav class="sr-pager" aria-label="Result pages">';
                    if($state['page'] > 1){
                        echo '<a class="sr-page" href="' . h(srURL(array('page'=>$state['page'] - 1))) . '" rel="prev">&larr; Prev</a>';
                    }
                    echo '<span class="sr-page__where">Page ' . number_format($state['page']) . ' of ' . number_format($nbPages) . '</span>';
                    if($state['page'] < $nbPages){
                        echo '<a class="sr-page" href="' . h(srURL(array('page'=>$state['page'] + 1))) . '" rel="next">Next &rarr;</a>';
                    }
                    echo '</nav>';
                }
            }else{
                // Empty state, with the escape hatches
                echo '<div class="sr-empty">';
                if($q !== ''){
                    echo '<p class="sr-empty__msg">No matches for &ldquo;' . h($q) . '&rdquo;' . ($hasRefinements ? ' with these filters' : '') . '.</p>';
                }else{
                    echo '<p class="sr-empty__msg">Nothing here' . ($hasRefinements ? ' with these filters' : '') . '.</p>';
                }
                if($hasRefinements){
                    $clear = array('abv'=>'', 'ibu'=>'', 'verified'=>'');
                    foreach(array_keys($activeGroups) as $key){ $clear[$key] = ''; }
                    echo '<p><a class="cb-action" href="' . h(srURL($clear)) . '">Clear the filters and search again</a></p>';
                }
                if(!empty($styleSuggestions)){
                    echo '<div class="sr-suggest">';
                    echo '<span class="cb-label cb-label--sm">Were you looking for a style?</span>';
                    foreach($styleSuggestions as $suggestion){
                        if(!isset($suggestion->id, $suggestion->name)){ continue; }
                        echo '<a class="sr-suggest__row" href="/style/' . h($suggestion->id) . '">';
                        if(isset($suggestion->srm->min) || isset($suggestion->srm->max)){
                            $sLo = isset($suggestion->srm->min) ? floatval($suggestion->srm->min) : floatval($suggestion->srm->max);
                            $sHi = isset($suggestion->srm->max) ? floatval($suggestion->srm->max) : floatval($suggestion->srm->min);
                            echo '<span class="sr-grad" style="background:' . SRM::gradient($sLo, $sHi) . ';"></span>';
                        }
                        echo '<span>' . h($suggestion->name) . '</span>';
                        echo '</a>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>'; // .sr-results

            // ----- Refinement rail (tab-scoped) -----
            if($hasRail){
                echo '<aside class="sr-rail" aria-label="Refine results">';
                foreach($facetGroups as $key => $group){
                    if(srGroupApplies($group, $type)){
                        echo srRenderGroup($key);
                    }
                }
                if($type === 'beer'){
                    echo srRenderBuckets('abv', 'ABV', $abvBuckets);
                    echo srRenderBuckets('ibu', 'IBU', $ibuBuckets, 'Only beers with a listed IBU');

                    // Verified toggle
                    echo '<div class="sr-group">';
                    echo '<span class="cb-label cb-label--sm sr-group__h">Trust</span>';
                    echo '<ul class="sr-facets">';
                    if($verifiedActive){
                        echo '<li><a class="sr-facet is-on" href="' . h(srURL(array('verified'=>''))) . '" title="Remove this filter"><span class="sr-facet__v">Catalog.beer verified</span><span class="sr-facet__x" aria-hidden="true">&times;</span></a></li>';
                    }else{
                        echo '<li><a class="sr-facet" href="' . h(srURL(array('verified'=>'1'))) . '"><span class="sr-facet__v">Catalog.beer verified</span></a></li>';
                    }
                    echo '</ul></div>';
                }
                echo '</aside>';
            }

            echo '</div>'; // .sr-layout
        }
        ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
