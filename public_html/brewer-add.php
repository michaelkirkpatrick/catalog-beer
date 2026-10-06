<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

// Default Values
$validState = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'', 'status'=>'', 'founded_year'=>'', 'closed_year'=>'', 'country_code'=>'');
$validMsg = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'', 'status'=>'', 'founded_year'=>'', 'closed_year'=>'', 'country_code'=>'');
$name = '';
$description = '';
$shortDescription = '';
$url = '';
$status = 'active';
$countryCode = 'US';
$foundedYear = '';
$closedYear = '';

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
    $countryCode = strtoupper(trim($_POST['country_code'] ?? 'US'));
    $foundedYear = trim($_POST['founded_year'] ?? '');
    $closedYear = trim($_POST['closed_year'] ?? '');

    $brewerData = array('name'=>$name, 'description'=>$description, 'short_description'=>$shortDescription, 'url'=>$url, 'country_code'=>$countryCode, 'status'=>$status);
    if($foundedYear !== ''){ $brewerData['founded_year'] = (int)$foundedYear; }
    if($status === 'closed' && $closedYear !== ''){ $brewerData['closed_year'] = (int)$closedYear; }
    $api = new API();
    $brewerResponse = $api->request('POST', '/brewer', $brewerData);
    $brewerArray = json_decode($brewerResponse, true);
    if(isset($brewerArray['error'])){
        $alert->msg = $brewerArray['error_msg'];
        $validState = array_merge($validState, $brewerArray['valid_state'] ?? array());
        $validMsg = array_merge($validMsg, $brewerArray['valid_msg'] ?? array());
    }else{
        // Success
        cacheDelete('counts');  // bust navbar count cache so the new brewer shows immediately
        header('location: /brewer/' . $brewerArray['id']);
        exit();
    }
    }
}

// HTML Head
$htmlHead = new htmlHead('Add a Brewer');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs
        $nav->breadcrumbText = array('Home', 'Brewers', 'Add');
        $nav->breadcrumbLink = array('/', '/brewer');
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Add a brewer</h1>
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

            // Country: every assigned ISO 3166-1 code, US first then by name.
            // The brewer may be anywhere; locations are still US-only.
            $countryCodes = CountryCode::CODES;
            asort($countryCodes);
            $countryValues = array_merge(array('US'), array_values(array_diff(array_keys($countryCodes), array('US'))));
            $countryNames = array_map(fn($c) => $countryCodes[$c], $countryValues);
            $dropCountry = new DropDown();
            $dropCountry->name = 'country_code';
            $dropCountry->values = $countryValues;
            $dropCountry->descriptions = $countryNames;
            $dropCountry->label = 'Country';
            $dropCountry->showLabel = true;
            $dropCountry->currentValue = $countryCode;
            $dropCountry->validState = $validState['country_code'];
            $dropCountry->validMsg = $validMsg['country_code'];
            echo $dropCountry->display();

            // Status + years. Year inputs post as strings; '' means "unknown"
            // and goes to the API as null so a cleared field actually clears.
            $dropStatus = new DropDown();
            $dropStatus->name = 'status';
            $dropStatus->values = array('active', 'closed');
            $dropStatus->descriptions = array('Open', 'Closed');
            $dropStatus->label = 'Brewery Status';
            $dropStatus->showLabel = true;
            $dropStatus->hint = 'Is this brewery currently in business and operating?';
            $dropStatus->currentValue = $status;
            $dropStatus->validState = $validState['status'];
            $dropStatus->validMsg = $validMsg['status'];
            echo $dropStatus->display();

            $inputFounded = new InputField();
            $inputFounded->name = 'founded_year';
            $inputFounded->description = 'Year Founded';
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
            $inputClosed->description = 'Year Closed';
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
                <button type="submit" class="cbf-btn" name="submit">Add Brewer</button>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer">Cancel</a>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <script>
    // Year Closed only applies to a closed brewery: show it when the status
    // says so, clear it when the status flips back to open (the API refuses a
    // closed_year on an open brewer, and clears it itself on a reopen).
    (function(){
        var status = document.getElementById('statusField');
        var closed = document.getElementById('closed_yearField');
        if(!status || !closed){ return; }
        var field = closed.closest('.cbf-field');
        var toggle = function(){
            var isClosed = status.value === 'closed';
            field.hidden = !isClosed;
            if(!isClosed){ closed.value = ''; }
        };
        status.addEventListener('change', toggle);
        toggle();
    })();

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
