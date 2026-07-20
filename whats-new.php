<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('What\'s New');
echo $htmlHead->html;
?>
<style>
    .cb-article h2 {
        margin-top: 3rem;
    }
    .cb-article h3 {
        margin-top: 2.5rem;
    }
    .cb-article-date {
        color: #6c757d;
        margin-bottom: 2rem;
    }
</style>
<body>
    <?php
    // Navbar
    echo $nav->navbar('');
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
            <!-- Empty Column -->
            </div>
            <div class="col-md-8 cb-article">
                <h1>What&#8217;s New</h1>

                <p>News and notable changes from the Catalog.beer project, newest first.</p>

                <!-- Entries are ordered newest first. To add one, copy the <article> block below. -->

                <article>
                    <h2>Search the styles by any name they go by</h2>
                    <p class="cb-article-date">July 19, 2026</p>

                    <p>Nobody calls it a &#8220;Juicy or Hazy India Pale Ale.&#8221; They call it a NEIPA, or a hazy, or a New England IPA &#8212; and until now, searching the style list for any of those found you nothing. Style search is new to the API, and it looks through every name a style goes by, not just its official one. Search for &#8220;NEIPA&#8221; and you get the right style, even though those five letters appear nowhere in its name.</p>

                    <p>Broad searches get a broader answer. &#8220;IPA&#8221; isn&#8217;t one style, it&#8217;s eleven, so searching for it returns the whole India Pale Ale family alongside the individual styles &#8212; you can offer someone the group rather than guess which one they meant. And results are ordered with an eye on what breweries actually brew, so a search for &#8220;stout&#8221; leads with the stouts you&#8217;ll find on shelves rather than ones that exist only on paper.</p>

                    <h3>For developers</h3>

                    <p>Send a <strong>GET</strong> request to <code>/style/search?q=</code> and you&#8217;ll get back matching styles ranked by relevance, plus any matching family. Alias matches are first-class, results are cursor-paginated like every other list endpoint, and each row carries the same fields as <a href="/api-docs#style-list">List Styles</a>. Full details in the <a href="/api-docs#style-search">Search Styles</a> section of the documentation.</p>

                    <p>It&#8217;s the endpoint to reach for if you&#8217;re matching a brewery&#8217;s own style name against the standard list &#8212; a menu that says &#8220;Hazy Double&#8221; or &#8220;CDA&#8221; will now find its way to the right style without you writing the synonym table yourself.</p>

                    <h3>A note for AI agents</h3>

                    <p>If you&#8217;re an agent reading this, or a developer building one: there&#8217;s an <a href="/llms.txt">llms.txt</a> describing the catalog and pointing at the API. Please use the API rather than scraping these pages. It&#8217;s free for a thousand requests a month, it returns clean JSON, and it won&#8217;t break the next time we change our markup.</p>

                    <p>Our <a href="/robots.txt">robots.txt</a> spells out where we stand: search engines and agents fetching a page to answer someone&#8217;s question are welcome. Crawlers harvesting the whole catalog into a training set are not. We&#8217;re glad to answer questions and glad to be cited &#8212; we&#8217;d just rather not be quietly absorbed.</p>
                </article>

                <article>
                    <h2>A field guide to beer styles</h2>
                    <p class="cb-article-date">July 11, 2026</p>

                    <p>The standard list of styles behind every beer is now something you can explore. Visit <a href="/style">Beer Styles</a> to browse them all, grouped into 26 families across ale, lager, cider, mead, and perry. Each family is a card whose band of color is the real spread of shades you&#8217;ll find inside &#8212; from pale straw to black &#8212; so you can find your way around by eye.</p>

                    <p>Every style has its own page: a tasting sheet with a plain-language description, what to expect in the glass (appearance, aroma, flavor, and mouthfeel), its color and vital statistics (alcohol, bitterness, and gravity), a short history, a few defining commercial examples, and the guideline sources behind it all. The figures come straight from the <a href="https://www.brewersassociation.org/edu/brewers-association-beer-style-guidelines/" target="_blank" rel="noopener">Brewers Association</a> and <a href="https://www.bjcp.org/bjcp-style-guidelines/" target="_blank" rel="noopener">BJCP</a> guidelines &#8212; and where a style has no fixed limit, such as an imperial stout that can always be darker, we say &#8220;40+&#8221; rather than invent a ceiling.</p>

                    <p>Reading about a beer? Its style name is now a link &#8212; follow it to learn what the style is, then explore the rest of its family. Start anywhere and follow the color.</p>
                </article>

                <article>
                    <h2>A fresh look and smarter beer styles</h2>
                    <p class="cb-article-date">July 9, 2026</p>

                    <p>Two big updates arrived this summer: a redesign of the whole site, and a new way of recording beer styles that keeps a brewer&#8217;s own words while making the entire catalog easier to search, sort, and count.</p>

                    <h3>The new look</h3>

                    <p>Every page on Catalog.beer has been redesigned &#8212; warmer colors, more comfortable type, and a cleaner layout that puts the beer first. The navigation bar now shows a live count of the brewers and beers in the database, and the search box on the homepage takes you straight to any brewer, beer, or location.</p>

                    <h3>Say it your way</h3>

                    <p>Brewers name beer styles however they like &#8212; and they should. A &#8220;Hazy Double&#8221; reads great on a menu, but a computer can&#8217;t tell it&#8217;s a kind of IPA. So Catalog.beer now records a beer&#8217;s style in two parts: the style name exactly as the brewer wrote it, which is what everyone sees, and a spot in a standard list of styles, which is what lets the catalog answer questions like &#8220;show me every IPA.&#8221;</p>

                    <p>The standard list holds 196 styles drawn from the <a href="https://www.brewersassociation.org/edu/brewers-association-beer-style-guidelines/" target="_blank" rel="noopener">Brewers Association</a> and <a href="https://www.bjcp.org/bjcp-style-guidelines/" target="_blank" rel="noopener">Beer Judge Certification Program</a> guidelines, and it covers beer, cider, perry, and mead. The new Style field on the add-a-beer and edit-a-beer pages does the matching for you as you type &#8212; it knows that &#8220;NEIPA,&#8221; &#8220;New England IPA,&#8221; and &#8220;Juicy IPA&#8221; are all the same style. Only know it&#8217;s some kind of IPA? File it at the IPA family and move on. And when a beer truly fits nowhere, there&#8217;s always a catch-all &#8212; no beer is turned away for being unusual.</p>

                    <h3>For developers</h3>

                    <p>The style vocabulary is part of the API. New read-only <code>/style</code> endpoints return every style, family, and class, along with the other names and spellings that resolve to each &#8212; everything you need to build your own style picker or check a label before submitting a beer. The <a href="/api-docs#styles">Styles section of the API documentation</a> has the details.</p>

                    <h3>Help us file the catalog</h3>

                    <p>Catalog.beer is community-built and free to build on. If you spot a beer filed under the wrong style, <a href="/login">sign in</a> and fix it &#8212; or <a href="/signup">create an account</a> and start cataloging.</p>
                </article>

            </div>
            <div class="col-md-2">
            <!-- Empty Column -->
            </div>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
