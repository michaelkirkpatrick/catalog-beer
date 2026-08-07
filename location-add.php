<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$api = new API();
$alert = new Alert();

/* ---
Add a location — name, URL and address in one form.

This used to be a two-step wizard: create the location, then bounce to
/add-address. A person who stopped at the seam left behind a location with no
address and (since the name became optional) no name either — a row that is
nothing but a UUID and a brewer. One form closes that gap.

The API still needs two calls, and here the order is forced: POST /location has
to happen first because PUT /address/{location_id} needs the id. So the failure
this page has to handle is a location that got created and an address that then
got rejected. See $_SESSION['locationAddPending'] below — without it, correcting
the ZIP and resubmitting would create a second location every time.
--- */

// Default Values
$disabled = false;
$validState = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'', 'address1'=>'', 'address2'=>'', 'city'=>'', 'sub_code'=>'', 'zip'=>'', 'telephone'=>'');
$validMsg = $validState;
$brewerID = '';
$brewerURL = '';
$brewerName = '';
$name = '';
$url = '';
$country_code = 'US';
$autofocus = true;
$addressFields = addressBlankFields();

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

                // ----- Is there a location left over from a failed attempt? -----
                // A previous submit may have created the location and then failed
                // on the address. Reuse that one rather than minting another. It's
                // held in the session, not a hidden field, so it can't be pointed
                // at someone else's location by editing the form.
                $locationError = false;
                $newLocationID = '';
                $pendingID = $_SESSION['locationAddPending'][$brewerID] ?? '';
                if($pendingID !== ''){
                    $pendingResp = $api->request('GET', '/location/' . $pendingID, '');
                    $pendingData = json_decode($pendingResp);
                    // Only reuse it while it's still the empty shell we left: same
                    // brewer, still no address. Anything else and we start clean.
                    if(isset($pendingData->id) && !isset($pendingData->error)
                        && ($pendingData->brewer->id ?? '') === $brewerID
                        && !isset($pendingData->address)){
                        $newLocationID = $pendingID;
                        // Carry over any edits to the name/URL made since the failed
                        // attempt. Errors here matter as much as they do on a create.
                        $patchData = array('name'=>$name, 'url'=>$url);
                        $patchResult = json_decode($api->request('PATCH', '/location/' . $newLocationID, $patchData), true);
                        if(!is_array($patchResult) || isset($patchResult['error'])){
                            $locationError = true;
                            $alert->msg = (is_array($patchResult) ? ($patchResult['error_msg'] ?? '') : '');
                            if($alert->msg === ''){
                                $alert->msg = 'Sorry, we couldn\'t save this location just now. Please try again in a few minutes.';
                            }
                            $alert->type = 'error';
                            foreach((is_array($patchResult) ? ($patchResult['valid_state'] ?? array()) : array()) as $field => $state){
                                $validState[$field] = $state;
                                $validMsg[$field] = $patchResult['valid_msg'][$field] ?? '';
                            }
                        }
                    }else{
                        unset($_SESSION['locationAddPending'][$brewerID]);
                    }
                }

                // ----- Create the location -----
                // Only when there was no reusable shell. The $locationError guard is
                // belt-and-braces: a failed reuse already leaves $newLocationID set,
                // and creating a second location here is the exact bug this whole
                // pending-ID dance exists to prevent.
                if(!$locationError && $newLocationID === ''){
                    $locationPOST = array('brewer_id'=>$brewerID, 'name'=>$name, 'url'=>$url, 'country_code'=>$country_code);
                    $locationResponse = $api->request('POST', '/location', $locationPOST);
                    $locationResult = json_decode($locationResponse, true);
                    if(is_array($locationResult) && !isset($locationResult['error']) && isset($locationResult['id'])){
                        $newLocationID = $locationResult['id'];
                        $_SESSION['locationAddPending'][$brewerID] = $newLocationID;
                    }else{
                        $locationError = true;
                        $alert->msg = $locationResult['error_msg'] ?? 'Sorry, we couldn\'t add this location.';
                        $alert->type = 'error';
                        foreach(($locationResult['valid_state'] ?? array()) as $field => $state){
                            $validState[$field] = $state;
                            $validMsg[$field] = $locationResult['valid_msg'][$field] ?? '';
                        }
                    }
                }

                // ----- Attach the address -----
                if(!$locationError){
                    $addressResult = addressPut($api, $newLocationID, $addressFields);
                    if($addressResult['error']){
                        // The location exists but has no address yet. It stays in
                        // the session so the next submit finishes it off instead of
                        // creating another.
                        $alert->msg = $addressResult['error_msg'];
                        $alert->type = 'error';
                        foreach($addressResult['valid_state'] as $field => $state){
                            $validState[$field] = $state;
                            $validMsg[$field] = $addressResult['valid_msg'][$field] ?? '';
                        }
                    }else{
                        // Successfully Added
                        unset($_SESSION['locationAddPending'][$brewerID]);
                        header('location: /location/' . $newLocationID);
                        exit();
                    }
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

            // Address — the dead US-only Country select became the note here
            echo '<div class="cbf-subhead"><span class="cbf-subhead__t">Address</span><span class="cbf-subhead__n">United States only, for now</span></div>' . "\n";
            echo '<div class="cbf-sec">' . "\n";
            echo addressFormFields($addressFields, $validState, $validMsg);
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
