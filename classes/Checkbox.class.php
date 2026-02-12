<?php
/* ---
$checkbox = new Checkbox();
echo $checkbox->display('name', 'text', 'value', $variable);

FINISH UPDATING
http://getbootstrap.com/docs/4.0/components/forms/#custom-forms

--- */

class Checkbox{
	
	// Variables
	public $name = '';
	public $text = '';
	public $value = '';
	public $variable; // array() or $var
	public $validState = '';
	
	function display($name, $text, $value, $variable){
		
		// Save to Class
		$this->name = $name;
		$this->text = $text;
		$this->value = $value;
		$this->variable = $variable;
		
		// Default Values
		$classAdd = '';

		// Error Class
		$inputClassAdd = '';
		$feedbackStates = array('valid', 'invalid');
		if(in_array($this->validState, $feedbackStates)){
			if($this->validState === 'valid'){$inputClassAdd = ' is-valid';}
			if($this->validState === 'invalid'){$inputClassAdd = ' is-invalid';}
		}

		// Start Div
		$html = '<div class="form-check">' . "\n";

		// Begin Checkbox
		$text1 = new Text(false, false, true);
		$html .= '<input class="form-check-input' . $inputClassAdd . '" type="checkbox" value="' . $text1->get($this->value) . '" name="' . $text1->get($this->name) . '" id="check' . $text1->get($this->name) . '"';
		
		// Checked?
		if(is_array($this->variable)){
			// Array -- Is in_array?
			if(in_array($this->value, $this->variable)){
				$html .= ' checked';
			}
		}else{
			// Single Variable -- Match?
			if($this->variable == $this->value){
				$html .= ' checked';
			}
		}		
		$html .= '>';
		
		// Label
		$text2 = new Text(true, true, true);
		$textString = $text2->get($this->text);
		$html .= '<label class="form-check-label" for="check' . $text1->get($this->name) . '"> ' . $textString . "</label>\n";
		$html .= "</div>\n";

		// Return
		return $html;
	}
}
?>