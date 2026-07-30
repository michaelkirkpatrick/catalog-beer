<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Alert
$alert = new Alert();

// Which inline panel is open (?edit=cap|disable)
$openPanel = '';
if(isset($_GET['edit']) && in_array($_GET['edit'], array('cap', 'disable'), true)){
    $openPanel = $_GET['edit'];
}

// Validation State
$validState = array('spendCap'=>'');
$validMsg = array('spendCap'=>'');
$capValue = '';

// Stripe sends the user back here after checkout (?checkout=success|cancelled).
// The success signal is informational — billing_enabled flips when Stripe's
// webhook lands, which is usually immediate but can lag the redirect.
if(isset($_GET['checkout'])){
    if($_GET['checkout'] === 'success'){
        $alert->msg = 'Payment method saved. Usage past the free tier is now billed at $1 per 1,000 requests. If billing still shows as off below, give it a few seconds and refresh.';
        $alert->type = 'success';
    }elseif($_GET['checkout'] === 'cancelled'){
        $alert->msg = 'No changes made&#8212;you left Stripe checkout without saving a payment method.';
    }
}

// Process Forms
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $pageURL = 'https://' . $_SERVER['SERVER_NAME'] . '/billing';
    if(!csrf_verify()){
        // Invalid CSRF Token
        $alert->msg = 'Sorry, we were unable to verify your request. Please try again.';
    }elseif(isset($_POST['submitAddCard'])){
        // --- Add a payment method: Stripe Checkout (setup mode) ---
        $apiResponse = $api->request('POST', '/billing/checkout-session', array(
            'success_url' => $pageURL . '?checkout=success',
            'cancel_url' => $pageURL . '?checkout=cancelled'
        ));
        $sessionData = json_decode($apiResponse);
        if($api->httpcode == 201 && !empty($sessionData->url)){
            header('location: ' . $sessionData->url);
            exit();
        }
        $alert->msg = 'Sorry, we are unable to start Stripe checkout right now. Please try again later.';
    }elseif(isset($_POST['submitManageCards'])){
        // --- Manage payment methods: Stripe Customer Portal ---
        $apiResponse = $api->request('POST', '/billing/portal-session', array(
            'return_url' => $pageURL
        ));
        $sessionData = json_decode($apiResponse);
        if($api->httpcode == 201 && !empty($sessionData->url)){
            header('location: ' . $sessionData->url);
            exit();
        }
        $alert->msg = 'Sorry, we are unable to open the Stripe billing portal right now. Please try again later.';
    }elseif(isset($_POST['submitSpendCap'])){
        // --- Update Spend Cap ---
        $openPanel = 'cap';
        $capValue = trim($_POST['spendCap'] ?? '');
        if(!ctype_digit($capValue) || (intval($capValue) !== 0 && (intval($capValue) < 1 || intval($capValue) > 1000))){
            $validState['spendCap'] = 'invalid';
            $validMsg['spendCap'] = 'Enter a whole-dollar amount between $1 and $1,000, or $0 to block all usage past the free tier.';
        }else{
            $apiResponse = $api->request('PATCH', '/billing', array('monthly_spend_cap_cents'=>intval($capValue) * 100));
            $patchData = json_decode($apiResponse);
            if($api->httpcode == 200 && isset($patchData->monthly_spend_cap_cents)){
                $_SESSION['billing_flash'] = 'Your monthly spend cap has been updated.';
                header('location: /billing');
                exit();
            }elseif(isset($patchData->error_msg)){
                $validState['spendCap'] = 'invalid';
                $validMsg['spendCap'] = $patchData->error_msg;
            }else{
                $alert->msg = 'Sorry, we are unable to update your spend cap right now. Please try again later.';
            }
        }
    }elseif(isset($_POST['submitDisable'])){
        // --- Turn Off Billing ---
        $api->request('DELETE', '/billing', '');
        if($api->httpcode == 200){
            $_SESSION['billing_flash'] = 'Billing has been turned off. Your API key is back on the free tier&#8212;1,000 requests per month.';
            header('location: /billing');
            exit();
        }
        $alert->msg = 'Sorry, we are unable to turn off billing right now. Please try again later.';
    }
}

