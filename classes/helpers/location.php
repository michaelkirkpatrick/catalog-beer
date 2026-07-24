<?php
/**
 * Location display helpers. Required from initialize.php.
 *
 * Location names are optional in the API — most breweries don't name a taproom
 * separately from the community it sits in, and while the field was required it
 * simply collected the city over and over (266 of 416 locations had a name that
 * was exactly their city). A location with no name of its own therefore has to
 * be labelled from what else is known about it.
 *
 * Which label depends on context, so there are two functions rather than one
 * with a flag. Pick by asking whether the brewer is already on screen:
 *
 *   locationDisplayName()  standalone — the location page, the site-wide map,
 *                          a page title. The brewer is part of the identity.
 *   locationShortName()    inside a brewer's own page or list, under a heading
 *                          that already names the brewer. Repeating it there
 *                          reads twice in one row.
 *
 * Both return a RAW string, not escaped output. Callers put it through their own
 * Text pipeline ($text1->get(...)) for HTML, or hand it to json_encode() for the
 * map popups, which write with textContent and escape for themselves.
 */

/**
 * A standalone label for a location: its name, else "{brewer} – {city}".
 *
 * The brewer name comes from the location object when it carries a nested
 * brewer (GET /location/{id}, GET /location/map); pass $brewerName for list
 * payloads such as GET /brewer/{id}/locations, which omit it because the brewer
 * is already known. GET /location/map carries no address, so those pins resolve
 * to the brewer name alone.
 *
 * @param object|null $location    Decoded location object from the API
 * @param string      $brewerName  Brewer name, when not nested on $location
 * @return string                  The location's name, or a composed stand-in
 */
function locationDisplayName($location, string $brewerName = ''): string {
    if(!empty($location->name)){
        return trim($location->name);
    }

    if($brewerName === '' && !empty($location->brewer->name)){
        $brewerName = $location->brewer->name;
    }
    $brewerName = trim($brewerName);
    $city = trim($location->address->city ?? '');

    if($brewerName !== '' && $city !== ''){
        return $brewerName . ' – ' . $city;
    }elseif($brewerName !== ''){
        return $brewerName;
    }elseif($city !== ''){
        return $city;
    }

    return 'Unnamed Location';
}

/**
 * A label for a location shown inside its own brewer's context: its name, else
 * the city alone. Never includes the brewer, even when the payload nests one.
 *
 * @param object|null $location  Decoded location object from the API
 * @return string                The location's name, its city, or "Taproom"
 */
function locationShortName($location): string {
    if(!empty($location->name)){
        return trim($location->name);
    }

    $city = trim($location->address->city ?? '');

    return ($city !== '') ? $city : 'Taproom';
}
