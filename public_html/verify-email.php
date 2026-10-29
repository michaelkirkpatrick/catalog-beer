<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/*
Email verification: /verify-email/{code}

A GET only SHOWS a confirm button; the POST from that button is what verifies.
Mail gateways and link-preview features fetch every URL in an incoming message
(Safe Links, Proofpoint, Mimecast, some consumer clients), so a GET that
verified on sight was being satisfied by scanners — the account flagged
verified before, or without, the person ever opening the email — and a
single-use code could be spent before the person clicked. Scanners follow
links; they do not submit forms. Changed 2026-10-29 alongside the sign-up
abuse work; the link in the email (API: email-confirm-address.html) is unchanged.
*/

// The rewrite hands the code over with its leading slash.
$emailAuth = isset($_GET['emailAuth']) ? substr($_GET['emailAuth'], 1, 36) : '';
$state = '';          // confirm | verified | error | unavailable | required | nocode
$apiErrorMsg = '';

if($emailAuth !== ''){
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])){
        $api = new API();
        $verifyResp = $api->request('POST', '/users/verify-email/' . $emailAuth, '');
        $apiData = json_decode($verifyResp);
        if($api->unavailable()){
            // Backend unreachable — do NOT claim the email was verified.
            $state = 'unavailable';
        }elseif(isset($apiData->error)){
            // error_msg is API-authored (every one a fixed string) but lands in
            // element body like any other value, so it escapes like one.
            $state = 'error';
            $apiErrorMsg = $apiData->error_msg ?? '';
        }else{
            $state = 'verified';
            trackEvent('Email verified');
        }
    }else{
        $state = 'confirm';
    }
}elseif(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
    $state = 'required';
}else{
    $state = 'nocode';
}

// HTML Head
$htmlHead = new htmlHead('Verify Email');
$htmlHead->noindex();
echo $htmlHead->html;
?>
<body>
    <div class="cb-page cb-page--xs">
        <div class="cbf-authmark"><a class="cb-wordmark" href="/">Catalog<span class="tld">.beer</span></a></div>
        <?php if($state === 'confirm'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Confirm your email address</h1>
            <p class="cbf-lede">One more click and your Catalog.beer account is ready.</p>
        </div>
        <form method="POST" class="cbf-panel" action="/verify-email/<?php echo h($emailAuth); ?>">
            <p>Press the button to confirm this is your email address. Once you have, you&#8217;ll be able to contribute to the database and get an API key.</p>
            <div class="cbf-actions">
                <button type="submit" name="confirm" value="1" class="cbf-btn cbf-btn--wide">Confirm Email Address</button>
            </div>
        </form>
        <?php }elseif($state === 'verified'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Email verified!</h1>
            <p class="cbf-lede">Thank you for jumping through those hoops to set up an account. It helps us combat spam on our site.</p>
        </div>
        <div class="cbf-panel">
            <p>You&#8217;re ready to get started. You can start by <a href="/brewer/add">adding a brewer</a> or by adding a beer that&#8217;s not currently listed under your favorite brewer. As always, if you have questions, <a href="/contact">let us know</a>.</p>
            <a class="cbf-btn" href="/" role="button">Get Started</a>
        </div>
        <?php }elseif($state === 'error'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">We couldn&#8217;t verify that</h1>
            <p class="cbf-lede">Sorry about that, there was an error verifying your email address.</p>
        </div>
        <div class="cbf-panel">
            <p><?php echo h($apiErrorMsg); ?></p>
            <p>If you have already confirmed this address, just <a href="/login">sign in</a>. Otherwise, <a href="/contact">contact us</a> and we&#8217;ll sort it out.</p>
        </div>
        <?php }elseif($state === 'unavailable'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Temporarily unavailable</h1>
            <p class="cbf-lede">Sorry, we couldn&#8217;t verify your email right now because we&#8217;re having trouble connecting.</p>
        </div>
        <div class="cbf-panel">
            <p>Your link is still valid. Please try again in a few minutes.</p>
            <a class="cbf-btn" href="/verify-email/<?php echo h($emailAuth); ?>" role="button">Try Again</a>
        </div>
        <?php }elseif($state === 'required'){
            // Look the user up to say when their verification email went out.
            $api = new API();
            $userResp = $api->request('GET', '/users/' . $_SESSION['userID'], '');
            $userInfo = json_decode($userResp);
            $dateString = '';
            if($api->httpcode == 200 && !empty($userInfo->email_auth_sent)){
                $sent = date('l, F jS', $userInfo->email_auth_sent);
                $when = ($sent == date('l, F jS')) ? 'today' : $sent;
                $dateString = ' <strong>' . h($when . ' at ' . date('g:i A', $userInfo->email_auth_sent)) . '</strong>';
            }
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Email verification required</h1>
            <p class="cbf-lede">Before you can add to the Catalog.beer database or obtain an API key, you&#8217;ll need to verify your email address.</p>
        </div>
        <div class="cbf-panel">
            <p>This helps us reduce spam on the site. Check your email; we sent you a message<?php echo $dateString; ?> with the subject line <strong>&#8220;Confirm your Catalog.beer Account&#8221;</strong>. Open the link in that email and press the button on the page it takes you to.</p>
        </div>
        <?php }else{ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Verify your email</h1>
            <p class="cbf-lede">The link to this page is in the email we sent when you created your account.</p>
        </div>
        <div class="cbf-panel">
            <p>Look for a message with the subject line <strong>&#8220;Confirm your Catalog.beer Account&#8221;</strong> and open the link in it. Already verified? <a href="/login">Sign in</a>.</p>
        </div>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
