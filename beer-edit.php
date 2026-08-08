<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

// Get Beer ID
$beerID = $_GET['beerID'] ?? '';

// Fetch Existing Beer Data
$api = new API();
$beerResp = $api->request('GET', '/beer/' . $beerID, '');
$beerData = json_decode($beerResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(isset($beerData->error) || !isset($beerData->id)){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Permissions — verified beers are staff/admin-editable only; bounce back
// to the beer page rather than showing a form the API would refuse.
$perms = brewerPermissions($api, $beerData->brewer->id);
if(!permissionsCanEdit($perms, !empty($beerData->cb_verified), !empty($beerData->brewer_verified))){
    header('location: /beer/' . urlencode($beerID));
    exit();
}

// Brewer Info
// Raw. h() at each echo; htmlHead and Navigation escape their own arguments.
$brewerName = $beerData->brewer->name;
$brewerURL = $beerData->brewer->id;

// Default Values from Existing Data
$validState = array('brewer_id'=>'', 'name'=>'', 'style'=>'', 'description'=>'', 'abv'=>'', 'ibu'=>'');
$validMsg = array('brewer_id'=>'', 'name'=>'', 'style'=>'', 'description'=>'', 'abv'=>'', 'ibu'=>'');
$name = $beerData->name;
$styleLabel = $beerData->style;
$styleID = $beerData->style_id ?? '';
$styleParent = $beerData->parent ?? '';
$styleClass = $beerData->class ?? '';
$beverageType = $beerData->beverage_type ?? '';
// style_confidence is internal to the API (not returned in beer objects). The
// hidden field starts empty: guided-style.js derives a fresh value on load, and
// the API keeps the stored value when the tier is unchanged and none is sent.
$styleConfidence = '';
$description = $beerData->description ?? '';
$abv = $beerData->abv ?? '';
$ibu = $beerData->ibu ?? '';

// Process Form
if(isset($_POST['submit'])){
    if(!csrf_verify()){
        $alert->msg = 'Invalid form submission. Please try again.';
        $alert->type = 'error';
    }else{
        // Get Posted Variables
        $name = $_POST['name'];
        $styleLabel = $_POST['style'] ?? '';
        $styleID = $_POST['style_id'] ?? '';
        $styleParent = $_POST['parent'] ?? '';
        $styleClass = $_POST['class'] ?? '';
        $beverageType = $_POST['beverage_type'] ?? '';
        $styleConfidence = $_POST['style_confidence'] ?? '';
        $description = $_POST['description'];
        $abv = $_POST['abv'];
        $ibu = $_POST['ibu'];

        $patchData = array('name'=>$name, 'style'=>$styleLabel, 'style_id'=>$styleID, 'parent'=>$styleParent, 'class'=>$styleClass, 'style_confidence'=>$styleConfidence, 'description'=>$description, 'abv'=>$abv, 'ibu'=>$ibu);
        $patchResponse = $api->request('PATCH', '/beer/' . $beerID, $patchData);
        $patchArray = json_decode($patchResponse, true);
        if(isset($patchArray['error'])){
            $alert->msg = $patchArray['error_msg'];
            $validState = $patchArray['valid_state'];
            $validMsg = $patchArray['valid_msg'];
        }else{
            // Success
            header('location: /beer/' . $patchArray['id']);
            exit();
        }
    }
}

// HTML Head
$beerName = $beerData->name;
$htmlHead = new htmlHead('Edit ' . $beerName);
$guidedCSS = cssTag('/assets/css/guided-style.css');
echo (strpos($htmlHead->html, '</head>') !== false)
    ? str_replace('</head>', "\t" . $guidedCSS . "\n</head>", $htmlHead->html)
    : $htmlHead->html . $guidedCSS;
?>
<body>
    <?php echo $nav->navbar('Beer'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs — these carry the brewer context; no disabled Brewer input
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, $beerName, 'Edit');
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerURL, '/beer/' . $beerID);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Edit <em><?php echo h($beerName); ?></em></h1>
            <p class="cbf-lede">Changes go live as soon as you save.</p>
        </div>
        <?php echo $alert->display(); ?>
        <p class="cbf-legend"><span aria-hidden="true">*</span> Required</p>
        <form method="post" class="cbf-panel">
            <?php echo csrf_field(); ?>
            <?php
            echo '<div class="cbf-sec">' . "\n";

            // Name
            $inputName = new InputField();
            $inputName->name = 'name';
            $inputName->description = 'Name';
            $inputName->type = 'text';
            $inputName->required = true;
            $inputName->autofocus = true;
            suppressAutofill($inputName);  // beer name, not the user's name
            $inputName->value = $name;
            $inputName->validState = $validState['name'];
            $inputName->validMsg = $validMsg['name'];
            echo $inputName->display();

            // Style (guided)
            $guidedStyle = new GuidedStyleField();
            $guidedStyle->required = true;
            $guidedStyle->value = $styleLabel;
            $guidedStyle->styleId = $styleID;
            $guidedStyle->parent = $styleParent;
            $guidedStyle->class = $styleClass;
            $guidedStyle->beverageType = $beverageType;
            $guidedStyle->styleConfidence = $styleConfidence;
            $guidedStyle->validState = $validState['style'];
            $guidedStyle->validMsg = $validMsg['style'];
            echo $guidedStyle->display();

            echo '</div>' . "\n";
            echo '<div class="cbf-sec"><div class="cbf-grid2">' . "\n";

            // ABV
            $inputAbv = new InputField();
            $inputAbv->name = 'abv';
            $inputAbv->description = 'ABV';
            $inputAbv->required = true;
            $inputAbv->placeholder = '0.0';
            $inputAbv->value = $abv;
            $inputAbv->validState = $validState['abv'];
            $inputAbv->validMsg = $validMsg['abv'];
            $inputAbv->addAfter = '%';
            echo $inputAbv->display();

            // IBU
            $inputIbu = new InputField();
            $inputIbu->name = 'ibu';
            $inputIbu->description = 'IBU';
            $inputIbu->placeholder = '0';
            $inputIbu->value = $ibu;
            $inputIbu->validState = $validState['ibu'];
            $inputIbu->validMsg = $validMsg['ibu'];
            $inputIbu->addAfter = 'IBU';
            echo $inputIbu->display();

            echo '</div></div>' . "\n";
            echo '<div class="cbf-sec">' . "\n";

            // Description
            $textarea = new Textarea();
            $textarea->name = 'description';
            $textarea->description = 'Description';
            $textarea->hint = 'Plain text — line breaks are preserved.';
            $textarea->value = $description;
            $textarea->validState = $validState['description'];
            $textarea->validMsg = $validMsg['description'];
            echo $textarea->display();

            echo '</div>' . "\n";
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit">Save Changes</button>
                <a class="cbf-btn cbf-btn--ghost" href="/beer/<?php echo h($beerID); ?>">Cancel</a>
                <?php
                // "Last edited …" — recent edits read relative, older ones as a date
                if(isset($beerData->last_modified) && is_numeric($beerData->last_modified)){
                    $daysAgo = (int)floor((time() - (int)$beerData->last_modified) / 86400);
                    if($daysAgo <= 0){ $lastEdited = 'today'; }
                    elseif($daysAgo === 1){ $lastEdited = 'yesterday'; }
                    elseif($daysAgo < 30){ $lastEdited = $daysAgo . ' days ago'; }
                    else{ $lastEdited = date('M j, Y', (int)$beerData->last_modified); }
                    echo '<span class="cbf-actnote">Last edited ' . $lastEdited . '</span>';
                }
                ?>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <?php echo StyleList::inlineScript(); ?>
    <?php echo jsTag('/assets/js/guided-style.js'); ?>
</body>
</html>
