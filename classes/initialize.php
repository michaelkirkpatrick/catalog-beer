<?php
// Session Configuration (30-day lifetime, isolated save path)
ini_set('session.save_path', '/var/lib/php/sessions/catalogbeer');
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params([
    'lifetime' => 2592000,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start a session only if the client already presents a session cookie. Pages
// that create a new session (login, account creation) call ensureSession()
// instead — defined in classes/helpers/session.php, loaded below.
if (isset($_COOKIE[session_name()])) {
    session_start();
}

// Define Root
define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
define("SERVER_NAME", $_SERVER['SERVER_NAME']);

// Load Configuration
require_once ROOT . '/config/config.php';

// Establish Environment
$serverName = explode('.', $_SERVER['SERVER_NAME']);
if($serverName[0] === 'staging'){
    define('ENVIRONMENT', 'staging');
}else{
    define('ENVIRONMENT', 'production');
}

// Set Timezone
date_default_timezone_set('America/Los_Angeles');

// Autoload Classes
spl_autoload_register(function ($class_name) {
    require_once  ROOT . '/classes/' . $class_name . '.class.php';
});

// HTML Purifier
require_once ROOT . '/classes/htmlpurifier/HTMLPurifier.auto.php';

// Function helpers (not autoloaded classes, so required explicitly). Loaded here,
// after ROOT/config and before the auth gate below, which calls serve503().
//   assets.php  — assetUrl/cssTag/jsTag: versioned, cache-busted local asset URLs
//   session.php — ensureSession + csrf_field/csrf_verify
//   http.php    — serve503
//   location.php— labels for an unnamed location, plus the address / maps-link
//                 formatting shared by the location and brewer facts rails
//   forms.php   — suppressAutofill: no-fill attributes for catalog fields
//   address.php — the address fieldset shared by location-add and location-edit
require_once ROOT . '/classes/helpers/assets.php';
require_once ROOT . '/classes/helpers/session.php';
require_once ROOT . '/classes/helpers/http.php';
require_once ROOT . '/classes/helpers/location.php';
require_once ROOT . '/classes/helpers/forms.php';
require_once ROOT . '/classes/helpers/address.php';

// Navigation
$nav = new Navigation();

// Sign In Required?
if($guest == false){
    // Requested URI
    $URI = $_SERVER['REQUEST_URI'];
    $request = '';
    if(!empty($URI)){
        $request = '?request=' . $URI;
    }
    
    if(session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['userID'])){
        $api = new API();
        $jsonResponse = $api->request('GET', '/users/' . $_SESSION['userID'], '');

        if($api->unavailable()){
            // Backend down — don't bounce the user to /login (which would also
            // fail); serve a 503 so they know it's temporary and to retry.
            serve503();
        }

        if($api->httpcode == 200){
            // Valid User
            $userInfo = json_decode($jsonResponse);
            if(!$userInfo->email_verified){
                // Unverified Email
                if($URI == '/verify-email' || $URI == '/account'){
                    // Page Okay
                }else{
                    // Redirect
                    header('location: /verify-email');
                    exit;
                }
            }
        }else{
            // Return to Homepage
            header('location: /login' . $request);
            exit;
        }
    }else{
        // Return to Homepage
        header('location: /login' . $request);
        exit;
    }
}
?>