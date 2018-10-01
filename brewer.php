<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Brewer Information
$brewerID = $_GET['brewerID'];
$api = new API();
$brewerResp = $api->request('GET', '/brewer/' . $brewerID . '/beer', '');
$brewerData = json_decode($brewerResp);
if(isset($brewerData->error)){
	// Invalid Brewer ID
	// Log Error
	$errorLog = new LogError();
	$errorLog->errorNumber = 'C14';
	$errorLog->errorMsg = 'Invalid brewerID';
	$errorLog->badData = "brewerID: $brewerID\n" . $brewerData->error_msg;
	$errorLog->filename = 'brewer.php';
	$errorLog->write();
	
	http_response_code(404);
	header('location: /error_page/404.php');
	exit();
}

// HTML Head
$htmlHead = new htmlHead($brewerData->brewer->name);
if(!empty($brewerData->brewer->short_description)){
	// Add meta-description
	$htmlHead->addDescription($brewerData->brewer->short_description);
}
echo $htmlHead->html;
?>
<body>
<style>
	@media only screen and (max-width: 991px) {
		/* Mobile */
		.navFloat {
			float:left;
			width: 10rem;
			margin-top: 2rem;
		}
		#map {
			height: 200px;
			margin-bottom: 2rem;
		}
	}
	@media only screen and (min-width: 992px) {
		/* Desktop */
		.navFloat {
			float:right;
			width: 10rem;
		}
		#map {
			height: 100%;
		}
	}
