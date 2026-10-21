# Locations & Addresses

A **location** is a physical place (taproom, brewery, brewpub) belonging to a brewer. Its street address is a **nested `address` object on the location's own POST, PUT and PATCH** — there is no separate address request or endpoint (API 6.0). The address takes its country from the location's `country_code`, which must be one of the 40 countries the validator covers — `GET /location/countries` lists them (AR AT AU BE BG BR CA CH CL CO CZ DE DK EE ES FI FR GB HR HU IE IN IT JP LT LU LV MX MY NL NO NZ PL PR PT SE SG SI SK US); any other code is a `400` on `country_code`.

## The location object

| Field | Type | Notes |
|---|---|---|
| `id` | string | location UUID |
| `object` | string | `"location"` |
| `name` | string \| null | what the address can't convey — the venue's own name, or its neighborhood when the brewer has siblings in one city; null when the city already identifies it. See "Naming a location" |
| `url` | string \| null | location-specific page; should differ from the brewer's main URL |
| `country_code` | string | ISO 3166-1 alpha-2 (e.g. `US`, `BE`) |
| `country_short_name` | string | server-derived from `country_code`; the ISO short name (`United States of America`, `Belgium`) |
| `latitude`, `longitude` | float \| null | geocoded server-side when an address is added |
| `cb_verified`, `brewer_verified` | boolean | server-controlled |
| `last_modified` | integer | Unix timestamp |
| `address` | object | address object (below). Every write carries one, so the key is absent only on the few locations that predate the rule |
| `brewer` | object | nested brewer object |

## POST /location — create

| Field | Required | Constraints |
|---|---|---|
| `brewer_id` | **yes** | |
| `country_code` | **yes** | ISO 3166-1 alpha-2, from the 40 supported countries |
| `address` | **yes** | the nested address object — see "The nested `address`" below. Validated before anything is written; omitting it is a `400` naming the fields the country needs |
| `name` | no | the venue's own name, or its neighborhood — see "Naming a location"; never the bare city |
| `url` | no | location-specific URL — subject to the same **live reachability check** as the brewer's `url` (see [brewers.md](brewers.md)); only a host that does not answer, or answers `404`/`410`, is refused (a bot-protection `403` is accepted), and a refused URL 400s the whole write, so retry once then send without `url` |

## Naming a location

`name` earns its place only by saying something the address does not. Work down this list and stop at the first match:

1. **The venue has a name of its own** — "The Barrel House", "The Harland Clubhouse". Use it.
2. **The brewer has more than one location in the same city** — use the **neighborhood** or district: "South Park", "Bay Park", "Scripps Ranch". This is what tells six San Diego taprooms apart in a list where every address already reads "San Diego".
3. **Otherwise** — leave `name` null. The address already identifies a brewer's only venue in a city; repeating the city name adds nothing.

Never the bare city, and never a neighborhood you *recalled* rather than read — that is rule 1 (*no source, no write*) applied to a name, and city geography is exactly the kind of fact that feels safe to supply from memory. The brewery's own wording is the source: prefer the short form it uses in a footer or locations list ("Scripps Ranch") over a longer page heading ("Scripps Ranch Tasting Room"). A development or center the brewery treats as the venue's identity ("One Paseo") counts under 1.

A location already in the catalog with a null or city-only `name` is corrected with `PATCH /location/{location_id}` — see below.

## PATCH /location/{location_id}

All optional: `brewer_id`, `name`, `country_code`, `url`, and `address` (merged over the stored one — it can be corrected but not removed; see below).

**Send `null` to clear a nullable field** — `name` and `url`. An absent key means "leave this alone". Prefer this over PUT for clearing, since PUT clears by omission and will take the other optional fields with it.

`brewer_id` and `country_code` are required and cannot be cleared; an explicit `null` on either returns `400`.

## PUT /location/{location_id}

`brewer_id`, `country_code` and `address` required; omitted `name`/`url` cleared to null.

## DELETE /location/{location_id}

