<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Brewer Information
$brewerID = $_GET['brewerID'] ?? '';
$api = new API();
$brewerResp = $api->request('GET', '/brewer/' . $brewerID . '/beer', '');
$brewerData = json_decode($brewerResp);
if(!isset($brewerData->brewer) || isset($brewerData->error)){
    // Invalid Brewer ID or bad API response
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
        // ----- Flash Messages -----
        if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['delete_location_success'])){
            if($_SESSION['delete_location_success']){
                $alert = new Alert();
                $alert->msg = 'Location has been deleted.';
                $alert->type = 'success';
                $alert->dismissible = true;
                echo $alert->display();
                $_SESSION['delete_location_success'] = false;
            }
        }

        // ----- Brewery Info -----
        echo '<div class="row">' . "\n";
        echo '<div class="col-md-12 col-sm-12 col-lg-9 col-xl-7">' . "\n";

        $text1 = new Text(false, true, true);
        $text3 = new Text(false, false, true);
        $brewerName = $text1->get($brewerData->brewer->name);
        echo '<h1 itemprop="name">';
        if(!empty($brewerData->brewer->url)){
            $brewerURL = $text3->get($brewerData->brewer->url);
            echo '<a href="' . $brewerURL . '" target="_blank" rel="noopener" itemprop="url" class="text-decoration-none text-primary">' . $brewerName . '</a>';
        }else{
            echo $brewerName;
        }
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

        $brewerIDString = $text3->get($brewerData->brewer->id);
        if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
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
            echo '<div class="row align-items-center" style="margin-top:1em;">' . "\n";
            echo '<div class="col">' . "\n";
            echo '<h2 id="locations">' . $locationH2 . '</h2>' . "\n";
            echo '</div>' . "\n";
            echo '<div class="col-auto">' . "\n";
            echo '<a href="/brewer/' . $brewerIDString . '/add-location" class="btn btn-primary btn-sm"><strong>+</strong> Add Location</a>';
            echo '</div>' . "\n";
            echo '</div>' . "\n";
            echo '<hr>' . "\n";

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
                $locationIDString = $text3->get($locationInfo->id);
                echo '<h3 itemprop="name">';
                if(!empty($locationDetailData->url)){
                    $locationURL = $text3->get($locationDetailData->url);
                    echo '<a href="' . $locationURL . '" target="_blank" rel="noopener" itemprop="url" class="text-decoration-none text-primary">' . $locationName . '</a>';
                }else{
                    echo $locationName;
                }
                if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
                    echo ' <a href="/location/' . $locationIDString . '/edit" title="Edit Location"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil text-muted" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg></a>';
                    echo ' <a href="/location/' . $locationIDString . '/delete" title="Delete Location"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash text-danger" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg></a>';
                }
                echo '</h3>' . "\n";

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

                    $streetAddress .= '<br><span itemprop="addressLocality">' . $text1->get($locationDetailData->address->city) . '</span>, <span itemprop="addressRegion">' . $text1->get($locationDetailData->address->state_short) . '</span> <span itemprop="postalCode">' . $zipCode . '</span><br>' . $text1->get($locationDetailData->country_short_name);
                    if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
                        $streetAddress .= ' <a href="/location/' . $locationIDString . '/edit-address" title="Edit Address"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil text-muted" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg></a>';
                    }
                    $streetAddress .= '</p></div>';
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

        }else{
            $brewerIDString = $text3->get($brewerData->brewer->id);
            echo '<div class="row align-items-center" style="margin-top:1em;">' . "\n";
            echo '<div class="col">' . "\n";
            echo '<h2>Location</h2>' . "\n";
            echo '</div>' . "\n";
            echo '<div class="col-auto">' . "\n";
            echo '<a href="/brewer/' . $brewerIDString . '/add-location" class="btn btn-primary btn-sm"><strong>+</strong> Add Location</a>';
            echo '</div>' . "\n";
            echo '</div>' . "\n";
            echo '<hr>' . "\n";
            echo '<div class="row">' . "\n";
            echo '<div class="col">' . "\n";
            echo '<p class="lead">We don&#8217;t have any locations on file yet for this brewery. Do you know where they have a tasting room? If you do, it&#8217;d be a big help if you could <a href="/brewer/' . $brewerIDString . '/add-location">add it</a>.</p>';
            echo '</div>' . "\n";
            echo '</div>' . "\n";
        }

        // ----- Beer -----
        // Heading
        echo '<div class="row align-items-center" style="margin-top:1em;">' . "\n";
        echo '<div class="col">' . "\n";
        echo '<h2 id="beer">Beer</h2>' . "\n";
        echo '</div>' . "\n";
        echo '<div class="col-auto">' . "\n";
        echo '<a href="/beer/add/' . $brewerIDString  . '" class="btn btn-primary btn-sm"><strong>+</strong> Add beer</a>';
        echo '</div>' . "\n";
        echo '</div>' . "\n";
        echo '<hr>' . "\n";

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

        }else{
            $brewerIDString = $text3->get($brewerData->brewer->id);
            echo '<div class="row">' . "\n";
            echo '<div class="col">' . "\n";
            echo '<p class="lead">Well shucks, we have information about the brewer but nothing about what they brew. Can you help? <a href="/beer/add/' . $brewerIDString   . '">Add a beer</a></p>';
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