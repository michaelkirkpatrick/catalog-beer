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
 * Call it in the success branch, just before the redirect. A no-op without a
 * session, which can't happen on the write pages (they all require login).
 */
function trackEvent(string $name): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION['fathom_events'][] = $name;
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
    if (empty($_SESSION['fathom_events'])) {
        return '';
    }
    $events = array_values(array_unique($_SESSION['fathom_events']));
    unset($_SESSION['fathom_events']);
    if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'production') {
        return '';
    }
    $json = json_encode($events, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    return '<script>window.addEventListener("load",function(){if(window.fathom){' . $json . '.forEach(function(e){fathom.trackEvent(e);});}});</script>';
}
