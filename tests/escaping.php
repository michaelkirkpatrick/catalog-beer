<?php
/*
Offline regression test for output escaping — the h() helper and the classes
that render user-controlled values into HTML.

    php tests/escaping.php          # pass/fail per assertion, exit 1 on failure
    php tests/escaping.php -v       # also dump the rendered markup

No network, no database, no session: every class covered here is a pure
renderer. That is the limit of this harness and it is deliberate — the page
files (brewer.php, location.php, ...) need initialize.php and a live API, so
they are verified by walking staging, not here.

WHY IT ASSERTS THE WAY IT DOES. The bug this migration exists to kill was an
attacker adding an ATTRIBUTE to a tag, not adding a tag. Grepping the output
for "onfocus" can't tell an injected handler from the same word sitting
harmlessly inside a value, so these tests parse the markup with DOM and ask
the structural question instead: which attributes actually exist on the
element? An injected handler shows up as a new attribute name; the same bytes
as text do not. Values are then read back through DOM, which decodes entities
exactly as a browser would — so "did it survive" and "is it inert" are two
separate, honest questions.

Add to this as chunks land. Covered so far: chunk 1 (h), chunk 3 (htmlHead),
chunk 4 (InputField, Textarea, GuidedStyleField, DropDown), chunk 5
(Checkbox), chunk 6 (Alert).

NOT DEPLOYED: deploy.sh excludes tests/. Keep it that way — this directory is
executable PHP and the web root is public.
*/

if(php_sapi_name() !== 'cli'){ exit('CLI only'); }

define('ROOT', dirname(__DIR__));
define('ENVIRONMENT', 'staging');   // htmlHead only adds Fathom on production

require_once(ROOT . '/classes/helpers/html.php');
require_once(ROOT . '/classes/helpers/assets.php');
require_once(ROOT . '/classes/htmlHead.class.php');
require_once(ROOT . '/classes/InputField.class.php');
require_once(ROOT . '/classes/Textarea.class.php');
require_once(ROOT . '/classes/GuidedStyleField.class.php');
require_once(ROOT . '/classes/DropDown.class.php');
require_once(ROOT . '/classes/Checkbox.class.php');
require_once(ROOT . '/classes/Alert.class.php');

$verbose = in_array('-v', $argv);
$pass = 0;
$fail = 0;

function section($title){
    echo "\n\033[1m$title\033[0m\n";
}

function ok($label, $condition, $detail = ''){
    global $pass, $fail;
    if($condition){ $pass++; } else { $fail++; }
    printf("  %s %s%s\n", $condition ? 'PASS' : 'FAIL', $label,
        ($detail !== '' && (!$condition || $GLOBALS['verbose'])) ? "  ($detail)" : '');
}

// --- DOM helpers -----------------------------------------------------------

function dom($html){
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'
        . $html . '</body></html>', LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();
    return $doc;
}

// Every attribute name present anywhere in the fragment. An injected handler
// appears here; the same characters sitting inside a value do not.
function attrNames($html){
    $names = array();
    foreach((new DOMXPath(dom($html)))->query('//body//*') as $el){
        foreach($el->attributes as $a){ $names[] = strtolower($a->nodeName); }
    }
    sort($names);
    return array_values(array_unique($names));
}

// An attribute's value as the BROWSER sees it — entities already decoded.
function attrValue($html, $xpath, $attr){
    $node = (new DOMXPath(dom($html)))->query($xpath)->item(0);
    return $node ? $node->getAttribute($attr) : null;
}

function textOf($html, $xpath){
    $node = (new DOMXPath(dom($html)))->query($xpath)->item(0);
    return $node ? $node->textContent : null;
}

// Assert no attribute outside the expected set appeared anywhere.
function noInjectedAttrs($label, $html, array $allowed){
    $found = attrNames($html);
    $extra = array_diff($found, $allowed);
    ok($label, count($extra) === 0, 'unexpected: ' . implode(', ', $extra));
}

