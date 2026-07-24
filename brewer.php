<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Brewer detail — "One Scroll". A single vertical read: about → taprooms →
beers, with a sticky facts rail on the right. Two API calls: the beer list
(which carries the brewer object) and the expanded locations list. The beer
list stays scannable at scale via a client-side toolbar (search + sort +
style-family chips) over rows grouped by style family — largest group first,
A–Z within. Per-beer provenance dots (brewer-provided / Catalog.beer
verified) render only when at least one beer on the page is verified.

Design system: composes .cb-* primitives (catalog-components.css); bp-*
classes are page layout only (styles-pages.css). Tokens in catalog.css.
--- */

// Get Brewer Information
$brewerID = $_GET['brewerID'] ?? '';
$api = new API();
$brewerResp = $api->request('GET', '/brewer/' . $brewerID . '/beer', '');
$brewerData = json_decode($brewerResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(!isset($brewerData->brewer) || isset($brewerData->error)){
    // Invalid Brewer ID or bad API response
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Text pipelines
$text1 = new Text(false, true, true);   // display names, short fields
$text2 = new Text(true, true, false);   // multi-paragraph prose (Markdown)
$text3 = new Text(false, false, true);  // ids, URLs

$loggedIn = (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID']));
$brewerName = $text1->get($brewerData->brewer->name);
$brewerIDString = $text3->get($brewerData->brewer->id);

// ----- Locations -----
$locationResp = $api->request('GET', '/brewer/' . $brewerID . '/locations', '');
$locationData = json_decode($locationResp);
$locations = array();
if(isset($locationData->data)){
    foreach($locationData->data as $locItem){
        if(!property_exists($locItem, 'latitude')){
            // Old list shape ({id, name} only) — the deployed API predates the
            // expanded /brewer/{id}/locations payload. Fall back to the
            // per-location detail fetch so the page still renders fully.
            $locationDetailResp = $api->request('GET', '/location/' . $locItem->id, '');
            $locationDetail = json_decode($locationDetailResp);
            // Guard on id, not name: location names are optional, and isset()
            // is false for a null one — checking name here would discard a
            // perfectly good unnamed location and log it as a failed fetch.
            if(!isset($locationDetail->id) || isset($locationDetail->error)){
                // The location detail request failed or came back unusable.
                // Skip this location; the rest of the page is still worth showing.
                $errorLog = new LogError();
                $errorLog->errorNumber = 'C22';
                $errorLog->errorMsg = 'Unable to load location details';
                $errorLog->badData = "brewerID: $brewerID\nlocationID: " . $locItem->id . "\nhttpcode: " . $api->httpcode . "\nresponse: " . var_export($locationDetailResp, true);
                $errorLog->filename = 'brewer.php';
                $errorLog->write();
                continue;
            }
            $locItem = $locationDetail;
        }
        $locations[] = $locItem;
    }
}
$locationCount = count($locations);
$taproomsLabel = ($locationCount === 1) ? 'Taproom' : 'Taprooms';

// Map pins; the map band renders only for multi-taproom breweries
$mapLocations = array();
foreach($locations as $loc){
    if(!empty($loc->latitude) && !empty($loc->longitude)){
        // Raw values, not $text1->get() output: cbMapPopup() writes these with
        // textContent, which escapes for us — pre-encoded entities would show
        // through as literal "&#8217;".
        $mapLocations[] = array(
            'lat' => (float)$loc->latitude,
            'lng' => (float)$loc->longitude,
            'id' => $loc->id,
            // The popup names the brewer on its own line, so an unnamed
            // taproom is labelled by its city here too.
            'name' => locationShortName($loc),
            'brewerID' => $brewerData->brewer->id,
            'brewerName' => $brewerData->brewer->name,
            'city' => $loc->address->city ?? ''
        );
    }
}
$showMap = ($locationCount > 1 && count($mapLocations) > 1);

// ----- Beers: group by style family -----
$beerCount = isset($brewerData->data) ? count($brewerData->data) : 0;
$hasAbv = false;
$hasVerifiedBeer = false;
$beerGroups = array();  // family slug => ['label' => ..., 'beers' => [...]]
if($beerCount > 0){
    foreach($brewerData->data as $beerInfo){
        $familySlug = !empty($beerInfo->parent) ? $beerInfo->parent : 'other';
        if(!isset($beerGroups[$familySlug])){
            $beerGroups[$familySlug] = array(
                'label' => ($familySlug === 'other') ? 'Other' : StyleList::parentName($familySlug),
                'beers' => array()
            );
        }
        $beerGroups[$familySlug]['beers'][] = $beerInfo;
        if(!empty($beerInfo->abv)){$hasAbv = true;}
        if(!empty($beerInfo->cb_verified) || !empty($beerInfo->brewer_verified)){$hasVerifiedBeer = true;}
    }
    // Largest family first (no popularity data — this surfaces what the
    // brewery makes most of without asserting a ranking); ties A–Z by label.
    // Within each group the API already returns beers A–Z by name.
    uasort($beerGroups, function($a, $b){
        return (count($b['beers']) <=> count($a['beers'])) ?: strcasecmp($a['label'], $b['label']);
    });
}
// Toolbar + above-the-fold cap only earn their keep on long lists. The cap
// never cuts mid-group: whole groups render up to ~10 rows, the rest sit in
// groups the JS collapses behind "Show all n beers" (no-JS shows everything).
$showToolbar = ($beerCount > 12);
$capRows = 10;

// HTML Head
$htmlHead = new htmlHead($brewerData->brewer->name);
if(!empty($brewerData->brewer->short_description)){
    // Add meta-description
    $htmlHead->addDescription($brewerData->brewer->short_description);
}
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page" style="padding-top:1.25rem;" itemscope itemtype="http://schema.org/Brewery">
        <?php
        // Flash message
        if($loggedIn && !empty($_SESSION['delete_location_success'])){
            $alert = new Alert();
            $alert->msg = 'Location has been deleted.';
            $alert->type = 'success';
            $alert->dismissible = true;
            echo $alert->display();
            $_SESSION['delete_location_success'] = false;
        }
        ?>
        <div class="cb-eyebrow">
            <a href="/brewer">Brewers</a> &nbsp;/&nbsp; <span aria-current="page"><?php echo $brewerName; ?></span>
        </div>

        <div class="bp-body">
            <div>
                <header class="bp-hero">
                    <h1 class="cb-title bp-title" itemprop="name"><?php
                    if(!empty($brewerData->brewer->url)){
                        $brewerURL = $text3->get($brewerData->brewer->url);
                        echo '<a href="' . $brewerURL . '" target="_blank" rel="noopener" itemprop="url">' . $brewerName . '</a>';
                    }else{
                        echo $brewerName;
                    }
                    ?></h1>
                    <?php
                    if(!empty($brewerData->brewer->short_description)){
                        echo '<p class="cb-lede bp-lede">' . $text1->get($brewerData->brewer->short_description) . '</p>';
                    }
                    ?>
                </header>

                <?php
                // About
                if(!empty($brewerData->brewer->description)){
                    echo '<div class="cb-prose bp-prose" itemprop="description">';
                    echo $text2->get($brewerData->brewer->description);
                    echo '</div>';
                }
                if($loggedIn){
                    echo '<p style="margin-top:1rem;"><a href="/brewer/' . $brewerIDString . '/edit" class="btn btn-outline-secondary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg> Edit Brewer</a></p>';
                }
                ?>

                <h2 class="cb-label cb-label--rule bp-sec" id="locations">
                    <span><?php echo $taproomsLabel; if($locationCount > 0){ echo ' &middot; ' . $locationCount; } ?></span>
                    <a href="/brewer/<?php echo $brewerIDString; ?>/add-location" class="cb-action"><strong>+</strong> Add Location</a>
                </h2>
                <?php if($locationCount > 0){ ?>
                <?php if($showMap){ echo '<div id="map" class="bp-map"></div>' . "\n"; } ?>
                <div class="bp-loc-grid">
                    <?php foreach($locations as $loc){
                        // Short form: we're already on the brewer's page, so an
                        // unnamed taproom is labelled by its city alone.
                        $locationName = $text1->get(locationShortName($loc));
                        $locationIDString = $text3->get($loc->id);
                        ?>
                    <div class="cb-card bp-loc-card" itemprop="location" itemscope itemtype="http://schema.org/Place"><meta itemprop="publicAccess" content="true" />
                        <h3 class="bp-loc-name" itemprop="name"><?php
                            // Link the location name to its detail page.
                            echo '<a href="/location/' . $locationIDString . '" itemprop="url">' . $locationName . '</a>';
                            if($loggedIn){
                                echo ' <a href="/location/' . $locationIDString . '/edit" title="Edit Location"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil text-muted" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg></a>';
                                echo ' <a href="/location/' . $locationIDString . '/delete" title="Delete Location"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash text-danger" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg></a>';
                            }
                        ?></h3>
                        <div class="bp-loc-meta">
                            <?php
                            if(isset($loc->address)){
                                // Street Address (address2 is the street line; address1 the unit)
                                echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><meta itemprop="addressCountry" content="' . $text1->get($loc->country_code ?? 'US') . '" /><p><span itemprop="streetAddress">' . $text1->get($loc->address->address2);
                                if(!empty($loc->address->address1)){
                                    echo ' ' . $text1->get($loc->address->address1);
                                }
                                echo '</span><br>';
                                if(!empty($loc->address->zip4)){
                                    $zipCode = $text1->get($loc->address->zip5) . '-' . $text1->get($loc->address->zip4);
                                }else{
                                    $zipCode = $text1->get($loc->address->zip5);
                                }
                                echo '<span itemprop="addressLocality">' . $text1->get($loc->address->city) . '</span>, <span itemprop="addressRegion">' . $text1->get($loc->address->state_short) . '</span> <span itemprop="postalCode">' . $zipCode . '</span>';
                                if($loggedIn){
                                    echo ' <a href="/location/' . $locationIDString . '/edit-address" title="Edit Address"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil text-muted" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg></a>';
                                }
                                echo '</p></div>';

                                // Telephone
                                $telephone = strval($loc->address->telephone ?? '');
                                if(strlen($telephone) === 10){
                                    echo '<p itemprop="telephone">(' . substr($telephone, 0, 3) . ') ' . substr($telephone, 3, 3) . '-' . substr($telephone, 6, 4) . '</p>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php }else{ ?>
                <p class="lead">We don&#8217;t have any locations on file yet for this brewery. Do you know where they have a tasting room? If you do, it&#8217;d be a big help if you could <a href="/brewer/<?php echo $brewerIDString; ?>/add-location">add it</a>.</p>
                <?php } ?>

                <h2 class="cb-label cb-label--rule bp-sec" id="beer">
                    <span>Beers<?php if($beerCount > 0){ echo ' &middot; ' . $beerCount; } ?></span>
                    <a href="/beer/add/<?php echo $brewerIDString; ?>" class="cb-action"><strong>+</strong> Add beer</a>
                </h2>
                <?php if($beerCount > 0){ ?>
                <?php if($showToolbar){ ?>
                <div class="cb-toolbar bp-toolbar" id="bpToolbar" hidden>
                    <input type="search" class="cb-search" id="bpSearch" placeholder="Search beers&#8230;" aria-label="Search beers">
                    <select class="cb-select" id="bpSort" aria-label="Sort beers">
                        <option value="style">Sort: Style</option>
                        <option value="az">Sort: A&#8211;Z</option>
                        <?php if($hasAbv){ echo '<option value="abv">Sort: ABV</option>'; } ?>
                    </select>
                </div>
                <div class="cb-toolbar bp-toolbar" id="bpChips" hidden>
                    <button type="button" class="cb-chip is-on" data-family="">All <span class="cb-count"><?php echo $beerCount; ?></span></button>
                    <?php foreach($beerGroups as $familySlug => $group){
                        echo '<button type="button" class="cb-chip" data-family="' . $text3->get($familySlug) . '">' . $text1->get($group['label']) . ' <span class="cb-count">' . count($group['beers']) . '</span></button>' . "\n";
                    } ?>
                </div>
                <?php } ?>
                <div id="bpGroups">
                    <?php
                    $rowsSoFar = 0;
                    foreach($beerGroups as $familySlug => $group){
                        $groupCount = count($group['beers']);
                        // Whole groups above the fold until the cap; never cut mid-group
                        $capped = ($showToolbar && $rowsSoFar >= $capRows) ? ' data-capped="1"' : '';
                        $rowsSoFar += $groupCount;
                        echo '<section class="bp-grp" data-family="' . $text3->get($familySlug) . '"' . $capped . '>' . "\n";
                        echo '<h3 class="bp-grp-h">' . $text1->get($group['label']) . ' <span class="cb-count cb-count--bare">' . $groupCount . '</span></h3>' . "\n";
                        echo '<div class="bp-grp-rows">' . "\n";
                        foreach($group['beers'] as $beerInfo){
                            $beerName = $text1->get($beerInfo->name);
                            $beerStyle = $text1->get($beerInfo->style);
                            $beerIDString = $text3->get($beerInfo->id);
                            $beerAbv = !empty($beerInfo->abv) ? floatval($beerInfo->abv) : 0;
                            $abvLabel = ($beerAbv > 0) ? rtrim(rtrim(number_format($beerAbv, 1), '0'), '.') . '%' : '';
                            echo '<a class="cb-row" href="/beer/' . $beerIDString . '" data-name="' . htmlspecialchars(mb_strtolower($beerInfo->name . ' ' . $beerInfo->style), ENT_QUOTES) . '" data-abv="' . $beerAbv . '">';
                            echo '<span class="bp-beer-l">';
                            if($hasVerifiedBeer){
                                if(!empty($beerInfo->brewer_verified)){
                                    echo '<span class="cb-vdot cb-vdot--first" title="Brewer-provided"></span>';
                                }elseif(!empty($beerInfo->cb_verified)){
                                    echo '<span class="cb-vdot cb-vdot--cbv" title="Catalog.beer verified"></span>';
                                }
                            }
                            echo '<span class="cb-row__name">' . $beerName . '</span> <span class="cb-row__meta">' . $beerStyle . '</span></span>';
                            echo '<span class="cb-row__value">' . $abvLabel . '</span>';
                            echo '</a>' . "\n";
                        }
                        echo '</div>' . "\n";
                        echo '</section>' . "\n";
                    }
                    ?>
                </div>
                <div id="bpFlat" hidden></div>
                <?php if($showToolbar){ ?>
                <button type="button" class="cb-note" id="bpShowAll" hidden>Show all <?php echo $beerCount; ?> beers &#9662;</button>
                <?php } ?>
                <?php if($hasVerifiedBeer){ ?>
                <div class="cb-legend"><span><span class="cb-vdot cb-vdot--first"></span>Brewer-provided</span><span><span class="cb-vdot cb-vdot--cbv"></span>Catalog.beer verified</span><span class="cb-legend__none">no mark &#8212; unverified</span></div>
                <?php } ?>
                <?php }else{ ?>
                <p class="lead">Well shucks, we have information about the brewer but nothing about what they brew. Can you help? <a href="/beer/add/<?php echo $brewerIDString; ?>">Add a beer</a></p>
                <?php } ?>
            </div>

            <aside class="cb-rail bp-rail">
                <div class="cb-label">Facts</div>
                <div class="cb-fact"><span class="cb-fact__k"><?php echo $taproomsLabel; ?></span><span class="cb-fact__v"><?php echo $locationCount; ?></span></div>
                <div class="cb-fact"><span class="cb-fact__k">Beers</span><span class="cb-fact__v"><?php echo $beerCount; ?></span></div>
                <?php
                if(!empty($brewerData->brewer->url)){
                    $urlHost = parse_url($brewerData->brewer->url, PHP_URL_HOST);
                    if(!empty($urlHost)){
                        $urlHost = preg_replace('/^www\./', '', $urlHost);
                        echo '<div class="cb-fact"><span class="cb-fact__k">Website</span><span class="cb-fact__v cb-fact__v--sm"><a href="' . $text3->get($brewerData->brewer->url) . '" target="_blank" rel="noopener">' . $text1->get($urlHost) . ' &#8599;</a></span></div>';
                    }
                }
                ?>
            </aside>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
    <?php if($showMap){ ?>
    <?php echo jsTag('/assets/js/map-popup.js'); ?>
    <script>
    function initMap() {
        var locations = <?php echo json_encode($mapLocations); ?>;
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 14,
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
        var bounds = new google.maps.LatLngBounds();
        locations.forEach(function(loc) {
            var position = { lat: loc.lat, lng: loc.lng };
            var marker = new google.maps.marker.AdvancedMarkerElement({
                position: position,
                map: map,
                title: loc.name,
                gmpClickable: true
            });
            var infoWindow = new google.maps.InfoWindow({ content: cbMapPopup(loc) });
            marker.addEventListener('gmp-click', function() { infoWindow.open({ anchor: marker, map: map }); });
            bounds.extend(position);
        });
        map.fitBounds(bounds);
        if (locations.length === 1) {
            google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                map.setZoom(14);
            });
        }
    }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_KEY; ?>&libraries=marker&loading=async&callback=initMap" async defer></script>
    <?php } ?>
    <?php if($showToolbar && $beerCount > 0){ ?>
    <script>
    (function() {
        // Beer list toolbar: search + sort + style-family chips + fold cap.
        // Pure progressive enhancement over the server-rendered rows — with
        // JS off, everything above stays visible and complete.
        var groups = Array.prototype.slice.call(document.querySelectorAll('.bp-grp'));
        var rows = [];
        groups.forEach(function(g) {
            g._rowsBox = g.querySelector('.bp-grp-rows');
            g._rows = Array.prototype.slice.call(g.querySelectorAll('.cb-row'));
            rows = rows.concat(g._rows);
        });
        var flat = document.getElementById('bpFlat');
        var search = document.getElementById('bpSearch');
        var sort = document.getElementById('bpSort');
        var chips = Array.prototype.slice.call(document.querySelectorAll('#bpChips .cb-chip'));
        var showAll = document.getElementById('bpShowAll');
        var expanded = false;

        function apply() {
            var q = search.value.trim().toLowerCase();
            var chipOn = chips.filter(function(c) { return c.classList.contains('is-on'); })[0];
            var family = chipOn ? chipOn.getAttribute('data-family') : '';
            var mode = sort.value;
            // Any narrowing or re-sorting reveals the capped tail — hiding
            // matches behind "Show all" would read as missing results.
            if((q || family || mode !== 'style') && !expanded) { expand(); }

            function match(r) {
                if(q && r.getAttribute('data-name').indexOf(q) === -1) { return false; }
                if(family && r._family !== family) { return false; }
                return true;
            }
            if(mode === 'style') {
                flat.hidden = true;
                groups.forEach(function(g) {
                    var visible = 0;
                    g._rows.forEach(function(r) {
                        var show = match(r);
                        r.hidden = !show;
                        if(show) { visible++; }
                        g._rowsBox.appendChild(r);
                    });
                    g.hidden = (visible === 0) || (!expanded && g.hasAttribute('data-capped'));
                });
            } else {
                groups.forEach(function(g) { g.hidden = true; });
                flat.hidden = false;
                var sorted = rows.slice().sort(mode === 'az'
                    ? function(a, b) { return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name')); }
                    : function(a, b) { return parseFloat(a.getAttribute('data-abv')) - parseFloat(b.getAttribute('data-abv')); });
                sorted.forEach(function(r) {
                    r.hidden = !match(r);
                    flat.appendChild(r);
                });
            }
        }
        function expand() {
            expanded = true;
            showAll.hidden = true;
            groups.forEach(function(g) { g.hidden = false; });
        }

        rows.forEach(function(r) {
            var grp = r.closest('.bp-grp');
            r._family = grp ? grp.getAttribute('data-family') : '';
        });
        // Apply the above-the-fold cap (server renders everything visible)
        var capped = groups.filter(function(g) { return g.hasAttribute('data-capped'); });
        if(capped.length > 0) {
            capped.forEach(function(g) { g.hidden = true; });
            showAll.hidden = false;
            showAll.addEventListener('click', expand);
        }
        document.getElementById('bpToolbar').hidden = false;
        document.getElementById('bpChips').hidden = false;
        search.addEventListener('input', apply);
        sort.addEventListener('change', apply);
        chips.forEach(function(c) {
            c.addEventListener('click', function() {
                chips.forEach(function(o) { o.classList.remove('is-on'); });
                c.classList.add('is-on');
                apply();
            });
        });
    })();
    </script>
    <?php } ?>
</body>
</html>
