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
		$feedbackStates = array('success', 'warning', 'error');
		if(in_array($this->validState, $feedbackStates)){
			$classAdd = ' has-feedback';
			$validation = true;
			if($this->validState == 'success'){$classAdd .= ' has-success';}
			if($this->validState == 'warning'){$classAdd .= ' has-warning';}
			if($this->validState == 'error'){$classAdd .= ' has-error';}
		}else{
			$validation = false;
			$classAdd = '';
		}

		// Start Div
		$return = '<div class="form-group' . $classAdd . '">';

		// Label
		$return .= '<label for="' . htmlspecialchars($this->name) . 'Field" class="control-label">' . $this->description;
		if(!$this->required){
			$return .= ' <span class="text-muted" style="font-weight:400">(optional)</span>';
		}
		$return .= '</label>';

		// Textarea Field Start
		$return .= '<textarea class="form-control" id="' . htmlspecialchars($this->name) . 'Field" name="' . $this->name . '" rows="' . $this->rows . '"';
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
		if(!empty($this->validState)){

			// Validation Message
			if($this->validState == 'success'){
				$this->validMsg = '(success)';
				$validClass = 'sr-only';
			}else{
				$validClass = 'help-block';
			}
			$return .= '<span class="' . $validClass . '" id="helpMsg' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->validMsg) . '</span>';
		}

		// Close Div
		$return .= "</div>";

		// Return Data
		return $return;
	}
}
?>