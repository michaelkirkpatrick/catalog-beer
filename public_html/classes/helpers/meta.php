<?php
/**
 * Page-metadata helpers: title and meta-description text built from API
 * values. Required from initialize.php.
 *
 * Everything here returns RAW text. The callers hand it to htmlHead, which
 * escapes at the point of output (see helpers/html.php for the one rule).
 */

/**
 * One-sentence meta description for a beer that has no prose of its own.
 *
 * Why: 97% of beer pages have no description, and their <title> used to be the
 * bare beer name, so two "Extra Pale Ale" pages from different breweries were
 * indistinguishable to Google and got filed as duplicates of each other
 * (Search Console, Sep 2026; ../Claude Ideas/beer-page-metadata.md). This
 * sentence puts the facts that DO differ — style, ABV, IBU, brewer — into the
 * snippet.
 *
 *   Southern Exposure is a Golden or Blonde Ale (5% ABV) brewed by Tampa Bay Brewing Company.
 *   Sculpin is an American-Style India Pale Ale (7% ABV, 70 IBU) brewed by Ballast Point Brewing Company.
 *   A Double IPA (8% ABV) brewed by Founders Brewing Co.       <- name equals style
 *
 * @param string     $name   Beer name, raw
 * @param string     $style  Style name — the canonical name when the beer
 *                           resolved to one, else the brewer's own label
 * @param float|null $abv    Percent, null when unknown (stored 0 means unknown)
 * @param float|null $ibu    null when unknown
 * @param string     $brewer Brewer name, raw
 * @return string            Raw text; escape at output
 */
function beerMetaDescription(string $name, string $style, ?float $abv, ?float $ibu, string $brewer): string {
    $name = trim($name);
    $style = trim($style);
    $brewer = trim($brewer);

    $facts = array();
    if($abv !== null && $abv > 0){
        // Up to one decimal, trailing zero trimmed: 5, 6.5 — never 5.00
        $facts[] = rtrim(rtrim(number_format($abv, 1, '.', ''), '0'), '.') . '% ABV';
    }
    if($ibu !== null && $ibu > 0){
        $facts[] = (string) (int) round($ibu) . ' IBU';
    }
    $parenthetical = $facts ? ' (' . implode(', ', $facts) . ')' : '';

    $styled = indefiniteArticle($style) . ' ' . $style;
    if($name === '' || mb_strtolower($name) === mb_strtolower($style)){
        // "Double IPA is a Double IPA" reads as a bug; drop the repeated subject.
        $lead = ucfirst($styled);
    }else{
        $lead = $name . ' is ' . $styled;
    }
    $sentence = $lead . $parenthetical . ' brewed by ' . $brewer;
    // "Founders Brewing Co." already ends the sentence; don't double the period
    return (substr($sentence, -1) === '.') ? $sentence : $sentence . '.';
}

/**
 * "a" or "an" for the word that follows. Spelling-based with the two
 * exceptions that come up in style names: initialisms are read letter by
 * letter ("an NEIPA", "an FES", "a UK Bitter"), and a leading "eu"/"uni"
 * takes "a" ("a European Dark Lager").
 */
function indefiniteArticle(string $word): string {
    $word = ltrim($word);
    if($word === ''){ return 'a'; }
    $first = mb_substr($word, 0, 1);
    $token = preg_split('/[\s\-]/', $word, 2)[0];
    // Initialism: two or more letters, no lowercase (IPA, NEIPA, ESB, DIPA)
    if(mb_strlen($token) >= 2 && preg_match('/^[A-Z0-9]+$/', $token)){
        return (strpos('AEFHILMNORSX', $first) !== false) ? 'an' : 'a';
    }
    $lower = mb_strtolower($word);
    if(preg_match('/^(eu|uni|use|ute)/', $lower)){ return 'a'; }
    return (strpos('aeiou', mb_strtolower($first)) !== false) ? 'an' : 'a';
}
