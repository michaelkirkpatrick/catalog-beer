# Brewers

## The brewer object

| Field | Type | Notes |
|---|---|---|
| `id` | string | brewer UUID |
| `object` | string | `"brewer"` |
| `name` | string | |
| `description` | string \| null | markdown/newlines allowed |
| `short_description` | string \| null | max 160 chars |
| `url` | string \| null | brewery website |
| `cb_verified` | boolean | verified by Catalog.beer — server-controlled |
| `brewer_verified` | boolean | verified by brewery staff — server-controlled |
| `last_modified` | integer | Unix timestamp |

## POST /brewer — create

| Field | Required | Constraints |
|---|---|---|
| `name` | **yes** | |
| `description` | no | markdown supported |
| `short_description` | no | max 160 characters |
| `url` | no | brewery's website URL — **fetched live**, see below |

Returns the created brewer object (grab `id` for subsequent beer/location
creates).

### The `url` reachability check

`url` is validated in two stages, on POST, PUT **and** PATCH:

1. **Syntax.** A bare host gets `http://` prepended; then
   `FILTER_VALIDATE_URL`. Max 255 bytes.
2. **Live fetch.** A `HEAD` request from the API server — 10s timeout, up to
   10 redirects, strict TLS verification, user agent
   `api.catalog.beer/1.0`. Any transport error, or a final status outside
   200–399, is treated as an invalid URL.

On success the **post-redirect** URL is stored, upgraded to `https://` if the
https variant also answers. The host (minus `www.`) is also recorded as the
brewer's domain, which is what lets brewery staff claim the record.

On failure: `400`, with `valid_state.url = "invalid"` and a generic
`valid_msg.url` ("something seems to be wrong with your URL") that does not
distinguish bad syntax from an unreachable host. **The whole write is
rejected** — no brewer is created or updated.

Correct URLs that fail this check: sites behind bot protection / a WAF
answering `403` to a non-browser user agent, servers that reject `HEAD` but
serve `GET`, sites slower than 10s, expired or self-signed certificates,
geo-blocked hosts. A brewery in this position cannot have *any* URL stored,
and an incorrect URL already on the record cannot be replaced with the
correct one.

Handling: retry once with the exact URL a browser resolves to, then resend
the request **without `url`** so the rest of the record still lands, and tell
the user the field was left unset and why. Don't substitute a different URL
(social page, old domain) to satisfy the validator.

Dropping `url` is safe on POST and PATCH. On **PUT** it is not — an omitted
`url` is cleared to null, so retrying a PUT without it wipes whatever URL the
record already had. Retry as a PATCH instead.

## PATCH /brewer/{brewer_id} — partial update

All fields optional: `name`, `description`, `short_description` (max 160),
`url`. Only provided fields change.

**To clear an optional field, PATCH it with `null`.** An absent key means
"leave this alone"; an explicit `null` means "clear this". Clearing a lapsed
or hijacked URL is therefore one field:

```json
{"url": null}
```

Use this rather than PUT whenever you are clearing something. PUT clears by
*omission*, so clearing one field means resending every other optional field
correctly in the same body — and getting that wrong silently wipes a
description.

`name` is required and cannot be cleared; `{"name": null}` returns `400` with
`valid_msg.name` explaining the field is needed.

## PUT /brewer/{brewer_id} — full replace

`name` required. **Omitted optional fields (`description`,
`short_description`, `url`) are cleared to null.** Creates if absent → `201`.

## DELETE /brewer/{brewer_id}

→ `204 No Content`. **Cascades: deletes the brewer's beers and locations.**
Only on explicit user instruction.

## Reads

- `GET /brewer/{brewer_id}` — one brewer object.
- `GET /brewer/search?q=` — full-text over name + description. `q` required
  (max 255 chars), `count` default 25 / max 100. Returns full brewer objects
  by relevance.
- `GET /brewer` — all brewers, alphabetical; rows are `{id, name,
  last_modified}` only. `count` default 500, cursor-paginated.
- `GET /brewer/count` — `{"object": "count", "value": N}`.
- `GET /brewer/{brewer_id}/beer` — the brewery's beers. Rows: `id`, `name`,
  `style`, `style_id` (nullable), `parent`, `class` (nullable),
  `beverage_type`, `abv`, `cb_verified`, `brewer_verified`. Includes the
  `brewer` object once at the top level. **Rows are compact — no
  `description`, `ibu`, or `last_modified`.** To verify a full beer record
  (e.g. after a write), use `GET /beer/{beer_id}`.
- `GET /brewer/{brewer_id}/locations` — the brewery's locations (location
  objects with `address`, `latitude`, `longitude`; no nested brewer).
