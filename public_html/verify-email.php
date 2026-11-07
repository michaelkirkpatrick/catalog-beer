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
            // Sign the person in: the link is often opened on a different device
            // from the one that created the account, where no session exists, and
            // the page's next steps (add a brewer, the API key) all need one. Same
            // pattern as create-account.php; the response is the user object.
            ensureSession();
            session_regenerate_id(true);
            $_SESSION['userID'] = $apiData->id;
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
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page cb-page--xs">
        <?php if($state === 'confirm'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Confirm your email address</h1>
        </div>
        <form method="POST" class="cbf-panel" action="/verify-email/<?php echo h($emailAuth); ?>">
            <div class="cbf-actions cbf-actions--top">
                <button type="submit" name="confirm" value="1" class="cbf-btn cbf-btn--wide">Confirm</button>
            </div>
            <p>Once confirmed, you&#8217;ll be able to add to the catalog and get your API key.</p>
        </form>
        <?php }elseif($state === 'verified'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Email verified</h1>
            <p class="cbf-intro">Your account is set up.</p>
        </div>
        <div class="cbf-panel cbf-choices">
            <a class="cbf-choice" href="/brewer/add">
                <span class="cbf-choice__t">Add to the catalog</span>
                <span class="cbf-choice__d">Brewers, beers, and taprooms.</span>
            </a>
            <a class="cbf-choice" href="/account">
                <span class="cbf-choice__t">Get your API key</span>
            </a>
        </div>
        <p class="cbf-authfoot">Questions? <a href="/contact">Get in touch</a></p>
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
                $dateString = ' ' . h($when . ' at ' . date('g:i A', $userInfo->email_auth_sent));
            }
        ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Verify your email to continue</h1>
            <p class="cbf-intro">Adding to the catalog and getting your API key both need a verified email address.</p>
        </div>
        <div class="cbf-panel">
            <p>We sent a message<?php echo $dateString; ?> with the subject line &#8220;Confirm your Catalog.beer Account&#8221;. Open the link in it and press the button.</p>
            <p>Wrong address? <a href="/account?edit=email">Change it on your account page</a> and we&#8217;ll send the message there instead.</p>
        </div>
        <?php }else{ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">That link is missing its code</h1>
            <p class="cbf-intro">This page only works from the link in your verification email.</p>
        </div>
        <div class="cbf-panel">
            <p>Open the email we sent when you created your account, subject line &#8220;Confirm your Catalog.beer Account&#8221;, and click the link in it. If it was copied by hand, make sure the whole URL came along.</p>
        </div>
        <p class="cbf-authfoot">Already verified? <a href="/login">Sign in</a></p>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
