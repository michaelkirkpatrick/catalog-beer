<?php
/* ---
// Top Navigation
$nav = new Navigation();
echo $nav->topNav('section');

<?php
// Breadcrumbs
$nav->breadcrumbText = array();
$nav->breadcrumbLink = array();
echo $nav->breadcrumbs();
?>

<?php echo $nav->footer(); ?>
--- */
class Navigation {
    /* Number of styles, stated in the homepage lede. Not a navbar badge.

    Hard-coded on purpose. There is no /style/count endpoint, and the only other
    way to get the number is to pull the whole vocabulary from GET /style — an
    extra blocking API call for the many visitors who arrive without a session to
    cache it in (initialize.php starts one only if a cookie is already present).
    The vocabulary changes a few times a year, so a constant someone bumps is the
    better trade.

    Bump this whenever styles are added or removed. The true figure is the one
    the Styles index prints in its headline ($beerStyleCount in style-list.php,
    counted live off the vocabulary) — if the two ever disagree, that page is
    right and this number is stale.

    Beer only, deliberately: the headline a visitor lands on after clicking
    through says "N beer styles", and the two numbers should match. The full
    vocabulary is larger — style-list.php closes with $otherStyleCount more
    styles of cider, perry and mead under "Beyond Beer". */
    const STYLE_COUNT = 171;

    // Public Variables
    public $currentURI;
    public $breadcrumbHTML;
    public $breadcrumbText = array();
    public $breadcrumbLink = array();
    
    // Private Variables
    private $URIArray = array();
    private $topNavSection = '';
    private $countsCache = null;
    
    // Startup
    function __construct(){
        $this->currentURI = $_SERVER['REQUEST_URI'];
        $step1 = explode('?', $this->currentURI);
        $pieces = array($step1[0]);
        if(isset($step1[1])){
            $append = explode('&', $step1[1]);
        }else{
            $append = '';   
        }
        array_push($pieces, $append);
        $this->URIArray = $pieces;
    }
    
    // ---------- BREADCRUMBS ----------
    public function breadcrumbs(){
        /* ---
        // Breadcrumbs
        $nav = new Navigation();
        $nav->breadcrumbText = array();
        $nav->breadcrumbLink = array();
        echo $nav->breadcrumbs();
        --- */
        $numItems = count($this->breadcrumbText);
        $html = '';
        if($numItems > 0){
            // Start HTML
            $html .= '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

            // Loop through crumbs
            for($i=0; $i<=$numItems-1; $i++){
                // Start LI Tag
                $html .= '<li class="breadcrumb-item';

                // Add Link
                if($i != $numItems-1 && !empty($this->breadcrumbLink[$i])){
                    $html .= '"><a href="'. $this->breadcrumbLink[$i] . '">';
                }else{
                    $html .= ' active">';
                }

                // Breadcrumb Text
                $text = htmlspecialchars($this->breadcrumbText[$i] ?? '');
                $html .= SmartyPants::defaultTransform($text);

                // Close Link
                if($i != $numItems-1 && !empty($this->breadcrumbLink[$i])){
                    $html .= '</a>';
                }

                // Close LI
                $html .= '</li>';
            }

            // End HTML
            $html .= '</ol></nav>';

            // Update Public Variable
            $this->breadcrumbHTML = $html;
        }
        // Return HTML
        return $html;
    }
    
    // ---------- FOOTER ----------
    public function footer(){
        $html = file_get_contents(ROOT . '/classes/resources/plain-footer.html');
        
        // Staging
        if(ENVIRONMENT == 'staging'){
            $staging = ' <span style="background-color: rgba(255, 238, 85, 0.54);">[Staging]</span>';
        }else{
            $staging = '';
        }
        $html = str_replace('##STAGING##', $staging, $html);
        return $html;
    }
    
