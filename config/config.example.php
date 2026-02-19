<?php
// Non-secret configuration — safe to deploy and commit.
// Secrets live in passwords.php (never deployed or committed).

// Load Secrets
require_once __DIR__ . '/passwords.php';

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_NAME', 'your_db_name');

// Google reCAPTCHA v3 (public site key)
define('RECAPTCHA_SITE_KEY', 'your_recaptcha_site_key');

?>
