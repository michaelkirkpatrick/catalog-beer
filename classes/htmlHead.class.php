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
        $text = new Text(false, false, true);
        $pageTitle = $text->get($pageTitle);
        $html = str_replace('##PAGETITLE##', $pageTitle, $html);

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
            . cssTag('/assets/css/catalog-components.css');
        $html = str_replace('##DESIGNSYSTEMCSS##', $designSystemCSS, $html);

        // Fathom Analytics (production only)
        $fathom = '';
        if(defined('ENVIRONMENT') && ENVIRONMENT === 'production'){
            $fathom = "<!-- Fathom Analytics -->\n\t" . '<script src="https://cdn.usefathom.com/script.js" data-site="YRZMNYXM" defer></script>';
        }
        $this->html = str_replace('##FATHOM##', $fathom, $html);
    }
    
    // Append a page-specific stylesheet (loads after catalog.css). Versioned via
    // cssTag() so a local edit busts the year-long immutable cache; an off-disk
    // path degrades to an unversioned link (see assets.php).
    function addStylesheet($href){
        $link = "\t" . cssTag($href) . "\n";
        $this->html = str_replace('</head>', $link . '</head>', $this->html);
    }

    // Mark the page noindex,follow — for thin/derived pages (search results)
    // that shouldn't compete with the real pages in search engines.
    function noindex(){
        $meta = "\t" . '<meta name="robots" content="noindex,follow">' . "\n";
        $this->html = str_replace('</head>', $meta . '</head>', $this->html);
    }

    function addDescription($description){
        if(!empty($description)){
            $text = new Text(false, false, true);
            $description = $text->get($description);
            $metaDescription = '<meta charset="UTF-8">' . "\n\t" . '<meta name="description" content="' . $description . '" />';
            $this->html = str_replace('<meta charset="utf-8">', $metaDescription, $this->html);
        }
    }
}
?>