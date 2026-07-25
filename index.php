<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Homepage — the mark, the scale, and one thing to do.

Search is not on this page. It lives in the top nav, on every page, so the
body no longer carries a second copy of it. What the old Browse/Add cards
carried comes back as a pair of buttons: "Add a brewer" takes the ink button
because it is the only action the nav leads to nowhere — today it sits two
clicks deep behind /brewer — and Browse is the ghost, for anyone who arrived
without something specific in mind.

The mark is drawn here rather than linked as a flat asset because its eight
squares ARE SRM values: the same chart the styles pages colour their glasses
from (SRM::hex), pale straw to near-black in reading order, with the glass
left ink as the anchor. Edit the chart and the logo follows.

Design system: composes .cb-* primitives (catalog-components.css); hp-*
classes are page layout only (styles-pages.css). Tokens in catalog.css.
--- */

// Scale for the lede — the same session-cached numbers the navbar badges use,
// so the page makes no API calls of its own. Brewers and beers are what the
// catalog has collected ("so far"); styles are the vocabulary it files them
// against, which is why that clause reads "covering". Either API count can be
// null if the API is unreachable; a missing number drops out of the sentence
// rather than printing as a zero. The style count is a constant, so it is
// always there to carry the sentence on its own.
$counts = $nav->counts();
$collected = array();
if($counts['brewers'] !== null){ $collected[] = '<span class="hp-num">' . number_format($counts['brewers']) . '</span> brewers'; }
if($counts['beers']   !== null){ $collected[] = '<span class="hp-num">' . number_format($counts['beers'])   . '</span> beers'; }
if($collected){
    $ledeScale = implode(' and ', $collected) . ' so far, covering '
        . '<span class="hp-num">' . number_format($counts['styles']) . '</span> styles, all of it open data.';
}else{
    // Both live counts unavailable: say what the project is instead of how big
    // it is, rather than leading with the one number that never moves.
    $ledeScale = 'Open data on the world&#8217;s brewers, beers and styles.';
}

// The mark: eight squares on a 3x3 grid (the ninth cell holds the glass),
// filled in reading order with SRM 1 -> 36. Stored as SRM values, not hexes,
// so the logo and the styles pages can never drift apart. Geometry matches
// /images/logo-black.svg, which is still the flat mark used elsewhere.
$markSRM   = array(1, 4, 7, 11, 15, 20, 27, 36);
$markCells = array(
    array(10,    10,    56.2, 56.2), array(71.9,  10,    56.3, 56.2), array(133.8, 10,    56.2, 56.2),
    array(10,    71.9,  56.2, 56.3), array(71.9,  71.9,  56.3, 56.3), array(133.8, 71.9,  56.2, 56.3),
    array(10,    133.8, 56.2, 56.2), array(71.9,  133.8, 56.3, 56.2)
);

// HTML Head
$htmlHead = new htmlHead('Catalog.beer: The Internet\'s Beer Database');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
$htmlHead->addDescription('The Internet\'s Beer Database — an open catalog of the world\'s brewers, beers and beer styles, free to use and free to build on.');
// Organization + WebSite as one JSON-LD graph, homepage only — the site-level
// identity search engines hang the per-page microdata (Brewery, Product,
// BreadcrumbList) off of. license = the CC BY 4.0 note in the pitch.
$htmlHead->addJsonLd(array(
    '@context' => 'https://schema.org',
    '@graph' => array(
        array(
            '@type' => 'Organization',
            '@id' => 'https://catalog.beer/#organization',
            'name' => 'Catalog.beer',
            'url' => 'https://catalog.beer/',
            'logo' => 'https://catalog.beer/images/logo-black.svg',
            'description' => 'An open catalog of the world\'s brewers, beers and beer styles, free to use and free to build on.'
        ),
        array(
            '@type' => 'WebSite',
            '@id' => 'https://catalog.beer/#website',
            'name' => 'Catalog.beer',
            'url' => 'https://catalog.beer/',
            'publisher' => array('@id' => 'https://catalog.beer/#organization'),
            'license' => 'https://creativecommons.org/licenses/by/4.0/'
        )
    )
));
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page">
        <div class="hp-hero">
            <svg class="hp-mark" viewBox="0 0 200 200" role="img" aria-label="Catalog.beer">
                <?php
                // Squares, light to dark
                foreach($markCells as $i => $cell){
                    echo '<rect x="' . $cell[0] . '" y="' . $cell[1] . '" width="' . $cell[2] . '" height="' . $cell[3] . '" fill="' . SRM::hex($markSRM[$i]) . '"></rect>' . "\n";
                }
                ?>
                <!-- The glass, in ink (currentColor, set on .hp-mark) -->
                <path fill="currentColor" d="M161.9,135c12,0,14.6,2,15.1,2.5l-4.5,49.1c-0.9,1.5-6.7,2.2-10.6,2.3s-9.8-0.8-10.6-2.3l-4.5-49.1C147.3,137,149.9,135,161.9,135 M161.9,133.8c-11.5,0-14.9,1.8-16,2.8l-0.4,0.4v0.6l4.5,49.1v0.3l0.1,0.2c1.6,2.7,10.6,2.9,11.6,2.9s10.1-0.2,11.7-2.9l0.1-0.2v-0.3l4.5-49.1V137l-0.4-0.4c-1-1.1-4.5-2.8-16-2.8H161.9z"></path>
                <path fill="currentColor" d="M161.9,140.9c-2.4,0-13.2,1.1-13.2,1.1l3.8,41.3c1,2.3,9.5,2.4,9.5,2.4s8.5-0.1,9.5-2.4l3.8-41.3C175.1,141.9,174.3,140.9,161.9,140.9L161.9,140.9z"></path>
            </svg>
            <h1 class="hp-title">
                <span class="hp-title__brand">Catalog.beer</span>
                The Internet&#8217;s Beer Database
            </h1>
        </div>

        <div class="hp-pitch">
            <p class="hp-lede"><?php echo $ledeScale; ?><br>If a brewer or a beer is missing, you can add it.</p>
            <div class="hp-cta">
                <a class="cb-btn cb-btn--primary hp-btn" href="/brewer/add">Add a brewer</a>
                <a class="cb-btn cb-btn--ghost hp-btn" href="/beer">Browse the catalog</a>
            </div>
            <p class="cb-label cb-label--sm hp-note">CC BY 4.0 &nbsp;&middot;&nbsp; <a href="/api-docs">Build on the API</a></p>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
