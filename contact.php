<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

$alert = new Alert();

// Defaults
$name = '';
$email = '';
$message = '';
$subject = '';
$validState = array('name'=>'', 'email'=>'', 'subject'=>'', 'message'=>'');
$validMsg = array('name'=>'', 'email'=>'', 'subject'=>'', 'message'=>'');

// Process Form
if(isset($_POST['signupFormHidden'])){
	// Assume no error
	$error = FALSE;
	
	// Get Variables
	$name = trim($_POST['name']);
	$email = trim($_POST['email']);
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);
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
		$errorLog->errorNumber = 'C17';
		$errorLog->errorMsg = 'CAPTCHA Error';
		$errorLog->badData = $errorLogMsg;
		$errorLog->filename = 'create-account';
		$errorLog->write();
	}else{
	
		// Send Email Class
		$sendEmail = new SendEmail();

		// Validate Name
		if(!empty($name)){
			if(strlen($name <= 255)){
				// Valid Name
				$validState['name'] = 'success';
				$sendEmail->name = $name;
			}else{
				// String too long
				$error = TRUE;
				$validState['name'] = 'invalid';
				$validMsg['name'] = 'Sorry, your name is a little too long for us to process. Please enter a name less than 255 characters in length.';
			}
		}else{
			// Missing Name
			$error = TRUE;
			$validState['name'] = 'invalid';
			$validMsg['name'] = 'What\'s your name?';
		}

		// Validate Subject
		if(!empty($subject)){
			if(strlen($subject <= 255)){
				// Valid Subject
				$validState['subject'] = 'success';
				$sendEmail->subject = $subject;
			}else{
				// Subject too long
				$error = TRUE;
				$validState['subject'] = 'invalid';
				$validMsg['subject'] = 'Sorry, the subject of your message is a little too long for us to process. Please enter a subject less than 255 characters in length.';
			}
		}else{
			// Missing Subject
			$error = TRUE;
			$validState['subject'] = 'invalid';
			$validMsg['subject'] = 'What\'s the subject of your message?';
		}

		// Validate Email
		if($sendEmail->validateEmail($email)){
			$validState['email'] = 'valid';
			$sendEmail->email = $email;
		}else{
			$error = true;
			$validState['email'] = 'invalid';
			$validMsg['email'] = $sendEmail->errorMsg;
		}

		// Validate Message
		if(!empty($message)){
			// Valid Message
			$validState['message'] = 'success';
			$sendEmail->plainText = $message;
		}else{
			// Missing Message
			$error = TRUE;
			$validState['message'] = 'invalid';
			$validMsg['message'] = 'What\'s your message?';
		}

		// Send Message
		if(!$error){
			// Send to Michael
			$sendEmail->send();

			if(!$sendEmail->error){
				// Email sent, show message
				$alert->msg = 'Thank you! Your message has been sent.';
				$alert->type = 'success';
				$alert->dismissible = true;

				// Clear Variables
				$name = '';
				$email = '';
				$subject = '';
				$message = '';
				$validState = array('name'=>'', 'email'=>'', 'subject'=>'', 'message'=>'');
				$validMsg = array('name'=>'', 'email'=>'', 'subject'=>'', 'message'=>'');
			}else{
				// Error Sending Email
				// Email sent, show message
				$alert->msg = 'Sorry, we encountered an error when we tried sending your message. It was unable to be sent. We\'ve logged the issue and our support team will look into it.';
				$alert->type = 'error';
			}
		}
	}
}

// HTML Head
$htmlHead = new htmlHead('Catalog.beer: The Internet\'s Beer Database');
$htmlHead->addDescription('Drop us a line. Our team will get back to you within 24-hours.');
echo $htmlHead->html;
?>
<body>
	<?php
	echo $nav->navbar('');
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-2">
			<!-- Empty Column -->
			</div>
			<div class="col-md-8">
				<h1>Get in Touch</h1>
				<?php
				// Display Alerts
				echo $alert->display();
				?>
				<form method="post" id="contact-form">
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
					$inputEmail->description = 'Email';
					$inputEmail->type = 'email';
					$inputEmail->required = true;
					$inputEmail->placeholder = 'e.g., hannah@catalog.beer';
					$inputEmail->value = $email;
					$inputEmail->validState = $validState['email'];
					$inputEmail->validMsg = $validMsg['email'];
					echo $inputEmail->display();

					// Subject
					$inputSubject = new InputField();
					$inputSubject->name = 'subject';
					$inputSubject->description = 'Subject';
					$inputSubject->required = true;
					$inputSubject->value = $subject;
					$inputSubject->validState = $validState['subject'];
					$inputSubject->validMsg = $validMsg['subject'];
					echo $inputSubject->display();

					// Body
					$textarea = new Textarea();
					$textarea->name = 'message';
					$textarea->description = 'Message';
					$textarea->value = $message;
					$textarea->required = true;
					$textarea->validState = $validState['message'];
					$textarea->validMsg = $validMsg['message'];
					$textarea->rows = 8;
					echo $textarea->display();
					?>
					<button class="btn btn-primary g-recaptcha" data-sitekey="6LfNzi0UAAAAAOyPRnBymKZfapoP_OibiDcA-O0f" data-callback="onSubmit" >Send Message</button>
				</form>
			</div>
			<div class="col-md-2">
			<!-- Empty Column -->
			</div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="application/javascript">
	function onSubmit(token) {	
		 document.getElementById("contact-form").submit();
	 }
</script>
</html>	