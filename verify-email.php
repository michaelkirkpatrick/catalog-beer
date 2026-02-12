<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Verify Email');
echo $htmlHead->html;

// Email Auth Code
$emailAuth = isset($_GET['emailAuth']) ? substr($_GET['emailAuth'], 1, 36) : '';
?>
<body>
	<?php echo $nav->navbar(''); ?>
	<div class="container">
		<div class="p-5 mb-4 bg-light rounded-3">
			<?php
			if(!empty($emailAuth)){
				$api = new API();
				$verifyResp = $api->request('POST', '/users/verify-email/' . $emailAuth , '');
				$apiData = json_decode($verifyResp);
				if(isset($apiData->error)){
					// Verification Error
					$text = new Text(false, true, true);
					$errorMsg = $text->get($apiData->error_msg);
					echo '<h1>Email Verification Error...</h1><p class="lead">Sorry about that, there was an error verifying your email address.</p><hr><p>' . $errorMsg . '</p>';
				}else{
					// Successfully Verified
					echo '<h1>Email Verified!</h1><p class="lead">Thank you for jumping through those hoops to setup an account. It helps us combat spam on our site.</p><hr><p>You&#8217;re ready to get started! You can start by <a href="/brewer/add">adding a brewer</a> or by adding a beer that&#8217;s not currently listed under your favorite brewer. As always, if you have questions, <a href="mailto:michael@catalog.beer">let us know</a>.</p><p><a class="btn btn-primary btn-lg" href="/" role="button">Get Started</a></p>';
				}
			}else{
				if(isset($_SESSION['userID'])){
					// Email Verification Required Message
					$today = date('l, F jS', time());
					$sent = date('l, F jS', $userInfo->email_auth_sent);
					if($sent == $today){
						$dateString = 'today at ' . date('g:i A', $userInfo->email_auth_sent);
					}else{
						$dateString = $sent . ' at ' . date('g:i A', $userInfo->email_auth_sent);
					}
					
					// Show Message
					echo '<h1>Email Verification Required</h1><p class="lead">Before you will be able to add data to the Catalog.beer database or obtain an API key, you will need to verify your email address.</p><p>This helps us reduce spam on the site. Check your email; we sent you a message <strong>' . $dateString . '</strong> with the subject line <strong>&#8220;Confirm your Catalog.beer Account&#8221;</strong>. Click the link in that email to verify your email address.</p>';
				}
			}
			?>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>