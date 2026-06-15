/* =============================================================================
   guided-style.js — Catalog.beer guided style input (vanilla JS, Bootstrap 5)
   -----------------------------------------------------------------------------
   Turns the "Style" field into a guided combobox that lets a brewer file at any
   tier — a specific style, a family, or a super-class (Ale/Lager) — while
   keeping their raw wording (style_label). Encyclopedic brewers pick a style;
   generic brewers pick "IPA" (family) or "Lager" (class) and are done.

   Markup contract (GuidedStyleField.class.php):
     <div class="cb-style" data-cb-style>
       <input name="style_label" autocomplete="off" ...>
       <input type="hidden" name="style_id">
       <input type="hidden" name="parent">
       <input type="hidden" name="class">
       <input type="hidden" name="beverage_type">
       <div class="cb-menu" hidden></div>
     </div>
     <div class="form-text cb-status"></div>

   Data (inlined server-side from the DB, the source of truth):
     window.CB_STYLES  = [{id,name,category,bev,ca,al}]
     window.CB_PARENTS = [{slug,name,bev,class,al}]
     window.CB_CLASSES = [{slug,name,bev,al}]

   Only the chosen tier's hidden field is set; the server derives the coarser
   levels (a style implies its family + class). Nothing legitimate is rejected.
   ========================================================================== */
