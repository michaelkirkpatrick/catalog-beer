<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Default Values
$name = '';
$email = '';
$password = '';
$termsAgreement = false;
$error = false;
$errorMsg = '';
$validState = array('name'=>'', 'email'=>'', 'password'=>'', 'terms_agreement'=>'');
$validMsg = array('name'=>'', 'email'=>'', 'password'=>'');

// Classes
$alert = new Alert();

if(isset($_SESSION['userID'])){
	// Destroy Session
	session_destroy();
	session_start();
}

if(isset($_POST['signupFormHidden'])){
	// Get Posted Variables
	$name = $_POST['name'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	if(isset($_POST['terms_agreement'])){
		$termsAgreement = $_POST['terms_agreement'];
	}else{
		$termsAgreement = false;
	}
	$captcha = $_POST['g-recaptcha-response'];
	
	// Verify Captcha
	$captchaSecretKey = '';
	$captchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captchaSecretKey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
	$captchaJSON = json_decode($captchaResponse, true);
	if($captchaJSON['success'] == false){
		// Didn't Pass Captcha
		$error = true;

		// Process Error Codes
		switch($captchaJSON['error-codes'][0]){
			case 'missing-input-response':
				$errorMsg = "Please complete the captcha below so we know you're not a robot.";
				$errorLogMsg = $errorMsg;
				break;
			case 'invalid-input-response':
				$errorMsg = 'Sorry, your captcha response is invalide or malformed. Please try again.';
				$errorLogMsg = $errorMsg;
				break;
			case 'missing-input-secret':
				$errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
				$errorLogMsg = 'The secret parameter for the captcha is missing.';
				break;
			case 'invalid-input-secret':
				$errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
				$errorLogMsg = 'The secret parameter for the captcha is invalid or malformed.';
				break;
			case 'timeout-or-duplicate':
				$errorMsg = 'Sorry, your request has timed out or is a duplicate. Please try your request again.';
				$errorLogMsg = 'timeout-or-duplicate';
				break;
			default:
				$errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
				$errorLogMsg = var_export($captchaJSON, true);
		}
		
		// Update Alert
		$alert->msg = $errorMsg;

		// Log Error
		$errorLog = new LogError();
		$errorLog->errorNumber = 'C1';
		$errorLog->errorMsg = 'CAPTCHA Error';
		$errorLog->badData = $errorLogMsg;
		$errorLog->filename = 'create-account';
		$errorLog->write();
	}else{
		// Send to API
		$data = array('name'=>$name, 'email'=>$email, 'password'=>$password, 'terms_agreement'=>$termsAgreement);
		$api = new API();
		$response = $api->request('POST', '/users', $data);
		if($api->httpcode == 200){
			// Successfully Created Account
			$array = json_decode($response);
			$_SESSION['userID'] = $array->id;
			$alert->type = 'success';
			$alert->msg = 'Success! We\'ve created your account. Please verify your account by clicking on the link in the email we just sent you. Once you do that, you\'ll be all set!';
			
			// Clear Variables
			$name = '';
			$email = '';
			$password = '';
			$termsAgreement = false;
			$error = false;
			$errorMsg = '';
			$validState = array('name'=>'', 'email'=>'', 'password'=>'', 'terms_agreement'=>'');
			$validMsg = array('name'=>'', 'email'=>'', 'password'=>'');
		}else{
			// Error
			$array = json_decode($response, true);
			if(!empty($array['error_msg'])){
				$alert->msg = $array['error_msg'];
			}
			$validState = $array['valid_state'];
			$validMsg = $array['valid_msg'];
		}
	}
}

// HTML Head
$htmlHead = new htmlHead('Create an Account');
echo $htmlHead->html;
?>
<body>
	<div class="container">
    <div class="row">
    	<div class="col"></div>
    	<div class="col-6">
				<div class="text-center"><a href="/"><img src="/images/logo-black.svg" alt="Catalog.beer" style="margin-top:2em; margin-bottom:2em; width:100px;"></a></div>
        <?php
				// Breadcrumbs
				$nav->breadcrumbText = array('Home', 'Create an account');
				$nav->breadcrumbLink = array('/');
				echo $nav->breadcrumbs();
				
				// Display Alerts
				echo $alert->display();
				?>
        <form method="POST" id="signup-form">
        	<input type="hidden" name="signupFormHidden" value="set" />
					<?php
					// Name
					$inputName = new InputField();
					$inputName->name = 'name';
					$inputName->description = 'Name';
					$inputName->required = true;
					$inputName->placeholder = 'e.g., Hannah Brewer';
					$inputName->value = $name;
					$inputName->validState = $validState['name'];
					$inputName->validMsg = $validMsg['name'];
					echo $inputName->display();

					// Email
					$inputEmail = new InputField();
					$inputEmail->name = 'email';
					$inputEmail->description = 'Email address';
					$inputEmail->type = 'email';
					$inputEmail->required = true;
					$inputEmail->placeholder = 'e.g., hannah@acme.beer';
					$inputEmail->value = $email;
					$inputEmail->validState = $validState['email'];
					$inputEmail->validMsg = $validMsg['email'];
					echo $inputEmail->display();

					// Password
					$inputPassword = new InputField();
					$inputPassword->name = 'password';
					$inputPassword->description = 'Password';
					$inputPassword->type = 'password';
					$inputPassword->required = true;
					$inputPassword->placeholder = 'e.g., &middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;';
					$inputPassword->value = $password;
					$inputPassword->validState = $validState['password'];
					$inputPassword->validMsg = $validMsg['password'];
					echo $inputPassword->display();
					
					// Terms and conditions
					$checkbox = new Checkbox();
					$checkbox->validState = $validState['terms_agreement'];
					echo $checkbox->display('terms_agreement', 'I agree to the [Terms & Conditions](/terms) for using this site.', true, $termsAgreement);
					?>
					
					<button class="btn btn-primary g-recaptcha" data-sitekey="6LfNzi0UAAAAAOyPRnBymKZfapoP_OibiDcA-O0f" data-callback="onSubmit" style="margin-top:1em;">Sign Up</button>
					<p class="text-center"><a href="/login">Sign in</a></p>
        </form>
      </div>
      <div class="col"></div>
    </div> 
  </div>
  <?php echo $nav->footer(); ?> 
</body>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="application/javascript">
	function onSubmit(token) {	
		 document.getElementById("signup-form").submit();
	 }
</script>
</html>