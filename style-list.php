<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Styles index — "Color Cards". One card per family (parent style), grouped by
fermentation class (Ale / Lager), then the classless beer families, then the
non-beer beverage types (cider / perry / mead) as lighter, swatch-free lists.
Each card's swatch row is built from the real per-style SRM ranges served by
GET /style, so the "spread of color you'll find inside" caption is true.
Rendered server-side: this is a public reference page (SEO).
--- */

// Style vocabulary (session-cached; fetched from the API once per session)
$classes = StyleList::classes();
$parents = StyleList::parents();
$styles  = StyleList::styles();
if(empty($classes) || empty($parents) || empty($styles)){
    serve503();
}

// Required Classes
$text = new Text(false, true, true);

// --- Build the Class → Family → Style tree ---

// Bucket non-catch-all styles by family, A→Z within each. Catch-alls
// (Specialty Beer, Experimental IPA, …) are competition buckets, not styles a
// reader browses for — hidden from cards and counts until they have pages.
$byParent = array();
foreach($styles as $s){
    if($s['ca']){
        continue;
    }
    $byParent[$s['parent']][] = $s;
}
foreach($byParent as $slug => $kids){
    usort($kids, function($a, $b){ return strcasecmp($a['name'], $b['name']); });
    $byParent[$slug] = $kids;
}

// Families in curated order
usort($parents, function($a, $b){
    return (($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
});

// Sections: one per beer class, then classless beer families, then one per
// non-beer beverage type (its single family renders as a chip list).
$sections = array();
foreach($classes as $c){
    $group = array();
    foreach($parents as $p){
        if($p['cls'] === $c['slug'] && !empty($byParent[$p['slug']])){
            $group[] = $p;
        }
    }
    if($group){
        $sections[] = array('name' => $c['name'], 'families' => $group, 'cards' => true);
    }
}
$otherBeer = array();
foreach($parents as $p){
    if(empty($p['cls']) && $p['bev'] === 'beer' && !empty($byParent[$p['slug']])){
        $otherBeer[] = $p;
    }
}
if($otherBeer){
    $sections[] = array('name' => 'Other Beers', 'families' => $otherBeer, 'cards' => true);
}
foreach($parents as $p){
    // Cider / Perry / Mead — no SRM data (they aren't measured in SRM), so no
    // color cards; each renders as its own section with a simple style list.
    if($p['bev'] !== 'beer' && !empty($byParent[$p['slug']])){
        $sections[] = array('name' => $p['name'], 'families' => array($p), 'cards' => false);
    }
}

// Counts for the header — computed, never hardcoded
$styleCount = 0;
foreach($byParent as $kids){
    $styleCount += count($kids);
}
$familyCount = count($parents);

// --- SRM helpers ---

// Midpoint of a style's SRM range, or null when there's no color data
function styleMidSRM($s){
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
}

// Swatch chips for a family: each style's mid SRM, light→dark, at most 8
// (sampled evenly across the sorted spread when the family is larger)
function familySwatchMids($kids){
    $mids = array();
    foreach($kids as $s){
        $mid = styleMidSRM($s);
        if($mid !== null){
            $mids[] = $mid;
        }
    }
    sort($mids);
    $n = count($mids);
    if($n <= 8){
        return $mids;
    }
    $out = array();
    for($i = 0; $i < 8; $i++){
        $out[] = $mids[intval(round($i * ($n - 1) / 7))];
    }
    return $out;
}

// HTML Head
$htmlHead = new htmlHead('Beer Styles');
$htmlHead->addDescription('Browse every beer style by family — real color ranges, ' . $styleCount . ' styles across ' . $familyCount . ' families of beer, cider, mead, and perry.');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Styles'); ?>
    <div class="sp-page">
        <header class="ix-head">
            <h1 class="ix-h1">Beer Styles</h1>
            <p class="ix-sub">Browse by family &mdash; each swatch row is the spread of color you&#8217;ll find inside. <?php echo $styleCount; ?> styles across <?php echo $familyCount; ?> families, sorted by fermentation class.</p>
        </header>
        <?php
        foreach($sections as $section){
            echo '<section class="ix-class">';
            if($section['cards']){
                echo '<h2 class="ix-class-h sp-class-h">' . $text->get($section['name']) . ' <span class="sp-count">' . count($section['families']) . ' families</span></h2>';
                echo '<div class="ix-card-grid">';
                foreach($section['families'] as $p){
                    $kids = $byParent[$p['slug']];
                    $mids = familySwatchMids($kids);

                    // Card accent = the family's median style color
                    $cardStyle = '';
                    if($mids){
                        $accent = SRM::hex($mids[intval(floor((count($mids) - 1) / 2))]);
                        $cardStyle = ' style="--pc:' . $accent . '"';
                    }

                    // Cards become links to family pages when those pages exist
                    echo '<div class="ix-card"' . $cardStyle . '>';
                    echo '<div class="ix-card-top"><span class="ix-card-name">' . $text->get($p['name']) . '</span><span class="sp-count">' . count($kids) . '</span></div>';
                    if($mids){
                        echo '<div class="ix-sw-row">';
                        foreach($mids as $mid){
                            echo '<span class="ix-sw" style="background:' . SRM::hex($mid) . '"></span>';
                        }
                        echo '</div>';
                    }
                    $preview = array();
                    foreach(array_slice($kids, 0, 3) as $s){
                        $preview[] = '<a href="/style/' . rawurlencode($s['id']) . '">' . $text->get($s['name']) . '</a>';
                    }
                    $more = (count($kids) > 3) ? ' &middot; +' . (count($kids) - 3) . ' more' : '';
                    echo '<div class="ix-card-styles">' . implode(' &middot; ', $preview) . $more . '</div>';
                    echo '</div>';  // Close ix-card
                }
                echo '</div>';  // Close ix-card-grid
            }else{
                // Non-beer beverage type: heading + plain list of its styles
                $p = $section['families'][0];
                $kids = $byParent[$p['slug']];
                echo '<h2 class="ix-class-h sp-class-h">' . $text->get($section['name']) . ' <span class="sp-count">' . count($kids) . ' styles</span></h2>';
                $names = array();
                foreach($kids as $s){
                    $names[] = '<a class="sp-style-link" href="/style/' . rawurlencode($s['id']) . '">' . $text->get($s['name']) . '</a>';
                }
                echo '<p class="ix-chip-list">' . implode('<span class="ix-chip-sep">&middot;</span>', $names) . '</p>';
            }
            echo '</section>';
        }
        ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
