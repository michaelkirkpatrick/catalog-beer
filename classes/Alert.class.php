<?php
/*
Warm alert family (.cbf-alert, catalog-forms.css): rust error / amber warning
and info / green success. One icon dot, message body alongside.

$alert->type = 'success/info/warning/error';

$alert = new Alert();
$alert->msg = '';
echo $alert->display();
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

            // ----- Message -----
            $text = new Text(true, true, true);

            // ----- HTML Output -----
            $return = '<div class="' . $class . '" role="alert">';
            $return .= '<span class="cbf-alert__i" aria-hidden="true">' . $icon . '</span>';
            $return .= '<div>' . $text->get($this->msg) . '</div>';
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
