<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Brewery Map');
echo $htmlHead->html;
?>
<body>
	<style>
		@media only screen and (max-width: 991px) {
			/* Mobile */
			#map {
				height: 400px;
				margin-bottom: 2rem;
			}
		}
		@media only screen and (min-width: 992px) {
			/* Desktop */
			#map {
				height: 600px;
				width: 100%;
			}
		}
	</style>
	<?php echo $nav->navbar('Brewer'); ?>
	<div class="container-fluid">
    <div class="row">
    	<div class="col">
    		<?php
				// Required Classes
				$api = new API();
				$alert = new Alert();

				// Query Map
				$mapResponse = $api->request('GET', '/location/nearby?latitude=32.748482&longitude=-117.130094', '');
				$mapResponse = json_decode($mapResponse);
				if(!isset($mapResponse->error)){
					// Build locations array
					$locations = [];
					for($i=0; $i<count($mapResponse->data); $i++){
						$locations[] = [
							'lat' => (float)$mapResponse->data[$i]->location->latitude,
							'lng' => (float)$mapResponse->data[$i]->location->longitude,
							'name' => $mapResponse->data[$i]->brewer->name
						];
					}
					echo '<div id="map"></div>' . "\n";
					?>
				<script>
				function initMap() {
					var locations = <?php echo json_encode($locations); ?>;
					var map = new google.maps.Map(document.getElementById('map'), { zoom: 4 });
					var bounds = new google.maps.LatLngBounds();
					var markers = locations.map(function(loc) {
						var marker = new google.maps.Marker({
							position: { lat: loc.lat, lng: loc.lng },
							title: loc.name
						});
						bounds.extend(marker.getPosition());
						return marker;
					});
					new markerClusterer.MarkerClusterer({ map: map, markers: markers });
					map.fitBounds(bounds);
				}
				</script>
				<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
				<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_KEY; ?>&callback=initMap" async defer></script>
				<?php
				}else{
					// Error Loading Map
					$alert->msg = $mapResponse->error_msg;
					echo $alert->display();
				}
				?>
			</div>
    </div>
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>
