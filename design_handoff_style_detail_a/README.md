# Export: Style Detail — "Tasting Sheet" (Detail A)

## Purpose of this export
This is the **Detail A** style-page concept ("Tasting Sheet"), exported to answer one
question: **what can we surface on a style page with today's API, and what would we
need to add?** Use the field map below as the starting point for the data-modelling
conversation — it lists every element the design shows and marks each as *available
now* or *not in the API yet*.

The design itself is high-fidelity (see "Design spec" at the end), but the headline
here is the **data gap**, not the pixels.

---

## What the API returns today

From `StyleList` (bundled as `StyleList.reference.php`) and the sample response
(`styles.sample.json`, v2.3.0), the style vocabulary is available via three endpoints:

- `GET /style` → `{ id, name, parent, beverage_type, catch_all, aliases[] }`
- `GET /style/parent` → `{ slug, name, class, beverage_type, sort_order, aliases[] }`
- `GET /style/class` → `{ slug, name, beverage_type, aliases[] }`

Plus a separate beer list: `GET /beer?style=<id>` (used for "Beers in this style").

That is the **entire** style dataset today: identity, taxonomy (class → parent →
style), beverage type, catch-all flag, and aliases. **No prose, no measurable specs,
no color.**

---

## Field map — what Detail A shows vs. what the API has

### ✅ Available now (wire these up immediately)
| On the page | Source |
|---|---|
| Style name (`t-name`) | `/style` → `name` |
| Breadcrumb: Class / Family (`t-class`, `t-parent`) | `/style/class` → `name`, `/style/parent` → `name` |
| Beverage type (beer/cider/mead/perry) | `/style` → `beverage_type` |
| "Beers in this style" list + count (`t-beers`, `t-beercount`) | `GET /beer?style=<id>` (real endpoint; prototype uses hardcoded demo beers) |
| Aliases | `/style` → `aliases[]` — **not currently rendered on Detail A**, but available and worth adding (common-name subhead / SEO / search) |

### ❌ Not in the API — needs a data decision
Everything in the body of the Tasting Sheet is currently **fabricated in the
prototype** (`window.SP_DATA.full`). None of it comes from `/style`:

| On the page | Field the design expects | Notes for the conversation |
|---|---|---|
| Lede + description prose (`t-lede`, `t-desc`) | `description` (long text) | The editorial heart of the page. Who authors/owns it? Licensing if sourced from BA/BJCP. |
| **Color · SRM** rail + glass fill (`t-srm`, `t-glass`) | `specs.srm { min, max }` | Drives the signature color device on **both** the index and detail pages. Highest-value single addition. |
| ABV bar (`t-specs`) | `specs.abv { min, max }` | Numeric range. |
| IBU bar | `specs.ibu { min, max }` | Numeric range. |
| OG bar | `specs.og { min, max }` | Numeric range. |
| FG bar | `specs.fg { min, max }` | Numeric range. |
| "In the glass" — Appearance | `appearance` (text) | Short descriptive field. |
| Aroma | `aroma` (text) | |
| Flavor | `flavor` (text) | |
| Mouthfeel | `mouthfeel` (text) | |
| Origin (`t-history`) | `history` (long text) | Editorial prose. |
| Sources / citations (`t-sources`) | `sources { brewers_association, bjcp, naba_2024, history_sources }` | Provenance for the above. Ties directly to the licensing question. |
| (present in prototype record, not yet on page) | `commercial` — example commercial beers | Would overlap with `/beer?style=`; decide which is canonical. |

### Suggested shape if these get added
The prototype's `full[id]` record is a concrete straw-man for what an expanded
`GET /style/<id>` could return — see `style-data.js` (`window.SP_DATA.full['hazy-ipa']`).
Its keys: `id, name, parent, category, family, aliases, description, appearance,
aroma, flavor, mouthfeel, specs{abv,ibu,srm,og,fg — each {min,max}}, history,
sources{...}, commercial`. Real example values are in that file.

### Conversation prompts this raises
1. **Ranges vs. points:** specs are modelled as `{min, max}`. Confirm the API should
   store ranges (BA/BJCP publish ranges).
