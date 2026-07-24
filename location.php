<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Location detail — a map-led editorial page for one taproom. The street map is
the hero (this is the one page where "where is it" is the whole question), the
facts rail carries address / phone / web / brewer, and the brewer's other
taprooms sit below as a lateral path.

Two API calls: /location/{id} for the record (it already nests the brewer and
the US address), and /brewer/{id}/locations for the siblings. Both endpoints
are already in production, so this page is deploy-order free.

Design system: composes .cb-* primitives (catalog-components.css); lv-*
classes are page layout only (styles-pages.css). Tokens in catalog.css.
--- */

// Get Location Information
$locationID = $_GET['locationID'] ?? '';
$api = new API();
$locationResp = $api->request('GET', '/location/' . $locationID, '');
$locationData = json_decode($locationResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(!isset($locationData->id) || isset($locationData->error) || !isset($locationData->brewer->id)){
    // Invalid Location ID or bad API response
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Text pipelines
$text1 = new Text(false, true, true);   // display names, short fields
$text3 = new Text(false, false, true);  // ids, URLs

$loggedIn = (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID']));
// Location names are optional — see locationDisplayName(). $locationRawName is
// the unescaped label, for the title, meta description and the map's JSON.
$locationRawName = locationDisplayName($locationData);
$locationName = $text1->get($locationRawName);
$locationIDString = $text3->get($locationData->id);
$brewerName = $text1->get($locationData->brewer->name);
$brewerIDString = $text3->get($locationData->brewer->id);

// ----- Address -----
// address2 is the street line, address1 the unit (see brewer.php). A location
// can exist without an address on file, so every piece below is optional.
$streetLine = '';
$cityLine = '';
$cityShort = '';    // "Portland, OR" — byline + meta description
$cityLong = '';     // "Portland, Oregon" — byline
$telephone = '';
$telephoneDigits = '';
if(isset($locationData->address)){
    $address = $locationData->address;
    if(!empty($address->address2)){
        $streetLine = $text1->get($address->address2);
        if(!empty($address->address1)){
            $streetLine .= ' ' . $text1->get($address->address1);
        }
    }
    if(!empty($address->city)){
        $zipCode = $text1->get($address->zip5 ?? '');
        if(!empty($address->zip4)){
            $zipCode .= '-' . $text1->get($address->zip4);
        }
        $city = $text1->get($address->city);
        $stateShort = $text1->get($address->state_short ?? '');
        $cityLine = '<span itemprop="addressLocality">' . $city . '</span>';
        if($stateShort !== ''){
            $cityLine .= ', <span itemprop="addressRegion">' . $stateShort . '</span>';
            $cityShort = $city . ', ' . $stateShort;
        }else{
            $cityShort = $city;
        }
        if($zipCode !== ''){
            $cityLine .= ' <span itemprop="postalCode">' . $zipCode . '</span>';
        }
        $stateLong = $text1->get($address->state_long ?? '');
        $cityLong = ($stateLong !== '') ? $city . ', ' . $stateLong : $city;
    }
    // Telephone — stored as 10 digits; anything else we pass through unformatted
    $telephoneDigits = strval($address->telephone ?? '');
    if(strlen($telephoneDigits) === 10){
        $telephone = '(' . substr($telephoneDigits, 0, 3) . ') ' . substr($telephoneDigits, 3, 3) . '-' . substr($telephoneDigits, 6, 4);
    }
}

// ----- Map -----
// Lat/lng are optional and default to 0 in the API, which would drop a pin in
// the Atlantic. Only draw the map when we have a real fix.
$hasMap = (!empty($locationData->latitude) && !empty($locationData->longitude));
$mapLatitude = (float)($locationData->latitude ?? 0);
$mapLongitude = (float)($locationData->longitude ?? 0);

// ----- The brewer's other taprooms -----
// The list endpoint carries the full location shape (address included) on
// current API builds; older builds return {id, name} only, in which case the
// city line is simply omitted rather than costing us a fetch per sibling.
$siblings = array();
$siblingResp = $api->request('GET', '/brewer/' . $locationData->brewer->id . '/locations', '');
$siblingData = json_decode($siblingResp);
if(isset($siblingData->data)){
    foreach($siblingData->data as $sibling){
        if(!isset($sibling->id) || $sibling->id === $locationData->id){
            // Skip the location we're already looking at
            continue;
        }
        $siblingCity = '';
        if(isset($sibling->address)){
            $parts = array();
            if(!empty($sibling->address->address2)){$parts[] = $sibling->address->address2;}
            if(!empty($sibling->address->city)){
                $cityPart = $sibling->address->city;
                if(!empty($sibling->address->state_short)){
                    $cityPart .= ', ' . $sibling->address->state_short;
                }
                $parts[] = $cityPart;
            }
            $siblingCity = implode(', ', $parts);
        }
        // An unnamed sibling takes the address line as its label and drops the
        // separate city span — the brewer is already named in the heading above
        // this list, so the usual "{brewer} – {city}" stand-in would just repeat
        // itself twice in one row.
        if(!empty($sibling->name)){
            $siblingName = $sibling->name;
        }elseif($siblingCity !== ''){
            $siblingName = $siblingCity;
            $siblingCity = '';
        }else{
            $siblingName = locationShortName($sibling);
        }
        $siblings[] = array(
            'id' => $text3->get($sibling->id),
            'name' => $text1->get($siblingName),
            'city' => ($siblingCity !== '') ? $text1->get($siblingCity) : ''
        );
    }
}

// ----- Byline: "A taproom of {brewer} · {city}" -----
$byline = 'A taproom of <a href="/brewer/' . $brewerIDString . '" itemprop="branchOf">' . $brewerName . '</a>';
if($cityLong !== ''){
    $byline .= ' &middot; ' . $cityLong;
}

// HTML Head
$htmlHead = new htmlHead($locationRawName . ' — ' . $locationData->brewer->name);
$metaDescription = $locationRawName . ', a taproom of ' . $locationData->brewer->name;
if($cityShort !== ''){
    $metaDescription .= ' in ' . $cityShort;
}
$htmlHead->addDescription($metaDescription . '.');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page" style="padding-top:1.25rem;" itemscope itemtype="http://schema.org/Place">
        <meta itemprop="publicAccess" content="true" />
        <div class="cb-eyebrow">
            <a href="/brewer">Brewers</a> &nbsp;/&nbsp;
            <a href="/brewer/<?php echo $brewerIDString; ?>"><?php echo $brewerName; ?></a> &nbsp;/&nbsp;
            <span aria-current="page"><?php echo $locationName; ?></span>
        </div>

        <header class="lv-hero">
            <h1 class="cb-title lv-title" itemprop="name"><?php echo $locationName;?></h1>
            <p class="lv-byline"><?php echo $byline; ?></p>
        </header>

        <div class="lv-body">
            <div>
                <?php
                if($hasMap){
                    // Raw address parts, reused below by cbMapPopup(). Both consumers
                    // escape for themselves — htmlspecialchars() here, textContent in
                    // the popup — so the $text1->get() versions built above would
                    // double-encode into a literal "&#8217;".
                    $popupMeta = array();
                    if(isset($locationData->address)){
                        $addr = $locationData->address;
                        $rawStreet = '';
                        if(!empty($addr->address2)){
                            $rawStreet = $addr->address2;
                            if(!empty($addr->address1)){
                                $rawStreet .= ' ' . $addr->address1;
                            }
                        }
                        if($rawStreet !== ''){
                            $popupMeta[] = $rawStreet;
                        }
                        if(!empty($addr->city)){
                            $rawCity = $addr->city;
                            if(!empty($addr->state_short)){
                                $rawCity .= ', ' . $addr->state_short;
                            }
                            $popupMeta[] = $rawCity;
                        }
                    }

                    $mapLabel = 'Map showing ' . $locationRawName;
                    if(count($popupMeta) > 0){
                        $mapLabel .= ' at ' . implode(', ', $popupMeta);
                    }
                    // role="region", not role="img": the map now holds keyboard-focusable
                    // markers and a popup with real links, and role="img" would hide all
                    // of that from assistive tech.
                    echo '<div id="map" class="lv-map" role="region" aria-label="' . htmlspecialchars($mapLabel, ENT_QUOTES) . '"></div>' . "\n";
                }
                ?>

                <?php if(count($siblings) > 0){ ?>
                <section class="lv-more">
                    <h2 class="cb-label cb-label--rule">
                        <span>More <?php echo $brewerName; ?> locations</span>
                    </h2>
                    <div>
                        <?php foreach($siblings as $sibling){ ?>
                        <a class="cb-row" href="/location/<?php echo $sibling['id']; ?>">
                            <span class="lv-row-l"><span class="cb-row__name"><?php echo $sibling['name']; ?></span><?php
                            if($sibling['city'] !== ''){
                                echo '<span class="lv-row-city">' . $sibling['city'] . '</span>';
                            }
                            ?></span>
                            <span class="cb-row__value">View &rarr;</span>
                        </a>
                        <?php } ?>
                    </div>
                </section>
                <?php } ?>
            </div>

            <aside class="cb-rail lv-rail">
                <div class="cb-label">Location</div>

                <div class="cb-fact lv-fact-addr" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <meta itemprop="addressCountry" content="<?php echo $text1->get($locationData->country_code ?? 'US'); ?>" />
                    <span class="cb-fact__k">Address</span>
                    <address class="lv-addr cb-fact__v cb-fact__v--sm">
                        <?php
                        if($streetLine !== '' || $cityLine !== ''){
                            if($streetLine !== ''){
                                echo '<span class="lv-street" itemprop="streetAddress">' . $streetLine . '</span><br>';
                            }
                            if($cityLine !== ''){
                                echo '<span class="lv-region">' . $cityLine . '</span>';
                            }
                            if($loggedIn){
                                echo '<a href="/location/' . $locationIDString . '/edit-address" class="lv-edit" title="Edit address"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:baseline;"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg></a>';
                            }
                        }else{
                            echo '<span class="lv-region">Not on file</span>';
                            if($loggedIn){
                                echo ' <a href="/location/' . $locationIDString . '/add-address" class="cb-action">Add</a>';
                            }
                        }
                        ?>
                    </address>
                </div>

                <?php
                if($telephone !== ''){
                    echo '<div class="cb-fact"><span class="cb-fact__k">Phone</span><span class="cb-fact__v cb-fact__v--sm"><a href="tel:+1' . $text3->get($telephoneDigits) . '" itemprop="telephone">' . $telephone . '</a></span></div>' . "\n";
                }

                if(!empty($locationData->url)){
                    $urlHost = parse_url($locationData->url, PHP_URL_HOST);
                    if(!empty($urlHost)){
                        $urlHost = preg_replace('/^www\./', '', $urlHost);
                        echo '<div class="cb-fact"><span class="cb-fact__k">Location Info</span><span class="cb-fact__v cb-fact__v--sm"><a href="' . $text3->get($locationData->url) . '" target="_blank" rel="noopener">' . $text1->get($urlHost) . ' &#8599;</a></span></div>' . "\n";
                    }
                }
                ?>

                <div class="cb-fact"><span class="cb-fact__k">Brewer</span><span class="cb-fact__v cb-fact__v--sm"><a href="/brewer/<?php echo $brewerIDString; ?>"><?php echo $brewerName; ?></a></span></div>

                <?php if($loggedIn){ ?>
                <div class="lv-rail-actions">
                    <a href="/location/<?php echo $locationIDString; ?>/edit" class="cb-btn cb-btn--ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
                        Edit location
                    </a>
                </div>
                <a href="/location/<?php echo $locationIDString; ?>/delete" class="lv-edit lv-delete" title="Delete location">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                    Delete location
                </a>
                <?php } ?>
            </aside>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
    <?php // $popupMeta is built alongside the map div's aria-label above. ?>
    <?php if($hasMap){ ?>
    <?php echo jsTag('/assets/js/map-popup.js'); ?>
    <script>
    function initMap() {
        var position = { lat: <?php echo json_encode($mapLatitude); ?>, lng: <?php echo json_encode($mapLongitude); ?> };
        var map = new google.maps.Map(document.getElementById('map'), {
            center: position,
            zoom: 15,
            scrollwheel: false,
            mapId: <?php echo json_encode(GOOGLE_MAPS_MAP_ID); ?>,
            zoomControl: true,          // explicit: the API hides controls under 200x200
            cameraControl: false,       // drops the N/S/E/W pan arrows
            streetViewControl: false,   // no pegman
            mapTypeControl: true,
            fullscreenControl: true,
            // Vector maps let users pitch and spin the map. Set in code, these beat the
            // Map ID's cloud configuration; delete the three lines to hand control back
            // to the console (where tilt/rotate is already enabled).
            tiltInteractionEnabled: false,
            headingInteractionEnabled: false,
            rotateControl: false
        });
        var marker = new google.maps.marker.AdvancedMarkerElement({
            position: position,
            map: map,
            title: <?php echo json_encode($locationRawName); ?>,
            gmpClickable: true
        });
        var infoWindow = new google.maps.InfoWindow({
            // No id passed on purpose: this IS the location page, so the taproom
            // name shouldn't be a link back to itself. The brewer still links out.
            content: cbMapPopup({
                name: <?php echo json_encode($locationRawName); ?>,
                city: <?php echo json_encode($locationData->address->city ?? ''); ?>,
                brewerID: <?php echo json_encode($locationData->brewer->id); ?>,
                brewerName: <?php echo json_encode($locationData->brewer->name); ?>,
                meta: <?php echo json_encode($popupMeta); ?>
            })
        });
        infoWindow.open({ anchor: marker, map: map });
        marker.addEventListener('gmp-click', function() { infoWindow.open({ anchor: marker, map: map }); });
    }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_KEY; ?>&libraries=marker&loading=async&callback=initMap" async defer></script>
    <?php } ?>
</body>
</html>
