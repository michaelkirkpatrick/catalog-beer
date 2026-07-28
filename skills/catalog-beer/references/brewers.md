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
| `url` | no | brewery's website URL |

Returns the created brewer object (grab `id` for subsequent beer/location
creates).

## PATCH /brewer/{brewer_id} — partial update

All fields optional: `name`, `description`, `short_description` (max 160),
`url`. Only provided fields change.

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
