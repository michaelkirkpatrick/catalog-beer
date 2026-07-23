<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Beer detail — "Record / Coaster". Server-rendered from GET /beer/{id}, with
two optional enrichments that each degrade to nothing if unavailable:

  • GET /style/{style_id}  — the beer's canonical style. Supplies the SRM
    color band + glass and the *typical* ABV/IBU ranges for the style, which
    the rail draws as a band with this beer's own ABV/IBU as a marker on top.
  • GET /brewer/{id}/beer  — the brewer's catalog, filtered to this beer's
    style family (its `parent`) to build a "More <family>" list. This is the
    only list endpoint that exists; there is no cross-brewer beers-in-style
    feed, so "related" is honestly scoped to the same brewer.

Layout picks itself from the data. A beer with prose or family siblings gets
the two-column record (State A). A bare beer — just name / style / ABV — gets
the centered coaster card (State B). Design system: composes .cb-* primitives
(catalog-components.css); be-* classes are page layout only (styles-pages.css).
--- */

// ----- Beer -----
$beerID = $_GET['beerID'] ?? '';
$api = new API();
$beerResp = $api->request('GET', '/beer/' . $beerID, '');
$beerData = json_decode($beerResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(!isset($beerData->name) || isset($beerData->error)){
    // Invalid beerID or bad API response
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Text pipelines
$text1 = new Text(false, true, true);   // display names, short fields
$text2 = new Text(true, true, false);   // multi-paragraph prose (Markdown)
$text3 = new Text(false, false, true);  // ids, URLs

$loggedIn = (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID']));
$beerName = $text1->get($beerData->name);
$beerStyle = $text1->get($beerData->style);
$beerIDString = $text3->get($beerData->id);
$brewerURL = $text3->get($beerData->brewer->id);
$brewerName = $text1->get($beerData->brewer->name);
$styleURL = !empty($beerData->style_id) ? $text3->get($beerData->style_id) : '';
$hasProse = !empty($beerData->description);

// This beer's own numbers. ABV/IBU are stored NOT NULL and default to 0, so a
// stored 0 means "unknown", not a real 0% / 0 IBU — treat non-positive as absent.
$abv = (isset($beerData->abv) && is_numeric($beerData->abv) && floatval($beerData->abv) > 0) ? floatval($beerData->abv) : null;
$ibu = (isset($beerData->ibu) && is_numeric($beerData->ibu) && floatval($beerData->ibu) > 0) ? floatval($beerData->ibu) : null;
$abvLabel = ($abv !== null) ? rtrim(rtrim(number_format($abv, 1), '0'), '.') : '';

// Verification badge — Catalog.beer verification outranks brewer-provided
$verifyDot = $verifyText = '';
if(!empty($beerData->cb_verified)){
    $verifyDot = 'cb-vdot--cbv';   $verifyText = 'Verified by Catalog.beer';
}elseif(!empty($beerData->brewer_verified)){
    $verifyDot = 'cb-vdot--first'; $verifyText = 'Verified by the brewer';
}

// ----- Style specs (color + typical ranges) -----
// Fetched only when the beer resolved to a canonical style. Failures leave the
// rail / glass off; they never block the page.
$srmMin = $srmMax = null;
$styleAbv = $styleIbu = null;   // {min,max} objects for the typical-range band
if($styleURL !== ''){
    $styleResp = $api->request('GET', '/style/' . $beerData->style_id, '');
    $styleData = json_decode($styleResp);
    if(isset($styleData->specs) && !isset($styleData->error)){
        $srm = $styleData->specs->srm ?? null;
        $srmMin = (is_object($srm) && is_numeric($srm->min ?? null)) ? floatval($srm->min) : null;
        $srmMax = (is_object($srm) && is_numeric($srm->max ?? null)) ? floatval($srm->max) : null;
        $styleAbv = $styleData->specs->abv ?? null;
        $styleIbu = $styleData->specs->ibu ?? null;
    }
}
// SRM paint range — mirrors style.php: open-ended guideline colors ("40+", "<8")
// paint against the chart's 1–40 bounds rather than collapsing to a point.
$srmOpen  = ($srmMin !== null && $srmMax === null);
$srmUnder = ($srmMin === null && $srmMax !== null);
$hasSrm   = ($srmMin !== null || $srmMax !== null);
$srmPaintMin = $srmUnder ? 1 : $srmMin;
$srmPaintMax = $srmOpen ? max($srmMin, 40) : $srmMax;

// Position + width on a fixed scale, as a clamped percentage (0–100)
$pct = function($v, $lo, $hi){ return max(0, min(100, ($v - $lo) / ($hi - $lo) * 100)); };
// One "This beer" spec bar: typical-range band (optional) + this beer's marker
$specBar = function($label, $value, $valueLabel, $range, $scaleMin, $scaleMax) use ($pct){
    $band = '';
    $rMin = (is_object($range) && is_numeric($range->min ?? null)) ? floatval($range->min) : null;
    $rMax = (is_object($range) && is_numeric($range->max ?? null)) ? floatval($range->max) : null;
    if($rMin !== null || $rMax !== null){
        $lo = $pct($rMin ?? $scaleMin, $scaleMin, $scaleMax);
        $hi = $pct($rMax ?? $scaleMax, $scaleMin, $scaleMax);
        $band = '<div class="be-band" style="left:' . number_format($lo, 1) . '%;width:' . number_format(max(2, $hi - $lo), 1) . '%;"></div>';
    }
    $marker = '<div class="be-marker" style="left:' . number_format($pct($value, $scaleMin, $scaleMax), 1) . '%;"></div>';
    return '<div class="cb-spec"><div class="cb-spec__k">' . $label . '</div>'
        . '<div class="cb-spec__track">' . $band . $marker . '</div>'
        . '<div class="cb-spec__v">' . $valueLabel . '</div></div>';
};
$specBars = '';
if($abv !== null){ $specBars .= $specBar('ABV', $abv, $abvLabel . '%', $styleAbv, 0, 14); }
if($ibu !== null){ $specBars .= $specBar('IBU', $ibu, (string)intval($ibu), $styleIbu, 0, 120); }

// ----- Related: the brewer's other beers in this style family -----
// The one list endpoint that exists carries `parent` (family), `abv`, and the
// verified flags per beer — everything the list needs, in one call.
$related = array();
$familyLabel = '';
$hasVerifiedRel = false;
$relCap = 6;
$relOverflow = false;
$brewerBeerResp = $api->request('GET', '/brewer/' . $beerData->brewer->id . '/beer', '');
$brewerBeerData = json_decode($brewerBeerResp);
if(isset($brewerBeerData->data)){
    // This beer's family comes from its own row in the brewer catalog
    $family = '';
    foreach($brewerBeerData->data as $b){
        if(isset($b->id) && $b->id === $beerData->id){ $family = $b->parent ?? ''; break; }
    }
    if(!empty($family)){
        foreach($brewerBeerData->data as $b){
            if(!isset($b->id) || $b->id === $beerData->id){ continue; }
            if(($b->parent ?? '') === $family){
                $related[] = $b;
                if(!empty($b->cb_verified) || !empty($b->brewer_verified)){ $hasVerifiedRel = true; }
            }
        }
        $familyLabel = ($family === 'other') ? 'Other' : StyleList::parentName($family);
        $relOverflow = (count($related) > $relCap);
        $related = array_slice($related, 0, $relCap);
    }
}
$hasRelated = count($related) > 0;

// State A (record) when there's body content to fill it; else State B (coaster)
$stateFull = ($hasProse || $hasRelated);

// ----- Glass markup (shared by both states) -----
$glassHTML = '';
if($hasSrm){
    $glassHTML = '<div class="be-glass" style="background:' . SRM::gradient(max(1, $srmPaintMin - 1), $srmPaintMax, '180deg') . '"><div class="be-foam"></div></div>';
}
// SRM color band + legend (shared by rail and card)
$srmBand = '';
if($hasSrm){
    if($srmOpen){
        $legend = '<span>SRM ' . ($srmMin + 0) . '+</span><span></span>';
    }elseif($srmUnder){
        $legend = '<span>SRM &lt;' . ($srmMax + 0) . '</span><span></span>';
    }else{
        $legend = '<span>SRM ' . ($srmMin + 0) . '</span><span>' . ($srmMax + 0) . '</span>';
    }
    $srmBand = '<div class="cb-srm-range" style="background:' . SRM::gradient($srmPaintMin, $srmPaintMax, '90deg') . '"></div>'
        . '<div class="cb-srm-legend d-flex justify-content-between" style="margin-top:.4rem;">' . $legend . '</div>';
}

// Edit button (shared) — same control brewer.php uses
$editBtn = '';
if($loggedIn){
    $editBtn = '<a href="/beer/' . $beerIDString . '/edit" class="btn btn-outline-secondary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg> Edit beer</a>';
}

// Style line — links to the reference page when the beer has a canonical style
if($styleURL !== ''){
    $styleLine = '<a href="/style/' . $styleURL . '" class="be-lede" title="Learn about this beer style">' . $beerStyle . '</a>';
}else{
    $styleLine = '<span class="be-lede be-lede--static">' . $beerStyle . '</span>';
}

// HTML Head
$htmlHead = new htmlHead($beerData->name);
if($hasProse){
    $htmlHead->addDescription(mb_substr(strip_tags($beerData->description), 0, 160));
}
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Beer'); ?>
    <div class="cb-page" style="padding-top:1.25rem;">
        <?php
        // Flash: "just added" success
        if($loggedIn && !empty($_SESSION['add_beer_success'])){
            $alert = new Alert();
            $alert->msg = '**Success!** Thanks for adding this beer to the database. [Add another beer by ' . $brewerName . '](/beer/add/' . $brewerURL . ').';
            $alert->type = 'success';
            $alert->dismissible = true;
            echo $alert->display();
            $_SESSION['add_beer_success'] = false;
        }
        ?>
        <div class="be-topbar">
            <div class="cb-eyebrow">
                <a href="/brewer">Brewers</a> &nbsp;/&nbsp; <a href="/brewer/<?php echo $brewerURL; ?>"><?php echo $brewerName; ?></a> &nbsp;/&nbsp; <span aria-current="page"><?php echo $beerName; ?></span>
            </div>
            <?php echo $editBtn; ?>
        </div>

        <?php if($stateFull){ /* ============ STATE A: RECORD ============ */ ?>
        <header class="be-hero">
            <div>
                <h1 class="cb-title be-title"><?php echo $beerName; ?></h1>
                <?php if($verifyText !== ''){ ?>
                <span class="be-verify"><span class="cb-vdot <?php echo $verifyDot; ?>"></span><?php echo $verifyText; ?></span>
                <?php } ?>
                <?php echo $styleLine; ?>
                <div class="be-byline">Brewed by <a href="/brewer/<?php echo $brewerURL; ?>"><?php echo $brewerName; ?></a></div>
            </div>
            <?php echo $glassHTML; ?>
        </header>

        <div class="be-body">
            <main class="cb-prose be-prose">
                <?php
                if($hasProse){
                    echo $text2->get($beerData->description);
                }
                if($hasRelated){
                    echo '<h2 class="be-rel-h">More ' . $text1->get($familyLabel) . ' <span class="cb-count cb-count--bare">' . count($related) . '</span></h2>';
                    echo '<div>';
                    foreach($related as $b){
                        $rName = $text1->get($b->name);
                        $rStyle = $text1->get($b->style);
                        $rID = $text3->get($b->id);
                        $rAbv = (isset($b->abv) && is_numeric($b->abv) && floatval($b->abv) > 0) ? rtrim(rtrim(number_format(floatval($b->abv), 1), '0'), '.') . '%' : '';
                        echo '<a href="/beer/' . $rID . '" class="cb-row"><span class="be-rel-l">';
                        if($hasVerifiedRel){
                            if(!empty($b->cb_verified)){
                                echo '<span class="cb-vdot cb-vdot--cbv" title="Catalog.beer verified"></span>';
                            }elseif(!empty($b->brewer_verified)){
                                echo '<span class="cb-vdot cb-vdot--first" title="Brewer-provided"></span>';
                            }
                        }
                        echo '<span style="min-width:0;"><span class="cb-row__name">' . $rName . '</span> <span class="cb-row__meta">' . $rStyle . '</span></span>';
                        echo '</span><span class="cb-row__value">' . $rAbv . '</span></a>' . "\n";
                    }
                    echo '</div>';
                    if($relOverflow){
                        echo '<a href="/brewer/' . $brewerURL . '#beer" class="cb-action">See more from ' . $brewerName . ' &rarr;</a>';
                    }
                    if($hasVerifiedRel){
                        echo '<div class="cb-legend"><span><span class="cb-vdot cb-vdot--first"></span>Brewer-provided</span><span><span class="cb-vdot cb-vdot--cbv"></span>Catalog.beer verified</span><span class="cb-legend__none">no mark &#8212; unverified</span></div>';
                    }
                }
                ?>
            </main>

            <aside class="cb-rail be-rail">
                <?php if($hasSrm){ ?>
                <div>
                    <span class="cb-label">Typical Color</span>
                    <?php echo $srmBand; ?>
                </div>
                <?php } if($specBars !== ''){ ?>
                <div>
                    <span class="cb-label">This beer</span>
                    <div class="cb-specs"><?php echo $specBars; ?></div>
                    <div class="be-hint"><?php echo ($styleAbv || $styleIbu) ? 'Band = typical range for the style &middot; marker = this beer' : 'Marker = this beer'; ?></div>
                </div>
                <?php } ?>
                <div>
                    <span class="cb-label">Details</span>
                    <div class="cb-fact"><span class="cb-fact__k">Style</span><span class="cb-fact__v cb-fact__v--sm"><?php
                        echo ($styleURL !== '') ? '<a href="/style/' . $styleURL . '">' . $beerStyle . '</a>' : $beerStyle;
                    ?></span></div>
                    <div class="cb-fact"><span class="cb-fact__k">Brewer</span><span class="cb-fact__v cb-fact__v--sm"><a href="/brewer/<?php echo $brewerURL; ?>"><?php echo $brewerName; ?></a></span></div>
                </div>
            </aside>
        </div>

        <?php }else{ /* ============ STATE B: COASTER ============ */ ?>
        <div class="be-card">
            <?php echo $glassHTML; ?>
            <h1 class="cb-title be-title"><?php echo $beerName; ?></h1>
            <?php echo $styleLine; ?>
            <div class="be-byline">Brewed by <a href="/brewer/<?php echo $brewerURL; ?>"><?php echo $brewerName; ?></a></div>
            <?php if($abv !== null){ ?>
            <hr class="be-card-rule">
            <div style="display:flex;flex-direction:column;align-items:center;gap:.25rem;">
                <span class="cb-label">ABV</span>
                <span class="be-card-abv"><?php echo $abvLabel; ?>%</span>
            </div>
            <?php } if($hasSrm){ ?>
            <div class="be-card-srm">
                <?php echo $srmBand; ?>
                <div class="be-hint" style="margin-top:.5rem;">Typical color for this style</div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
