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
    $city = trim($location->address->locality ?? '');

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

    $city = trim($location->address->locality ?? '');

    return ($city !== '') ? $city : 'Taproom';
}

/**
 * The address as raw display lines, in the country's own order.
 *
 * The API's `formatted` array is the source — the validator wrote it in the
 * order the country writes an address ("Donkerstraat 12" / "8640 Westvleteren"
 * versus "2215 India St" / "San Diego, CA 92101"), without the country name.
 * The street and unit are folded onto one line here, as the site has always
 * shown them; everything after them is kept as written. A row that predates
 * `formatted` (there should be none, the migration backfilled it) is built
 * from the fields in the two common orders.
 *
 * RAW, not escaped. The consumers escape for themselves — json_encode() for the
 * map popups, rawurlencode() for the maps links — so $text1->get() output would
 * reach them as a literal "&#8217;". A location need not have an address on
 * file, in which case this comes back empty.
 *
 * @param object|null $location  Decoded location object from the API
 * @return string[]              Zero or more lines
 */
function locationRawAddressLines($location): array {
    if(!isset($location->address) || empty($location->address->line1)){
        return array();
    }
    $address = $location->address;
    $line1 = trim((string)$address->line1);
    $line2 = trim((string)($address->line2 ?? ''));

    $lines = array($line2 !== '' ? $line1 . ' ' . $line2 : $line1);
    foreach(locationAddressTail($location) as $line){
        $lines[] = $line;
    }
    return $lines;
}

/**
 * The display lines AFTER the street: "San Diego, CA 92101-1725" in the US,
 * "8640 Westvleteren" in Belgium, "Chambly, QC J3L 2H6" in Canada. Raw.
 *
 * Taken from `formatted` with the street and unit lines dropped; built from
 * the fields when `formatted` is absent (US order for the US, postal-code-first
 * everywhere else, which covers most of Europe and is wrong nowhere worse than
 * a swapped order).
 */
function locationAddressTail($location): array {
    $address = $location->address;
    $line1 = trim((string)($address->line1 ?? ''));
    $line2 = trim((string)($address->line2 ?? ''));
    $tail = array();
    if(!empty($address->formatted) && is_array($address->formatted)){
        foreach($address->formatted as $line){
            $line = trim((string)$line);
            if($line === '' || $line === $line1 || ($line2 !== '' && $line === $line2)){
                continue;
            }
            $tail[] = $line;
        }
        return $tail;
    }
    $locality = trim((string)($address->locality ?? ''));
    $postal = trim((string)($address->postal_code ?? ''));
    $region = locationRegionCode($address);
    if(($location->country_code ?? 'US') === 'US'){
        $line = $locality;
        if($region !== ''){ $line .= ($line !== '' ? ', ' : '') . $region; }
        if($postal !== ''){ $line .= ($line !== '' ? ' ' : '') . $postal; }
    }else{
        $line = trim($postal . ' ' . $locality);
    }
    if($line !== ''){ $tail[] = $line; }
    return $tail;
}

/**
 * The short region code behind an address: "OR" from "US-OR", "VWV" from
 * "BE-VWV". '' when the address carries no region. Raw.
 */
function locationRegionCode($address): string {
    $subCode = trim((string)($address->sub_code ?? ''));
    return (strlen($subCode) > 3 && $subCode[2] === '-') ? substr($subCode, 3) : '';
}

/**
 * The address formatted for a facts rail. Every piece is '' when that part isn't
 * on file — a location can exist without an address at all.
 *
 * FOUR KEYS ARE RAW AND ONE IS HTML. That is not tidy, so the HTML one is named
 * for it rather than left to be discovered:
 *
 *   street           RAW  street line, unit appended        -> caller wraps in h()
 *   cityShort        RAW  "Portland, OR" / "Westvleteren, Belgium", for prose
 *                                                           -> caller wraps in h()
 *   telephone        RAW  "(503) 555-0100" / "+32 57 40 03 76" -> caller wraps in h()
 *   telephoneHref    RAW  "tel:+15035550100", the E.164 number -> caller wraps in h()
 *   cityHtml         HTML the lines after the street, WITH the schema.org
 *                         microdata, every interpolated piece already escaped
 *                         here -> echo as-is, and do NOT pass it through h() or
 *                         the tags become visible text.
 *
 * `cityHtml` renders the country-ordered lines from `formatted` as visible
 * text (joined with <br>) and carries addressLocality, addressRegion and
 * postalCode as <meta> tags, since the visible line's pieces no longer map
 * one-to-one onto those properties outside the US. The spans expect a
 * PostalAddress scope around them — see the rail markup on location.php.
 *
 * `cityShort` must stay RAW: location.php feeds it to htmlHead::addDescription(),
 * which escapes for itself, so pre-escaping here would double-escape the meta
 * description. Outside the US it names the country, since "Westvleteren" alone
 * places nothing for most readers.
 *
 * @param object|null $location  Decoded location object from the API
 */
