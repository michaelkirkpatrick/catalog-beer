<?php
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';
session_destroy();
header('location: /');
exit();
?>
