<?php
/* ---
$sendEmail = new SendEmail();
$sendEmail->email = '';
$sendEmail->subject = '';
$sendEmail->filename = 'VAR'; // replace VAR, i.e. email-VAR.html
$sendEmail->find = array(); // Optional
$sendEmail->replace = array(); // Optional
$sendEmail->send();
--- */

class SendEmail {
	
	// Variables
	public $email = '';	// From email (e.g. "hannah@catalog.beer")
	public $name = ''; 	// From name (e.g. "Hannah Brewer")
	public $subject = '';
	public $plainText = '';
	
	// Validation
	public $error = false;
	public $errorMsg = '';
		
	public function validateEmail($email){
		// Initial State
		$validEmail = false;
		
		// Trim Email
		$email = trim($email);
		$email = strtolower($email);
		
		if(!empty($email)){
			// Not Blank
			if(filter_var($email, FILTER_VALIDATE_EMAIL)){
				if(strlen($email) <= 255){
					// Valid Email
					$validEmail = true;

					// Save to Class
					$this->email = $email;
				}else{
					// Check string length
					$this->error = true;
					$this->errorMsg = 'We apologize, your email address is a little too long for us to process. Please input an email that is less than 255 bytes in length.';
					
					// Log Error
					$errorLog = new LogError();
					$errorLog->errorNumber = 'C7';
					$errorLog->errorMsg = 'Email address > 255 characters';
					$errorLog->badData = $email;
					$errorLog->filename = 'API / SendEmail.class.php';
					$errorLog->write();
				}
			}else{
				// Invalid Email
				$this->error = true;
				$this->errorMsg = 'Sorry, the email address you provided appears to be invalid.';

				// Log Error
				$errorLog = new LogError();
				$errorLog->errorNumber = 'C8';
				$errorLog->errorMsg = 'Invliad Email. Does not pass filter_var';
				$errorLog->badData = $email;
				$errorLog->filename = 'API / SendEmail.class.php';
				$errorLog->write();
			}
		}else{
			// Invalid Email
			$this->error = true;
			$this->errorMsg = 'Sorry, we seem to be missing your email address. Please enter it.';

			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C9';
			$errorLog->errorMsg = 'No email address provided';
			$errorLog->badData = $email;
			$errorLog->filename = 'SendEmail.class.php';
			$errorLog->write();
		}
		
		// Return Status
		return $validEmail;
	}
	
	public function send(){
		
		/*---
		Required Set Variables
		$this->email --> FROM Email (as submitted in Contact Form)
		$this->subject
		$this->plainText
		---*/
		
		if(empty($this->name)){
			// Missing Email
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C16';
			$errorLog->errorMsg = 'Missing name';
			$errorLog->badData = '';
			$errorLog->filename = 'API / SendEmail.class.php';
			$errorLog->write();
		}else{
			// Prep Name
			$this->name = strip_tags($this->name);
		}
		
		if(empty($this->email)){
			// Missing Email
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C10';
			$errorLog->errorMsg = 'Missing email';
			$errorLog->badData = '';
			$errorLog->filename = 'API / SendEmail.class.php';
			$errorLog->write();
		}
		
		if(empty($this->subject)){
			// Missing Email
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C11';
			$errorLog->errorMsg = 'Missing subject';
			$errorLog->badData = '';
			$errorLog->filename = 'API / SendEmail.class.php';
			$errorLog->write();
		}else{
			// Prep Subject
			$this->subject = strip_tags($this->subject);
		}
		
		if(empty($this->plainText)){
			// Missing Email
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C12';
			$errorLog->errorMsg = 'Missing plain text of email';
			$errorLog->badData = '';
			$errorLog->filename = 'API / SendEmail.class.php';
			$errorLog->write();
		}else{
			// Prep Message
			$prefix = '-- Catalog.beer Website Email --' . "\n\n" . 'From: ' . $this->name . ' <' . $this->email . '>' . "\n\n";
			$this->plainText = $prefix . strip_tags($this->plainText);
		}
		
		// Validate Email
		$this->validateEmail($this->email);
		
		if(!$this->error){
			// Required Files
			include 'Mail.php';
			include 'Mail/mime.php';

			/* ---
			PEAR Mail Factory
			http://pear.php.net/manual/en/package.mail.mail.factory.php
			--- */
			$host = "smtp-relay.gmail.com";
			$port = 587;
			$smtp = Mail::factory('smtp', array('host'=>$host, 'port'=>$port));

			/* ---
			PEAR MIME
			http://pear.php.net/manual/en/package.mail.mail-mime.mail-mime.php
			--- */
			$crlf = "\n";
			$mime = new Mail_mime(array('eol' => $crlf));

			// Headers
			$from = 'Catalog.beer <michael@catalog.beer>';
			$replyto = $this->email;
			$headers = array('From'=>$from, 'To'=>'michael@interchangedesign.com', 'Subject'=>$this->subject, 'Reply-To'=>$replyto);

			// Plain Text
			$mime->setTXTBody($this->plainText);

			$body = $mime->get();
			$headers = $mime->headers($headers);

			$smtp = Mail::factory('smtp',
				array ('host' => 'smtp-relay.gmail.com',
							 'port' => 587,
							 'auth' => true,
							 'username' => '',
							 'password' => '',
							 'debug' => false));

			/* ---
			PEAR Send Mail
			http://pear.php.net/manual/en/package.mail.mail.send.php
			--- */
			$mail = $smtp->send('michael@interchangedesign.com', $headers, $body);

			// Process Errors
			if(PEAR::isError($mail)){
				// Error Sending Email
				$this->error = true;
				$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';

				// Log Error
				$errorLog = new LogError();
				$errorLog->errorNumber = 'C13';
				$errorLog->errorMsg = 'Error sending email';
				$errorLog->badData = $mail->getMessage();
				$errorLog->filename = 'API / SendEmail.class.php';
				$errorLog->write();
			}
		}
	}
}
?>