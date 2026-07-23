<?php
/**
 * HTTP response helpers. Required from initialize.php.
 */

/**
 * Serve a 503 "temporarily unavailable" page and stop.
 *
 * Call this when a backend API request fails because the service is unreachable
 * or returning 5xx (see API::unavailable()), so users get a clear "try again"
 * page instead of a 404, a broken page, or a misleading login redirect. The 503
 * page is navbar-free so it can't re-trigger the outage through Navigation's
 * blocking count calls.
 */
function serve503(): void {
    require ROOT . '/error_page/503.php';
    exit();
}
