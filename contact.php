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
if(isset($_POST['submit'])){
	// Assume no error
	$error = FALSE;
	
	// Get Variables
	$name = trim($_POST['name']);
	$email = trim($_POST['email']);
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);
	
	// Send Email Class
	$sendEmail = new SendEmail();
	
	// Validate Name
	if(!empty($name)){
		if(strlen($name <= 255)){
			// Valid Name
			$validState['name'] = 'success';
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
		
		// Alert
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
				<form method="post">
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
					<button type="submit" class="btn btn-primary" name="submit">Send Message</button>
				</form>
			</div>
			<div class="col-md-2">
			<!-- Empty Column -->
			</div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>	