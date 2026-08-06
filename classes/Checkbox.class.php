<?php
/* ---
Native checkbox row (.cbf-checkrow, catalog-forms.css) — accent-color does the
styling, no custom control to maintain.

$checkbox = new Checkbox();
$checkbox->validState = $var;   // 'invalid' shows $validMsg under the row
$checkbox->validMsg = $var;
echo $checkbox->display('name', 'text', 'value', $variable);

*** $text is DEVELOPER-AUTHORED HTML and is emitted RAW. ***

It is the checkbox label, and the one caller needs a link in it ("I agree to
the <a href="/terms">Terms &amp; Conditions</a>"), so this argument carries
markup by contract -- the same deal as Alert::$msg. It used to be Markdown,
which made that link possible while looking like plain text.

So: write the HTML yourself, including the entities (&amp;, not a bare &), and
NEVER pass a value that came from a user, the API or the database. If you need
user data in a label, escape it at the interpolation point with h(). Every
other argument here is escaped for you.

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
        $html .= '<input type="checkbox" value="' . h($this->value) . '" name="' . h($this->name) . '" id="check' . h($this->name) . '"';

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

        // Label -- RAW BY CONTRACT. See the docblock: $text is developer-authored
        // HTML so the Terms link can exist, and it must never carry user data.
        $html .= '<label for="check' . h($this->name) . '">' . $this->text . "</label>\n";
        $html .= "</div>\n";

        // Validation State
        if($invalid && $this->validMsg !== ''){
            // Escaped, unlike $text: validation messages are API-authored data.
            $message = h($this->validMsg);
            $html .= '<div class="cbf-err"><span class="cbf-err__m" aria-hidden="true">!</span><div>' . $message . '</div></div>' . "\n";
        }

        $html .= "</div>\n";

        // Return
        return $html;
    }
}
?>
