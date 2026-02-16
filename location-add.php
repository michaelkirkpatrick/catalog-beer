<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$api = new API();
$alert = new Alert();

// Default Values
$disabled = false;
$validState = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'');
$validMsg = array('brewer_id'=>'', 'name'=>'', 'url'=>'', 'country_code'=>'');
$brewerID = '';
$name = '';
$url = '';
$country_code = 'US';
$autofocus = true;

// Get Brewery Info
if(isset($_GET['brewerID'])){
	// Get BrewerID from URL
	$brewerID = $_GET['brewerID'];
	$brewerResp = $api->request('GET', '/brewer/' . $brewerID, '');
	$brewerData = json_decode($brewerResp);
	if(!isset($brewerData->error)){
		// Save Brewer Info
		$text1 = new Text(false, true, true);
		$brewerName = $text1->get($brewerData->name);
		
		$text2 = new Text(false, false, true);
		$brewerURL = $text2->get($brewerData->id);
		
		// Process Form
		if(isset($_POST['submit'])){
			// Remove Input Field Autofocus
			$autofocus = false;
			
			// Get Posted Variables
			$name = $_POST['name'];
			$url = $_POST['url'];

			$locationPOST = array('brewer_id'=>$brewerID, 'name'=>$name, 'url'=>$url, 'country_code'=>$country_code);
			$locationResponse = $api->request('POST', '/location', $locationPOST);
			$locationData = json_decode($locationResponse, true);
			if(!isset($locationData['error'])){
				// Successfully Added
				header('location: /location/' . $locationData['id'] . '/add-address');
				exit();
			}else{
				// Error Adding Beer
				$alert->msg = $locationData['error_msg'];
				$validState = $locationData['valid_state'];
				$validMsg = $locationData['valid_msg'];
			}
		}
	}else{
		// Invalid Brewer
		$disabled = true;
		$alert->msg = 'Sorry, this looks like an invalid brewery. Try navigating back to this page from the [list of brewers](/brewer).';
		$validState['brewer_id'] = 'invalid';
		$validMsg['brewer_id'] = 'Invalid brewer';
		$brewerName = '';
	}
}else{
	// Missing Brewer ID
	$disabled = true;
	$alert->msg = 'We seem to be missing the brewery this new beer would be associated with. Try navigating back to this page from the [list of brewers](/brewer).';
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
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Add a Location');
				$nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerURL);
				echo $nav->breadcrumbs();
				
				// Display Alerts
				echo $alert->display();
				?>
        <form method="post">
					<?php
					// Brewery
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
					
					if($disabled){
						echo '<fieldset disabled>' . "\n";
					}
					
					// Name
					$inputName = new InputField();
					$inputName->name = 'name';
					$inputName->description = 'Name';
					$inputName->type = 'text';
					$inputName->required = true;
					$inputName->value = $name;
					$inputName->autofocus = $autofocus;
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

					// Country
					echo '<div class="mb-3">' . "\n";
					echo '<label for="CountryCodeField" class="form-label">Country</label>' . "\n";
					echo '<fieldset disabled>' . "\n";
					echo '<select name="country_code" class="form-select" id="CountryCodeField">' . "\n";
					echo '<option value="US">United States of America</option>' . "\n";
					echo '</select>' . "\n";
					echo '</fieldset>' . "\n";
					echo '</div>' . "\n";
					
					// Close Disabled
					if($disabled){
						echo '</fieldset>' . "\n";
					}
					?>
					<button type="submit" class="btn btn-primary" name="submit">Next &raquo;</button>
        </form>
      </div>
    </div>  
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>