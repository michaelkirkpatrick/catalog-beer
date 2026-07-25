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
// Location names are optional — see the helpers in classes/helpers/location.php.
// Two labels, picked by whether the brewer is already on screen:
//   $locationRawName  standalone "{brewer} – {city}" stand-in — title, meta
//                     description, schema.org name, the map's JSON.
//   $locationRawShort city alone — the h1 and the last breadcrumb, both of which
//                     sit directly under the brewer's own name.
// Raw = unescaped, for json_encode()/htmlspecialchars() consumers.
$locationRawName = locationDisplayName($locationData);
$locationRawShort = locationShortName($locationData);
$locationShort = $text1->get($locationRawShort);
$locationIDString = $text3->get($locationData->id);
$brewerName = $text1->get($locationData->brewer->name);
$brewerIDString = $text3->get($locationData->brewer->id);

// ----- Address -----
// Formatted in classes/helpers/location.php, which brewer.php's single-taproom
// rail shares. Every piece is '' when it isn't on file — a location can exist
// without an address at all.
$addressFacts = locationAddressFacts($locationData, $text1);
$streetLine = $addressFacts['street'];
$cityLine = $addressFacts['city'];
$cityShort = $addressFacts['cityShort'];    // "Portland, OR" — meta description
$telephone = $addressFacts['telephone'];
$telephoneDigits = $addressFacts['telephoneDigits'];

// The same address unescaped, for the map popup (writes with textContent) and
// the maps link (runs through rawurlencode()) — the $text1->get() versions above
// would reach them as literal "&#8217;".
$rawAddressLines = locationRawAddressLines($locationData);

// ----- Map -----
// Lat/lng are optional and default to 0 in the API, which would drop a pin in
// the Atlantic. Only draw the map when we have a real fix.
$hasMap = (!empty($locationData->latitude) && !empty($locationData->longitude));
$mapLatitude = (float)($locationData->latitude ?? 0);
$mapLongitude = (float)($locationData->longitude ?? 0);

// ----- "Open in maps" -----
// The address in the rail doubles as a link into the visitor's mapping app. Two
// hrefs — Google in the markup, Apple swapped in by maps-link.js on Apple
// platforms; see locationMapsLinks() for why.
$mapsLinks = locationMapsLinks($locationData, $locationRawName);
$mapsHref = $mapsLinks['google'];
$mapsHrefApple = $mapsLinks['apple'];

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

