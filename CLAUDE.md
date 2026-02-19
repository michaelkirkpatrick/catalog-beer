# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Catalog.beer is a traditional PHP web application serving as the frontend for "The Internet's Beer Database." This is **not** a full-stack application—it consumes the [Catalog.beer REST API](https://github.com/michaelkirkpatrick/catalog-beer-api) for all data operations. See also the [MySQL Schema](https://github.com/michaelkirkpatrick/catalog-beer-mysql).

**Stack:** PHP 8.3, Bootstrap 5.3.3, Apache (mod_rewrite), no build system or package manager.

## Development

**No build/test/lint commands exist.** This is a traditional PHP application with no automated testing.

**Environments:**
- Production: `catalog.beer` → API at `api.catalog.beer`
- Staging: `staging.catalog.beer` → API at `api-staging.catalog.beer`

Environment is detected via subdomain in `classes/initialize.php`. Configuration is split across two files:
- **`classes/config.php`** — Non-secret configuration (DB host/user/name, public keys, identifiers). Committed and deployed. Copy `config.example.php` to create it.
- **`classes/passwords.php`** — Secrets (DB passwords, API keys, tokens). **Never committed or deployed** — lives on each server, managed manually. Copy `passwords.example.php` to create it.

## Architecture

### Page Structure Pattern

Every page follows this exact structure:

```php
<?php
$guest = true;  // false for pages requiring login
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Page logic: API calls, form processing, etc.

$htmlHead = new htmlHead('Page Title');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar('Section'); ?>
    <div class="container">
        <!-- Content -->
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
```

Key details:
- `$guest` must be set **before** the `initialize.php` include — it controls the auth gate
- `$nav` is a global `Navigation` instance created by `initialize.php`
- The `navbar('Section')` argument highlights the active nav section (use `'Brewers'` or `'Beer'`)
- HTML templates for head/navbar/footer live in `classes/resources/*.html` with `##PLACEHOLDER##` tokens

### Class Autoloading

`initialize.php` registers an autoloader: `require_once ROOT . '/classes/' . $class_name . '.class.php'`. Classes must be named `ClassName.class.php` and the class name must match the filename.

### Key Classes (`classes/`)

- **`API.class.php`** — REST API client using cURL. Constructor auto-fetches the user's API key if `$_SESSION['userID']` is set. Use: `$api->request('GET'|'POST'|'PUT', '/endpoint', $data)`. Check `$api->error` and `$api->httpcode` after calls.
- **`Database.class.php`** — MySQLi wrapper using prepared statements for local DB queries (error logging, etc). Use `$db->query($sql, $params)` with `?` placeholders and a params array. Check `$db->error` / `$db->errorMsg` after calls. SELECT queries return `mysqli_result` (use `->fetch_assoc()`, `->num_rows`); INSERT/UPDATE/DELETE return `null`. Also: `$db->getInsertId()`, `$db->getNumRows($result)`, `$db->close()`. Has built-in recursion guard for error logging.
- **`Text.class.php`** — Text processing pipeline: `new Text($markdown, $smartyPants, $htmlPurifier)`. All three params are booleans. Call `$text->get($string)` to process. HTMLPurifier always runs for XSS prevention. Common patterns:
  - `new Text(false, true, true)` — SmartyPants quotes + purify (for display names)
  - `new Text(true, true, false)` — Markdown + SmartyPants (for descriptions with `<p>` tags kept)
  - `new Text(false, false, true)` — Purify only (for IDs, numbers)
- **`Alert.class.php`** — Bootstrap alert. Set `$alert->msg` (supports Markdown), `$alert->type` (`'success'`/`'info'`/`'warning'`/`'error'`), `$alert->dismissible`. Call `$alert->display()`.
- **`LogError.class.php`** — Logs errors to the `error_log` DB table. Set `errorNumber`, `errorMsg`, `badData`, `filename`, then call `$errorLog->write()`. Has a static recursion guard to prevent infinite loops when the DB is down.
- **`Navigation.class.php`** — Navbar, breadcrumbs (`$nav->breadcrumbText` / `$nav->breadcrumbLink` arrays), pagination.
- **Form classes** (`InputField`, `Textarea`, `Checkbox`, `DropDown`) — Bootstrap-styled form components. Set properties then call `->display()`.

### URL Routing

Apache `.htaccess` handles clean URLs. Pattern: `/resource/UUID` → `resource.php?resourceID=UUID`. UUIDs are 36-char hyphenated format (`[-0-9a-f]{36}`).

### Authentication & Security

- Session-based: `$_SESSION['userID']`
- Set `$guest = false` at page top to require login
- Protected pages redirect to `/login?request=<URI>` with return URL (validated to prevent open redirects)
- Email verification enforced — unverified users are redirected to `/verify-email`
- Session cookies: `httponly`, `secure`, `samesite=Lax`
- `session_regenerate_id(true)` after login and account creation
- CSRF tokens on all authenticated forms via `csrf_field()` / `csrf_verify()`

### Form Processing Pattern

Forms use POST to the same page. The pattern:
1. Initialize default values and validation state arrays
2. Check `isset($_POST['submit'])`
3. Verify CSRF token with `csrf_verify()` — reject if invalid
4. POST to the API via `$api->request('POST', ...)`
5. On success: set a session flash variable, redirect with `header('location: ...')`, `exit()`
6. On error: populate `$validState` and `$validMsg` arrays from API response, display form with errors

Forms must include `<?php echo csrf_field(); ?>` inside the `<form>` tag. The token is generated per-session in `initialize.php`.

### Error Logging Convention

Errors are identified by error numbers prefixed with `C` (e.g., `C2`, `C5`, `C15`). Each error is logged with a filename reference to the originating file.

### External Integrations

- **Postmark** — Transactional email via `PostmarkSendEmail.class.php`
- **Google reCAPTCHA v3** — Form protection (`recaptcha.php`)
- **Google Maps JavaScript API** — Map functionality, API key in `config.php` (domain-restricted, safe for client-side)
- **Fathom Analytics** — Privacy-focused analytics (in head template)
