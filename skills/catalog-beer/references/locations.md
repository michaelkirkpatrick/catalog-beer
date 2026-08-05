# Locations & Addresses

A **location** is a physical place (taproom, brewery, brewpub) belonging to a
brewer. Street addresses are stored separately and added with a **second
request** after the location is created. Addresses are US-only for now.

## The location object

| Field | Type | Notes |
|---|---|---|
| `id` | string | location UUID |
| `object` | string | `"location"` |
| `name` | string \| null | what the address can't convey — the venue's own name, or its neighborhood when the brewer has siblings in one city; null when the city already identifies it. See "Naming a location" |
| `url` | string \| null | location-specific page; should differ from the brewer's main URL |
| `country_code` | string | ISO 3166-1 alpha-2 (e.g. `US`) |
| `country_short_name` | string | server-derived from `country_code`; currently always `United States of America` |
| `latitude`, `longitude` | float \| null | geocoded server-side when an address is added |
| `cb_verified`, `brewer_verified` | boolean | server-controlled |
| `last_modified` | integer | Unix timestamp |
| `address` | object \| null | US address object (below) |
| `brewer` | object | nested brewer object |

## POST /location — create

| Field | Required | Constraints |
|---|---|---|
| `brewer_id` | **yes** | |
| `country_code` | **yes** | ISO 3166-1 alpha-2 |
| `name` | no | the venue's own name, or its neighborhood — see "Naming a location"; never the bare city |
| `url` | no | location-specific URL — subject to the same **live reachability check** as the brewer's `url` (see [brewers.md](brewers.md)); an unreachable URL 400s the whole write, so retry once then send without `url` |

## Naming a location

`name` earns its place only by saying something the address does not. Work down
this list and stop at the first match:

1. **The venue has a name of its own** — "The Barrel House", "The Harland
   Clubhouse". Use it.
2. **The brewer has more than one location in the same city** — use the
   **neighborhood** or district: "South Park", "Bay Park", "Scripps Ranch".
   This is what tells six San Diego taprooms apart in a list where every
   address already reads "San Diego".
3. **Otherwise** — leave `name` null. The address already identifies a brewer's
   only venue in a city; repeating the city name adds nothing.

Never the bare city, and never a neighborhood you *recalled* rather than read —
that is rule 1 (*no source, no write*) applied to a name, and city geography is
exactly the kind of fact that feels safe to supply from memory. The brewery's
own wording is the source: prefer the short form it uses in a footer or
locations list ("Scripps Ranch") over a longer page heading ("Scripps Ranch
Tasting Room"). A development or center the brewery treats as the venue's
identity ("One Paseo") counts under 1.

A location already in the catalog with a null or city-only `name` is corrected
with `PATCH /location/{location_id}` — see below.

## PATCH /location/{location_id}

All optional: `brewer_id`, `name`, `country_code`, `url`.

**Send `null` to clear a nullable field** — `name` and `url`. An absent key
means "leave this alone". Prefer this over PUT for clearing, since PUT clears
by omission and will take the other optional fields with it.

`brewer_id` and `country_code` are required and cannot be cleared; an explicit
`null` on either returns `400`.

## PUT /location/{location_id}

`brewer_id` + `country_code` required; omitted `name`/`url` cleared to null.

## DELETE /location/{location_id}

→ `204 No Content`. Only on explicit user instruction.

## POST /address/{location_id} — add the street address

Validated and geocoded server-side (returns the full location object with
`address`, `latitude`, `longitude` populated). US addresses only.

| Field | Required | Constraints |
|---|---|---|
| `address2` | **yes** | the street address (yes, `address2` — `address1` is the suite/unit) |
| `address1` | no | suite/unit, e.g. "Suite 101" |
| `city` | either/or | provide (`city` AND `sub_code`) OR `zip5` |
| `sub_code` | either/or | ISO 3166-2 subdivision, e.g. `US-CA` |
| `zip5` | either/or | 5-digit ZIP, as a **string** — keep the leading zero (`"01085"`) |
| `zip4` | no | ZIP+4, as a string |
| `telephone` | no | 10-digit; formatting accepted but stripped (returned as integer) |

`PUT /address/{location_id}` replaces the address entirely: `address1` and
`telephone` *will* clear if omitted, while `city`, `sub_code` and `zip4`
re-derive from the street address and ZIP whether you send them or not.

`PATCH /address/{location_id}` updates only the fields you send — they are
merged with the stored address and the whole thing re-validates as one. Send
`null` to clear `telephone`; `address1` and `zip4` re-derive from the
validated address rather than clearing directly. One asymmetry to know:
`city`+`sub_code` and `zip5` are two spellings of the same locality, so when
you patch one group the stored other group is dropped and re-derived rather
than merged — patching a new ZIP with an old stored city would otherwise hand
the validator a contradiction. Patch whichever group you trust and let the
other come back derived.

## The US address object (in responses)

`address1` (nullable), `address2`, `city`, `sub_code` (e.g. `US-CA`),
`state_short` (`CA`), `state_long` (`California`), `zip5` (**string**),
`zip4` (string, nullable), `telephone` (integer, nullable — no country
code).

**ZIP codes are strings, not numbers.** They are fixed-width identifiers whose
leading zero is significant — `00501`–`09999` covers New England, New Jersey,
Puerto Rico and the US Virgin Islands — and nothing ever does arithmetic on
one. Send `"01085"`, not `1085`; a 4-digit value is rejected with
`valid_state.zip5: "invalid"`. Don't parse the response value as an integer and
write it back, or you will strip the zero and the next write will fail.

**The whole address is standardised — `address1` included.** Every write runs
through Google Address Validation with USPS CASS. The street stores with its
words spelled out (`Woodinville Redmond Rd NE`, even where USPS abbreviates);
the city stores as USPS's mailing city — an unincorporated community like
Paoli, WI stores as its post-office city, Belleville — falling back to
Google's locality where USPS's 13-character field truncates the name; and the
suite/unit is re-derived from the CASS secondary line, not passed through.
Casing is normalised with directionals and unit letters intact (`Rd NE`,
`Ste 105B`). Send whatever form you have and treat what comes back as
canonical — a re-submit of your original spelling will standardise the same
way, so don't retry when the stored form differs from what you sent.

## Finding breweries near a place

All three return the same shape and are cursor-paginated (`count` default
100):

- `GET /location/nearby?latitude=&longitude=` — `search_radius` optional
  (default 25 miles; `metric=true` for km)
- `GET /location/zip?zip_code=` — 5-digit US ZIP, geocoded then as nearby
- `GET /location/city?city=&state=` — state as name or abbreviation

Each `data[]` element has three keys: `location` (with flattened address),
`distance` (`{distance, units}` — straight-line, 1 decimal), and `brewer`.

## Reads

- `GET /location/{location_id}` — one location object.
- `GET /location` — all locations; rows `{id, name (nullable),
  last_modified}`; `count` default 500.
