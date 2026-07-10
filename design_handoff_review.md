# Styles Pages — Design Review & Data Plan

Review of `design_handoff_styles_index_b` (Color Cards index) and
`design_handoff_style_detail_a` (Tasting Sheet detail), assessed against what
actually exists today in the three repos:

- **Frontend** (this repo) — `StyleList.class.php`, `GuidedStyleField`, `catalog.css`
- **API** (`../catalog-beer-api`) — `Style.class.php`, `Beer.class.php`
- **DB schema** (`../catalog-beer-mysql`) — `style`, `style_parent`, `style_class`, alias tables
- **Style library** (`~/Documents/Claude/Projects/Catalog.beer/style-library`) — 196 authored entries

Date: 2026-07-09 · Branch: `styles-pages`

---

## Headline findings — the handoff READMEs are stale in our favor

The handoffs frame two big blockers ("no SRM anywhere", "no detail endpoint,
everything on the Tasting Sheet is fabricated"). **Both are out of date:**

1. **`GET /style/{id}` already exists** (`Style.class.php → getStyle()`) and
   already returns `specs.abv / ibu / srm / og / fg` as `{min,max}` ranges,
   plus `parent_name`, `class`, `source`, and `aliases`. The production
   `style` table has all ten spec columns, and the v2.3.0 seed populates them
   (BA 2026 values for beer; BJCP for cider/perry/mead).

2. **The index page's "one open decision" (swatch colors) resolves to
   Option A — real SRM — cheaply.** Per-style `srm_min`/`srm_max` is already
   in the database. The only gap is that the *list* endpoint (`GET /style`)
   doesn't return it. One added column set in `listStyles()` and the swatch
   row can show each family's true spread of style colors — the caption
   *"each swatch row is the spread of color you'll find inside"* becomes
   literally true. Do not ship the `PSRM` fake-spread table.

3. **The style library already contains everything the Tasting Sheet needs —
   including history.** All 196 entries have `description`, `history` (with
   Chicago-style `history_sources` citations), `commercial_examples`, and
   `sources`; 188 have Appearance/Aroma/Flavor/Mouthfeel (the 8 missing are
   the competition catch-alls, by design). `compile.py` already emits all of
   it into `styles.json`. **No authoring work is required for launch.** The
   gap is purely pipeline + API surface: `scripts/migration/seed.py` emits
   only taxonomy + specs and drops the prose on the floor.

4. **What genuinely doesn't exist:** (a) prose/sources/commercial-examples
   storage in MySQL and in the API responses, and (b) any way to list beers
   by style — `GET /beer` is a plain paginated list; the detail handoff's
   claim that `GET /beer?style=<id>` is "a real endpoint" is **wrong**. The
   `beer` table does already have `style_id`/`parent`/`class` columns, all
   indexed, so the filter is easy to add.

Verified counts for the index page: **196 styles (171 beer / 12 cider /
9 mead / 4 perry), 26 families, 11 catch-alls** — matches the handoff's
"Counts" section; the prototype header numbers are indeed stale.
Parent slugs and style ids have **no collisions**, so a flat
`/styles/{slug}` URL space could serve both styles and families; explicit
namespacing is still recommended (below).

---

## Recommended rollout

### Phase 1 — `/styles` index (buildable now, one small API change)

**API (catalog-beer-api):**
- `Style::listStyles()`: include `srm` per style — `'srm' => $this->range($row['srm_min'], $row['srm_max'], true)` — by joining the two extra columns into the existing query. Nullable (cider/mead/perry and catch-alls stay `null`; the design already says to give those families a swatch-free treatment).

**Frontend (this repo):**
- New `style-list.php` following the `beer-list.php` page pattern; **render server-side** (SEO — this is a public reference page). Port `tree()` + `renderIndexCards()` from `render-index.js` to PHP; port `srm.js`'s SRM→hex chart to a small PHP helper (keep `srm.js` for any client-side use later).
- `StyleList.class.php`: pass `srm` through in the cached style shape (bump the session cache key or clear on deploy so stale session caches don't strand the new field).
- Swatch row = real per-style mid-SRM values for the family's styles, sorted light→dark, capped at 8 chips (the prototype's cap). Family accent `--pc` = median style mid-SRM.
- Beverage-type sections: Ale, Lager, then Cider / Mead / Perry with the lighter no-swatch treatment. Fix the prototype's "Other Beers" bucket bug per the handoff.
- Catch-alls: hide from counts/previews now (prototype behavior); revisit when detail pages exist so nothing links to a 404.
- `.htaccess`: `^styles/?$ → style-list.php` (there are currently **no** style rewrite rules).
- `Navigation.class.php`: add a "Styles" navbar item (the `navbar('Section')` highlight arg gains a `'Styles'` value).
- Fold the needed `.ix-*` / `.sp-*` rules from `styles-pages.css` into a new appended section — **`catalog.css` is append-only** per project convention, or ship as a separate `assets/css/styles-pages.css`.
- `generate-sitemap.php`: add `/styles` (and detail/family URLs in later phases).

### Phase 2 — style content in the DB + API (unblocks the Tasting Sheet)

**Schema (catalog-beer-mysql; same-change rule per CLAUDE.md):**

```sql
CREATE TABLE `style_content` (
  `style_id`    varchar(64) NOT NULL,
  `description` text NOT NULL,
  `appearance`  text NULL,
  `aroma`       text NULL,
  `flavor`      text NULL,
  `mouthfeel`   text NULL,
  `history`     text NULL,
  `notes`       text NULL,
  `commercial_examples` json NULL,     -- ["Bell's Two Hearted Ale", ...]
  `sources`     json NULL,             -- {brewers_association, bjcp, naba_2024, history_sources[]}
  PRIMARY KEY (`style_id`),
  CONSTRAINT `fk_style_content_style` FOREIGN KEY (`style_id`) REFERENCES `style` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

A separate table (rather than widening `style`) keeps the hot vocabulary
table lean — the guided-style resolver and `/style` list never touch prose.
JSON columns for `commercial_examples`/`sources` match how the library
structures them; nothing queries *into* them, they're display payload.

**Pipeline (style-library):**
- Extend `scripts/migration/seed.py` to also emit `INSERT INTO style_content`
  from `styles.json` (all fields listed above are already in the build).
  Reseed = version-bump `style_meta` + upsert, same flow as today.

**API (catalog-beer-api):**
- `Style::getStyle()`: LEFT JOIN `style_content`; add `description`,
  `appearance`, `aroma`, `flavor`, `mouthfeel`, `history`,
  `commercial_examples`, `sources` to the response. Keep `GET /style`
  lightweight (name + taxonomy + srm only).
- New filter on the beer list: `GET /beer?style_id={id}` (and, for family
  pages later, `?parent=` / `?class=`) reusing the existing
  `cursor`/`count` pagination. Return richer rows than today's list —
  `id, name, abv, brewer{id,name}` — plus `total_count`, since the design
  shows "Beers in this style · N catalogued" with brewer names and ABV.
  `beer.style_id/parent/class` are already indexed (`fk_beer_style`,
  `idx_parent`, `idx_class`). *(Alternative shape: `GET /style/{id}/beer`;
  pick whichever fits the router better — `?style_id=` on `/beer` is less
  routing work since style slugs already get special-cased in `index.php`.)*
- Update `api-docs.php` samples in the same change (house convention).

### Phase 3 — `/styles/{slug}` detail pages (the Tasting Sheet)

- New `style.php` mirroring `beer.php`/`brewer.php` single-record pages;
  `.htaccess` rule `^styles/([-0-9a-z]+)/?$ → style.php?styleID=$1`
  (slugs, not UUIDs — max 64 chars).
- Server-side render from `GET /style/{id}` + `GET /beer?style_id=`; reuse
  `render-detail.js`'s graceful-degradation logic (skip AAFM grid for
  catch-alls, skip SRM glass/bars when specs are null — cider/mead/perry).
- Run prose through the standard `Text` pipeline
  (`new Text(true, true, false)` for multi-paragraph description/history).
- Aliases: render as a subhead ("Also known as …") — the handoff flags this
  as an easy win, and it's good SEO.
- Sources block: BA/BJCP/NABA tags + history citations (with URLs where
  present) straight from `sources`.
- URL namespacing decision: `/styles` index, `/styles/{style-id}` detail,
  and family pages at `/styles/family/{parent-slug}` (explicit, even though
  there are currently no slug collisions — new styles shouldn't be able to
  collide with family URLs later).
- Add all style + family URLs to `generate-sitemap.php`; consider adding
  styles to the Algolia index (`generateSearchObject()` pattern in the API
  repo) so site search covers them.

### Phase 4 (later) — family pages `/styles/family/{slug}`

Index cards link to family pages in the design. `style_parent` already has
a curated `description` served by `GET /style/parent`, so a family page has
real content on day one: description, its styles (with SRM chips), and
`GET /beer?parent=` for beers filed at family level.

---

## Library changes ("how do we add history?")

**History is already done** — every entry has a `## History` section with
anchored citations; it was part of the v1.0 authoring standard
(see library `README.md` "History anchoring"). What the library actually needs:

1. `seed.py` emits `style_content` (the one real change).
2. Optional polish: decide whether the 8 competition catch-alls should ever
   get detail pages (they have description + history but no AAFM/specs). If
   yes, the Tasting Sheet degrades fine; if no, don't link them.
3. Keep `notes` internal or expose it — it's editorial ("why these examples")
   and reads fine publicly; recommend exposing as a small "Notes" block or
   folding into description at authoring time. Decide before Phase 2 freezes
   the column list.
4. `commercial_examples` vs. live catalog data: show both — curated classics
   ("Defining examples") from the library, live "Beers in this style" from
   the API. They answer different questions; no canonicalization needed.

## Design-spec notes for the build (deviations from the prototypes)

- Compute all counts from data at render time (handoff already mandates this).
- The index prototype's `mid + (i − n/2) × 1.6` swatch math and the whole
  `PSRM` table become dead code once real SRM flows — don't port them.
- The detail prototype defaults missing SRM to `{min:4, max:8}`
  (`render-detail.js` line 25) — **don't**: null specs must suppress the
  color device, not fake a pale beer.
- Both prototypes load Bootstrap from CDN with `integrity` — production pages
  use the site's existing head include; no new external assets. Fonts and
  all `--cb-*` tokens are already in the deployed `catalog.css`.
