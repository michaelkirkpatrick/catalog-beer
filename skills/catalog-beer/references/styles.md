# Beer Styles

Catalog.beer maintains a curated, versioned style vocabulary sourced from
Brewers Association and BJCP guidelines. This is the authoritative answer to
"what's the ABV/IBU/SRM range for style X" — prefer it over recalled specs.
Read-only.

## The three tiers

| Tier | Beer field | Values | Example |
|---|---|---|---|
| Class | `class` | `ale`, `lager`, or null | "Lager" |
| Family | `parent` | 26 families | `ipa`, `stout`, `pilsner` |
| Style | `style_id` | ~200 styles | `west-coast-ipa` |

A beer files at any tier; broader tiers are derived automatically. Some
families (wheat, sour, cider, mead) have no class. `beverage_type`
(`beer`/`cider`/`perry`/`mead`) is derived from the classification.

Style IDs are **slugs**, not UUIDs.

## GET /style — list all styles

Alphabetical compact style objects: `id` (slug), `name`, `beverage_type`,
`parent`, `class`, `catch_all`, `aliases[]`, `srm` (`{min,max}` or null).
The envelope includes **`version`** (e.g. `"2.3.0"`) — refresh any cached
style list when it changes.

## GET /style/{style_id} — style detail

Everything in the compact object plus:

- `parent_name`, `source` (`BA-2026` | `BJCP-2021` | `OCB-2012` | `NABA-2024`)
- **`specs`**: `abv`, `ibu`, `srm`, `og`, `fg` — each `{min, max}` or null
- Prose: `description`, `appearance`, `aroma`, `flavor`, `mouthfeel`
  (null for catch-alls), `history`, `notes`
- `commercial_examples[]`, `sources` (with one marked `"primary": true`),
  `history_sources[]`

## GET /style/search?q= — search styles

Full-text over canonical names, aliases, and descriptions; alias matches are
first-class ("NEIPA" finds New England IPA). `q` required (max 255), `count`
default 25 / max 100.

Ranking tiers: exact name/alias match → full name match → partial name match
→ description-only. Within a tier, ordered by how many catalogued beers use
the style.

The response also carries a **`families`** array (first page only) when the
query exactly matches a family slug/name/alias — useful for resolving labels
like "IPA" that are families, not styles.

## GET /style/parent — list the 26 families

Each: `slug` (use as `parent` on a beer), `name`, `beverage_type`, `class`,
`description`, `sort_order`, `aliases[]`. Display order.

## GET /style/class — list classes

Currently `ale` and `lager`: `slug` (use as `class` on a beer), `name`,
`beverage_type`, `sort_order`, `aliases[]`.

## Resolving a brewery's label to a classification

1. Try sending the verbatim label as `style` on the beer write — the API
   matches canonical names, then class/family/style aliases.
2. On `400`: `GET /style/search?q={label}`. Exact/alias match → use its `id`
   as `style_id`.
3. No confident style match → check the `families` array or
   `GET /style/parent` for a family match → send `parent`.
4. Still nothing, but it's clearly an ale or lager → send `class`.
5. Genuinely ambiguous → ask the user. There are catch-all styles
   (`catch_all: true`, e.g. `specialty-beer`) for beers that defy the
   taxonomy.

Always keep the brewery's verbatim wording in `style` — it's preserved as
the display label regardless of classification.
