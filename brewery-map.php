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
        <?php
            // Required Classes
            $api = new API();
            $alert = new Alert();

            // Query Map
            $mapResponse = $api->request('GET', '/location/map', '');
            $mapResponse = json_decode($mapResponse);
            if($api->unavailable()){
                // Backend unreachable. Output (head + navbar) has already
                // started, so we can't serve a 503 page here — show an inline
                // notice instead of dereferencing a null response.
                $alert->msg = 'Sorry, we couldn\'t load the map right now because we\'re having trouble connecting. Please try again in a few minutes.';
                $alert->type = 'warning';
                echo $alert->display();
            }elseif(!isset($mapResponse->error) && isset($mapResponse->data)){
                // Build locations array
                $locations = [];
                foreach($mapResponse->data as $loc){
                    // Raw values, not $text1->get() output: cbMapPopup() writes
                    // these with textContent, which escapes for us — pre-encoded
                    // entities would show through as literal "&#8217;".
                    $locations[] = [
                        'lat' => (float)$loc->latitude,
                        'lng' => (float)$loc->longitude,
                        'id' => $loc->id,
                        // Site-wide map: the brewer isn't implied by context,
                        // so an unnamed location takes the full stand-in. The
                        // map payload carries no address, so it resolves to
                        // the brewer name alone.
                        'name' => locationDisplayName($loc, $loc->brewer->name),
                        'brewerName' => $loc->brewer->name,
                        'brewerID' => $loc->brewer->id
                    ];
                }
                echo '<div id="map"></div>' . "\n";
                echo '                ' . jsTag('/assets/js/map-popup.js') . "\n";
                ?>
            <script>
            function initMap() {
                var locations = <?php echo json_encode($locations); ?>;
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 4,
                    mapId: <?php echo json_encode(GOOGLE_MAPS_MAP_ID); ?>,
                    zoomControl: true,          // explicit: the API hides controls under 200x200
                    cameraControl: false,       // drops the N/S/E/W pan arrows
                    streetViewControl: false,   // no pegman
                    mapTypeControl: true,
                    fullscreenControl: true,
                    // Vector maps let users pitch and spin the map. Set in code, these
                    // beat the Map ID's cloud configuration; delete the three lines to
                    // hand control back to the console (tilt/rotate is enabled there).
                    tiltInteractionEnabled: false,
                    headingInteractionEnabled: false,
                    rotateControl: false
                });
                var bounds = new google.maps.LatLngBounds();
                var activeInfoWindow = null;
                var markers = locations.map(function(loc) {
                    var position = { lat: loc.lat, lng: loc.lng };
                    var marker = new google.maps.marker.AdvancedMarkerElement({
                        position: position,
                        title: loc.brewerName,
                        gmpClickable: true
                    });
                    var infoWindow = new google.maps.InfoWindow({ content: cbMapPopup(loc) });
                    marker.addEventListener('gmp-click', function() {
                        if (activeInfoWindow) activeInfoWindow.close();
                        infoWindow.open({ anchor: marker, map: map });
                        activeInfoWindow = infoWindow;
                    });
                    bounds.extend(position);
                    return marker;
                });
                new markerClusterer.MarkerClusterer({ map: map, markers: markers });
                map.fitBounds(bounds);
            }
            </script>
            <?php // Clustering is not in the Maps JS API; this is the library Google's own
                  // clustering sample imports, vendored locally so the page carries no
                  // third-party runtime dependency. Provenance, license and upgrade
                  // steps live in the file's header. Must load before initMap runs. ?>
            <?php echo jsTag('/assets/js/markerclusterer-2.6.2.js'); ?>
            <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_KEY; ?>&libraries=marker&loading=async&callback=initMap" async defer></script>
            <?php
            }else{
                // Error Loading Map
                $alert->msg = $mapResponse->error_msg;
                echo $alert->display();
            }
            ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
