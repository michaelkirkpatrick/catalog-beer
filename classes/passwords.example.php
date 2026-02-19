<?php
// Secrets — this file lives on each server and is never deployed or committed.
// Copy this file to passwords.php and fill in your credentials.

// Database Credentials
define('DB_PASSWORD_STAGING', 'your_staging_password');
define('DB_PASSWORD_PRODUCTION', 'your_production_password');

// API Keys
define('API_KEY_STAGING', 'your_staging_api_key');
define('API_KEY_PRODUCTION', 'your_production_api_key');

// Postmark Email
define('POSTMARK_SERVER_TOKEN', 'your_postmark_token');

// Google reCAPTCHA v3
define('RECAPTCHA_SECRET_KEY', 'your_recaptcha_secret_key');

// Google Maps JavaScript API
define('GOOGLE_MAPS_KEY', 'your_google_maps_key');
?>