(function () {
  'use strict';

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
  function aliasHit(item, q) {            // returns the matching alias, or null
    var al = item.al || [];
    for (var j = 0; j < al.length; j++) if (norm(al[j]).indexOf(q) >= 0) return al[j];
    return null;
  }

  // --- matching ------------------------------------------------------------
  function searchStyles(styles, q) {
    q = norm(q);                  // case/accent-insensitive: names/aliases below are normalized too
    var out = [];
    for (var i = 0; i < styles.length; i++) {
      var s = styles[i];
      if (s.ca) continue;
      var n = norm(s.name), score = -1, via = null;
      if (n.indexOf(q) === 0) score = 0;
      else if (n.indexOf(q) > 0) score = 3;
      var al = s.al || [];
      for (var j = 0; j < al.length; j++) {
        var a = norm(al[j]);
        if (a.indexOf(q) === 0 && (score < 0 || score > 1)) { score = 1; via = al[j]; }
        else if (a.indexOf(q) > 0 && score < 0) { score = 4; via = al[j]; }
      }
      if (score >= 0) out.push({ s: s, via: via, score: score });
    }
    out.sort(function (x, y) { return x.score - y.score || x.s.name.localeCompare(y.s.name); });
    return out.slice(0, 10);
  }
  function searchTier(list, q) {          // families or classes: match name or alias
    q = norm(q);                  // case/accent-insensitive (aliasHit + names normalize too)
    var out = [];
    for (var i = 0; i < list.length; i++) {
      var it = list[i], n = norm(it.name);
      if (n.indexOf(q) >= 0) out.push({ it: it, via: null });
      else { var v = aliasHit(it, q); if (v) out.push({ it: it, via: v }); }
    }
    return out;
  }
  function exactIn(list, q, key) {        // exact name or alias match in a tier list
    for (var i = 0; i < list.length; i++) {
      var it = list[i];
      if (norm(it.name) === q) return it;
      var al = it.al || [];
      for (var j = 0; j < al.length; j++) if (norm(al[j]) === q) return it;
    }
    return null;
  }
  function exactStyleName(styles, q) {
    for (var i = 0; i < styles.length; i++) if (!styles[i].ca && norm(styles[i].name) === q) return styles[i];
    return null;
  }
  function exactStyleAlias(styles, q) {
    for (var i = 0; i < styles.length; i++) {
      var s = styles[i]; if (s.ca) continue;
      var al = s.al || []; for (var j = 0; j < al.length; j++) if (norm(al[j]) === q) return s;
    }
    return null;
  }

  // --- component -----------------------------------------------------------
  function enhance(root) {
    var styles = window.CB_STYLES || [];
    var families = window.CB_PARENTS || [];
    var classes = window.CB_CLASSES || [];

    function catchAllsFor(bev) { return styles.filter(function (s) { return s.ca && s.bev === bev; }); }
    var catchallGroups = [
      { label: 'Beer', items: GENERAL_BEER_CATCHALLS.map(function (id) { return styles.filter(function (s) { return s.id === id; })[0]; }).filter(Boolean) },
      { label: 'Cider', items: catchAllsFor('cider') },
      { label: 'Mead', items: catchAllsFor('mead') }
    ].filter(function (g) { return g.items.length; });

    var input = root.querySelector('input[name="style_label"]');
    var hidId = root.querySelector('input[name="style_id"]');
    var hidParent = root.querySelector('input[name="parent"]');
    var hidClass = root.querySelector('input[name="class"]');
    var hidBev = root.querySelector('input[name="beverage_type"]');
    var menu = root.querySelector('.cb-menu');
    var status = (root.parentNode || document).querySelector('.cb-status');
    var hint = (input.getAttribute('data-hint') || 'Start typing a style, family (IPA), or class (Lager). Your exact wording is always kept.');

    var sel = [];
    var active = -1;

    function setStatus(kind, html) {
      if (!status) return;
      status.className = 'form-text cb-status ' + (kind === 'ok' ? 'text-success' : kind === 'warn' ? 'text-warning-emphasis' : 'text-muted');
      status.innerHTML = html;
    }
    function setFields(styleId, parent, cls, bev) {
      hidId.value = styleId || '';
      if (hidParent) hidParent.value = parent || '';
      if (hidClass) hidClass.value = cls || '';
      if (hidBev) hidBev.value = bev || 'beer';
    }
    function unresolved() { setFields('', '', '', ''); }

    function resolveStyle(s, label) {
      setFields(s.id, '', '', s.bev);
      var msg = CHECK + 'Will be filed as <strong>' + esc(s.name) + '</strong>';
      if (label && norm(label) !== norm(s.name)) msg += ' · your label “' + esc(label) + '” is kept';
      setStatus('ok', msg);
    }
    function resolveFamily(f, label) {
      setFields('', f.slug, '', f.bev);
      var msg = CHECK + 'Filed under the <strong>' + esc(f.name) + '</strong> family (no specific style)';
      if (label && norm(label) !== norm(f.name)) msg += ' · your label “' + esc(label) + '” is kept';
      setStatus('ok', msg);
    }
    function resolveClass(c, label) {
      setFields('', '', c.slug, c.bev);
      var msg = CHECK + 'Filed as <strong>' + esc(c.name) + '</strong> (any style)';
      if (label && norm(label) !== norm(c.name)) msg += ' · your label “' + esc(label) + '” is kept';
      setStatus('ok', msg);
    }
    function resolveCatch(s, label) {
      setFields(s.id, '', '', s.bev);
      var msg = CHECK + 'Filed under <strong>' + esc(s.name) + '</strong> — a non-standard style';
      if (label && norm(label) !== norm(s.name)) msg += ' · your label “' + esc(label) + '” is kept';
      setStatus('ok', msg);
    }

    function close() { menu.hidden = true; menu.innerHTML = ''; sel = []; active = -1; }

    function render(q) {
      var sMatches = searchStyles(styles, q);
      var fMatches = searchTier(families, q);
      var cMatches = searchTier(classes, q);
      var html = '', idx = 0;
      sel = [];

      if (q && (sMatches.length || fMatches.length || cMatches.length)) {
        var lastCat = null;
        for (var i = 0; i < sMatches.length; i++) {
          var m = sMatches[i], s = m.s;
          if (s.category !== lastCat) { html += '<div class="cb-group">' + esc(s.category) + '</div>'; lastCat = s.category; }
          var note = m.via ? '<span class="cb-alias">matched “' + highlight(m.via, q) + '”</span>' : '';
          html += '<button type="button" class="cb-opt" role="option" data-idx="' + idx + '"><span class="cb-opt-name">' + highlight(s.name, q) + '</span>' + note + '</button>';
          sel.push({ kind: 'style', item: s }); idx++;
        }
        if (fMatches.length) {
          html += '<div class="cb-group">Families</div>';
          for (var fi = 0; fi < fMatches.length; fi++) {
            var fm = fMatches[fi];
            var fnote = fm.via ? '<span class="cb-alias">matched “' + highlight(fm.via, q) + '”</span>' : '';
            html += '<button type="button" class="cb-opt cb-opt-catch" role="option" data-idx="' + idx + '"><span class="cb-opt-name">' + highlight(fm.it.name, q) + '</span>' + fnote + '<span class="cb-catch-badge">family</span></button>';
            sel.push({ kind: 'family', item: fm.it }); idx++;
          }
        }
        if (cMatches.length) {
          html += '<div class="cb-group">Classes</div>';
          for (var ci = 0; ci < cMatches.length; ci++) {
            var cm = cMatches[ci];
            html += '<button type="button" class="cb-opt cb-opt-catch" role="option" data-idx="' + idx + '"><span class="cb-opt-name">' + highlight(cm.it.name, q) + '</span><span class="cb-catch-badge">class</span></button>';
            sel.push({ kind: 'class', item: cm.it }); idx++;
          }
        }
      } else if (q) {
        html += '<div class="cb-nomatch">No standard style, family, or class matches “<strong>' + esc(q) + '</strong>.” File it under a catch-all — your wording is kept exactly:</div>';
        for (var gi = 0; gi < catchallGroups.length; gi++) {
          var grp = catchallGroups[gi];
          html += '<div class="cb-group">' + esc(grp.label) + '</div>';
          for (var k = 0; k < grp.items.length; k++) {
            html += '<button type="button" class="cb-opt cb-opt-catch" role="option" data-idx="' + idx + '"><span class="cb-opt-name">' + esc(grp.items[k].name) + '</span><span class="cb-catch-badge">catch-all</span></button>';
            sel.push({ kind: 'catch', item: grp.items[k] }); idx++;
          }
        }
      }
      if (html) { menu.innerHTML = html; menu.hidden = false; active = -1; }
      else { close(); }
      return { s: sMatches, f: fMatches, c: cMatches };
    }

    function update() {
      var q = input.value.trim();
      var nq = norm(q);
      var m = render(q);
      // Auto-resolve on an exact match, mirroring server precedence:
      // exact style name -> class -> family -> style alias.
      var byName = exactStyleName(styles, nq);
      if (byName) { resolveStyle(byName, q); return; }
      var c = exactIn(classes, nq);
      if (c) { resolveClass(c, q); return; }
      var f = exactIn(families, nq);
      if (f) { resolveFamily(f, q); return; }
      var byAlias = exactStyleAlias(styles, nq);
      if (byAlias) { resolveStyle(byAlias, q); return; }

      unresolved();
      if (!q) setStatus('muted', esc(hint));
      else if (m.s.length || m.f.length || m.c.length) setStatus('muted', 'Choose a style, family, or class from the list.');
      else setStatus('warn', 'No match — pick a catch-all below so nothing is lost.');
    }

    function choose(i) {
      var e = sel[i];
      if (!e) return;
      if (e.kind === 'style') { input.value = e.item.name; resolveStyle(e.item, e.item.name); }
      else if (e.kind === 'family') { input.value = e.item.name; resolveFamily(e.item, e.item.name); }
      else if (e.kind === 'class') { input.value = e.item.name; resolveClass(e.item, e.item.name); }
      else { resolveCatch(e.item, input.value.trim()); }
      close();
    }

    function setActive(n) {
      var opts = menu.querySelectorAll('.cb-opt');
      if (active >= 0 && opts[active]) opts[active].classList.remove('cb-active');
      active = n;
      if (active >= 0 && opts[active]) { opts[active].classList.add('cb-active'); opts[active].scrollIntoView({ block: 'nearest' }); }
    }

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
    menu.addEventListener('mousedown', function (e) {
      var btn = e.target.closest('.cb-opt');
      if (!btn) return;
      e.preventDefault();
      choose(parseInt(btn.getAttribute('data-idx'), 10));
    });

    // initial state (edit screens arrive with a label + the resolved tier)
    if (input.value.trim()) {
      var v = input.value.trim(), nv = norm(v);
      var s0 = exactStyleName(styles, nv) || exactStyleAlias(styles, nv);
      if (s0) resolveStyle(s0, v);
      else if (hidId.value) { var ps = styles.filter(function (s) { return s.id === hidId.value; })[0]; if (ps) (ps.ca ? resolveCatch : resolveStyle)(ps, v); else setStatus('muted', esc(hint)); }
      else if (hidParent && hidParent.value) { var pf = families.filter(function (f) { return f.slug === hidParent.value; })[0]; if (pf) resolveFamily(pf, v); else setStatus('muted', esc(hint)); }
      else if (hidClass && hidClass.value) { var pc = classes.filter(function (c) { return c.slug === hidClass.value; })[0]; if (pc) resolveClass(pc, v); else setStatus('muted', esc(hint)); }
      else setStatus('muted', esc(hint));
    } else setStatus('muted', esc(hint));
  }

  function init() {
    var nodes = document.querySelectorAll('[data-cb-style]');
    for (var i = 0; i < nodes.length; i++) enhance(nodes[i]);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
