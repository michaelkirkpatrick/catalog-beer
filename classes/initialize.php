<?php
// Session Security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

// Start Session
session_start();

// Define Root
define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
define("SERVER_NAME", $_SERVER['SERVER_NAME']);

// Load Configuration
require_once ROOT . '/classes/config.php';

// Establish Environment
$serverName = explode('.', $_SERVER['SERVER_NAME']);
if($serverName[0] === 'staging'){
	define('ENVIRONMENT', 'staging');
}elseif($serverName[0] === 'catalog'){
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

// CSRF Protection
if(empty($_SESSION['csrf_token'])){
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field(){
	return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_verify(){
	return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

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
	
	if(!empty($_SESSION['userID'])){
		$api = new API();
		$jsonResponse = $api->request('GET', '/users/' . $_SESSION['userID'], '');

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