<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

/* ---
Edit a location — name, URL and address in one form, and one request.

PATCH /location/{id} takes the address nested and merges it over the stored
one, re-validating the whole as one (API 6.0). A blank address section is
left out of the request entirely: the API has no way to express "remove the
address" from a form that was simply left empty, and a location that never
had one shouldn't be held hostage to it when only the name is being edited.
--- */

// Get Location ID
$locationID = $_GET['locationID'] ?? '';

// Fetch Existing Location Data
$api = new API();
$locationResp = $api->request('GET', '/location/' . $locationID, '');
$locationData = json_decode($locationResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(isset($locationData->error) || !isset($locationData->id)){
    serve404();
}

// Permissions — brewer_verified locations are staff/admin-editable only; bounce back
// to the location page rather than showing a form the API would refuse.
$perms = brewerPermissions($api, $locationData->brewer->id);
if(!permissionsCanEdit($perms, !empty($locationData->cb_verified), !empty($locationData->brewer_verified))){
    header('location: /location/' . urlencode($locationID));
    exit();
}

// Brewer Info
// Raw. h() at each echo; htmlHead and Navigation escape their own arguments.
$brewerName = $locationData->brewer->name;
$brewerID = $locationData->brewer->id;
$locationIDString = $locationData->id;

// Default Values from Existing Data
$validState = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'', 'line1'=>'', 'line2'=>'', 'locality'=>'', 'dependent_locality'=>'', 'sub_code'=>'', 'postal_code'=>'', 'telephone'=>'');
$validMsg = $validState;
// Raw stored name, not the display stand-in — this prefills the form field, and
// an unnamed location must show an empty box rather than a composed label the
// user would then save as a real name.
$name = $locationData->name ?? '';
$url = $locationData->url ?? '';
$addressFields = addressFieldsFromLocation($locationData);
// Whether this location had an address before this edit. Words the "we
// couldn't save" message, and fixes the country: the API refuses a country
// change on a location that holds an address.
$hadAddress = isset($locationData->address);
if(!$hadAddress && isset($_GET['country'])){
    // The Country select reloaded the page; keep the name and URL typed so
    // far rather than resetting them to the stored values (address.php).
    $name = (string)($_GET['name'] ?? '');
    $url = (string)($_GET['url'] ?? '');
}
$countries = addressCountries($api);
$country_code = $hadAddress
    ? strtoupper((string)($locationData->country_code ?? 'US'))
    : addressFormCountry($countries, (string)($locationData->country_code ?? 'US'));
if(!$hadAddress && isset($_POST['submit']) && !empty($_POST['country_code']) && isset($countries[strtoupper($_POST['country_code'])])){
    $country_code = strtoupper($_POST['country_code']);
}
$subdivisions = addressSubdivisions($api, $country_code);

// Process Form
if(isset($_POST['submit'])){
    if(!csrf_verify()){
        $alert->msg = 'Invalid form submission. Please try again.';
        $alert->type = 'error';
    }else{
        // Get Posted Variables
        $name = $_POST['name'] ?? '';
        $url = $_POST['url'] ?? '';
        $addressFields = addressFieldsFromPost();

        $patchData = array('name'=>$name, 'url'=>$url);
        if(addressHasContent($addressFields)){
            $patchData['address'] = $addressFields;
        }
        if(!$hadAddress && $country_code !== strtoupper((string)($locationData->country_code ?? 'US'))){
            // A country chosen for a location that had no address yet. With
            // an address in the same request it validates there.
            $patchData['country_code'] = $country_code;
        }
        $patchArray = json_decode($api->request('PATCH', '/location/' . $locationID, $patchData), true);
        if(is_array($patchArray) && !isset($patchArray['error'])){
            // Success — back to the location itself, which is where the Edit
            // button that opened this form lives.
            trackEvent('Location updated');
            header('location: /location/' . $locationIDString);
            exit();
        }
        // Anything we can't parse counts as a failure. Reporting a save that
        // may not have happened is the worse of the two mistakes.
        if(is_array($patchArray)){
            $alert->msg = $patchArray['error_msg'] ?? '';
            if($alert->msg === ''){
                $alert->msg = 'Sorry, we couldn\'t save this location. Please check the fields below.';
            }
            foreach(locationFormState($patchArray['valid_state'] ?? array()) as $field => $state){
                $validState[$field] = $state;
            }
            foreach(locationFormState($patchArray['valid_msg'] ?? array()) as $field => $msg){
                $validMsg[$field] = $msg;
            }
        }else{
            $alert->msg = 'Sorry, we couldn\'t save this location just now. Please try again in a few minutes.';
        }
        $alert->type = 'error';
    }
}

// HTML Head
$locationName = locationDisplayName($locationData);
$htmlHead = new htmlHead('Edit ' . $locationName);
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs — these carry the brewer context; no disabled Brewer input
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Edit ' . $locationName);
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Edit <em><?php echo h($locationName); ?></em></h1>
        </div>
        <?php echo $alert->display(); ?>
        <p class="cbf-legend"><span aria-hidden="true">*</span> Required</p>
        <form method="post" class="cbf-panel">
            <?php echo csrf_field(); ?>
            <?php
            echo '<div class="cbf-sec">' . "\n";

            // Name — optional; clearing it is a valid edit. See
            // location-add.php for why the hint says this.
            $inputName = new InputField();
            $inputName->name = 'name';
            $inputName->description = 'Name';
            $inputName->hint = 'Only if this taproom has a unique name of its own.';
            $inputName->type = 'text';
            $inputName->required = false;
            $inputName->autofocus = true;
            $inputName->value = $name;
            $inputName->validState = $validState['name'];
            $inputName->validMsg = $validMsg['name'];
            suppressAutofill($inputName);  // the taproom's name, not the contributor's
            echo $inputName->display();

            // URL
            $inputURL = new InputField();
            $inputURL->name = 'url';
            $inputURL->description = 'Location URL';
            $inputURL->type = 'url';
            $inputURL->required = false;
            $inputURL->value = $url;
            $inputURL->validState = $validState['url'];
            $inputURL->validMsg = $validMsg['url'];
            suppressAutofill($inputURL);
            echo $inputURL->display();

            echo '</div>' . "\n";

            // Address. The country is fixed once an address exists (the API
            // validated it for that country); before that it is a select that
            // reloads the form for the chosen country.
            echo '<div class="cbf-subhead"><span class="cbf-subhead__t">Address</span></div>' . "\n";
            echo '<div class="cbf-sec">' . "\n";
            echo addressCountryField($countries, $country_code, $validState, $validMsg, $hadAddress);
            echo addressFormFields($addressFields, $validState, $validMsg, $country_code, $subdivisions);
            echo '</div>' . "\n";
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit">Save Changes</button>
                <a class="cbf-btn cbf-btn--ghost" href="/location/<?php echo h($locationIDString); ?>">Cancel</a>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <?php echo addressAutocompleteScripts(); ?>
</body>
</html>
