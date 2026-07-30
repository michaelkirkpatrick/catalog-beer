<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Alert
$alert = new Alert();

// Which inline editor is open (?edit=name|email|password|delete)
$openPanel = '';
if(isset($_GET['edit']) && in_array($_GET['edit'], array('name', 'email', 'password', 'delete'), true)){
    $openPanel = $_GET['edit'];
}

// Validation State
$validState = array('name'=>'', 'email'=>'', 'currentPassword'=>'', 'newPassword'=>'');
$validMsg = array('name'=>'', 'email'=>'', 'currentPassword'=>'', 'newPassword'=>'');

// Field values (repopulated from the POST on a validation error)
$nameValue = $userInfo->name;
$emailValue = $userInfo->email;

// Process Forms
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!csrf_verify()){
        // Invalid CSRF Token
        $alert->msg = 'Sorry, we were unable to verify your request. Please try again.';
    }elseif(isset($_POST['submitName'])){
        // --- Update Name ---
        $openPanel = 'name';
        $nameValue = trim($_POST['name'] ?? '');
        $apiResponse = $api->request('PATCH', '/users/' . $_SESSION['userID'], array('name'=>$nameValue));
        $patchData = json_decode($apiResponse);
        if($api->httpcode == 200 && isset($patchData->id)){
            $_SESSION['account_flash'] = 'Your name has been updated.';
            header('location: /account');
            exit();
        }elseif(isset($patchData->error)){
            $validState['name'] = $patchData->valid_state->name ?? 'invalid';
            $validMsg['name'] = $patchData->valid_msg->name ?? '';
            if(empty($validMsg['name']) && !empty($patchData->error_msg)){
                $alert->msg = $patchData->error_msg;
            }
        }else{
            $alert->msg = 'Sorry, we are unable to update your name right now. Please try again later.';
        }
    }elseif(isset($_POST['submitEmail'])){
        // --- Update Email ---
        $openPanel = 'email';
        $emailValue = trim($_POST['email'] ?? '');

        // Mirror the API's re-verification rule for messaging: moving to a new
        // email *domain* resets verification, brewery privileges, and the API key.
        $oldDomain = strtolower(substr(strrchr($userInfo->email, '@') ?: '', 1));
        $newDomain = strtolower(substr(strrchr($emailValue, '@') ?: '', 1));

        $apiResponse = $api->request('PATCH', '/users/' . $_SESSION['userID'], array('email'=>$emailValue));
        $patchData = json_decode($apiResponse);
        if($api->httpcode == 200 && isset($patchData->id)){
            if($newDomain !== $oldDomain){
                $_SESSION['account_flash'] = 'Your email address has been updated. Because it is on a new domain, we sent a verification message to ' . $emailValue . ' and reset your API key. Verify your address to keep adding data.';
            }else{
                $_SESSION['account_flash'] = 'Your email address has been updated.';
            }
            header('location: /account');
            exit();
        }elseif(isset($patchData->error)){
            $validState['email'] = $patchData->valid_state->email ?? 'invalid';
            $validMsg['email'] = $patchData->valid_msg->email ?? '';
            if(empty($validMsg['email']) && !empty($patchData->error_msg)){
                $alert->msg = $patchData->error_msg;
            }
        }else{
            $alert->msg = 'Sorry, we are unable to update your email address right now. Please try again later.';
        }
    }elseif(isset($_POST['submitPassword'])){
        // --- Update Password ---
        $openPanel = 'password';
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';

        // The API's PATCH doesn't ask for the current password, so confirm it
        // against /login first — a borrowed session can't silently take the account.
        $loginResponse = $api->request('POST', '/login', array('email'=>$userInfo->email, 'password'=>$currentPassword));
        $loginData = json_decode($loginResponse, true);
        if(isset($loginData['id'])){
            $apiResponse = $api->request('PATCH', '/users/' . $_SESSION['userID'], array('password'=>$newPassword));
            $patchData = json_decode($apiResponse);
            if($api->httpcode == 200 && isset($patchData->id)){
                $_SESSION['account_flash'] = 'Your password has been updated.';
                header('location: /account');
                exit();
            }elseif(isset($patchData->error)){
                $validState['newPassword'] = $patchData->valid_state->password ?? 'invalid';
                $validMsg['newPassword'] = $patchData->valid_msg->password ?? '';
                if(empty($validMsg['newPassword']) && !empty($patchData->error_msg)){
                    $alert->msg = $patchData->error_msg;
                }
            }else{
                $alert->msg = 'Sorry, we are unable to update your password right now. Please try again later.';
            }
        }elseif(is_array($loginData) && isset($loginData['valid_state'])){
            $validState['currentPassword'] = 'invalid';
            $validMsg['currentPassword'] = $loginData['valid_msg']['password'] ?? 'Incorrect password. Please check your password and try again.';
        }else{
            $alert->msg = 'Sorry, we are unable to update your password right now. Please try again later.';
        }
    }elseif(isset($_POST['submitDelete'])){
        // --- Delete Account ---
        $openPanel = 'delete';
        $api->request('DELETE', '/users/' . $_SESSION['userID'], '');
        if($api->httpcode == 204){
            session_destroy();
            header('location: /');
            exit();
        }
        $alert->msg = 'Sorry, we are unable to delete your account right now. Please try again later.';
    }
}

