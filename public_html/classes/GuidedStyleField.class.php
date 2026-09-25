<?php
/* ---
GuidedStyleField — renders the confidence-ladder "Style" field used on beer
add/edit. Emits the markup contract guided-style.js (v2) enhances: a visible
style input (the hero, never overwritten), the resolved tier as hidden
fields, plus empty card/picker mounts the script fills in.

    $guidedStyle = new GuidedStyleField();
    $guidedStyle->value = $styleLabel;           // brewer's raw label (style)
    $guidedStyle->styleId = $styleID;            // resolved canonical style_id
    $guidedStyle->parent = $styleParent;         // resolved family slug
    $guidedStyle->class = $styleClass;           // resolved class slug
    $guidedStyle->beverageType = $beverageType;  // derived
    $guidedStyle->styleConfidence = $styleConfidence; // how style_id was arrived at
    $guidedStyle->validState = $validState['style'];
    $guidedStyle->validMsg = $validMsg['style'];
    $guidedStyle->required = true;
    echo $guidedStyle->display();

Requires (page-level): the guided-style.css/js assets and an inlined
window.CB_TAX (see StyleList::inlineScript).
--- */
class GuidedStyleField {

    public $description = 'Style';
    public $value = '';            // style (the brewer's raw label)
    public $styleId = '';          // style_id (hidden) — set when filed at style level
    public $parent = '';           // parent/family slug (hidden) — set when filed at family level
    public $class = '';            // super-class slug (hidden) — set when filed at class level
    public $beverageType = '';     // beverage_type (hidden)
    public $styleConfidence = '';  // confidence (hidden): confident|override|family|catch-all|unresolved
    public $placeholder = '';
    public $hint = 'Type the style however you brand it — your exact wording is always kept.';
    public $required = false;
    public $validState = '';
    public $validMsg = '';

    public function display(){

        $invalid = ($this->validState === 'invalid');
        $inputClass = 'cbf-input sf-input' . ($invalid ? ' is-invalid' : '');

        $return  = '<div class="cbf-field">';
        $return .= '<div class="cbf-labelrow">';
        $return .= '<label class="cbf-label" for="styleField">' . h($this->description) . '</label>';
        if($this->required){
            $return .= '<span class="cbf-req" aria-hidden="true">*</span>';
        }
        $return .= '</div>';
        $return .= '<div class="sf" data-sf>';
        $return .= '<input type="text" class="' . $inputClass . '" id="styleField" name="style" autocomplete="off"'
                 . ' placeholder="' . h($this->placeholder) . '"'
                 . ' value="' . h($this->value) . '"'
                 . ($this->required ? ' required' : '') . '>';
        $return .= '<input type="hidden" name="style_id" value="' . h($this->styleId) . '">';
        $return .= '<input type="hidden" name="parent" value="' . h($this->parent) . '">';
        $return .= '<input type="hidden" name="class" value="' . h($this->class) . '">';
        $return .= '<input type="hidden" name="beverage_type" value="' . h($this->beverageType) . '">';
        $return .= '<input type="hidden" name="style_confidence" value="' . h($this->styleConfidence) . '">';
        $return .= '<div class="sf-card" hidden></div>';
        $return .= '<div class="sf-picker" hidden></div>';
        $return .= '</div>';

        // Hint — persistent, visible guidance (was a data-hint attribute nothing read)
        if($this->hint !== ''){
            $return .= '<p class="cbf-hint">' . h($this->hint) . '</p>';
        }

        // Validation message
        if($invalid){
            // Plain text now — see InputField for why this renders the same.
            $return .= '<div class="cbf-err"><span class="cbf-err__m" aria-hidden="true">!</span><div>' . h($this->validMsg) . '</div></div>';
        }

        $return .= '</div>';
        return $return;
    }
}
?>
