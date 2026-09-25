<?php
/**
 * HTML output escaping.
 *
 * The one rule this file exists to enforce: **escaping is a function of where
 * the value is going, not of what the value is.** A beer name is not "safe" or
 * "unsafe" in the abstract — it needs different treatment in an element body,
 * in an attribute, in a URL, and inside a <script>. Asking "is this HTML safe
 * to render?" (which is what HTML Purifier answered) is the wrong question, and
 * getting it wrong is what left a stored attribute-injection in
 * htmlHead::addDescription() for years.
 *
 * So: the API stores raw bytes, this site escapes at the moment of output, and
 * a value is escaped exactly ONCE. Never store escaped, never hand escaped
 * output to something that escapes again. (Passing an already-escaped name to
 * Navigation::breadcrumbs() is what used to render a literal
 * "Bob&#8217;s Brewery".)
 *
 * Pick by destination:
 *
 *   element body AND attribute   h($v)
 *   URL path / query segment     rawurlencode($v), then h() if it lands in an
 *                                attribute
 *   inside <script>              json_encode($v, JSON_HEX_TAG | JSON_HEX_AMP
 *                                | JSON_HEX_APOS | JSON_HEX_QUOT)
 *   href                         confirm the scheme is http/https first, then h()
 *
 * Usage:
 *   echo '<h1>' . h($brewer->name) . '</h1>';
 *   echo '<input value="' . h($brewer->name) . '">';
 */

/**
 * Escape a value for HTML, safe in both element-body and attribute context.
 *
 * ENT_QUOTES is the whole point: it escapes single AND double quotes, which is
 * what makes one function correct in both contexts. Purifier's generator used
 * ENT_NOQUOTES because it assumed element content, so every attribute sink it
 * fed was one raw quote away from attribute injection.
 *
 * ENT_SUBSTITUTE matters more than it looks. Passing flags explicitly overrides
 * PHP 8.1+'s defaults, and without it htmlspecialchars() returns an EMPTY
 * STRING for malformed UTF-8 — a field that silently blanks itself rather than
 * showing a replacement character. The API rejects bad encoding at the door
 * (TextInput::check), but this is the backstop and it costs nothing.
 *
 * The (string) cast covers null: PHP 8.1+ deprecates passing null to
 * htmlspecialchars(), and API fields are routinely absent.
 *
 * @param mixed $value  Raw value, straight from the API or the database
 * @return string       Escaped for HTML
 */
function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
