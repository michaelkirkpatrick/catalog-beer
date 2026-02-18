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
if(isset($beerData->error) || !isset($beerData->id)){
	http_response_code(404);
	header('location: /error_page/404.php');
	exit();
}

// Brewer Info
$text1 = new Text(false, true, true);
$text2 = new Text(false, false, true);
$brewerName = $text1->get($beerData->brewer->name);
$brewerURL = $text2->get($beerData->brewer->id);

// Default Values from Existing Data
$validState = array('brewer_id'=>'', 'name'=>'', 'style'=>'', 'description'=>'', 'abv'=>'', 'ibu'=>'');
$validMsg = array('brewer_id'=>'', 'name'=>'', 'style'=>'', 'description'=>'', 'abv'=>'', 'ibu'=>'');
$name = $beerData->name;
$style = $beerData->style;
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
		$style = $_POST['style'];
		$description = $_POST['description'];
		$abv = $_POST['abv'];
		$ibu = $_POST['ibu'];

		$patchData = array('name'=>$name, 'style'=>$style, 'description'=>$description, 'abv'=>$abv, 'ibu'=>$ibu);
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
$beerName = $text1->get($beerData->name);
$htmlHead = new htmlHead('Edit ' . $beerName);
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Beer'); ?>
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, $beerName, 'Edit');
				$nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerURL, '/beer/' . $beerID);
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

					// Style
					$inputStyle = new InputField();
					$inputStyle->name = 'style';
					$inputStyle->description = 'Style';
					$inputStyle->type = 'text';
					$inputStyle->required = true;
					$inputStyle->value = $style;
					$inputStyle->validState = $validState['style'];
					$inputStyle->validMsg = $validMsg['style'];
					echo $inputStyle->display();

					// Description
					$textarea = new Textarea();
					$textarea->name = 'description';
					$textarea->description = 'Description';
					$textarea->value = $description;
					$textarea->validState = $validState['description'];
					$textarea->validMsg = $validMsg['description'];
					echo $textarea->display();

					// ABV
					$inputAbv = new InputField();
					$inputAbv->name = 'abv';
					$inputAbv->description = 'abv';
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
					echo $inputIbu->display();
					?>
					<button type="submit" class="btn btn-primary" name="submit">Save Changes</button>
					<a href="/beer/<?php echo htmlspecialchars($beerID); ?>" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>
