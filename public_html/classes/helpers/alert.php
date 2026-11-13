<?php
/*--
The one place an alert box (.cbf-alert, catalog-forms.css) is built. Loaded by
initialize.php; Alert.class.php and every hand-written box route through here.

Types: 'error' (rust, the default), 'warning' (amber), 'info' (neutral ink),
'success' (green). Each gets its own badge glyph, drawn with Lucide marks
(inline SVG, stroke="currentColor", stroke-width 2 — the design system's icon
rules): error/warning use the circle-alert "!", info the info "i", success the
check. The badge circle is the CSS dot; the SVG is only the mark inside it.

Every box carries role="status" (polite). These are rendered with the page, not
injected later, so role="alert" bought nothing but an interruption.

  alertHtml($msg, $type)        — $msg is PLAIN TEXT and is escaped here. Use
                                  this for API / database / query-string text.
                                  ($type also accepts the old boolean: true =
                                  success, false = error.)
  alertBox($type, $html, $cls)  — $html is DEVELOPER-AUTHORED HTML, emitted
                                  RAW. Escape any interpolated value with h()
                                  first. $cls adds page layout classes (ac-note).
--*/

function alertType($type){
    if($type === true){ return 'success'; }
    if($type === false || $type === null || $type === ''){ return 'error'; }
    $type = strtolower((string)$type);
    return in_array($type, array('error', 'warning', 'info', 'success'), true) ? $type : 'error';
}

function alertIcon($type){
    $type = alertType($type);
    $open = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"';
    switch($type){
        case 'success':   // lucide: check
            return $open . ' viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>';
        case 'info':      // lucide: info (inner marks; the badge is the circle)
            return $open . ' viewBox="4 4 16 16"><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>';
        default:          // lucide: circle-alert (inner marks)
            return $open . ' viewBox="4 4 16 16"><path d="M12 8v4"/><path d="M12 16h.01"/></svg>';
    }
}

function alertBox($type, $html, $extraClass = ''){
    $type = alertType($type);
    $mod = array('error' => '', 'warning' => ' cbf-alert--warn', 'info' => ' cbf-alert--info', 'success' => ' cbf-alert--ok');
    $class = 'cbf-alert' . $mod[$type] . ($extraClass !== '' ? ' ' . h($extraClass) : '');
    $out  = '<div class="' . $class . '" role="status">';
    $out .= '<span class="cbf-alert__i" aria-hidden="true">' . alertIcon($type) . '</span>';
    $out .= '<div>' . $html . '</div>';
    $out .= '</div>';
    return $out;
}

function alertHtml($msg, $type = 'error'){
    return alertBox($type, h($msg));
}
?>
