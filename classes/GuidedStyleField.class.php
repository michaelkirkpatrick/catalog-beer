<?php
/* ---
GuidedStyleField — renders the confidence-ladder "Style" field used on beer
add/edit. Emits the markup contract guided-style.js (v2) enhances: a visible
style_label input (the hero, never overwritten), the resolved tier as hidden
fields, plus empty card/picker mounts the script fills in.

    $guidedStyle = new GuidedStyleField();
    $guidedStyle->value = $styleLabel;           // brewer's raw label (style_label)
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
    public $value = '';            // style_label (raw text)
    public $styleId = '';          // style_id (hidden) — set when filed at style level
    public $parent = '';           // parent/family slug (hidden) — set when filed at family level
    public $class = '';            // super-class slug (hidden) — set when filed at class level
    public $beverageType = '';     // beverage_type (hidden)
    public $styleConfidence = '';  // confidence (hidden): confident|override|approx|family|catch-all|unresolved
    public $placeholder = '';
    public $hint = 'Type the style however you brand it — your exact wording is always kept.';
    public $required = false;
    public $validState = '';
    public $validMsg = '';

    public function display(){
        $attr = function($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); };
        $text = new Text(false, false, true);

        $invalid = ($this->validState === 'invalid');
        $inputClass = 'form-control sf-input' . ($invalid ? ' is-invalid' : ($this->validState === 'valid' ? ' is-valid' : ''));

        $return  = '<div class="mb-3">';
        $return .= '<label class="form-label" for="styleField">' . $text->get($this->description) . '</label>';
        $return .= '<div class="sf" data-sf>';
        $return .= '<input type="text" class="' . $inputClass . '" id="styleField" name="style_label" autocomplete="off"'
                 . ' placeholder="' . $attr($this->placeholder) . '"'
                 . ' data-hint="' . $attr($this->hint) . '"'
                 . ' value="' . $attr($this->value) . '"'
                 . ($this->required ? ' required' : '') . '>';
        $return .= '<input type="hidden" name="style_id" value="' . $attr($this->styleId) . '">';
        $return .= '<input type="hidden" name="parent" value="' . $attr($this->parent) . '">';
        $return .= '<input type="hidden" name="class" value="' . $attr($this->class) . '">';
        $return .= '<input type="hidden" name="beverage_type" value="' . $attr($this->beverageType) . '">';
        $return .= '<input type="hidden" name="style_confidence" value="' . $attr($this->styleConfidence) . '">';
        $return .= '<div class="sf-card" hidden></div>';
        $return .= '<div class="sf-picker" hidden></div>';
        $return .= '</div>';

        // Validation message (forced visible — the input isn't a direct sibling here)
        if($invalid){
            $text2 = new Text(true, true, true);
            $return .= '<div class="invalid-feedback d-block">' . $text2->get($this->validMsg) . '</div>';
        }

        $return .= '</div>';
        return $return;
    }
}
?>
