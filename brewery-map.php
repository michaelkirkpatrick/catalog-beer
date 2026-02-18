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
				height: 200px;
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
				
				// Latitude Longitude
				$latitude = 32.748482;
				$longitude = -117.130094;
				
				// Query Map
				$mapResponse = $api->request('GET', '/location/nearby?latitude=' . $latitude . '&longitude=' . $longitude, '');
				$mapResponse = json_decode($mapResponse);
				if(!isset($mapResponse->error)){
					// Display Breweries
					// Successfully Added
					echo '<script src="https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js"></script>';
					echo '<div id="map"></div>' . "\n";
					// Initalize Map
					?>
				<script>
					mapkit.init({
							authorizationCallback: function (done) {
									var xhr = new XMLHttpRequest();
									xhr.open("GET", "/jwt-token.php");
									xhr.addEventListener("load", function () {
											done(this.responseText)
									});
									xhr.send()
							}
					});
					var map = new mapkit.Map("map");
					var showsUserLocation=true;
					<?php
					// Display Breweries
					for($i=0; $i<count($mapResponse->data); $i++){
						echo 'var brewery' . $i . '=new mapkit.Coordinate(' . $mapResponse->data[$i]->location->latitude . ',' . $mapResponse->data[$i]->location->longitude . ');' . "\n";
						echo 'var breweryAnnotation' . $i . '=new mapkit.MarkerAnnotation(brewery' . $i . ',{title:"' . $mapResponse->data[$i]->brewer->name . '",clusteringIdentifier:"Brewery"});' . "\n" . 'map.showItems(breweryAnnotation' . $i . ');' . "\n";
					}
					
					// Close Map
					echo 'var BreweryLocation=new mapkit.CoordinateRegion(new mapkit.Coordinate(' . $latitude . ',' . $longitude . '),new mapkit.CoordinateSpan(0.01,0.01));' . "\n" . 'map.region=BreweryLocation;' . "\n";
					echo '</script>' . "\n";	
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