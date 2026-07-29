<?php
/* ---
Copyright 2017 Michael Eason Kirkpatrick. All rights reserved.

Renders a .cbf-field (catalog-forms.css): mono uppercase label, amber
required asterisk, optional unit suffix (.cbf-unitwrap), hint line and
field-level error.

Text Fields: name, description, type, placeholder, value, validMsg, addBefore, addAfter, hint

TRUE/FALSE Fields: required, autofocus, markRequired, showCount

Options: validState = 'valid', 'invalid'

Numeric: max_length (If == 0){don't add parameter}

// Input Field
$input = new InputField();
$input->description = '';
$input->hint = '';
$input->name = '';
$input->value = $var;
$input->validState = $var;
$input->validMsg = $var;
echo $input->display();

--- */
class InputField {

    // Public
    public $addAfter = '';          // unit suffix ('%', 'IBU') — renders a .cbf-unitwrap
    public $addBefore = '';         // unit prefix — same treatment, left side
    public $autocomplete = '';      // e.g. 'off' to suppress browser autofill
    public $autofocus = false;
    public $dataAttributes = array();   // ['1p-ignore' => ''] renders data-1p-ignore
    public $description = '';
    public $hint = '';              // guidance under the field — replaces prose placeholders
    public $markRequired = true;    // false on all-required forms (login/signup/contact),
                                    // where marking every field says nothing
    public $maxLength = 255;
    public $name = '';
    public $placeholder = '';       // unit examples only ('0.0') — no prose placeholders
    public $required = false;
    public $showCount = false;      // live "n / max" count in the label row (needs maxLength)
    public $type = 'text';
    public $validState = '';
    public $validMsg = '';
    public $value;

    public function display(){

        // HTML Purifier
        $text = new Text(false, false, true);

        $invalid = ($this->validState === 'invalid');
        $hasUnit = (!empty($this->addBefore) || !empty($this->addAfter));

        // Start Field
        $return = '<div class="cbf-field">';

        // Label Row
        $return .= '<div class="cbf-labelrow">';
        $return .= '<label class="cbf-label" for="' . $text->get($this->name) . 'Field">' . $text->get($this->description) . '</label>';
        if($this->required && $this->markRequired){
            $return .= '<span class="cbf-req" aria-hidden="true">*</span>';
        }
        if($this->showCount && $this->maxLength > 0){
            // Static render; a small page script keeps it live (data-count-for).
            $length = strlen((string)($this->value ?? ''));
            $return .= '<span class="cbf-count" data-count-for="' . $text->get($this->name) . 'Field">' . $length . ' / ' . $this->maxLength . '</span>';
        }
        $return .= '</div>';

        // Unit wrapper — the invalid state and focus ring live on the wrapper
        if($hasUnit){
            $return .= '<div class="cbf-unitwrap' . ($invalid ? ' is-invalid' : '') . '">';
            if(!empty($this->addBefore)){
                $return .= '<span class="cbf-unit">' . $text->get($this->addBefore) . '</span>';
            }
        }

        // Input Field
        $inputClass = 'cbf-input' . ((!$hasUnit && $invalid) ? ' is-invalid' : '');
        $return .= '<input type="' . $this->type . '" class="' . $inputClass . '" id="' . $text->get($this->name) . 'Field" name="' . $text->get($this->name) . '"';
        if($this->placeholder !== ''){
            $return .= ' placeholder="' . $text->get($this->placeholder) . '"';
        }
        if($this->maxLength !== 0){
            $return .= ' maxlength="' . $this->maxLength . '"';
        }
        if(!empty($this->value) || $this->value === 0 || $this->value === '0'){
            $return .= ' value="' . htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8') . '"';
        }
        if($invalid){
            $return .= ' aria-describedby="helpMsg' . $text->get($this->name) . '"';
        }
        if($this->required){
            $return .= ' required';
        }
        if($this->autofocus){
            $return .= ' autofocus';
        }
        if(!empty($this->autocomplete)){
            $return .= ' autocomplete="' . $text->get($this->autocomplete) . '"';
        }
        // Arbitrary data-* attributes, for the things only a vendor reads —
        // data-1p-ignore, data-lpignore. A key that isn't a plain attribute name
        // is dropped rather than escaped: it would be a bug, not a value.
        foreach($this->dataAttributes as $key => $dataValue){
            // A digit may lead: the attribute is data-1p-ignore, and it's the
            // "data-" that has to satisfy HTML's name grammar, not the suffix.
            if(preg_match('/^[a-z0-9][a-z0-9-]*$/', (string)$key) !== 1){
                continue;
            }
            $return .= ' data-' . $key;
            if((string)$dataValue !== ''){
                $return .= '="' . htmlspecialchars((string)$dataValue, ENT_QUOTES, 'UTF-8') . '"';
            }
        }
        $return .= '>';

        // Close Unit Wrapper
        if($hasUnit){
            if(!empty($this->addAfter)){
                $return .= '<span class="cbf-unit">' . $text->get($this->addAfter) . '</span>';
            }
            $return .= '</div>';
        }

        // Hint — persistent guidance; doesn't vanish on focus like a placeholder
        if($this->hint !== ''){
            $return .= '<p class="cbf-hint">' . $text->get($this->hint) . '</p>';
        }

        // Validation State
        if($invalid){
            // Message
            $text2 = new Text(true, true, true);
            $message = $text2->get($this->validMsg);
            // A div, not a p — the Markdown pass wraps the message in <p> tags.
            $return .= '<div class="cbf-err" id="helpMsg' . $text->get($this->name) . '"><span class="cbf-err__m" aria-hidden="true">!</span><div>' . $message . '</div></div>';
        }

        // Close Field
        $return .= "</div>";

        // Return Data
        return $return;
    }
}
?>