// ----- Byline: "A taproom of {brewer}" -----
// No city here — the heading above is the city for an unnamed taproom, and the
// facts rail carries the full address either way.
//
// parentOrganization, not branchOf: branchOf is superseded, and it was only ever
// valid on LocalBusiness, never on the bare Place this page used to declare. The
// value has to be an Organization rather than a URL, hence the nested item. Typed
// Brewery to match how brewer.php declares the same entity on its own page.
$byline = 'A taproom of <a href="/brewer/' . $brewerIDString . '" itemprop="parentOrganization" itemscope itemtype="https://schema.org/Brewery">'
    . '<span itemprop="name">' . $brewerName . '</span>'
    . '<link itemprop="url" href="/brewer/' . $brewerIDString . '" />'
    . '</a>';

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
    <?php
    /* Brewery, not the bare Place this was: Brewery descends from BOTH Place and
       LocalBusiness, so every property already on this page stays valid while the
       Organization side (parentOrganization, telephone, url) becomes legal too.
       A taproom is a place you can walk into and drink the beer, which is exactly
       what the type means. */
    ?>
    <div class="cb-page" style="padding-top:1.25rem;" itemscope itemtype="https://schema.org/Brewery">
        <meta itemprop="publicAccess" content="true" />
        <?php
        // The visible heading is the short label, which for an unnamed taproom is
        // just "Portland" — too thin to publish as the Place's name. The full
        // standalone form carries the brewer, so schema.org gets that instead.
        ?>
        <meta itemprop="name" content="<?php echo htmlspecialchars($locationRawName, ENT_QUOTES); ?>" />
        <?php
        if($hasMap){
            // The coordinates are already on the page as a rendered map; geo states
            // them in a form a machine can read without running the Maps JS.
            echo '<div itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">' . "\n";
            echo '            <meta itemprop="latitude" content="' . htmlspecialchars((string)$mapLatitude, ENT_QUOTES) . '" />' . "\n";
            echo '            <meta itemprop="longitude" content="' . htmlspecialchars((string)$mapLongitude, ENT_QUOTES) . '" />' . "\n";
            echo '        </div>' . "\n";
        }
        if($mapsHref !== ''){
            // hasMap belongs to the Brewery, so it can't live on the address link
            // in the rail — that sits inside the PostalAddress scope.
            echo '        <link itemprop="hasMap" href="' . htmlspecialchars($mapsHref, ENT_QUOTES) . '" />' . "\n";
        }
        ?>
        <?php
        /* BreadcrumbList sits alongside the Brewery rather than inside it — no
           itemprop, so it's a second top-level item. This is the trail search
           engines render under the result, so it annotates the crumbs already on
           screen instead of restating them in a JSON-LD block that could drift. */
        ?>
        <div class="cb-eyebrow" itemscope itemtype="https://schema.org/BreadcrumbList">
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="/brewer"><span itemprop="name">Brewers</span></a><meta itemprop="position" content="1" /></span> &nbsp;/&nbsp;
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="/brewer/<?php echo $brewerIDString; ?>"><span itemprop="name"><?php echo $brewerName; ?></span></a><meta itemprop="position" content="2" /></span> &nbsp;/&nbsp;
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name" aria-current="page"><?php echo $locationShort; ?></span><meta itemprop="position" content="3" /></span>
        </div>

        <header class="lv-hero">
            <h1 class="cb-title lv-title"><?php echo $locationShort;?></h1>
            <p class="lv-byline"><?php echo $byline; ?></p>
        </header>

        <div class="lv-body">
            <div>
                <?php
                if($hasMap){
                    // $rawAddressLines is built once up top and shared with the maps
                    // link and cbMapPopup(); each consumer escapes for itself
                    // (htmlspecialchars here, textContent in the popup).
                    $mapLabel = 'Map showing ' . $locationRawName;
                    if(count($rawAddressLines) > 0){
                        $mapLabel .= ' at ' . implode(', ', $rawAddressLines);
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

                <div class="cb-fact cb-fact--addr" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                    <meta itemprop="addressCountry" content="<?php echo $text1->get($locationData->country_code ?? 'US'); ?>" />
                    <span class="cb-fact__k">Address</span>
                    <address class="cb-addr cb-fact__v cb-fact__v--sm">
                        <?php
                        if($streetLine !== '' || $cityLine !== ''){
                            $addressBlock = '';
                            if($streetLine !== ''){
                                $addressBlock .= '<span class="cb-addr__street" itemprop="streetAddress">' . $streetLine . '</span><br>';
                            }
                            if($cityLine !== ''){
                                $addressBlock .= '<span class="cb-addr__region">' . $cityLine . '</span>';
                            }
                            if($mapsHref !== ''){
                                // The whole address is the link target — on a phone this is
                                // the point of the rail, and a two-line tap target beats a
                                // separate "directions" affordance next to it.
                                echo '<a class="cb-addr__link" data-maps-link href="' . htmlspecialchars($mapsHref, ENT_QUOTES) . '"'
                                    . ' data-apple-href="' . htmlspecialchars($mapsHrefApple, ENT_QUOTES) . '"'
                                    . ' target="_blank" rel="noopener" title="Open this address in Maps">'
                                    . $addressBlock . '</a>';
                            }else{
                                echo $addressBlock;
                            }
                        }else{
                            echo '<span class="cb-addr__region">Not on file</span>';
                            if($loggedIn){
                                // The location editor carries the address now.
                                echo ' <a href="/location/' . $locationIDString . '/edit" class="cb-action">Add</a>';
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
                        // itemprop="url" — the taproom's own page is the canonical URL
                        // for the business in schema.org's sense, not this catalog entry.
                        echo '<div class="cb-fact"><span class="cb-fact__k">Location Info</span><span class="cb-fact__v cb-fact__v--sm"><a href="' . $text3->get($locationData->url) . '" itemprop="url" target="_blank" rel="noopener">' . $text1->get($urlHost) . ' &#8599;</a></span></div>' . "\n";
                    }
                }
                ?>

                <div class="cb-fact"><span class="cb-fact__k">Brewer</span><span class="cb-fact__v cb-fact__v--sm"><a href="/brewer/<?php echo $brewerIDString; ?>"><?php echo $brewerName; ?></a></span></div>

                <?php if($loggedIn){ ?>
                <div class="cb-rail-actions">
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
    <?php if($mapsHrefApple !== ''){ echo jsTag('/assets/js/maps-link.js') . "\n"; } ?>
    <?php if($hasMap){ ?>
    <?php echo jsTag('/assets/js/map-popup.js'); ?>
    <script>
    function initMap() {
        var position = { lat: <?php echo json_encode($mapLatitude); ?>, lng: <?php echo json_encode($mapLongitude); ?> };
        var map = new google.maps.Map(document.getElementById('map'), {
            center: position,
            zoom: 15,
            mapId: <?php echo json_encode(GOOGLE_MAPS_MAP_ID); ?>,
            // Explicit, not the 'auto' default: auto only picks cooperative while
            // the page happens to be scrollable, and flips to greedy — wheel zooms
            // the map, one finger drags it — on a short page or a tall window.
            // Cooperative asks for ctrl/cmd + scroll and two fingers on touch, and
            // the API relaxes it to greedy in fullscreen on its own. Supersedes the
            // legacy scrollwheel flag, which this map used to set.
            gestureHandling: 'cooperative',
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
            // The location's OWN name, not $locationRawName — the composed
            // "{brewer} – {city}" stand-in would print a line that just repeats
            // the brewer line and the address under it.
            content: cbMapPopup({
                name: <?php echo json_encode(trim($locationData->name ?? '')); ?>,
                city: <?php echo json_encode($locationData->address->city ?? ''); ?>,
                brewerID: <?php echo json_encode($locationData->brewer->id); ?>,
                brewerName: <?php echo json_encode($locationData->brewer->name); ?>,
                meta: <?php echo json_encode($rawAddressLines); ?>
            })
        });
        // Closed on load, unlike the brewer and site-wide maps: everything the
        // popup says — brewer, street, city — is already set in the facts rail
        // beside it, so opening it by default just covers the map with a copy of
        // the page. Still there on click for anyone who taps the pin.
        marker.addEventListener('gmp-click', function() { infoWindow.open({ anchor: marker, map: map }); });
    }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_KEY; ?>&libraries=marker&loading=async&callback=initMap" async defer></script>
    <?php } ?>
</body>
</html>
