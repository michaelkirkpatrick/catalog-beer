<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

/* ---
Edit a location — name, URL and address in one form.

These used to be two pages (this one, and address-edit.php / address-add.php),
which split one editing task across two screens and let a location exist with
nothing attached to it at all. Now that a location's name is optional, a
location without an address is a bare UUID and a brewer: it can't even label
itself. So the address lives here, and the address pages redirect in.

Two API calls, because the API keeps them apart: PUT /address/{id} then
PATCH /location/{id}. The address goes first on purpose — it's the call that
actually fails (Google validates and standardizes the street, the API rejects a
bad ZIP), so failing before the location write leaves nothing half-saved.
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
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Brewer Info
$text1 = new Text(false, true, true);
$text2 = new Text(false, false, true);
$brewerName = $text1->get($locationData->brewer->name);
$brewerID = $text2->get($locationData->brewer->id);
$locationIDString = $text2->get($locationData->id);

// Default Values from Existing Data
$validState = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'', 'address1'=>'', 'address2'=>'', 'city'=>'', 'sub_code'=>'', 'zip'=>'', 'telephone'=>'');
$validMsg = $validState;
// Raw stored name, not the display stand-in — this prefills the form field, and
// an unnamed location must show an empty box rather than a composed label the
// user would then save as a real name.
$name = $locationData->name ?? '';
$url = $locationData->url ?? '';
$addressFields = addressFieldsFromLocation($locationData);
// Whether this location had an address before this edit. Only used to word the
// "we couldn't save" message accurately.
$hadAddress = isset($locationData->address);

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

        $addressSaved = true;
        if(addressHasContent($addressFields)){
            $addressResult = addressPut($api, $locationID, $addressFields);
            if($addressResult['error']){
                $addressSaved = false;
                $alert->msg = $addressResult['error_msg'];
                $alert->type = 'error';
                foreach($addressResult['valid_state'] as $field => $state){
                    $validState[$field] = $state;
                    $validMsg[$field] = $addressResult['valid_msg'][$field] ?? '';
                }
            }
        }
        // An empty address section is only reachable on a location that never had
        // one — the fields are marked required, so the browser stops everyone
        // else. Let the name/URL edit through rather than holding it hostage to
        // an address the editor may not know.

        if($addressSaved){
            $patchData = array('name'=>$name, 'url'=>$url);
            $patchResponse = $api->request('PATCH', '/location/' . $locationID, $patchData);
            $patchArray = json_decode($patchResponse, true);
            if(is_array($patchArray) && !isset($patchArray['error'])){
                // Success — back to the location itself, which is where the Edit
                // button that opened this form lives.
                header('location: /location/' . $locationIDString);
                exit();
            }else{
                // Anything we can't parse counts as a failure. Reporting a save
                // that may not have happened is the worse of the two mistakes.
                if(is_array($patchArray)){
                    $alert->msg = $patchArray['error_msg'] ?? 'Sorry, we couldn\'t save this location.';
                    foreach(($patchArray['valid_state'] ?? array()) as $field => $state){
                        $validState[$field] = $state;
                        $validMsg[$field] = $patchArray['valid_msg'][$field] ?? '';
                    }
                }else{
                    $alert->msg = 'Sorry, we couldn\'t save this location just now. Please try again in a few minutes.';
                }
                $alert->type = 'error';
                if($hadAddress || addressHasContent($addressFields)){
                    // The address write already went through. Say so, so nobody
                    // retypes it thinking the whole form was rejected.
                    $alert->msg .= ' (The address was saved.)';
                }
            }
        }
    }
}

// HTML Head
$locationName = $text1->get(locationDisplayName($locationData));
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
            <h1 class="cbf-h1">Edit <em><?php echo $locationName; ?></em></h1>
            <p class="cbf-lede">Changes go live as soon as you save.</p>
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

            // Address — the dead US-only Country select became the note here
            echo '<div class="cbf-subhead"><span class="cbf-subhead__t">Address</span><span class="cbf-subhead__n">United States only, for now</span></div>' . "\n";
            echo '<div class="cbf-sec">' . "\n";
            echo addressFormFields($addressFields, $validState, $validMsg);
            echo '</div>' . "\n";
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit">Save Changes</button>
                <a class="cbf-btn cbf-btn--ghost" href="/location/<?php echo htmlspecialchars($locationIDString); ?>">Cancel</a>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <?php echo addressAutocompleteScripts(); ?>
</body>
</html>
