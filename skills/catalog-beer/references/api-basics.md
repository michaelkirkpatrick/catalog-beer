# API Basics

Base URL: `https://api.catalog.beer` — HTTPS required; plain HTTP fails.

Headers on every request:

- `Accept: application/json`
- `Content-Type: application/json` on POST/PUT/PATCH (bodies are JSON)

## Authentication

HTTP Basic. The API key is the username, the password is blank:

```
Authorization: Basic base64("{api_key}:")
```

With curl: `-u "$CATALOG_BEER_API_KEY:"` (note the trailing colon).

Accounts are created on the website, not the API: sign up at
https://catalog.beer/signup, verify email, key at https://catalog.beer/account.
The key is `null` until the email is verified (`GET /users/{id}/api-key`
returns `api_key: null`).

## HTTP method semantics

| Method | Semantics |
|---|---|
| GET | Retrieve resource or list |
| POST | Create a new resource |
| PUT | **Full replacement.** All required fields must be present. Omitted optional fields are **cleared to null**. Upsert: creates if missing → `201`, replaces if exists → `200` |
| PATCH | Partial update; only provided fields change. Resource must exist → `404` otherwise |
| DELETE | Removes resource → `204 No Content`, no body |

`405 Method Not Allowed` if the endpoint doesn't support the method; the
`Allow` response header lists what it does support.

## Errors

Error responses are JSON:

| Field | Type | Notes |
|---|---|---|
| `error` | boolean | |
| `error_msg` | string | optional human-readable message |
| `valid_state` | object | optional; per-field `"valid"` / `"invalid"` |
| `valid_msg` | object | optional; per-field explanation of what's wrong |

Example (invalid POST /brewer):

```json
{
  "error": true,
  "error_msg": "",
  "valid_state": {"name": "invalid", "url": "valid", "description": "valid", "short_description": "valid"},
  "valid_msg": {"name": "Please give us the name of the brewery you'd like to add.", "url": "", "description": "", "short_description": ""}
}
```

Read `valid_msg` for the failing field — it says exactly what to fix.

## Rate limiting & billing

- Each key includes **1,000 free requests/month**, plus a small grace
  buffer. The counter resets on the 1st of each month (Pacific time).
- With a payment method on file (added by the user at
  https://catalog.beer/billing), the key keeps working past the free tier at
  **$1 per 1,000 requests**, rounded up to whole blocks and invoiced monthly
  — bounded by a per-key monthly spend cap ($50 by default).
- Without a payment method, exceeding limit + buffer → `429 Too Many
  Requests` until the month resets. With one, a `429` appears only once the
  month's usage would cost more than the spend cap. The `error_msg`
  says which case applies.
- `GET /usage/my-usage` (usage object: `count`, `request_limit`,
  `request_buffer`, `resets_on`) and everything under `/billing` are never
  rate limited and don't count toward usage — always safe to check.
- Pricing details: https://catalog.beer/api-pricing

### Billing endpoints

| Endpoint | What it does |
|---|---|
| `GET /billing` | Billing status for the key: `billing_enabled`, `monthly_spend_cap_cents`, `card` (brand/last4, or `null`), plus this month's `count`, `request_limit`, `billable_requests`, `estimated_charge_cents`, `unbilled_balance_cents` |
| `POST /billing/checkout-session` | Body: `success_url` + `cancel_url` (HTTPS URLs on catalog.beer or a subdomain). Returns a Stripe-hosted `url` — give it to the user to add their card in a browser. Nothing is charged at checkout; billing enables automatically once the card is saved |
| `POST /billing/portal-session` | Body: `return_url`. Returns a Stripe portal `url` where the user manages saved cards and sees invoices |
| `PATCH /billing` | Body: `monthly_spend_cap_cents` — `0` (block all paid usage) or `100`–`100000` ($1–$1,000) |
| `DELETE /billing` | Turns billing off; the key returns to the free-tier cap. The card stays saved with Stripe |

**These endpoints spend the user's money — only call them when the user
explicitly asks.** Never start a checkout session, raise a spend cap, or
disable billing on your own initiative. If a key hits its free-tier `429`
mid-task, stop and tell the user their options — wait for the monthly reset,
or add a payment method at https://catalog.beer/billing — rather than
"fixing" it yourself.

## Pagination

Cursor-based. List responses:

```json
{"object": "list", "url": "/brewer", "has_more": true, "next_cursor": "...", "data": [...]}
```

- Pass `next_cursor` back as the `cursor` query param to get the next page.
- `next_cursor` is only present when `has_more` is `true`.
- `count` query param controls page size. Defaults: 500 for `/brewer`,
  `/beer`, `/location`; 25 (max 100) for `*/search`; 100 for
  `/location/nearby|zip|city`.
- URL-encode query values (everything except alphanumerics, `-`, `_`).

## IDs

Entity IDs (beer, brewer, location, user) are 36-character UUIDs. Style IDs
are human-readable slugs (`american-ipa`). `last_modified` fields are Unix
timestamps (integers).
