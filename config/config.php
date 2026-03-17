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

?>