// Flash message from a successful update
if(!empty($_SESSION['billing_flash'])){
    $alert->msg = $_SESSION['billing_flash'];
    $alert->type = 'success';
    unset($_SESSION['billing_flash']);
}

// Billing status
$billingLoaded = false;
$billingResp = $api->request('GET', '/billing', '');
$billingData = json_decode($billingResp);
if($api->httpcode == 200 && isset($billingData->billing_enabled)){
    $billingLoaded = true;
    $billingEnabled = (bool)$billingData->billing_enabled;
    $capCents = intval($billingData->monthly_spend_cap_cents);
    if($capValue === ''){
        $capValue = strval(intdiv($capCents, 100));
    }
    $usageCount = intval($billingData->count);
    $usageLimit = intval($billingData->request_limit);
    $billableRequests = intval($billingData->billable_requests);
    $estimatedCents = intval($billingData->estimated_charge_cents);
    $unbilledCents = intval($billingData->unbilled_balance_cents);
    $usageMonth = date('F Y', mktime(0, 0, 0, intval($billingData->month), 1, intval($billingData->year)));

    // Card on file
    $cardLine = '';
    if(isset($billingData->card->last4)){
        $cardLine = ucfirst($billingData->card->brand) . ' &#8226;&#8226;&#8226;&#8226; ' . $billingData->card->last4 . ' <span class="ac-row__note">(expires ' . intval($billingData->card->exp_month) . '/' . intval($billingData->card->exp_year) . ')</span>';
    }
}elseif(empty($alert->msg)){
    $alert->msg = 'Sorry, we are unable to load your billing information right now. Please try again later.';
}

