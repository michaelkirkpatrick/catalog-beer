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

// Text pipelines
$text1 = new Text(false, true, true);   // display names, short fields
$text2 = new Text(true, true, false);   // multi-paragraph prose (Markdown)
$text3 = new Text(false, false, true);  // ids, URLs

$styleName = $text1->get($styleData->name);
$parentName = !empty($styleData->parent_name) ? $text1->get($styleData->parent_name) : '';
$parentSlug = !empty($styleData->parent) ? $text3->get($styleData->parent) : '';
// Only beer has a fermentation class. Cider / mead / perry have none, and their
// family already names the beverage type, so there's no tier to show.
$className = !empty($styleData->class) ? $text1->get(ucfirst($styleData->class)) : '';

// SRM color device
$srm = $styleData->specs->srm ?? null;
$srmMin = (is_object($srm) && is_numeric($srm->min ?? null)) ? floatval($srm->min) : null;
$srmMax = (is_object($srm) && is_numeric($srm->max ?? null)) ? floatval($srm->max) : null;
if($srmMin === null){ $srmMin = $srmMax; }
if($srmMax === null){ $srmMax = $srmMin; }
$hasSrm = ($srmMin !== null);
$srmMid = $hasSrm ? ($srmMin + $srmMax) / 2 : null;

// Lede: the first sentence of the description
$lede = '';
if(!empty($styleData->description)){
    $parts = preg_split('/(?<=\.)\s/', trim($styleData->description), 2);
    $lede = $text1->get($parts[0]);
}

