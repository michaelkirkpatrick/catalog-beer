# Beers

## The beer object

| Field | Type | Notes |
|---|---|---|
| `id` | string | beer UUID |
| `object` | string | `"beer"` |
| `name` | string | |
| `style` | string | the brewery's own label, preserved verbatim |
| `style_id` | string \| null | canonical style slug (e.g. `american-ipa`); null when filed at family/class level |
| `parent` | string \| null | family slug (e.g. `ipa`) |
| `class` | string \| null | `ale` \| `lager` \| null |
| `beverage_type` | string | `beer` \| `cider` \| `perry` \| `mead` — derived, never settable |
| `description` | string \| null | markdown/newlines allowed |
| `abv` | float | ABV percentage |
| `ibu` | integer \| null | |
| `cb_verified` | boolean | server-controlled |
| `brewer_verified` | boolean | server-controlled |
| `last_modified` | integer | Unix timestamp |
| `brewer` | object | nested brewer object |

## POST /beer — create

| Field | Required | Constraints |
|---|---|---|
| `brewer_id` | **yes** | UUID of the brewery |
| `name` | **yes** | |
| `style` | **yes**, unless `style_id`/`parent`/`class` given | the brewery's verbatim label — send it even when you also send a tier, or the canonical name replaces it |
| `style_id` | no | canonical style slug; sets the tier, overriding label matching |
| `parent` | no | family slug (e.g. `ipa`) |
| `class` | no | `ale` or `lager` |
| `style_confidence` | no | usually omit — see "Recording how sure you are" |
| `abv` | **yes** | float |
| `ibu` | no | integer |
| `description` | no | markdown |

### Style resolution on write

- A `style` label that **matches** the vocabulary is stored verbatim
  alongside its match. Matching order: canonical style names, then class
  aliases, family aliases, style aliases. "West Coast IPA" → style
  `west-coast-ipa`; "IPA" → family `ipa`; "Lager" → class `lager`; "NEIPA",
  "New England IPA" and "Juicy IPA" → aliases for `hazy-ipa`.
- A label that **doesn't** match, with no explicit classification, is
  **`400 Bad Request`** — the write is rejected outright. Verbatim storage is
  not a fallback for unmatched labels: there is no path that persists a label
  with a null `style_id` and null `parent`/`class`.
- Explicit `style_id` / `parent` / `class` override label matching; if more
  than one is sent, **the most specific wins**. Broader tiers are derived
  automatically (send `style_id: west-coast-ipa` → `parent: ipa`,
  `class: ale`). The API never fills in a *more specific* tier than given.
- **Recovery for an unmatched label — send both.** The verbatim `style` and
  an explicit tier in the same request is accepted, and it preserves both:
  the explicit field sets the classification, the label is written exactly
  as submitted.

  ```json
  {"style": "Cali Pilsner", "style_id": "contemporary-american-pilsner"}
  ```

  Drop to `parent`/`class` when the evidence doesn't support a specific
  style. When no real style fits, prefer the nearest **catch-all style**
  (`catch_all: true`, e.g. `wild-beer` for a sour, `experimental-ipa` for an
  odd IPA, `specialty-beer` as the last resort) over a bare `parent` —
  ranking puts catch-alls after real matches, so the first `catch_all: true`
  hit for the label is usually the right one.
- **The rejection carries its own fix.** A 400 from an unmatched label (or an
  unrecognised `style_id`/`parent`/`class`) includes a `suggestions` object
  keyed by field, alongside `valid_state`/`valid_msg`:

  ```json
  "suggestions": {
    "style": {
      "styles": [
        {"style_id": "contemporary-gose", "name": "Contemporary-Style Gose",
         "parent": "sour-wild", "class": null, "catch_all": false}
      ]
    }
  }
  ```

  Ranked best-first: exact name/alias hits, then all-terms matches, then
  partial, with catch-alls below real styles at each level. **Read the whole
  array, not just `[0]`** — within a tier the order falls to how many beers
  we hold in each style, so a populous style outranks a better-fitting rare
  one. For "Cali Pilsner" the closest match,
  `contemporary-american-pilsner`, comes back last of six. Rows carry only
  what's needed to retry — call `GET /style/{id}` for specs. Treat the key as
  **optional**: it's absent when nothing matched, so fall back to
  `GET /style/search?q=` rather than assuming it's there.

  There is no `families[]` here. A label that names a family outright — by
  slug, display name, or alias — resolves instead of failing, so it never
  reaches this path. `GET /style/search` does return `families`; that's a
  different response.
- **Never send an explicit tier without `style`.** When `style` is empty the
  API fills the label with the resolved style's canonical name, silently
  replacing the brewery's wording ("Cali Pilsner" → "Contemporary
  American-Style Pilsener").
- `beverage_type` is derived from the resolved classification — never send it.

### Recording how sure you are

`style_confidence` marks a classification for editorial review. It's stored,
never returned in a beer object, and **usually you should omit it** — the API
derives it from what it can verify:

| Derived | When |
|---|---|
| `confident` | your label matched a canonical name or alias on its own |
| `override` | you named a `style_id` for a label that doesn't match it |
| `catch-all` | as above, but the style you chose is `catch_all: true` |
| `family` | you filed at `parent`/`class` rather than a specific style |

Send it yourself **only to claim less certainty than the request implies** —
you inferred the style from the beer's *name* rather than a stated style, the
brewery's page was ambiguous, or you picked the least-wrong of two plausible
families. `catch-all` or `family` says so honestly and is kept.

You cannot claim more. Whether your label matches is checked, not trusted, so
an unmatched label sent with `style_confidence: "confident"` is reduced to
`override`. That happens silently and the write still succeeds — so don't
send `confident` expecting it to stick.

This is the classification counterpart of rule 1 (*no source, no write*): the
tier you send says what you decided, this says how well you knew it.

## PATCH /beer/{beer_id} — partial update

All fields optional: `brewer_id`, `name`, `style`, `style_id`, `parent`,
`class`, `style_confidence`, `description`, `abv`, `ibu`. Sending any of
`style`/`style_id`/`parent`/`class` re-resolves and updates **all four**
classification fields together. Resending the beer's current `style`
unchanged never fails, and a re-resolve that lands on the same tier keeps the
beer's stored `style_confidence` rather than re-deriving it.

## PUT /beer/{beer_id} — full replace

Required: `brewer_id`, `name`, `abv`, and a style (as in POST). **Omitted
`description` and `ibu` are cleared to null.** Creates if absent → `201`.
An unmatched label with no classification → `400`, *except* when the label
equals the beer's current `style` — then the update succeeds and keeps the
current classification.

## DELETE /beer/{beer_id}

→ `204 No Content`. Only on explicit user instruction.

## Reads

- `GET /beer/{beer_id}` — one beer object (with nested brewer).
- `GET /beer/search?q=` — full-text over name, style, description. `q`
  required (max 255), `count` default 25 / max 100. Returns full beer
  objects (each with nested `brewer`) by relevance.
- `GET /beer` — all beers, alphabetical; rows are `{id, name,
  last_modified}`. `count` default 500, cursor-paginated.
- `GET /beer/count` — `{"object": "count", "value": N}`.
