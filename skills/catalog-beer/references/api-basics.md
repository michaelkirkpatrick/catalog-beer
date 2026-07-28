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

## Rate limiting

- Each key: 1,000 requests/month by default, plus a small grace buffer.
- Exceeding limit + buffer → `429 Too Many Requests` on all non-usage
  endpoints. Counter resets on the 1st of each month.
- `GET /usage/my-usage` returns the usage object (`count`, `request_limit`,
  `request_buffer`, `resets_on`) and is **not** counted against the limit.
- Need a higher limit? Email michael@catalog.beer.

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
