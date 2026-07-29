<?php
/* ---
Copyright 2017 Michael Eason Kirkpatrick. All rights reserved.

Renders a .cbf-field select (.cbf-input.cbf-select, catalog-forms.css).

name - Field Name
values - (array) Values for the option
descriptions - (array) Descriptions for the options
label - Label for the field (e.g. "Beer")
showLabel - Default False
currentValue
validState
validMsg
markRequired

$dropDown = new DropDown();
$dropDown->name = '';
$dropDown->values = array();
$dropDown->descriptions = array();
$dropDown->label = '';
$dropDown->showLabel = true;
$dropDown->currentValue = $var;
$dropDown->validState = $validState[''];
$dropDown->validMsg = $validMsg[''];
echo $dropDown->display();
--- */

class DropDown {

    // Public
    public $autocomplete = '';      // e.g. 'off' to suppress browser autofill
    public $name = '';
    public $values = array();
    public $descriptions = array();
    public $label = '';
    public $showLabel = false;
    public $currentValue = '';
    public $validState = '';
    public $validMsg = '';
    public $disabled = false;
    public $required = false;
    public $markRequired = true;    // false on all-required forms

    public function display(){

        // Selected Value Tagged?
        $selectedShown = false;

        // Default for Label
        if(empty($this->showLabel)){
            $this->showLabel = false;
        }

        // Invalid? ('warning'/'error' arrive from older callers; treat as invalid)
        $invalid = in_array($this->validState, array('warning', 'error', 'invalid'), true);

        // Start Field
        $return = '<div class="cbf-field">' . "\n";

        // Label Row
        if($this->showLabel){
            $return .= '<div class="cbf-labelrow">';
            $return .= '<label class="cbf-label" for="' . htmlspecialchars($this->name) . 'Field">' . htmlspecialchars($this->label) . '</label>';
            if($this->required && $this->markRequired){
                $return .= '<span class="cbf-req" aria-hidden="true">*</span>';
            }
            $return .= '</div>' . "\n";
        }

        // Select
        $return .= '<select class="cbf-input cbf-select' . ($invalid ? ' is-invalid' : '') . '" name="' . htmlspecialchars($this->name) . '" id="' . htmlspecialchars($this->name) . 'Field"';
        if($invalid){
            $return .= ' aria-describedby="helpMsg' . htmlspecialchars($this->name) . '"';
        }
        if(!empty($this->autocomplete)){
            $return .= ' autocomplete="' . htmlspecialchars($this->autocomplete) . '"';
        }
        if($this->disabled){
            $return .= ' disabled';
        }
        if($this->required){
            $return .= ' required';
        }
        $return .= '>' . "\n";

        // Options
        for($i=0; $i<count($this->values); $i++){
            $return .= '<option value="' . htmlspecialchars($this->values[$i]) . '"';
            if($this->currentValue === $this->values[$i] && !$selectedShown){
                // Show as selected
                $return .= ' selected';

                // Erase current value to prevent future matches
                $selectedShown = true;
            }
            $return .= '>' . htmlspecialchars($this->descriptions[$i]) . '</option>' . "\n";
        }

        // Close Select
        $return .= '</select>' . "\n";

        // Validation State
        if($invalid){
            $return .= '<div class="cbf-err" id="helpMsg' . htmlspecialchars($this->name) . '"><span class="cbf-err__m" aria-hidden="true">!</span><span>' . htmlspecialchars($this->validMsg) . '</span></div>' . "\n";
        }

        // Close Field
        $return .= "</div>" . "\n";

        // Return
        return $return;
    }
}
?>
