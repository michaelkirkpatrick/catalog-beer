<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Brewer index — "Editorial index". The full A-Z list of brewers in a 3-column,
letter-grouped layout. Each row is a brewer name with its beer count trailing.
Server-rendered, one page of 500 at a time; the global Algolia search in the
nav is the way to jump to a specific brewer.

Data: GET /brewer with the master-key-only ?enriched flag, which adds
`beer_count` per row. If the deployed API predates that flag the rows come back
name-only and the page degrades to plain names (no count) — so frontend and
API can deploy in any order.

Design system: composes .cb-* primitives (catalog-components.css); cx-*
classes are page layout only (styles-pages.css). Tokens in catalog.css.
--- */

// Required Classes
$alert = new Alert();

// Total Number of Pages
$api = new API();
$brewerCountResp = $api->request('GET', '/brewer/count', '');
$brewerCountData = json_decode($brewerCountResp);
if($api->unavailable() || !isset($brewerCountData->value)){
    serve503();
}
$numBrewers = intval($brewerCountData->value);
$perPage = 500;
$totalPages = max(1, (int)ceil($numBrewers / $perPage));

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
$htmlHead = new htmlHead('List of Brewers');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page" style="padding-bottom:2.2rem;">
        <?php
        // Page header: title + count/page meta, and the add-a-brewer action.
        echo '<div class="cx-head">';
        echo '<div>';
        echo '<h1 class="cb-title cx-title">Brewers</h1>';
        echo '<p class="cx-meta">' . number_format($numBrewers) . ' brewers &middot; page ' . number_format($page) . ' of ' . number_format($totalPages) . '</p>';
        echo '</div>';
        echo '<a class="btn btn-primary cx-add" href="/brewer/add" role="button" title="Add a brewer"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg> Add a brewer</a>';
        echo '</div>';

        // Flash: brewer just deleted (set by brewer-delete.php)
        if(!empty($_SESSION['delete_brewer_success'])){
            $flashAlert = new Alert();
            $flashAlert->msg = 'Brewer has been deleted.';
            $flashAlert->type = 'success';
            $flashAlert->dismissible = true;
            echo $flashAlert->display();
            unset($_SESSION['delete_brewer_success']);
        }

        // Invalid-page notice, if any
        echo $alert->display();

        // Get Brewer List (enriched: beer_count per row for master keys)
        $brewerResp = $api->request('GET', '/brewer?count=' . $perPage . '&cursor=' . $cursor . '&enriched=1', '');
        $brewerData = json_decode($brewerResp);
        if(!isset($brewerData->data)){
            $listAlert = new Alert();
            $listAlert->msg = 'Sorry, we were unable to load the brewer list. Please try again later.';
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
        foreach($brewerData->data as $row){
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
        echo '<meta itemprop="numberOfItems" content="' . count($brewerData->data) . '" />';
        $position = 0;
        foreach($groups as $group){
            $letter = $group['letter'];
            $rows = $group['rows'];
            echo '<div class="cx-grp">';
            echo '<div class="cx-letter">' . h($letter) . '</div>';
            foreach($rows as $row){
                $brewerName = $row->name;
                $brewerID = $row->id;
                $position++;

                echo '<a class="cx-row" href="/brewer/' . h($brewerID) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
                echo '<meta itemprop="position" content="' . $position . '" /><link itemprop="url" href="/brewer/' . h($brewerID) . '" />';
                echo '<span class="cx-name" itemprop="name">' . h($brewerName) . '</span>';
                // Trailing value: beer count (where present in the enriched shape).
                if(property_exists($row, 'beer_count')){
                    echo '<span class="cx-value">' . number_format(intval($row->beer_count)) . '</span>';
                }
                echo '</a>';
            }
            echo '</div>';
        }
        echo '</div>';

        // Pagination
        echo $nav->catalogPager($page, $totalPages, '/brewer');
        ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
