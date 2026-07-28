---
name: catalog-beer
description: >-
  Read from and contribute to Catalog.beer, an open database of breweries,
  beers, and brewery locations, via its REST API. Use when the user wants to
  add or update a beer, brewery, or taproom on catalog.beer; search the beer
  catalog; find breweries near a location; or needs authoritative beer style
  specifications (ABV/IBU/SRM ranges from Brewers Association / BJCP
  guidelines).
license: MIT
---

# Catalog.beer API

Catalog.beer is an open database of 6,700+ breweries and 60,000+ beers with a
curated, versioned style taxonomy. The API is plain REST + JSON.

- Base URL: `https://api.catalog.beer` (HTTPS required — plain HTTP fails)
- Send `Accept: application/json` on every request
- Send `Content-Type: application/json` on POST/PUT/PATCH
- Full API docs: https://catalog.beer/api-docs

## Authentication

HTTP Basic auth. **The API key is the username; the password is blank.**

```bash
curl https://api.catalog.beer/beer/search?q=pliny \
  -u "$CATALOG_BEER_API_KEY:" \
  -H "Accept: application/json"
```

Getting a key (there is no signup API — accounts are created on the website):

1. The user creates a free account at https://catalog.beer/signup
2. They verify their email address (the key is `null` until verified)
3. The key is shown at https://catalog.beer/account

