<?php
// Get Token from Javascript Call
$captcha = $_GET['token'];

// Verify Captcha
$captchaSecretKey = '';
$captchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captchaSecretKey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
echo json_decode($captchaResponse, true);
?>