<?php
/* ---
Copyright 2017 Michael Eason Kirkpatrick. All rights reserved.

name - Field Name
values - (array) Values for the option
descriptions - (array) Descriptions for the options
label - Label for the field (e.g. "Beer")
showLabel - Default False
currentValue
validState
validMsg

$dropDown = new DropDown();
$dropDown->name = '';
$dropDown->values = array();
$dropDown->descriptions = array();
$dropDown->label = '';
$dropDown->showLabel = true;
$dropDown->currentValue = $var;
$dropDown->validState = $validState[''];
$dropDown->validMsg = $validMsg[''];
echo $dropDown->display();
--- */

class DropDown {

	// Public
	public $name = '';
	public $values = array();
	public $descriptions = array();
	public $label = '';
	public $showLabel = false;
	public $currentValue = '';
	public $validState = '';
	public $validMsg = '';
	public $disabled = false;
	public $required = false;

	public function display(){
		
		// Selected Value Tagged?
		$selectedShown = false;

		// Default for Label
		if(empty($this->showLabel)){
			$this->showLabel = false;
		}

		// Error Class
		// Validation class for select element
		$selectClassAdd = '';
		if(!empty($this->validState)){
			if($this->validState === 'success' || $this->validState === 'valid'){$selectClassAdd = ' is-valid';}
			if($this->validState === 'warning' || $this->validState === 'error' || $this->validState === 'invalid'){$selectClassAdd = ' is-invalid';}
		}

		// Start Div
		$return = '<div class="mb-3">' . "\n";

		// Label
		if($this->showLabel){
			$return .= '<label for="' . htmlspecialchars($this->name) . 'Field" class="form-label">' . htmlspecialchars($this->label);	
			if(!$this->required){
				$return .= ' <span class="text-muted" style="font-weight:400">(optional)</span>';
			}
			$return .= '</label>' . "\n";
		}

		// Select
		$return .= '<select class="form-select' . $selectClassAdd . '" name="' . htmlspecialchars($this->name) . '" id="' . htmlspecialchars($this->name) . 'Field"';
		if(!empty($this->validState)){
			$return .= ' aria-describedby="helpMsg' . htmlspecialchars($this->name) . '"';
		}
		if($this->disabled){
			$return .= ' disabled';
		}
		if($this->required){
			$return .= ' required';
		}
		$return .= '>' . "\n";

		// Options
		for($i=0; $i<count($this->values); $i++){
			$return .= '<option value="' . htmlspecialchars($this->values[$i]) . '"';
			if($this->currentValue === $this->values[$i] && !$selectedShown){
				// Show as selected
				$return .= ' selected';
				
				// Erase current value to prevent future matches
				$selectedShown = true;
			}
			$return .= '>' . htmlspecialchars($this->descriptions[$i]) . '</option>' . "\n";
		}

		// Close Select
		$return .= '</select>' . "\n";

		// Validation State
		if(!empty($this->validState)){

			// Validation Message
			if($this->validState === 'success' || $this->validState === 'valid'){
				$this->validMsg = '(success)';
				$validation_class = 'valid-feedback';
			}else{
				$validation_class = 'invalid-feedback';
			}
			$return .= '<div class="' . $validation_class . '" id="helpMsg' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->validMsg) . '</div>' . "\n";
		}

		// Close Div
		$return .= "</div>" . "\n";

		// Return
		return $return;
	}
}
?>