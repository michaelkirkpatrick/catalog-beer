<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Style family page — one family (parent style) with its curated description
and every style in it, each as a row with an SRM color chip linking to that
style's Tasting Sheet. Catch-alls are listed last with a tag instead of a
chip (they have no fixed color). Fed entirely from the session-cached
StyleList vocabulary — no per-page API call.
--- */

// Vocabulary
$parents = StyleList::parents();
$styles  = StyleList::styles();
if(empty($parents) || empty($styles)){
    serve503();
}

// Find the requested family
$parentSlug = $_GET['parentSlug'] ?? '';
$family = null;
foreach($parents as $p){
    if($p['slug'] === $parentSlug){
        $family = $p;
        break;
    }
}
if($family === null){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Its styles, A→Z, regular styles first, catch-alls last
$kids = array();
$catchAlls = array();
foreach($styles as $s){
    if($s['parent'] !== $family['slug']){
        continue;
    }
    if($s['ca']){
        $catchAlls[] = $s;
    }else{
        $kids[] = $s;
    }
}
usort($kids, function($a, $b){ return strcasecmp($a['name'], $b['name']); });
usort($catchAlls, function($a, $b){ return strcasecmp($a['name'], $b['name']); });

// Required Classes
$text = new Text(false, true, true);

$familyName = $text->get($family['name']);
$crumbContext = !empty($family['cls']) ? ucfirst($family['cls']) : ucfirst($family['bev']);
$crumbContext = $text->get($crumbContext);

// Midpoint of a style's SRM range, or null when there's no color data
$midSRM = function($s){
    if(!isset($s['srm']) || !is_array($s['srm'])){
        return null;
    }
    $min = $s['srm']['min'] ?? null;
    $max = $s['srm']['max'] ?? null;
    if(is_numeric($min) && is_numeric($max)){
        return (floatval($min) + floatval($max)) / 2;
    }elseif(is_numeric($min)){
        return floatval($min);
    }elseif(is_numeric($max)){
        return floatval($max);
    }
    return null;
};

// SRM range label ("SRM 4–12"), or '' when there's no color data
$srmLabel = function($s){
    if(!isset($s['srm']) || !is_array($s['srm'])){
        return '';
    }
    $min = $s['srm']['min'] ?? null;
    $max = $s['srm']['max'] ?? null;
    if(!is_numeric($min) && !is_numeric($max)){
        return '';
    }
    if(!is_numeric($min)){ $min = $max; }
    if(!is_numeric($max)){ $max = $min; }
    return ($min == $max) ? 'SRM ' . ($min + 0) : 'SRM ' . ($min + 0) . '&ndash;' . ($max + 0);
};

// One row per style: chip + name (+ SRM range), linking to the Tasting Sheet
$styleRow = function($s, $isCatchAll) use ($text, $midSRM, $srmLabel){
    $mid = $midSRM($s);
    $html = '<a class="fam-row" href="/style/' . rawurlencode($s['id']) . '">';
    if($mid !== null){
        $html .= '<span class="fam-chip" style="background:' . SRM::hex($mid) . '"></span>';
    }else{
        $html .= '<span class="fam-chip fam-chip-none"></span>';
    }
    $html .= '<span class="fam-name">' . $text->get($s['name']);
    if($isCatchAll){
        $html .= ' <span class="sp-source-tag">catch-all</span>';
    }
    $html .= '</span>';
    $srm = $srmLabel($s);
    if($srm !== ''){
        $html .= '<span class="fam-srm">' . $srm . '</span>';
    }
    $html .= '</a>';
    return $html;
};

// HTML Head
$htmlHead = new htmlHead($family['name'] . ' Styles');
if(!empty($family['desc'])){
    $htmlHead->addDescription(mb_substr($family['desc'], 0, 160));
}
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Styles'); ?>
    <div class="sp-page" style="padding-top:1.25rem;">
        <div class="sp-eyebrow"><a href="/style">Styles</a> &nbsp;/&nbsp; <span><?php echo $crumbContext; ?></span></div>

        <header class="fam-hero">
            <h1 class="sp-title fam-title"><?php echo $familyName; ?> <span class="sp-count"><?php echo count($kids) + count($catchAlls); ?> styles</span></h1>
            <?php
            if(!empty($family['desc'])){
                echo '<p class="sp-lede fam-lede">' . $text->get($family['desc']) . '</p>';
            }
            if(!empty($family['al'])){
                $aliases = array();
                foreach($family['al'] as $alias){
                    $aliases[] = $text->get($alias);
                }
                echo '<p class="da-aka">Also known as ' . implode(', ', $aliases) . '</p>';
            }
            ?>
        </header>

        <div class="fam-styles">
            <?php
            foreach($kids as $s){
                echo $styleRow($s, false);
            }
            foreach($catchAlls as $s){
                echo $styleRow($s, true);
            }
            ?>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
