<?php
// Get Token from Javascript Call
$captcha = $_GET['token'] ?? '';

// Verify Captcha
$captchaSecretKey = RECAPTCHA_SECRET_KEY;
$captchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captchaSecretKey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
echo $captchaResponse;
?>