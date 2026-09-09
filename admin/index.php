<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Admin Gate
if(!$userInfo->admin){
    header('location: /');
    exit;
}

// HTML Head
$htmlHead = new htmlHead('Admin');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Admin</h1>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Activity</h5>
                                <p class="card-text">See what users are adding and updating, top contributors, and GET traffic by endpoint.</p>
                                <a href="/admin/activity.php" class="btn btn-primary">View Activity</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">API Usage</h5>
                                <p class="card-text">Monthly API call counts by user over the last 13 months.</p>
                                <a href="/admin/usage.php" class="btn btn-primary">View Usage</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Metrics</h5>
                                <p class="card-text">Catalog health dashboard: size and growth since 2017, freshness, verification, completeness, and API demand.</p>
                                <a href="/admin/metrics.php" class="btn btn-primary">View Metrics</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Reviews</h5>
                                <p class="card-text">The brewer review loop's check-in: answer what is waiting on a decision, and read every change a review made.</p>
                                <a href="/admin/reviews" class="btn btn-primary">View Reviews</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Leads</h5>
                                <p class="card-text">Breweries the review loop met that the catalog does not hold, queued to be researched before anything is created.</p>
                                <a href="/admin/leads" class="btn btn-primary">View Leads</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Error Log</h5>
                                <p class="card-text">Unresolved errors, daily trends, top error numbers, and recent errors.</p>
                                <a href="/admin/error-log.php" class="btn btn-primary">View Errors</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