2. **SRM first:** if only one thing gets added, `specs.srm` unlocks the color device
   across index + detail — biggest visual payoff for least data.
3. **Editorial ownership:** `description` / `history` / AAFM are authored prose.
   In-house, or licensed from a guideline body? That drives the `sources` model and
   possibly per-style copyright/attribution requirements.
4. **A detail endpoint:** none exists today. Likely `GET /style/<id>` returning the
   expanded record, while `GET /style` stays the lightweight list.
5. **Graceful degradation:** the renderer already skips missing ids/fields
   (`render-detail.js` → `set()` no-ops on absent elements; bars are omitted when a
   spec is null). So the page can ship with only the ✅ fields and grow as data lands.

---

## Design spec (high-fidelity — for when the data conversation resolves)

Layout: `.sp-page` (max-width 1080px) under the standard `cb-nav`.
- **Breadcrumb** `.sp-eyebrow` — mono, uppercase, warm-rust links: Styles / Class / Family.
- **Hero** `.da-hero` — `padding: 1.5rem 0 1.75rem; border-bottom: 2px solid var(--cb-ink);`
  - `.da-title` (`.sp-title`) — Instrument Serif, `4.2rem`, `line-height:1`, `max-width:14ch`.
  - `.da-sub` (`.sp-lede`) — italic serif lede, `max-width:34ch`, first sentence of description.
  - `.da-glass` (`.sp-glass`) — absolutely positioned top-right, `112×188px`, filled
    with a vertical SRM gradient (foam cap on top). Depends on `specs.srm`.
- **Body** `.da-body` — grid `1fr 320px`, `gap:3rem`, `align-items:start`.
  - **Main** `.sp-prose`: description prose → "In the glass" AAFM grid (`.sp-aafm`,
    mono amber keys `--cb-amber-deep`, `.sp-aafm-v` body) → "Origin" history prose →
    "Beers in this style" (`.sp-beer-row` list, hover `--cb-paper-2`) → sources.
  - **Rail** `.da-rail` — sticky, `--cb-paper-2` card, `border-radius:.75rem`,
    `padding:1.25rem`. Two blocks: **Color · SRM** (`.sp-srm-range` gradient bar +
    min/max legend) and **Vital stats** (`.sp-specs` — ABV/IBU/OG/FG range bars,
    fill uses the style's mid-SRM color via `--style-srm`).
- **Responsive** (≤860px): body collapses to one column; title `2.8rem`; glass goes static.

Tokens: identical to the rest of the site — all `--cb-*` values live in `catalog.css`.
Range-bar fill color = the style's mid-SRM hex (set as `--style-srm` at render).
SRM→hex mapping and gradient helper live in `srm.js` (reuse as-is).

Environment: recreate in the PHP + Bootstrap 5.3.3 + `catalog.css` stack (mirror
`beer.php` / `brewer.php` single-record pages). Feed it from an expanded style record
once the fields above are agreed. The prototype's `render-detail.js` shows the exact
render logic and degrades cleanly when fields are missing.

## Files in this bundle
- `detail-a.html` — the Tasting Sheet prototype (target design).
- `render-detail.js` — render logic; shows exactly which field drives each element and
  how missing fields degrade.
- `srm.js` — SRM value→hex + gradient/onColor helpers. Reuse as-is (needs `specs.srm`).
- `styles-pages.css` — shared detail primitives (`.sp-glass`, `.sp-specs`, `.sp-aafm`,
  `.sp-srm-range`, `.sp-beer-row`, `.sp-sources`).
- `catalog.css` — the live site theme (all `--cb-*` tokens + `cb-nav`/`cb-footer`).
- `style-data.js` — prototype dataset. `window.SP_DATA.full` is the **straw-man shape
  for an expanded style record** — the concrete proposal to react to. Illustrative
  values only.
- `styles.sample.json` — a real `GET /style` response (v2.3.0) showing today's actual
  fields (no color/specs/prose).
- `StyleList.reference.php` — the existing data path + the three real endpoints.
