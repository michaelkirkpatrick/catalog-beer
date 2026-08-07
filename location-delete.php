<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Location ID
$locationID = $_GET['locationID'] ?? '';

// Fetch Existing Location Data
$api = new API();
$locationResp = $api->request('GET', '/location/' . $locationID, '');
$locationData = json_decode($locationResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(isset($locationData->error) || !isset($locationData->id)){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Permissions — deleting is admin/staff only; everyone else goes back to the
// location page rather than being shown a confirm the API would refuse.
$perms = brewerPermissions($api, $locationData->brewer->id);
if(!permissionsCanManage($perms)){
    header('location: /location/' . urlencode($locationID));
    exit();
}

// Brewer Info — raw. h() at each echo below; htmlHead and Navigation escape
// their own arguments.
$brewerName = $locationData->brewer->name;
$brewerID = $locationData->brewer->id;

// Process Deletion
if(isset($_POST['submit'])){
    if(!csrf_verify()){
        $alert = new Alert();
        $alert->msg = 'Invalid form submission. Please try again.';
        $alert->type = 'error';
    }else{
        $deleteResponse = $api->request('DELETE', '/location/' . $locationID, '');
        if($api->httpcode == 204){
            // Success
            $_SESSION['delete_location_success'] = true;
            header('location: /brewer/' . $brewerID);
            exit();
        }else{
            // Error
            $deleteData = json_decode($deleteResponse);
            $alert = new Alert();
            if(isset($deleteData->error_msg)){
                $alert->msg = $deleteData->error_msg;
            }else{
                $alert->msg = 'An error occurred while deleting this location. Please try again.';

                // Log Error
                $errorLog = new LogError();
                $errorLog->errorNumber = 'C26';   // C16 was already taken by SendEmail.class.php
                $errorLog->errorMsg = 'Unexpected response when deleting location';
                $errorLog->badData = "locationID: $locationID\nhttpcode: " . $api->httpcode . "\nresponse: $deleteResponse";
                $errorLog->filename = 'location-delete.php';
                $errorLog->write();
            }
            $alert->type = 'error';
        }
    }
}

// HTML Head
// Short form: the brewer is named alongside this on every line it appears in.
$locationName = locationShortName($locationData);
$htmlHead = new htmlHead('Delete ' . $locationName);
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Delete ' . $locationName);
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Delete this location?</h1>
        </div>
        <?php
        // Display Alerts
        if(isset($alert)){
            echo $alert->display();
        }
        ?>
        <p class="cbf-confirm">You&#8217;re about to delete <strong><?php echo h($locationName); ?></strong> from <?php echo h($brewerName); ?>. This can&#8217;t be undone.</p>
        <form method="post">
            <?php echo csrf_field(); ?>
            <div class="cbf-actions cbf-actions--bare">
                <button type="submit" class="cbf-btn cbf-btn--danger" name="submit">Delete Location</button>
                <a class="cbf-btn cbf-btn--ghost" href="/brewer/<?php echo h($brewerID); ?>">Cancel</a>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
