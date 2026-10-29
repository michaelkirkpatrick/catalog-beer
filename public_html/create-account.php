<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Default Values
$name = '';
$email = '';
$password = '';
$termsAgreement = false;
$success = false;
$error = false;
$errorMsg = '';
$validState = array('name'=>'', 'email'=>'', 'password'=>'', 'terms_agreement'=>'');
$validMsg = array('name'=>'', 'email'=>'', 'password'=>'');

// Classes
$alert = new Alert();

if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
    // Destroy Session
    session_destroy();
    session_start();
}

if(isset($_POST['signupFormHidden'])){
    // Get Posted Variables
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if(isset($_POST['terms_agreement'])){
        if($_POST['terms_agreement'] === "1"){
            $termsAgreement = true;
        }else{
            $termsAgreement = false;
        }
    }else{
        $termsAgreement = false;
    }
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    // Honeypot, signed form stamp, then reCAPTCHA (classes/helpers/antispam.php).
    // The account-level checks — disposable domains, per-network caps — are the
    // API's, which is why the visitor's address goes along as signup_ip.
    $blocked = formGate('signup', $captcha, 'create-account.php');
    if($blocked === 'honeypot'){
        // A bot filled the hidden field: show it the success page, create nothing.
        $success = true;
    }elseif($blocked !== ''){
        $error = true;
        $alert->msg = formGateMessage($blocked, 'create an account');
    }else{
        // Send to API
        $data = array('name'=>$name, 'email'=>$email, 'password'=>$password, 'terms_agreement'=>$termsAgreement, 'signup_ip'=>($_SERVER['REMOTE_ADDR'] ?? ''));
        $api = new API();
        $response = $api->request('POST', '/users', $data);
        if($api->httpcode == 201){
            // Successfully Created Account
            ensureSession();
            session_regenerate_id(true);
            $array = json_decode($response);
            $_SESSION['userID'] = $array->id;
            $success = true;
            // Same request renders the confirmation, so this fires on it.
            trackEvent('Account created');
        }else{
            // Error (a 400 carries field messages; the API's 429 sign-up caps carry error_msg)
            $array = json_decode($response, true);
            if(!empty($array['error_msg'])){
                $alert->msg = $array['error_msg'];
            }
            if(isset($array['valid_state'])){$validState = $array['valid_state'];}
            if(isset($array['valid_msg'])){$validMsg = $array['valid_msg'];}
        }
    }
}

// HTML Head
$htmlHead = new htmlHead('Create an Account');
echo $htmlHead->html;
?>
<body>
    <div class="cb-page cb-page--xs">
        <div class="cbf-authmark"><a class="cb-wordmark" href="/">Catalog<span class="tld">.beer</span></a></div>
        <?php if($success){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Account created!</h1>
            <p class="cbf-lede">Before you can contribute to the database, or obtain an API key, you&#8217;ll need to verify your email address.</p>
        </div>
        <div class="cbf-panel">
            <p>Check your inbox for an email with the subject line <strong>&#8220;Confirm your Catalog.beer Account&#8221;</strong>. Click the link in that email and you&#8217;ll be all set!</p>
            <a class="cbf-btn" href="/" role="button">Go to Homepage</a>
        </div>
        <?php }else{ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Create an account</h1>
            <p class="cbf-lede">Free to use, free to build on.</p>
        </div>
        <?php
        // Display Alerts
        echo $alert->display();
        ?>
        <form method="POST" id="signup-form" class="cbf-panel" data-recaptcha-sitekey="<?php echo h(RECAPTCHA_SITE_KEY); ?>" data-recaptcha-action="signup">
            <input type="hidden" name="signupFormHidden" value="set" />
            <?php echo formGuardFields('signup'); ?>
            <?php
            // Everything here is required — no asterisks, no legend.
            // Name
            $inputName = new InputField();
            $inputName->name = 'name';
            $inputName->description = 'Name';
            $inputName->required = true;
            $inputName->markRequired = false;
            $inputName->autocomplete = 'name';
            $inputName->value = $name;
            $inputName->validState = $validState['name'];
            $inputName->validMsg = $validMsg['name'];
            echo $inputName->display();

            // Email
            $inputEmail = new InputField();
            $inputEmail->name = 'email';
            $inputEmail->description = 'Email address';
            $inputEmail->hint = 'You’ll need to verify your email address before contributing or getting an API key.';
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
            $inputPassword->autocomplete = 'new-password';
            $inputPassword->value = $password;
            $inputPassword->validState = $validState['password'];
            $inputPassword->validMsg = $validMsg['password'];
            echo $inputPassword->display();

            // Terms of Use
            $checkbox = new Checkbox();
            $checkbox->validState = $validState['terms_agreement'];
            // Label is raw HTML by contract (see Checkbox's docblock).
            echo $checkbox->display('terms_agreement', 'I agree to the <a href="/terms">Terms of Use</a> for using this site.', true, $termsAgreement);
            ?>
            <div class="cbf-actions">
                <button type="submit" class="cbf-btn cbf-btn--wide">Sign Up</button>
            </div>
            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="">
        </form>
        <p class="cbf-authfoot">Already have an account? <a href="/login">Sign in</a></p>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
<?php if(!$success){ ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo h(RECAPTCHA_SITE_KEY); ?>"></script>
<?php echo jsTag('/assets/js/recaptcha-form.js'); ?>
<?php } ?>
</html>
