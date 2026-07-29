<?php
/* ---
Native checkbox row (.cbf-checkrow, catalog-forms.css) — accent-color does the
styling, no custom control to maintain.

$checkbox = new Checkbox();
$checkbox->validState = $var;   // 'invalid' shows $validMsg under the row
$checkbox->validMsg = $var;
echo $checkbox->display('name', 'text', 'value', $variable);

--- */

class Checkbox{

    // Variables
    public $name = '';
    public $text = '';
    public $value = '';
    public $variable; // array() or $var
    public $validState = '';
    public $validMsg = '';

    function display($name, $text, $value, $variable){

        // Save to Class
        $this->name = $name;
        $this->text = $text;
        $this->value = $value;
        $this->variable = $variable;

        $invalid = ($this->validState === 'invalid');

        // Start Field
        $html = '<div class="cbf-field"><div class="cbf-checkrow">' . "\n";

        // Begin Checkbox
        $text1 = new Text(false, false, true);
        $html .= '<input type="checkbox" value="' . $text1->get($this->value) . '" name="' . $text1->get($this->name) . '" id="check' . $text1->get($this->name) . '"';

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
        $html .= '<label for="check' . $text1->get($this->name) . '">' . $textString . "</label>\n";
        $html .= "</div>\n";

        // Validation State
        if($invalid && $this->validMsg !== ''){
            $message = $text2->get($this->validMsg);
            $html .= '<div class="cbf-err"><span class="cbf-err__m" aria-hidden="true">!</span><div>' . $message . '</div></div>' . "\n";
        }

        $html .= "</div>\n";

        // Return
        return $html;
    }
}
?>
