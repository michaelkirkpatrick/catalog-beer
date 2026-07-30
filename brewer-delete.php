<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Brewer ID
$brewerID = $_GET['brewerID'] ?? '';

// Fetch Existing Brewer Data (the beer list carries the brewer object, and
// its rows feed the cascade count in the confirmation copy)
$api = new API();
$brewerResp = $api->request('GET', '/brewer/' . $brewerID . '/beer', '');
$brewerData = json_decode($brewerResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(!isset($brewerData->brewer) || isset($brewerData->error)){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Permissions — deleting is admin/staff only; everyone else goes back to the
// brewer page rather than being shown a confirm the API would refuse.
$perms = brewerPermissions($api, $brewerID);
if(!permissionsCanManage($perms)){
    header('location: /brewer/' . urlencode($brewerID));
    exit();
}

// Cascade counts — the MySQL delete takes the brewer's beers and locations
// with it, so the confirmation names what's about to vanish.
$beerCount = isset($brewerData->data) ? count($brewerData->data) : 0;
$locationCount = 0;
$locationResp = $api->request('GET', '/brewer/' . $brewerID . '/locations', '');
$locationData = json_decode($locationResp);
if(isset($locationData->data)){
    $locationCount = count($locationData->data);
}

// Brewer Info
$text1 = new Text(false, true, true);
$text2 = new Text(false, false, true);
$brewerName = $text1->get($brewerData->brewer->name);
$brewerIDString = $text2->get($brewerData->brewer->id);

// Process Deletion
if(isset($_POST['submit'])){
    if(!csrf_verify()){
        $alert = new Alert();
        $alert->msg = 'Invalid form submission. Please try again.';
        $alert->type = 'error';
    }else{
        $deleteResponse = $api->request('DELETE', '/brewer/' . $brewerID, '');
        if($api->httpcode == 204){
            // Success
            $_SESSION['delete_brewer_success'] = true;
            header('location: /brewer');
            exit();
        }else{
            // Error
            $deleteData = json_decode($deleteResponse);
            $alert = new Alert();
            if(isset($deleteData->error_msg)){
                $alert->msg = $deleteData->error_msg;
            }else{
                $alert->msg = 'An error occurred while deleting this brewer. Please try again.';

                // Log Error
                $errorLog = new LogError();
                $errorLog->errorNumber = 'C27';
                $errorLog->errorMsg = 'Unexpected response when deleting brewer';
                $errorLog->badData = "brewerID: $brewerID\nhttpcode: " . $api->httpcode . "\nresponse: $deleteResponse";
                $errorLog->filename = 'brewer-delete.php';
                $errorLog->write();
            }
            $alert->type = 'error';
        }
    }
}

// Cascade sentence — name the blast radius in plain words
$cascadeParts = array();
if($beerCount > 0){
    $cascadeParts[] = number_format($beerCount) . ' ' . ($beerCount === 1 ? 'beer' : 'beers');
}
if($locationCount > 0){
    $cascadeParts[] = number_format($locationCount) . ' ' . ($locationCount === 1 ? 'location' : 'locations');
}
$cascadeSentence = '';
if(!empty($cascadeParts)){
    $cascadeSentence = ' This also permanently deletes its ' . implode(' and ', $cascadeParts) . '.';
}

// HTML Head
$htmlHead = new htmlHead('Delete ' . $brewerName);
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Delete');
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerIDString);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Delete this brewer?</h1>
        </div>
        <?php
        // Display Alerts
        if(isset($alert)){
            echo $alert->display();
        }
        ?>
        <p class="cbf-confirm">You&#8217;re about to delete <strong><?php echo $brewerName; ?></strong> from the catalog.<?php echo $cascadeSentence; ?> This can&#8217;t be undone.</p>
        <form method="post">
            <?php echo csrf_field(); ?>
            <div class="cbf-actions cbf-actions--bare">
                <button type="submit" class="cbf-btn cbf-btn--danger" name="submit">Delete Brewer</button>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer/<?php echo htmlspecialchars($brewerIDString); ?>">Cancel</a>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
