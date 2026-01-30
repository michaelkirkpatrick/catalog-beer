<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

$privateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/classes/resources/AuthKey_KY3482YPC5.p8');

$my_token = JWT::getToken($privateKey, 'KY3482YPC5', 'WJL53F635R');
echo $my_token;
?>