<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Admin Gate
if(!$userInfo->admin){
	header('location: /');
	exit;
}

// Handle Resolve All
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_all'])){
	$api = new API();
	$response = $api->request('PATCH', '/error-log', ['resolve_all' => true]);
	$result = json_decode($response);
	if(isset($result->error) && $result->error){
		header('location: /admin/error-log.php?error=' . urlencode($result->error_msg));
	}else{
		header('location: /admin/error-log.php?resolved=' . $result->resolved_count);
	}
	exit;
}

// HTML Head
$htmlHead = new htmlHead('Error Log');
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar(''); ?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<h1>Error Log</h1>
				<?php
				// Flash messages
				if(isset($_GET['resolved'])){
					echo '<div class="alert alert-success">Resolved ' . number_format(intval($_GET['resolved'])) . ' errors.</div>';
				}
				if(isset($_GET['error'])){
					echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
				}

				// Fetch error report data
				$api = new API();
				$response = $api->request('GET', '/error-log', '');
				$report = json_decode($response);

				if(isset($report->error) && $report->error){
					echo '<div class="alert alert-danger">' . htmlspecialchars($report->error_msg) . '</div>';
				}elseif(isset($report->summary)){
					// Summary Cards
					echo '<div class="row mb-4">';
					echo '<div class="col-md-4"><div class="card"><div class="card-body text-center"><h5 class="card-title">Total Unresolved</h5><p class="card-text display-6">' . number_format($report->summary->total_unresolved) . '</p></div></div></div>';
					echo '<div class="col-md-4"><div class="card"><div class="card-body text-center"><h5 class="card-title">Last 24 Hours</h5><p class="card-text display-6">' . number_format($report->summary->last_24h) . '</p></div></div></div>';
					echo '<div class="col-md-4"><div class="card"><div class="card-body text-center"><h5 class="card-title">Last 7 Days</h5><p class="card-text display-6">' . number_format($report->summary->last_7d) . '</p></div></div></div>';
					echo '</div>';

					// Resolve All button
					if($report->summary->total_unresolved > 0){
						echo '<form method="POST" class="mb-4">';
						echo '<button type="submit" name="resolve_all" value="1" class="btn btn-primary" onclick="return confirm(\'Resolve all ' . number_format($report->summary->total_unresolved) . ' unresolved errors?\')">Resolve All Errors</button>';
						echo '</form>';
					}

					// Top Errors table
					if(!empty($report->by_error_number)){
						echo '<h2>Top Errors <small class="text-muted">(Last 7 Days)</small></h2>';
						$table = new Table();
						echo $table->startTable(array('Error #', 'Message', 'Count'));
						foreach($report->by_error_number as $err){
							echo '<tr>';
							echo '<td>' . htmlspecialchars($err->error_number) . '</td>';
							echo '<td>' . htmlspecialchars($err->error_message) . '</td>';
							echo '<td>' . number_format($err->count) . '</td>';
							echo '</tr>' . "\n";
						}
						echo $table->closeTable();
					}

					// Daily Trend table
					if(!empty($report->by_day)){
						echo '<h2>Daily Trend <small class="text-muted">(Last 7 Days)</small></h2>';
						$table = new Table();
						echo $table->startTable(array('Date', 'Count'));
						foreach($report->by_day as $day){
							echo '<tr>';
							echo '<td>' . htmlspecialchars($day->date) . '</td>';
							echo '<td>' . number_format($day->count) . '</td>';
							echo '</tr>' . "\n";
						}
						echo $table->closeTable();
					}

					// Top IPs table
					if(!empty($report->top_ips)){
						echo '<h2>Top IPs <small class="text-muted">(Last 7 Days)</small></h2>';
						$table = new Table();
						echo $table->startTable(array('IP Address', 'Count'));
						foreach($report->top_ips as $ip){
							echo '<tr>';
							echo '<td>' . htmlspecialchars($ip->ip_address) . '</td>';
							echo '<td>' . number_format($ip->count) . '</td>';
							echo '</tr>' . "\n";
						}
						echo $table->closeTable();
					}

					// Recent Errors table
					if(!empty($report->recent_errors)){
						echo '<h2>Recent Errors</h2>';
						$table = new Table();
						echo $table->startTable(array('Timestamp', 'Error #', 'Message', 'URI', 'IP', 'Filename'));
						foreach($report->recent_errors as $err){
							echo '<tr>';
							echo '<td>' . date('Y-m-d H:i:s', $err->timestamp) . '</td>';
							echo '<td>' . htmlspecialchars($err->error_number) . '</td>';
							echo '<td>' . htmlspecialchars($err->error_message) . '</td>';
							echo '<td>' . htmlspecialchars($err->uri) . '</td>';
							echo '<td>' . htmlspecialchars($err->ip_address) . '</td>';
							echo '<td>' . htmlspecialchars($err->filename) . '</td>';
							echo '</tr>' . "\n";
						}
						echo $table->closeTable();
					}
				}else{
					echo '<p>No error data available.</p>';
				}
				?>
			</div>
		</div>
	</div>
	<?php echo $nav->footer(); ?>
</body>
</html>