// Vital-stat range bar (fixed scales so ranges are comparable across styles);
// '' when no data
$specBar = function($label, $spec, $decimals, $suffix, $scaleMin, $scaleMax){
    $min = (is_object($spec) && is_numeric($spec->min ?? null)) ? floatval($spec->min) : null;
    $max = (is_object($spec) && is_numeric($spec->max ?? null)) ? floatval($spec->max) : null;
    if($min === null){ $min = $max; }
    if($max === null){ $max = $min; }
    if($min === null){
        return '';
    }
    $lo = max(0, min(1, ($min - $scaleMin) / ($scaleMax - $scaleMin)));
    $hi = max(0, min(1, ($max - $scaleMin) / ($scaleMax - $scaleMin)));
    $left = number_format($lo * 100, 1);
    $width = number_format(max(3, ($hi - $lo) * 100), 1);
    $fmt = function($v) use ($decimals, $suffix){
        $s = ($decimals !== null) ? number_format($v, $decimals) : rtrim(rtrim(number_format($v, 1), '0'), '.');
        return $s . $suffix;
    };
    $value = ($min === $max) ? $fmt($min) : $fmt($min) . '&ndash;' . $fmt($max);
    return '<div class="sp-spec"><div class="sp-spec-k">' . $label . '</div>'
        . '<div class="sp-spec-track"><div class="sp-spec-fill" style="left:' . $left . '%;width:' . $width . '%;background:var(--style-srm,var(--cb-amber));"></div></div>'
        . '<div class="sp-spec-v">' . $value . '</div></div>';
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
        $aafm[$label] = $text1->get($styleData->$field);
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
    <div class="sp-page" style="padding-top:1.25rem;<?php if($hasSrm){ echo '--style-srm:' . SRM::hex($srmMid) . ';'; } ?>">
        <div class="sp-eyebrow">
            <a href="/style">Styles</a><?php
            // Styles / Ale / India Pale Ale / American-Style India Pale Ale
            // Styles / Cider / Applewine        (no class tier for non-beer)
            if($className !== ''){ echo ' &nbsp;/&nbsp; <span>' . $className . '</span>'; }
            if($parentName !== ''){
                if($parentSlug !== ''){
                    echo ' &nbsp;/&nbsp; <a href="/style/family/' . $parentSlug . '">' . $parentName . '</a>';
                }else{
                    echo ' &nbsp;/&nbsp; <span>' . $parentName . '</span>';
                }
            }
            echo ' &nbsp;/&nbsp; <span aria-current="page">' . $styleName . '</span>';
            ?>
        </div>

        <header class="da-hero">
            <h1 class="sp-title da-title"><?php echo $styleName; ?></h1>
            <?php
            if($lede !== ''){
                echo '<p class="sp-lede da-sub">' . $lede . '</p>';
            }
            if(!empty($styleData->aliases)){
                $aliases = array();
                foreach($styleData->aliases as $alias){
                    $aliases[] = $text1->get($alias);
                }
                echo '<p class="da-aka">Also known as ' . implode(', ', $aliases) . '</p>';
            }
            if($hasSrm){
                echo '<div class="da-glass sp-glass" style="background:' . SRM::gradient(max(1, $srmMin - 1), $srmMax, '180deg') . '"><div class="sp-foam"></div></div>';
            }
            ?>
        </header>

        <div class="da-body<?php if(!$hasRail){ echo ' da-body-solo'; } ?>">
            <main class="sp-prose">
                <?php
                // Description
                if(!empty($styleData->description)){
                    echo $text2->get($styleData->description);
                }

                // In the glass — AAFM
                if($aafm){
                    echo '<h2 class="da-prose-h">In the glass</h2>';
                    echo '<div class="sp-aafm">';
                    foreach($aafm as $label => $value){
                        echo '<div><div class="sp-aafm-k">' . $label . '</div><div class="sp-aafm-v">' . $value . '</div></div>';
                    }
                    echo '</div>';
                }

                // Origin — history
                if(!empty($styleData->history)){
                    echo '<h2 class="da-prose-h">Origin</h2>';
                    echo $text2->get($styleData->history);
                }

                // Notes
                if(!empty($styleData->notes)){
                    echo '<h2 class="da-prose-h">Notes</h2>';
                    echo $text2->get($styleData->notes);
                }

                // Defining examples (curated classics from the style library —
                // a live "beers in this style" list may join them later)
                if(!empty($styleData->commercial_examples)){
                    echo '<h2 class="da-prose-h">Defining examples</h2>';
                    $examples = array();
                    foreach($styleData->commercial_examples as $example){
                        $examples[] = $text1->get($example);
                    }
                    echo '<p class="ix-chip-list">' . implode('<span class="ix-chip-sep">&middot;</span>', $examples) . '</p>';
                }

                // Sources
                $sourceRows = array();
                if(!empty($styleData->sources)){
                    $src = $styleData->sources;
                    if(!empty($src->brewers_association->name)){
                        $sourceRows[] = '<span class="sp-source-tag">BA 2026</span>' . $text1->get($src->brewers_association->name);
                    }
                    if(!empty($src->bjcp->name)){
                        $bjcpTag = 'BJCP' . (!empty($src->bjcp->year) ? ' ' . intval($src->bjcp->year) : '') . (!empty($src->bjcp->code) ? ' &middot; ' . $text1->get($src->bjcp->code) : '');
                        $sourceRows[] = '<span class="sp-source-tag">' . $bjcpTag . '</span>' . $text1->get($src->bjcp->name);
                    }
                    if(!empty($src->naba_2024->name)){
                        $sourceRows[] = '<span class="sp-source-tag">NABA 2024</span>' . $text1->get($src->naba_2024->name);
                    }
                    if(!empty($src->history_sources)){
                        foreach($src->history_sources as $hs){
                            if(empty($hs->citation)){
                                continue;
                            }
                            $citation = $text1->get($hs->citation);
                            if(!empty($hs->url)){
                                $url = $text3->get($hs->url);
                                $citation .= ' <a href="' . $url . '" target="_blank" rel="noopener">&#8599;</a>';
                            }
                            $sourceRows[] = $citation;
                        }
                    }
                }
                if($sourceRows){
                    echo '<div class="sp-sources" style="margin-top:2rem;">';
                    echo '<div class="sp-section-h">Sources</div>';
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
                    <div class="sp-section-h" style="border:0;padding:0;margin-bottom:.7rem;">Color &middot; SRM</div>
                    <div class="sp-srm-range" style="background:<?php echo SRM::gradient($srmMin, $srmMax, '90deg'); ?>"></div>
                    <div class="sp-srm-legend d-flex justify-content-between" style="margin-top:.4rem;"><span>SRM <?php echo $srmMin + 0; ?></span><span><?php echo $srmMax + 0; ?></span></div>
                </div>
                <?php } if($specBars !== ''){ ?>
                <div>
                    <div class="sp-section-h" style="border:0;padding:0;margin-bottom:.7rem;">Vital stats</div>
                    <div class="sp-specs"><?php echo $specBars; ?></div>
                </div>
                <?php } ?>
            </aside>
            <?php } ?>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
