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
| `style` | **yes**, unless `style_id`/`parent`/`class` given | the brewery's verbatim label |
| `style_id` | no | canonical style slug; takes precedence over `style` |
| `parent` | no | family slug (e.g. `ipa`) |
| `class` | no | `ale` or `lager` |
| `abv` | **yes** | float |
| `ibu` | no | integer |
| `description` | no | markdown |

### Style resolution on write

- The `style` label is stored verbatim AND matched against the vocabulary:
  canonical style names first, then class aliases, family aliases, style
  aliases. "West Coast IPA" → style `west-coast-ipa`; "IPA" → family `ipa`;
  "Lager" → class `lager`; "NEIPA" → alias for `new-england-ipa`.
- Explicit `style_id` / `parent` / `class` override label matching; if more
  than one is sent, **the most specific wins**. Broader tiers are derived
  automatically (send `style_id: west-coast-ipa` → `parent: ipa`,
  `class: ale`). The API never fills in a *more specific* tier than given.
- Unresolvable label with no explicit classification → **`400 Bad
  Request`**. Recovery: `GET /style/search?q={label}` to find the right
  `style_id`, or fall back to `parent`/`class` at the specificity the
  evidence supports.
- `beverage_type` is derived from the resolved classification — never send it.

## PATCH /beer/{beer_id} — partial update

All fields optional: `brewer_id`, `name`, `style`, `style_id`, `parent`,
`class`, `description`, `abv`, `ibu`. Sending any of
`style`/`style_id`/`parent`/`class` re-resolves and updates **all four**
classification fields together. Resending the beer's current `style`
unchanged never fails.

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
