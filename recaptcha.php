<?php
// Get Token from Javascript Call
$captcha = $_GET['token'];

// Verify Captcha
$captchaSecretKey = '6Le1WMUUAAAAAEPIAyNW6dFiISUWg3i3AEob2YVv';
$captchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captchaSecretKey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
echo json_decode($captchaResponse, true);
?>