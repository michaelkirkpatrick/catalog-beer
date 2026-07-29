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

// Default Values from Existing Data
$validState = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'');
$validMsg = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'');
$name = $brewerData->name;
$description = $brewerData->description ?? '';
$shortDescription = $brewerData->short_description ?? '';
$url = $brewerData->url ?? '';

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

    $patchData = array('name'=>$name, 'description'=>$description, 'short_description'=>$shortDescription, 'url'=>$url);
    $api = new API();
    $patchResponse = $api->request('PATCH', '/brewer/' . $brewerID, $patchData);
    $patchArray = json_decode($patchResponse, true);
    if(isset($patchArray['error'])){
        $alert->msg = $patchArray['error_msg'];
        $validState = $patchArray['valid_state'];
        $validMsg = $patchArray['valid_msg'];
    }else{
        // Success
        header('location: /brewer/' . $patchArray['id']);
        exit();
    }
    }
}

// HTML Head
$text = new Text(false, true, true);
$brewerName = $text->get($brewerData->name);
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
            <h1 class="cbf-h1">Edit <em><?php echo $brewerName; ?></em></h1>
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
            echo $inputName->display();

            // Description
            $textarea = new Textarea();
            $textarea->name = 'description';
            $textarea->description = 'About the brewer';
            $textarea->hint = 'Markdown supported.';
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
            echo $inputURL->display();
            ?>
            </div>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn" name="submit">Save Changes</button>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer/<?php echo htmlspecialchars($brewerID); ?>">Cancel</a>
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
