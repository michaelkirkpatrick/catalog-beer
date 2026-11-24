<?php
/**
 * Session lifecycle + CSRF helpers.
 *
 * Grouped because they're one concern: the CSRF token lives in $_SESSION, so
 * csrf_field() has to ensure a session exists before it can mint one. Required
 * from initialize.php; the session cookie params are configured there, before
 * any of these run.
 */

/**
 * Start the session unless one is already active.
 *
 * initialize.php starts a session automatically only when the client already
 * presents a session cookie. Pages that CREATE a new session (login, account
 * creation) must call this before writing to $_SESSION.
 */
function ensureSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_start();
}

/**
 * Open a signed-in session for an account.
 *
 * $sessionGen is the account's `session_gen` from the API (on the user object
 * and on POST /login): a number that goes up by one each time the password is
 * changed or reset. The session keeps the number it signed in with, and
 * initialize.php ends a session whose number no longer matches the account's
 * (sessionCurrent()), which is how a new password signs the account out on
 * every other device. Every page that signs someone in goes through here.
 */
function signIn(string $userID, $sessionGen): void {
    ensureSession();
    session_regenerate_id(true);
    $_SESSION['userID'] = $userID;
    $_SESSION['gen'] = (int) $sessionGen;
}

/**
 * Whether this session was opened under the account's current password.
 *
 * A session from before `gen` existed counts as 0, and so does an account
 * whose password has not changed since the column was added, so the deploy
 * itself signs nobody out. An API that predates `session_gen` reads as 0 too.
 *
 * @param object $userInfo  The decoded user object from GET /users/{id}.
 */
function sessionCurrent($userInfo): bool {
    return (int) ($userInfo->session_gen ?? 0) === (int) ($_SESSION['gen'] ?? 0);
}

/**
 * End the session in this request. session_destroy() alone leaves $_SESSION
 * populated until the request ends, and pages read it.
 */
function endSession(): void {
    $_SESSION = array();
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * The page to send someone to after they sign in, or '/' if $path isn't one.
 *
 * Only a path on this site passes: it must start with one '/', and may not
 * start '//' or '/\' (browsers read both as another host) or hold a control
 * character. Anything else is an open redirect, so it goes home instead.
 */
function safeReturnPath($path): string {
    if (!is_string($path) || $path === '' || $path[0] !== '/') {
        return '/';
    }
    if (isset($path[1]) && ($path[1] === '/' || $path[1] === '\\')) {
        return '/';
    }
    if (preg_match('/[\x00-\x1F\x7F]/', $path)) {
        return '/';
    }
    return $path;
}

/**
 * The nav's "Sign in" link: /login carrying the page it was clicked on, so
 * signing in lands back there. Pages where coming back makes no sense (home,
 * and the account pages themselves) get a bare /login.
 */
function signInHref(): string {
    $uri = safeReturnPath($_SERVER['REQUEST_URI'] ?? '/');
    $path = strtok($uri, '?');
    $skip = array('/', '/login', '/logout', '/signup', '/verify-email');
    if (in_array(rtrim($path, '/') ?: '/', $skip, true) || str_starts_with($path, '/verify-email/')) {
        return '/login';
    }
    return '/login?next=' . rawurlencode($uri);
}

/**
 * A hidden CSRF-token field for authenticated forms.
 *
 * Mints a per-session token on first use (ensuring a session exists first) and
 * returns the <input> to drop inside the <form>. Pair with csrf_verify() on POST.
 */
function csrf_field(){
    ensureSession();
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Constant-time check that the POSTed CSRF token matches the session's.
 *
 * @return bool  True only when a session is active and the tokens match.
 */
function csrf_verify(){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return false;
    }
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}
