<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Beer index — "Editorial index". The full A-Z list of beers in a 3-column,
letter-grouped layout. Each row is a beer: an SRM color swatch (derived from
the beer's style) + name. Server-rendered, one page of 500 at a time; the
global Algolia search in the nav is the way to jump to a specific beer (this
page no longer client-filters the set).

Data: GET /beer with the master-key-only ?enriched flag, which adds a
representative `srm` per row. The style name is deliberately not shown — the
stored `beer.style` is the brewer's raw label, not the canonical style name,
so an alias like "Dark American Wheat Ale or Lager without Yeast" would read
as the style here. If the deployed API predates the enriched flag the rows come
back name-only and the page degrades to plain names — no swatch — so frontend
and API can deploy in any order.

Design system: composes .cb-* primitives (catalog-components.css); cx-*
classes are page layout only (styles-pages.css). Tokens in catalog.css.
--- */

// Required Classes
$alert = new Alert();

// Total Number of Pages
$api = new API();
$beerCountResp = $api->request('GET', '/beer/count', '');
$beerCountData = json_decode($beerCountResp);
if($api->unavailable() || !isset($beerCountData->value)){
    serve503();
}
$numBeers = intval($beerCountData->value);
$perPage = 500;
$totalPages = max(1, (int)ceil($numBeers / $perPage));

// Specific Page Requested? Validate to [1, totalPages].
$page = 1;
if(isset($_GET['page'])){
    $requested = filter_var($_GET['page'], FILTER_VALIDATE_INT);
    if($requested === false || $requested < 1 || $requested > $totalPages){
        http_response_code(404);
        $alert->msg = 'Whoops, the page number you requested is invalid. Let\'s start with page 1.';
        $alert->type = 'warning';
        $alert->dismissible = true;
    }else{
        $page = $requested;
    }
}

// Set Cursor
$cursor = base64_encode(($page - 1) * $perPage);

// First-letter bucket for A-Z grouping, taken from the name's first character
// as written: an accented letter counts as its base letter (À -> A), anything
// else -- punctuation, digits, other scripts -- groups under '#'. Leading
// punctuation is NOT skipped: the API sorts by the raw name, so "#FREEDOM" and
// "'t Hofbrouwerijke" sit among the symbols at the front of the catalog, and a
// heading of "F" or "T" there would be a lone group out of place.
if(!function_exists('cbListLetter')){
    function cbListLetter($name){
        $first = mb_substr((string)$name, 0, 1, 'UTF-8');
        // Only letters get transliterated: iconv also turns "§" into "SS".
        if(!preg_match('/^\p{L}$/u', $first)){ return '#'; }
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $first);
        // glibc gives "A" for "À"; other iconvs give "`A" -- keep only the letter.
        $ch = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string)$ascii), 0, 1));
        return ($ch >= 'A' && $ch <= 'Z') ? $ch : '#';
    }
}

// HTML Head
$htmlHead = new htmlHead('List of Beers');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Beer'); ?>
    <div class="cb-page" style="padding-bottom:2.2rem;">
        <?php
        // Page header: title + count/page meta. No add action here — a beer is
        // always created under a brewer (beer-add.php requires a brewerID), so
        // the entry point lives on the brewer page, not on this list.
        echo '<div class="cx-head">';
        echo '<div>';
        echo '<h1 class="cb-title cx-title">Beer</h1>';
        echo '<p class="cx-meta">' . number_format($numBeers) . ' beers &middot; page ' . number_format($page) . ' of ' . number_format($totalPages) . '</p>';
        echo '</div>';
        echo '</div>';

        // Invalid-page notice, if any
        echo $alert->display();

        // Get Beer List (enriched: style + SRM per row for master keys)
        $beerResp = $api->request('GET', '/beer?count=' . $perPage . '&cursor=' . $cursor . '&enriched=1', '');
        $beerData = json_decode($beerResp);
        if(!isset($beerData->data)){
            $listAlert = new Alert();
            $listAlert->msg = 'Sorry, we were unable to load the beer list. Please try again later.';
            $listAlert->type = 'warning';
            echo $listAlert->display();
            echo '</div>';
            echo $nav->footer();
            echo '</body></html>';
            exit();
        }

        // Group consecutive rows by first letter, in the API's order. The groups
        // are not re-sorted: pagination follows the API's ORDER BY name, which
        // puts symbols and digits first and other scripts after Z, so a '#'
        // heading belongs wherever those rows actually fall.
        $groups = array();
        foreach($beerData->data as $row){
            $letter = cbListLetter($row->name);
            $last = count($groups) - 1;
            if($last >= 0 && $groups[$last]['letter'] === $letter){
                $groups[$last]['rows'][] = $row;
            }else{
                $groups[] = array('letter' => $letter, 'rows' => array($row));
            }
        }

        // Render the 3-column A-Z index (CSS multi-column; groups stay intact).
        // The grid doubles as a schema.org ItemList (summary-page pattern: each
        // ListItem carries name + url + position). Positions are page-relative
        // — each page of 500 is its own list.
        echo '<div class="cx-cols" itemscope itemtype="https://schema.org/ItemList">';
        echo '<meta itemprop="numberOfItems" content="' . count($beerData->data) . '" />';
        $position = 0;
        foreach($groups as $group){
            $letter = $group['letter'];
            $rows = $group['rows'];
            echo '<div class="cx-grp">';
            echo '<div class="cx-letter">' . h($letter) . '</div>';
            foreach($rows as $row){
                // Raw from the API; h() at each of the three sinks below.
                $beerName = $row->name;
                $beerID = $row->id;
                $position++;

                echo '<a class="cx-row" href="/beer/' . h($beerID) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
                echo '<meta itemprop="position" content="' . $position . '" /><link itemprop="url" href="/beer/' . h($beerID) . '" />';
                echo '<span class="cx-row__l">';
                // Swatch only when the enriched shape is present. SRM::hex()
                // maps 1-40 to the beer-color chart, neutral when unknown.
                if(property_exists($row, 'srm')){
                    echo '<span class="cb-swatch cx-swatch" style="background:' . SRM::hex($row->srm) . ';"></span>';
                }
                echo '<span class="cx-name" itemprop="name">' . h($beerName) . '</span>';
                echo '</span>';
                echo '</a>';
            }
            echo '</div>';
        }
        echo '</div>';

        // Legend (beers only)
        echo '<div class="cb-legend"><span class="cb-legend__none">Swatch color = SRM &mdash; lighter is paler, darker is roastier</span></div>';

        // Pagination
        echo $nav->catalogPager($page, $totalPages, '/beer');
        ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
