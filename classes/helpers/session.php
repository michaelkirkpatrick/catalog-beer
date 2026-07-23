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
