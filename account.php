<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Alert
$alert = new Alert();

// Generate API Key?
if(isset($_POST['api-key'])){
	$apiResp = $api->request('POST', '/users/' . $_SESSION['userID'] . '/api-key', '');
	$apiData = json_decode($apiResp);
	if(isset($apiData->error)){
		$alert->msg = $apiData->error_msg;
	}
}

// HTML Head
$htmlHead = new htmlHead('My Account');
echo $htmlHead->html;

// API Call
$api = new API();
$userResp = $api->request('GET', '/users/' . $_SESSION['userID'], '');
$userData = json_decode($userResp);
?>
<body>
	<?php echo $nav->navbar(''); ?>
	<div class="container">
    <div class="row">
    	<div class="col-md-12 col-sm-12 col-lg-9 col-xl-7">
				<h1>My Account</h1>
				<?php
				// Alert
				echo $alert->display();
				
				// Text Prep
				$text = new Text(false, true, true);
				
				// Name
				$accountInfoTable = '<table class="table"><tr><td><strong>Name</strong></td><td>' . $text->get($userData->name) . '</td></tr>';
				
				// Email
				if($userData->emailVerified){
					// Verified
					$pillAdd = ' <span class="badge badge-pill badge-success">Verified</span>';
					
					// Get API Key
					$apiKeyResp = $api->request('GET', '/users/' . $_SESSION['userID'] . '/api-key', '');
					$apiKeyData = json_decode($apiKeyResp);
					
					// Get API Usage
					$currentUsageResp = $api->request('GET', '/usage/currentMonth/' . $apiKeyData->api_key, '');
					$currentUsageData = json_decode($currentUsageResp);
					$apiKey = '<table class="table"><tr><td><strong>Secret key</strong></td><td><code>' . $apiKeyData->api_key . '</code></td></tr><tr><td><strong>Requests this month</strong></td><td>' . number_format($currentUsageData->count) . '</td></tr></table><p>Learn more about <a href="/api-usage">API usage</a> and about the <a href="/api-docs">Catalog.beer API</a>.</p>';
				}else{
					// Unverified
					$pillAdd = ' <span class="badge badge-pill badge-warning">Unverified</span>';
					
					// Sent Date
					$today = date('l, F jS', time());
					$sent = date('l, F jS', $userData->emailAuthSent);
					if($sent == $today){
						$dateString = 'today at ' . date('g:i A', $userData->emailAuthSent);
					}else{
						$dateString = $sent . ' at ' . date('g:i A', $userData->emailAuthSent);
					}
					$helpText = '<div class="card"><div class="card-header bg-warning">Verification Required</div><div class="card-body"><p class="card-text">Before you will be able to add data to the Catalog.beer database or obtain an API key, you will need to verify your email address. This helps us reduce spam on the site. Check your email; we sent you a message <strong>' . $dateString . '</strong> with the subject line <strong>&#8220;Confirm your Catalog.beer Account&#8221;</strong>.</p></div></div>';
				}
				$accountInfoTable .= '<tr><td><strong>Email</strong></td><td>' . $text->get($userData->email) . $pillAdd . '</td></tr></table>';
				echo $accountInfoTable;
				
				// API Key
				echo '<h2>API</h2>';
				if(isset($helpText)){echo $helpText;}
				elseif(isset($apiKey)){echo $apiKey;}
				?>
			</div>
			<div class="col-lg-3 col-xl-5"></div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>