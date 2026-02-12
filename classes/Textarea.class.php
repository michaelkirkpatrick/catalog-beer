<?php
/* ---
Text Fields: $this->name, $this->description, $this->value, $this->validMsg

TRUE/FALSE Fields: $this->required

Options: $this->validState = 'success', 'warning', 'error'

Numeric: $this->rows

// Text Area
$textarea = new Textarea();
$textarea->name = '';
$textarea->description = '';
$textarea->value = $var;
$textarea->validState = $var;
$textarea->validMsg = $var;
echo $textarea->display();

--- */

class Textarea {
	
	public $name = '';
	public $description = '';
	public $value = '';
	public $required = false;
	public $validState = '';
	public $validMsg = '';
	public $rows = 3;

	public function display(){

		// Error Class
		$feedbackStates = array('valid', 'invalid');
		if(in_array($this->validState, $feedbackStates)){
			$validation = true;
			if($this->validState == 'valid'){$classAdd = ' is-valid';}
			if($this->validState == 'invalid'){$classAdd = ' is-invalid';}
		}else{
			$validation = false;
			$classAdd = '';
		}

		// Start Div
		$return = '<div class="mb-3">';

		// Label
		$return .= '<label for="' . htmlspecialchars($this->name) . 'Field" class="form-label">' . $this->description;
		if(!$this->required){
			$return .= ' <span class="text-muted" style="font-weight:400">(optional)</span>';
		}
		$return .= '</label>';

		// Textarea Field Start
		$return .= '<textarea class="form-control' . $classAdd . '" id="' . htmlspecialchars($this->name) . 'Field" name="' . $this->name . '" rows="' . $this->rows . '"';
		if($validation){
			$return .= ' aria-describedby="helpMsg' . htmlspecialchars($this->name) . '"';
		}
		if($this->required){
			$return .= ' required';
		}
		$return .= '>';

		// Content
		if(!empty($this->value)){
			$return .= htmlspecialchars($this->value);
		}

		// Close Textarea
		$return .= '</textarea>';


		// Validation State
		if($this->validState == 'invalid'){
			// Message
			$text = new Text(true, true, true);
			$message = $text->get($this->validMsg);
			$return .= '<div class="invalid-feedback">' . $message . '</div>';
		}

		// Close Div
		$return .= "</div>";

		// Return Data
		return $return;
	}
}
?>