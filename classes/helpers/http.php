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

/**
 * Serve the 404 page in place and stop.
 *
 * In place, not via redirect: the detail pages used to send
 * http_response_code(404) and then header('Location: /error_page/404.php'),
 * and PHP turns any Location header into a 302. Search Console then saw
 * hundreds of deleted-beer URLs 302ing to one shared page and filed them as
 * "Duplicate without user-selected canonical" of /error_page/404.php, rather
 * than as the 404s they are. Including the page keeps the missing URL in the
 * address bar with a real 404 status, which is what both users and crawlers
 * expect.
 */
function serve404(): void {
    // require() inside a function runs the file in this function's scope; the
    // 404 page renders the navbar and footer through initialize.php's $nav.
    global $nav;
    require ROOT . '/error_page/404.php';
    exit();
}
