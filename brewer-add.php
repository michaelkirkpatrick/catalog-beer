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
    <div class="container">
    <div class="row">
        <div class="col">
        <?php
                // Breadcrumbs
                $nav->breadcrumbText = array('Home', 'Brewers', 'Add');
                $nav->breadcrumbLink = array('/', '/brewer');
                echo $nav->breadcrumbs();
                
                // Display Alerts
                echo $alert->display();
                
                ?>
        <form method="post">
                    <?php echo csrf_field(); ?>
                    <?php
                    // Name
                    $inputName = new InputField();
                    $inputName->name = 'name';
                    $inputName->description = 'Brewer';
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
                    $textarea->value = $description;
                    $textarea->validState = $validState['description'];
                    $textarea->validMsg = $validMsg['description'];
                    echo $textarea->display();
                    
                    // Short Description
                    $inputMeta = new InputField();
                    $inputMeta->name = 'short_description';
                    $inputMeta->description = 'Short Description';
                    $inputMeta->type = 'text';
                    $inputMeta->required = false;
                    $inputMeta->maxLength = 160;
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
                    <button type="submit" class="btn btn-primary" name="submit">Add Brewer</button>
        </form>
      </div>
    </div>  
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>