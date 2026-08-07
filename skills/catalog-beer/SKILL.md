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
metadata:
  version: "2.0.0"
  updated: "2026-08-07"
---

# Catalog.beer API

Catalog.beer is an open database of 6,700+ breweries and 60,000+ beers with a
curated, versioned style taxonomy. The API is plain REST + JSON.

- Base URL: `https://api.catalog.beer` (HTTPS required — plain HTTP fails)
- Send `Accept: application/json` on every request
- Send `Content-Type: application/json` on POST/PUT/PATCH
- Full API docs: https://catalog.beer/api-docs

This skill is always served current at
https://catalog.beer/skills/catalog-beer/SKILL.md. Copies installed from a zip
don't update themselves — if the `updated` date above is months old, or the API
rejects a request in a way these instructions don't explain, fetch that URL and
follow it instead of this copy.

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
environment variable. Never hardcode or commit it. Keys include 1,000 free
requests/month; past that, usage bills at $1 per 1,000 requests **only if
the user has added a payment method** at https://catalog.beer/billing —
otherwise a `429` ends the month's access. `GET /usage/my-usage` and
`GET /billing` report status without counting against the limit. See
`references/api-basics.md` → "Rate limiting & billing" — and never call the
billing endpoints (checkout, spend cap, disable) unless the user explicitly
asks; they spend the user's money.

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
160 characters** — exceeding it returns a 400. `url` is **fetched live by the
API** and rejects far more than bad syntax — see "The URL field bites" below:

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
required; `abv` is a float `0`–`99.9` **stored rounded to one decimal place**,
`ibu` an optional whole number `0`–`1000`). Send the brewery's exact figure and
let the API round it — and when a label publishes a bound instead of a number
("Less than 0.5% ABV"), record the bound (`0.5`). Omit `ibu` when the brewery
doesn't publish one: `0` means the beer has no measurable bitterness, not that
you don't know. See `references/beers.md` → "Recording ABV" and "Recording IBU":

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

- **Default: pass the brewery's own label as `style`.** It's matched against
  canonical names + aliases — "NEIPA", "New England IPA", and "Juicy IPA" all
  resolve to the same style — and stored verbatim alongside the match.
- **A label that doesn't resolve is rejected, not stored.** There is no
  "keep the label with a null `style_id`" fallback: the API returns `400`
  and writes *nothing*. Marketing names ("Cali Pilsner",
  "Margarita-inspired Gose") hit this constantly.
