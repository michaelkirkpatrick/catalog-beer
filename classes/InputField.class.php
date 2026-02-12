<?php
/* ---
Copyright 2017 Michael Eason Kirkpatrick. All rights reserved.

name: name=""
description: Text to display describing the field

Text Fields: name, description, type, placeholder, value, validMsg, addBefore, addAfter

TRUE/FALSE Fields: required, autofocus

Options: validState = 'success', 'warning', 'error'

Numeric: max_length (If == 0){don't add parameter}

// Input Field
$input = new InputField();
$input->description = '';
$input->placeholder = '';
$input->name = '';
$input->value = $var;
$input->validState = $var;
$input->validMsg = $var;
echo $input->display();

--- */
class InputField {
	
	// Public
	public $addAfter = '';
	public $addBefore = '';
	public $autofocus = false;
	public $description = '';
	public $maxLength = 255;
	public $name = '';
	public $placeholder = '';
	public $required = false;
	public $type = 'text';
	public $validState = '';
	public $validMsg = '';
	public $value;

	public function display(){
		
		// HTML Purifier
		$text = new Text(false, false, true);

		// Default Values
		$classAdd = '';

		// Start Div
		$return = '<div class="mb-3">';

		// Label
		$return .= '<label class="form-label" for="' . $text->get($this->name) . 'Field">' . $text->get($this->description);
		if(!$this->required){
			$return .= ' <span class="text-muted" style="font-weight:400">(optional)</span>';
		}
		$return .= '</label>';

		// Add On
		if(!empty($this->addBefore) || !empty($this->addAfter)){
			$return .= '<div class="input-group">';
		}
		if(!empty($this->addBefore)){
			$return .= '<span class="input-group-text">' . $text->get($this->addBefore) . '</span>';
		}

		// Input Field
		$validInputClass = '';
		if(!empty($this->validState)){
			if($this->validState === 'valid'){
				$validInputClass = ' is-valid';
			}elseif($this->validState === 'invalid'){
				$validInputClass = ' is-invalid';
			}
		}
		$return .= '<input type="' . $this->type . '" class="form-control' . $validInputClass . '" id="' . $text->get($this->name) . 'Field" placeholder="' . $text->get($this->placeholder) . '" name="' . $text->get($this->name) . '"';
		if($this->maxLength !== 0){
			$return .= 'maxlength="' . $this->maxLength . '"';
		}
		if(!empty($this->value) || $this->value === 0 || $this->value === '0'){
			$return .= ' value="' . $text->get($this->value) . '"';
		}
		if(!empty($this->validState)){
			$return .= ' aria-describedby="helpMsg' . $text->get($this->name) . '"';
		}
		if($this->required){
			$return .= ' required';
		}
		if($this->autofocus){
			$return .= ' autofocus';
		}
		$return .= '>';

		// Close Add On
		if(!empty($this->addAfter)){
			$return .= '<span class="input-group-text">' . $text->get($this->addAfter) . '</span>';
		}
		if(!empty($this->addBefore) || !empty($this->addAfter)){
			$return .= '</div>';
		}

		// Validation State
		if($this->validState === 'invalid'){
			// Message
			$text2 = new Text(true, true, true);
			$message = $text2->get($this->validMsg);
			$return .= '<div class="invalid-feedback">' . $message . '</div>';
		}

		// Close Div
		$return .= "</div>";

		// Return Data
		return $return;
	}
}
?>