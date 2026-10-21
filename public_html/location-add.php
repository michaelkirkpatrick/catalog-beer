<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$api = new API();
$alert = new Alert();

/* ---
Add a location — name, URL and address in one form, and one request.

POST /location takes the address nested, validates it first and writes the
row only when it passes, so a refused street never leaves a half-made
location behind. (It used to: the address was a second request that could
fail after the location existed, and this page held a "pending location" in
the session to survive a retry. API 6.0 made that whole mechanism
unnecessary.)
--- */

// Default Values
$disabled = false;
$validState = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'', 'line1'=>'', 'line2'=>'', 'locality'=>'', 'dependent_locality'=>'', 'sub_code'=>'', 'postal_code'=>'', 'telephone'=>'');
$validMsg = $validState;
$brewerID = '';
$brewerURL = '';
$brewerName = '';
$name = '';
$url = '';
// The Country select reloads the page with ?country= and carries the name and
// URL typed so far, so changing the country doesn't wipe them (address.php).
if(isset($_GET['country'])){
    $name = (string)($_GET['name'] ?? '');
    $url = (string)($_GET['url'] ?? '');
}
$autofocus = true;
$addressFields = addressBlankFields();
// The location's country: the ?country= override (the Country select reloads
// with it), else the brewer's own country, else US. The address controls and
// the region list follow it; see classes/helpers/address.php.
$countries = addressCountries($api);
$country_code = 'US';
$subdivisions = array();

// Get Brewery Info
if(isset($_GET['brewerID'])){
    // Get BrewerID from URL
    $brewerID = $_GET['brewerID'];
    $brewerResp = $api->request('GET', '/brewer/' . $brewerID, '');
    $brewerData = json_decode($brewerResp);
    if($api->unavailable()){
        serve503();
    }
    if(!isset($brewerData->error)){
        // Save Brewer Info — raw. h() at each echo; htmlHead and Navigation
        // escape their own arguments.
        $brewerName = $brewerData->name;
        $brewerURL = $brewerData->id;
        $country_code = addressFormCountry($countries, (string)($brewerData->country_code ?? 'US'));
        if(isset($_POST['submit']) && !empty($_POST['country_code']) && isset($countries[strtoupper($_POST['country_code'])])){
            $country_code = strtoupper($_POST['country_code']);
        }
        $subdivisions = addressSubdivisions($api, $country_code);

        // Process Form
        if(isset($_POST['submit'])){
            if(!csrf_verify()){
                $alert->msg = 'Invalid form submission. Please try again.';
                $alert->type = 'error';
            }else{
                // Remove Input Field Autofocus
                $autofocus = false;

                // Get Posted Variables
                $name = $_POST['name'] ?? '';
                $url = $_POST['url'] ?? '';
                $addressFields = addressFieldsFromPost();

                // One request: the location with its address nested. The
                // address is required, and the API validates it before writing
                // anything; a blank one comes back naming the missing fields.
                $locationPOST = array('brewer_id'=>$brewerID, 'name'=>$name, 'url'=>$url, 'country_code'=>$country_code, 'address'=>$addressFields);
                $locationResult = json_decode($api->request('POST', '/location', $locationPOST), true);
                if(is_array($locationResult) && !isset($locationResult['error']) && isset($locationResult['id'])){
                    // Successfully Added
                    trackEvent('Location created');
                    header('location: /location/' . $locationResult['id']);
                    exit();
                }
                $alert->msg = is_array($locationResult) ? ($locationResult['error_msg'] ?? '') : '';
                if($alert->msg === ''){
                    $alert->msg = 'Sorry, we couldn\'t add this location. Please check the fields below.';
                }
                $alert->type = 'error';
                foreach(locationFormState(is_array($locationResult) ? ($locationResult['valid_state'] ?? array()) : array()) as $field => $state){
                    $validState[$field] = $state;
                }
                foreach(locationFormState(is_array($locationResult) ? ($locationResult['valid_msg'] ?? array()) : array()) as $field => $msg){
                    $validMsg[$field] = $msg;
                }
            }
        }
    }else{
        // Invalid Brewer
        $disabled = true;
        $alert->msg = 'Sorry, this looks like an invalid brewery. Try navigating back to this page from the <a href="/brewer">list of brewers</a>.';
        $validState['brewer_id'] = 'invalid';
        $validMsg['brewer_id'] = 'Invalid brewer';
        $brewerName = '';
    }
}else{
    // Missing Brewer ID
    $disabled = true;
    $alert->msg = 'We seem to be missing the brewery this new location would be associated with. Try navigating back to this page from the <a href="/brewer">list of brewers</a>.';
    $validState['brewer_id'] = 'invalid';
    $validMsg['brewer_id'] = 'Invalid brewer';
    $brewerName = '';
}

// HTML Head
$htmlHead = new htmlHead('Add a Location');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewer'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs — these carry the brewer context; no disabled Brewer input
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Add a Location');
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerURL);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Add a location</h1>
        </div>
        <?php echo $alert->display(); ?>
        <p class="cbf-legend"><span aria-hidden="true">*</span> Required</p>
        <form method="post" class="cbf-panel<?php if($disabled){echo ' cbf-inert';} ?>">
            <?php echo csrf_field(); ?>
            <?php
            if($disabled){
                echo '<fieldset disabled>' . "\n";
            }

            echo '<div class="cbf-sec">' . "\n";

            // Name — optional. Most taprooms aren't named separately from
            // the community they sit in; the hint says so, because while
            // this field was required people filled it with the city.
            $inputName = new InputField();
            $inputName->name = 'name';
            $inputName->description = 'Name';
            $inputName->hint = 'Only if this taproom has a unique name of its own.';
            $inputName->type = 'text';
            $inputName->required = false;
            $inputName->value = $name;
            $inputName->autofocus = $autofocus;
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

            // Address. The Country select is a location field that sits with the
            // address because the address controls follow it (reload on change).
            echo '<div class="cbf-subhead"><span class="cbf-subhead__t">Address</span></div>' . "\n";
            echo '<div class="cbf-sec">' . "\n";
            echo addressCountryField($countries, $country_code, $validState, $validMsg);
            echo addressFormFields($addressFields, $validState, $validMsg, $country_code, $subdivisions);
            echo '</div>' . "\n";

            // Close Disabled
            if($disabled){
                echo '</fieldset>' . "\n";
            }
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit"<?php if($disabled){echo ' disabled';} ?>>Add Location</button>
                <?php if(!$disabled){ ?>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer/<?php echo h($brewerURL); ?>">Cancel</a>
                <?php } ?>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <?php echo addressAutocompleteScripts(); ?>
</body>
</html>
