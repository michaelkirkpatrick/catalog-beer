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
 * Both return a RAW string, not escaped output. Callers escape at the point of
 * output — h() for HTML, or json_encode() for the map popups, which write with
 * textContent and escape for themselves.
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

/**
 * The address as raw display lines: the street (unit appended), then "City, ST".
 *
 * RAW, not escaped. The consumers escape for themselves — json_encode() for the
 * map popups, rawurlencode() for the maps links — so $text1->get() output would
 * reach them as a literal "&#8217;". A location need not have an address on
 * file, in which case this comes back empty.
 *
 * @param object|null $location  Decoded location object from the API
 * @return string[]              Zero, one or two lines
 */
function locationRawAddressLines($location): array {
    $lines = array();
    if(!isset($location->address)){
        return $lines;
    }
    $address = $location->address;

    // address2 is the street line, address1 the unit — the API's naming, not a typo
    if(!empty($address->address2)){
        $street = $address->address2;
        if(!empty($address->address1)){
            $street .= ' ' . $address->address1;
        }
        $lines[] = $street;
    }
    if(!empty($address->city)){
        $city = $address->city;
        if(!empty($address->state_short)){
            $city .= ', ' . $address->state_short;
        }
        $lines[] = $city;
    }

    return $lines;
}

/**
 * The address formatted for a facts rail. Every piece is '' when that part isn't
 * on file — a location can exist without an address at all.
 *
 * FOUR KEYS ARE RAW AND ONE IS HTML. That is not tidy, so the HTML one is named
 * for it rather than left to be discovered:
 *
 *   street           RAW  street line, unit appended        -> caller wraps in h()
 *   cityShort        RAW  "Portland, OR", for prose         -> caller wraps in h()
 *   telephone        RAW  "(503) 555-0100"                  -> caller wraps in h()
 *   telephoneDigits  RAW  those digits, for a tel: href     -> caller wraps in h()
 *   cityHtml         HTML the city line WITH the schema.org spans, every
 *                         interpolated piece already escaped here -> echo as-is,
 *                         and do NOT pass it through h() or the spans become
 *                         visible text.
 *
 * `cityHtml` has to be assembled here because the markup interleaves with the
 * data: addressLocality, addressRegion and postalCode each wrap their own piece.
 * The alternative — handing back three raw pieces for two callers to assemble
 * identically — duplicates the microdata contract in two places.
 *
 * `cityShort` must stay RAW: location.php feeds it to htmlHead::addDescription(),
 * which escapes for itself, so pre-escaping here would double-escape the meta
 * description.
 *
 * The spans expect a PostalAddress scope around them — see the rail markup on
 * location.php.
 *
 * @param object|null $location  Decoded location object from the API
 */
function locationAddressFacts($location): array {
    $facts = array('street' => '', 'cityHtml' => '', 'cityShort' => '', 'telephone' => '', 'telephoneDigits' => '');
    if(!isset($location->address)){
        return $facts;
    }
    $address = $location->address;

    if(!empty($address->address2)){
        $facts['street'] = $address->address2;
        if(!empty($address->address1)){
            $facts['street'] .= ' ' . $address->address1;
        }
    }

    if(!empty($address->city)){
        $zipCode = strval($address->zip5 ?? '');
        if(!empty($address->zip4)){
            $zipCode .= '-' . $address->zip4;
        }
        $city = $address->city;
        $stateShort = strval($address->state_short ?? '');

        // cityShort stays raw; cityHtml escapes each piece as it goes in.
        $facts['cityShort'] = ($stateShort !== '') ? $city . ', ' . $stateShort : $city;

        $facts['cityHtml'] = '<span itemprop="addressLocality">' . h($city) . '</span>';
        if($stateShort !== ''){
            $facts['cityHtml'] .= ', <span itemprop="addressRegion">' . h($stateShort) . '</span>';
        }
        if($zipCode !== ''){
            $facts['cityHtml'] .= ' <span itemprop="postalCode">' . h($zipCode) . '</span>';
        }
    }

    $facts['telephoneDigits'] = strval($address->telephone ?? '');
    $facts['telephone'] = formatTelephone($facts['telephoneDigits']);

    return $facts;
}

/**
 * A stored phone number as "(503) 555-0100".
 *
 * We store exactly 10 digits (NANP, no country code). Anything else — a short
 * number, a stray extension, an empty field — returns '' so the caller renders
 * nothing at all; a half-formatted number reads as broken data, and a raw
 * 10-digit run reads as an ID rather than something you could dial.
 *
 * Takes the digits loosely typed because callers get them from different
 * places: the API decodes JSON, so a phone arrives as an int, while the same
 * value out of the Algolia index or a form arrives as a string.
 *
 * @param string|int|null $digits  The stored number, digits only
 */
function formatTelephone($digits): string {
    $digits = strval($digits ?? '');
    if(strlen($digits) !== 10 || !ctype_digit($digits)){
        return '';
    }
    return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
}

/**
 * "Open in maps" hrefs — the address in a facts rail doubles as a link into the
 * visitor's mapping app, which is the whole interaction on a phone; nobody wants
 * to copy-paste a street address into another app one-handed.
 *
 * Coordinates win when we have them: a pin is exact, where a geocoded string is
 * the mapping app's best guess. The address text is the fallback so a location
 * with no fix on file still resolves to something.
 *
 * Two hrefs, because there is no one URL that honours every platform's default:
 * the Google form is universal (opens the Google Maps app on Android, the web
 * everywhere else), and maps.apple.com hands off to Maps.app on iOS/macOS.
 * assets/js/maps-link.js picks the Apple one on Apple platforms.
 *
 * @param object|null $location  Decoded location object from the API
 * @param string      $label     Pin label for the Apple href, raw and optional
 * @return array                 ['google' => string, 'apple' => string], both ''
 *                               when there's neither a fix nor an address
 */
function locationMapsLinks($location, string $label = ''): array {
    $links = array('google' => '', 'apple' => '');

    if(!empty($location->latitude) && !empty($location->longitude)){
        $coords = (float)$location->latitude . ',' . (float)$location->longitude;
        $links['google'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($coords);
        // ll centres the map, q labels the pin that lands on it.
        $links['apple'] = 'https://maps.apple.com/?ll=' . rawurlencode($coords);
        if(trim($label) !== ''){
            $links['apple'] .= '&q=' . rawurlencode(trim($label));
        }
        return $links;
    }

    $addressLines = locationRawAddressLines($location);
    if(count($addressLines) > 0){
        $query = implode(', ', $addressLines);
        $links['google'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
        $links['apple'] = 'https://maps.apple.com/?q=' . rawurlencode($query);
    }

    return $links;
}
