<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Brewer Information
$brewerID = $_GET['brewerID'] ?? '';
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
			height: 250px;
		}
	}
	@media only screen and (min-width: 992px) {
		/* Desktop */
		.navFloat {
			float:right;
			width: 10rem;
		}
		#map {
			height: 300px;
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
			echo '<img src="/images/brewer-verified.svg" width="20" height="20" class="d-inline-block align-baseline" alt="Brewer Verified" data-bs-toggle="tooltip" data-bs-placement="right" title="Verified by the brewer" style="margin-left:2px;">';
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
		echo '<div>';
		if(!empty($brewerData->brewer->url)){
			// Prep Text
			$urlString = $text3->get($brewerData->brewer->url);

			// Show HTML
			echo '<a href="' . $urlString . '" itemprop="url">Brewer&#8217;s Website <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg></a>';
		}
		echo '</div>';
		$brewerIDString = $text3->get($brewerData->brewer->id);
		if(isset($_SESSION['userID'])){
			echo '<p style="margin-top:1rem;"><a href="/brewer/' . $brewerIDString . '/edit" class="btn btn-outline-secondary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg> Edit Brewer</a></p>';
		}

		// Locations Info
		$locationResp = $api->request('GET', '/brewer/' . $brewerID . '/locations', '');
		$locationData = json_decode($locationResp);
		$locationH2 = 'Location';
		if(isset($locationData->data) && count($locationData->data) > 1){$locationH2 .= 's';}

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
		if(isset($locationData->data) && count($locationData->data) > 0){

			// Section Heading
			echo '<div class="row">' . "\n";
			echo '<div class="col">' . "\n";
			echo '<h2 style="margin-top:1em;" id="locations">' . $locationH2 . '<hr>' . "\n";
			echo '</div>' . "\n";
			echo '</div>' . "\n";

			// Loop Through Locations
			$mapLocations = [];
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

				// Collect coordinates for map
				if(!empty($locationDetailData->latitude) && !empty($locationDetailData->longitude)){
					$mapLocations[] = [
						'lat' => (float)$locationDetailData->latitude,
						'lng' => (float)$locationDetailData->longitude,
						'name' => $locationDetailData->name
					];
				}

				$locationName = $text1->get($locationDetailData->name);
				echo '<h3 itemprop="name">' . $locationName . '</h3>' . "\n";

				// Website
				if(!empty($locationDetailData->url)){
					$locationURL = $text3->get($locationDetailData->url);
					$locationWebsite = '<p><a href="' . $locationURL . '" itemprop="url">Location&#8217;s Webpage <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg></a></p>';
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

				// Edit Links
				if(isset($_SESSION['userID'])){
					$locationIDString = $text3->get($locationInfo->id);
					echo '<p><a href="/location/' . $locationIDString . '/edit" class="btn btn-outline-secondary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg> Edit Location</a>';
					if(isset($locationDetailData->address)){
						echo ' <a href="/location/' . $locationIDString . '/edit-address" class="btn btn-outline-secondary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg> Edit Address</a>';
					}
					echo '</p>' . "\n";
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
					// Add Two Blank Columns
					echo '<div class="col-md-4"></div>' . "\n";
					echo '<div class="col-md-4"></div>' . "\n";

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

			// Map
			if(!empty($mapLocations)){
				echo '<div class="row" style="margin-top:1rem; margin-bottom:1rem;">' . "\n";
				echo '<div class="col">' . "\n";
				echo '<div id="map"></div>' . "\n";
				echo '</div>' . "\n";
				echo '</div>' . "\n";
				?>
				<script>
				function initMap() {
					var locations = <?php echo json_encode($mapLocations); ?>;
					var map = new google.maps.Map(document.getElementById('map'), { zoom: 14 });
					var bounds = new google.maps.LatLngBounds();
					locations.forEach(function(loc) {
						var marker = new google.maps.Marker({
							position: { lat: loc.lat, lng: loc.lng },
							map: map,
							title: loc.name
						});
						var infoWindow = new google.maps.InfoWindow({ content: '<strong>' + loc.name + '</strong>' });
						marker.addListener('click', function() { infoWindow.open(map, marker); });
						bounds.extend(marker.getPosition());
					});
					map.fitBounds(bounds);
					if (locations.length === 1) {
						google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
							map.setZoom(14);
						});
					}
				}
				</script>
				<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_KEY; ?>&callback=initMap" async defer></script>
				<?php
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
	// Load Footer
	 echo $nav->footer();
	?>
</body>
</html>