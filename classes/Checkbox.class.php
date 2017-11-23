<?php
/* ---
$checkbox = new Checkbox();
echo $checkbox->display('name', 'text', 'value', $variable);
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
		$feedbackStates = array('valid', 'invalid');
		if(in_array($this->validState, $feedbackStates)){
			$classAdd = ' has-feedback';
			if($this->validState == 'valid'){$classAdd .= ' text-success';}
			if($this->validState == 'invalid'){$classAdd .= ' text-danger';}
		}

		// Start Div
		$html = '<div class="form-check">' . "\n";

		// Begin Checkbox
		$text1 = new Text(false, false, true);
		$html .= '<label class="form-check-label' . $classAdd . '">';
		$html .= '<input type="checkbox" value="' . $text1->get($this->value) . '" name="' . $text1->get($this->name) . '" class="form-check-input"';
		
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
		
		// Finish HTML
		$text2 = new Text(true, true, true);
		$textString = $text2->get($this->text);
		
		$html .= '>';
		$html .= ' ' . $textString;
		$html .= "</label>\n";
		$html .= "</div>\n";

		// Return
		return $html;
	}
}
?>