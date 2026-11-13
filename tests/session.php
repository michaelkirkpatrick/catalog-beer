<?php
/*
Offline test for the sign-in helpers in classes/helpers/session.php: the rule
that ends a session opened before the account's last password change.

    php tests/session.php       # pass/fail per assertion, exit 1 on failure

No network or database. The session is a real PHP file session in a temp
directory. The bootstrap that applies the rule (classes/initialize.php) needs
a live API and stays a manual staging walk, like the rest of tests/.
NOT DEPLOYED: deploy.sh excludes tests/ — keep it that way.
*/

if(php_sapi_name() !== 'cli'){ exit('CLI only'); }

define('ROOT', dirname(__DIR__) . '/public_html');
require_once(ROOT . '/classes/helpers/session.php');

$dir = sys_get_temp_dir() . '/cb-session-test-' . getmypid();
mkdir($dir);
ini_set('session.save_path', $dir);
ini_set('session.use_cookies', '0');
ob_start();   // session functions refuse to run once output has started

$results = array();
function ok($label, $condition){ global $results; $results[] = array($label, (bool) $condition); }
$account = function($gen){ return json_decode(json_encode($gen === null ? array('id' => 'u1') : array('id' => 'u1', 'session_gen' => $gen))); };

// Signing in
signIn('u1', 0);
$firstID = session_id();
ok('signIn opens a session holding the account and its number', session_status() === PHP_SESSION_ACTIVE && $_SESSION['userID'] === 'u1' && $_SESSION['gen'] === 0);
ok('a session is current while the numbers match', sessionCurrent($account(0)));
ok('the password changes elsewhere: this session is behind', !sessionCurrent($account(1)));

// The session that makes the change takes the new number and a new id
$_SESSION['csrf_token'] = 'kept';
signIn('u1', 1);
ok('signIn again takes the new number', $_SESSION['gen'] === 1 && sessionCurrent($account(1)));
ok('and a fresh session id, keeping what else the session held', session_id() !== $firstID && $_SESSION['csrf_token'] === 'kept');
ok('a number from the API as a string still matches', sessionCurrent($account('1')));
ok('a session AHEAD of the account is not current either', !sessionCurrent($account(0)));

// Before and after the deploy
unset($_SESSION['gen']);
ok('a session from before the deploy counts as 0: current on an unchanged account', sessionCurrent($account(0)));
ok('a session from before the deploy is ended by the first password change', !sessionCurrent($account(1)));
ok('an API that does not send session_gen yet reads as 0', sessionCurrent($account(null)));
$_SESSION['gen'] = 0;
ok('a session opened against the old API survives the API deploy', sessionCurrent($account(0)));

// Ending it
endSession();
ok('endSession empties $_SESSION in this request and closes the session', $_SESSION === array() && session_status() === PHP_SESSION_NONE);
endSession();
ok('endSession with no session open is harmless', $_SESSION === array());

ob_end_clean();
array_map('unlink', glob($dir . '/*'));
rmdir($dir);

$fail = 0;
foreach($results as $r){
    echo ($r[1] ? '  PASS ' : '  FAIL ') . $r[0] . "\n";
    if(!$r[1]){ $fail++; }
}
echo "\n" . (count($results) - $fail) . " passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
