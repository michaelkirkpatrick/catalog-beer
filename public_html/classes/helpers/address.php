<?php
/**
 * Address form helpers. Required from initialize.php.
 *
 * A location and its address are one thing to the person filling the form in,
 * and two things to the API: POST/PATCH /location carries the name, URL and
 * country, PUT /address/{location_id} carries the street. location-add.php and
 * location-edit.php both present them as a single form, so the field rendering,
 * the $_POST unpacking and the reference lists live here rather than twice.
 *
 * The address takes its country from the location (there is no country field
 * on the address resource), so the form's Country select is a LOCATION field
 * that happens to sit above the address. Changing it reloads the page with
 * ?country= so the server can render that country's controls — the region list
 * and the postal-code label differ per country, and a page reload is the one
 * mechanism that needs no JavaScript and no second copy of the subdivision
 * lists in the browser. Once an address exists the API refuses a country
 * change (it validated the address for the country it is in), so the edit form
 * shows the country as fixed text.
 *
 * Why PUT for the address, always: all three verbs land in the same add() method
 * on the API side, which checks whether a row exists and INSERTs or UPDATEs to
 * suit. POST 405s when an address already exists and PATCH 405s when one
 * doesn't, but PUT carries no such guard — it is the one verb that works without
 * the caller having to know which case it's in. That's what lets a single form
 * serve both "adding an address" and "editing one".
 *
 * Note there is no DELETE on /address — the endpoint allows POST, PUT and PATCH
 * only. An address can be corrected but not removed, so a blank address section
 * means "leave whatever is on file alone", never "clear it".
 */

/**
 * The address fields, empty. The shape every other function here passes around;
 * the keys are the API's own (one vocabulary since API 6.0).
 */
function addressBlankFields(): array {
    return array(
        'line1' => '',        // street line
        'line2' => '',        // unit / suite
        'locality' => '',     // city, town, municipality
        'sub_code' => '',     // "US-OR", "BE-VWV"
        'postal_code' => '',  // "97227", "97227-1234", "8640", "1018 AL"
        'telephone' => ''
    );
}

/**
 * Prefill from a decoded location object. A location need not have an address,
 * in which case every field comes back blank.
 *
 * @param object|null $locationData  Decoded GET /location/{id} response
 */
function addressFieldsFromLocation($locationData): array {
    $fields = addressBlankFields();
    if(!isset($locationData->address)){
        return $fields;
    }
    $address = $locationData->address;
    foreach(array_keys($fields) as $key){
        $fields[$key] = (string)($address->{$key} ?? '');
    }
    return $fields;
}

/**
 * Unpack a submitted form. Nothing is split or reshaped here — the API takes
 * the postal code whole and normalises the telephone itself.
 */
function addressFieldsFromPost(): array {
    $fields = addressBlankFields();
    foreach(array_keys($fields) as $key){
        $fields[$key] = (string)($_POST[$key] ?? '');
    }
    return $fields;
}

/**
 * Has the user typed anything into the address at all?
 *
 * The form marks the street and city required, so this is not the normal route
 * through — it's the guard for a legacy location with no address on file whose
 * name or URL is being edited without one being supplied. Sending a blank
 * address to the API would fail validation and block an otherwise fine edit.
 */
function addressHasContent(array $fields): bool {
    foreach($fields as $value){
        if(trim((string)$value) !== ''){
            return true;
        }
    }
    return false;
}

/**
 * Write the address. PUT upserts, so this is the same call whether the location
 * already had an address or not.
 *
 * @return array  ['error' => bool, 'error_msg' => string, 'valid_state' => array, 'valid_msg' => array]
 */
function addressPut($api, string $locationID, array $fields): array {
    $response = $api->request('PUT', '/address/' . $locationID, $fields);
    $decoded = json_decode($response, true);

    if(!is_array($decoded)){
        // Unparseable response — treat as a failure rather than reporting a save
        // that may not have happened.
        return array(
            'error' => true,
            'error_msg' => 'Sorry, we couldn\'t save the address just now. Please try again in a few minutes.',
            'valid_state' => array(),
            'valid_msg' => array()
        );
    }

    if(!isset($decoded['error'])){
        return array('error' => false, 'error_msg' => '', 'valid_state' => array(), 'valid_msg' => array());
    }

    return array(
        'error' => true,
        'error_msg' => $decoded['error_msg'] ?? 'Sorry, we couldn\'t save the address.',
        'valid_state' => $decoded['valid_state'] ?? array(),
        'valid_msg' => $decoded['valid_msg'] ?? array()
    );
}

