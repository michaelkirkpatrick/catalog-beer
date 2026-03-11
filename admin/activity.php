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
				<h1>Activity</h1>
				<?php
				// Fetch activity data
				$api = new API();
				$response = $api->request('GET', '/activity', '');
				$report = json_decode($response);

				if(isset($report->error) && $report->error){
					echo '<div class="alert alert-danger">' . htmlspecialchars($report->error_msg) . '</div>';
				}elseif(isset($report->write_activity)){

					// --- Write Activity Summary ---
					echo '<h2>Write Activity <small class="text-muted">(Last 3 Months)</small></h2>';
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
							echo '<td>' . htmlspecialchars(ucfirst($row->resource)) . '</td>';
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
						echo $table->startTable(array('Name', 'Email', 'Created', 'Updated', 'Total'));
						foreach($report->write_activity->top_contributors as $contributor){
							echo '<tr>';
							echo '<td>' . htmlspecialchars($contributor->name) . '</td>';
							if(!empty($contributor->email)){
								echo '<td><a href="mailto:' . htmlspecialchars($contributor->email) . '">' . htmlspecialchars($contributor->email) . '</a></td>';
							}else{
								echo '<td></td>';
							}
							echo '<td>' . number_format($contributor->created) . '</td>';
							echo '<td>' . number_format($contributor->updated) . '</td>';
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
						echo $table->startTable(array('When', 'Who', 'Action', 'URI', 'Status'));
						foreach($report->write_activity->recent as $entry){
							// Determine action label
							switch($entry->method){
								case 'POST':
									$action = 'Created';
									break;
								case 'PUT':
								case 'PATCH':
									$action = 'Updated';
									break;
								case 'DELETE':
									$action = 'Deleted';
									break;
								default:
									$action = $entry->method;
							}

							// Extract resource type from URI
							$uriPath = strtok($entry->uri, '?');
							$parts = explode('/', trim($uriPath, '/'));
							$resource = !empty($parts[0]) ? ucfirst($parts[0]) : '';
							$actionLabel = $action . ' ' . $resource;

							// Status badge
							$statusCode = $entry->response_code;
							if($statusCode >= 200 && $statusCode < 300){
								$badge = '<span class="badge bg-success">' . $statusCode . '</span>';
							}elseif($statusCode >= 400){
								$badge = '<span class="badge bg-danger">' . $statusCode . '</span>';
							}else{
								$badge = '<span class="badge bg-secondary">' . $statusCode . '</span>';
							}

							echo '<tr>';
							echo '<td>' . date('M j, g:ia', $entry->timestamp) . '</td>';
							echo '<td>' . htmlspecialchars($entry->user_name) . '</td>';
							echo '<td>' . htmlspecialchars($actionLabel) . '</td>';
							echo '<td><code>' . htmlspecialchars($entry->uri) . '</code></td>';
							echo '<td>' . $badge . '</td>';
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
						echo $table->startTable(array('Endpoint', 'Count'));
						foreach($report->read_traffic->by_endpoint as $ep){
							echo '<tr>';
							echo '<td><code>' . htmlspecialchars($ep->endpoint) . '</code></td>';
							echo '<td>' . number_format($ep->count) . '</td>';
							echo '</tr>' . "\n";
						}
						echo '<tr class="table-active">';
						echo '<td><strong>Total</strong></td>';
						echo '<td><strong>' . number_format($report->read_traffic->total) . '</strong></td>';
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
