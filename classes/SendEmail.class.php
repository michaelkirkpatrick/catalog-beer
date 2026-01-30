<?php
/* ---
Catalog.beer
$sendEmail = new SendEmail();
$sendEmail->email = '';
--- */

class SendEmail {
	
	// Variables
	public $email = '';	// From email (e.g. "hannah@catalog.beer")
	public $name = ''; 	// From name (e.g. "Hannah Brewer")
	public $subject = '';
	public $plainText = '';
	private $postmarkServerToken = '';

	// Validation
	public $error = false;
	public $errorMsg = '';

	function __construct(){
		$this->postmarkServerToken = POSTMARK_SERVER_TOKEN;
	}

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
					$errorLog->filename = 'SendEmail.class.php';
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
				$errorLog->filename = 'SendEmail.class.php';
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
	
	public function send($name, $email, $subject, $message){
		// Save to Class
		$this->name = $name;
		$this->email = $email;
		$this->subject = $subject;
		$this->plainText = $message;
		
		if(empty($this->name)){
			// Missing Name
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C16';
			$errorLog->errorMsg = 'Missing name';
			$errorLog->badData = '';
			$errorLog->filename = 'SendEmail.class.php';
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
			$errorLog->filename = 'SendEmail.class.php';
			$errorLog->write();
		}else{
			// Validate Email
			$this->validateEmail($this->email);
		}
		
		if(empty($this->subject)){
			// Missing Subject
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C11';
			$errorLog->errorMsg = 'Missing subject';
			$errorLog->badData = '';
			$errorLog->filename = 'SendEmail.class.php';
			$errorLog->write();
		}else{
			// Prep Subject
			$this->subject = strip_tags($this->subject);
		}
		
		if(empty($this->plainText)){
			// Missing Message
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C12';
			$errorLog->errorMsg = 'Missing plain text of email';
			$errorLog->badData = '';
			$errorLog->filename = 'SendEmail.class.php';
			$errorLog->write();
		}else{
			// Prep Message
			$prefix = '-- Catalog.beer Website Email --' . "\n\n" . 'From: ' . $this->name . ' <' . $this->email . '>' . "\n\n";
			$this->plainText = $prefix . strip_tags($this->plainText);
		}
		
		if(!$this->error){
			$postmarkSendEmail = new PostmarkSendEmail();
			$text = new Text(true, false, false);
			$postmarkSendEmail->generateBody('michael@catalog.beer', $this->subject, 'contact-form', $text->get($this->plainText), $this->plainText, $this->email);
			
			$json = json_encode($postmarkSendEmail);

			// Start cURL
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://api.postmarkapp.com/email",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_HTTPHEADER => array(
					"Accept: application/json",
					"cache-control: no-cache",
					"Content-Type: application/json",
					"X-Postmark-Server-Token: " . $this->postmarkServerToken
				),
				CURLOPT_POSTFIELDS => "$json"
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if($err){
				// cURL Error
				$this->error = true;
				$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';

				// Log Error
				$errorLog = new LogError();
				$errorLog->errorNumber = 'C19';
				$errorLog->errorMsg = 'cURL Error';
				$errorLog->badData = $err;
				$errorLog->filename = 'SendEmail.class.php';
				$errorLog->write();
			}else{
				// Response Received
				$decodedReponse = json_decode($response);
				if($decodedReponse->ErrorCode !== 0){
					// Error Sending Email
					$this->error = true;
					$this->errorMsg = 'Sorry, there was an error sending your email. We\'ve logged the issue and our support team will look into it.';

					// Log Error
					$errorLog = new LogError();
					$errorLog->errorNumber = 'C20';
					$errorLog->errorMsg = 'Postmark App Error';
					$errorLog->badData = $decodedReponse;
					$errorLog->filename = 'SendEmail.class.php';
					$errorLog->write();
				}
			}
		}
	}
}
?>