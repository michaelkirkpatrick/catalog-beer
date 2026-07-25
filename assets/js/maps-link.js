/* Apple platforms get the maps.apple.com href, which hands off to Maps.app on
   iOS and macOS; everyone else keeps the Google URL already in the markup, which
   opens the Google Maps app on Android and the web elsewhere. Done in JS rather
   than by sniffing the UA server-side because the pages are served straight from
   Apache with no cache layer to vary on.

   Markup contract: <a data-maps-link href="{google}" data-apple-href="{apple}">.
   Both hrefs come from locationMapsLinks() in classes/helpers/location.php. */
(function () {
    if (!/iPhone|iPad|iPod|Macintosh/.test(navigator.userAgent)) { return; }
    var links = document.querySelectorAll('a[data-maps-link][data-apple-href]');
    for (var i = 0; i < links.length; i++) {
        links[i].href = links[i].getAttribute('data-apple-href');
    }
})();
