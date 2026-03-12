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
    <?php echo $nav->navbar('Map'); ?>
    <div class="container-fluid">
    <div class="row">
        <div class="col">
            <?php
                // Required Classes
                $api = new API();
                $alert = new Alert();

                // Query Map
                $mapResponse = $api->request('GET', '/location/map', '');
                $mapResponse = json_decode($mapResponse);
                if(!isset($mapResponse->error)){
                    // Build locations array
                    $locations = [];
                    foreach($mapResponse->data as $loc){
                        $locations[] = [
                            'lat' => (float)$loc->latitude,
                            'lng' => (float)$loc->longitude,
                            'name' => $loc->name,
                            'brewerName' => $loc->brewer->name,
                            'brewerID' => $loc->brewer->id
                        ];
                    }
                    echo '<div id="map"></div>' . "\n";
                    ?>
                <script>
                function initMap() {
                    var locations = <?php echo json_encode($locations); ?>;
                    var map = new google.maps.Map(document.getElementById('map'), { zoom: 4 });
                    var bounds = new google.maps.LatLngBounds();
                    var activeInfoWindow = null;
                    var markers = locations.map(function(loc) {
                        var marker = new google.maps.Marker({
                            position: { lat: loc.lat, lng: loc.lng },
                            title: loc.brewerName
                        });
                        var infoWindow = new google.maps.InfoWindow({
                            content: '<strong><a href="/brewer/' + loc.brewerID + '">' + loc.brewerName + '</a></strong>' +
                                (loc.name !== loc.brewerName ? '<br>' + loc.name : '')
                        });
                        marker.addListener('click', function() {
                            if (activeInfoWindow) activeInfoWindow.close();
                            infoWindow.open(map, marker);
                            activeInfoWindow = infoWindow;
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
