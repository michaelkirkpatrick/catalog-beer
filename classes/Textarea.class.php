<?php
/* ---
Renders a .cbf-field textarea (catalog-forms.css).

Text Fields: $this->name, $this->description, $this->value, $this->validMsg, $this->hint

TRUE/FALSE Fields: $this->required, $this->markRequired

Options: $this->validState = 'valid', 'invalid'

Numeric: $this->rows

// Text Area
$textarea = new Textarea();
$textarea->name = '';
$textarea->description = '';
$textarea->hint = '';
$textarea->value = $var;
$textarea->validState = $var;
$textarea->validMsg = $var;
echo $textarea->display();

--- */

class Textarea {

    public $name = '';
    public $description = '';
    public $hint = '';              // guidance under the field (e.g. "Markdown supported.")
    public $value = '';
    public $required = false;
    public $markRequired = true;    // false on all-required forms
    public $validState = '';
    public $validMsg = '';
    public $rows = 3;

    public function display(){

        $invalid = ($this->validState === 'invalid');

        // Start Field
        $return = '<div class="cbf-field">';

        // Label Row
        $return .= '<div class="cbf-labelrow">';
        $return .= '<label class="cbf-label" for="' . h($this->name) . 'Field">' . h($this->description) . '</label>';
        if($this->required && $this->markRequired){
            $return .= '<span class="cbf-req" aria-hidden="true">*</span>';
        }
        $return .= '</div>';

        // Textarea Field Start
        $return .= '<textarea class="cbf-input' . ($invalid ? ' is-invalid' : '') . '" id="' . h($this->name) . 'Field" name="' . h($this->name) . '" rows="' . $this->rows . '"';
        if($invalid){
            $return .= ' aria-describedby="helpMsg' . h($this->name) . '"';
        }
        if($this->required){
            $return .= ' required';
        }
        $return .= '>';

        // Content
        if(!empty($this->value)){
            $return .= h($this->value);
        }

        // Close Textarea
        $return .= '</textarea>';

        // Hint
        if($this->hint !== ''){
            $return .= '<p class="cbf-hint">' . h($this->hint) . '</p>';
        }

        // Validation State
        if($invalid){
            // Plain text now — see InputField for why dropping the Markdown
            // pass leaves this looking identical.
            $message = h($this->validMsg);
            $return .= '<div class="cbf-err" id="helpMsg' . h($this->name) . '"><span class="cbf-err__m" aria-hidden="true">!</span><div>' . $message . '</div></div>';
        }

        // Close Field
        $return .= "</div>";

        // Return Data
        return $return;
    }
}
?>