/**
 * The countries a location may be in — GET /address/countries, cached a day.
 * Each row: country_code, country_short_name, subdivisions (count), calling_code.
 * Keyed by code, in the API's order (alphabetical by code).
 *
 * Empty when the API is unreachable; the caller falls back to a US-only form
 * rather than a form with no country at all.
 */
function addressCountries($api): array {
    $cached = cacheGet('address_countries_v1');
    if(is_array($cached)){
        return $cached;
    }
    $decoded = json_decode($api->request('GET', '/address/countries', ''), true);
    $countries = array();
    foreach(($decoded['data'] ?? array()) as $row){
        if(!empty($row['country_code'])){
            $countries[$row['country_code']] = $row;
        }
    }
    if(!empty($countries)){
        cacheSet('address_countries_v1', $countries, 86400);
    }
    return $countries;
}

/**
 * The subdivisions the catalog holds for one country — GET
 * /address/subdivisions?country=XX, cached a day. Each row: sub_code, name,
 * type, parent_code. Empty for a country with none (or when unreachable), in
 * which case the form shows no region control at all.
 */
function addressSubdivisions($api, string $countryCode): array {
    $countryCode = strtoupper($countryCode);
    $key = 'address_subdivisions_v1_' . $countryCode;
    $cached = cacheGet($key);
    if(is_array($cached)){
        return $cached;
    }
    $decoded = json_decode($api->request('GET', '/address/subdivisions?country=' . rawurlencode($countryCode), ''), true);
    $rows = array();
    foreach(($decoded['data'] ?? array()) as $row){
        if(!empty($row['sub_code'])){
            $rows[] = $row;
        }
    }
    cacheSet($key, $rows, 86400);
    return $rows;
}

/**
 * Pick the location's country for a form: the ?country= override when it is a
 * supported code, else the given default (the brewer's country on add, the
 * location's own on edit), else US.
 */
function addressFormCountry(array $countries, string $default): string {
    $requested = strtoupper(trim((string)($_GET['country'] ?? '')));
    if($requested !== '' && isset($countries[$requested])){
        return $requested;
    }
    $default = strtoupper(trim($default));
    if($default !== '' && (empty($countries) || isset($countries[$default]))){
        return $default;
    }
    return 'US';
}

/**
 * What the form calls each control in this country. The labels follow the
 * country's own vocabulary where it is unambiguous (State, Province, ZIP Code)
 * and a neutral word elsewhere.
 */
function addressFieldLabels(string $countryCode, array $subdivisions): array {
    $regionLabel = 'Region';
    if(!empty($subdivisions)){
        $types = array_values(array_unique(array_map(fn($r) => (string)($r['type'] ?? ''), $subdivisions)));
        $main = $types[0] ?? '';
        $map = array('state' => 'State', 'province' => 'Province', 'canton' => 'Canton', 'region' => 'Region', 'county' => 'County',
            'prefecture' => 'Prefecture', 'land' => 'State', 'district' => 'District', 'department' => 'Department',
            'voivodship' => 'Voivodeship', 'territory' => 'State', 'country' => 'Country', 'municipality' => 'Municipality');
        if($countryCode === 'US'){
            $regionLabel = 'State';
        }elseif($countryCode === 'BE' || $countryCode === 'ES' || $countryCode === 'IT' || $countryCode === 'IE'){
            // Two ISO levels seeded; the lower one is what an address carries.
            $regionLabel = $countryCode === 'IE' ? 'County' : 'Province';
        }elseif(isset($map[$main])){
            $regionLabel = $map[$main];
        }
    }
    return array(
        'region' => $regionLabel,
        'postal' => in_array($countryCode, array('US', 'PR'), true) ? 'ZIP Code' : ($countryCode === 'GB' ? 'Postcode' : 'Postal Code'),
        'locality' => in_array($countryCode, array('GB', 'IE'), true) ? 'Town / City' : 'City',
    );
}

/**
 * The Country select for a location form, reloading the page with ?country=
 * when changed so the address controls can follow. $locked renders the country
 * as fixed text instead — the edit form once an address exists, since the API
 * refuses a country change on a location that holds one.
 *
 * @param array  $countries   addressCountries() rows keyed by code
 * @param string $countryCode the selected code
 * @param array  $validState  per-field state from the API
 * @param array  $validMsg    per-field message from the API
 * @param bool   $locked      show as text, not a control
 */
