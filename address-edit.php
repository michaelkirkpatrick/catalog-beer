<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Retired. The address is edited alongside the name and URL on location-edit.php —
a location and its address are one thing to the person filling the form in, and
splitting them across two screens let a location exist with nothing attached to
it.

This file stays because /location/{id}/edit-address is a real URL that people
have bookmarked and that the .htaccess still routes. It forwards rather than
404s. The same goes for address-add.php.
--- */

$locationID = $_GET['locationID'] ?? '';

if($locationID === ''){
    header('location: /brewer', true, 302);
    exit();
}

header('location: /location/' . rawurlencode($locationID) . '/edit', true, 301);
exit();
