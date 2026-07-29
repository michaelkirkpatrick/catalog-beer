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
        $return .= '<label class="cbf-label" for="' . htmlspecialchars($this->name) . 'Field">' . $this->description . '</label>';
        if($this->required && $this->markRequired){
            $return .= '<span class="cbf-req" aria-hidden="true">*</span>';
        }
        $return .= '</div>';

        // Textarea Field Start
        $return .= '<textarea class="cbf-input' . ($invalid ? ' is-invalid' : '') . '" id="' . htmlspecialchars($this->name) . 'Field" name="' . $this->name . '" rows="' . $this->rows . '"';
        if($invalid){
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

        // Hint
        if($this->hint !== ''){
            $text = new Text(false, false, true);
            $return .= '<p class="cbf-hint">' . $text->get($this->hint) . '</p>';
        }

        // Validation State
        if($invalid){
            // Message — a div, not a p: the Markdown pass wraps it in <p> tags
            $text2 = new Text(true, true, true);
            $message = $text2->get($this->validMsg);
            $return .= '<div class="cbf-err" id="helpMsg' . htmlspecialchars($this->name) . '"><span class="cbf-err__m" aria-hidden="true">!</span><div>' . $message . '</div></div>';
        }

        // Close Field
        $return .= "</div>";

        // Return Data
        return $return;
    }
}
?>
