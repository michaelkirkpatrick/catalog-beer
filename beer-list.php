<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$text = new Text(false, true, true);
$alert = new Alert();

// Total Number of Pages
$api = new API();
$beerCountResp = $api->request('GET', '/beer/count', '');
$beerCountData = json_decode($beerCountResp);
if(!isset($beerCountData->value)){
	http_response_code(503);
	$htmlHead = new htmlHead('Beer');
	echo $htmlHead->html;
	echo '<body>' . $nav->navbar('Beer') . '<div class="container"><h1>Beer</h1><p>Sorry, we are unable to connect to our database right now. Please try again later.</p></div>' . $nav->footer() . '</body></html>';
	exit();
}
$numBeers = $beerCountData->value;
$perPage = 500;
$totalPages = round($numBeers/$perPage, 0, PHP_ROUND_HALF_UP);

// Specific Page Requested?
if(isset($_GET['page'])){
	$page = intval($_GET['page']);
	if(is_int($page)){
		if($page >= $totalPages){
			// Exceeds max page number
			$page = 1;
			http_response_code(404);
			$alert->msg = 'Whoops, the page number you requested is invalid. Let\'s start with page 1.';
			$alert->type = 'warning';
			$alert->dismissible = true;
		}elseif($page == 0){
			// Not an integer page number
			$page = 1;
			http_response_code(404);
			$alert->msg = 'Whoops, the page number you requested is invalid. Let\'s start with page 1.';
			$alert->type = 'warning';
			$alert->dismissible = true;
		}
	}else{
		// Not an integer page number
		$page = 1;
		http_response_code(404);
		$alert->msg = 'Whoops, the page number you requested is invalid. Let\'s start with page 1.';
		$alert->type = 'warning';
		$alert->dismissible = true;
	}
}else{
	$page = 1;
}

// Set Cursor
$cursor = base64_encode(($page-1)*$perPage);

// HTML Head
$htmlHead = new htmlHead('List of Beers');
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Beer'); ?>
	<div class="container">
		<?php
		// Heading 1
		echo '<div class="row">';
    echo '<div class="col">';
		echo '<h1>Beer</h1>';
		echo '<p class="text-muted"><small>Page ' . $page . ' of ' . $totalPages . '</small></p>';
		echo $alert->display();
		echo '</div>';	// Close Col
		echo '</div>';	// Close Row

		// Get Beer List
		$beerResp = $api->request('GET', '/beer?limit=' . $perPage . '&cursor=' . $cursor, '');
		$beerData = json_decode($beerResp);
		if(!isset($beerData->data)){
			$alert->msg = 'Sorry, we were unable to load the beer list. Please try again later.';
			$alert->type = 'warning';
			echo $alert->display();
			echo '</div>';
			echo $nav->footer();
			echo '</body></html>';
			exit();
		}

		// Setup Columns
		echo '<div class="row">';
		echo '<div class="col-md-4">';
		$perColumn = ceil(count($beerData->data)/3);
		$j = 1;
		$firstLetterStore = '';
		$numericShown = false;

		for($i=0; $i<count($beerData->data); $i++){
			// Prep Text
			$beerName = $text->get($beerData->data[$i]->name);
			$beerID = $text->get($beerData->data[$i]->id);
			
			// First Letter
			$firstLetter = strtolower(substr($beerName, 0, 1));
			$alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
			if($firstLetter != $firstLetterStore){
				if(in_array($firstLetter, $alphabet)){
					echo '<h2>' . strtoupper($firstLetter) . '</h2>';
				}else{
					if(!$numericShown){
						echo '<h2>#</h2>';
					}
					$numericShown = true;
				}
				$firstLetterStore = $firstLetter;
			}

			// Show Text
			echo '<p><a href="/beer/' . $beerID . '">' . $beerName . '</a></p>';

			// Handle column
			if($j == $perColumn){
				echo '</div>';
				echo '<div class="col-md-4">';
				$j = 1;
			}else{
				$j++;
			}
		}
		// Close Last Column and Row
		echo '</div></div>';

		// Footer Navigation
		echo '<div class="row">';
		echo '<div class="col">';
		echo $nav->pagination($page, $totalPages, '/beer');
		echo '</div>';	// Close col
		echo '</div>';	// Close row
		?>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>