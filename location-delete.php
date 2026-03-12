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
if(isset($locationData->error) || !isset($locationData->id)){
    http_response_code(404);
    header('location: /error_page/404.php');
    exit();
}

// Brewer Info
$text1 = new Text(false, true, true);
$text2 = new Text(false, false, true);
$brewerName = $text1->get($locationData->brewer->name);
$brewerID = $text2->get($locationData->brewer->id);

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
                $errorLog->errorNumber = 'C16';
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
$locationName = $text1->get($locationData->name);
$htmlHead = new htmlHead('Delete ' . $locationName);
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Brewers'); ?>
    <div class="container">
    <div class="row">
        <div class="col">
        <?php
                // Breadcrumbs
                $nav->breadcrumbText = array('Home', 'Brewers', $brewerName, 'Delete ' . $locationName);
                $nav->breadcrumbLink = array('/', '/brewer', '/brewer/' . $brewerID);
                echo $nav->breadcrumbs();

                // Display Alerts
                if(isset($alert)){
                    echo $alert->display();
                }
                ?>
        <p class="lead">Are you sure you want to delete the location <strong><?php echo $locationName; ?></strong> from <?php echo $brewerName; ?>? This action cannot be undone.</p>
        <form method="post">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-danger" name="submit">Delete Location</button>
                    <a href="/brewer/<?php echo htmlspecialchars($brewerID); ?>" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>
