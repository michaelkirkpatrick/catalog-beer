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

// Must have an existing address to edit
if(!isset($locationData->address)){
	// No address yet — redirect to add-address instead
	header('location: /location/' . $locationID . '/add-address');
	exit();
}

// Location & Brewer Info
$text1 = new Text(false, true, true);
$text2 = new Text(false, false, true);
$locationName = $text1->get($locationData->name);
$brewerName = $text1->get($locationData->brewer->name);
$brewerID = $text2->get($locationData->brewer->id);

// Default Values from Existing Address
$validState = array('address1'=>'', 'address2'=>'', 'city'=>'', 'sub_code'=>'', 'zip'=>'', 'telephone'=>'');
$validMsg = array('address1'=>'', 'address2'=>'', 'city'=>'', 'sub_code'=>'', 'zip'=>'', 'telephone'=>'');
$address1 = $locationData->address->address1 ?? '';
$address2 = $locationData->address->address2 ?? '';
$city = $locationData->address->city ?? '';
$sub_code = $locationData->address->sub_code ?? '';
$zip = '';
$zip5 = '';
$zip4 = '';
if(!empty($locationData->address->zip5)){
	$zip = $locationData->address->zip5;
	if(!empty($locationData->address->zip4)){
		$zip .= '-' . $locationData->address->zip4;
	}
}
$telephone = '';
if(!empty($locationData->address->telephone)){
	$telephone = $locationData->address->telephone;
}

// Process Form
if(isset($_POST['submit'])){
	if(!csrf_verify()){
		$alert->msg = 'Invalid form submission. Please try again.';
		$alert->type = 'error';
	}else{
		// Get Posted Variables
		$address1 = $_POST['address1'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$sub_code = $_POST['sub_code'];
		$zip = $_POST['zip'];
		$telephone = $_POST['telephone'];

		// Process ZIP Code
		$zip5 = '';
		$zip4 = '';
		if(!empty($zip)){
			$zip5 = substr($zip, 0, 5);
			if(strlen($zip) > 5){
				$zip4 = substr($zip, 6, 4);
			}
		}

		$patchData = array('address1'=>$address1, 'address2'=>$address2, 'city'=>$city, 'sub_code'=>$sub_code, 'zip5'=>$zip5, 'zip4'=>$zip4, 'telephone'=>$telephone);
		$patchResponse = $api->request('PUT', '/address/' . $locationID, $patchData);
		$patchArray = json_decode($patchResponse, true);
		if(isset($patchArray['error'])){
			$alert->msg = $patchArray['error_msg'];
			$validState = $patchArray['valid_state'];
			$validMsg = $patchArray['valid_msg'];

			$validState['zip'] = $patchArray['valid_state']['zip5'] ?? '';
			$validMsg['zip'] = $patchArray['valid_msg']['zip5'] ?? '';
		}else{
			// Success
			header('location: /brewer/' . $brewerID);
			exit();
		}
	}
}

// HTML Head
$htmlHead = new htmlHead('Edit Address for ' . $locationName);
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Brewers'); ?>
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Edit Address for ' . $locationName);
				$nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
				echo $nav->breadcrumbs();

				// Display Alerts
				echo $alert->display();
				?>
        <form method="post">
					<?php echo csrf_field(); ?>
					<?php
					// Street Address - Address2
					$inputAddress2 = new InputField();
					$inputAddress2->name = 'address2';
					$inputAddress2->description = 'Street Address';
					$inputAddress2->type = 'text';
					$inputAddress2->required = true;
					$inputAddress2->value = $address2;
					$inputAddress2->autofocus = true;
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
