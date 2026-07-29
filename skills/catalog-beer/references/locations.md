# Locations & Addresses

A **location** is a physical place (taproom, brewery, brewpub) belonging to a
brewer. Street addresses are stored separately and added with a **second
request** after the location is created. Addresses are US-only for now.

## The location object

| Field | Type | Notes |
|---|---|---|
| `id` | string | location UUID |
| `object` | string | `"location"` |
| `name` | string \| null | most taprooms have no name of their own — leave null; never just repeat the city |
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
| `name` | no | only if the venue has a real name (e.g. "The Barrel House") |
| `url` | no | location-specific URL — subject to the same **live reachability check** as the brewer's `url` (see [brewers.md](brewers.md)); an unreachable URL 400s the whole write, so retry once then send without `url` |

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
| `zip5` | either/or | 5-digit ZIP |
| `zip4` | no | ZIP+4 |
| `telephone` | no | 10-digit; formatting accepted but stripped (returned as integer) |

`PUT /address/{location_id}` replaces the address entirely (omitted optional
fields cleared).

## The US address object (in responses)

`address1` (nullable), `address2`, `city`, `sub_code` (e.g. `US-CA`),
`state_short` (`CA`), `state_long` (`California`), `zip5` (**integer**),
`zip4` (integer, nullable), `telephone` (integer, nullable — no country
code).

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