Ask the user for their key and read it from the `CATALOG_BEER_API_KEY`
environment variable. Never hardcode or commit it. Keys include 1,000
requests/month; a `429` means the monthly limit was exceeded (check
`GET /usage/my-usage` — it doesn't count against the limit).

## The contribution rules (non-negotiable)

Catalog.beer's value is that its data is *verifiable*. Contributions must
follow these rules:

1. **No source, no write.** Only submit facts you have actually verified —
   fetched from the brewery's own website, or read by the user off the can,
   label, or menu in front of them. **Never fill in ABV, IBU, style, or any
   other fact from your own knowledge or memory**, no matter how confident
   you are. If a required fact (like ABV) can't be found, ask the user —
   don't guess.
2. **Search before you create.** Always call `GET /brewer/search` and
   `GET /beer/search` before POSTing. Names vary ("Russian River Brewing
   Company" vs "Russian River") — search by the distinctive part of the name
   and check the results before concluding something is missing. If the
   entity exists, update it (PATCH) instead of creating a duplicate.
3. **Prefer PATCH for edits.** `PUT` is a full replacement: any optional
   field you omit is **cleared to null**. Only use PUT when you intend to
   replace the whole record.
4. **Never DELETE without the user explicitly asking for that specific
   deletion.** Deleting a brewer cascades to its beers and locations.
5. **When unsure about style, be less specific.** Filing a beer as family
   `ipa` is correct; guessing `west-coast-ipa` when the brewery just says
   "IPA" is wrong. See "Classifying styles" below.
6. **Read the reference before your first write to an entity type.** Before
   your first write to a brewer, beer, or location, read that entity's file
   in `references/` (plus `references/api-basics.md` once per session). The
   examples in this file show request *shape*, not the full contract — field
   limits, clearing semantics, and edge cases live in the references.

## Workflow: add a beer the user is drinking

The most common task. Order of operations:

```
1. GET /brewer/search?q={brewery name}     → does the brewery exist?
2. If not: fetch the brewery's website, then POST /brewer
3. GET /brewer/{brewer_id}/beer            → does the beer exist?
4. If not: find the beer on the brewery's site, then POST /beer
5. (Optional) POST /location + POST /address/{location_id} for taprooms
```

Step 2 — create the brewery (verify name/URL/description from their site).
`short_description` is a subtitle shown in search results and is **limited to
160 characters** — exceeding it returns a 400:

```bash
curl -X POST https://api.catalog.beer/brewer \
  -u "$CATALOG_BEER_API_KEY:" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{
    "name": "Alibi Brewing",
    "url": "https://alibi.beer",
    "short_description": "Neighborhood brewery in ..."
  }'
```

Step 4 — create the beer (`brewer_id`, `name`, `abv`, and a style are
required; `abv` is a float, `ibu` an integer):

```bash
curl -X POST https://api.catalog.beer/beer \
  -u "$CATALOG_BEER_API_KEY:" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{
    "brewer_id": "c1a5b1f0-...",
    "name": "Kölsch",
    "style": "Kölsch",
    "abv": 4.8,
    "description": "..."
  }'
```

Successful creates return the full object including its `id`. Report the new
catalog.beer URL to the user: `https://catalog.beer/beer/{id}`.

## Classifying styles

The taxonomy has three tiers — **class** (`ale`/`lager`) → **family** (26,
e.g. `ipa`, `stout`) → **style** (~200, e.g. `west-coast-ipa`). A beer may be
filed at any tier; the API derives the broader tiers automatically.

- **Default: pass the brewery's own label as `style`** (it's preserved
  verbatim and matched against canonical names + aliases — "NEIPA", "New
  England IPA", and "Juicy IPA" all resolve to the same style).
- If the label doesn't resolve, the API returns `400`. Then look it up:
  `GET /style/search?q={label}`, or list families via `GET /style/parent`.
  Pass an explicit `style_id` (style slug), `parent` (family slug), or
  `class` (`ale`/`lager`) alongside the verbatim `style` label. The most
  specific field you send wins.
- **File at the tier the evidence supports.** Brewery says "IPA" → send
  `parent: "ipa"`, not a guessed sub-style.
- `beverage_type` (beer/cider/perry/mead) is derived — never send it.

Style specs are also the API's best read feature: `GET /style/{slug}`
returns curated ABV/IBU/SRM/OG/FG ranges sourced from BA/BJCP guidelines —
use it instead of recalling specs from memory.

## Endpoint quick reference

| Task | Endpoint |
|---|---|
| Search beers / brewers / styles | `GET /beer/search?q=` · `/brewer/search?q=` · `/style/search?q=` (max `count` 100) |
| Get one | `GET /beer/{id}` · `/brewer/{id}` · `/location/{id}` · `/style/{slug}` |
| A brewery's beers / locations | `GET /brewer/{id}/beer` · `/brewer/{id}/locations` |
| Breweries near me | `GET /location/nearby?latitude=&longitude=` · `/location/zip?zip_code=` · `/location/city?city=&state=` |
| Create | `POST /brewer` · `/beer` · `/location`, then `POST /address/{location_id}` |
| Edit | `PATCH /beer/{id}` etc. (partial) · `PUT` (full replace — clears omitted fields) |
| Styles | `GET /style` (all, with `version`) · `/style/parent` (families) · `/style/class` |
| My usage | `GET /usage/my-usage` (not counted against limit) |

All entity IDs are 36-char UUIDs; style IDs are slugs. List endpoints use
cursor pagination: pass `next_cursor` back as `cursor`; `next_cursor` is only
present when `has_more` is true.

## Common mistakes

- Guessing ABV/IBU because the brewery's site doesn't list them. Don't —
  omit `ibu` (optional) and ask the user for `abv` (required).
- Writing a brewer `short_description` longer than **160 characters** →
  400. Compose it as a one-line subtitle; put anything longer in
  `description`.
- Verifying a write against a list endpoint. List and nested rows are
  **compact** — e.g. `GET /brewer/{id}/beer` rows omit `description` — so a
  missing field there is not a failed write. Verify with the single-object
  endpoint (`GET /beer/{id}`).
- Creating a duplicate because search used the full legal name. Search the
  distinctive word ("russian river", not "Russian River Brewing Company").
- Using PUT to change one field — it nulls every optional field you omitted.
- Sending `beverage_type` or verification flags (`cb_verified`,
  `brewer_verified`) — these are server-controlled and cannot be set.
- Forgetting the address is a **second request**: `POST /location` creates
  the location (needs `brewer_id` + `country_code`, ISO 3166-1 alpha-2);
  `POST /address/{location_id}` adds the street address (US only; needs
  `address2` = street, plus either `city`+`sub_code` or `zip5`).
- Giving a location a `name` that's just the city — leave `name` empty
  unless the venue has a real name of its own (e.g. "The Barrel House").
- Error responses use `error`, `error_msg`, and per-field
  `valid_state`/`valid_msg` objects — read `valid_msg` to see exactly which
  field failed and why.

## Detailed references

Read the matching file **before your first write** to each entity type — the
examples above are shape, these are the contract. Read the rest as needed:

- [references/beers.md](references/beers.md) — full beer endpoints, style resolution details
- [references/brewers.md](references/brewers.md) — full brewer endpoints
- [references/locations.md](references/locations.md) — locations, addresses, nearby search
- [references/styles.md](references/styles.md) — style taxonomy, objects, endpoints
- [references/api-basics.md](references/api-basics.md) — auth, errors, pagination, rate limits, method semantics
