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
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Edit');
				$nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
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
					<button type="submit" class="btn btn-primary" name="submit">Save Changes</button>
					<a href="/brewer/<?php echo htmlspecialchars($brewerID); ?>" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>
