<?php
/**
 * Abuse checks for the public forms — create-account.php and contact.php.
 *
 * Three layers, cheapest first, run by formGate():
 *
 *   1. Honeypot. A text field named `website`, visually hidden and aria-hidden,
 *      that no person sees and most form-filling bots complete. Tripping it is
 *      the one case the pages answer with a FAKE success, so the bot moves on.
 *   2. Signed form stamp. A hidden `stamp` field holding the time the form was
 *      rendered, HMAC-signed so it can't be minted. A POST with no valid stamp
 *      never loaded the form (a scripted request straight at the URL); one
 *      older than a day is a replay; one under CB_FORM_MIN_SECONDS was filled
 *      faster than a person fills four fields and a checkbox. Signed rather
 *      than kept in $_SESSION because these pages serve guests, and
 *      initialize.php deliberately starts no session for a guest.
 *   3. reCAPTCHA v3, verified with cURL and judged by recaptchaEvaluate():
 *      Google's `success`, then the token's `action` (it must be the one this
 *      form requested — a token farmed from a different form on the site won't
 *      do), its `hostname` (this host — a token minted elsewhere with our public
 *      site key won't do) and the score. Until Oct 2026 only `success` and the
 *      score were checked, and the sign-up form sent `action: 'contact_form'`.
 *      Google unreachable fails CLOSED, as it always has here; it is logged
 *      (C29) so an outage is visible instead of silent.
 *
 * None of this stops a person. The 2026-10-29 wave that prompted it — nine
 * accounts, seven in fourteen minutes, every one verified from a temp-mail
 * inbox and its key burned to the free limit — passed reCAPTCHA because a human
 * was typing. The checks that stop that are in the API (disposable-domain
 * refusal and per-network caps on POST /users, see the API's SignupGuard); the
 * form forwards the visitor's address as `signup_ip` so the API can count.
 *
 * Offline test: php tests/antispam.php
 */

const CB_FORM_MIN_SECONDS = 3;          // faster than this is a bot, not a person
const CB_FORM_MAX_AGE = 86400;          // older is a replay or a very stale tab
const CB_RECAPTCHA_MIN_SCORE = 0.5;

/**
 * A signed "form rendered at" value for the hidden `stamp` field.
 */
function formStamp(): string {
    $time = time();
    return $time . '.' . hash_hmac('sha256', (string)$time, formStampKey());
}

/**
 * Seconds since the stamp was issued, or null when it is missing or forged.
 */
function formStampAge(string $stamp): ?int {
    $parts = explode('.', $stamp, 2);
    if(count($parts) !== 2 || $parts[0] === '' || !ctype_digit($parts[0])){
        return null;
    }
    if(!hash_equals(hash_hmac('sha256', $parts[0], formStampKey()), $parts[1])){
        return null;
    }
    return time() - (int)$parts[0];
}

/**
 * Derived from an existing secret so nothing new needs provisioning. Rotating
 * RECAPTCHA_SECRET_KEY only invalidates forms already on screen.
 */
function formStampKey(): string {
    return hash('sha256', 'cb-form-stamp|' . RECAPTCHA_SECRET_KEY);
}

/**
 * The hidden fields every guarded form carries: the honeypot and the stamp.
 * Drop inside the <form>. $formId keeps the honeypot's id unique per page.
 */
function formGuardFields(string $formId): string {
    $id = h($formId . '-website');
    return '<div class="cb-sr-only" aria-hidden="true">'
        . '<label for="' . $id . '">Website</label>'
        . '<input type="text" name="website" id="' . $id . '" value="" tabindex="-1" autocomplete="cb-no-autofill-website" data-1p-ignore data-lpignore="true">'
        . '</div>' . "\n"
        . '<input type="hidden" name="stamp" value="' . h(formStamp()) . '">' . "\n";
}

/**
 * Judge Google's siteverify answer. Pure, so the test can feed it literals.
 * Returns '' when the token passes, else a reason:
 *   unavailable  no parseable answer (Google unreachable, garbage body)
 *   failed       success=false (expired, duplicate, malformed, wrong secret)
 *   action       token minted for another action
 *   hostname     token minted on another host
 *   score        below $minScore
 */
