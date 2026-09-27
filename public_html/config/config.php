<?php
// Non-secret configuration — safe to deploy and commit.
// Secrets live in the vhost-root common/passwords.php (never deployed or committed).

// Load Secrets
//
// The secrets file lives OUTSIDE the web root, at the vhost root beside
// public_html (/var/www/html/<vhost>/common/passwords.php), so Apache cannot
// serve it under any misconfiguration. ROOT is public_html in every context
// that loads this file (DOCUMENT_ROOT on the web, derived from __DIR__ in
// cron/), so dirname(ROOT) is the vhost root. Locally the same expression
// resolves to the repo's own common/passwords.php.
require_once dirname(ROOT) . '/common/passwords.php';

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'catalogadmin');
define('DB_NAME', 'catalogbeer');

// Google reCAPTCHA v3 (public site key)
define('RECAPTCHA_SITE_KEY', '6LfLYo0sAAAAAMXwORFEsq5yuDW-a62k5FBc-yp2');

// Google Maps Map ID (public, not a secret — it ships in client-side JS by design.
// Pairs with GOOGLE_MAPS_KEY in passwords.php).
// Required by AdvancedMarkerElement — advanced markers will not load without it.
// The renderer (vector) and the map style are configured against this ID in the Cloud
// Console, not in code: restyling the maps needs no deploy, and leaves no trace in this
// repo. Tilt/rotate is enabled on the ID but overridden off in each map's options.
// Shared by staging and production. To split them, branch on ENVIRONMENT, which
// initialize.php defines before it requires this file.
define('GOOGLE_MAPS_MAP_ID', 'b57fea0da75866f5998c4200');

?>