→ `204 No Content`. Only on explicit user instruction.

## The nested `address` on POST / PUT / PATCH /location

Send the address inside the location request. It is validated and geocoded server-side before the row is written: on a `POST` a refused address means no location is created; the response is the full location object with `address`, `latitude` and `longitude` populated. Send the address the way the country writes it; what comes back is the validated form. One vocabulary for every country (API 6.0 — the old `address2`/`address1`/`city`/`zip5`/`zip4` keys and the `/address` endpoint are gone; a body using them is a `400` on `address.line1`).

```json
{"brewer_id": "…", "country_code": "BE", "address": {"line1": "Donkerstraat 12", "locality": "Westvleteren", "postal_code": "8640", "telephone": "057 40 03 76"}}
```

| Field | Required | Constraints |
|---|---|---|
| `line1` | **yes** | the street line, in the country's order: `110 E 22nd St`, `Donkerstraat 12`, `28 Rue de la Goutte d'Or` |
| `line2` | no | suite/unit/building, e.g. `Suite 101`, `bus 3` |
| `locality` | either/or | the city or town; provide `locality` OR `postal_code`. In the US, `locality` needs `sub_code` beside it |
| `dependent_locality` | no | district/village within the locality; the validator fills it in where it applies, so usually omit |
| `sub_code` | US: with `locality` | ISO 3166-2 region, e.g. `US-CA`, `BE-VWV`, `CA-QC`; a bare suffix (`CA`) is prefixed with the location's country. Must belong to that country — `GET /location/subdivisions?country=BE` lists the codes. Optional outside the US; where the validator reports a region itself, that wins |
| `postal_code` | either/or | the country's own form, as a **string**: `90731`, `90731-7202`, `8640`, `1018 AL`, `SW1A 1AA`. An obviously wrong shape for the country is a `400` before validation |
| `telephone` | no | international (`+32 57 40 03 76`) or the country's national format (`057 40 03 76`, `(310) 833-9330`); returned as an E.164 **string** (`+3257400376`). A bare number is read in the **location's** country — a US number on a Belgian location needs its `+1` |

Validation errors come back nested: `valid_state.address.line1`, `valid_msg.address.postal_code`, and so on, beside the location's own `brewer_id`/`name`/`url`/`country_code`.

**`PUT /location/{id}`** replaces the address wholesale: sub-fields you omit clear. `address` itself is required on a PUT — leaving it out is a `400`, not a way to clear it. `locality`, `sub_code`, `dependent_locality` and `postal_code` re-derive from the validated address whether you send them or not.

**`PATCH /location/{id}`** merges the sub-fields you send over the stored address and re-validates the whole as one. Send `null` for a sub-field to clear it (`{"address": {"telephone": null}}`). The address itself can be corrected but not removed: `"address": null` is a `400`. A patch that sends only `telephone` is written without re-validating the street. One asymmetry to know: `locality`+`sub_code` and `postal_code` are two spellings of the same place, so when you patch one group the stored other group is dropped and re-derived rather than merged — patching a new postal code with an old stored city would otherwise hand the validator a contradiction. Patch whichever group you trust and let the other come back derived.

**Changing the country of a location that holds an address** needs the address in the same request (it re-validates in the new country); a `country_code` change on its own is a `400`.

## The address object (in responses)

`line1`, `line2` (nullable), `locality`, `dependent_locality` (nullable), `sub_code` (nullable outside the US, e.g. `US-CA`, `BE-VWV`), `postal_code` (**string**, nullable), `formatted` (array of display lines in the country's own order, country name excluded — render these rather than composing an address yourself), `language_code` (`en`, `nl`, `fr` …), `telephone` (E.164 **string**, nullable), `admin_area` (`{sub_code, name, type}` or null — `name` is the local official form: `California`, `West-Vlaanderen`).

**Postal codes are strings, not numbers.** US ZIPs are fixed-width identifiers whose leading zero is significant — `00501`–`09999` covers New England, New Jersey, Puerto Rico and the US Virgin Islands — and other countries' codes carry letters and spaces. Send `"01085"`, not `1085`; a 4-digit US value is rejected with `valid_state.postal_code: "invalid"`. Don't parse the response value as an integer and write it back, or you will strip the zero and the next write will fail. A US ZIP+4 comes back as one value: `"92101-1725"`.

**The whole address is standardized — `line2` included.** Every write runs through Google Address Validation; US addresses also go through USPS CASS. In the US the street stores with its words spelled out (`Woodinville Redmond Rd NE`, even where USPS abbreviates); the city stores as USPS's mailing city, falling back to Google's locality where USPS's 13-character field truncates the name; and the suite/unit is re-derived from the CASS secondary line, not passed through. Casing is normalized with directionals and unit letters intact (`Rd NE`, `Ste 105B`). Outside the US, Google's own rendering is stored: the street spelled out in the local language (`Köpenicker Straße 70`, not `Str.`), the locality as the postal service names it (Westvleteren, not the municipality Vleteren), the postal code normalized (`1018 AL`). Send whatever form you have and treat what comes back as canonical — a re-submit of your original spelling will standardize the same way, so don't retry when the stored form differs from what you sent.

**The mailing city is frequently not the municipality the venue sits in**, and that is correct rather than a bug. A ZIP has one preferred city name, and it is often a larger neighbour: a taproom in Maplewood, MO stores as `Saint Louis` (the preferred name for `63143`), and most of St. Louis County, Brooklyn and much of LA County behave the same way. Unincorporated communities resolve to their post office — Paoli, WI stores as Belleville. A `PATCH` putting the municipality back re-derives straight to the mailing city again. To check what a ZIP's preferred city is, use [USPS's lookup **by ZIP code**](https://tools.usps.com/zip-code-lookup.htm?bycitystate), not by address: the by-address form echoes back whichever acceptable alias you typed, which makes the stored value look wrong when it isn't.

**An address the validator cannot place at building level is refused**, not stored approximately: an unnumbered street the validator can only place to street level (`Chiswick Lane South`) comes back `400` — and on a `POST` the location is not created either. A named premise the validator does know (`St James's Gate, Dublin`) passes, with no postal code if the premise has none. Find the numbered street address on the brewery's site or a map. A location cannot be created without an address, so if none can be found, report that rather than guessing one.

