# Brewers

## The brewer object

| Field | Type | Notes |
|---|---|---|
| `id` | string | brewer UUID |
| `object` | string | `"brewer"` |
| `name` | string | |
| `description` | string \| null | plain text; may contain newlines |
| `short_description` | string \| null | max 160 chars |
| `url` | string \| null | brewery website |
| `cb_verified` | boolean | verified by Catalog.beer — server-controlled |
| `brewer_verified` | boolean | verified by brewery staff — server-controlled |
| `last_modified` | integer | Unix timestamp |

## POST /brewer — create

| Field | Required | Constraints |
|---|---|---|
| `name` | **yes** | |
| `description` | no | plain text; newlines are kept, markup renders literally |
| `short_description` | no | max 160 characters |
| `url` | no | brewery's website URL — **fetched live**, see below |

Returns the created brewer object (grab `id` for subsequent beer/location creates).

### The `url` reachability check

`url` is validated in two stages, on POST, PUT **and** PATCH:

1. **Syntax.** A bare host gets `http://` prepended; then `FILTER_VALIDATE_URL`. Max 255 bytes.
2. **Live fetch.** From the API server: `HEAD`, then `GET` if `HEAD` did not serve a page — 10s timeout, up to 10 redirects, strict TLS verification, a desktop-browser user agent. **Only a transport error (no answer at all) or a final `404`/`410` is treated as an invalid URL.** Every other answer — including a bot-protection or WAF `403`, a `429` or a `5xx` — is accepted: a site that answers is taken to be a real address that happens to be walled to bots, and link health is re-tested by a scheduled check afterwards.

When the site served a page, the **submitted** URL is stored with only provable canonicalisation adopted from where its redirects landed (when it answered but did not serve one — a `403` challenge page, say — the submitted URL is stored verbatim, with none of the below): `www.` ↔ apex host normalisation, an `http://` → `https://` upgrade (also applied when the https variant answers directly), and trailing-slash normalisation. **A redirect that leaves the registrable domain is never followed into storage** — the submitted URL is stored verbatim. This is deliberate: a lapsed domain redirecting off-site must not be silently adopted (the check-urls cron reports that case as `moved`, for a human to decide), and an age gate or cookie wall's redirect path would bake session state into the record. If a brewery has genuinely moved domains, submit the destination URL yourself. The stored host (minus `www.`) is also recorded as the brewer's domain, which is what lets brewery staff claim the record — another reason it stays anchored to what was submitted.

On failure: `400`, with `valid_state.url = "invalid"` and a generic `valid_msg.url` ("something seems to be wrong with your URL") that does not distinguish bad syntax from an unreachable host. **The whole write is rejected** — no brewer is created or updated.

Correct URLs that still fail this check: hosts slower than 10s, expired or self-signed certificates, and hosts that refuse the connection outright. Bot protection is **not** on this list — a `403` is accepted — so always attempt the write with the correct URL even when your own fetch of it is blocked; what you can reach and what the API accepts are different questions.

Handling: retry once with the exact URL a browser lands on; if it is refused again the host did not answer or answered `404`/`410`, so resend the request **without `url`** so the rest of the record still lands, and tell the user the field was left unset and why. Don't substitute a different URL (social page, old domain) to satisfy the validator.

Dropping `url` is safe on POST and PATCH. On **PUT** it is not — an omitted `url` is cleared to null, so retrying a PUT without it wipes whatever URL the record already had. Retry as a PATCH instead.

## PATCH /brewer/{brewer_id} — partial update

All fields optional: `name`, `description`, `short_description` (max 160), `url`. Only provided fields change.

**To clear an optional field, PATCH it with `null`.** An absent key means "leave this alone"; an explicit `null` means "clear this". Clearing a lapsed or hijacked URL is therefore one field:

```json
{"url": null}
```

Use this rather than PUT whenever you are clearing something. PUT clears by *omission*, so clearing one field means resending every other optional field correctly in the same body — and getting that wrong silently wipes a description.

`name` is required and cannot be cleared; `{"name": null}` returns `400` with `valid_msg.name` explaining the field is needed.

## PUT /brewer/{brewer_id} — full replace

`name` required. **Omitted optional fields (`description`, `short_description`, `url`) are cleared to null.** Creates if absent → `201`.

## DELETE /brewer/{brewer_id}

→ `204 No Content`. **Cascades: deletes the brewer's beers and locations.** Only on explicit user instruction.

## Reads

- `GET /brewer/{brewer_id}` — one brewer object.
- `GET /brewer/search?q=` — full-text over name + description. `q` required (max 255 chars), `count` default 25 / max 100. Returns full brewer objects by relevance, each with a `match` field saying why it matched: `exact` (name equals the query), `all_terms` (every query term prefix-matches the name), `partial` (a *distinctive* query term hits the name — structural words like Brewing/Brewery/Co don't count), or `description` (no name evidence — the hit is a description mention). **When screening for duplicates before creating a brewer, only `exact`/`all_terms`/`partial` rows are name evidence.** A result list that is all `description` means no brewer with a similar name exists — common with short or unusual names, where the blended index returns breweries whose descriptions merely use the words ("GOAL Brewing" once returned three unrelated breweries whose descriptions contained "goal"; they now label `description`, since their only name overlap was the word Brewing).
- `GET /brewer` — all brewers, alphabetical; rows are `{id, name, last_modified}` only. `count` default 500, cursor-paginated.
- `GET /brewer/count` — `{"object": "count", "value": N}`.
- `GET /brewer/{brewer_id}/beer` — the brewery's beers. Rows: `id`, `name`, `style`, `style_id` (nullable), `parent`, `class` (nullable), `beverage_type`, `abv`, `cb_verified`, `brewer_verified`. Includes the `brewer` object once at the top level. **Rows are compact — no `description`, `ibu`, or `last_modified`.** To verify a full beer record (e.g. after a write), use `GET /beer/{beer_id}`.
- `GET /brewer/{brewer_id}/locations` — the brewery's locations (location objects with `address`, `latitude`, `longitude`; no nested brewer).
