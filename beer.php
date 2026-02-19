<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Brewer Information
$beerID = $_GET['beerID'] ?? '';
$api = new API();
$beerResp = $api->request('GET', '/beer/' . $beerID, '');
$beerData = json_decode($beerResp);
if(isset($beerData->error)){
	// Invalid beerID
	// Log Error
	$errorLog = new LogError();
	$errorLog->errorNumber = 'C15';
	$errorLog->errorMsg = 'Invalid beerID';
	$errorLog->badData = "beerID: $beerID\n" . $beerData->error_msg;
	$errorLog->filename = 'beer.php';
	$errorLog->write();
	
	http_response_code(404);
	header('location: /error_page/404.php');
	exit();
}

// HTML Head
$htmlHead = new htmlHead($beerData->name);
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Beer'); ?>
	<div class="container">
    <div class="row">
    	<div class="col-md-12 col-sm-12 col-lg-9 col-xl-7">
    		<?php
				// Info
				$text1 = new Text(false, true, true);
				$text2 = new Text(true, true, false);
				$text3 = new Text(false, false, true);
				$beerName = $text1->get($beerData->name);
				$beerStyle = $text1->get($beerData->style);
				$brewerURL = $text3->get($beerData->brewer->id);
				$brewerName = $text1->get($beerData->brewer->name);
				
				// --- Just Added? ---
				if(isset($_SESSION['add_beer_success'])){
					if($_SESSION['add_beer_success']){
						// Show Alert
						$alert = new Alert();
						$alert->msg = '**Success!** Thanks for adding this beer to the database. [Add another beer by ' . $brewerName . '](/beer/add/' . $brewerURL . ').';
						$alert->type = 'success';
						$alert->dismissible = true;
						echo $alert->display();

						// Reset Variable
						$_SESSION['add_beer_success'] = false;
					}
				}
				
				// ----- Brewery Info -----
				echo '<h1>' . $beerName;
				if($beerData->cb_verified){
					echo '<img src="/images/cb-verified.svg" width="20" height="20" class="d-inline-block align-baseline" alt="Catalog.beer Verified"  title="Verified by Catalog.beer" style="margin-left:2px;">';
				}elseif($beerData->brewer_verified){
					echo '<img src="/images/brewer-verified.svg" width="20" height="20" class="d-inline-block align-baseline" alt="Brewer Verified" data-bs-toggle="tooltip" data-bs-placement="right" title="Verified by the brewer" style="margin-left:2px;">';
				}
				echo '</h1>' . "\n";
				
				// Style
				echo '<p class="lead">' . $beerStyle . '</p>' . "\n";
				
				// Description
				if(!empty($beerData->description)){
					echo $text2->get($beerData->description);
				}
				
				// ABV & IBU
				if(!empty($beerData->abv)){
					$abv = $text3->get($beerData->abv);
					echo '<p><strong>ABV:</strong> ' . $abv . '%</p>' . "\n";
				}
				if(!empty($beerData->ibu)){
					$ibu = $text3->get($beerData->ibu);
					echo '<p><strong>IBU:</strong> ' . $ibu . '</p>' . "\n";
				}
				
				// Brewer
				echo '<p><strong>Brewer:</strong> <a href="/brewer/' . $brewerURL . '">' . $brewerName . '</a></p>' . "\n";

				// Edit Button
				$beerIDString = $text3->get($beerData->id);
				if(isset($_SESSION['userID'])){
					echo '<p style="margin-top:1rem;"><a href="/beer/' . $beerIDString . '/edit" class="btn btn-outline-secondary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg> Edit Beer</a></p>';
				}
				?>
			</div>
			<div class="col-lg-3 col-xl-5"></div>
    </div>  
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>