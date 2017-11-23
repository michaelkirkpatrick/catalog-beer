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
$style = '';
$description = '';
$abv = '';
$ibu = '';

// Get Brewery Info
if(isset($_GET['brewerID'])){
	$brewerID = $_GET['brewerID'];
	$brewerResp = $api->request('GET', '/brewer/' . $brewerID, '');
	$brewerData = json_decode($brewerResp);
	if(!isset($brewerResp->error)){
		// Save Brewer Info
		$text1 = new Text(false, true, true);
		$brewerName = $text1->get($brewerData->name);
		
		$text2 = new Text(false, false, true);
		$brewerURL = $text2->get($brewerData->id);
		
		// Process Form
		if(isset($_POST['submit'])){
			// Get Posted Variables
			$name = $_POST['name'];
			$style = $_POST['style'];
			$description = $_POST['description'];
			$abv = $_POST['abv'];
			$ibu = $_POST['ibu'];

			$beerPOST = array('brewer_id'=>$brewerID, 'name'=>$name, 'style'=>$style, 'description'=>$description, 'abv'=>$abv, 'ibu'=>$ibu);
			$beerResponse = $api->request('POST', '/beer', $beerPOST);
			$beerData = json_decode($beerResponse, true);
			if(!isset($beerData['error'])){
				// Successfully Added
				$_SESSION['add_beer_success'] = true;
				header('location: /beer/' . $beerData['id']);
				exit();
			}else{
				// Error Adding Beer
				$alert->msg = $beerData['error_msg'];
				$validState = $beerData['valid_state'];
				$validMsg = $beerData['valid_msg'];
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
$htmlHead = new htmlHead('Add a Beer');
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar('Beer'); ?>
	<div class="container">
    <div class="row">
    	<div class="col">
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Add Beer');
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
					
					// Close Disabled
					if($disabled){
						echo '</fieldset>' . "\n";
					}
					?>
					<button type="submit" class="btn btn-primary" name="submit">Add Beer</button>
        </form>
      </div>
    </div>  
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>