function addressCountryField(array $countries, string $countryCode, array $validState, array $validMsg, bool $locked = false): string {
    $name = $countries[$countryCode]['country_short_name'] ?? (CountryCode::shortName($countryCode) ?? $countryCode);
    if($locked){
        return '<div class="cbf-field"><div class="cbf-labelrow"><span class="cbf-label">Country</span></div>'
            . '<p class="cbf-static">' . h($name) . '</p>'
            . '<p class="cbf-hint">The address was validated in ' . h($name) . ', so the country can\'t change. For a location in another country, add a new location.</p>'
            . '<input type="hidden" name="country_code" value="' . h($countryCode) . '"></div>' . "\n";
    }

    $values = array_keys($countries);
    if(empty($values)){
        $values = array('US');
    }
    // US first (most of the catalog), then by name.
    $names = array();
    foreach($values as $code){
        $names[$code] = $countries[$code]['country_short_name'] ?? (CountryCode::shortName($code) ?? $code);
    }
    uasort($names, 'strcmp');
    $ordered = array_merge(in_array('US', $values, true) ? array('US') : array(), array_values(array_filter(array_keys($names), fn($c) => $c !== 'US')));

    $dropCountry = new DropDown();
    $dropCountry->name = 'country_code';
    $dropCountry->values = $ordered;
    $dropCountry->descriptions = array_map(fn($c) => $names[$c], $ordered);
    $dropCountry->label = 'Country';
    $dropCountry->showLabel = true;
    $dropCountry->required = true;
    $dropCountry->currentValue = $countryCode;
    $dropCountry->validState = $validState['country_code'] ?? '';
    $dropCountry->validMsg = $validMsg['country_code'] ?? '';
    $dropCountry->hint = 'The address fields below follow the country.';
    suppressAutofill($dropCountry);
    $html = $dropCountry->display();

    // Reload with the chosen country so the region list and labels follow.
    // Progressive: with JS off the select still posts, and the API validates
    // the address for whatever country was chosen.
    $html .= '<script>(function(){var s=document.getElementById("country_codeField");if(!s){return;}s.addEventListener("change",function(){var u=new URL(window.location.href);u.searchParams.set("country",s.value);window.location.assign(u.toString());});})();</script>' . "\n";
    return $html;
}

/**
 * Render the address controls for one country. Street and city are required: an
 * address without them isn't one, and a location without an address is a bare
 * UUID that can't even label itself now that the name is optional. The region
 * is required in the US (the API wants a state beside a city) and optional
 * elsewhere; it is omitted entirely for a country the catalog holds no
 * subdivisions for. Postal code and telephone stay optional — the API doesn't
 * need either, and the validator fills the postal code in from the rest.
 *
 * @param array  $fields       addressBlankFields() shape
 * @param array  $validState   per-field state from the API ('' when untouched)
 * @param array  $validMsg     per-field message from the API
 * No hint under Telephone: the API reads a number in the national format of
 * the location's country or with its country code, and stores E.164 either
 * way, so there is nothing the contributor has to get right.
 *
 * @param string $countryCode  the location's country
 * @param array  $subdivisions addressSubdivisions() rows for that country
 * @param bool   $autofocus    put the cursor in the street field
 */