function recaptchaEvaluate(?array $json, string $action, string $hostname, float $minScore = CB_RECAPTCHA_MIN_SCORE): string {
    if($json === null || !array_key_exists('success', $json)){
        return 'unavailable';
    }
    if($json['success'] !== true){
        return 'failed';
    }
    if(($json['action'] ?? '') !== $action){
        return 'action';
    }
    if(strcasecmp((string)($json['hostname'] ?? ''), $hostname) !== 0){
        return 'hostname';
    }
    if(!isset($json['score']) || !is_numeric($json['score']) || (float)$json['score'] < $minScore){
        return 'score';
    }
    return '';
}

/**
 * Verify a reCAPTCHA v3 token with Google. Returns ['reason' => '' or a
 * recaptchaEvaluate() reason, 'json' => Google's answer or null].
 */
function recaptchaVerify(string $token, string $action): array {
    if($token === ''){
        return array('reason' => 'failed', 'json' => array('success' => false, 'error-codes' => array('missing-input-response')));
    }
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(array('secret' => RECAPTCHA_SECRET_KEY, 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '')),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ));
    $body = curl_exec($ch);
    unset($ch);
    $json = is_string($body) ? json_decode($body, true) : null;
    if(!is_array($json)){
        $json = null;
    }
    $host = strtolower((string)($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? ''));
    return array('reason' => recaptchaEvaluate($json, $action, $host), 'json' => $json);
}

/**
 * Run the three layers for a POSTed form. Returns '' when the submission may
 * be processed, else why not: honeypot, expired, fast, score, or captcha.
 * Logs each refusal (C30 for the first two, C21 for a low score, C29 for the
 * rest) with $filename so the error log says which form.
 */
function formGate(string $action, string $token, string $filename): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $who = $ip . ' / ' . ($_POST['email'] ?? '');

    if(!empty($_POST['website'])){
        formGateLog('C30', 'Honeypot filled', $who . ' / website: ' . mb_substr((string)$_POST['website'], 0, 120), $filename);
        return 'honeypot';
    }

    $age = formStampAge((string)($_POST['stamp'] ?? ''));
    if($age === null || $age > CB_FORM_MAX_AGE){
        formGateLog('C30', 'Form stamp ' . ($age === null ? 'missing or forged' : 'expired'), $who, $filename);
        return 'expired';
    }
    if($age < CB_FORM_MIN_SECONDS){
        formGateLog('C30', "Form filled in {$age}s", $who, $filename);
        return 'fast';
    }

    $check = recaptchaVerify($token, $action);
    if($check['reason'] === 'score'){
        formGateLog('C21', 'reCAPTCHA: Likely Bot', $check['json'], $filename);
        return 'score';
    }
    if($check['reason'] !== ''){
        formGateLog('C29', 'reCAPTCHA verification: ' . $check['reason'], array('who' => $who, 'response' => $check['json']), $filename);
        return 'captcha';
    }
    return '';
}

/**
 * What to tell the visitor for a formGate() reason other than honeypot.
 * $verb completes "we are not going to allow you to …" (e.g. 'create an account').
 * Developer-authored HTML for Alert::$msg.
 */
function formGateMessage(string $reason, string $verb): string {
    switch($reason){
        case 'expired':
            return 'This form had expired, so we couldn&#8217;t accept it. Please check your details and try again.';
        case 'fast':
            return 'That was quick! Please check your details and try again.';
        case 'score':
            return 'Sorry, Google&#8217;s reCAPTCHA algorithm thinks you are a bot. As such, we are not going to allow you to ' . h($verb) . ' using this form. We have logged this incident. Try <a href="/contact">contacting us</a> for support.';
        default:
            return 'Sorry, we couldn&#8217;t verify this form submission. Please try again.';
    }
}

function formGateLog(string $number, string $msg, $badData, string $filename): void {
    if(!class_exists('LogError')){
        return;
    }
    $errorLog = new LogError();
    $errorLog->errorNumber = $number;
    $errorLog->errorMsg = $msg;
    $errorLog->badData = $badData;
    $errorLog->filename = $filename;
    $errorLog->write();
}
