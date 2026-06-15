<?php
/* ---
GuidedStyleField — renders the guided "Style" combobox used on beer add/edit.

Mirrors the InputField pattern, but emits the markup contract guided-style.js
enhances: a visible style_label text input plus hidden style_id and
beverage_type fields, a menu container, and a status line.

    $guidedStyle = new GuidedStyleField();
    $guidedStyle->value = $styleLabel;         // brewer's raw label (style_label)
    $guidedStyle->styleId = $styleID;          // resolved canonical style_id
    $guidedStyle->beverageType = $beverageType;// derived; pre-fills on edit
    $guidedStyle->validState = $validState['style'];
    $guidedStyle->validMsg = $validMsg['style'];
    $guidedStyle->required = true;
    echo $guidedStyle->display();

Requires (page-level): the guided-style.css/js assets and an inlined
window.CB_STYLES (see StyleList).
--- */
class GuidedStyleField {

    public $description = 'Style';
    public $value = '';            // style_label (raw text)
    public $styleId = '';          // style_id (hidden)
    public $beverageType = '';     // beverage_type (hidden)
    public $placeholder = 'Start typing — e.g. IPA, Tripel, Pilsner, Stout';
    public $hint = 'Start typing to match a canonical style. Your exact wording is always kept.';
    public $required = false;
    public $validState = '';
    public $validMsg = '';

    public function display(){
        // HTML Purifier (plain escaping), mirroring InputField
        $text = new Text(false, false, true);

        $attr = function($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); };

        $invalid = ($this->validState === 'invalid');
        $inputClass = 'form-control' . ($invalid ? ' is-invalid' : ($this->validState === 'valid' ? ' is-valid' : ''));

        $return  = '<div class="mb-3">';
        $return .= '<label class="form-label" for="styleField">' . $text->get($this->description) . '</label>';
        $return .= '<div class="cb-style" data-cb-style>';
        $return .= '<input type="text" class="' . $inputClass . '" id="styleField" name="style_label" autocomplete="off"'
                 . ' placeholder="' . $attr($this->placeholder) . '"'
                 . ' data-hint="' . $attr($this->hint) . '"'
                 . ' value="' . $attr($this->value) . '"'
                 . ($this->required ? ' required' : '') . '>';
        $return .= '<input type="hidden" name="style_id" value="' . $attr($this->styleId) . '">';
        $return .= '<input type="hidden" name="beverage_type" value="' . $attr($this->beverageType) . '">';
        $return .= '<div class="cb-menu" hidden></div>';
        $return .= '</div>';

        // Validation message (forced visible — the input isn't a direct sibling here)
        if($invalid){
            $text2 = new Text(true, true, true);
            $return .= '<div class="invalid-feedback d-block">' . $text2->get($this->validMsg) . '</div>';
        }

        // Resolution status line (populated by guided-style.js)
        $return .= '<div class="form-text cb-status"></div>';

        $return .= '</div>';
        return $return;
    }
}
?>
