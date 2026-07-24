<?php
// Non-secret configuration — safe to deploy and commit.
// Secrets live in passwords.php (never deployed or committed).

// Load Secrets
require_once __DIR__ . '/passwords.php';

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
// Shared by staging and production. To split them, note that config.php loads at
// initialize.php:25 but ENVIRONMENT is not defined until line 29 — either move that
// block above the require, or branch on $_SERVER['SERVER_NAME'] here.
define('GOOGLE_MAPS_MAP_ID', 'b57fea0da75866f5998c4200');

?>