- **Recovery: resend the same verbatim label *plus* an explicit tier, in one
  request.** The explicit field classifies the beer; the label is stored
  exactly as you sent it, so the brewery's own words survive:

  ```json
  {"style": "Margarita-inspired Gose", "style_id": "contemporary-gose"}
  ```

  **The 400 tells you what to send.** It carries a `suggestions.style`
  object — `styles[]`, each with `style_id`, `name`, `parent`, `class`,
  `catch_all`, `aliases[]` and `match` — ranked best-first. Retry with the
  best candidate that fits; no second lookup needed.

  **Check `match` before you trust the order.** It is the difference between
  an answer and a guess:

  | `match` | What to do |
  | --- | --- |
  | `exact` | Your label *is* this style's name or alias. Take it. |
  | `all_terms` | Every word of your label is in its name or aliases. Usually right. |
  | `partial` | Only some words matched. **Never take `[0]` on faith.** Read the list; prefer `families`/`classes` or a catch-all. |
  | `description` | Matched only our prose about the style. Weakest signal we return. |

  Within a `match` level ties break on how many beers we hold in each style,
  so a populous style can outrank a better-fitting rare one (for "Cali
  Pilsner" every candidate is `partial` and the closest is last of six). An
  all-`partial` list means the API did not recognise your label — that is a
  cue to file one tier up, not to pick the first row.

  **`families[]` and `classes[]` ride along** when your label names a family
  or super-class but no style matched outright — "Crisp American Lager"
  returns the `lager` class. Send `parent`/`class` back the same way you
  would a `style_id`. **`matched_on`** appears when we could not match your
  whole label and fell back to its last two words; the candidates describe
  that shorter phrase, not what you sent.

  `GET /style/search?q={label}` and `GET /style/parent` are there if you want
  to look further afield — and dropping marketing words from the query
  ("Crisp American Lager" → "American Lager") is usually what finds it.
  Send `style_id` (style slug), `parent` (family slug), or `class`
  (`ale`/`lager`) — the most specific field you send wins. When no real
  style fits, use the nearest **catch-all style** (`catch_all: true` —
  `wild-beer`, `experimental-ipa`, `specialty-beer`, …). It keeps the beer
  in the right family and filed at style tier, which a bare `parent`
  doesn't.
- **Always send the label with the tier, never the tier alone.** With
  `style_id` and no `style`, the API substitutes the canonical style name —
  "Margarita-inspired Gose" is stored as "Contemporary-Style Gose" and the
  brewery's wording is lost.
- **File at the tier the evidence supports.** Brewery says "IPA" → send
  `parent: "ipa"`, not a guessed sub-style. Picking the tier is a mapping
  judgment, not a fact you're inventing — but the label you send must be the
  brewery's, verbatim.
- **If you had to guess, say so.** `style_confidence` flags a classification
  for review. Omit it normally — the API derives it. Send `"catch-all"` or
  `"family"` when your mapping is shakier than the request looks (style
  inferred from the beer's *name*, ambiguous brewery page). You can only
  claim *less* certainty this way, never more: an unmatched label sent as
  `"confident"` is silently reduced. See `references/beers.md`.
- `beverage_type` (beer/cider/perry/mead) is derived — never send it.

Style specs are also the API's best read feature: `GET /style/{slug}`
returns curated ABV/IBU/SRM/OG/FG ranges sourced from BA/BJCP guidelines —
use it instead of recalling specs from memory.

## The URL field bites

`url` (on brewers and locations) is not just syntax-checked — **the API
fetches it live** before accepting the write: a `HEAD` request, 10s timeout,
up to 10 redirects, strict TLS verification, sent with the user agent
`api.catalog.beer/1.0`. Anything other than a final 2xx/3xx is treated as a
bad URL.

That produces false rejections on URLs that are perfectly correct:

- **Bot protection / WAF** (Cloudflare et al.) answering `403` to a
  non-browser user agent — common for breweries on hosted platforms
- Servers that answer `405` to `HEAD` but serve `GET` fine
- Sites slower than 10s, expired/self-signed certs, geo-blocked hosts

Two consequences worth knowing before you start:

- **A refused URL fails the entire request** — `POST /brewer` with an
  unreachable `url` creates *no brewer at all*, not a brewer without a URL.
- **A wrong URL already in the catalog can't be corrected** if the correct
  one is bot-protected: the PATCH 400s and the wrong URL stays.

When a write fails with `valid_msg.url` set (the message says "something
seems to be wrong with your URL" regardless of cause — it is not evidence
the URL is wrong):

1. Retry **once** with the exact URL a browser lands on — `https://`,
   correct `www.` or bare host, no tracking params.
2. If it fails again, **resend without `url`** so the rest of the record is
   still created or updated. Never let the URL sink the write. (If the
   original was a PUT, retry as a PATCH — an omitted `url` on PUT clears the
   URL already on the record.)
3. Tell the user plainly: the URL is correct, the API's reachability check
   refused it, and the field was left unset. Don't record a substitute URL
   (a Facebook page, an old domain) just to fill the field — a wrong URL is
   worse than none.

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
| My usage / billing status | `GET /usage/my-usage` · `GET /billing` (never rate limited, not counted) |

All entity IDs are 36-char UUIDs; style IDs are slugs. List endpoints use
cursor pagination: pass `next_cursor` back as `cursor`; `next_cursor` is only
present when `has_more` is true.

## Common mistakes

- Guessing ABV/IBU because the brewery's site doesn't list them. Don't —
  omit `ibu` (optional) and ask the user for `abv` (required).
- Sending `ibu: 0` to mean "not listed". `0` is a real, storable value meaning
  *no measurable bitterness*; `null` (or omitting the field) means unknown.
  Getting this backwards writes a false fact about the beer.
- Reading an `abv` rounding as stale data. `abv` is stored to **one decimal
  place**, so a record holding `13.9` where the brewery says `13.89%` is
  already right. Compare site figures to stored ones at one decimal before
  deciding a field needs a PATCH, or a reconcile run fills up with writes that
  change nothing.
- Skipping a non-alcoholic beer because the label says "Less than 0.5% ABV"
  rather than a number. Record the published bound — `<0.5%` → `abv: 0.5`,
  "Under 4%" → `4.0` — and say in your report that the stored figure is an
  upper bound. Never pick an interior value like `0.4`; that one *is* invented.
- Writing a brewer `short_description` longer than **160 characters** →
  400. Compose it as a one-line subtitle; put anything longer in
  `description`.
- Formatting a `description` with Markdown — `**bold**`, `- bullets`,
  `[links](...)`. The API stores exactly the bytes you send and catalog.beer
  renders them as plain text, so the syntax appears literally on a public page
  and a human has to go and strip it. Write prose. **Newlines are the one thing
  that survives** — use blank lines for paragraphs.
- Treating a `valid_msg.url` 400 as "the URL is wrong" and abandoning the
  write, or swapping in a different URL. The API fetched the site and
  something answered non-2xx — usually bot protection. Retry once, then
  send the record **without** `url`.
- Assuming an unmatched `style` label is stored verbatim with a null
  `style_id`. It isn't — it's a `400` and nothing is written. Resend the
  label **plus** `style_id`/`parent`/`class` together. This applies to PATCH
  as well, where the whole patch is discarded: a `name` and an `abv` sent
  alongside an unresolvable `style` are not saved either, even though
  `valid_state` marks them `"valid"` (which means "passed validation", not
  "was written"). Fix the `"invalid"` field and resend the entire body.
- Sending `style_id` without `style`, which overwrites the brewery's label
  with the canonical style name.
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
- Giving a location a `name` that's just the city, or leaving `name` null when
  a brewer runs several venues in one city. `name` is for what the address
  doesn't already say: a venue with its own name uses it ("The Barrel House"),
  siblings in one city use the **neighborhood** ("South Park", "Bay Park"), and
  a brewer's only location in a city needs no `name`. Read the neighborhood off
  the brewery's page — never supply one from your own knowledge of the city.
- Calling billing endpoints (`POST /billing/checkout-session`,
  `PATCH /billing`, `DELETE /billing`) without the user explicitly asking.
  They spend the user's money. On a free-tier `429`, report the options —
  wait for the monthly reset, or add a payment method at
  https://catalog.beer/billing — and let the user decide.
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
