/**
 * Address autocomplete for the location forms (location-add, location-edit).
 *
 * Progressive enhancement over the existing fields: with JS off, a missing API
 * key, or Places unavailable, the form is exactly the six controls it always
 * was. Nothing here is required to submit.
 *
 * WHY IT SITS IN THE STREET FIELD, AND WHY IT SEARCHES BUSINESSES TOO
 * The person filling this in is transcribing a BREWERY's address off the
 * brewery's own website — not typing an address they know by heart. So the
 * useful query is "Worthy Brewing Bend", not a street they'd have to find
 * first. Picking an establishment gets us the street, city, state, ZIP, the
 * phone number and the website in one selection: the whole address section
 * plus two fields above it. Plain address predictions still work for anyone
 * who has the street to hand, so the field keeps its own name.
 *
 * We deliberately do NOT fill the location Name field from displayName. Name
 * is optional precisely because most taprooms have no name of their own, and
 * autofilling "Worthy Brewing" there would recreate the duplicate-of-the-brewer
 * data the field was made optional to get rid of. Same for a URL the person has
 * already typed — a suggestion never overwrites something they entered.
 *
 * API: Places (New) via AutocompleteSuggestion.fetchAutocompleteSuggestions().
 * The old google.maps.places.Autocomplete widget would have been fewer lines,
 * but it renders its own input — it can't attach to the field InputField built,
 * and it's closed to new customers as of March 2025. Fetching suggestions as
 * data is what lets the dropdown be ours: our markup, our tokens, our a11y.
 *
 * BILLING: session tokens group the keystrokes and the final selection into one
 * billable autocomplete session. One token per search, discarded once
 * fetchFields() runs — reusing it after that silently un-groups the session and
 * bills each keystroke separately.
 *
 * ATTRIBUTION: Google's terms require a "Powered by Google" credit wherever
 * predictions are shown outside a Google map. It's the last row of the listbox.
 * Don't remove it.
 */
