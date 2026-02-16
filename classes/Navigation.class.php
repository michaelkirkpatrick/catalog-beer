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
	// Public Variables
	public $currentURI;
	public $breadcrumbHTML;
	public $breadcrumbText = array();
	public $breadcrumbLink = array();
	
	// Private Variables
	private $URIArray = array();
	private $topNavSection = '';
	
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
		
		// Generate Links
		$links = $this->activeNav($section, '/brewer', 'Brewers');
		$links .= $this->activeNav($section, '/beer', 'Beer');
		
		// Add in Links
		$html = str_replace('##ITEMS##', $links, $html);
		
		// Sign In / Sign Out
		if(isset($_SESSION['userID'])){
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
	
	private function activeNav($section, $url, $title){
		// Add Active?
		$classAdd = '';
		$srAdd = '';
		if($section == $title){
			$classAdd = ' active';
			$srAdd = ' <span class="visually-hidden">(current)</span>';
		}
		
		// Create HTML
		$html = '<a class="nav-item nav-link' . $classAdd . '" href="'. $url . '">' . $title . '</a>';
		
		// Return
		return $html;
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
		
		$pageNav .= '</ul>';		// Close pagination
		$pageNav .= '</nav>';		// Close nav
		return $pageNav;
	}
}
?>