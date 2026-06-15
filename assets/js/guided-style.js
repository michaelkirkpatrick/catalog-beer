/* =============================================================================
   guided-style.js — Catalog.beer guided style input (vanilla JS, Bootstrap 5)
   -----------------------------------------------------------------------------
   Progressive enhancement for the beer add/edit "Style" field. Turns a plain
   text input into a guided combobox that resolves the brewer's free text to a
   canonical style_id while preserving their raw wording (style_label).

   Markup contract (see GuidedStyleField.class.php):
     <div class="cb-style" data-cb-style>
       <input class="form-control" name="style_label" autocomplete="off" ...>
       <input type="hidden" name="style_id">
       <input type="hidden" name="beverage_type">
       <div class="cb-menu" hidden></div>
     </div>
     <div class="form-text cb-status"></div>     <!-- sibling, resolution note -->

   Data: window.CB_STYLES = [{id,name,category,bev,ca,al:[aliases]}]
   (inlined server-side from GET /style; the DB is the source of truth).

   Backend mirror: resolve typed text -> style_id via (1) exact name, (2) alias,
   (3) explicit pick, else a chosen catch-all. Stores style_id + style_label +
   beverage_type. Nothing legitimate is rejected.
   ========================================================================== */
(function () {
  'use strict';

  // The three general beer catch-alls offered first on no-match. Cider/mead
  // catch-alls are derived from the data (ca === true) per beverage_type, so the
  // no-match menu is beverage-aware: an unmatched cider/mead can still be filed
  // correctly instead of defaulting to a beer bucket.
  var GENERAL_BEER_CATCHALLS = ['specialty-beer', 'experimental-beer', 'historical-beer'];
  var CHECK = '<svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor" class="me-1" style="vertical-align:-1px"><path d="M13.485 1.929a.75.75 0 0 1 .022 1.06l-7.25 7.5a.75.75 0 0 1-1.082 0l-3.25-3.364a.75.75 0 1 1 1.08-1.04l2.71 2.804 6.71-6.94a.75.75 0 0 1 1.06-.02z"/></svg>';

  function norm(s) { return (s || '').toString().toLowerCase().trim(); }
  function esc(s) {
    return (s || '').replace(/[&<>"]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
  }
  function highlight(text, q) {
    if (!q) return esc(text);
    var i = text.toLowerCase().indexOf(q.toLowerCase());
    if (i < 0) return esc(text);
    return esc(text.slice(0, i)) + '<mark>' + esc(text.slice(i, i + q.length)) + '</mark>' + esc(text.slice(i + q.length));
  }

  // --- matching ------------------------------------------------------------
  // Returns ranked, non-catch-all matches. Each: {s, via} where via is the
  // alias that matched (if the name itself didn't).
  function search(styles, q) {
    q = norm(q);
    if (!q) return [];
    var out = [];
    for (var i = 0; i < styles.length; i++) {
      var s = styles[i];
      if (s.ca) continue;   // catch-alls are offered only on no-match, not in normal results
      var n = norm(s.name), score = -1, via = null;
      if (n.indexOf(q) === 0) score = 0;
      else if (n.indexOf(q) > 0) score = 3;
      for (var j = 0; j < s.al.length; j++) {
        var a = norm(s.al[j]);
        if (a.indexOf(q) === 0 && (score < 0 || score > 1)) { score = 1; via = s.al[j]; }
        else if (a.indexOf(q) > 0 && score < 0) { score = 4; via = s.al[j]; }
      }
      if (score >= 0) out.push({ s: s, via: via, score: score });
    }
    out.sort(function (x, y) { return x.score - y.score || x.s.name.localeCompare(y.s.name); });
    return out.slice(0, 10);
  }

  function exactMatch(styles, q) {
    q = norm(q);
    if (!q) return null;
    for (var i = 0; i < styles.length; i++) {
      var s = styles[i];
      if (s.ca) continue;
      if (norm(s.name) === q) return s;
      for (var j = 0; j < s.al.length; j++) if (norm(s.al[j]) === q) return s;
    }
    return null;
  }

  // --- component -----------------------------------------------------------
  function enhance(root) {
    var styles = window.CB_STYLES || [];

    // Build the beverage-aware no-match offer: 3 general beer catch-alls, then
    // the cider and mead general catch-alls (derived from the data).
    function pickIds(ids) {
      return ids.map(function (id) {
        return styles.filter(function (s) { return s.id === id; })[0];
      }).filter(Boolean);
    }
    function catchAllsFor(bev) {
      return styles.filter(function (s) { return s.ca && s.bev === bev; });
    }
    var catchallGroups = [
      { label: 'Beer', items: pickIds(GENERAL_BEER_CATCHALLS) },
      { label: 'Cider', items: catchAllsFor('cider') },
      { label: 'Mead', items: catchAllsFor('mead') }
    ].filter(function (g) { return g.items.length; });

    var input = root.querySelector('input[name="style_label"]');
    var hidId = root.querySelector('input[name="style_id"]');
    var hidBev = root.querySelector('input[name="beverage_type"]');
    var menu = root.querySelector('.cb-menu');
    var status = (root.parentNode || document).querySelector('.cb-status');
    var hint = (input.getAttribute('data-hint') || 'Start typing to find a canonical style. Your exact wording is always kept.');

    var sel = [];     // flat list of selectable entries currently in the menu
    var active = -1;  // index into sel

    function setStatus(kind, html) {
      if (!status) return;
      status.className = 'form-text cb-status ' + (kind === 'ok' ? 'text-success' : kind === 'warn' ? 'text-warning-emphasis' : 'text-muted');
      status.innerHTML = html;
    }
    function resolved(style, label, isCatch) {
      hidId.value = style.id;
      if (hidBev) hidBev.value = style.bev || 'beer';
      var msg = isCatch
        ? CHECK + 'Filed under <strong>' + esc(style.name) + '</strong> — a non-standard style'
        : CHECK + 'Will be filed as <strong>' + esc(style.name) + '</strong>';
      if (label && norm(label) !== norm(style.name)) msg += ' · your label “' + esc(label) + '” is kept';
      setStatus('ok', msg);
    }
    function unresolved() { hidId.value = ''; if (hidBev) hidBev.value = ''; }

    function close() { menu.hidden = true; menu.innerHTML = ''; sel = []; active = -1; }

    function render(q) {
      var matches = search(styles, q);
      var html = '', idx = 0;
      sel = [];
      if (q && matches.length) {
        var lastCat = null;
        for (var i = 0; i < matches.length; i++) {
          var m = matches[i], s = m.s;
          if (s.category !== lastCat) {
            html += '<div class="cb-group">' + esc(s.category) + '</div>';
            lastCat = s.category;
          }
          var aliasNote = m.via ? '<span class="cb-alias">matched “' + highlight(m.via, q) + '”</span>' : '';
          html += '<button type="button" class="cb-opt" role="option" data-idx="' + idx + '">' +
            '<span class="cb-opt-name">' + highlight(s.name, q) + '</span>' + aliasNote + '</button>';
          sel.push({ kind: 'style', style: s });
          idx++;
        }
      } else if (q) {
        html += '<div class="cb-nomatch">No standard style matches “<strong>' + esc(q) + '</strong>.” ' +
          'File it under a catch-all — your wording is kept exactly:</div>';
        for (var gi = 0; gi < catchallGroups.length; gi++) {
          var grp = catchallGroups[gi];
          html += '<div class="cb-group">' + esc(grp.label) + '</div>';
          for (var k = 0; k < grp.items.length; k++) {
            html += '<button type="button" class="cb-opt cb-opt-catch" role="option" data-idx="' + idx + '">' +
              '<span class="cb-opt-name">' + esc(grp.items[k].name) + '</span>' +
              '<span class="cb-catch-badge">catch-all</span></button>';
            sel.push({ kind: 'catch', style: grp.items[k] });
            idx++;
          }
        }
      }
      if (html) { menu.innerHTML = html; menu.hidden = false; active = -1; }
      else { close(); }
      return matches;
    }

    function update() {
      var q = input.value.trim();
      var matches = render(q);
      var ex = exactMatch(styles, q);
      if (ex) { resolved(ex, q, false); return; }
      unresolved();
      if (!q) setStatus('muted', esc(hint));
      else if (matches.length) setStatus('muted', 'Choose the closest match from the list.');
      else setStatus('warn', 'No standard style — pick a catch-all below so nothing is lost.');
    }

    function choose(i) {
      var e = sel[i];
      if (!e) return;
      if (e.kind === 'style') {
        input.value = e.style.name;            // show the clean canonical name
        resolved(e.style, e.style.name, false);
      } else {
        resolved(e.style, input.value.trim(), true); // keep raw label verbatim
      }
      close();
    }

    function setActive(n) {
      var opts = menu.querySelectorAll('.cb-opt');
      if (active >= 0 && opts[active]) opts[active].classList.remove('cb-active');
      active = n;
      if (active >= 0 && opts[active]) {
        opts[active].classList.add('cb-active');
        opts[active].scrollIntoView({ block: 'nearest' });
      }
    }

    // events
    input.addEventListener('input', update);
    input.addEventListener('focus', function () { if (input.value.trim()) update(); });
    input.addEventListener('keydown', function (e) {
      if (menu.hidden) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(active + 1, sel.length - 1)); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(active - 1, 0)); }
      else if (e.key === 'Enter') { if (active >= 0) { e.preventDefault(); choose(active); } }
      else if (e.key === 'Escape') { close(); }
    });
    input.addEventListener('blur', function () { setTimeout(close, 140); });
    // mousedown (not click) so focus isn't lost before we read the value
    menu.addEventListener('mousedown', function (e) {
      var btn = e.target.closest('.cb-opt');
      if (!btn) return;
      e.preventDefault();
      choose(parseInt(btn.getAttribute('data-idx'), 10));
    });

    // initial state (edit screens arrive with a value + maybe style_id)
    if (input.value.trim()) {
      var ex0 = exactMatch(styles, input.value.trim());
      if (ex0) resolved(ex0, input.value.trim(), false);
      else if (hidId.value) {
        var pre = styles.filter(function (s) { return s.id === hidId.value; })[0];
        if (pre) resolved(pre, input.value.trim(), pre.ca);
      } else setStatus('muted', esc(hint));
    } else setStatus('muted', esc(hint));
  }

  function init() {
    var nodes = document.querySelectorAll('[data-cb-style]');
    for (var i = 0; i < nodes.length; i++) enhance(nodes[i]);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
