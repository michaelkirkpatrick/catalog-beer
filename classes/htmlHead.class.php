<?php
/* ---
// HTML Head
$htmlHead = new htmlHead('PageName');
echo $htmlHead->html;
--- */
class htmlHead {
    
    public $html;
    
    function __construct($pageTitle){
        // HTML Header
        $html = file_get_contents(ROOT . '/classes/resources/head.html');
        // <title> is RCDATA: "<" and "&" are the characters that matter, and
        // character references are still decoded, so h()'s escaped quotes render
        // as the quotes the brewery typed. Callers pass RAW API values — do not
        // hand this pre-escaped text or the title reads "Bob&#8217;s Brewery".
        $html = str_replace('##PAGETITLE##', h($pageTitle), $html);

        // Bootstrap, self-hosted rather than CDN: same bytes, but no DNS+TLS
        // handshake to a third party on the render-blocking path, and it rides the
        // same ?v=<mtime> immutable caching as everything else. Vendored copy is
        // byte-identical to bootstrap@5.3.3 — re-verify with:
        //   openssl dgst -sha384 -binary assets/css/bootstrap.min.css | openssl base64 -A
        //   => QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH
        $html = str_replace('##BOOTSTRAPCSS##', cssTag('/assets/css/bootstrap.min.css'), $html);

        // Design-system stylesheets, versioned for cache-busting (tokens+bridge
        // first, then shared primitives — order matters, see the design system
        // layering in CLAUDE.md). Emitted here because head.html is static and
        // can't run cssTag() itself.
        $designSystemCSS = cssTag('/assets/css/catalog.css') . "\n\t"
            . cssTag('/assets/css/catalog-components.css') . "\n\t"
            . cssTag('/assets/css/catalog-forms.css');
        $html = str_replace('##DESIGNSYSTEMCSS##', $designSystemCSS, $html);

        // Fathom Analytics (production only)
        $fathom = '';
        if(defined('ENVIRONMENT') && ENVIRONMENT === 'production'){
            $fathom = "<!-- Fathom Analytics -->\n\t" . '<script src="https://cdn.usefathom.com/script.js" data-site="YRZMNYXM" defer></script>';
        }
        $html = str_replace('##FATHOM##', $fathom, $html);

        // Self-referencing canonical. Search Console filed 3,000+ URLs as
        // "Duplicate without user-selected canonical" (Sep 2026): www, http,
        // trailing-slash (/api-docs/ vs /api-docs), and stray query-string
        // variants all serve the same page with nothing declaring which is
        // real. The canonical is rebuilt from the request path, so www and the
        // trailing slash collapse; every query parameter is dropped except the
        // list pages' ?page=N, which is the only one that selects distinct
        // content. Staging gets its own host so a noindex page never canonicals
        // to a production URL (Google treats that as mixed signals).
        $this->html = str_replace('</head>', "\t" . '<link rel="canonical" href="' . h(self::canonicalURL()) . '">' . "\n" . '</head>', $html);
    }

    static function canonicalURL(){
        $host = (defined('ENVIRONMENT') && ENVIRONMENT === 'staging') ? 'https://staging.catalog.beer' : 'https://catalog.beer';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = '/' . trim((string)$path, '/');
        $query = '';
        if(isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 1){
            $query = '?page=' . (int)$_GET['page'];
        }
        return $host . $path . $query;
    }
    
    // Append a page-specific stylesheet (loads after catalog.css). Versioned via
    // cssTag() so a local edit busts the year-long immutable cache; an off-disk
    // path degrades to an unversioned link (see assets.php).
    function addStylesheet($href){
        $link = "\t" . cssTag($href) . "\n";
        $this->html = str_replace('</head>', $link . '</head>', $this->html);
    }

    // Mark the page noindex,follow — for thin/derived pages (search results,
    // login, 404) that shouldn't compete with the real pages in search engines.
    // Also drops the canonical: noindex plus a canonical is a mixed signal.
    function noindex(){
        $this->html = preg_replace('/\t<link rel="canonical" href="[^"]*">\n/', '', $this->html, 1);
        $meta = "\t" . '<meta name="robots" content="noindex,follow">' . "\n";
        $this->html = str_replace('</head>', $meta . '</head>', $this->html);
    }

    // Embed a JSON-LD structured-data block before </head>. JSON_HEX_TAG so a
    // literal "</script>" in any value can't break out of the script element.
    function addJsonLd(array $data){
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
        $script = "\t" . '<script type="application/ld+json">' . $json . '</script>' . "\n";
        $this->html = str_replace('</head>', $script . '</head>', $this->html);
    }

    // Set the meta description. THIS is where the stored attribute-injection
    // lived: it ran the value through HTML Purifier, whose generator escapes text
    // nodes with ENT_NOQUOTES — right for element content, wrong here, because
    // the value lands in an attribute. A brewer short_description of
    //   0;url=https://evil.example" http-equiv="refresh
    // closed content="" and hung a second attribute on the same tag, and browsers
    // honour http-equiv even when name is present: the page redirected. h()'s
    // ENT_QUOTES is the whole fix.
    function addDescription($description){
        if(!empty($description)){
            $meta = "\t" . '<meta name="description" content="' . h($description) . '" />' . "\n";
            // Insert before </head>, like every other method here. This used to
            // str_replace the charset tag with an uppercased copy of itself plus
            // the description — which left the charset intact but meant a second
            // addDescription() call silently did nothing (the lowercase needle was
            // gone), and it kept the charset declaration hostage to an unrelated
            // method. The charset stays where head.html puts it, first in <head>
            // and well inside the 1024 bytes browsers sniff.
            $this->html = str_replace('</head>', $meta . '</head>', $this->html);
        }
    }
}
?>