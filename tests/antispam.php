<?php
/*
Offline regression test for classes/helpers/antispam.php — the honeypot and
signed-stamp fields the guarded forms carry, and the judgement applied to
Google's reCAPTCHA siteverify answer.

    php tests/antispam.php          # pass/fail per assertion, exit 1 on failure

No network, no database, no session. recaptchaVerify() and formGate() make the
cURL call and read $_POST, so they are exercised on staging; everything they
decide WITH is pure and covered here. The stamp cases use a throwaway secret —
the key is derived from RECAPTCHA_SECRET_KEY, which this defines itself.

NOT DEPLOYED: deploy.sh excludes tests/. Keep it that way.
*/

if(php_sapi_name() !== 'cli'){ exit('CLI only'); }

define('ROOT', dirname(__DIR__) . '/public_html');
define('RECAPTCHA_SECRET_KEY', 'test-secret-not-real');

require_once(ROOT . '/classes/helpers/html.php');
require_once(ROOT . '/classes/helpers/antispam.php');

$pass = 0;
$fail = 0;
function check($label, $actual, $expect){
    global $pass, $fail;
    if($actual === $expect){
        $pass++;
        echo "  ok    $label\n";
    }else{
        $fail++;
        echo "  FAIL  $label\n        expected " . var_export($expect, true) . "\n        actual   " . var_export($actual, true) . "\n";
    }
}

echo "Form stamp\n";
$stamp = formStamp();
check('a fresh stamp is 0–1 seconds old', in_array(formStampAge($stamp), array(0, 1), true), true);
check('stamp is time.hmac', (bool)preg_match('/^\d{10}\.[0-9a-f]{64}$/', $stamp), true);
list($t, $sig) = explode('.', $stamp, 2);
check('an altered time is forged', formStampAge(($t - 100) . '.' . $sig), null);
check('an altered signature is forged', formStampAge($t . '.' . strrev($sig)), null);
check('empty is forged', formStampAge(''), null);
check('no dot is forged', formStampAge('1234567890'), null);
check('non-digit time is forged', formStampAge('12a4567890.' . $sig), null);
check('negative time is forged', formStampAge('-1.' . $sig), null);
$old = (string)(time() - 2 * 86400);
$oldStamp = $old . '.' . hash_hmac('sha256', $old, formStampKey());
check('a two-day-old stamp reports its age', formStampAge($oldStamp) >= 2 * 86400, true);
check('… which is past CB_FORM_MAX_AGE', formStampAge($oldStamp) > CB_FORM_MAX_AGE, true);
check('a stamp signed with another key is forged', formStampAge($t . '.' . hash_hmac('sha256', $t, 'other-key')), null);

echo "Guard fields\n";
$html = formGuardFields('signup');
$dom = new DOMDocument();
$dom->loadHTML('<!doctype html><html><body><form>' . $html . '</form></body></html>');
$xp = new DOMXPath($dom);
$hp = $xp->query('//input[@name="website"]')->item(0);
check('honeypot input present', $hp !== null, true);
check('honeypot is a text input', $hp ? $hp->getAttribute('type') : null, 'text');
check('honeypot starts empty', $hp ? $hp->getAttribute('value') : null, '');
check('honeypot is out of the tab order', $hp ? $hp->getAttribute('tabindex') : null, '-1');
check('honeypot id is per form', $hp ? $hp->getAttribute('id') : null, 'signup-website');
check('honeypot wrapper is hidden from assistive tech', $xp->query('//div[@aria-hidden="true"][@class="cb-sr-only"]//input[@name="website"]')->length, 1);
check('no inline style (design-system guardrail)', strpos($html, 'style='), false);
$st = $xp->query('//input[@name="stamp"]')->item(0);
check('stamp input present and hidden', $st ? $st->getAttribute('type') : null, 'hidden');
check('stamp value verifies', $st ? formStampAge($st->getAttribute('value')) : null, 0);
$html2 = formGuardFields('con"tact');
check('form id is escaped in the attribute', strpos($html2, 'con&quot;tact-website') !== false, true);

echo "reCAPTCHA judgement\n";
$good = array('success' => true, 'score' => 0.9, 'action' => 'signup', 'hostname' => 'catalog.beer', 'challenge_ts' => '2026-10-29T11:00:00Z');
check('good token passes', recaptchaEvaluate($good, 'signup', 'catalog.beer'), '');
check('hostname compare is case-insensitive', recaptchaEvaluate($good, 'signup', 'Catalog.BEER'), '');
check('score at the threshold passes', recaptchaEvaluate(array('score' => 0.5) + $good, 'signup', 'catalog.beer'), '');
check('score below the threshold', recaptchaEvaluate(array('score' => 0.3) + $good, 'signup', 'catalog.beer'), 'score');
check('score given as a string is read', recaptchaEvaluate(array('score' => '0.7') + $good, 'signup', 'catalog.beer'), '');
check('missing score fails as score', recaptchaEvaluate(array_diff_key($good, array('score' => 1)), 'signup', 'catalog.beer'), 'score');
check('token minted for another action', recaptchaEvaluate($good, 'contact_form', 'catalog.beer'), 'action');
check('token with no action', recaptchaEvaluate(array_diff_key($good, array('action' => 1)), 'signup', 'catalog.beer'), 'action');
check('token minted on another host', recaptchaEvaluate($good, 'signup', 'staging.catalog.beer'), 'hostname');
check('success=false', recaptchaEvaluate(array('success' => false, 'error-codes' => array('timeout-or-duplicate')), 'signup', 'catalog.beer'), 'failed');
check('success as the string "true" is not success', recaptchaEvaluate(array('success' => 'true') + $good, 'signup', 'catalog.beer'), 'failed');
check('no answer at all', recaptchaEvaluate(null, 'signup', 'catalog.beer'), 'unavailable');
check('an answer without success', recaptchaEvaluate(array('error' => 'nope'), 'signup', 'catalog.beer'), 'unavailable');
check('order: failed beats action beats hostname beats score', recaptchaEvaluate(array('success' => true, 'action' => 'x', 'hostname' => 'y', 'score' => 0), 'signup', 'catalog.beer'), 'action');

echo "Messages\n";
foreach(array('expired', 'fast', 'score', 'captcha', 'anything-else') as $reason){
    check("$reason has a message", formGateMessage($reason, 'create an account') !== '', true);
}
check('score message names the verb, escaped', strpos(formGateMessage('score', 'send a <b>message</b>'), 'send a &lt;b&gt;message&lt;/b&gt;') !== false, true);
check('only the score message offers the contact link', strpos(formGateMessage('fast', 'x'), '/contact'), false);

echo "\n$pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
