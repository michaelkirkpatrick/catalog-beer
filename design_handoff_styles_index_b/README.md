# Handoff: Beer Styles Index — "Color Cards" (`/style`)

## Overview
A new public index page for **catalog.beer** that lists every beer style, grouped
into families ("parents"), grouped in turn by fermentation class (Ale / Lager) and
by beverage type (Beer / Cider / Mead / Perry). Each family renders as a **card**
whose signature element is a horizontal **SRM color swatch row** — the visual
device that makes the page feel like a beer reference rather than a database dump.

This replaces the current styles listing. It is the "Index B — Color Cards"
direction (an "Index A — Outline" alternative was explored and rejected).

## About the design files
The files in this bundle are **design references created in HTML/CSS/JS** — a
prototype showing intended look and behavior, **not production code to ship as-is**.
The task is to **recreate this design inside catalog.beer's existing environment**:
PHP page + Bootstrap 5.3.3 + the `catalog.css` theme, fed by the site's existing
`StyleList` data path. Reuse the codebase's patterns (see "Target environment"),
don't paste the prototype's React/vanilla scaffolding.

## Fidelity
**High-fidelity.** Colors, typography, spacing, radii, and hover states below are
final and exact. Recreate the card, the swatch row, and the class/family grouping
pixel-for-pixel using the existing theme tokens. The prototype's *data* is
illustrative (see the SRM caveat — it is the one open decision on this page).

---

## ⚠️ The one open decision: the swatch row has no data behind it

The whole point of "Color Cards" is the swatch row — the prototype's caption reads
*"each swatch row is the spread of color you'll find inside."* **The style data does
not contain color.** The API (`/style`, `/style/parent`, `/style/class`) exposes only
`id, name, parent, class, beverage_type, sort_order, catch_all, aliases` — **no SRM,
ABV, IBU, or color field.** In the prototype the swatches come from `PSRM`, a
hardcoded representative SRM per family, faked into a spread with
`mid + (i − n/2) × 1.6`. Those colors are invented.

Pick one before building; each is legitimate:

- **A — Add real SRM to the data (best).** Add `srm_low`/`srm_high` (or a single
  representative SRM) per style or per family to the source, then drive the swatch
  row from it. Only option where the caption is *true*. Requires a data change.
- **B — One representative swatch per family (honest, no data change).** Show a
  single color chip per family from a curated per-family SRM table (seed it from
  `PSRM` in `render-index.js`). Reads as a family identity color, not a false
  measurement. Update the caption to match (e.g. "sorted light to dark").
- **C — Decorative only.** Keep a subtle color accent per card (left border / top
  hairline) with no "spread" claim, and drop the swatch row entirely.

**Recommendation:** ship **B** now (curated per-family SRM, ~26 values — table
included in `render-index.js`), and move to **A** if/when per-style SRM lands in the
API. Do **not** ship the fake per-style spread with the "you'll find inside" caption.

`srm.js` (bundled) is the correct SRM→hex mapping to reuse for whichever option —
it's the Bryce/Druey 1–40 chart plus `onColor()` and `gradient()` helpers.

---

## Target environment (how to build it in this codebase)

- **Page:** create a PHP page for the `/style` route, mirroring the existing
  `beer-list.php` / `brewer-list.php` pattern (same head include, `cb-nav` navbar,
  `cb-footer`). There is currently no `style-list.php`.
