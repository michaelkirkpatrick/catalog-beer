<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

// Get Brewer ID
$brewerID = $_GET['brewerID'] ?? '';

// Fetch Existing Brewer Data
$api = new API();
$brewerResp = $api->request('GET', '/brewer/' . $brewerID, '');
$brewerData = json_decode($brewerResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(isset($brewerData->error) || !isset($brewerData->id)){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Permissions — verified brewers are staff/admin-editable only; bounce back
// to the brewer page rather than showing a form the API would refuse.
$perms = brewerPermissions($api, $brewerID);
if(!permissionsCanEdit($perms, !empty($brewerData->cb_verified), !empty($brewerData->brewer_verified))){
    header('location: /brewer/' . urlencode($brewerID));
    exit();
}

// Default Values from Existing Data
$validState = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'', 'status'=>'', 'founded_year'=>'', 'closed_year'=>'', 'country_code'=>'');
$validMsg = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'', 'status'=>'', 'founded_year'=>'', 'closed_year'=>'', 'country_code'=>'');
$name = $brewerData->name;
$description = $brewerData->description ?? '';
$shortDescription = $brewerData->short_description ?? '';
$url = $brewerData->url ?? '';
$status = $brewerData->status ?? 'active';
$foundedYear = $brewerData->founded_year ?? '';
$closedYear = $brewerData->closed_year ?? '';

// Process Form
if(isset($_POST['submit'])){
    if(!csrf_verify()){
        $alert->msg = 'Invalid form submission. Please try again.';
        $alert->type = 'error';
    }else{
    // Get Posted Variables
    $name = $_POST['name'];
    $description = $_POST['description'];
    $shortDescription = $_POST['short_description'];
    $url = $_POST['url'];
    $status = ($_POST['status'] ?? 'active') === 'closed' ? 'closed' : 'active';
    $foundedYear = trim($_POST['founded_year'] ?? '');
    $closedYear = trim($_POST['closed_year'] ?? '');

    // Every field goes, so the form is a full picture of the record: the API
    // only writes what changed, and a year cleared here clears there.
    $patchData = array('name'=>$name, 'description'=>$description, 'short_description'=>$shortDescription, 'url'=>$url,
        'status'=>$status, 'founded_year'=>($foundedYear === '' ? null : (int)$foundedYear), 'closed_year'=>($closedYear === '' ? null : (int)$closedYear));
    $api = new API();
    $patchResponse = $api->request('PATCH', '/brewer/' . $brewerID, $patchData);
    $patchArray = json_decode($patchResponse, true);
    if(isset($patchArray['error'])){
        $alert->msg = $patchArray['error_msg'];
        $validState = array_merge($validState, $patchArray['valid_state'] ?? array());
        $validMsg = array_merge($validMsg, $patchArray['valid_msg'] ?? array());
    }else{
        // Success
        header('location: /brewer/' . $patchArray['id']);
        exit();
    }
    }
}

