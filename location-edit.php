<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
$alert = new Alert();

// Get Location ID
$locationID = $_GET['locationID'] ?? '';

// Fetch Existing Location Data
$api = new API();
$locationResp = $api->request('GET', '/location/' . $locationID, '');
$locationData = json_decode($locationResp);
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

// Default Values from Existing Data
$validState = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'');
$validMsg = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'');
$name = $locationData->name;
$url = $locationData->url ?? '';

// Process Form
if(isset($_POST['submit'])){
	if(!csrf_verify()){
		$alert->msg = 'Invalid form submission. Please try again.';
		$alert->type = 'error';
	}else{
		// Get Posted Variables
		$name = $_POST['name'];
		$url = $_POST['url'];

		$patchData = array('name'=>$name, 'url'=>$url);
		$patchResponse = $api->request('PATCH', '/location/' . $locationID, $patchData);
		$patchArray = json_decode($patchResponse, true);
		if(isset($patchArray['error'])){
			$alert->msg = $patchArray['error_msg'];
			$validState = $patchArray['valid_state'];
			$validMsg = $patchArray['valid_msg'];
		}else{
			// Success
			header('location: /brewer/' . $brewerID);
			exit();
		}
	}
}

// HTML Head
$locationName = $text1->get($locationData->name);
$htmlHead = new htmlHead('Edit ' . $locationName);
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Brewers'); ?>
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Edit ' . $locationName);
				$nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
				echo $nav->breadcrumbs();

				// Display Alerts
				echo $alert->display();
				?>
        <form method="post">
					<?php echo csrf_field(); ?>
					<?php
					// Brewery (disabled, display only)
					echo '<fieldset disabled>' . "\n";
					$inputBrewerID = new InputField();
					$inputBrewerID->name = 'brewer_id';
					$inputBrewerID->description = 'Brewer';
					$inputBrewerID->type = 'text';
					$inputBrewerID->required = true;
					$inputBrewerID->value = $brewerName;
					$inputBrewerID->validState = $validState['brewer_id'];
					$inputBrewerID->validMsg = $validMsg['brewer_id'];
					echo $inputBrewerID->display();
					echo '</fieldset>' . "\n";

					// Name
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

					// URL
					$inputURL = new InputField();
					$inputURL->name = 'url';
					$inputURL->description = 'Location Specific URL';
					$inputURL->type = 'url';
					$inputURL->required = false;
					$inputURL->value = $url;
					$inputURL->validState = $validState['url'];
					$inputURL->validMsg = $validMsg['url'];
					echo $inputURL->display();

					// Country (disabled, US only)
					echo '<div class="mb-3">' . "\n";
					echo '<label for="CountryCodeField" class="form-label">Country</label>' . "\n";
					echo '<fieldset disabled>' . "\n";
					echo '<select name="country_code" class="form-select" id="CountryCodeField">' . "\n";
					echo '<option value="US">United States of America</option>' . "\n";
					echo '</select>' . "\n";
					echo '</fieldset>' . "\n";
					echo '</div>' . "\n";
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