// --- payloads --------------------------------------------------------------

// The live bug: closes content="" and hangs a second attribute on the tag.
$ATTR_BREAKOUT = '0;url=https://evil.example" http-equiv="refresh';
// Quote breakout aimed at an event handler.
$HANDLER = 'Sierra" autofocus onfocus="alert(document.domain)';
// The entity bypass. Purifier decoded these LAST, so they defeated SmartyPants
// and re-formed a real quote. h() must render them as visible text.
$ENTITY = 'Sierra&quot; autofocus onfocus=&quot;alert(1)';
$ENTITY_DEC = 'Sierra&#34; autofocus';
$ENTITY_HEX = 'Sierra&#x22; autofocus';
$TAG = '</textarea><script>alert(1)</script>';

// Real production values — these must survive completely intact.
$REAL = array(
    'ABV notation'   => 'Athletic crafts full-flavor, non-alcoholic brews (<0.5% ABV) made for every day',
    'censor stars'   => 'a creamy F***ing pint on Nitro',
    'apostrophe'     => "O'Brien's Brewery",
    'ampersand'      => 'Barley & Hops Brewing Co.',
    'double quote'   => 'The "Summer" Ale',
    'em dash + curly'=> 'Bob’s Brewery — est. 1994',
    'newlines'       => "First paragraph.\n\nSecond paragraph.",
);

// ===========================================================================
section('h() — chunk 1');

ok('escapes &',            h('a & b') === 'a &amp; b');
ok('escapes < and >',      h('<b>') === '&lt;b&gt;');
ok('escapes double quote', h('say "hi"') === 'say &quot;hi&quot;');
ok('escapes single quote (ENT_QUOTES — the attribute-context fix)',
    h("it's") === 'it&#039;s');
ok('null becomes empty string, no deprecation', h(null) === '');
ok('integers survive', h(42) === '42');

// ENT_SUBSTITUTE. Without it htmlspecialchars() returns '' for malformed
// UTF-8 — a field that silently blanks itself.
$bad = "Sierra \xC3\x28 Nevada";
ok('malformed UTF-8 is not blanked (ENT_SUBSTITUTE)', h($bad) !== '', var_export(h($bad), true));
ok('malformed UTF-8 keeps the surrounding text', str_contains(h($bad), 'Nevada'));

// The entity bypass renders as text, not as a delimiter.
ok('&quot; is shown, not decoded', h('&quot;') === '&amp;quot;');
ok('&#34; is shown, not decoded',  h('&#34;')  === '&amp;#34;');
ok('&#x22; is shown, not decoded', h('&#x22;') === '&amp;#x22;');

// Escaping is lossless: the browser sees the original bytes back.
foreach($REAL as $label => $value){
    ok("round-trips intact: $label",
        html_entity_decode(h($value), ENT_QUOTES, 'UTF-8') === $value);
}

// Double-escaping is the failure mode on the other side.
ok('h() is NOT idempotent — escaping twice is a bug, not a no-op',
    h(h('a & b')) === 'a &amp;amp; b');

// ===========================================================================
section('htmlHead — chunk 3 (the live stored redirect)');

$head = new htmlHead('Evil" onload="alert(1) Brewing');
$head->addDescription($ATTR_BREAKOUT);
preg_match('~<meta name="description".*?>~s', $head->html, $m);
$metaTag = $m[0] ?? '';

if($verbose){ echo "    $metaTag\n"; }

ok('meta description carries exactly name + content',
    attrNames($metaTag) === array('content', 'name'), implode(',', attrNames($metaTag)));
ok('no http-equiv attribute exists on the tag',
    !in_array('http-equiv', attrNames($metaTag)));
// Scoped to name="description": dom() supplies its own <meta charset> wrapper,
// so a bare //meta matches that one first.
ok('the payload survives as the content VALUE',
    attrValue($metaTag, '//meta[@name="description"]', 'content') === $ATTR_BREAKOUT);