**Belgium reports no province.** The validator returns none for Belgian addresses, so `sub_code` and `admin_area` are null unless you send one (`BE-VWV` for West-Vlaanderen); Brussels has no province at all. That is the normal shape, not a failed write.

## Finding breweries near a place

All three return the same shape and are cursor-paginated (`count` default 100):

- `GET /location/nearby?latitude=&longitude=` — `search_radius` optional (default 25 miles; `metric=true` for km)
- `GET /location/zip?postal_code=&country=` — a postal code in its country's form (`country` defaults to `US`), geocoded then as nearby
- `GET /location/city?city=&region=&country=` — `region` (state/province, name or abbreviation) is required in the US and optional elsewhere; `country` defaults to `US`

`metric` defaults to kilometers outside the US (and Liberia and Myanmar), miles there; say it explicitly if the unit matters.

Each `data[]` element has three keys: `location` (with its `address` object; every key null when the location has no address yet), `distance` (`{distance, units}` — straight-line, 1 decimal), and `brewer`.

## Reference lists

- `GET /location/countries` — the 40 countries a location may be in: `country_code`, `country_short_name`, `subdivisions` (how many ISO 3166-2 rows the catalog holds; 0 means send no `sub_code`), `calling_code`.
- `GET /location/subdivisions?country=BE` — that country's regions: `sub_code`, `name` (local official form), `type` (`state`, `province`, …), `parent_code`. These are the only values `sub_code` accepts.

## Reads

- `GET /location/{location_id}` — one location object.
- `GET /location` — all locations; rows `{id, name (nullable), last_modified}`; `count` default 500.