function addressFormFields(array $fields, array $validState, array $validMsg, string $countryCode, array $subdivisions, bool $autofocus = false): string {
    $html = '';
    $labels = addressFieldLabels($countryCode, $subdivisions);
    $isUS = ($countryCode === 'US');

    /* Every control below opts out of autofill via suppressAutofill()
       (classes/helpers/forms.php holds the how and why): the person filling
       this in is cataloguing a BREWERY's address, not their own, so their
       saved home address is never the right answer — at best noise, at worst
       a contributor's home address in a public database. */

    // Grid (catalog-forms.css): street full width, then Unit/City, then
    // Region/Postal/Telephone. grid2 collapses on its own; grid3 breaks at 560px.

    // Street
    $inputLine1 = new InputField();
    $inputLine1->name = 'line1';
    $inputLine1->description = 'Street Address';
    $inputLine1->type = 'text';
    $inputLine1->required = true;
    $inputLine1->value = $fields['line1'];
    $inputLine1->autofocus = $autofocus;
    $inputLine1->validState = $validState['line1'] ?? '';
    $inputLine1->validMsg = $validMsg['line1'] ?? '';
    // The hook address-autocomplete.js looks for, carrying the country so the
    // suggestions are scoped to it. It also rewrites the label and placeholder
    // once it's running — the server-rendered pair has to stay true for the
    // no-JS case, where this is just a street address box.
    $inputLine1->dataAttributes = array('cb-address-autocomplete' => '', 'cb-country' => strtolower($countryCode));
    suppressAutofill($inputLine1);
    $html .= $inputLine1->display();

    $html .= '<div class="cbf-grid2">' . "\n";

    // Unit / Suite
    $inputLine2 = new InputField();
    $inputLine2->name = 'line2';
    $inputLine2->description = 'Unit/Suite';
    $inputLine2->type = 'text';
    $inputLine2->required = false;
    $inputLine2->value = $fields['line2'];
    $inputLine2->validState = $validState['line2'] ?? '';
    $inputLine2->validMsg = $validMsg['line2'] ?? '';
    suppressAutofill($inputLine2);
    $html .= $inputLine2->display();

    // City
    $inputLocality = new InputField();
    $inputLocality->name = 'locality';
    $inputLocality->description = $labels['locality'];
    $inputLocality->type = 'text';
    $inputLocality->required = true;
    $inputLocality->value = $fields['locality'];
    $inputLocality->validState = $validState['locality'] ?? '';
    $inputLocality->validMsg = $validMsg['locality'] ?? '';
    suppressAutofill($inputLocality);
    $html .= $inputLocality->display();

    $html .= '</div>' . "\n";
    $html .= '<div class="' . (!empty($subdivisions) ? 'cbf-grid3' : 'cbf-grid2') . '">' . "\n";

    // Region — only where the catalog holds subdivisions for the country.
    if(!empty($subdivisions)){
        $values = array('');
        $descriptions = array('-- Choose --');
        foreach($subdivisions as $row){
            $values[] = $row['sub_code'];
            $descriptions[] = $row['name'];
        }
        $dropDown = new DropDown();
        $dropDown->name = 'sub_code';
        $dropDown->values = $values;
        $dropDown->descriptions = $descriptions;
        $dropDown->label = $labels['region'];
        $dropDown->showLabel = true;
        $dropDown->required = $isUS;
        $dropDown->currentValue = $fields['sub_code'];
        $dropDown->validState = $validState['sub_code'] ?? '';
        $dropDown->validMsg = $validMsg['sub_code'] ?? '';
        suppressAutofill($dropDown);
        $html .= $dropDown->display();
    }

    // Postal code
    $inputPostal = new InputField();
    $inputPostal->name = 'postal_code';
    $inputPostal->description = $labels['postal'];
    $inputPostal->type = 'text';
    $inputPostal->required = false;
    $inputPostal->value = $fields['postal_code'];
    $inputPostal->validState = $validState['postal_code'] ?? '';
    $inputPostal->validMsg = $validMsg['postal_code'] ?? '';
    suppressAutofill($inputPostal);
    $html .= $inputPostal->display();

    // Telephone
    $inputTelephone = new InputField();
    $inputTelephone->name = 'telephone';
    $inputTelephone->description = 'Telephone';
    $inputTelephone->type = 'tel';
    $inputTelephone->required = false;
    $inputTelephone->value = $fields['telephone'];
    $inputTelephone->validState = $validState['telephone'] ?? '';
    $inputTelephone->validMsg = $validMsg['telephone'] ?? '';
    suppressAutofill($inputTelephone);
    $html .= $inputTelephone->display();

    $html .= '</div>' . "\n";

    return $html;
}

/**
 * The scripts behind the street field's Places autocomplete, for the foot of
 * location-add.php and location-edit.php. See assets/js/address-autocomplete.js
 * for what it does and why it's in that field.
 *
 * Two tags, in this order: ours defines window.cbAddressAutocompleteInit, then
 * the Maps loader calls it. Ours is deliberately NOT async — it has to have run
 * by the time the callback fires.
 *
 * Returns '' when there's no key to load with, so an environment whose
 * passwords.php predates GOOGLE_PLACES_KEY degrades to the plain form rather
 * than a console full of Google errors. There is deliberately no fallback to
 * GOOGLE_MAPS_KEY: that key is on three public pages, so anyone can read it,
 * and Places sessions cost enough that it shouldn't be able to spend them.
 *
 * GOOGLE_PLACES_KEY must allow BOTH "Places API (New)" and "Maps JavaScript
 * API" — the library is loaded through the Maps JS bootstrap below, so a
 * Places-only restriction 403s the loader before autocomplete ever runs.
 */
function addressAutocompleteScripts(): string {
    if(!defined('GOOGLE_PLACES_KEY') || GOOGLE_PLACES_KEY === ''){
        return '';
    }

    $mapsSrc = 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode(GOOGLE_PLACES_KEY)
        . '&libraries=places&loading=async&callback=cbAddressAutocompleteInit';

    return jsTag('/assets/js/address-autocomplete.js') . "\n"
        . '    <script src="' . h($mapsSrc) . '" async defer></script>';
}
