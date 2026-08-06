<?php
/*
Warm alert family (.cbf-alert, catalog-forms.css): rust error / amber warning
and info / green success. One icon dot, message body alongside.

$alert->type = 'success/info/warning/error';

$alert = new Alert();
$alert->msg = '';
echo $alert->display();

*** $msg is DEVELOPER-AUTHORED HTML and is emitted RAW. ***

It used to run through Markdown, which is how the "create one", "list of
brewers" and "Add another beer" links worked. Those are now written as literal
<a href> and <strong>, so the property carries markup by contract -- the same
deal as Checkbox's $text.

So: write the HTML yourself, entities included (&amp;, not a bare &), and NEVER
assign a value that came from a user, the API or the database without escaping
it first. Interpolating one? Wrap that piece in h() at the interpolation point,
the way beer.php and account.php do.

Checked when this contract was introduced (6 Aug 2026), and worth re-checking
if the API changes: every error_msg the API can return is a fixed string or a
canned message delegated from $db / $uuid / $users / $stripe / $sendEmail. The
only interpolations are numeric (minutes, dollar caps) or internal identifiers,
and TextInput::check() names the problem rather than echoing the input. So the
~20 `$alert->msg = $x['error_msg']` sites carry no user data.
*/

class Alert {

    // Public
    public $type = 'error';
    public $dismissible = false;   // kept for back-compat; alerts no longer dismiss
    public $msg = '';

    public function display(){

        if(!empty($this->msg)){
            // Variant + icon per type. Error is the base class; warning and info
            // share the amber wash so only success and failure read differently.
            $class = 'cbf-alert';
            $icon = '!';
            if($this->type == 'success'){
                $class .= ' cbf-alert--ok';
                $icon = '&#10003;';   // ✓
            }elseif($this->type == 'warning' || $this->type == 'info'){
                $class .= ' cbf-alert--warn';
            }

            // ----- HTML Output -----
            // $msg is raw by contract -- see the docblock. Every caller was
            // audited when this changed; none pass unescaped user data.
            $return = '<div class="' . $class . '" role="alert">';
            $return .= '<span class="cbf-alert__i" aria-hidden="true">' . $icon . '</span>';
            $return .= '<div>' . $this->msg . '</div>';
            $return .= '</div>';
        }else{
            // No Message
            $return = '';
        }

        // Return
        return $return;
    }
}
?>
