<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

// Default Values
$validState = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'');
$validMsg = array('name'=>'', 'url'=>'', 'description'=>'', 'short_description'=>'');
$name = '';
$description = '';
$shortDescription = '';
$url = '';

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

    $brewerData = array('name'=>$name, 'description'=>$description, 'short_description'=>$shortDescription, 'url'=>$url);
    $api = new API();
    $brewerResponse = $api->request('POST', '/brewer', $brewerData);
    $brewerArray = json_decode($brewerResponse, true);
    if(isset($brewerArray['error'])){
        $alert->msg = $brewerArray['error_msg'];
        $validState = $brewerArray['valid_state'];
        $validMsg = $brewerArray['valid_msg'];
    }else{
        // Success
        unset($_SESSION['cb_counts']);  // bust navbar count cache so the new brewer shows immediately
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
                <button type="submit" class="cbf-btn" name="submit">Add Brewer</button>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer">Cancel</a>
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