<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/* ---
Retired — see address-edit.php for the reasoning. Adding an address is now part
of location-add.php (for a new location) and location-edit.php (for one already
in the database), so there is no separate "add an address" step to land on.

/location/{id}/add-address forwards to the location editor, which upserts: it
writes an address whether or not one is already on file.
--- */

$locationID = $_GET['locationID'] ?? '';

if($locationID === ''){
    header('location: /brewer', true, 302);
    exit();
}

header('location: /location/' . rawurlencode($locationID) . '/edit', true, 301);
exit();