// Flash message from a successful update
if(!empty($_SESSION['account_flash'])){
    $alert->msg = $_SESSION['account_flash'];
    $alert->type = 'success';
    unset($_SESSION['account_flash']);
}

// Text Prep
$text = new Text(false, true, true);

// Email verification tag + API section data
if($userInfo->email_verified){
    $emailTag = ' <span class="cb-tag ac-tag ac-tag--ok">Verified</span>';

    // Get API Key
    $apiKeyResp = $api->request('GET', '/users/' . $_SESSION['userID'] . '/api-key', '');
    $apiKeyData = json_decode($apiKeyResp);
    if(isset($apiKeyData->api_key)){
        $apiKey = $apiKeyData->api_key;

        // Fetch usage + billing status in one call
        $billingResp = $api->request('GET', '/billing', '');
        $billingData = json_decode($billingResp);
        if(isset($billingData->count) && isset($billingData->request_limit)){
            $billingEnabled = !empty($billingData->billing_enabled);
            $estimatedCents = intval($billingData->estimated_charge_cents ?? 0);
            $usageCount = intval($billingData->count);
            $usageLimit = intval($billingData->request_limit);
            $usagePercent = $usageLimit > 0 ? min(round(($usageCount / $usageLimit) * 100), 100) : 0;
            $usageMonth = date('F Y', mktime(0, 0, 0, intval($billingData->month), 1, intval($billingData->year)));
            if($billingEnabled){
                // Past the line is billed, not blocked — no alarm colors.
                $meterClass = '';
            }elseif($usagePercent >= 90){
                $meterClass = ' ac-meter__fill--danger';
            }elseif($usagePercent >= 75){
                $meterClass = ' ac-meter__fill--warn';
            }else{
                $meterClass = '';
            }
            if(isset($billingData->card->last4)){
                $billingCard = ucfirst($billingData->card->brand) . ' &#8226;&#8226;&#8226;&#8226; ' . $billingData->card->last4;
            }
        }
    }
}else{
    $emailTag = ' <span class="cb-tag ac-tag ac-tag--warn">Unverified</span>';

    // Verification-sent date
    $today = date('l, F jS', time());
    $sent = date('l, F jS', $userInfo->email_auth_sent);
    if($sent == $today){
        $dateString = 'today at ' . date('g:i A', $userInfo->email_auth_sent);
    }else{
        $dateString = $sent . ' at ' . date('g:i A', $userInfo->email_auth_sent);
    }
}