ok('page title round-trips through <title>',
    html_entity_decode(strip_tags((string)preg_replace('~.*(<title>.*</title>).*~s', '$1', $head->html)),
        ENT_QUOTES, 'UTF-8') === 'Evil" onload="alert(1) Brewing');
ok('exactly one <meta charset>', preg_match_all('~<meta charset~i', $head->html) === 1);
ok('charset stays inside the 1024-byte sniff window',
    stripos($head->html, '<meta charset') < 1024, 'offset ' . stripos($head->html, '<meta charset'));

$twice = new htmlHead('T');
$twice->addDescription('one');
$twice->addDescription('two');
ok('addDescription() is no longer a silent no-op on the second call',
    preg_match_all('~name="description"~', $twice->html) === 2);

$empty = new htmlHead('T');
$empty->addDescription('');
ok('empty description adds no tag', !str_contains($empty->html, 'name="description"'));

// ===========================================================================
section('InputField — chunk 4');

$in = new InputField();
$in->name        = $HANDLER;
$in->description = $TAG;
$in->value       = $ATTR_BREAKOUT;
$in->placeholder = $ENTITY;
$in->hint        = $ENTITY_DEC;
$in->validState  = 'invalid';
$in->validMsg    = $ENTITY_HEX;
$in->type        = 'text" autofocus onfocus="alert(1)';   // the raw sink at :84
$html = $in->display();
if($verbose){ echo "    $html\n"; }

noInjectedAttrs('no attribute injected anywhere in the field', $html, array(
    'class', 'for', 'id', 'name', 'type', 'placeholder', 'maxlength',
    'value', 'aria-describedby', 'aria-hidden',
));
ok('type= is escaped (was interpolated raw)',
    attrValue($html, '//input', 'type') === 'text" autofocus onfocus="alert(1)');
ok('no autofocus attribute materialised', !in_array('autofocus', attrNames($html)));
ok('no onfocus handler materialised',     !in_array('onfocus', attrNames($html)));
ok('value survives exactly',  attrValue($html, '//input', 'value') === $ATTR_BREAKOUT);
ok('name survives exactly',   attrValue($html, '//input', 'name')  === $HANDLER);
ok('label text is shown literally, no tag opened',
    textOf($html, '//label') === $TAG);
ok('no script element was created', (new DOMXPath(dom($html)))->query('//script')->length === 0);
ok('placeholder entity shown as text',
    attrValue($html, '//input', 'placeholder') === $ENTITY);
ok('hint entity shown as text', textOf($html, '//p[@class="cbf-hint"]') === $ENTITY_DEC);
ok('validMsg entity shown as text',
    trim((string)textOf($html, '//div[@class="cbf-err"]')) === '!' . $ENTITY_HEX);

// A real brewer name must come out looking like itself.
$plain = new InputField();
$plain->name = 'name';
$plain->description = 'Brewer Name';
$plain->value = "O'Brien's Brewery & Co.";
$plainHtml = $plain->display();
ok('real name renders unchanged',
    attrValue($plainHtml, '//input', 'value') === "O'Brien's Brewery & Co.");
ok('label renders unchanged', textOf($plainHtml, '//label') === 'Brewer Name');

// ===========================================================================
section('Textarea — chunk 4');

$ta = new Textarea();
$ta->name        = $HANDLER;          // the raw attribute sink at :53
$ta->description = $TAG;              // the raw label-body sink at :46
$ta->value       = $TAG;
$ta->hint        = $ENTITY;
$ta->validState  = 'invalid';
$ta->validMsg    = $ENTITY_DEC;
$html = $ta->display();
if($verbose){ echo "    $html\n"; }

noInjectedAttrs('no attribute injected anywhere in the field', $html, array(
    'class', 'for', 'id', 'name', 'rows', 'aria-describedby', 'aria-hidden',
));
ok('name= is escaped (was interpolated raw at :53)',
    attrValue($html, '//textarea', 'name') === $HANDLER);
