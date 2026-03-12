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
$htmlHead = new htmlHead('API Usage');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php
                // Breadcrumbs
                $nav->breadcrumbText = array('Admin', 'API Usage');
                $nav->breadcrumbLink = array('/admin/');
                echo $nav->breadcrumbs();
                ?>
                <h1>API Usage</h1>
                <?php
                // Fetch usage data
                $api = new API();
                $response = $api->request('GET', '/usage', '');
                $usageData = json_decode($response);

                if(isset($usageData->error) && $usageData->error){
                    echo '<div class="alert alert-danger">' . htmlspecialchars($usageData->error_msg) . '</div>';
                }elseif(isset($usageData->data)){
                    // Pivot data: group by api_key
                    $users = array();
                    foreach($usageData->data as $row){
                        $key = $row->api_key;
                        if(!isset($users[$key])){
                            $users[$key] = array(
                                'name' => $row->name,
                                'email' => $row->email,
                                'api_key' => $row->api_key,
                                'months' => array()
                            );
                        }
                        $users[$key]['months'][$row->year . '-' . $row->month] = $row->count;
                    }

                    // Build month columns from earliest data to current month (max 12)
                    $currentMonth = (int)date('n');
                    $currentYear = (int)date('Y');
                    $earliestYear = $currentYear;
                    $earliestMonth = $currentMonth;
                    foreach($usageData->data as $row){
                        if($row->year < $earliestYear || ($row->year == $earliestYear && $row->month < $earliestMonth)){
                            $earliestYear = (int)$row->year;
                            $earliestMonth = (int)$row->month;
                        }
                    }
                    // Cap at 12 months back
                    $maxMonthsBack = (($currentYear - $earliestYear) * 12) + ($currentMonth - $earliestMonth);
                    if($maxMonthsBack > 11) $maxMonthsBack = 11;
                    $months = array();
                    for($i = 0; $i <= $maxMonthsBack; $i++){
                        $m = $currentMonth - $i;
                        $y = $currentYear;
                        if($m <= 0){
                            $m += 12;
                            $y--;
                        }
                        $months[] = array('month' => $m, 'year' => $y, 'label' => date('M Y', mktime(0, 0, 0, $m, 1, $y)));
                    }

                    // Sort by current month usage descending
                    $currentKey = $currentYear . '-' . $currentMonth;
                    usort($users, function($a, $b) use ($currentKey){
                        $aCount = $a['months'][$currentKey] ?? 0;
                        $bCount = $b['months'][$currentKey] ?? 0;
                        return $bCount - $aCount;
                    });

                    // Build table headings
                    $headings = array('Name', 'API Key');
                    foreach($months as $m){
                        $headings[] = $m['label'];
                    }
                    $headings[] = 'Total';

                    $table = new Table();
                    echo $table->startTable($headings);

                    // Build rows
                    foreach($users as $user){
                        echo '<tr>';
                        if(!empty($user['email'])){
                            echo '<td><a href="mailto:' . htmlspecialchars($user['email']) . '">' . htmlspecialchars($user['name']) . '</a></td>';
                        }else{
                            echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                        }
                        echo '<td><code>' . htmlspecialchars($user['api_key']) . '</code></td>';
                        $total = 0;
                        foreach($months as $m){
                            $key = $m['year'] . '-' . $m['month'];
                            $count = $user['months'][$key] ?? 0;
                            $total += $count;
                            echo '<td>' . number_format($count) . '</td>';
                        }
                        echo '<td><strong>' . number_format($total) . '</strong></td>';
                        echo '</tr>' . "\n";
                    }

                    echo $table->closeTable();
                }else{
                    echo '<p>No usage data available.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