</style>
	<?php echo $nav->navbar('Brewers'); ?>
	<div class="container" itemscope itemtype="http://schema.org/Brewery">
		<?php
		// ----- Brewery Info -----
		echo '<div class="row">' . "\n";
		echo '<div class="col-md-12 col-sm-12 col-lg-9 col-xl-7">' . "\n";

		$text1 = new Text(false, true, true);
		$brewerName = $text1->get($brewerData->brewer->name);
		echo '<h1 itemprop="name">' . $brewerName;
		if($brewerData->brewer->cb_verified){
			echo '<img src="/images/cb-verified.svg" width="20" height="20" class="d-inline-block align-baseline" alt="Catalog.beer Verified"  title="Verified by Catalog.beer" style="margin-left:2px;">';
		}elseif($brewerData->brewer->brewer_verified){
			echo '<img src="/images/brewer-verified.svg" width="20" height="20" class="d-inline-block align-baseline" alt="Brewer Verified" data-toggle="tooltip" data-placement="right" title="Verified by the brewer" style="margin-left:2px;">';
		}
		echo '</h1>' . "\n";

		// Description
		if(!empty($brewerData->brewer->description)){
			$text2 = new Text(true, true, false);
			echo '<div itemprop="description">';
			echo $text2->get($brewerData->brewer->description);
			echo '</div>';
		}

		// Link(s)
		$text3 = new Text(false, false, true);
		$addMargin = false;
		$dimension = 24;
		echo '<div>';
		if(!empty($brewerData->brewer->url)){
			// Prep Text
			$urlString = $text3->get($brewerData->brewer->url);
			
			// Show HTML
			echo '<a href="' . $urlString . '" itemprop="url"><img src="/images/internet-icon.svg" width="' . $dimension . '" height="' . $dimension . '" alt="Website Icon"  title="Visit ' . $brewerName . ' on the web"></a>';
			
			// Add Margin
			$addMargin = true;
		}
		if(!empty($brewerData->brewer->twitter_url)){
			// Prep Text
			$TurlString = $text3->get($brewerData->brewer->twitter_url);
			
			// Margin
			if($addMargin){
				$margin = ' style="margin-left:15px;"';
			}else{
				$margin = '';
				$addMargin = true;
			}
			
			// Show HTML
			echo '<a href="' . $TurlString . '"><img src="/images/twitter-icon.svg" width="' . $dimension . '" height="' . $dimension . '" alt="Twitter Icon"  title="Visit ' . $brewerName . ' on Twitter"' . $margin . '></a>';
		}
		if(!empty($brewerData->brewer->instagram_url)){
			// Prep Text
			$IurlString = $text3->get($brewerData->brewer->instagram_url);
			
			// Margin
			if($addMargin){
				$margin = ' style="margin-left:15px;"';
			}else{
				$margin = '';
				$addMargin = true;
			}
			
			// Show HTML
			echo '<a href="' . $IurlString . '"><img src="/images/instagram-icon.svg" width="' . $dimension . '" height="' . $dimension . '" alt="Instagram Icon"  title="Visit ' . $brewerName . ' on Instagram"' . $margin . '></a>';
		}
		if(!empty($brewerData->brewer->facebook_url)){
			// Prep Text
			$FurlString = $text3->get($brewerData->brewer->facebook_url);
			
			// Margin
			if($addMargin){
				$margin = ' style="margin-left:15px;"';
			}else{
				$margin = '';
			}
			
			// Show HTML
			echo '<a href="' . $FurlString . '"><img src="/images/facebook-icon.svg" width="' . $dimension . '" height="' . $dimension . '" alt="Facebook Icon"  title="Visit ' . $brewerName . ' on Facebook"' . $margin . '></a>';
		}
		echo '</div>';
		
		// Locations Info
		$locationResp = $api->request('GET', '/brewer/' . $brewerID . '/locations', '');
		$locationData = json_decode($locationResp);
		$locationH2 = 'Location';
		if(count($locationData->data) > 1){$locationH2 .= 's';}

		// Add Second Column & Close Row
		echo '</div>' . "\n";
		echo '<div class="col-lg-3 col-xl-5">' . "\n";
		echo '<div class="card navFloat">' . "\n";
		echo '<div class="card-header">Navigation</div>' . "\n";
		echo '<ul class="list-group list-group-flush">' . "\n";
		echo '  <li class="list-group-item"><a href="#locations" class="card-link">' . $locationH2 . '</a></li>' . "\n";
		echo '  <li class="list-group-item"><a href="#beer" class="card-link">Beer</a></li>' . "\n";
		echo '</ul>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";

		// ----- Location(s) ------
		if(count($locationData->data) > 0){
			
			// Section Heading
			echo '<div class="row">' . "\n";
			echo '<div class="col">' . "\n";
			echo '<h2 style="margin-top:1em;" id="locations">' . $locationH2 . '<hr>' . "\n";
			echo '</div>' . "\n";
			echo '</div>' . "\n";
			
			// Loop Through Locations
			$i=1;
			foreach($locationData->data as &$locationInfo){
				// Column Info
				if($i == 1){
					// Start New Row
					echo '<div class="row">' . "\n";
				}
				// New Column
				echo '<div class="col-md-4" itemprop="location" itemscope itemtype="http://schema.org/Place"><meta itemprop="publicAccess" content="true" />' . "\n";
				
				// Get Location Info
				$locationDetailResp = $api->request('GET', '/location/' . $locationInfo->id, '');
				$locationDetailData = json_decode($locationDetailResp);

				$locationName = $text1->get($locationDetailData->name);
				echo '<h3 itemprop="name">' . $locationName . '</h3>' . "\n";

				// Website
				if(!empty($locationDetailData->url)){
					$locationURL = $text3->get($locationDetailData->url);
					$locationWebsite = '<p><a href="' . $locationURL . '" itemprop="url">Website</a></p>';
					echo $locationWebsite;
				}

				// Telephone
				if(!empty($locationDetailData->telephone)){
					$telephoneNumber = '<p itemprop="telephone">(' . substr($locationDetailData->telephone, 0, 3) . ') ' . substr($locationDetailData->telephone, 3, 3) . '-' . substr($locationDetailData->telephone, 6, 4) . '</p>';
					$telephoneNumber = $text1->get($telephoneNumber);
					echo $telephoneNumber;
				}

				// Address
				if(isset($locationDetailData->address)){
					// Street Address
					$streetAddress = '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><p><span itemprop="streetAddress">' . $text1->get($locationDetailData->address->address2);
					if(isset($locationDetailData->address->address1)){
						$streetAddress .= ' ' . $text1->get($locationDetailData->address->address1);
					}
					$streetAddress .= '</span>';
					
					// ZIP Code
					if(!empty($locationDetailData->address->zip4)){
						$zipCode = $text1->get($locationDetailData->address->zip5) . '-' . $text1->get($locationDetailData->address->zip4);
					}else{
						$zipCode = $text1->get($locationDetailData->address->zip5);
					}
					
					$streetAddress .= '<br><span itemprop="addressLocality">' . $text1->get($locationDetailData->address->city) . '</span>, <span itemprop="addressRegion">' . $text1->get($locationDetailData->address->state_short) . '</span> <span itemprop="postalCode">' . $zipCode . '</span><br>' . $text1->get($locationDetailData->country_short_name) . '</p></div>';
					echo $streetAddress;
				}
				
				// Close Row/Column
				echo '</div>' . "\n";
				if($i == 3){
					// Close Row
					echo '</div>' . "\n";
					$i = 1;
				}else{
					$i++;
				}
			}
			
			// Close Div
			switch($i){
				case 1:
					// No Action Needed
					break;
				case 2:
					if(count($locationData->data) > 1 || empty($locationDetailData->latitude)){
						// Multiple Locations or No Latitude/Longitude
						// Add Two Blank Columns
						echo '<div class="col-md-4"></div>' . "\n";
						echo '<div class="col-md-4"></div>' . "\n";
					}else{
						// Add Map to Right Two Columns
						echo '<div class="col-md-8">' . "\n";
						
						echo '<div id="map"></div>' . "\n";
						echo "<script>function initMap(){var map;var breweryLocation={lat:" . $locationDetailData->latitude . ",lng:" . $locationDetailData->longitude . "};map=new google.maps.Map(document.getElementById('map'),{center:breweryLocation,zoom:12,zoomControl:true,fullscreenControl:false,streetViewControl:false,mapTypeControl:false});var marker=new google.maps.Marker({position:breweryLocation,map:map})}</script>\n";
						echo '</div>' . "\n";
					}
					
					// Close Row
					echo '</div>';
					break;
				case 3:
					// Add One Blank Column
					echo '<div class="col-md-4"></div>' . "\n";
					
					// Close Row
					echo '</div>';
					break;
			}

			// Add Location Button
			$brewerIDString = $text3->get($brewerData->brewer->id);
			echo '<div class="row">' . "\n";
			echo '<div class="col">' . "\n";
			echo '<p><a href="/brewer/' . $brewerIDString . '/add-location" class="btn btn-primary btn-sm"><strong>+</strong> Add Location</a></p>';
			echo '</div>' . "\n";
			echo '</div>' . "\n";
		}else{
			$brewerIDString = $text3->get($brewerData->brewer->id);
			echo '<div class="row">' . "\n";
			echo '<div class="col">' . "\n";
			echo '<h2 style="margin-top:1em;">Location</h2><hr>' . "\n";
			echo '<p class="lead">We don&#8217;t have any locations on file yet for this brewery. Do you know where they have a tasting room? If you do, it&#8217;d be a big help if you could <a href="/brewer/' . $brewerIDString . '/add-location">add it</a>.</p>';
			echo '<p><a href="/brewer/' . $brewerIDString . '/add-location" class="btn btn-primary btn-sm"><strong>+</strong> Add Location</a></p>';
			echo '</div>' . "\n";
			echo '</div>' . "\n";
		}

		// ----- Beer -----
		// Heading
		echo '<div class="row">' . "\n";
		echo '<div class="col">' . "\n";
		echo '<h2 style="margin-top:1em;" id="beer">Beer</h2><hr>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
		
		if(count($brewerData->data) > 0){
			// Column Sizing
			$perColumn = ceil(count($brewerData->data)/3);
			$i = 1;
						
			// First column
			echo '<div class="row">' . "\n";
			echo '<div class="col-md-4">' . "\n";
			
			foreach($brewerData->data as &$beerInfo){
				$beerName = $text1->get($beerInfo->name);
				$beerStyle = $text1->get($beerInfo->style);
				$beerID = $text3->get($beerInfo->id);
				echo '<p><a href="/beer/' . $beerID . '"><span class="lead">' . $beerName . '</span></a><br>' . $beerStyle . '</p>' . "\n";
				
				if($i == $perColumn){
					// New Column
					echo '</div>' . "\n";
					echo '<div class="col-md-4">' . "\n";
					$i = 1;
				}else{
					$i++;
				}
			}
			
			// Close Row and Column
			echo '</div>' . "\n";
			echo '</div>' . "\n";

			// Add Button
			$brewerIDString = $text3->get($brewerData->brewer->id);
			echo '<div class="row">' . "\n";
			echo '<div class="col">' . "\n";
			echo '<p><a href="/beer/add/' . $brewerIDString	 . '" class="btn btn-primary btn-sm"><strong>+</strong> Add beer</a></p>';
			echo '</div>' . "\n";
			echo '</div>' . "\n";
		}else{
			$brewerIDString = $text3->get($brewerData->brewer->id);
			echo '<div class="row">' . "\n";
			echo '<div class="col">' . "\n";
			echo '<p class="lead">Well shucks, we have information about the brewer but nothing about what they brew. Can you help? <a href="/beer/add/' . $brewerIDString	 . '">Add a beer</a></p>';
			echo '<p><a href="/beer/add/' . $brewerIDString	 . '" class="btn btn-primary btn-sm"><strong>+</strong> Add beer</a></p>';
			echo '</div>' . "\n";
			echo '</div>' . "\n";
		}
		?>
  </div>
	<?php
	// Load Google Map?
	if(count($locationData->data) > 0 && !empty($locationDetailData->latitude)){
		// Add Google Maps Javascript
		echo '<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbTNud5BpMY01Z3h5dTpNvdSijQXY4fog&callback=initMap"
    async defer></script>';
	}
	// Load Footer
	 echo $nav->footer();
	?>
</body>
</html>