ok('description is escaped (was interpolated raw at :46)',
    textOf($html, '//label') === $TAG);
ok('no script element was created', (new DOMXPath(dom($html)))->query('//script')->length === 0);
ok('no autofocus attribute materialised', !in_array('autofocus', attrNames($html)));
ok('textarea content survives exactly', textOf($html, '//textarea') === $TAG);

$taPlain = new Textarea();
$taPlain->name = 'description';
$taPlain->description = 'Description';
$taPlain->value = "Athletic's brews (<0.5% ABV)\n\nSecond paragraph.";
ok('newlines and < survive in the textarea body',
    textOf($taPlain->display(), '//textarea') === "Athletic's brews (<0.5% ABV)\n\nSecond paragraph.");

// ===========================================================================
section('GuidedStyleField — chunk 4');

$gs = new GuidedStyleField();
$gs->value           = $HANDLER;
$gs->styleId         = $ATTR_BREAKOUT;
$gs->parent          = $ENTITY;
$gs->description     = $TAG;
$gs->hint            = $ENTITY_DEC;
$gs->validState      = 'invalid';
$gs->validMsg        = $ENTITY_HEX;
$html = $gs->display();
if($verbose){ echo "    $html\n"; }

noInjectedAttrs('no attribute injected anywhere in the field', $html, array(
    'class', 'for', 'id', 'name', 'type', 'value', 'placeholder',
    'autocomplete', 'hidden', 'data-sf', 'aria-hidden',
));
ok('style value survives exactly',
    attrValue($html, '//input[@id="styleField"]', 'value') === $HANDLER);
ok('hidden style_id survives exactly',
    attrValue($html, '//input[@name="style_id"]', 'value') === $ATTR_BREAKOUT);
ok('label text shown literally', textOf($html, '//label') === $TAG);
ok('no script element was created', (new DOMXPath(dom($html)))->query('//script')->length === 0);

// ===========================================================================
section('DropDown — chunk 4 (already correct; guarding against regression)');

$dd = new DropDown();
$dd->name         = $HANDLER;
$dd->label        = $TAG;
$dd->values       = array($ATTR_BREAKOUT, 'b');
$dd->descriptions = array($ENTITY, 'B');
$dd->validState   = 'invalid';
$dd->validMsg     = $ENTITY_DEC;
$html = $dd->display();
if($verbose){ echo "    $html\n"; }

noInjectedAttrs('no attribute injected anywhere in the field', $html, array(
    'class', 'for', 'id', 'name', 'selected', 'value', 'aria-describedby', 'aria-hidden',
));
ok('option value survives exactly',
    attrValue($html, '//option[1]', 'value') === $ATTR_BREAKOUT);
ok('option text shown literally', textOf($html, '//option[1]') === $ENTITY);

// ===========================================================================
section('Checkbox — chunk 5');

// $text is developer-authored HTML by contract; everything else is escaped.
$cb = new Checkbox();
$cb->validState = 'invalid';
$cb->validMsg   = $ENTITY_DEC;
$html = $cb->display($HANDLER, '<a href="/terms">Terms &amp; Conditions</a>', $ATTR_BREAKOUT, true);
if($verbose){ echo "    $html\n"; }

// 'checked' is expected here, not injected: the variable is true and PHP's
// loose == makes any non-empty value match it.
noInjectedAttrs('no attribute injected by name/value/validMsg', $html, array(
    'class', 'for', 'id', 'name', 'type', 'value', 'href', 'aria-hidden', 'checked',
));
ok('checkbox value survives exactly',
    attrValue($html, '//input', 'value') === $ATTR_BREAKOUT);
ok('checkbox name survives exactly',
    attrValue($html, '//input', 'name') === $HANDLER);
ok('validMsg entity shown as text, not decoded',
    trim((string)textOf($html, '//div[@class="cbf-err"]')) === '!' . $ENTITY_DEC);
