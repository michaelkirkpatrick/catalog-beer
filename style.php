<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Style detail — "Tasting Sheet". Server-rendered from GET /style/{slug}:
hero with SRM-filled glass, description prose, In-the-glass tasting notes
(AAFM), Origin history, notes, defining commercial examples, and source
citations, with a sticky rail of the color range and vital-stat bars.
Every section is optional — catch-alls have no AAFM/specs, and a pre-seed
API returns null content — so the page degrades to whatever data exists.
--- */

// Get Style Information
$styleID = $_GET['styleID'] ?? '';
$api = new API();
$styleResp = $api->request('GET', '/style/' . $styleID, '');
$styleData = json_decode($styleResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(!isset($styleData->name) || isset($styleData->error)){
    // Invalid styleID or bad API response
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Raw values; h() goes at each output. $styleName alone feeds the eyebrow
// crumb and the <h1>, and $parentName feeds two branches of the crumb.
$styleName = $styleData->name;
$parentName = !empty($styleData->parent_name) ? $styleData->parent_name : '';
$parentSlug = !empty($styleData->parent) ? $styleData->parent : '';
// Only beer has a fermentation class. Cider / mead / perry have none, and their
// family already names the beverage type, so there's no tier to show.
$className = !empty($styleData->class) ? ucfirst($styleData->class) : '';

// SRM color device. The guidelines publish some colors open-ended ("40+" —
// at least 40, no upper bound), which the API returns as max: null. Don't
// collapse that to a closed range: "40+" is not "exactly 40".
$srm = $styleData->specs->srm ?? null;
$srmMin = (is_object($srm) && is_numeric($srm->min ?? null)) ? floatval($srm->min) : null;
$srmMax = (is_object($srm) && is_numeric($srm->max ?? null)) ? floatval($srm->max) : null;
$srmOpen = ($srmMin !== null && $srmMax === null);   // "40+"  — floor, no ceiling
$srmUnder = ($srmMin === null && $srmMax !== null);  // "<8"   — ceiling, no floor
$hasSrm = ($srmMin !== null || $srmMax !== null);
// The SRM color chart tops out at 40, so an open range paints to 40
$srmPaintMin = $srmUnder ? 1 : $srmMin;
$srmPaintMax = $srmOpen ? max($srmMin, 40) : $srmMax;
if($srmOpen){
    $srmMid = $srmMin;
}elseif($srmUnder){
    $srmMid = $srmMax / 2;
}elseif($hasSrm){
    $srmMid = ($srmMin + $srmMax) / 2;
}else{
    $srmMid = null;
}

// Lede: the first sentence of the description
$lede = '';
if(!empty($styleData->description)){
    $parts = preg_split('/(?<=\.)\s/', trim($styleData->description), 2);
    $lede = $parts[0];
}

// Vital-stat range bar (fixed scales so ranges are comparable across styles);
// '' when no data
$specBar = function($label, $spec, $decimals, $suffix, $scaleMin, $scaleMax){
    $min = (is_object($spec) && is_numeric($spec->min ?? null)) ? floatval($spec->min) : null;
    $max = (is_object($spec) && is_numeric($spec->max ?? null)) ? floatval($spec->max) : null;
    // Open-ended guideline values: "8%+" is a floor with no ceiling;
    // "<0.5%" is a ceiling with no floor
    $open = ($min !== null && $max === null);
    $under = ($min === null && $max !== null);
    if($min === null && $max === null){
        return '';
    }
    $lo = $under ? 0 : max(0, min(1, ($min - $scaleMin) / ($scaleMax - $scaleMin)));
    $hi = $open ? 1 : max(0, min(1, ($max - $scaleMin) / ($scaleMax - $scaleMin)));
    $left = number_format($lo * 100, 1);
    $width = number_format(max(3, ($hi - $lo) * 100), 1);
    $fmt = function($v) use ($decimals, $suffix){
        $s = ($decimals !== null) ? number_format($v, $decimals) : rtrim(rtrim(number_format($v, 1), '0'), '.');
        return $s . $suffix;
    };
    if($open){
        $value = $fmt($min) . '+';
    }elseif($under){
        $value = '&lt;' . $fmt($max);
    }elseif($min === $max){
        $value = $fmt($min);
    }else{
        $value = $fmt($min) . '&ndash;' . $fmt($max);
    }
    return '<div class="cb-spec"><div class="cb-spec__k">' . $label . '</div>'
        . '<div class="cb-spec__track"><div class="cb-spec__fill" style="left:' . $left . '%;width:' . $width . '%;background:var(--style-srm,var(--cb-amber));"></div></div>'
        . '<div class="cb-spec__v">' . $value . '</div></div>';
};
$specBars = '';
$specBars .= $specBar('ABV', $styleData->specs->abv ?? null, null, '%', 0, 14);
$specBars .= $specBar('IBU', $styleData->specs->ibu ?? null, 0, '', 0, 120);
$specBars .= $specBar('OG', $styleData->specs->og ?? null, 3, '', 1.0, 1.12);
$specBars .= $specBar('FG', $styleData->specs->fg ?? null, 3, '', 0.995, 1.03);
$hasRail = ($hasSrm || $specBars !== '');

// AAFM tasting notes
$aafm = array();
foreach(array('Appearance' => 'appearance', 'Aroma' => 'aroma', 'Flavor' => 'flavor', 'Mouthfeel' => 'mouthfeel') as $label => $field){
    if(!empty($styleData->$field)){
        $aafm[$label] = $styleData->$field;
    }
}

// HTML Head
$htmlHead = new htmlHead($styleData->name);
if(!empty($styleData->description)){
    $metaParts = preg_split('/(?<=\.)\s/', trim($styleData->description), 2);
    $htmlHead->addDescription(mb_substr($metaParts[0], 0, 160));
}
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Styles'); ?>
    <div class="cb-page" style="padding-top:1.25rem;<?php if($hasSrm){ echo '--style-srm:' . SRM::hex($srmMid) . ';'; } ?>">
        <div class="cb-eyebrow">
            <a href="/style">Styles</a><?php
            // Styles / Ale / India Pale Ale / American-Style India Pale Ale
            // Styles / Cider / Applewine        (no class tier for non-beer)
            if($className !== ''){ echo ' &nbsp;/&nbsp; <span>' . h($className) . '</span>'; }
            if($parentName !== ''){
                if($parentSlug !== ''){
                    echo ' &nbsp;/&nbsp; <a href="/style/family/' . h(rawurlencode($parentSlug)) . '">' . h($parentName) . '</a>';
                }else{
                    echo ' &nbsp;/&nbsp; <span>' . h($parentName) . '</span>';
                }
            }
            echo ' &nbsp;/&nbsp; <span aria-current="page">' . h($styleName) . '</span>';
            ?>
        </div>

        <header class="da-hero">
            <h1 class="cb-title da-title"><?php echo h($styleName); ?></h1>
            <?php
            if($lede !== ''){
                echo '<p class="cb-lede da-sub">' . h($lede) . '</p>';
            }
            if(!empty($styleData->aliases)){
                $aliases = array();
                foreach($styleData->aliases as $alias){
                    $aliases[] = $alias;
                }
                echo '<p class="da-aka">Also known as ' . implode(', ', array_map('h', $aliases)) . '</p>';
            }
            if($hasSrm){
                echo '<div class="da-glass sp-glass" style="background:' . SRM::gradient(max(1, $srmPaintMin - 1), $srmPaintMax, '180deg') . '"><div class="sp-foam"></div></div>';
            }
            ?>
        </header>

        <div class="da-body<?php if(!$hasRail){ echo ' da-body-solo'; } ?>">
            <main class="cb-prose">
                <?php
                // Description
                if(!empty($styleData->description)){
                    // Inner wrapper, not <main class="cb-prose"> itself: that is a
                    // container for the headings and blocks below, and pre-line on
                    // it would make the source whitespace between them visible.
                    echo '<div class="cb-prose__text">' . h($styleData->description) . '</div>';
                }

                // In the glass — AAFM
                if($aafm){
                    echo '<h2 class="da-prose-h">In the glass</h2>';
                    echo '<div class="sp-aafm">';
                    foreach($aafm as $label => $value){
                        echo '<div><div class="sp-aafm-k">' . $label . '</div><div class="sp-aafm-v">' . h($value) . '</div></div>';
                    }
                    echo '</div>';
                }

                // Origin — history
                if(!empty($styleData->history)){
                    echo '<h2 class="da-prose-h">Origin</h2>';
                    echo '<div class="cb-prose__text">' . h($styleData->history) . '</div>';
                }

                // Notes
                if(!empty($styleData->notes)){
                    echo '<h2 class="da-prose-h">Notes</h2>';
                    echo '<div class="cb-prose__text">' . h($styleData->notes) . '</div>';
                }

                // Defining examples (curated classics from the style library —
                // a live "beers in this style" list may join them later)
                if(!empty($styleData->commercial_examples)){
                    echo '<h2 class="da-prose-h">Defining examples</h2>';
                    $examples = array();
                    foreach($styleData->commercial_examples as $example){
                        $examples[] = $example;
                    }
                    echo '<p class="ix-chip-list">' . implode('<span class="ix-chip-sep">&middot;</span>', array_map('h', $examples)) . '</p>';
                }

                // Sources
                $sourceRows = array();
                if(!empty($styleData->sources)){
                    $src = $styleData->sources;
                    if(!empty($src->brewers_association->name)){
                        $sourceRows[] = '<span class="cb-tag">BA 2026</span>' . h($src->brewers_association->name);
                    }
                    if(!empty($src->bjcp->name)){
                        $bjcpTag = 'BJCP' . (!empty($src->bjcp->year) ? ' ' . intval($src->bjcp->year) : '') . (!empty($src->bjcp->code) ? ' &middot; ' . h($src->bjcp->code) : '');
                        $sourceRows[] = '<span class="cb-tag">' . $bjcpTag . '</span>' . h($src->bjcp->name);
                    }
                    if(!empty($src->naba_2024->name)){
                        $sourceRows[] = '<span class="cb-tag">NABA 2024</span>' . h($src->naba_2024->name);
                    }
                    if(!empty($src->history_sources)){
                        foreach($src->history_sources as $hs){
                            if(empty($hs->citation)){
                                continue;
                            }
                            // $sourceRows holds HTML by construction -- the tags and
                            // the citation link are assembled here -- so each value is
                            // escaped as it goes in, and the rows echo as-is below.
                            $citation = h($hs->citation);
                            if(!empty($hs->url)){
                                $url = h($hs->url);
                                $citation .= ' <a href="' . $url . '" target="_blank" rel="noopener">&#8599;</a>';
                            }
                            $sourceRows[] = $citation;
                        }
                    }
                }
                if($sourceRows){
                    echo '<div class="sp-sources" style="margin-top:2rem;">';
                    echo '<div class="cb-label cb-label--rule">Sources</div>';
                    foreach($sourceRows as $row){
                        echo '<div style="margin-bottom:.3rem;">' . $row . '</div>';
                    }
                    echo '</div>';
                }
                ?>
            </main>
            <?php if($hasRail){ ?>
            <aside class="da-rail">
                <?php if($hasSrm){ ?>
                <div>
                    <div class="cb-label">Color &middot; SRM</div>
                    <div class="cb-srm-range" style="background:<?php echo SRM::gradient($srmPaintMin, $srmPaintMax, '90deg'); ?>"></div>
                    <div class="cb-srm-legend d-flex justify-content-between" style="margin-top:.4rem;">
                        <?php
                        if($srmOpen){
                            echo '<span>SRM ' . ($srmMin + 0) . '+</span><span></span>';
                        }elseif($srmUnder){
                            echo '<span>SRM &lt;' . ($srmMax + 0) . '</span><span></span>';
                        }else{
                            echo '<span>SRM ' . ($srmMin + 0) . '</span><span>' . ($srmMax + 0) . '</span>';
                        }
                        ?>
                    </div>
                </div>
                <?php } if($specBars !== ''){ ?>
                <div>
                    <div class="cb-label">Vital stats</div>
                    <div class="cb-specs"><?php echo $specBars; ?></div>
                </div>
                <?php } ?>
            </aside>
            <?php } ?>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
