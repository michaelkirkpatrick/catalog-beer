<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$text = new Text(false, true, true);
$alert = new Alert();

// Total Number of Pages
$api = new API();
$brewerCountResp = $api->request('GET', '/brewer/count', '');
$brewerCountData = json_decode($brewerCountResp);
$numBrewers = $brewerCountData->value;
$perPage = 500;
$totalPages = round($numBrewers/$perPage, 0, PHP_ROUND_HALF_UP);

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
$htmlHead = new htmlHead('List of Brewers');
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Brewers'); ?>
	<div class="container">
    <div class="row">
    	
			
		</div>
		<?php
		// Heading 1
		echo '<div class="row">';
    echo '<div class="col">';
		echo '<h1>Brewers</h1>';
		echo '<p class="text-muted"><small>Page ' . $page . ' of ' . $totalPages . '</small></p>';
		echo '</div>';
		echo '<div class="col">';
		echo '<p class="text-right"><a class="btn btn-primary" href="/brewer/add" role="button" title="Add a brewer"><strong>+</strong> Add</a></p>';
		echo '</div>';
		echo '</div>';
		echo '<div class="row">';
		echo '<div class="col">';
		echo $alert->display();
		echo '</div>';
		echo '</div>';
		
		// Get Brewer IDs
		$api = new API();
		$brewerResp = $api->request('GET', '/brewer?limit=' . $perPage . '&cursor=' . $cursor, '');
		$brewerData = json_decode($brewerResp);

		// Setup Columns
		echo '<div class="row">';
		echo '<div class="col-md-4">';
		$perColumn = ceil(count($brewerData->data)/3);
		$j = 1;
		$firstLetterStore = '';
		$numericShown = false;

		for($i=0; $i<count($brewerData->data); $i++){
			// Parse Text
			$brewerName = $text->get($brewerData->data[$i]->name);

			// First Letter
			$firstLetter = strtolower(substr($brewerName, 0, 1));
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
			echo '<p><a href="/brewer/' . $brewerData->data[$i]->id . '">' . $brewerName . '</a></p>' . "\n";
			
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
		echo $nav->pagination($page, $totalPages, '/brewer');
		echo '</div>';	// Close col
		echo '</div>';	// Close row
		?>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>