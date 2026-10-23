<?php
/*--
One alert box for the server-rendered admin pages: an error by default, a
success with $isSuccess. Styled by .cbf-alert in catalog-forms.css.
--*/
function alertHtml($msg, $isSuccess = false){
    $html = '<div class="cbf-alert' . ($isSuccess ? ' cbf-alert--ok" role="status"' : '" role="alert"') . '>';
    $html .= '<span class="cbf-alert__i" aria-hidden="true">' . ($isSuccess ? '&#10003;' : '!') . '</span>';
    $html .= '<div>' . h($msg) . '</div></div>';
    return $html;
}
?>
