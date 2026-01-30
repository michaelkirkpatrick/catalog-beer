# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Catalog.beer is a traditional PHP web application serving as the frontend for "The Internet's Beer Database." This is **not** a full-stack application—it consumes the [Catalog.beer REST API](https://github.com/michaelkirkpatrick/catalog-beer-api) for all data operations. See also the [MySQL Schema](https://github.com/michaelkirkpatrick/catalog-beer-mysql).

**Stack:** PHP 7.x+, Bootstrap 4.3.1, Apache (mod_rewrite), no build system or package manager.

## Development

**No build/test/lint commands exist.** This is a traditional PHP application with no automated testing.

**Environments:**
- Production: `catalog.beer`
- Staging: `staging.catalog.beer`

Environment is detected via subdomain in `classes/initialize.php`.

## Architecture

### Page Structure Pattern

All pages follow this structure:

```php
<?php
$guest = true;  // false for protected pages requiring login
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Page logic here

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

### Key Classes (`classes/`)

- **`initialize.php`** - Bootstrap: session start, environment detection, class autoloader, authentication gate
- **`API.class.php`** - REST API client for Catalog.beer API (handles auth, GET/POST/PUT)
- **`Navigation.class.php`** - Navbar, breadcrumbs, pagination
- **`Text.class.php`** - Text processing: optional Markdown, SmartyPants, HTMLPurifier (XSS prevention)
- **`Alert.class.php`** - Bootstrap alert component
- **`htmlHead.class.php`** - HTML `<head>` generator
- **Form classes:** `InputField`, `Textarea`, `Checkbox`, `DropDown` - Bootstrap-styled form fields

### URL Routing

Apache `.htaccess` handles clean URLs. Pattern: `/resource/UUID` → `resource.php?resourceID=UUID`

Key routes:
- `/beer`, `/beer/{uuid}`, `/beer/add/{brewerID}`
- `/brewer`, `/brewer/{uuid}`, `/brewer/add`
- `/location/{uuid}/add-address`

### Authentication

- Session-based: `$_SESSION['userID']`
- Set `$guest = false` at page start to require login
- Protected pages redirect to `/login` with return URL
- Email verification enforced for authenticated users

### External Integrations

- **Catalog.beer API** - Primary data source (production: `api.catalog.beer`, staging: `api-staging.catalog.beer`)
- **Postmark** - Transactional email
- **Google reCAPTCHA v3** - Form protection
- **Apple MapKit JS** - Map functionality (JWT tokens via `JWT.class.php`)
- **Fathom Analytics** - Privacy-focused analytics