(function () {
    'use strict';

    var MIN_CHARS = 3;
    var DEBOUNCE_MS = 250;
    var LABEL_ENHANCED = 'Street Address — or search for the taproom by name';
    var PLACEHOLDER_ENHANCED = 'e.g. Worthy Brewing Bend, or 495 NE Bellevue Dr';

    var input, form, wrap, list, status;
    var places = null;
    var token = null;
    var suggestions = [];
    var activeIndex = -1;
    var timer = null;
    var requestSeq = 0;
    // Set while fill() writes the street field. Filling dispatches a real input
    // event (so anything watching the field sees an edit), and without this the
    // search listener treats our own write as typing and re-opens the list on
    // top of the address it just chose.
    var filling = false;
    // What the last selection wrote, keyed by field name. See setValue().
    var written = {};

    /* ---------- small helpers ---------- */

    function field(name) {
        return form ? form.querySelector('[name="' + name + '"]') : null;
    }

    /* Write a field on behalf of a selection, and remember we wrote it.
       An empty value means the chosen place doesn't have this component, which
       has to CLEAR the field rather than skip it: pick a place with a unit
       number, then one without, and a skip would leave the first place's unit
       sitting in the second place's address. Half of one address and half of
       another is the one outcome worse than an incomplete form.
       Only our own leftovers get cleared, though — `written` holds what we last
       put in each field, so a value that no longer matches has been edited by
       the person filling the form and is theirs to keep. */
    function setValue(name, value) {
        var el = field(name);
        if (!el) { return; }
        if (value === '' && (!written.hasOwnProperty(name) || el.value !== written[name])) {
            return;
        }
        if (el.value === value) {
            written[name] = value;
            return;
        }
        el.value = value;
        written[name] = value;
        // Anything watching the field (validation, other scripts) should see this
        // as a real edit, not a value that appeared from nowhere.
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function component(components, type) {
        for (var i = 0; i < components.length; i++) {
            if (components[i].types && components[i].types.indexOf(type) !== -1) {
                return components[i];
            }
        }
        return null;
    }

    function longText(component) { return component ? (component.longText || '') : ''; }
    function shortText(component) { return component ? (component.shortText || '') : ''; }

    function announce(message) {
        if (status) { status.textContent = message; }
    }

    /* ---------- the listbox ---------- */

    function render() {
        list.textContent = '';
        activeIndex = -1;

        if (suggestions.length === 0) {
            close();
            return;
        }

        suggestions.forEach(function (suggestion, index) {
            var prediction = suggestion.placePrediction;
            var item = document.createElement('li');
            item.className = 'cb-ac__item';
            item.id = 'cbAcOption' + index;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');

            var name = document.createElement('span');
            name.className = 'cb-ac__name';
            name.textContent = prediction.mainText ? prediction.mainText.text : prediction.text.text;
            item.appendChild(name);

            if (prediction.secondaryText) {
                var meta = document.createElement('span');
                meta.className = 'cb-ac__meta';
                meta.textContent = prediction.secondaryText.text;
                item.appendChild(meta);
            }

            // mousedown, not click: click fires after blur, by which point the
            // list has already closed underneath the pointer.
            item.addEventListener('mousedown', function (event) {
                event.preventDefault();
                choose(index);
            });
            list.appendChild(item);
        });

        var credit = document.createElement('li');
        credit.className = 'cb-ac__credit';
        credit.setAttribute('aria-hidden', 'true');
        credit.textContent = 'powered by Google';
        list.appendChild(credit);

        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        announce(suggestions.length + (suggestions.length === 1 ? ' suggestion' : ' suggestions') + ' available.');
    }

    function close() {
        list.hidden = true;
        list.textContent = '';
        activeIndex = -1;
        suggestions = [];
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    }

    function highlight(index) {
        var items = list.querySelectorAll('.cb-ac__item');
        if (items.length === 0) { return; }
        if (activeIndex >= 0 && items[activeIndex]) {
            items[activeIndex].classList.remove('is-active');
            items[activeIndex].setAttribute('aria-selected', 'false');
        }
        activeIndex = (index + items.length) % items.length;
        items[activeIndex].classList.add('is-active');
        items[activeIndex].setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', items[activeIndex].id);
        items[activeIndex].scrollIntoView({ block: 'nearest' });
    }

    /* ---------- query ---------- */

    function search() {
        var value = input.value.trim();
        if (value.length < MIN_CHARS) {
            close();
            return;
        }
        if (!token) {
            token = new places.AutocompleteSessionToken();
        }

        var seq = ++requestSeq;
        places.AutocompleteSuggestion.fetchAutocompleteSuggestions({
            input: value,
            sessionToken: token,
            // US-only, because the address form is: the state control holds the
            // 50 states and country_code is a disabled "US". Drop this line the
            // day the form can express somewhere else.
            includedRegionCodes: ['us'],
            language: 'en-US',
            region: 'us'
            // A locationBias circle would go here. Nothing to bias toward yet:
            // on add we don't know where the taproom is, and on edit the address
            // is already filled in.
        }).then(function (response) {
            // A slower earlier request must not overwrite a newer one's results.
            if (seq !== requestSeq) { return; }
            suggestions = (response && response.suggestions ? response.suggestions : []).filter(function (s) {
                return s.placePrediction;
            });
            render();
        }).catch(function (error) {
            if (seq !== requestSeq) { return; }
            // A failure here is never worth blocking on — the fields still work.
            // Most likely cause is Places API (New) not enabled on the key.
            console.warn('Address autocomplete unavailable:', error && error.message ? error.message : error);
            close();
        });
    }

    /* ---------- selection ---------- */

    function choose(index) {
        var suggestion = suggestions[index];
        if (!suggestion) { return; }
        var place = suggestion.placePrediction.toPlace();

        // Drop the pending keystroke and orphan any request still in flight —
        // either would re-open the list a moment after this closes it.
        window.clearTimeout(timer);
        requestSeq++;
        close();
        announce('Looking up address…');

        place.fetchFields({
            fields: ['addressComponents', 'formattedAddress', 'nationalPhoneNumber', 'websiteUri']
        }).then(function () {
            fill(place);
        }).catch(function (error) {
            console.warn('Address lookup failed:', error && error.message ? error.message : error);
            announce('Couldn’t load that address. Please fill the fields in by hand.');
        }).then(function () {
            // fetchFields() ends the billable session either way; the next search
            // has to start a new one.
            token = null;
        });
    }

    function fill(place) {
        var components = place.addressComponents || [];
        filling = true;

        // shortText for the route: "NE Bellevue Dr", not "Northeast Bellevue
        // Drive". That's the USPS-preferred abbreviation, which is both what
        // /address validation normalises to and what's already in the table.
        var streetNumber = shortText(component(components, 'street_number'));
        var route = shortText(component(components, 'route'));
        var street = (streetNumber + ' ' + route).trim();
        if (street === '' && place.formattedAddress) {
            // An establishment can come back without a street_number. Its first
            // formatted line beats leaving the required field empty.
            street = place.formattedAddress.split(',')[0].trim();
        }
        if (street !== '') {
            input.value = street;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }

        setValue('address1', longText(component(components, 'subpremise')));

        var city = component(components, 'locality')
            || component(components, 'postal_town')
            || component(components, 'sublocality_level_1')
            || component(components, 'neighborhood');
        setValue('city', longText(city));

        // "US-OR". Only when the state is one the dropdown actually offers — a
        // value it doesn't have would silently select nothing. The shape test
        // comes first: `state` is Google's string, and interpolating one that
        // held a quote would throw out of querySelector and abandon the fields
        // below. Two letters is also exactly what the dropdown's values are.
        var country = shortText(component(components, 'country'));
        var state = shortText(component(components, 'administrative_area_level_1'));
        var stateField = field('sub_code');
        var subCode = '';
        if (country === 'US' && /^[A-Za-z]{2}$/.test(state) && stateField
            && stateField.querySelector('option[value="US-' + state.toUpperCase() + '"]')) {
            subCode = 'US-' + state.toUpperCase();
        }
        // '' clears to the dropdown's "-- Choose --" option, which is a real
        // option with an empty value, so this can't leave it unselectable.
        setValue('sub_code', subCode);

        var zip = longText(component(components, 'postal_code'));
        var zip4 = longText(component(components, 'postal_code_suffix'));
        setValue('zip', (zip !== '' && zip4 !== '') ? zip + '-' + zip4 : zip);

        // Stored as ten digits — strip the formatting Google returns.
        var digits = place.nationalPhoneNumber ? place.nationalPhoneNumber.replace(/\D/g, '') : '';
        setValue('telephone', digits.length === 10 ? digits : '');

        // Only into an empty box, or over one a previous suggestion filled.
        // Someone who typed a taproom-specific URL meant it, and Places would
        // hand back the brewery's front page.
        var urlField = field('url');
        if (urlField && (urlField.value.trim() === '' || urlField.value === written.url)) {
            setValue('url', place.websiteUri || '');
        }

        announce('Address filled in. Please check it before saving.');
        filling = false;
    }

    /* ---------- wiring ---------- */

    function init() {
        input = document.querySelector('[data-cb-address-autocomplete]');
        if (!input) { return; }
        form = input.form;

        if (!(window.google && google.maps && google.maps.places
            && google.maps.places.AutocompleteSuggestion)) {
            // Places didn't load. Leave the plain fields exactly as they are.
            return;
        }
        places = google.maps.places;

        wrap = input.parentNode;
        wrap.classList.add('cb-ac');

        list = document.createElement('ul');
        list.className = 'cb-ac__list';
        list.id = 'cbAcList';
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', 'Address suggestions');
        list.hidden = true;
        wrap.appendChild(list);

        status = document.createElement('div');
        status.className = 'cb-sr-only';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        wrap.appendChild(status);

        // Say what the field can do now that it can do it — the server-rendered
        // label has to stay honest for the no-JS case.
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', 'cbAcList');
        input.setAttribute('aria-autocomplete', 'list');
        input.placeholder = PLACEHOLDER_ENHANCED;
        var label = form ? form.querySelector('label[for="' + input.id + '"]') : null;
        if (label) { label.textContent = LABEL_ENHANCED; }

        input.addEventListener('input', function () {
            if (filling) { return; }
            window.clearTimeout(timer);
            timer = window.setTimeout(search, DEBOUNCE_MS);
        });

        input.addEventListener('keydown', function (event) {
            if (list.hidden) {
                return;
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlight(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(activeIndex - 1);
            } else if (event.key === 'Enter') {
                if (activeIndex >= 0) {
                    // Only swallow the Enter that picks a suggestion; with
                    // nothing highlighted it still submits the form.
                    event.preventDefault();
                    choose(activeIndex);
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                close();
            }
        });

        input.addEventListener('blur', function () {
            // After the item's mousedown has had its turn.
            window.setTimeout(close, 120);
        });
    }

    // Named callback for the Maps JS loader (see addressAutocompleteScripts()).
    window.cbAddressAutocompleteInit = init;
})();