// HTML Head
$htmlHead = new htmlHead('Billing');
$htmlHead->addStylesheet('/assets/css/styles-pages.css');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <main class="cb-page cb-page--form ac-page">
        <div class="cbf-pagehead ac-pagehead">
            <h1 class="cbf-h1">Billing</h1>
        </div>
        <?php echo $alert->display(); ?>

        <p class="cbf-hint ac-note">Every account includes 1,000 free API requests per month. With a payment method on file, usage past the free tier is billed at <strong>$1 per 1,000 requests</strong> (rounded up), invoiced monthly. Charges under $5 roll forward to a later invoice. <a href="/api-usage">Learn more.</a></p>

        <?php if($billingLoaded){ ?>

        <span class="cb-label cb-label--rule ac-sec ac-sec--first">Payment</span>

        <?php if($billingEnabled){ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Status</span>
            <span class="ac-row__value"><span class="cb-tag ac-tag ac-tag--ok">Billing enabled</span></span>
        </div>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Card</span>
            <span class="ac-row__value"><?php echo !empty($cardLine) ? $cardLine : 'On file with Stripe'; ?></span>
        </div>
        <form method="post" class="ac-actions">
            <?php echo csrf_field(); ?>
            <button type="submit" class="cbf-btn cbf-btn--ghost" name="submitManageCards">Manage payment methods</button>
        </form>
        <p class="cbf-hint ac-note">Payment methods are stored and managed by Stripe&#8212;your card details never touch Catalog.beer&#8217;s servers.</p>
        <?php }else{ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Status</span>
            <span class="ac-row__value"><span class="cb-tag ac-tag">Free tier</span></span>
        </div>
        <p class="ac-note">Your API key stops working after 1,000 requests each month. Add a payment method to keep going&#8212;you&#8217;ll only be charged for what you use past the free tier.</p>
        <form method="post" class="ac-actions">
            <?php echo csrf_field(); ?>
            <button type="submit" class="cbf-btn" name="submitAddCard">Add a payment method</button>
        </form>
        <p class="cbf-hint ac-note">You&#8217;ll be sent to Stripe&#8217;s secure checkout&#8212;your card details never touch Catalog.beer&#8217;s servers.</p>
        <?php } ?>

        <span class="cb-label cb-label--rule ac-sec">Usage &#8212; <?php echo $usageMonth; ?></span>

        <div class="ac-row">
            <span class="cbf-label ac-row__label">Requests</span>
            <span class="ac-row__value"><?php echo number_format($usageCount); ?> <span class="ac-row__note">(<?php echo number_format($usageLimit); ?> free per month)</span></span>
        </div>
        <?php if($billingEnabled){ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Estimated charge</span>
            <span class="ac-row__value">$<?php echo number_format($estimatedCents / 100, 2); ?><?php if($billableRequests > 0){ echo ' <span class="ac-row__note">(' . number_format($billableRequests) . ' requests past the free tier)</span>'; } ?></span>
        </div>
        <?php } ?>
        <?php if($unbilledCents > 0){ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Unbilled balance</span>
            <span class="ac-row__value">$<?php echo number_format($unbilledCents / 100, 2); ?> <span class="ac-row__note">(from earlier months, invoiced once it reaches $5)</span></span>
        </div>
        <?php } ?>

        <?php if($billingEnabled){ ?>

        <span class="cb-label cb-label--rule ac-sec">Spend cap</span>

        <?php if($openPanel === 'cap'){ ?>
        <form method="post" class="ac-card">
            <?php
            echo csrf_field();
            $inputCap = new InputField();
            $inputCap->name = 'spendCap';
            $inputCap->description = 'Monthly spend cap';
            $inputCap->addBefore = '$';
            $inputCap->required = true;
            $inputCap->markRequired = false;
            $inputCap->autofocus = true;
            $inputCap->maxLength = 4;
            $inputCap->value = $capValue;
            $inputCap->validState = $validState['spendCap'];
            $inputCap->validMsg = $validMsg['spendCap'];
            $inputCap->hint = 'Whole dollars, $1&#8211;$1,000. Each $1 covers 1,000 requests past the free tier. Set $0 to block all paid usage.';
            echo $inputCap->display();
            ?>
            <div class="ac-actions">
                <button type="submit" class="cbf-btn" name="submitSpendCap">Save</button>
                <a class="cbf-btn cbf-btn--ghost" href="/billing">Cancel</a>
            </div>
        </form>
        <?php }else{ ?>
        <div class="ac-row">
            <span class="cbf-label ac-row__label">Monthly cap</span>
            <span class="ac-row__value">$<?php echo number_format($capCents / 100, ($capCents % 100) ? 2 : 0); ?> <span class="ac-row__note">(up to <?php echo number_format(intdiv($capCents, 100) * 1000); ?> requests past the free tier)</span></span>
            <a class="cb-action" href="/billing?edit=cap">Edit</a>
        </div>
        <p class="cbf-hint ac-note">A guardrail against surprise bills&#8212;once the month&#8217;s usage would cost more than the cap, further requests are declined until the month resets.</p>
        <?php } ?>

        <span class="cb-label cb-label--rule ac-sec">Turn off billing</span>

        <?php if($openPanel === 'disable'){ ?>
        <div class="ac-confirm">
            <p class="cbf-confirm">Turn off billing? Your API key immediately returns to the free tier&#8212;<?php echo number_format($usageLimit); ?> requests per month. Any usage already past the free tier this month will still be invoiced. Your card stays saved with Stripe; you can re-enable billing anytime by adding a payment method again.</p>
            <form method="post" class="ac-actions">
                <?php echo csrf_field(); ?>
                <button type="submit" class="cbf-btn cbf-btn--danger" name="submitDisable">Turn off billing</button>
                <a class="cbf-btn cbf-btn--ghost" href="/billing">Cancel</a>
            </form>
        </div>
        <?php }else{ ?>
        <div class="ac-row ac-row--bare">
            <span class="ac-row__note">Go back to the free tier. Usage already accrued this month will still be invoiced.</span>
            <a class="cb-action ac-danger" href="/billing?edit=disable">Turn off billing</a>
        </div>
        <?php } ?>

        <?php } ?>

        <?php } ?>
    </main>
    <?php echo $nav->footer(); ?>
</body>
</html>