ok('validMsg is no longer wrapped in <p> (the CSS reset is gone with it)',
    (new DOMXPath(dom($html)))->query('//div[@class="cbf-err"]//p')->length === 0);

// The contract itself: the label renders as real markup.
ok('label HTML is honoured — the Terms link exists',
    attrValue($html, '//label/a', 'href') === '/terms');
ok('label ampersand entity renders as one character',
    textOf($html, '//label/a') === 'Terms & Conditions');

// The real signup label, verbatim from create-account.php.
$terms = new Checkbox();
$termsHtml = $terms->display('terms_agreement',
    'I agree to the <a href="/terms">Terms &amp; Conditions</a> for using this site.',
    true, false);
ok('signup Terms checkbox links to /terms',
    attrValue($termsHtml, '//label/a', 'href') === '/terms');
ok('signup Terms label reads correctly',
    textOf($termsHtml, '//label') === 'I agree to the Terms & Conditions for using this site.');
ok('unchecked when the variable is false',
    !str_contains($termsHtml, 'checked'));
$termsOn = new Checkbox();
ok('checked when the variable matches the value',
    str_contains($termsOn->display('terms_agreement', 'x', true, true), 'checked'));

// ===========================================================================
section('Alert — chunk 6');

// The contract: developer-authored HTML renders as markup.
$al = new Alert();
$al->type = 'success';
$al->msg  = '<strong>Success!</strong> Thanks. <a href="/beer/add/abc">Add another beer by Barley &amp; Hops</a>.';
$html = $al->display();
if($verbose){ echo "    $html\n"; }

ok('alert markup is honoured — the link exists',
    attrValue($html, '//a', 'href') === '/beer/add/abc');
ok('alert <strong> survives', textOf($html, '//strong') === 'Success!');
ok('alert entity renders as one character',
    textOf($html, '//a') === 'Add another beer by Barley & Hops');
ok('success type sets the ok variant', str_contains($html, 'cbf-alert--ok'));

$empty = new Alert();
$empty->msg = '';
ok('empty message renders nothing at all', $empty->display() === '');

// The obligation the contract creates: user data must be escaped BY THE CALLER
// at the interpolation point. This is what beer.php:226 and account.php:61 do.
$hostileName = 'Barley & Hops" onmouseover="alert(1)';
$safe = new Alert();
$safe->msg = '<a href="/beer/add/' . h('abc"><script>alert(1)</script>') . '">Add another beer by ' . h($hostileName) . '</a>.';
$html = $safe->display();

noInjectedAttrs('caller-escaped interpolation injects no attribute', $html, array(
    'class', 'role', 'href', 'aria-hidden',
));
ok('no onmouseover handler materialised', !in_array('onmouseover', attrNames($html)));
ok('no script element materialised',
    (new DOMXPath(dom($html)))->query('//script')->length === 0);
ok('the hostile name still reads correctly to a human',
    textOf($html, '//a') === 'Add another beer by ' . $hostileName);

// And the counter-case, documenting exactly what the contract does NOT do: an
// UNescaped interpolation is live markup. Asserted so nobody "discovers" it as
// a surprise. Note the payload has to OPEN A TAG — $msg lands in element body,
// not in an attribute, so a stray quote alone is inert there. That difference
// is the whole reason escaping is chosen by destination.
$bodyPayload = '<img src=x onerror="alert(1)">';
$unsafe = new Alert();
$unsafe->msg = 'Hello ' . $bodyPayload;
ok('UNescaped interpolation is live markup — caller escaping is mandatory',
    in_array('onerror', attrNames($unsafe->display())));
$escaped = new Alert();
$escaped->msg = 'Hello ' . h($bodyPayload);
ok('...and h() at the interpolation point makes it inert text',
    !in_array('onerror', attrNames($escaped->display()))
    && str_contains((string)textOf($escaped->display(), '//div/div'), $bodyPayload));

// ===========================================================================
echo "\n$pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
