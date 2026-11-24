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
    // Save Next Page — a path on this site only (safeReturnPath())
    $nextPage = safeReturnPath($_GET['request']);
    $exploded = explode('/', substr($nextPage, 1));
    
    // Default Message
    $message = 'Hello! Before redirecting you to the page you requested, would you please sign in?';
    
    // Detailed Messages
    switch($exploded[0]){
        case 'brewer':
            if(isset($exploded[1]) && $exploded[1] === 'add'){
                $message = 'Hello! Before you can add a new brewer to the database, you will need to sign in. Don\'t have an account? You can <a href="/signup">create one</a>.';
            }elseif(isset($exploded[2]) && $exploded[2] === 'add-location'){
                $message = 'Hello! Before you can add new location for this brewer to the database, you will need to sign in. Don\'t have an account? You can <a href="/signup">create one</a>.';
            }
            break;
        case 'beer':
            if(isset($exploded[1]) && $exploded[1] === 'add'){
                $message = 'Hello! Before you can add a new beer to the database, you will need to sign in. Don\'t have an account? You can <a href="/signup">create one</a>.';
            }
            break;
        case 'location':
            // $exploded is ['location', '{id}', '{action}'] — the action is at [2],
            // not [1], which is why this message never used to appear. add-address
            // and edit-address now redirect into the location editor, but they're
            // still live URLs, so all three are worth a message.
            if(isset($exploded[2]) && ($exploded[2] === 'edit' || $exploded[2] === 'add-address' || $exploded[2] === 'edit-address')){
                $message = 'Hello! Before you can edit this location, you will need to sign in. Don\'t have an account? You can <a href="/signup">create one</a>.';
            }
            break;
        case 'confirm-email':
            // The link in a "confirm your new email address" message. The
            // account still signs in with its CURRENT address at this point.
            $message = 'Hello! To confirm your new email address, please sign in first. Use your account&#8217;s current email address; the new one takes over once you confirm.';
            break;
        default:
            // No Action
    }
    
    // Sent here because the password changed after this browser signed in
    // (initialize.php): that outranks the page they were headed for.
    if(isset($_GET['signed_out'])){
        $message = 'Your password was changed, so you have been signed out here. Please sign in with your new password.';
    }

    // Show Alert
    $alert->msg = $message;
    $alert->type = 'warning';
    $alert->dismissible = false;
}elseif(isset($_GET['next'])){
    // The nav's "Sign in" link (signInHref()): the visitor chose to sign in
    // from a page they could already see, so go back there without the
    // "page you requested" alert, which is for login-gated pages.
    $nextPage = safeReturnPath($_GET['next']);
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
        signIn($loginArray['id'], $loginArray['session_gen'] ?? 0);
        
        // Go to $nextPage
        header('location: ' . $nextPage);
        exit();
    }elseif(is_array($loginArray) && isset($loginArray['valid_state'])){
        // Error Logging In
        $validState = $loginArray['valid_state'];
        $validMsg = $loginArray['valid_msg'];
        if(!empty($loginArray['error_msg'])){
            $alert->msg = $loginArray['error_msg'];
        }
    }else{
        // API unreachable or unexpected response
        http_response_code(503);
        $alert->msg = 'Sorry, we are unable to process your login right now. Please try again later.';
        $alert->type = 'warning';
    }
}

// HTML Head
$htmlHead = new htmlHead('Sign In');
// robots.txt already disallows /login; this is the belt to that suspender, and
// it means the ?request= variants never carry a canonical of their own.
$htmlHead->noindex();
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page cb-page--xs">
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Sign in</h1>
        </div>
        <?php echo $alert->display(); ?>
        <form method="post" class="cbf-panel">
            <?php
            // Both fields required — marking them individually says nothing,
            // so the asterisks and legend are omitted here.
            // Email
            $inputEmail = new InputField();
            $inputEmail->name = 'email';
            $inputEmail->description = 'Email address';
            $inputEmail->type = 'email';
            $inputEmail->required = true;
            $inputEmail->markRequired = false;
            $inputEmail->autocomplete = 'email';
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
            $inputPassword->markRequired = false;
            $inputPassword->autocomplete = 'current-password';
            $inputPassword->value = $password;
            $inputPassword->validState = $validState['password'];
            $inputPassword->validMsg = $validMsg['password'];
            echo $inputPassword->display();
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn cbf-btn--wide" name="submit">Sign in</button>
            </div>
        </form>
        <p class="cbf-authfoot">New here? <a href="/signup">Create an account</a></p>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>