<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Required Classes
$api = new API();
$alert = new Alert();

// Default Values
$disabled = false;
$validState = array('location_id'=>'', 'address1'=>'', 'address2'=>'', 'city'=>'', 'sub_code'=>'', 'zip'=>'', 'zip'=>'', 'telephone'=>'');
$validMsg = array('location_id'=>'', 'address1'=>'', 'address2'=>'', 'city'=>'', 'sub_code'=>'', 'zip'=>'', 'telephone'=>'');
$locationID = '';
$address1 = '';
$address2 = '';
$city = '';
$sub_code = '';
$zip = '';
$zip5 = '';
$zip4 = '';
$telephone = '';
$autofocus = true;

// Get Brewery Info
if(isset($_GET['locationID'])){
	// Get LocationID from URL
	$locationID = $_GET['locationID'];
	$locationResp = $api->request('GET', '/location/' . $locationID, '');
	$locationData = json_decode($locationResp);
	if(!isset($locationData->error)){
		// Save Location Info
		$text1 = new Text(false, true, true);
		$locationName = $text1->get($locationData->name);
		
		$text2 = new Text(false, false, true);
		$brewerID = $text2->get($locationData->brewer_id);
		
		// Get Brewer Name
		$brewerResp = $api->request('GET', '/brewer/' . $brewerID, '');
		$brewerData = json_decode($brewerResp);
		if(isset($brewerResp->error)){
			$brewerName = 'Brewer';
		}else{
			$brewerName = $text2->get($brewerData->name);
		}
		
		// Process Form
		if(isset($_POST['submit'])){
			// Remove Autofocus
			$autofocus = false;
			
			// Get Posted Variables
			$address1 = $_POST['address1'];
			$address2 = $_POST['address2'];
			$city = $_POST['city'];
			$sub_code = $_POST['sub_code'];
			$zip = $_POST['zip'];
			$telephone = $_POST['telephone'];
			
			// Process ZIP Code
			if(!empty($zip)){
				$zip5 = substr($zip, 0, 5);
				if(strlen($zip) > 5){
					$zip4 = substr($zip, 6, 4);
				}
			}
			
			$addressPOST = array('address1'=>$address1, 'address2'=>$address2, 'city'=>$city, 'sub_code'=>$sub_code, 'zip5'=>$zip5, 'zip4'=>$zip4, 'telephone'=>$telephone);
			$addressResponse = $api->request('POST', '/location/' . $locationID, $addressPOST);
			$locationData = json_decode($addressResponse, true);
			if(isset($locationData['error'])){
				// Error Adding Beer
				$alert->msg = $locationData['error_msg'];
				$validState = $locationData['valid_state'];
				$validMsg = $locationData['valid_msg'];
				
				$validState['zip'] = $locationData['valid_state']['zip5'];
				$validMsg['zip'] = $locationData['valid_msg']['zip5'];
			}else{
				// Successfully Added
				header('location: /brewer/' . $brewerID);
				exit();
			}
		}
	}else{
		// Invalid Location
		$disabled = true;
		$alert->msg = 'Sorry, this looks like an invalid location. Try navigating back to this page from the [brewery page](/brewer/' . $brewerID . ')';
	}
}else{
	// Missing Location ID
	$disabled = true;
	$alert->msg = 'We seem to be missing the location_id for this location. Try navigating back to this page from the [list of brewers](/brewer).';
}

// HTML Head
$htmlHead = new htmlHead('Add Location Address');
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Brewer'); ?>
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Add Address for ' . $locationName);
				$nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
				echo $nav->breadcrumbs();
				
				// Display Alerts
				echo $alert->display();
				?>
        <form method="post">
					<?php
					if($disabled){
						echo '<fieldset disabled>' . "\n";
					}
					
					// Street Address - Address2
					$inputAddress2 = new InputField();
					$inputAddress2->name = 'address2';
					$inputAddress2->description = 'Street Address';
					$inputAddress2->type = 'text';
					$inputAddress2->required = true;
					$inputAddress2->value = $address2;
					$inputAddress2->autofocus = $autofocus;
					$inputAddress2->validState = $validState['address2'];
					$inputAddress2->validMsg = $validMsg['address2'];
					echo $inputAddress2->display();
					
					// Unit/Suite - Address1
					$inputAddress1 = new InputField();
					$inputAddress1->name = 'address1';
					$inputAddress1->description = 'Unit/Suite';
					$inputAddress1->type = 'text';
					$inputAddress1->required = false;
					$inputAddress1->value = $address1;
					$inputAddress1->validState = $validState['address1'];
					$inputAddress1->validMsg = $validMsg['address1'];
					echo $inputAddress1->display();
					
					// City
					$inputCity = new InputField();
					$inputCity->name = 'city';
					$inputCity->description = 'City';
					$inputCity->type = 'text';
					$inputCity->required = true;
					$inputCity->value = $city;
					$inputCity->validState = $validState['city'];
					$inputCity->validMsg = $validMsg['city'];
					echo $inputCity->display();

					// State
					$dropDown = new DropDown();
					$dropDown->name = 'sub_code';
					$dropDown->values = array('', 'US-AL', 'US-AK', 'US-AZ', 'US-AR', 'US-CA', 'US-CO', 'US-CT', 'US-DE', 'US-DC', 'US-FL', 'US-GA', 'US-HI', 'US-ID', 'US-IL', 'US-IN', 'US-IA', 'US-KS', 'US-KY', 'US-LA', 'US-ME', 'US-MD', 'US-MA', 'US-MI', 'US-MN', 'US-MS', 'US-MO', 'US-MT', 'US-NE', 'US-NV', 'US-NH', 'US-NJ', 'US-NM', 'US-NY', 'US-NC', 'US-ND', 'US-OH', 'US-OK', 'US-OR', 'US-PA', 'US-RI', 'US-SC', 'US-SD', 'US-TN', 'US-TX', 'US-UT', 'US-VT', 'US-VA', 'US-WA', 'US-WV', 'US-WI', 'US-WY');
					$dropDown->descriptions = array('-- Choose --', 'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming');
					$dropDown->label = 'State';
					$dropDown->showLabel = true;
					$dropDown->required = true;
					$dropDown->currentValue = $sub_code;
					$dropDown->validState = $validState['sub_code'];
					$dropDown->validMsg = $validMsg['sub_code'];
					echo $dropDown->display();
					
					// ZIP
					$inputZIP = new InputField();
					$inputZIP->name = 'zip';
					$inputZIP->description = 'ZIP Code';
					$inputZIP->type = 'text';
					$inputZIP->required = false;
					$inputZIP->value = $zip;
					$inputZIP->validState = $validState['zip'];
					$inputZIP->validMsg = $validMsg['zip'];
					echo $inputZIP->display();
					
					// Telephone
					$inputTelephone = new InputField();
					$inputTelephone->name = 'telephone';
					$inputTelephone->description = 'Telephone Number';
					$inputTelephone->type = 'tel';
					$inputTelephone->required = false;
					$inputTelephone->value = $telephone;
					$inputTelephone->validState = $validState['telephone'];
					$inputTelephone->validMsg = $validMsg['telephone'];
					echo $inputTelephone->display();
					
					// Close Disabled
					if($disabled){
						echo '</fieldset>' . "\n";
					}
					?>
					<button type="submit" class="btn btn-primary" name="submit">Add Address</button>
        </form>
      </div>
    </div>  
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>