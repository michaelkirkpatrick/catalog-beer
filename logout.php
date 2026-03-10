<?php
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
if(session_status() === PHP_SESSION_ACTIVE){
	session_destroy();
}
header('location: /');
exit();
?>