    // ---------- NAV BAR ----------
    public function navbar($section){
        // Get Navbar
        $html = file_get_contents(ROOT . '/classes/resources/navbar.html');
        
        // Generate Links (with cached counts for Brewers + Beer). Styles carries no
        // badge — the number isn't one a reader is browsing by.
        $counts = $this->counts();
        $links = $this->activeNav($section, '/brewer', 'Brewers', $counts['brewers']);
        $links .= $this->activeNav($section, '/beer', 'Beer', $counts['beers']);
        $links .= $this->activeNav($section, '/style', 'Styles');
        $links .= $this->activeNav($section, '/map', 'Map');
        
        // Add in Links
        $html = str_replace('##ITEMS##', $links, $html);

        // Global search placeholder — reflects the section + cached counts.
        // (The field is wired to Algolia separately; this is copy only.)
        if($section == 'Beer'){
            $searchPlaceholder = ($counts['beers'] !== null)
                ? 'Search ' . number_format($counts['beers']) . ' beers…'
                : 'Search beers…';
        }elseif($section == 'Brewers'){
            $searchPlaceholder = ($counts['brewers'] !== null)
                ? 'Search ' . number_format($counts['brewers']) . ' brewers…'
                : 'Search brewers…';
        }else{
            $searchPlaceholder = 'Search Catalog.beer…';
        }
        $html = str_replace('##SEARCHPLACEHOLDER##', htmlspecialchars($searchPlaceholder, ENT_QUOTES), $html);

        // Sign In / Sign Out
        if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
            $signIn = '<li><a class="dropdown-item" href="/account">My Account</a></li>' . "\n";
            $signIn .= '<li><hr class="dropdown-divider"></li>' . "\n";
            $signIn .= '<li><a class="dropdown-item" href="/logout">Log out</a></li>' . "\n";
        }else{
            $signIn = '<li><a class="dropdown-item" href="/signup">Create an Account</a></li>' . "\n";
            $signIn .= '<li><a class="dropdown-item" href="/login">Log in</a></li>' . "\n";
        }
        $html = str_replace('##ACCOUNT##', $signIn, $html);

