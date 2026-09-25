# Secrets Rotation Checklist

All secrets live in `common/passwords.php` (gitignored, **never deployed**). On the server it sits at the vhost root, outside the web root, as `0640 www-data:developers`; the repo-root `common/` mirrors that location so the same `dirname(ROOT)` expression resolves locally and on the server. Rotating a secret means editing the file **on each server** -- a deploy never carries it. Non-secret configuration (DB host/user/name, public keys, the Maps map ID) is in `public_html/config/config.php`, which is committed and deployed.

The one file holds both environments' values (`DB_PASSWORD_STAGING` / `DB_PASSWORD_PRODUCTION`, `API_KEY_STAGING` / `API_KEY_PRODUCTION`) and `config.php` picks by `ENVIRONMENT`, so the same file is installed on every server.

## Server layout

The site loads secrets with `require_once dirname(ROOT) . '/common/passwords.php'` from `public_html/config/config.php` (`ROOT` is `public_html` on the web and in `cron/generate-sitemap.php` alike). `deploy.sh` refuses to transfer anything until the file exists at the vhost root, and warns afterwards if a stale copy is still inside `public_html/config/`. On a 26.04 server `add-domain.sh` puts it there (and the real file replaces its generated stub -- Linode repo, `2604 Installation Instructions.md` § 9). On a 24.04 server still on the legacy layout (file at `public_html/config/passwords.php`, `0640`; was `0600` before 2026-09-22), move it up once, as root, before the first deploy of this layout:

```bash
V=/var/www/html/<vhost>
sudo mkdir -p $V/common $V/cron
sudo mv $V/public_html/config/passwords.php $V/common/passwords.php
sudo chown www-data:developers $V/common $V/common/passwords.php $V/cron
sudo chmod 775 $V/common $V/cron
sudo chmod 640 $V/common/passwords.php
```

Then re-point the crontab (`Cron-Jobs.md` / `crontab.txt` in the Linode repo): `public_html/generate-sitemap.php` becomes `cron/generate-sitemap.php`. The first deploy of the new layout removes the old `public_html/generate-sitemap.php` from the server (`--delete`), so do the crontab edit in the same sitting as the deploy or the next 04:10 run fails with "No such file".

To edit the file on a server, copy it up and install it as root so owner and mode are set in one step (a plain `scp` onto the `0640 www-data` file fails):

```bash
scp common/passwords.php michael@<server>:~/passwords.php
sudo install -o www-data -g developers -m 640 ~/passwords.php /var/www/html/<vhost>/common/passwords.php && rm ~/passwords.php
```

## Rotation cadence

| Secret | Suggested cadence | Trigger-based rotation |
|---|---|---|
| `DB_PASSWORD_STAGING` / `DB_PASSWORD_PRODUCTION` | Annually | Any DB user compromise, dev offboarding |
| `API_KEY_STAGING` / `API_KEY_PRODUCTION` (the site's master API keys) | Every 6 months | Admin offboarding; key seen in a log, chat or commit |
| `POSTMARK_SERVER_TOKEN` | Annually | Suspicious sends |
| `RECAPTCHA_SECRET_KEY` | Annually | Provider notice |
| `GOOGLE_MAPS_KEY` / `GOOGLE_PLACES_KEY` | Annually | Quota anomalies; these are browser keys, referrer-restricted to the site's hostnames |
| `ALGOLIA_SEARCH_API_KEY` | As needed | Public by design (search-only); rotate only on Algolia's advice |

`ALGOLIA_APPLICATION_ID` is a public identifier, not a secret.

Always rotate immediately if the secret was committed to git (even briefly), pasted into Slack, email, a ticket, a chat with an LLM or any third-party tool, held by someone leaving, or shows unexpected usage in the provider's dashboard.

## Rotation procedure (general shape)

1. Generate the new secret in the provider's dashboard.
2. Update `common/passwords.php` on the **staging** server (§ Server layout; the file is never deployed) → smoke-test the affected feature on staging.
3. Update it on the **production** server → verify in production.
4. Keep the local copy in step so a fresh server gets the current values.
5. Revoke the old secret in the provider's dashboard.
6. Update the rotation log below.

## Per-secret notes

- **`DB_PASSWORD_*`** — MySQL `catalogadmin` on each server (which box is in `Sites.md` in the Linode repo). `ALTER USER 'catalogadmin'@'localhost' IDENTIFIED BY '...'` as MySQL root, then the file. Staging and production passwords are independent — rotate them separately.
- **`API_KEY_*`** — the master keys the site uses to read the catalog on visitors' behalf (`classes/API.class.php`). They are `MASTER_API_KEYS` entries in the **API** repo's `common/passwords.php` and rows in `api_keys`; rotating one means adding the new key there first, switching this file, then removing the old key. See `catalog-beer-api/common/Secrets.md`.
- **`POSTMARK_SERVER_TOKEN`** — Postmark → Servers → API Tokens; staging uses the sandbox server. Trigger a contact-form send to confirm.
- **`GOOGLE_MAPS_KEY` / `GOOGLE_PLACES_KEY`** — Cloud Console → Credentials. Browser keys, restricted by HTTP referrer (`catalog.beer/*`, `staging.catalog.beer/*`), so a server move needs no change here; the *server-side* Address Validation key that does need the servers' IPs belongs to the API repo.

## Rotation log

| Secret | Last rotated | Rotated by | Notes |
|---|---|---|---|
| `DB_PASSWORD_STAGING` | 2026-09-25 | Michael | New value for `staging-2604` (`add-domain.sh`) |
| `DB_PASSWORD_PRODUCTION` | — | — | |
| `API_KEY_*` | — | — | |
| `POSTMARK_SERVER_TOKEN` | — | — | |
| `RECAPTCHA_SECRET_KEY` | — | — | |
| `GOOGLE_MAPS_KEY` / `GOOGLE_PLACES_KEY` | — | — | |
