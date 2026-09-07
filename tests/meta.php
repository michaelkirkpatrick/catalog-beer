<?php
/*
Offline test for classes/helpers/meta.php — the generated beer meta
description and its article rule.

    php tests/meta.php          # pass/fail per assertion, exit 1 on failure

Pure functions, so no network, database or session. The page that calls them
(beer.php) needs a live API and stays a manual staging walk, like the rest of
tests/. NOT DEPLOYED: deploy.sh excludes tests/ — keep it that way.
*/

if(php_sapi_name() !== 'cli'){ exit('CLI only'); }

define('ROOT', dirname(__DIR__));
define('ENVIRONMENT', 'staging');

require_once(ROOT . '/classes/helpers/html.php');
require_once(ROOT . '/classes/helpers/assets.php');
require_once(ROOT . '/classes/helpers/meta.php');
require_once(ROOT . '/classes/htmlHead.class.php');

$pass = 0;
$fail = 0;
function ok($label, $condition, $detail = ''){
    global $pass, $fail;
    if($condition){ $pass++; echo "  PASS $label\n"; }
    else{ $fail++; echo "  FAIL $label" . ($detail !== '' ? "\n       got: $detail" : '') . "\n"; }
}
function is($label, $expected, $actual){ ok($label, $expected === $actual, var_export($actual, true)); }

echo "beerMetaDescription\n";
is('ABV and IBU',
    'Sculpin is an American-Style India Pale Ale (7% ABV, 70 IBU) brewed by Ballast Point Brewing Company.',
    beerMetaDescription('Sculpin', 'American-Style India Pale Ale', 7.0, 70.0, 'Ballast Point Brewing Company'));
is('ABV only',
    'Southern Exposure is a Golden or Blonde Ale (5% ABV) brewed by Tampa Bay Brewing Company.',
    beerMetaDescription('Southern Exposure', 'Golden or Blonde Ale', 5.0, null, 'Tampa Bay Brewing Company'));
is('IBU only',
    'Goldfinch is a Kölsch (22 IBU) brewed by Someone.',
    beerMetaDescription('Goldfinch', 'Kölsch', null, 22.0, 'Someone'));
is('no numbers: no parenthetical',
    'Export Stout is a Stout brewed by Someone.',
    beerMetaDescription('Export Stout', 'Stout', null, null, 'Someone'));
is('stored zero means unknown',
    'X is a Stout brewed by Someone.',
    beerMetaDescription('X', 'Stout', 0.0, 0.0, 'Someone'));
is('ABV keeps one decimal', 'X is a Stout (6.5% ABV) brewed by B.', beerMetaDescription('X', 'Stout', 6.5, null, 'B'));
is('ABV rounds to one decimal', 'X is a Stout (5.3% ABV) brewed by B.', beerMetaDescription('X', 'Stout', 5.25, null, 'B'));
is('ABV drops trailing zero', 'X is a Stout (5% ABV) brewed by B.', beerMetaDescription('X', 'Stout', 5.0, null, 'B'));
is('IBU is an integer', 'X is a Stout (35 IBU) brewed by B.', beerMetaDescription('X', 'Stout', null, 34.6, 'B'));
is('brewer ending in a period is not doubled', 'X is a Stout brewed by Brewing Co.', beerMetaDescription('X', 'Stout', null, null, 'Brewing Co.'));
is('name equals style: subject dropped',
    'A Double IPA (8% ABV) brewed by Founders Brewing Co.',
    beerMetaDescription('Double IPA', 'Double IPA', 8.0, null, 'Founders Brewing Co.'));
is('name equals style, case-insensitive',
    'An American IPA brewed by B.',
    beerMetaDescription('american ipa', 'American IPA', null, null, 'B'));
is('empty name falls back to the style-led form',
    'A Stout brewed by B.',
    beerMetaDescription('', 'Stout', null, null, 'B'));
is('surrounding whitespace trimmed',
    'X is a Stout brewed by B.',
    beerMetaDescription(' X ', ' Stout ', null, null, ' B '));
is('raw in, raw out — no escaping inside the helper',
    'Bob\'s "Best" is a Pale Ale brewed by Bob & Sons <Brewing>.',
    beerMetaDescription('Bob\'s "Best"', 'Pale Ale', null, null, 'Bob & Sons <Brewing>'));

echo "\nindefiniteArticle\n";
foreach(array(
    'American-Style Pale Ale' => 'an', 'Golden or Blonde Ale' => 'a', 'IPA' => 'an', 'NEIPA' => 'an',
    'ESB' => 'an', 'DIPA' => 'a', 'UK Bitter' => 'a', 'European Dark Lager' => 'a', 'Hazy IPA' => 'a',
    'Oatmeal Stout' => 'an', 'Imperial Porter' => 'an', 'Unfiltered Lager' => 'an', 'Eisbock' => 'an',
    'Kölsch' => 'a', 'Öl' => 'a', '' => 'a',
) as $word => $expected){
    is("'$word' takes '$expected'", $expected, indefiniteArticle($word));
}

echo "\nhtmlHead round trip\n";
$_SERVER['REQUEST_URI'] = '/beer/00000000-0000-0000-0000-000000000000';
$head = new htmlHead('Bob\'s "Best" by Bob & Sons');
$sentence = beerMetaDescription('Bob\'s "Best"', 'Pale Ale', 5.5, null, 'Bob & Sons <Brewing>');
$head->addDescription($sentence);
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($head->html . '<body></body></html>');
libxml_clear_errors();
$xp = new DOMXPath($dom);
$metas = $xp->query('//meta[@name="description"]');
ok('exactly one description meta', $metas->length === 1, (string) $metas->length);
is('description reads back exactly, entities decoded once', $sentence, $metas->item(0)->getAttribute('content'));
is('title reads back exactly', 'Bob\'s "Best" by Bob & Sons', $xp->query('//title')->item(0)->textContent);
ok('no attribute injected on the meta', $metas->item(0)->attributes->length === 2, (string) $metas->item(0)->attributes->length);

echo "\n$pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
