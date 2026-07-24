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
$text = new Text(false, true, true);    // display names
$textID = new Text(false, false, true); // ids
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

// SRM 1-40 -> hex (the beer-color chart). A beer's SRM comes from its style;
// where it's unknown, fall back to a neutral swatch.
$srmHex = array(
    1=>'#FFE699', 2=>'#FFD878', 3=>'#FFCA5A', 4=>'#FFBF42', 5=>'#FBB123', 6=>'#F8A600', 7=>'#F39C00', 8=>'#EA8F00',
    9=>'#E58500', 10=>'#DE7C00', 11=>'#D77200', 12=>'#CF6900', 13=>'#CB6200', 14=>'#C35900', 15=>'#BB5100', 16=>'#B54C00',
    17=>'#B04500', 18=>'#A63E00', 19=>'#A13700', 20=>'#9B3200', 21=>'#952D00', 22=>'#8E2900', 23=>'#882300', 24=>'#821E00',
    25=>'#7B1A00', 26=>'#771900', 27=>'#701400', 28=>'#6A0E00', 29=>'#660D00', 30=>'#5E0B00', 31=>'#5A0A02', 32=>'#560903',
    33=>'#520907', 34=>'#4C0505', 35=>'#470606', 36=>'#440607', 37=>'#3F0708', 38=>'#3B0607', 39=>'#3A070B', 40=>'#360A0A',
);
$srmNeutral = '#C8A86B';

// First-letter bucket for A-Z grouping: transliterate to ASCII, drop leading
// punctuation/space, uppercase the first character. Non-letters group under '#'.
if(!function_exists('cbListLetter')){
    function cbListLetter($name){
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string)$name);
        if($ascii === false || $ascii === ''){ $ascii = (string)$name; }
        $ascii = preg_replace('/^[^A-Za-z0-9]+/', '', $ascii);
        $ch = strtoupper(substr($ascii, 0, 1));
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
        // Page header: title + count/page meta, and the add-a-beer action.
        echo '<div class="cx-head">';
        echo '<div>';
        echo '<h1 class="cb-title cx-title">Beer</h1>';
        echo '<p class="cx-meta">' . number_format($numBeers) . ' beers &middot; page ' . number_format($page) . ' of ' . number_format($totalPages) . '</p>';
        echo '</div>';
        echo '<a class="btn btn-primary cx-add" href="/beer/add" role="button" title="Add a beer"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg> Add a beer</a>';
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

        // Group rows by first letter (data already sorted by name from the API).
        $groups = array();
        foreach($beerData->data as $row){
            $groups[cbListLetter($row->name)][] = $row;
        }
        // A-Z, with the non-alphabetic '#' group last.
        uksort($groups, function($a, $b){
            if($a === '#'){ return 1; }
            if($b === '#'){ return -1; }
            return strcmp($a, $b);
        });

        // Render the 3-column A-Z index (CSS multi-column; groups stay intact).
        echo '<div class="cx-cols">';
        foreach($groups as $letter => $rows){
            echo '<div class="cx-grp">';
            echo '<div class="cx-letter">' . htmlspecialchars($letter) . '</div>';
            foreach($rows as $row){
                $beerName = $text->get($row->name);
                $beerID = $textID->get($row->id);

                echo '<a class="cx-row" href="/beer/' . $beerID . '">';
                echo '<span class="cx-row__l">';
                // Swatch only when the enriched shape is present.
                if(property_exists($row, 'srm')){
                    $srm = ($row->srm === null) ? null : intval($row->srm);
                    $color = ($srm !== null && isset($srmHex[$srm])) ? $srmHex[$srm] : $srmNeutral;
                    echo '<span class="cb-swatch cx-swatch" style="background:' . $color . ';"></span>';
                }
                echo '<span class="cx-name">' . $beerName . '</span>';
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
