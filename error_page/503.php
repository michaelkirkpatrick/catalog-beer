<?php
// 503 Service Unavailable — the backend API is unreachable / returning errors.
//
// Served two ways, so it self-bootstraps idempotently:
//  - via serve503() right after a failed backend call (initialize.php already ran;
//    the include_once below is then a no-op)
//  - potentially as an Apache ErrorDocument (initialize.php has NOT run yet)
//
// It deliberately does NOT render the navbar: Navigation::navbar() makes blocking
// /count API calls that would hang during the very outage this page is reporting.
http_response_code(503);
header('Retry-After: 120');

$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

$htmlHead = new htmlHead('Temporarily Unavailable');
echo $htmlHead->html;
?>
<body>
    <div class="container">
        <div class="p-5 mb-4 bg-light rounded-3">
            <h1 class="display-4">Temporarily Unavailable</h1>
            <p class="lead">Sorry &#8212; we&#8217;re having trouble connecting right now. This is usually temporary. Please try again in a few minutes.</p>
            <p><a class="btn btn-primary btn-lg" href="/" role="button">Back to the homepage</a></p>
        </div>
    </div>
</body>
</html>
