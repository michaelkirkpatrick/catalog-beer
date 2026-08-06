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

    // Verify Captcha
    $captchaSecretKey = RECAPTCHA_SECRET_KEY;
    $captchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captchaSecretKey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
    $captchaJSON = json_decode($captchaResponse, true);
    if($captchaJSON['success'] == false){
        // Didn't Pass Captcha
        $error = true;
        $errorMsg = 'Sorry, there was an error processing the Captcha.';

        // Update Alert
        $alert->msg = $errorMsg;

    }else{
        // Successful Captcha, check score
        if($captchaJSON['score'] >= 0.5){
            // Send to API
            $data = array('name'=>$name, 'email'=>$email, 'password'=>$password, 'terms_agreement'=>$termsAgreement);
            $api = new API();
            $response = $api->request('POST', '/users', $data);
            if($api->httpcode == 201){
                // Successfully Created Account
                ensureSession();
                session_regenerate_id(true);
                $array = json_decode($response);
                $_SESSION['userID'] = $array->id;
                $success = true;
            }else{
                // Error
                $array = json_decode($response, true);
                if(!empty($array['error_msg'])){
                    $alert->msg = $array['error_msg'];
                }
                if(isset($array['valid_state'])){$validState = $array['valid_state'];}
                if(isset($array['valid_msg'])){$validMsg = $array['valid_msg'];}
            }
        }else{
            // Didn't Pass Captcha
            $error = true;
            $errorMsg = 'Sorry, Google\'s reCAPTCHA algorithm thinks you are a bot. As such, we are not going to allow you to create an account using this form. We have logged this incident. Try <a href="/contact">contacting us</a> for support.';

            // Update Alert
            $alert->msg = $errorMsg;

            // Log Error
            $errorLog = new LogError();
            $errorLog->errorNumber = 'C21';
            $errorLog->errorMsg = 'reCAPTCHA: Likely Bot';
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
        <form method="POST" id="signup-form" class="cbf-panel">
            <input type="hidden" name="signupFormHidden" value="set" />
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

            // Terms and conditions
            $checkbox = new Checkbox();
            $checkbox->validState = $validState['terms_agreement'];
            // Label is raw HTML by contract (see Checkbox's docblock), so the &
            // is written as an entity rather than left for Markdown to escape.
            echo $checkbox->display('terms_agreement', 'I agree to the <a href="/terms">Terms &amp; Conditions</a> for using this site.', true, $termsAgreement);
            ?>
            <div class="cbf-actions">
                <button class="cbf-btn cbf-btn--wide" data-callback="onSubmit">Sign Up</button>
            </div>
            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="">
        </form>
        <p class="cbf-authfoot">Already have an account? <a href="/login">Sign in</a></p>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
<?php if(!$success){ ?>
<script src='https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>'></script>
<script type="application/javascript">
    grecaptcha.ready(function() {
        grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'contact_form'}).then(function(token) {
            document.getElementById("g-recaptcha-response").value = token;
        });
    });
    function onSubmit(token) {
        document.getElementById("signup-form").submit();
    }
</script>
<?php } ?>
</html>
