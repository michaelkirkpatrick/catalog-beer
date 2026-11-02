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
$htmlHead = new htmlHead('Activity');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php
                // Breadcrumbs
                $nav->breadcrumbText = array('Admin', 'Activity');
                $nav->breadcrumbLink = array('/admin/');
                echo $nav->breadcrumbs();
                ?>
                <h1>Activity</h1>
                <?php
                // Fetch activity data
                $api = new API();
                $response = $api->request('GET', '/activity', '');
                $report = json_decode($response);

                if(isset($report->error) && $report->error){
                    echo '<div class="alert alert-danger">' . h($report->error_msg) . '</div>';
                }elseif(isset($report->write_activity)){

                    // --- Write Activity Summary ---
                    // Since API 6.5.0 the write half comes from audit_log: every
                    // revision of a brewer, beer or location, master keys included,
                    // with the Newman suite's entities filtered out by name.
                    $excluded = intval($report->test_entities_excluded ?? 0);
                    echo '<h2>Write Activity <small class="text-muted">(Last 3 Months)</small></h2>';
                    if($excluded > 0){
                        echo '<p class="text-muted"><small>' . number_format($excluded) . ' test ' . ($excluded === 1 ? 'entity' : 'entities') . ' from the API test suite excluded.</small></p>';
                    }
                    if(!empty($report->write_activity->summary)){
                        $table = new Table();
                        echo $table->startTable(array('Resource', 'Created', 'Updated', 'Deleted', 'Total'));
                        $grandCreated = 0;
                        $grandUpdated = 0;
                        $grandDeleted = 0;
                        foreach($report->write_activity->summary as $row){
                            $total = $row->created + $row->updated + $row->deleted;
                            $grandCreated += $row->created;
                            $grandUpdated += $row->updated;
                            $grandDeleted += $row->deleted;
                            echo '<tr>';
                            echo '<td>' . h(ucfirst($row->resource)) . '</td>';
                            echo '<td>' . number_format($row->created) . '</td>';
                            echo '<td>' . number_format($row->updated) . '</td>';
                            echo '<td>' . number_format($row->deleted) . '</td>';
                            echo '<td><strong>' . number_format($total) . '</strong></td>';
                            echo '</tr>' . "\n";
                        }
                        $grandTotal = $grandCreated + $grandUpdated + $grandDeleted;
                        echo '<tr class="table-active">';
                        echo '<td><strong>Total</strong></td>';
                        echo '<td><strong>' . number_format($grandCreated) . '</strong></td>';
                        echo '<td><strong>' . number_format($grandUpdated) . '</strong></td>';
                        echo '<td><strong>' . number_format($grandDeleted) . '</strong></td>';
                        echo '<td><strong>' . number_format($grandTotal) . '</strong></td>';
                        echo '</tr>' . "\n";
                        echo $table->closeTable();
                    }else{
                        echo '<p>No write activity.</p>';
                    }

                    // --- Top Contributors ---
                    echo '<h2>Top Contributors <small class="text-muted">(Last 3 Months)</small></h2>';
                    if(!empty($report->write_activity->top_contributors)){
                        $table = new Table();
                        echo $table->startTable(array('Name', 'Email', 'Created', 'Updated', 'Deleted', 'Total'));
                        foreach($report->write_activity->top_contributors as $contributor){
                            echo '<tr>';
                            echo '<td>' . h($contributor->name) . '</td>';
                            if(!empty($contributor->email)){
                                echo '<td><a href="mailto:' . h($contributor->email) . '">' . h($contributor->email) . '</a></td>';
                            }else{
                                echo '<td></td>';
                            }
                            echo '<td>' . number_format($contributor->created) . '</td>';
                            echo '<td>' . number_format($contributor->updated) . '</td>';
                            echo '<td>' . number_format($contributor->deleted ?? 0) . '</td>';
                            echo '<td><strong>' . number_format($contributor->total) . '</strong></td>';
                            echo '</tr>' . "\n";
                        }
                        echo $table->closeTable();
                    }else{
                        echo '<p>No contributors.</p>';
                    }

                    // --- Recent Activity ---
                    echo '<h2>Recent Activity</h2>';
                    if(!empty($report->write_activity->recent)){
                        $table = new Table();
                        echo $table->startTable(array('When', 'Who', 'Action', 'What', 'History'));
                        foreach($report->write_activity->recent as $entry){
                            $entityType = $entry->entity_type ?? '';
                            $entityID = $entry->entity_id ?? '';
                            switch($entry->action ?? ''){
                                case 'create':
                                    $action = 'Created';
                                    break;
                                case 'update':
                                    $action = 'Updated';
                                    break;
                                case 'delete':
                                    $action = 'Deleted';
                                    break;
                                case 'revert':
                                    $action = 'Restored';
                                    break;
                                default:
                                    $action = ucfirst($entry->action ?? '');
                            }
                            $actionLabel = $action . ' ' . ucfirst($entityType);
                            if(!empty($entry->note)){
                                $actionLabel .= ' <small class="text-muted">' . h($entry->note) . '</small>';
                            }else{
                                $actionLabel = h($actionLabel);
                            }

                            // "What": the record, linked while it exists; a deleted
                            // record still names itself from its last snapshot
                            $resourceName = $entry->resource_name ?? null;
                            $brewerName = $entry->brewer_name ?? null;
                            $brewerId = $entry->brewer_id ?? null;
                            $deleted = (($entry->action ?? '') === 'delete');
                            if($resourceName !== null && $entityID !== '' && !$deleted){
                                $whatHtml = '<a href="/' . h($entityType) . '/' . h($entityID) . '">' . h($resourceName) . '</a>';
                            }elseif($resourceName !== null){
                                $whatHtml = h($resourceName);
                            }else{
                                $whatHtml = '<code>' . h($entityType . '/' . $entityID) . '</code>';
                            }
                            if($brewerName !== null && $brewerId !== null && $entityType !== 'brewer'){
                                $whatHtml .= ', <a href="/brewer/' . h($brewerId) . '">' . h($brewerName) . '</a>';
                            }elseif($brewerName !== null && $entityType !== 'brewer'){
                                $whatHtml .= ', ' . h($brewerName);
                            }

                            $historyHtml = '';
                            if($entityID !== '' && in_array($entityType, array('brewer', 'beer', 'location'), true)){
                                $historyHtml = '<a href="/' . h($entityType) . '/' . h($entityID) . '/history#rev-' . intval($entry->audit_id ?? 0) . '">#' . intval($entry->audit_id ?? 0) . '</a>';
                            }

                            echo '<tr>';
                            echo '<td>' . date('M j, g:ia', $entry->timestamp) . '</td>';
                            echo '<td>' . h($entry->user_name) . '</td>';
                            echo '<td>' . $actionLabel . '</td>';
                            echo '<td>' . $whatHtml . '</td>';
                            echo '<td>' . $historyHtml . '</td>';
                            echo '</tr>' . "\n";
                        }
                        echo $table->closeTable();
                    }else{
                        echo '<p>No recent activity.</p>';
                    }

                    // --- GET Traffic ---
                    echo '<h2>GET Traffic <small class="text-muted">(Last 3 Months)</small></h2>';
                    if(!empty($report->read_traffic->by_endpoint)){
                        $table = new Table();
                        // Durations exist on rows logged since API 6.7.0 (null before)
                        echo $table->startTable(array('Endpoint', 'Count', 'Avg ms', 'Max ms'));
                        foreach($report->read_traffic->by_endpoint as $ep){
                            echo '<tr>';
                            echo '<td><code>' . h($ep->endpoint) . '</code></td>';
                            echo '<td>' . number_format($ep->count) . '</td>';
                            echo '<td>' . (isset($ep->avg_ms) ? number_format($ep->avg_ms) : '<span class="text-muted">&#8212;</span>') . '</td>';
                            echo '<td>' . (isset($ep->max_ms) ? number_format($ep->max_ms) : '<span class="text-muted">&#8212;</span>') . '</td>';
                            echo '</tr>' . "\n";
                        }
                        echo '<tr class="table-active">';
                        echo '<td><strong>Total</strong></td>';
                        echo '<td><strong>' . number_format($report->read_traffic->total) . '</strong></td>';
                        echo '<td></td><td></td>';
                        echo '</tr>' . "\n";
                        echo $table->closeTable();
                    }else{
                        echo '<p>No GET traffic data.</p>';
                    }

                }else{
                    echo '<p>No activity data available.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