        // Return
        return $html;
    }
    
    private function activeNav($section, $url, $title, $count = null){
        // Add Active?
        $classAdd = '';
        $srAdd = '';
        if($section == $title){
            $classAdd = ' active';
            $srAdd = ' <span class="visually-hidden">(current)</span>';
        }

        // Optional count badge
        $badge = '';
        if($count !== null){
            $badge = ' <span class="cb-count">' . number_format($count) . '</span>';
        }

        // Create HTML
        $html = '<a class="nav-item nav-link' . $classAdd . '" href="'. $url . '">' . $title . $badge . $srAdd . '</a>';

        // Return
        return $html;
    }

    // Brewer, beer + style counts. Brewers and beers come from the API and are
    // cached per-session (short TTL) because the navbar badges them on every page
    // and each count is a blocking API call. Cache is busted on add (see
    // beer-add.php / brewer-add.php) for instant freshness.
    //
    // Public because the homepage states all three numbers in its lede — it reads
    // brewers and beers from here rather than counting again, so the page costs
    // nothing the navbar wasn't already paying for. Either may be null (API down).
    public function counts(){
        // Per-request memo. Anonymous visitors have no session at all (see
        // initialize.php), so the session cache below can't hold anything for
        // them — without this the homepage, which asks for the counts before it
        // renders the navbar, would fetch them twice on every hit.
        if($this->countsCache !== null){
            return $this->countsCache;
        }

        $out = $this->fetchCounts();

        // Styles are not fetched — see STYLE_COUNT. Stamped on every read rather
        // than stored, so bumping the constant takes effect immediately instead
        // of waiting out every live session's cached copy.
        $out['styles'] = self::STYLE_COUNT;

        $this->countsCache = $out;
        return $out;
    }

    // The API-backed half of counts(): brewers + beers, session-cached.
    private function fetchCounts(){
        if(isset($_SESSION['cb_counts']['ts']) && (time() - $_SESSION['cb_counts']['ts']) < 600){
            // Fill any key a session cached before this shape grew, so a live
            // session mid-deploy reads null (no badge) rather than warning.
            return $_SESSION['cb_counts'] + array('brewers' => null, 'beers' => null);
        }

        $out = array('brewers' => null, 'beers' => null, 'ts' => time());
        $api = new API();
        $b = json_decode($api->request('GET', '/brewer/count', ''));
        if(isset($b->value)){ $out['brewers'] = intval($b->value); }
        // Backend unreachable — skip the second blocking call so the navbar doesn't
        // hang twice on a timeout during an outage. Return uncached so the next
        // page retries.
        if($api->unavailable()){ return $out; }
        $r = json_decode($api->request('GET', '/beer/count', ''));
        if(isset($r->value)){ $out['beers'] = intval($r->value); }

        // Cache only on at least one success, so a transient API blip retries next page
        if($out['brewers'] !== null || $out['beers'] !== null){
            $_SESSION['cb_counts'] = $out;
        }

        return $out;
    }
    
    // ----- Pagination -----
    public function pagination($page, $totalPages, $baseURL){
        $pageNav = '<nav aria-label="Page navigation">';
        $pageNav .= '<ul class="pagination justify-content-center">';
        
        if($page > 1){
            // Previous
            $previous = $page - 1;
            $pageNav .= '<li class="page-item"><a class="page-link" href="' . $baseURL . '?page=' . $previous . '" aria-label="Previous" title="Previous Page"><span aria-hidden="true">&lt;</span><span class="visually-hidden">Previous</span></a></li>';
        }
        
        if($page >= 15){
            // Jump 10 back
            $minusTen = $page - 10;
            $pageNav .= '<li class="page-item"><a class="page-link" href="' . $baseURL . '?page=' . $minusTen . '" aria-label="Jump Back 10" title="Jump Back 10"><span aria-hidden="true">-10</span><span class="visually-hidden">Jump Back 10</span></a></li>';
        }
        
        // Starting Page Number
        if($page-5 > 0){$start = $page-5;}
        else{$start = 1;}
        
        // Display Navigation
        for($i=$start; $i<=$start+9; $i++){
            // Active State?
            if($i == $page){$classAdd = ' active';}
            else{$classAdd = '';}
            
            // Display HTML
            $pageNav .= '<li class="page-item' . $classAdd . '"><a class="page-link" href="' . $baseURL . '?page=' . $i . '">' . $i . '</a></li>';
        }
        
        if($page+14 < $totalPages){
            // Jump forward 10
            $plusTen = $page + 10;
            $pageNav .= '<li class="page-item"><a class="page-link" href="' . $baseURL . '?page=' . $plusTen . '" aria-label="Jump Forward 10" title="Jump Forward 10"><span aria-hidden="true">+10</span><span class="visually-hidden">Jump Forward 10</span></a></li>';
        }
        
        if($page < $totalPages){
            // Next
            $next = $page + 1;
            $pageNav .= '<li class="page-item"><a class="page-link" href="' . $baseURL . '?page=' . $next . '" aria-label="Next" title="Next Page"><span aria-hidden="true">&gt;</span><span class="visually-hidden">Next</span></a></li>';
        }
        
        $pageNav .= '</ul>';        // Close pagination
        $pageNav .= '</nav>';       // Close nav
        return $pageNav;
    }

    // ----- Editorial Pager (catalog A-Z index pages) -----
    // Mono chip pagination for the beer/brewer index: chevron Prev/Next, a
    // 5-wide window centered on the current page, and first/last with ellipses.
    // Distinct from pagination() (Bootstrap list, ±10 jumps) so restyling here
    // doesn't disturb pages still using that one. Styles: .cx-pager* (styles-pages.css).
    public function catalogPager($page, $totalPages, $baseURL){
        $page = intval($page);
        $totalPages = intval($totalPages);
        if($totalPages <= 1){
            return '';
        }

        $chevronLeft = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg>';
        $chevronRight = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg>';

        $html = '<nav class="cx-pager" aria-label="Page navigation">';

        // Prev
        if($page > 1){
            $html .= '<a class="cx-pager__chip" href="' . $baseURL . '?page=' . ($page - 1) . '" rel="prev" aria-label="Previous page">' . $chevronLeft . ' Prev</a>';
        }

        // 5-wide window, clamped so it never overruns either end
        $window = 5;
        $start = max(1, min($page - 2, $totalPages - ($window - 1)));
        $end = min($totalPages, $start + $window - 1);

        // Leading: first page + ellipsis when the window has moved off the start
        if($start > 1){
            $html .= $this->pagerNum(1, $page, $baseURL);
            if($start > 2){
                $html .= '<span class="cx-pager__gap" aria-hidden="true">&hellip;</span>';
            }
        }

        for($i = $start; $i <= $end; $i++){
            $html .= $this->pagerNum($i, $page, $baseURL);
        }

        // Trailing: ellipsis + last page when the window stops short of the end
        if($end < $totalPages){
            if($end < $totalPages - 1){
                $html .= '<span class="cx-pager__gap" aria-hidden="true">&hellip;</span>';
            }
            $html .= $this->pagerNum($totalPages, $page, $baseURL);
        }

        // Next
        if($page < $totalPages){
            $html .= '<a class="cx-pager__chip" href="' . $baseURL . '?page=' . ($page + 1) . '" rel="next" aria-label="Next page">Next ' . $chevronRight . '</a>';
        }

        $html .= '</nav>';
        return $html;
    }

    private function pagerNum($i, $page, $baseURL){
        if($i == $page){
            return '<a class="cx-pager__num is-current" href="' . $baseURL . '?page=' . $i . '" aria-current="page">' . $i . '</a>';
        }
        return '<a class="cx-pager__num" href="' . $baseURL . '?page=' . $i . '">' . $i . '</a>';
    }
}
?>