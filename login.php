<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Classes
$alert = new Alert();

// Default Values
$validState = array('email'=>'', 'password'=>'');
$validMsg = array('email'=>'', 'password'=>'');
$email = '';
$password = '';

// Requested Page
if(isset($_GET['request'])){
	// Save Next Page
	$nextPage = substr($_GET['request'], 1);
	$exploded = explode('/', $nextPage);
	
	// Default Message
	$message = 'Hello! Before redirecting you to the page you requested, would you please sign in?';
	
	// Detailed Messages
	switch($exploded[0]){
		case 'brewer':
			if($exploded[1] == 'add'){
				$message = 'Hello! Before you can add a new brewer to the database, you will need to sign in. Don\'t have an account? You can [create one](/signup).';
			}elseif($exploded[2] == 'add-location'){
				$message = 'Hello! Before you can add new location for this brewer to the database, you will need to sign in. Don\'t have an account? You can [create one](/signup).';
			}
			break;
		case 'beer':
			if($exploded[1] == 'add'){
				$message = 'Hello! Before you can add a new beer to the database, you will need to sign in. Don\'t have an account? You can [create one](/signup).';
			}
			break;
		case 'location':
			if($exploded[1] == 'add-address'){
				$message = 'Hello! Before you can add an address for this location to the database, you will need to sign in. Don\'t have an account? You can [create one](/signup).';
			}
			break;
		default:
			// No Action
	}
	
	// Show Alert
	$alert->msg = $message;
	$alert->type = 'warning';
	$alert->dismissible = false;
}else{
	$nextPage = '/';
}

// Process Form
if(isset($_POST['submit'])){
	// Get Posted Variables
	$email = $_POST['email'];
	$password = $_POST['password'];
	
	// Login
	$api = new API();
	$apiResponse = $api->request('POST', '/login', array('email'=>$email, 'password'=>$password));
	$loginArray = json_decode($apiResponse, true);
	if(isset($loginArray['id'])){
		// Successful Log In
		// Set userID
		$_SESSION['userID'] = $loginArray['id'];
		
		// Go to $nextPage
		header('location: ' . $nextPage);
		exit();
	}else{
		// Error Logging In
		$validState = $loginArray['valid_state'];
		$validMsg = $loginArray['valid_msg'];
		if(!empty($loginArray['error_msg'])){
			$alert->msg = $loginArray['error_msg'];
		}
	}
}

// HTML Head
$htmlHead = new htmlHead('Sign In');
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
				$nav->breadcrumbText = array('Home', 'Sign In');
				$nav->breadcrumbLink = array('/');
				echo $nav->breadcrumbs();
				
				// Display Alerts
				echo $alert->display();
				?>
        <form method="post">
					<?php
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
					?>
					<button type="submit" class="btn btn-primary" name="submit">Sign in</button>
       		<p class="text-center"><a href="/signup">Create an account</a></p>
        </form>
      </div>
      <div class="col"></div>
    </div>  
  </div>
  <?php echo $nav->footer(); ?>
</body>
</html>