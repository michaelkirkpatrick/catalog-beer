<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$api = new API();
$alert = new Alert();

// Default Values
$disabled = false;
$validState = array('brewer_id'=>'', 'name'=>'', 'style'=>'', 'description'=>'', 'abv'=>'', 'ibu'=>'');
$validMsg = array('brewer_id'=>'', 'name'=>'', 'style'=>'', 'description'=>'', 'abv'=>'', 'ibu'=>'');
$brewerID = '';
$name = '';
$styleLabel = '';
$styleID = '';
$styleParent = '';
$styleClass = '';
$beverageType = '';
$styleConfidence = '';
$description = '';
$abv = '';
$ibu = '';

// Get Brewery Info
if(isset($_GET['brewerID'])){
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

                // Send the brewer's raw label + the resolved tier (style/family/class);
                // the API derives the coarser levels + beverage_type (client not trusted).
                $beerPOST = array('brewer_id'=>$brewerID, 'name'=>$name, 'style'=>$styleLabel, 'style_id'=>$styleID, 'parent'=>$styleParent, 'class'=>$styleClass, 'style_confidence'=>$styleConfidence, 'description'=>$description, 'abv'=>$abv, 'ibu'=>$ibu);
                $beerResponse = $api->request('POST', '/beer', $beerPOST);
                $beerData = json_decode($beerResponse, true);
                if(!isset($beerData['error'])){
                    // Successfully Added
                    $_SESSION['add_beer_success'] = true;
                    unset($_SESSION['cb_counts']);  // bust navbar count cache so the new beer shows immediately
                    header('location: /beer/' . $beerData['id']);
                    exit();
                }else{
                    // Error Adding Beer
                    $alert->msg = $beerData['error_msg'];
                    $validState = $beerData['valid_state'];
                    $validMsg = $beerData['valid_msg'];
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
    $alert->msg = 'We seem to be missing the brewery this new beer would be associated with. Try navigating back to this page from the <a href="/brewer">list of brewers</a>.';
    $validState['brewer_id'] = 'invalid';
    $validMsg['brewer_id'] = 'Invalid brewer';
    $brewerName = '';
}

// HTML Head
$htmlHead = new htmlHead('Add a Beer');
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
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Add Beer');
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerURL);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Add a beer</h1>
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

            // Close Disabled
            if($disabled){
                echo '</fieldset>' . "\n";
            }
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit"<?php if($disabled){echo ' disabled';} ?>>Add Beer</button>
                <?php if(!$disabled){ ?>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer/<?php echo h($brewerURL); ?>">Cancel</a>
                <?php } ?>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
    <?php echo StyleList::inlineScript(); ?>
    <?php echo jsTag('/assets/js/guided-style.js'); ?>
</body>
</html>