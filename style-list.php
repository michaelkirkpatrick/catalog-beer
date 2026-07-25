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

// Bucket every style by family, A→Z within each, catch-alls last. Catch-alls
// (Specialty Beer, Experimental IPA, …) are broad competition buckets rather
// than styles a reader browses for, but each one has a real page, so they're
// listed with a tag and counted — the same treatment the family pages give
// them. They stay out of the swatch rows (see familySwatchMids).
$byParent = array();
foreach($styles as $s){
    $byParent[$s['parent']][] = $s;
}
foreach($byParent as $slug => $kids){
    usort($kids, function($a, $b){
        if($a['ca'] !== $b['ca']){
            return $a['ca'] ? 1 : -1;
        }
        return strcasecmp($a['name'], $b['name']);
    });
    $byParent[$slug] = $kids;
}

// Families in curated order
usort($parents, function($a, $b){
    return (($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
});

// Sections: one per beer class, then classless beer families. The non-beer
// beverage types are kept in their own list so they can render below the beer
// sections, under their own heading — this is a beer catalog first, and the
// header count says so.
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
$otherSections = array();
foreach($parents as $p){
    // Cider / Perry / Mead — no SRM data (they aren't measured in SRM), so no
    // color cards; each renders as its own section with a simple style list.
    if($p['bev'] !== 'beer' && !empty($byParent[$p['slug']])){
        $otherSections[] = array('name' => $p['name'], 'families' => array($p), 'cards' => false);
    }
}

// Counts — computed, never hardcoded, and split by beverage so the headline
// counts beer and the closing section counts everything else. Every style in
// $byParent is listed on the page, catch-alls included, so each number here is
// one a reader can verify by counting.
$beerStyleCount = 0;
$beerFamilyCount = 0;
$otherStyleCount = 0;
$otherNames = array();
foreach($parents as $p){
    if(empty($byParent[$p['slug']])){
        continue;
    }
    if($p['bev'] === 'beer'){
        $beerStyleCount += count($byParent[$p['slug']]);
        $beerFamilyCount++;
    }else{
        $otherStyleCount += count($byParent[$p['slug']]);
        $otherNames[] = mb_strtolower($p['name']);
    }
}

// "cider, perry, and mead" — the beverage list read back in prose
function proseList($items){
    $n = count($items);
    if($n === 0){
        return '';
    }elseif($n === 1){
        return $items[0];
    }elseif($n === 2){
        return $items[0] . ' and ' . $items[1];
    }
    return implode(', ', array_slice($items, 0, -1)) . ', and ' . $items[$n - 1];
}

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
// (sampled evenly across the sorted spread when the family is larger).
// Catch-alls are skipped — their guideline color ranges are deliberately wide
// (Experimental IPA is SRM 3–40), so a chip for one paints a color no actual
// style in the family has, and the "spread you'll find inside" caption stops
// being true.
function familySwatchMids($kids){
    $mids = array();
    foreach($kids as $s){
        if($s['ca']){
            continue;
        }
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
$htmlHead->addDescription('Browse every beer style by family — real color ranges, ' . $beerStyleCount . ' beer styles across ' . $beerFamilyCount . ' families, plus ' . $otherStyleCount . ' styles of ' . proseList($otherNames) . '.');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Styles'); ?>
    <div class="cb-page">
        <header class="ix-head">
            <h1 class="ix-h1">Beer Styles</h1>
            <p class="ix-sub">Browse by family &mdash; each swatch row is the spread of color you&#8217;ll find inside. <?php echo $beerStyleCount; ?> beer styles across <?php echo $beerFamilyCount; ?> families, sorted by fermentation class.</p>
        </header>
        <?php
        // One section of the index: a grid of family color cards, or (for the
        // non-beer beverages) a plain chip list of style links
        $renderSection = function($section) use ($byParent, $text){
            echo '<section class="ix-class">';
            if($section['cards']){
                echo '<h2 class="ix-class-h sp-class-h">' . $text->get($section['name']) . ' <span class="cb-count cb-count--bare">' . count($section['families']) . ' families</span></h2>';
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

                    // The card isn't a link — the family name and every style
                    // name are, so a reader can jump straight to a style
                    echo '<div class="ix-card"' . $cardStyle . '>';
                    echo '<div class="ix-card-top"><a class="ix-card-name" href="/style/family/' . rawurlencode($p['slug']) . '">' . $text->get($p['name']) . '</a></div>';
                    if($mids){
                        echo '<div class="ix-sw-row">';
                        foreach($mids as $mid){
                            echo '<span class="ix-sw" style="background:' . SRM::hex($mid) . '"></span>';
                        }
                        echo '</div>';
                    }
                    echo '<ul class="ix-style-list">';
                    foreach($kids as $s){
                        // The tag is load-bearing: a catch-all's page is prose
                        // only — no tasting notes, usually no color or stats —
                        // so it shouldn't read as another Tasting Sheet
                        $tag = $s['ca'] ? ' <span class="cb-tag">catch-all</span>' : '';
                        echo '<li><a href="/style/' . rawurlencode($s['id']) . '">' . $text->get($s['name']) . $tag . '</a></li>';
                    }
                    echo '</ul>';
                    echo '</div>';  // Close ix-card
                }
                echo '</div>';  // Close ix-card-grid
            }else{
                // Non-beer beverage type: heading (linking to the family page)
                // + plain list of its styles
                $p = $section['families'][0];
                $kids = $byParent[$p['slug']];
                echo '<h2 class="ix-class-h sp-class-h"><a class="ix-fam-link" href="/style/family/' . rawurlencode($p['slug']) . '">' . $text->get($section['name']) . '</a> <span class="cb-count cb-count--bare">' . count($kids) . ' styles</span></h2>';
                $names = array();
                foreach($kids as $s){
                    $tag = $s['ca'] ? ' <span class="cb-tag">catch-all</span>' : '';
                    $names[] = '<a class="sp-style-link" href="/style/' . rawurlencode($s['id']) . '">' . $text->get($s['name']) . $tag . '</a>';
                }
                echo '<p class="ix-chip-list">' . implode('<span class="ix-chip-sep">&middot;</span>', $names) . '</p>';
            }
            echo '</section>';
        };

        foreach($sections as $section){
            $renderSection($section);
        }

        // Beyond beer: same vocabulary, different ferment. Kept below the beer
        // sections and out of the headline count so neither number is a lie.
        if($otherSections){
            echo '<header class="ix-divide">';
            echo '<h2 class="ix-divide-h">Beyond Beer</h2>';
            echo '<p class="ix-divide-sub">' . $otherStyleCount . ' more styles of ' . proseList($otherNames) . ' &mdash; fermented from fruit and honey rather than grain.</p>';
            echo '</header>';
            foreach($otherSections as $section){
                $renderSection($section);
            }
        }
        ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
