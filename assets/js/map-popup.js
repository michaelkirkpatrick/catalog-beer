/*
 * cbMapPopup — shared InfoWindow body for map pins.
 *
 * Used by brewer.php, brewery-map.php and location.php so all three maps label
 * their pins identically. Most specific line first:
 *
 *     Taproom name     serif  -> /location/{id}   (only when it has one)
 *     Brewer name      sans   -> /brewer/{id}
 *     Address lines    sans   (plain text, optional)
 *
 * Most taprooms aren't named separately, so the common shape is just the brewer
 * over the address; the brewer takes the serif lead line whenever there's no
 * distinct name above it. The taproom line is dropped when it only repeats the
 * brewer name ("Ninkasi Brewing") or the city it sits in ("Portland") — both are
 * common naming habits and neither adds anything next to the brewer line and the
 * pin's own position. Pass the location's OWN name, never a composed
 * "{brewer} – {city}" stand-in, or the repeat won't be recognised.
 *
 * SAFETY: every name is written with textContent, never innerHTML, so a value
 * containing markup renders as text. The maps used to concatenate names straight
 * into an HTML string; the catalog is publicly editable, so that was a real
 * injection path rather than a theoretical one. Because textContent escapes,
 * callers must pass RAW API values — Text::get() output is entity-encoded and
 * would show up here as a literal "&#8217;".
 */
(function (global) {
    'use strict';

    // The 36-char hyphenated form the .htaccess routes expect. Anything else
    // gets no link at all, rather than a half-built href.
    var UUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

    function href(prefix, id) {
        return UUID.test(String(id == null ? '' : id)) ? prefix + id : '';
    }

    function row(className, text, linkHref) {
        var el = document.createElement('div');
        el.className = className;

        var sink = el;
        if (linkHref) {
            sink = document.createElement('a');
            sink.href = linkHref;
            el.appendChild(sink);
        }
        sink.textContent = text;
        return el;
    }

    function clean(value) {
        return String(value == null ? '' : value).trim();
    }

    // Names come from a publicly editable catalog, so compare loosely — case and
    // stray whitespace shouldn't decide whether a line is a duplicate.
    function same(a, b) {
        return a !== '' && b !== '' && a.toLowerCase() === b.toLowerCase();
    }

    /**
     * pin: { id, name, brewerID, brewerName, meta } — all optional.
     *   meta: string, or array of lines (rendered with white-space: pre-line).
     * Returns an HTMLElement for the InfoWindow `content` option.
     */
    global.cbMapPopup = function (pin) {
        pin = pin || {};

        var wrap = document.createElement('div');
        wrap.className = 'cb-mappop';

        var name = clean(pin.name);
        var brewer = clean(pin.brewerName);
        var city = clean(pin.city);
        var locationHref = href('/location/', pin.id);
        var brewerHref = href('/brewer/', pin.brewerID);

        // A taproom named after its brewer ("Ninkasi Brewing") or after the city
        // it sits in ("Portland") says nothing the brewer line and the pin's own
        // position on the map don't already say.
        var showName = name !== '' && !same(name, brewer) && !same(name, city);

        if (showName) {
            wrap.appendChild(row('cb-mappop__title', name, locationHref));
            if (brewer) {
                wrap.appendChild(row('cb-mappop__sub', brewer, brewerHref));
            }
        } else if (brewer) {
            // The usual case: no name of its own, so the brewer leads.
            wrap.appendChild(row('cb-mappop__title', brewer, brewerHref));
        }

        var meta = Array.isArray(pin.meta) ? pin.meta.map(clean).filter(Boolean).join('\n')
                                           : clean(pin.meta);
        if (meta) {
            wrap.appendChild(row('cb-mappop__meta', meta, ''));
        }

        return wrap;
    };
})(window);