// HTML Head
// Raw. h() at each echo; htmlHead and Navigation escape their own arguments.
$brewerName = $brewerData->name;
$htmlHead = new htmlHead('Edit ' . $brewerName);
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Edit');
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Edit <em><?php echo h($brewerName); ?></em></h1>
            <p class="cbf-lede">Changes go live as soon as you save.</p>
        </div>
        <?php echo $alert->display(); ?>
        <p class="cbf-legend"><span aria-hidden="true">*</span> Required</p>
        <form method="post" class="cbf-panel">
            <?php echo csrf_field(); ?>
            <div class="cbf-sec">
            <?php
            // Name — the H1 already says brewer, so the label doesn't repeat it
            $inputName = new InputField();
            $inputName->name = 'name';
            $inputName->description = 'Name';
            $inputName->type = 'text';
            $inputName->required = true;
            $inputName->autofocus = true;
            $inputName->value = $name;
            $inputName->validState = $validState['name'];
            $inputName->validMsg = $validMsg['name'];
            suppressAutofill($inputName);  // the brewer's name, not the contributor's
            echo $inputName->display();

            // Description
            $textarea = new Textarea();
            $textarea->name = 'description';
            $textarea->description = 'About the brewer';
            $textarea->hint = 'Plain text — line breaks are preserved.';
            $textarea->rows = 4;
            $textarea->value = $description;
            $textarea->validState = $validState['description'];
            $textarea->validMsg = $validMsg['description'];
            echo $textarea->display();

            // Short Description
            $inputMeta = new InputField();
            $inputMeta->name = 'short_description';
            $inputMeta->description = 'Short Description';
            $inputMeta->hint = 'Appears in search results and link previews.';
            $inputMeta->type = 'text';
            $inputMeta->required = false;
            $inputMeta->maxLength = 160;
            $inputMeta->showCount = true;
            $inputMeta->value = $shortDescription;
            $inputMeta->validState = $validState['short_description'];
            $inputMeta->validMsg = $validMsg['short_description'];
            echo $inputMeta->display();

            // URL
            $inputURL = new InputField();
            $inputURL->name = 'url';
            $inputURL->description = 'Website';
            $inputURL->type = 'url';
            $inputURL->required = false;
            $inputURL->value = $url;
            $inputURL->validState = $validState['url'];
            $inputURL->validMsg = $validMsg['url'];
            suppressAutofill($inputURL);
            echo $inputURL->display();

            // Status + years. Year inputs post as strings; '' means "unknown"
            // and goes to the API as null so a cleared field actually clears.
            $dropStatus = new DropDown();
            $dropStatus->name = 'status';
            $dropStatus->values = array('active', 'closed');
            $dropStatus->descriptions = array('Open', 'Closed');
            $dropStatus->label = 'Status';
            $dropStatus->showLabel = true;
            $dropStatus->currentValue = $status;
            $dropStatus->validState = $validState['status'];
            $dropStatus->validMsg = $validMsg['status'];
            echo $dropStatus->display();

            $inputFounded = new InputField();
            $inputFounded->name = 'founded_year';
            $inputFounded->description = 'Founded';
            $inputFounded->hint = 'Year only, as the brewery states it (e.g. 2014). Leave blank if unknown.';
            $inputFounded->type = 'number';
            $inputFounded->required = false;
            $inputFounded->maxLength = 4;
            $inputFounded->placeholder = 'YYYY';
            $inputFounded->value = $foundedYear;
            $inputFounded->validState = $validState['founded_year'];
            $inputFounded->validMsg = $validMsg['founded_year'];
            suppressAutofill($inputFounded);
            echo $inputFounded->display();

            $inputClosed = new InputField();
            $inputClosed->name = 'closed_year';
            $inputClosed->description = 'Closed';
            $inputClosed->hint = 'Only for a closed brewery, and only when the year is known.';
            $inputClosed->type = 'number';
            $inputClosed->required = false;
            $inputClosed->maxLength = 4;
            $inputClosed->placeholder = 'YYYY';
            $inputClosed->value = $closedYear;
            $inputClosed->validState = $validState['closed_year'];
            $inputClosed->validMsg = $validMsg['closed_year'];
            suppressAutofill($inputClosed);
            echo $inputClosed->display();
            ?>
            </div>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit">Save Changes</button>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer/<?php echo h($brewerID); ?>">Cancel</a>
                <?php
                // "Last edited …" — recent edits read relative, older ones as a date
                if(isset($brewerData->last_modified) && is_numeric($brewerData->last_modified)){
                    $daysAgo = (int)floor((time() - (int)$brewerData->last_modified) / 86400);
                    if($daysAgo <= 0){ $lastEdited = 'today'; }
                    elseif($daysAgo === 1){ $lastEdited = 'yesterday'; }
                    elseif($daysAgo < 30){ $lastEdited = $daysAgo . ' days ago'; }
                    else{ $lastEdited = date('M j, Y', (int)$brewerData->last_modified); }
                    echo '<span class="cbf-actnote">Last edited ' . $lastEdited . '</span>';
                }
                ?>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <script>
    // Live "n / max" count for fields that render a .cbf-count
    document.querySelectorAll('.cbf-count[data-count-for]').forEach(function(el){
        var field = document.getElementById(el.getAttribute('data-count-for'));
        if(!field){ return; }
        var max = field.maxLength > 0 ? field.maxLength : null;
        var update = function(){ el.textContent = field.value.length + (max ? ' / ' + max : ''); };
        field.addEventListener('input', update);
        update();
    });
    </script>
</body>
</html>