function locationAddressFacts($location): array {
    $facts = array('street' => '', 'cityHtml' => '', 'cityShort' => '', 'telephone' => '', 'telephoneHref' => '');
    if(!isset($location->address) || empty($location->address->line1)){
        return $facts;
    }
    $address = $location->address;
    $countryCode = (string)($location->country_code ?? 'US');

    $facts['street'] = trim((string)$address->line1);
    if(!empty($address->line2)){
        $facts['street'] .= ' ' . trim((string)$address->line2);
    }

    $locality = trim((string)($address->locality ?? ''));
    $region = locationRegionCode($address);
    $postal = trim((string)($address->postal_code ?? ''));
    $tail = locationAddressTail($location);

    if($locality !== ''){
        if($countryCode === 'US'){
            $facts['cityShort'] = ($region !== '') ? $locality . ', ' . $region : $locality;
        }else{
            $countryName = CountryCode::shortName($countryCode) ?? $countryCode;
            $facts['cityShort'] = $locality . ', ' . $countryName;
        }
    }

    if(!empty($tail)){
        $facts['cityHtml'] = implode('<br>', array_map('h', $tail));
        $facts['cityHtml'] .= '<meta itemprop="addressLocality" content="' . h($locality) . '">';
        if($region !== ''){
            $facts['cityHtml'] .= '<meta itemprop="addressRegion" content="' . h($region) . '">';
        }
        if($postal !== ''){
            $facts['cityHtml'] .= '<meta itemprop="postalCode" content="' . h($postal) . '">';
        }
    }

    $e164 = trim((string)($address->telephone ?? ''));
    $facts['telephone'] = formatTelephone($e164);
    $facts['telephoneHref'] = ($facts['telephone'] !== '') ? 'tel:' . $e164 : '';

    return $facts;
}

/**
 * A stored phone number for display: "(503) 555-0100" for the North American
 * numbering plan, "+32 57 40 03 76" for everything else.
 *
 * The API stores E.164 — a plus sign, the country calling code and the
 * national number, no separators. NANP numbers get the familiar US form
 * without the +1. Other countries get the calling code and the national
 * number in pairs from the right (so an odd length leads with a trio), which
 * is readable everywhere even where the local convention groups differently;
 * a real per-country formatter is a library this site does not carry.
 *
 * Anything that is not an E.164 string — an old ten-digit value from a cached
 * index record, an empty field — returns '' so the caller renders nothing: a
 * half-formatted number reads as broken data.
 *
 * @param string|int|null $e164  The stored number
 */
function formatTelephone($e164): string {
    $e164 = trim(strval($e164 ?? ''));
    if(!preg_match('/^\+([1-9][0-9]{6,14})$/', $e164, $m)){
        return '';
    }
    $digits = $m[1];
    if($digits[0] === '1' && strlen($digits) === 11){
        return '(' . substr($digits, 1, 3) . ') ' . substr($digits, 4, 3) . '-' . substr($digits, 7, 4);
    }
    // Calling codes are 1–3 digits; take the longest known prefix.
    static $callingCodes = array('31','32','33','34','39','41','43','44','45','46','47','48','49','52','54','55','56','57','60','61','64','65','81','91','351','352','353','358','359','370','371','372','385','386','420','421');
    $code = '';
    foreach(array(3, 2, 1) as $len){
        $candidate = substr($digits, 0, $len);
        if(in_array($candidate, $callingCodes, true) || ($len === 1 && $candidate === '1')){
            $code = $candidate;
            break;
        }
    }
    if($code === ''){
        $code = substr($digits, 0, 2);
    }
    $national = substr($digits, strlen($code));
    $groups = array();
    if(strlen($national) % 2 === 1){
        $groups[] = substr($national, 0, 3);
        $national = substr($national, 3);
    }
    foreach(str_split($national, 2) as $pair){
        $groups[] = $pair;
    }
    return '+' . $code . ' ' . implode(' ', array_filter($groups, 'strlen'));
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
