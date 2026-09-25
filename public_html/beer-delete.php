<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Get Beer ID
$beerID = $_GET['beerID'] ?? '';

// Fetch Existing Beer Data
$api = new API();
$beerResp = $api->request('GET', '/beer/' . $beerID, '');
$beerData = json_decode($beerResp);
if($api->unavailable()){
    // Backend down — temporarily unavailable, not "not found".
    serve503();
}
if(!isset($beerData->id) || isset($beerData->error) || !isset($beerData->brewer->id)){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Permissions — deleting is admin/staff only; everyone else goes back to the
// beer page rather than being shown a confirm the API would refuse.
$perms = brewerPermissions($api, $beerData->brewer->id);
if(!permissionsCanManage($perms)){
    header('location: /beer/' . urlencode($beerID));
    exit();
}

// Beer & Brewer Info — raw. h() at each echo below; htmlHead and Navigation
// escape their own arguments, so the page title, the breadcrumb text and the
// breadcrumb links all take these unescaped.
$beerName = $beerData->name;
$beerIDString = $beerData->id;
$brewerName = $beerData->brewer->name;
$brewerIDString = $beerData->brewer->id;

// Process Deletion
if(isset($_POST['submit'])){
    if(!csrf_verify()){
        $alert = new Alert();
        $alert->msg = 'Invalid form submission. Please try again.';
        $alert->type = 'error';
    }else{
        $deleteResponse = $api->request('DELETE', '/beer/' . $beerID, '');
        if($api->httpcode == 204){
            // Success
            $_SESSION['delete_beer_success'] = true;
            header('location: /brewer/' . $brewerIDString);
            exit();
        }else{
            // Error
            $deleteData = json_decode($deleteResponse);
            $alert = new Alert();
            if(isset($deleteData->error_msg)){
                $alert->msg = $deleteData->error_msg;
            }else{
                $alert->msg = 'An error occurred while deleting this beer. Please try again.';

                // Log Error
                $errorLog = new LogError();
                $errorLog->errorNumber = 'C28';
                $errorLog->errorMsg = 'Unexpected response when deleting beer';
                $errorLog->badData = "beerID: $beerID\nhttpcode: " . $api->httpcode . "\nresponse: $deleteResponse";
                $errorLog->filename = 'beer-delete.php';
                $errorLog->write();
            }
            $alert->type = 'error';
        }
    }
}

// HTML Head
$htmlHead = new htmlHead('Delete ' . $beerName);
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Beer'); ?>
    <div class="cb-page cb-page--form">
        <?php
        // Breadcrumbs
        $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, $beerName, 'Delete');
        $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerIDString, '/beer/' . $beerIDString);
        echo $nav->breadcrumbs();
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1">Delete this beer?</h1>
        </div>
        <?php
        // Display Alerts
        if(isset($alert)){
            echo $alert->display();
        }
        ?>
        <p class="cbf-confirm">You&#8217;re about to delete <strong><?php echo h($beerName); ?></strong> by <?php echo h($brewerName); ?>. This can&#8217;t be undone.</p>
        <form method="post">
            <?php echo csrf_field(); ?>
            <div class="cbf-actions cbf-actions--bare">
                <button type="submit" class="cbf-btn cbf-btn--danger" name="submit">Delete Beer</button>
                <a class="cbf-btn cbf-btn--ghost" href="/beer/<?php echo h($beerIDString); ?>">Cancel</a>
            </div>
        </form>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
