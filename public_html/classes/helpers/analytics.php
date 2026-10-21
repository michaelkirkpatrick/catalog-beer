<?php
/**
 * Fathom custom events. Required from initialize.php.
 *
 * Every write on this site is a POST that redirects on success, so the page
 * that did the work never renders and has nowhere to put a script tag. Events
 * are queued in the session instead and fired by the next page's <head> (see
 * htmlHead), which is the page the user lands on. The API is used far more
 * than the website; these events are how we find out whether anyone writes
 * through the website at all.
 *
 * Event names are fixed strings chosen here — never put a beer name, ID or
 * anything a user typed into one. Fathom shows them verbatim in the dashboard
 * and the privacy page promises it collects nothing personal.
 */

/**
 * Queue a Fathom event for the next rendered page.
 *
 * Call it in the success branch, just before the redirect. Without a session
 * (a logged-out visitor on /contact) the event is held for this request only,
 * which is enough for a page that renders its own confirmation; starting a
 * session just for analytics would hand an anonymous visitor a cookie.
 */
function trackEvent(string $name): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['fathom_events'][] = $name;
    } else {
        $GLOBALS['fathomEventsThisRequest'][] = $name;
    }
}

/**
 * Drain the queue into an inline script, or '' when it is empty.
 *
 * Drains in every environment so staging doesn't accumulate a queue, but only
 * emits in production, where the Fathom script is loaded. The script tag is
 * deferred, so the calls wait for window load; the window.fathom check covers
 * a blocker that stopped the script loading at all.
 */
function fathomEventsScript(): string {
    $events = array_merge($_SESSION['fathom_events'] ?? array(), $GLOBALS['fathomEventsThisRequest'] ?? array());
    if (empty($events)) {
        return '';
    }
    $events = array_values(array_unique($events));
    unset($_SESSION['fathom_events'], $GLOBALS['fathomEventsThisRequest']);
    if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'production') {
        return '';
    }
    $json = json_encode($events, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    return '<script>window.addEventListener("load",function(){if(window.fathom){' . $json . '.forEach(function(e){fathom.trackEvent(e);});}});</script>';
}
