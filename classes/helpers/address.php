<?php
/**
 * Address form helpers. Required from initialize.php.
 *
 * A location and its address are one thing to the person filling the form in,
 * and two things to the API: POST/PATCH /location carries the name and URL,
 * PUT /address/{location_id} carries the street. location-add.php and
 * location-edit.php both present them as a single form, so the field rendering,
 * the $_POST unpacking and the ZIP splitting live here rather than twice.
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
 * The address fields, empty. The shape every other function here passes around.
 *
 * 'zip' is the combined field the form shows ("97227" or "97227-1234"); zip5 and
 * zip4 are what the API wants. addressFieldsFromPost() splits one into the other.
 */
function addressBlankFields(): array {
    return array(
        'address1' => '',   // unit / suite
        'address2' => '',   // street line — the API's naming, not a typo
        'city' => '',
        'sub_code' => '',   // "US-OR"
        'zip' => '',
        'zip5' => '',
        'zip4' => '',
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
    $fields['address1'] = $address->address1 ?? '';
    $fields['address2'] = $address->address2 ?? '';
    $fields['city'] = $address->city ?? '';
    $fields['sub_code'] = $address->sub_code ?? '';
    $fields['zip5'] = $address->zip5 ?? '';
    $fields['zip4'] = $address->zip4 ?? '';
    $fields['telephone'] = $address->telephone ?? '';

    if(!empty($fields['zip5'])){
        $fields['zip'] = $fields['zip5'];
        if(!empty($fields['zip4'])){
            $fields['zip'] .= '-' . $fields['zip4'];
        }
    }

    return $fields;
}

/**
 * Unpack a submitted form. Splits the single ZIP box into the zip5 / zip4 pair
 * the API stores, taking the digits by position the way the two address pages
 * always have — anything that isn't a ZIP is left for the API to reject.
 */
function addressFieldsFromPost(): array {
    $fields = addressBlankFields();
    $fields['address1'] = $_POST['address1'] ?? '';
    $fields['address2'] = $_POST['address2'] ?? '';
    $fields['city'] = $_POST['city'] ?? '';
    $fields['sub_code'] = $_POST['sub_code'] ?? '';
    $fields['zip'] = $_POST['zip'] ?? '';
    $fields['telephone'] = $_POST['telephone'] ?? '';

    if($fields['zip'] !== ''){
        $fields['zip5'] = substr($fields['zip'], 0, 5);
        if(strlen($fields['zip']) > 5){
            $fields['zip4'] = substr($fields['zip'], 6, 4);
        }
    }

    return $fields;
}

/**
 * Has the user typed anything into the address at all?
 *
 * The form marks street, city and state required, so this is not the normal
 * route through — it's the guard for a legacy location with no address on file
 * whose name or URL is being edited without one being supplied. Sending a blank
 * address to the API would fail validation and block an otherwise fine edit.
 */
function addressHasContent(array $fields): bool {
    foreach(array('address1', 'address2', 'city', 'sub_code', 'zip', 'telephone') as $key){
        if(trim((string)$fields[$key]) !== ''){
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
 *                valid_state/valid_msg carry a 'zip' key mapped from the API's
 *                'zip5', because the form shows one ZIP box rather than two.
 */
function addressPut($api, string $locationID, array $fields): array {
    $putData = array(
        'address1' => $fields['address1'],
        'address2' => $fields['address2'],
        'city' => $fields['city'],
        'sub_code' => $fields['sub_code'],
        'zip5' => $fields['zip5'],
        'zip4' => $fields['zip4'],
        'telephone' => $fields['telephone']
    );

    $response = $api->request('PUT', '/address/' . $locationID, $putData);
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

    $validState = $decoded['valid_state'] ?? array();
    $validMsg = $decoded['valid_msg'] ?? array();
    // The form's single ZIP box wears whatever the API said about zip5.
    $validState['zip'] = $validState['zip5'] ?? '';
    $validMsg['zip'] = $validMsg['zip5'] ?? '';

    return array(
        'error' => true,
        'error_msg' => $decoded['error_msg'] ?? 'Sorry, we couldn\'t save the address.',
        'valid_state' => $validState,
        'valid_msg' => $validMsg
    );
}

/**
 * Render the address controls. Street, city and state are required: an address
 * without them isn't one, and a location without an address is a bare UUID that
 * can't even label itself now that the name is optional. ZIP and telephone stay
 * optional — the API doesn't need either, and Google's validation fills the ZIP
 * in from the rest.
 *
 * @param array $fields      addressBlankFields() shape
 * @param array $validState  per-field state from the API ('' when untouched)
 * @param array $validMsg    per-field message from the API
 * @param bool  $autofocus   put the cursor in the street field
 */
function addressFormFields(array $fields, array $validState, array $validMsg, bool $autofocus = false): string {
    $html = '';

    // Street Address — address2 in the API's naming
    $inputAddress2 = new InputField();
    $inputAddress2->name = 'address2';
    $inputAddress2->description = 'Street Address';
    $inputAddress2->type = 'text';
    $inputAddress2->required = true;
    $inputAddress2->value = $fields['address2'];
    $inputAddress2->autofocus = $autofocus;
    $inputAddress2->validState = $validState['address2'] ?? '';
    $inputAddress2->validMsg = $validMsg['address2'] ?? '';
    $html .= $inputAddress2->display();

    // Unit / Suite — address1
    $inputAddress1 = new InputField();
    $inputAddress1->name = 'address1';
    $inputAddress1->description = 'Unit/Suite';
    $inputAddress1->type = 'text';
    $inputAddress1->required = false;
    $inputAddress1->value = $fields['address1'];
    $inputAddress1->validState = $validState['address1'] ?? '';
    $inputAddress1->validMsg = $validMsg['address1'] ?? '';
    $html .= $inputAddress1->display();

    // City
    $inputCity = new InputField();
    $inputCity->name = 'city';
    $inputCity->description = 'City';
    $inputCity->type = 'text';
    $inputCity->required = true;
    $inputCity->value = $fields['city'];
    $inputCity->validState = $validState['city'] ?? '';
    $inputCity->validMsg = $validMsg['city'] ?? '';
    $html .= $inputCity->display();

    // State
    $dropDown = new DropDown();
    $dropDown->name = 'sub_code';
    $dropDown->values = array('', 'US-AL', 'US-AK', 'US-AZ', 'US-AR', 'US-CA', 'US-CO', 'US-CT', 'US-DE', 'US-DC', 'US-FL', 'US-GA', 'US-HI', 'US-ID', 'US-IL', 'US-IN', 'US-IA', 'US-KS', 'US-KY', 'US-LA', 'US-ME', 'US-MD', 'US-MA', 'US-MI', 'US-MN', 'US-MS', 'US-MO', 'US-MT', 'US-NE', 'US-NV', 'US-NH', 'US-NJ', 'US-NM', 'US-NY', 'US-NC', 'US-ND', 'US-OH', 'US-OK', 'US-OR', 'US-PA', 'US-RI', 'US-SC', 'US-SD', 'US-TN', 'US-TX', 'US-UT', 'US-VT', 'US-VA', 'US-WA', 'US-WV', 'US-WI', 'US-WY');
    $dropDown->descriptions = array('-- Choose --', 'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming');
    $dropDown->label = 'State';
    $dropDown->showLabel = true;
    $dropDown->required = true;
    $dropDown->currentValue = $fields['sub_code'];
    $dropDown->validState = $validState['sub_code'] ?? '';
    $dropDown->validMsg = $validMsg['sub_code'] ?? '';
    $html .= $dropDown->display();

    // ZIP
    $inputZIP = new InputField();
    $inputZIP->name = 'zip';
    $inputZIP->description = 'ZIP Code';
    $inputZIP->type = 'text';
    $inputZIP->required = false;
    $inputZIP->value = $fields['zip'];
    $inputZIP->validState = $validState['zip'] ?? '';
    $inputZIP->validMsg = $validMsg['zip'] ?? '';
    $html .= $inputZIP->display();

    // Telephone
    $inputTelephone = new InputField();
    $inputTelephone->name = 'telephone';
    $inputTelephone->description = 'Telephone Number';
    $inputTelephone->type = 'tel';
    $inputTelephone->required = false;
    $inputTelephone->value = $fields['telephone'];
    $inputTelephone->validState = $validState['telephone'] ?? '';
    $inputTelephone->validMsg = $validMsg['telephone'] ?? '';
    $html .= $inputTelephone->display();

    return $html;
}
