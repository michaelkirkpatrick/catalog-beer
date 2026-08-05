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
| `abv` | float | ABV percentage, `0`–`99.9` — **stored rounded to one decimal place** |
| `ibu` | integer \| null | `0`–`1000`. `null` means never recorded; `0` means no measurable bitterness — see "Recording IBU" |
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
| `abv` | **yes** | float `0`–`99.9` — rounded to one decimal place on write; see "Recording ABV" |
| `ibu` | no | whole number `0`–`1000` — omit when unknown, never send `0`; see "Recording IBU" |
| `description` | no | markdown |

### Recording ABV

**`abv` is stored to one decimal place.** A more precise figure is rounded on
write, so the value you read back may not be the value you sent:

| Sent | Stored |
|---|---|
| `7.25` | `7.3` |
| `11.65` | `11.7` |
| `13.46` | `13.5` |
| `14.29` | `14.3` |

Two consequences:

- **Send the brewery's exact figure anyway.** Rounding is the API's job, not
  yours — don't pre-round, and don't treat the rounded result as a failed write.
- **Don't mistake rounding for stale data.** A record holding `13.9` where the
  brewery now says `13.89%` is already correct; PATCHing it changes nothing.
  Compare a site figure against a stored one **at one decimal place**, or you
  will "fix" fields that were never wrong.

#### When the brewery publishes a bound, not a figure

Labels often give a limit rather than a number — "Less than 0.5% ABV" on
non-alcoholic beer, "Under 4%" on a light lager. `abv` is a required float and
cannot express "less than", so **record the bound itself**: `<0.5%` → `0.5`,
`Less than 4%` → `4.0`.

This is a reading of a published number, not a guess, so it doesn't breach rule
1 (*no source, no write*) — but it does overstate slightly, and it is the one
place the stored `abv` is knowingly not the beer's actual strength. Say so when
you report the write. Where a brewery publishes both a bound and a headline
figure (a "4% ABV" banner above a "Less than 4%" spec), they agree at one
decimal anyway.

Never convert a bound into a plausible-looking interior value — `<0.5%` is
`0.5`, never `0.4`. That would be inventing a fact.

### Recording IBU

**`0` and `null` are different claims, and you have to pick the right one.**

| You know | Send | Stored |
|---|---|---|
| The brewery publishes an IBU | that whole number, `0`–`1000` | the number |
| The brewery publishes "0 IBU" | `0` | `0` |
| The brewery publishes nothing | omit the field, or send `null` | `null` |

`null` says *nobody has recorded this beer's bitterness*. `0` says *this beer
has no measurable bitterness* — a substantive claim about the beer, and one
that is wrong for almost every beer style. **Never send `0` to mean "I don't
know"**, and never read a stored `0` as "unknown".

That distinction is new as of August 2026. Before then `0` was unstorable —
the API accepted it, returned `201`, and silently wrote `null` — so older
records and any client written against the old behaviour may still conflate
the two. A `0` you did not write yourself is not evidence of a zero-IBU beer.

Two more constraints:

- **Whole numbers only.** `45.7` is rejected (`400`), not truncated.
- **The ceiling is 1000**, which is above the highest independently verified
  beer (658 IBU) and admits published label claims like Mikkeller's "1000 IBU".
  Anything higher is rejected — a figure above ~120 is almost always a typo or
  a marketing number, so check the source before recording it.

To clear a recorded IBU back to unknown, PATCH it with `null` (PUT clears it by
omission). PATCHing `0` sets a real zero — it does not clear the field.

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
         "parent": "sour-wild", "class": null, "catch_all": false,
         "match": "exact", "aliases": ["Gose", "Leipzig-Style Gose"]}
      ]
    }
  }
  ```

  Ranked best-first, and every row says how it matched in `match`:
  `exact` (your label is the style's name or alias), `all_terms` (every word
  of your label is in its name or aliases), `partial` (only some words), or
  `description` (only our prose about the style). **`partial` means don't
  trust `[0]`.** Within one `match` level the order falls to how many beers
  we hold in each style, so a populous style outranks a better-fitting rare
  one — for "Cali Pilsner" every row is `partial` and the closest match,
  `contemporary-american-pilsner`, comes back last of six. Rows carry
  `aliases[]` so you can recognise the right style without a second call;
  call `GET /style/{id}` for specs. Treat the key as **optional**: it's
  absent when nothing matched, so fall back to `GET /style/search?q=` rather
  than assuming it's there.

  `families[]` and `classes[]` are present when your label *contains* a
  family or super-class term but matched no style outright — "Crisp American
  Lager" returns the `lager` class. (A label that names a family and nothing
  else resolves instead of failing, so it never reaches this path; it's the
  term buried inside a longer label that lands here.) Filing one tier up
  beats picking a style that merely shares a word with your label.

  `matched_on` appears when the whole label matched nothing and the API fell
  back to its last two words — "Crisp American Lager" is matched on "American
  Lager". The candidates describe that phrase, not what you sent, so an
  `exact` under a `matched_on` is exact for the *shorter* string.
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
`class`, `style_confidence`, `description`, `abv`, `ibu`. A PATCHed `abv` is
rounded to one decimal like any other write, so diff site figures against stored
ones at that precision before deciding this beer needs updating at all — see
"Recording ABV". `abv` cannot be `null` (`400`); `ibu` set to `null` clears it
back to unknown, while `0` records a real zero — see "Recording IBU". Sending any of
`style`/`style_id`/`parent`/`class` re-resolves and updates **all four**
classification fields together. Resending the beer's current `style`
unchanged never fails, and a re-resolve that lands on the same tier keeps the
beer's stored `style_confidence` rather than re-deriving it.

**A PATCH is all-or-nothing.** If any field in the body fails validation the
response is a `400` and *nothing* is written — the other fields in the same
request are discarded too, even though `valid_state` marks them `"valid"`.
That flag means "this field passed validation", not "this field was saved".
The most common way to hit it is an unmatched bare `style` label: send a name,
an ABV and an unresolvable style together and the name and ABV do not land
either. Fix the field `valid_state` marks `"invalid"` and resend the whole
body; `last_modified` on a `GET` confirms whether anything actually changed.

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
  objects (each with nested `brewer`) by relevance, each with a `match`
  field: `exact` / `all_terms` / `partial` (the name matched, fully or in
  part) or `description` (the name matched nothing — the hit is in the
  style or description text). For duplicate screening, only the first three
  count as name evidence.
- `GET /beer` — all beers, alphabetical; rows are `{id, name,
  last_modified}`. `count` default 500, cursor-paginated.
- `GET /beer/count` — `{"object": "count", "value": N}`.