- **Data:** use the existing **`StyleList`** class — do **not** re-fetch or read the
  raw JSON. It already fetches and session-caches all three lists and emits them as
  one global via `StyleList::inlineScript()`:
  ```php
  echo StyleList::inlineScript(); // -> <script>window.CB_TAX = {classes, parents, styles}</script>
  ```
  Runtime shape (this is exactly what the prototype's `window.SP_DATA` mimics):
  - `classes: [{slug, name, bev, al}]`
  - `parents: [{slug, name, cls, bev, sort, al}]`
  - `styles:  [{id, name, parent, bev, ca, al}]`   (`ca` = catch_all, `al` = aliases[])
- **Render:** the grouping/label logic in `render-index.js` (`tree()` +
  `renderIndexCards()`) is sound and can be ported directly — it reads the same
  field names. You may keep it as a small vanilla JS file loaded after
  `inlineScript()`, or port the tree-building to PHP and render server-side (better
  for SEO on a public reference page — see "SEO"). Either is fine; server-side is
  preferred for this page.
- **Theme:** `catalog.css` is already the site theme (Bootstrap variable overrides).
  All `--cb-*` tokens below already exist there. The page-specific `.ix-*` classes
  live in the prototype's `<style>` block / `styles-pages.css` — fold these into the
  page or a shared stylesheet.
- **Fonts:** Instrument Serif (display/names), Instrument Sans (body), JetBrains
  Mono (labels/counts) — already loaded site-wide by `catalog.css`.

---

## Layout

Container: `.sp-page` — `max-width: 1080px; margin: 0 auto; padding: 0 1.25rem;`
below the standard `cb-nav` navbar; standard `cb-footer` below.

1. **Header** (`.ix-head`, padding `2rem 0 1.5rem`)
   - `h1.ix-h1` — "Beer Styles". Instrument Serif, `3.4rem`, `line-height: 1`,
     color `--cb-ink`. (Drop to `2.4rem` under 560px.)
   - `p.ix-sub` — one-line description, `--cb-muted`, `max-width: 60ch`,
     `margin-top: .6rem`. **Counts must be computed from the data, not hardcoded**
     (the prototype's "188 styles / 26 families" is wrong — see Counts).

2. **One `<section class="ix-class">` per group** (`margin-bottom: 2.5rem`)
   - `h2.ix-class-h.sp-class-h` — group name + a `.sp-count` of family count,
     e.g. "Ale <span class="sp-count">N families</span>". Instrument Serif,
     `1.5rem`, `margin: 0 0 1.1rem`.
   - `.ix-card-grid` — CSS grid, `grid-template-columns: repeat(3, 1fr); gap: 1rem`.
     → 2 columns under 860px, 1 column under 560px.

3. **Family card** — `a.ix-card` (the whole card is a link to that family page):
   - `display: block; text-decoration: none; background: #fff;`
   - `border: 1px solid var(--cb-line-2); border-radius: .7rem;`
   - `padding: 1rem 1.1rem 1.1rem;`
   - `transition: border-color .12s, box-shadow .12s;`
   - Carries `style="--pc: <family color hex>"` (the card's accent color).
   - **Hover:** `border-color: var(--pc); box-shadow: 0 .4rem 1rem rgba(27,26,23,.08);`
   - **`.ix-card-top`** — `display:flex; align-items:baseline; justify-content:space-between;`
     - `.ix-card-name` — family name. Instrument Serif, `1.35rem`, `--cb-ink`.
     - `.sp-count` — number of styles in the family. JetBrains Mono, `.68rem`, `--cb-muted`.
   - **`.ix-sw-row`** — `display:flex; gap:3px; margin:.8rem 0;` (the swatch row)
     - `.ix-sw` — `flex:1; height:26px; border-radius:3px; border:1px solid rgba(27,26,23,.08);`
       `background:` the SRM hex. **See the open decision above for how many chips
       and where the colors come from.**
   - **`.ix-card-styles`** — `font-size:.82rem; color:var(--cb-muted); line-height:1.5;`
     First 3 style names joined with " · ", then " · +N more" if the family has more.
     Exclude catch-all styles from the count/preview (see catch-all handling).

---

## Grouping logic (port from `render-index.js`)

1. Bucket every non-catch-all style by its `parent` slug; sort styles A→Z within each.
2. Order families by their parent `sort` value (from `/style/parent`).
3. Group families by class: iterate `classes` in order (Ale, Lager); any family whose
   `cls` matches a class goes under it.
4. **Families with no matching class** (cider, mead, perry, specialty) currently fall
   into a single "Other Beers" bucket — **this is a bug for cider/mead/perry.** Split
   them into their own **beverage-type sections** using the family `bev` field
   ("cider", "mead", "perry"). See "Beverage types" below.

---

## Beverage types (beer is not the whole catalog)

The data spans four beverage types. Handle each:
- **Beer** — Ale and Lager class sections, full color-card treatment.
- **Cider** (family `cider`), **Mead** (`mead`), **Perry** (`perry`) — give each its own
  section after the beer classes. The SRM color device is meaningless for these
  (there is no beer-color data and cider/mead aren't measured in SRM). Use a lighter
  treatment — a simple chip list of the styles, or a card without the swatch row.
  Do **not** show fabricated SRM colors for cider/mead/perry.

---

## Catch-all styles

11 styles have `catch_all: true` (`ca` in the runtime shape), e.g. *Wild Beer,
Experimental India Pale Ale, Historical Beer, Open Category Mead, Other Belgian-Style
Ale*. The prototype silently drops all of them. Decide per the product's intent:
- They are hidden from the count and preview by default (matches prototype), **or**
- Surface them as a muted "+ catch-all" chip within their family (recommended for the
  genuinely useful ones like *Wild Beer* / *Experimental IPA*), so nothing 404s from a
  data record that exists.
Pick one and apply it consistently.

## Aliases (an easy win the prototype ignores)

Every style carries an `aliases` array (≈3.5 per style — e.g. *Hefeweizen →
Weissbier, Weizen, Hefe*; *German-Style Maerzen → Oktoberfest*). Use them for:
- **Client-side filter/search** on the index (match name **or** any alias), and
- Optionally as small secondary text where a style's common name differs from its
  formal name.
The prototype shows only formal names and no aliases.

---

## Counts — compute, never hardcode

From the current data (`styles.sample.json`, API version 2.3.0): **196 styles total —
171 beer, 12 cider, 9 mead, 4 perry — across 26 families** (23 of them beer). The
prototype header ("188 styles / 26 families") and the sibling concept's "168 · 23"
are both stale. Derive all counts at render time from the data so they never drift.

## SEO

This is a public reference page. Prefer **server-side rendering** of the family grid
in PHP (real `<a>` links to each family page, real text) over building the DOM in JS
after load, and generate real per-family / per-style URLs. Add the style/family pages
to `generate-sitemap.php` (styles are not currently in the sitemap).

---

## Design tokens (all already in `catalog.css`)

Colors:
- `--cb-paper #FAF7F2` · `--cb-paper-2 #F3EDE2` · `--cb-paper-3 #E9E2D3`
- `--cb-ink #1B1A17` · `--cb-ink-2 #39352F` · `--cb-muted #6B6660` · `--cb-muted-2 #9A948A`
- `--cb-line #E4DDCF` · `--cb-line-2 #D6CDB9`
- `--cb-amber #D49A3D` · `--cb-amber-deep #B26B1E`
- `--cb-link #9A4A2E` · `--cb-link-hover #B26B1E`
- Card surface `#fff`; card accent `--pc` = the family's SRM hex.

SRM swatch palette: see `srm.js` (`SRM.hex(v)` for v≈1–40). Family seed values: the
`PSRM` table in `render-index.js`.

Type:
- Serif `--cb-serif` = "Instrument Serif", Georgia, serif — h1 3.4rem, family name
  1.35rem, class heading 1.5rem; weight 400; `letter-spacing: -.01em`.
- Sans `--cb-sans` = "Instrument Sans", system-ui — body/sub text.
- Mono `--cb-mono` = "JetBrains Mono" — counts & labels, `.68rem`,
  `letter-spacing: .06–.14em`, often uppercase.

Radius: card `.7rem`; swatch `3px`; count pill `99px`.
Spacing: grid gap `1rem`; swatch gap `3px`; section gap `2.5rem`; card padding
`1rem 1.1rem 1.1rem`.
Shadow (card hover): `0 .4rem 1rem rgba(27,26,23,.08)`.

## Interactions
- **Card hover:** border adopts the family color (`--pc`) + soft shadow, 120ms.
- **Card click:** navigate to that family's page (`/style/{parent-slug}` or your route).
- **(Optional) filter box:** client-side, matches style name or any alias; hides cards
  with no matching style. Use the site's existing input styling (`.form-control`,
  amber focus ring already themed).
- **Responsive:** 3 → 2 (≤860px) → 1 (≤560px) columns; `h1` shrinks to 2.4rem ≤560px.

## Assets
No image assets. Wordmark + navbar/footer come from existing site chrome
(`cb-wordmark`, `cb-nav`, `cb-footer` in `catalog.css`). SRM colors are generated in
`srm.js`. Fonts load via `catalog.css`.

## Files in this bundle (design references)
- `index-b.html` — the Color-Cards page prototype (the target design).
- `render-index.js` — grouping/tree logic + `renderIndexCards()` + the `PSRM` family
  color seed table. Port this logic; note the SRM caveat.
- `srm.js` — SRM value→hex mapping and helpers. Reuse as-is.
- `styles-pages.css` — shared style-page primitives (`.sp-page`, `.sp-count`,
  `.sp-class-h`, swatch/spec kit).
- `catalog.css` — the live site theme (all `--cb-*` tokens + `cb-nav`/`cb-footer`).
- `style-data.js` — the prototype's illustrative dataset (`window.SP_DATA`), same
  shape as the live `window.CB_TAX`. **Reference only — use `StyleList`/`CB_TAX`
  in production, not this file.**
- `styles.sample.json` — a real `/style` API response (v2.3.0, 196 styles) so you can
  see the exact production data shape and verify counts. Note it has **no color/SRM**.
- `StyleList.reference.php` — copy of the existing `StyleList` class showing the data
  path, the three endpoints, and the `CB_TAX` runtime shape to build against.
