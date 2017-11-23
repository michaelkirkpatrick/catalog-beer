<?php
// Start Session
session_start();

// Define Root
define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
define("SERVER_NAME", $_SERVER['SERVER_NAME']);

// Establish Environment
$serverName = explode('.', $_SERVER['SERVER_NAME']);
if($serverName[0] == 'staging'){
	define('ENVIRONMENT', 'staging');
}elseif($serverName[0] == 'catalog'){
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
			if(!$userInfo->emailVerified){
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