// HTML Head
$htmlHead = new htmlHead('My Account');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <main class="cb-page cb-page--form ac-page">
        <div class="cbf-pagehead ac-pagehead">
            <h1 class="cbf-h1">My Account</h1>
        </div>
        <?php echo $alert->display(); ?>

        <span class="cb-label cb-label--rule ac-sec ac-sec--first">Profile</span>

        <?php if($openPanel === 'name'){ ?>
        <form method="post" class="ac-card">
            <?php
            echo csrf_field();
            $inputName = new InputField();
            $inputName->name = 'name';
            $inputName->description = 'Name';
            $inputName->required = true;
            $inputName->markRequired = false;
            $inputName->autocomplete = 'name';
            $inputName->autofocus = true;
            $inputName->value = $nameValue;
            $inputName->validState = $validState['name'];
            $inputName->validMsg = $validMsg['name'];
            echo $inputName->display();
            ?>
            <div class="ac-actions">
                <button type="submit" class="cbf-btn" name="submitName">Save</button>
                <a class="cbf-btn cbf-btn--ghost" href="/account">Cancel</a>
            </div>
        </form>
        <?php }else{ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Name</span>
            <span class="ac-row__value"><?php echo $text->get($userInfo->name); ?></span>
            <a class="cb-action" href="/account?edit=name">Edit</a>
        </div>
        <?php } ?>

        <?php if($openPanel === 'email'){ ?>
        <form method="post" class="ac-card">
            <?php
            echo csrf_field();
            $inputEmail = new InputField();
            $inputEmail->name = 'email';
            $inputEmail->description = 'Email';
            $inputEmail->type = 'email';
            $inputEmail->required = true;
            $inputEmail->markRequired = false;
            $inputEmail->autocomplete = 'email';
            $inputEmail->autofocus = true;
            $inputEmail->value = $emailValue;
            $inputEmail->validState = $validState['email'];
            $inputEmail->validMsg = $validMsg['email'];
            $inputEmail->hint = 'If the new address is on a different domain, you&#8217;ll need to re-verify your email and your API key will be reset.';
            echo $inputEmail->display();
            ?>
            <div class="ac-actions">
                <button type="submit" class="cbf-btn" name="submitEmail">Save</button>
                <a class="cbf-btn cbf-btn--ghost" href="/account">Cancel</a>
            </div>
        </form>
        <?php }else{ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Email</span>
            <span class="ac-row__value"><?php echo $text->get($userInfo->email) . $emailTag; ?></span>
            <a class="cb-action" href="/account?edit=email">Change</a>
        </div>
        <?php } ?>

        <?php if($openPanel === 'password'){ ?>
        <form method="post" class="ac-card">
            <?php echo csrf_field(); ?>
            <div class="cbf-grid2">
                <?php
                $inputCurrent = new InputField();
                $inputCurrent->name = 'currentPassword';
                $inputCurrent->description = 'Current password';
                $inputCurrent->type = 'password';
                $inputCurrent->required = true;
                $inputCurrent->markRequired = false;
                $inputCurrent->autocomplete = 'current-password';
                $inputCurrent->autofocus = true;
                $inputCurrent->validState = $validState['currentPassword'];
                $inputCurrent->validMsg = $validMsg['currentPassword'];
                echo $inputCurrent->display();

                $inputNew = new InputField();
                $inputNew->name = 'newPassword';
                $inputNew->description = 'New password';
                $inputNew->type = 'password';
                $inputNew->required = true;
                $inputNew->markRequired = false;
                $inputNew->autocomplete = 'new-password';
                $inputNew->validState = $validState['newPassword'];
                $inputNew->validMsg = $validMsg['newPassword'];
                echo $inputNew->display();
                ?>
            </div>
            <div class="ac-actions">
                <button type="submit" class="cbf-btn" name="submitPassword">Save</button>
                <a class="cbf-btn cbf-btn--ghost" href="/account">Cancel</a>
            </div>
        </form>
        <?php }else{ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Password</span>
            <span class="ac-pw-dots">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span>
            <a class="cb-action" href="/account?edit=password">Change</a>
        </div>
        <?php } ?>

        <span class="cb-label cb-label--rule ac-sec">API</span>

        <?php if(!$userInfo->email_verified){ ?>
        <div class="cbf-alert cbf-alert--warn ac-note">
            <span class="cbf-alert__i" aria-hidden="true">!</span>
            <div>Before you will be able to add data to the Catalog.beer database or obtain an API key, you will need to verify your email address. This helps us reduce spam on the site. Check your email; we sent you a message <strong><?php echo $dateString; ?></strong> with the subject line <strong>&#8220;Confirm your Catalog.beer Account&#8221;</strong>.</div>
        </div>
        <?php }elseif(isset($apiKey)){ ?>
        <div class="ac-keybar">
            <span class="cbf-label ac-keybar__label">Secret key</span>
            <code class="ac-key" id="acctKey" data-key="<?php echo htmlspecialchars($apiKey, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($apiKey, ENT_QUOTES, 'UTF-8'); ?></code>
            <div class="ac-keybar__btns" id="acctKeyBtns" hidden>
                <button class="cb-btn cb-btn--ghost ac-minibtn" type="button" id="acctKeyToggle">Hide</button>
                <button class="cb-btn cb-btn--ghost ac-minibtn" type="button" id="acctKeyCopy">Copy</button>
            </div>
        </div>
        <p class="cbf-hint ac-note">Anyone with this key can make requests as you. Keep it out of shared code and public repositories.</p>
        <?php if(isset($usagePercent)){ ?>
        <div class="ac-usage">
            <div class="ac-usage__head">
                <span class="cb-label cb-label--sm">Usage &#8212; <?php echo $usageMonth; ?></span>
                <span class="ac-usage__nums"><?php echo number_format($usageCount) . ' / ' . number_format($usageLimit); ?> requests</span>
            </div>
            <div class="ac-meter" role="progressbar" aria-label="API usage" aria-valuenow="<?php echo $usagePercent; ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="ac-meter__fill<?php echo $meterClass; ?>" style="width: <?php echo $usagePercent; ?>%;"></div>
            </div>
            <?php if(!empty($billingEnabled) && $usageCount > $usageLimit){ ?>
            <p class="cbf-hint ac-note">You&#8217;re past the free tier&#8212;estimated charge so far: $<?php echo number_format($estimatedCents / 100, 2); ?>. Usage resets on the first of each month.</p>
            <?php }else{ ?>
            <p class="cbf-hint ac-note">Usage resets on the first of each month. <a href="/api-pricing">See API pricing.</a></p>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Billing</span>
            <span class="ac-row__value"><?php if(!empty($billingEnabled)){ echo '<span class="cb-tag ac-tag ac-tag--ok">Enabled</span>' . (isset($billingCard) ? ' <span class="ac-row__note">' . $billingCard . '</span>' : ''); }else{ echo '<span class="cb-tag ac-tag">Free tier</span>'; } ?></span>
            <a class="cb-action" href="/billing">Manage</a>
        </div>
        <p class="ac-apidocs">Want to use the Catalog.beer API? Check out our <a href="/api-docs">API Documentation</a>.</p>
        <?php }else{ ?>
        <div class="cbf-alert ac-note">
            <span class="cbf-alert__i" aria-hidden="true">!</span>
            <div>Unable to load your API key. Please try again later.</div>
        </div>
        <?php } ?>

        <span class="cb-label cb-label--rule ac-sec">Danger zone</span>

        <?php if($openPanel === 'delete'){ ?>
        <div class="ac-confirm">
            <p class="cbf-confirm">Are you sure you want to delete the account for <strong><?php echo $text->get($userInfo->email); ?></strong>? Your API key will stop working immediately.</p>
            <form method="post" class="ac-actions">
                <?php echo csrf_field(); ?>
                <button type="submit" class="cbf-btn cbf-btn--danger" name="submitDelete">Delete my account</button>
                <a class="cbf-btn cbf-btn--ghost" href="/account">Cancel</a>
            </form>
        </div>
        <?php }else{ ?>
        <div class="ac-row ac-row--bare">
            <span class="ac-row__note">Permanently delete your account and API key.</span>
            <a class="cb-action ac-danger" href="/account?edit=delete">Delete account</a>
        </div>
        <?php } ?>
    </main>
    <?php echo $nav->footer(); ?>
    <?php if(isset($apiKey)){ ?>
    <script>
    (function(){
        // Mask the API key by default; without JS it stays visible, as before.
        var key = document.getElementById('acctKey');
        var toggle = document.getElementById('acctKeyToggle');
        var copy = document.getElementById('acctKeyCopy');
        var full = key.getAttribute('data-key');
        var masked = full.slice(0, -4).replace(/[^-]/g, '•') + full.slice(-4);
        var visible = false;
        function paint(){
            key.textContent = visible ? full : masked;
            toggle.textContent = visible ? 'Hide' : 'Show';
        }
        toggle.addEventListener('click', function(){ visible = !visible; paint(); });
        var copyTimer;
        copy.addEventListener('click', function(){
            if(navigator.clipboard){ navigator.clipboard.writeText(full); }
            copy.textContent = 'Copied';
            clearTimeout(copyTimer);
            copyTimer = setTimeout(function(){ copy.textContent = 'Copy'; }, 1600);
        });
        document.getElementById('acctKeyBtns').hidden = false;
        paint();
    })();
    </script>
    <?php } ?>
</body>
</html>
