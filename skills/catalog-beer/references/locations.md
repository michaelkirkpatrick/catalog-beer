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
| `country_short_name` | string | e.g. `United States` |
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

All optional: `brewer_id`, `name` (send `null`/empty string to clear),
`country_code`, `url`.

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

`PUT /address/{location_id}` replaces the address entirely (omitted optional
fields cleared) — **except** `zip4`, `city` and `sub_code`, which the geocoder
re-derives from the street address and ZIP. `address1` and `telephone` are
passed through verbatim and *will* clear if omitted.

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

**`address1` is never normalised.** The geocoder standardises `address2`,
`city` and `zip5`, and derives `zip4` — but Google's Geocoding API maps
properties and street infrastructure, not destinations inside a building, so
suite/unit/floor is stored exactly as submitted. Send it in USPS form
(`Ste 101`) if you care how it reads.

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
