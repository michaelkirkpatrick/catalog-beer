<?php
// Initialize. Not a guest page: the confirmation has to come from someone
// signed in to the account. Signed out, initialize.php sends the visitor to
// /login?request=/confirm-email/{code} and login brings them back here.
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

/*
Confirming a change of email: /confirm-email/{code}

A verified account's email changes only when the new inbox confirms it (API
6.10.0). Requesting the change on /account stores it and mails this link to
the new address; this page is where it takes effect. The API key, billing and
usage are never involved.

Two rules shape the page:

- A GET only SHOWS a button; the POST from it is what confirms. Mail gateways
  fetch every link in a message, and a GET that confirmed on sight would let
  a scanner do it (the same reason verify-email.php asks for a click).
- The API refuses the code unless user_id is the account it belongs to, so
  the person clicking must be signed in to that account. Without that, an
  account could name someone else's address and, if that person clicked, take
  on their domain and the brewery staff rights that come with it.
*/

// The rewrite hands the code over with its leading slash.
$code = isset($_GET['code']) ? substr($_GET['code'], 1, 36) : '';
$state = '';          // confirm | expired | nopending | confirmed | error | unavailable | nocode
$apiErrorMsg = '';
$newEmail = '';
$pendingEmail = !empty($userInfo->pending_email) ? $userInfo->pending_email : '';
// The API refuses a code older than 24 hours; knowing that here lets a GET
// say so instead of offering a button that can only fail.
$pendingExpired = ($pendingEmail !== '' && !empty($userInfo->pending_email_sent) && $userInfo->pending_email_sent + 86400 < time());
$pendingSent = $pendingExpired ? date('l, F jS \\a\\t g:i A', $userInfo->pending_email_sent) : '';

// What confirming would end: brewery staff rights, when the domain changes.
// Read before the POST, because afterwards the privileges are gone.
$staffNames = array();
if($code !== '' && $pendingEmail !== '' && !$pendingExpired && emailDomain($pendingEmail) !== emailDomain($userInfo->email)){
    $staffNames = staffBrewerNames($api, $_SESSION['userID']);
}

if($code === ''){
    $state = 'nocode';
}elseif($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])){
    if(!csrf_verify()){
        $state = 'error';
        $apiErrorMsg = 'Sorry, we were unable to verify your request. Please open the link from your email again.';
    }else{
        $confirmResp = $api->request('POST', '/users/confirm-email/' . $code, array('user_id' => $_SESSION['userID']));
        $apiData = json_decode($confirmResp);
        if($api->unavailable()){
            // Backend unreachable: do NOT claim the email was changed.
            $state = 'unavailable';
        }elseif($api->httpcode == 200 && isset($apiData->email)){
            $state = 'confirmed';
            $newEmail = $apiData->email;
            trackEvent('Email changed');
        }else{
            // error_msg is API-authored (every one a fixed string) but lands
            // in element body like any other value, so it escapes like one.
            $state = 'error';
            $apiErrorMsg = $apiData->error_msg ?? '';
        }
    }
}elseif($pendingEmail === ''){
    // Signed in, but this account has no change waiting: the link was already
    // used, the change was cancelled or replaced, or it belongs to another account.
    $state = 'nopending';
}elseif($pendingExpired){
    $state = 'expired';
}else{
    $state = 'confirm';
}

// HTML Head
$htmlHead = new htmlHead('Confirm Email Change');
$htmlHead->noindex();
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page cb-page--xs">
        <?php if($state === 'confirm'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Confirm your new email address</h1>
            <p class="cbf-lede">One click and your account moves to the new address.</p>
        </div>
        <form method="POST" class="cbf-panel" action="/confirm-email/<?php echo h($code); ?>">
            <?php echo csrf_field(); ?>
            <p>Change the email address on your Catalog.beer account from <strong><?php echo h($userInfo->email); ?></strong> to <strong><?php echo h($pendingEmail); ?></strong>? You&#8217;ll sign in with the new address from now on.</p>
            <?php if(!empty($staffNames)){ ?>
            <div class="cbf-alert cbf-alert--warn">
                <span class="cbf-alert__i" aria-hidden="true">!</span>
                <div><?php echo staffNoticeHtml($staffNames, emailDomain($userInfo->email), true); ?></div>
            </div>
            <?php } ?>
            <div class="cbf-actions">
                <button type="submit" name="confirm" value="1" class="cbf-btn cbf-btn--wide">Confirm New Email Address</button>
            </div>
            <p class="cbf-hint">Changed your mind? <a href="/account">Cancel the change</a> from your account page.</p>
        </form>
        <?php }elseif($state === 'confirmed'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Email address changed</h1>
            <p class="cbf-lede">Your email is now <?php echo h($newEmail); ?>.</p>
        </div>
        <div class="cbf-panel">
            <?php if(!empty($staffNames)){ ?>
            <p>Your brewery staff privileges for <?php echo staffBrewerList($staffNames); ?> have ended.</p>
            <?php } ?>
            <p>Use the new address the next time you sign in. We&#8217;ve sent a note about the change to your old address.</p>
            <a class="cbf-btn" href="/account" role="button">Back to My Account</a>
        </div>
        <?php }elseif($state === 'error'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">We couldn&#8217;t confirm that</h1>
            <p class="cbf-lede">Your email address has not been changed.</p>
        </div>
        <div class="cbf-panel">
            <p><?php echo h($apiErrorMsg); ?></p>
            <p>You can check your email address or request the change again from <a href="/account">your account page</a>. If something looks wrong, <a href="/contact">contact us</a> and we&#8217;ll sort it out.</p>
        </div>
        <?php }elseif($state === 'unavailable'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Temporarily unavailable</h1>
            <p class="cbf-lede">Sorry, we couldn&#8217;t confirm your new email address right now because we&#8217;re having trouble connecting.</p>
        </div>
        <div class="cbf-panel">
            <p>Your link is still valid. Please try again in a few minutes.</p>
            <a class="cbf-btn" href="/confirm-email/<?php echo h($code); ?>" role="button">Try Again</a>
        </div>
        <?php }elseif($state === 'expired'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">This link has expired</h1>
            <p class="cbf-lede">Your email address has not been changed.</p>
        </div>
        <div class="cbf-panel">
            <p>Confirmation links work for 24 hours, and the one we sent to <strong><?php echo h($pendingEmail); ?></strong> on <?php echo h($pendingSent); ?> is past that. Your account page can send a new one, or cancel the change.</p>
            <a class="cbf-btn" href="/account" role="button">Go to My Account</a>
        </div>
        <?php }elseif($state === 'nopending'){ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Nothing to confirm</h1>
            <p class="cbf-lede">The account you&#8217;re signed in to, <?php echo h($userInfo->email); ?>, has no email change waiting.</p>
        </div>
        <div class="cbf-panel">
            <p>The link may already have been used, or the change may have been cancelled or replaced by a newer request. If you asked for the change from a different account, <a href="/logout">sign out</a> and sign in to that one, then open the link again.</p>
            <a class="cbf-btn" href="/account" role="button">Go to My Account</a>
        </div>
        <?php }else{ ?>
        <div class="cbf-pagehead">
            <h1 class="cbf-h1 cbf-h1--sm">Confirm your new email address</h1>
            <p class="cbf-lede">The link to this page is in the email we sent to your new address.</p>
        </div>
        <div class="cbf-panel">
            <p>Look for a message with the subject line <strong>&#8220;Confirm your new email address for Catalog.beer&#8221;</strong> and open the link in it. You can see or cancel a change that is waiting on <a href="/account">your account page</a>.</p>
        </div>
        <?php } ?>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
