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
		if($_POST['terms_agreement'] === "1"){
			$termsAgreement = true;
		}else{
			$termsAgreement = false;
		}
	}else{
		$termsAgreement = false;
	}
	$captcha = $_POST['g-recaptcha-response'];
	
	// Verify Captcha
	$captchaSecretKey = '6Le1WMUUAAAAAEPIAyNW6dFiISUWg3i3AEob2YVv';
	$captchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captchaSecretKey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
	$captchaJSON = json_decode($captchaResponse, true);
	if($captchaJSON['success'] == false){
		// Didn't Pass Captcha
		$error = true;
		$errorMsg = 'Sorry, there was an error processing the Captcha.';
		
		// Update Alert
		$alert->msg = $errorMsg;

		// Log Error
		$errorLog = new LogError();
		$errorLog->errorNumber = 'C22';
		$errorLog->errorMsg = 'CAPTCHA Error';
		$errorLog->badData = $captchaJSON;
		$errorLog->filename = 'create-account.php';
		$errorLog->write();
	}else{
		// Successful Captcha, check score
		if($captchaJSON['score'] >= 0.5){
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
				if(isset($array['valid_state'])){$validState = $array['valid_state'];}
				if(isset($array['valid_state'])){$validState = $array['valid_state'];}
				if(isset($array['valid_msg'])){$validMsg = $array['valid_msg'];}
			}
		}else{
			// Didn't Pass Captcha
			$error = true;
			$errorMsg = 'Sorry, Google\'s reCAPTCHA algorithm thinks you are a bot. As such, we are not going to allow you to create an account using this form. We have logged this incident. Try reaching us on [Twitter](https://twitter.com/CatalogBeer) for support.';

			// Update Alert
			$alert->msg = $errorMsg;

			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C21';
			$errorLog->errorMsg = 'reCAPTCHA: Likley Bot';
			$errorLog->badData = $captchaJSON;
			$errorLog->filename = 'create-account.php';
			$errorLog->write();
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
					<button class="btn btn-primary" data-callback="onSubmit" >Sign Up</button>
					<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="">
					<p class="text-center"><a href="/login">Sign in</a></p>
        </form>
      </div>
      <div class="col"></div>
    </div> 
  </div>
  <?php echo $nav->footer(); ?> 
</body>
<script src='https://www.google.com/recaptcha/api.js?render=6Le1WMUUAAAAANAfdjxqXAo2OpkfmkxH7RSD-sLK'></script>
<script type="application/javascript">
	grecaptcha.ready(function() {
		grecaptcha.execute('6Le1WMUUAAAAANAfdjxqXAo2OpkfmkxH7RSD-sLK', {action: 'contact_form'}).then(function(token) {
			document.getElementById("g-recaptcha-response").value = token;
		});
	});
	function onSubmit(token) {	
		document.getElementById("signup-form").submit();
	}
</script>
</html>