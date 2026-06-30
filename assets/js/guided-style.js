/* =============================================================================
   guided-style.js — Catalog.beer Style field, confidence-ladder edition
   -----------------------------------------------------------------------------
   The UX is driven by HOW WELL the typed label resolves, not by forcing a pick:

     label (free text)  ─ the hero, kept verbatim, shown publicly
        │ resolve()
        ├─ specific   exact name / alias hit, high confidence   → quiet ✓ + Change
        ├─ family     matches a family/umbrella, not a style    → filed at family, refine optional
        └─ unknown    new coinage, nothing matches              → picker, label kept

   The input is NEVER overwritten with the canonical name. The brewer's words
   stay; the canonical classification is the derived, overridable chip underneath.

   Markup contract (GuidedStyleField.class.php):
     <div class="sf" data-sf>
       <input class="form-control sf-input" name="style_label" autocomplete="off">
       <input type="hidden" name="style_id">
       <input type="hidden" name="parent">           <!-- family slug -->
       <input type="hidden" name="class">            <!-- super-class slug -->
       <input type="hidden" name="beverage_type">
       <input type="hidden" name="style_confidence">
       <div class="sf-card" hidden></div>
       <div class="sf-picker" hidden></div>
     </div>

   Data (inlined server-side from the DB, the source of truth — StyleList):
     window.CB_TAX = {
       classes: [{slug,name,bev,al}],
       parents: [{slug,name,cls,bev,sort,al}],
       styles:  [{id,name,parent,cat,fam,bev,ca,al}]
     }

   We emit the resolved tier as parent/class SLUGS (+ style_id) so the API can
   re-derive and validate server-side; style_confidence rides along as the only
   client-authored field (it records HOW the brewer resolved — override vs auto).
   ========================================================================== */
(function () {
  'use strict';

  var CATCHALL_IDS = ['specialty-beer', 'experimental-beer', 'historical-beer'];
  var FILLER = /\b(just|a|an|the|some|plain|kinda|sorta|our|my|its|it'?s|like|basically|style|beer)\b/g;

  function norm(s) { return (s || '').toString().toLowerCase().trim().replace(/\s+/g, ' '); }
  function loose(s) { return norm(s).replace(FILLER, ' ').replace(/[^\w ]/g, ' ').replace(/\s+/g, ' ').trim(); }
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

  function byId(styles, id) { for (var i = 0; i < styles.length; i++) if (styles[i].id === id) return styles[i]; return null; }

  // ranked specific-style matches (name + alias), excludes catch-alls
  function search(styles, q) {
    q = norm(q); if (!q) return [];
    var out = [];
    for (var i = 0; i < styles.length; i++) {
      var s = styles[i]; if (s.ca) continue;
      var n = norm(s.name), score = -1, via = null, al = s.al || [];
      if (n.indexOf(q) === 0) score = 0;
      else if (n.indexOf(q) > 0) score = 3;
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

  function exactMatch(styles, q) {
    q = norm(q); if (!q) return null;
    for (var i = 0; i < styles.length; i++) {
      var s = styles[i]; if (s.ca) continue;
      if (norm(s.name) === q) return { s: s, via: null };
      var al = s.al || [];
      for (var j = 0; j < al.length; j++) if (norm(al[j]) === q) return { s: s, via: al[j] };
    }
    return null;
  }

  // --- tier matching off real taxonomy -----------------------------------
  function aliasHit(aliases, q, lq) {
    for (var i = 0; i < (aliases || []).length; i++) { var a = norm(aliases[i]); if (a === q || a === lq) return true; }
    return false;
  }
  function parentMatch(tax, q, lq) {
    for (var i = 0; i < tax.parents.length; i++) if (aliasHit(tax.parents[i].al, q, lq)) return tax.parents[i];
    return null;
  }
  function classMatch(tax, q, lq) {
    for (var i = 0; i < tax.classes.length; i++) if (aliasHit(tax.classes[i].al, q, lq)) return tax.classes[i];
    return null;
  }
  function childrenOf(tax, slug) {
    return tax.styles.filter(function (s) { return s.parent === slug && !s.ca; })
      .sort(function (a, b) { return a.name.localeCompare(b.name); });
  }
  function parentsOf(tax, clsSlug) {
    return tax.parents.filter(function (p) { return p.cls === clsSlug; })
      .sort(function (a, b) { return (a.sort || 99) - (b.sort || 99); });
  }

  /* resolve typed text → specific | group(parent|class) | unknown.
     Order matters. A recognized family/class umbrella ("Pale Ale", "Strong Ale",
     "Brown Ale", "Lager") is checked FIRST, so a broad label opens the chip picker
     to hone in instead of snapping to one sub-style — even when that same term is
     also a specific style's alias/name. Fully-qualified names ("American-Style
     Pale Ale") aren't family aliases, so they still resolve specific, and the
     matching sub-style stays one click away as a chip. */
  function resolve(tax, raw) {
    var q = norm(raw); if (!q) return { state: 'empty' };
    var lq = loose(raw) || q;
    var p = parentMatch(tax, q, lq);
    if (p) return { state: 'group', gkind: 'parent', parent: p };
    var c = classMatch(tax, q, lq);
    if (c) return { state: 'group', gkind: 'class', cls: c };
    var ex = exactMatch(tax.styles, q);
    if (ex) return { state: 'specific', style: ex.s, via: ex.via };
    return { state: 'unknown' };
  }

  // --- component -----------------------------------------------------------
  function enhance(root) {
    var tax = window.CB_TAX || { classes: [], parents: [], styles: [] };
    var styles = tax.styles;
    var parentBySlug = {};
    tax.parents.forEach(function (p) { parentBySlug[p.slug] = p; });
    var classBySlug = {};
    tax.classes.forEach(function (c) { classBySlug[c.slug] = c; });
    function parentName(style) {
      var p = style && style.parent && parentBySlug[style.parent];
      return p ? p.name : '';
    }
    function classSlugOf(parentSlug) {
      var p = parentSlug && parentBySlug[parentSlug];
      return (p && p.cls) ? p.cls : '';
    }

    var input   = root.querySelector('input[name="style_label"]');
    var hId     = root.querySelector('input[name="style_id"]');
    var hParent = root.querySelector('input[name="parent"]');
    var hClass  = root.querySelector('input[name="class"]');
    var hBev    = root.querySelector('input[name="beverage_type"]');
    var hConf   = root.querySelector('input[name="style_confidence"]');
    var card    = root.querySelector('.sf-card');
    var picker  = root.querySelector('.sf-picker');
    var catchalls = CATCHALL_IDS.map(function (id) { return byId(styles, id); }).filter(Boolean);

    // snapshot of the currently-committed resolution, so Cancel can restore the
    // exact card the user was on (incl. a manual override) rather than re-deriving.
    var current = null;
    function restore(s) {
      if (!s) { update(); return; }
      if (s.kind === 'specific') renderSpecific(s.style, s.label, { override: s.override });
      else if (s.kind === 'group') renderGroup(s.g, s.label, s.scope);
      else if (s.kind === 'unknown') renderUnknown(s.label);
    }

    // Emit the resolved tier as slugs (+ style_id) plus the confidence signal.
    // o = { id, parent, cls, bev, confidence }
    function setHidden(o) {
      hId.value = o.id || '';
      if (hParent) hParent.value = o.parent || '';
      if (hClass)  hClass.value  = o.cls || '';
      if (hBev)    hBev.value    = o.bev || '';
      if (hConf)   hConf.value   = o.confidence || '';
    }
    function showCard(cls, html) {
      card.className = 'sf-card ' + cls;
      card.innerHTML = html;
      card.hidden = false;
    }
    function hidePicker() { picker.hidden = true; picker.innerHTML = ''; }

    // ---- render each state --------------------------------------------------
    function renderSpecific(style, label, opts) {
      opts = opts || {};
      var isCatch = !!style.ca;
      var pname = parentName(style);
      var pslug = style.parent || '';
      setHidden({
        id: style.id, parent: pslug, cls: classSlugOf(pslug), bev: style.bev || 'beer',
        confidence: isCatch ? 'catch-all' : (opts.override ? 'override' : 'confident')
      });
      var fam = isCatch
        ? ' <span class="sf-fam">· non-standard style</span>'
        : (pname ? ' <span class="sf-fam">· ' + esc(pname) + ' family</span>' : '');
      showCard('sf-ok',
        '<span class="sf-ico">✓</span>' +
        '<div class="sf-body">' +
          // The brewer's verbatim label stays visible in the input above; the
          // (label -> style_id + confidence) pairing is captured silently on save.
          '<div class="sf-line">Categorized as <strong>' + esc(style.name) + '</strong>' + fam + '</div>' +
        '</div>' +
        '<button type="button" class="sf-change">Change</button>');
      current = { kind: 'specific', style: style, label: label, override: !!opts.override };
      bindChange(label);
      hidePicker();
    }

    /* renderGroup — the honoring "family selector".
       g = { gkind:'parent', parent }  → ONE tier: the parent's styles as chips.
       g = { gkind:'class',  cls }     → TWO tiers: parent chips, then (scope=parentSlug)
                                          that parent's styles + a back chip. */
    function renderGroup(g, label, scope, edit) {
      var editing = !!edit;
      current = { kind: 'group', g: g, label: label, scope: scope || null };
      var headName, nudge, chips = '';

      if (g.gkind === 'parent') {
        headName = g.parent.name;
        if (!editing) setHidden({ id: '', parent: g.parent.slug, cls: g.parent.cls || '', bev: g.parent.bev || 'beer', confidence: 'family' });
        var pStyles = childrenOf(tax, g.parent.slug);
        nudge = editing
          ? 'Pick a different style in the “' + esc(g.parent.name) + '” family.'
          : '“' + esc(g.parent.name) + '” spans ' + pStyles.length + ' styles — pick one, or leave it at the family level.';
        pStyles.forEach(function (s) { chips += '<button type="button" class="sf-chip" data-id="' + esc(s.id) + '">' + esc(s.name) + '</button>'; });

      } else { // class
        if (!scope) {
          headName = g.cls.name;
          if (!editing) setHidden({ id: '', parent: '', cls: g.cls.slug, bev: g.cls.bev || 'beer', confidence: 'family' });
          var kids = parentsOf(tax, g.cls.slug);
          nudge = editing
            ? 'Pick a family, then a style.'
            : '“' + esc(g.cls.name) + '” is broad — narrow it down, or leave it general.';
          kids.forEach(function (p) { chips += '<button type="button" class="sf-chip" data-pslug="' + esc(p.slug) + '">' + esc(p.name) + '</button>'; });
        } else {
          var par = tax.parents.filter(function (p) { return p.slug === scope; })[0];
          headName = par ? par.name : g.cls.name;
          // filed at the drilled-into family level (parent slug wins server-side)
          if (!editing) setHidden({ id: '', parent: scope, cls: g.cls.slug, bev: g.cls.bev || 'beer', confidence: 'family' });
          var sStyles = childrenOf(tax, scope);
          nudge = editing
            ? 'Pick a different style, or go back to all ' + esc(g.cls.name) + '.'
            : 'Pick a specific style, or leave it at the ' + esc(headName) + ' level.';
          chips += '<button type="button" class="sf-chip sf-chip-back" data-back="1">← all ' + esc(g.cls.name) + '</button>';
          sStyles.forEach(function (s) { chips += '<button type="button" class="sf-chip" data-id="' + esc(s.id) + '">' + esc(s.name) + '</button>'; });
        }
      }

      if (editing) {
        showCard('sf-edit',
          '<span class="sf-ico">✎</span>' +
          '<div class="sf-body">' +
            '<div class="sf-edit-head"><span>Change category</span>' +
              '<button type="button" class="sf-link sf-cancel">Cancel</button></div>' +
            '<div class="sf-sub">' + nudge + '</div>' +
            '<div class="sf-chips">' + chips + '</div>' +
            '<button type="button" class="sf-link sf-search-all">Search all styles…</button>' +
          '</div>');
        card.querySelector('.sf-cancel').addEventListener('click', function () { restore(edit.prev); });
        card.querySelector('.sf-search-all').addEventListener('click', function () { renderEditSearch(label, edit.prev); });
      } else {
        showCard('sf-fam-card',
          '<span class="sf-ico">◇</span>' +
          '<div class="sf-body">' +
            '<div class="sf-line">Categorized in the <strong>' + esc(headName) + '</strong> family.</div>' +
            '<div class="sf-sub">' + nudge + '</div>' +
            '<div class="sf-chips">' + chips + '</div>' +
          '</div>');
      }

      card.querySelectorAll('.sf-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
          if (chip.dataset.back) { renderGroup(g, label, null, edit); return; }
          if (chip.dataset.pslug) { renderGroup(g, label, chip.dataset.pslug, edit); return; }
          var s = byId(styles, chip.dataset.id);
          if (s) renderSpecific(s, label, { override: true });
        });
      });
      hidePicker();
    }

    function renderUnknown(label) {
      setHidden({ id: '', parent: '', cls: '', bev: '', confidence: 'unresolved' });
      current = { kind: 'unknown', label: label };
      showCard('sf-new',
        '<span class="sf-ico">+</span>' +
        '<div class="sf-body">' +
          '<div class="sf-line"><strong>New to us.</strong> We don’t recognize “' + esc(label) + '” yet.</div>' +
          '<div class="sf-sub">Search for the closest standard style so it stays searchable — your label will stay exactly as written.</div>' +
          '<div class="sf-picker-mount"></div>' +
          '<button type="button" class="sf-link sf-catch-toggle">Can’t find a match? File under a catch-all</button>' +
          '<div class="sf-catch-list" hidden></div>' +
        '</div>');
      buildPicker(card.querySelector('.sf-picker-mount'), label, true, false);
      var toggle = card.querySelector('.sf-catch-toggle');
      var clist = card.querySelector('.sf-catch-list');
      toggle.addEventListener('click', function () {
        if (clist.hidden) {
          clist.innerHTML = catchalls.map(function (c) {
            return '<button type="button" class="sf-chip" data-id="' + esc(c.id) + '">' + esc(c.name) + '</button>';
          }).join('');
          clist.hidden = false;
          toggle.textContent = 'Hide catch-alls';
          clist.querySelectorAll('.sf-chip').forEach(function (ch) {
            ch.addEventListener('click', function () { var s = byId(styles, ch.dataset.id); if (s) renderSpecific(s, label, { override: true }); });
          });
        } else {
          clist.hidden = true;
          toggle.textContent = 'Can’t find a match? File under a catch-all';
        }
      });
    }

    // ---- override = in-card edit mode --------------------------------------
    function bindChange(label) {
      var btn = card.querySelector('.sf-change');
      if (btn) btn.addEventListener('click', function () { renderEdit(label); });
    }
    // Change / Pick-another: return to the chips this style sits in (reconstructed
    // from its place in the taxonomy) so re-picking a sibling is one click, not a
    // blank search box. Non-destructive — the committed value holds until you pick.
    // Catch-alls and family-less styles fall back to the search picker.
    function renderEdit(label) {
      var prev = current;
      var style = prev && prev.style;
      if (style && !style.ca && style.parent && parentBySlug[style.parent]) {
        var p = parentBySlug[style.parent];
        if (p.cls && classBySlug[p.cls]) renderGroup({ gkind: 'class', cls: classBySlug[p.cls] }, label, style.parent, { prev: prev });
        else renderGroup({ gkind: 'parent', parent: p }, label, null, { prev: prev });
      } else {
        renderEditSearch(label, prev);
      }
    }
    function renderEditSearch(label, prev) {
      showCard('sf-edit',
        '<span class="sf-ico">✎</span>' +
        '<div class="sf-body">' +
          '<div class="sf-edit-head"><span>Search all styles</span>' +
            '<button type="button" class="sf-link sf-cancel">Cancel</button></div>' +
          '<div class="sf-picker-mount"></div>' +
        '</div>');
      buildPicker(card.querySelector('.sf-picker-mount'), label, true, true);
      card.querySelector('.sf-cancel').addEventListener('click', function () { restore(prev); });
      var inp = card.querySelector('.sf-pick-input');
      if (inp) inp.focus();
      hidePicker();
    }

    function buildPicker(mount, label, inline, includeCatch) {
      mount.innerHTML =
        '<input type="text" class="form-control form-control-sm sf-pick-input" placeholder="Search styles…" autocomplete="off">' +
        '<div class="sf-menu" hidden></div>';
      var pInput = mount.querySelector('.sf-pick-input');
      var menu = mount.querySelector('.sf-menu');
      var sel = [], active = -1;

      function close() { menu.hidden = true; menu.innerHTML = ''; sel = []; active = -1; }
      function draw(q) {
        var matches = search(styles, q); sel = [];
        var html = '', idx = 0;
        matches.forEach(function (m) {
          var s = m.s;
          var alias = m.via ? '<span class="sf-opt-alias">matched “' + highlight(m.via, q) + '”</span>' : '';
          html += '<button type="button" class="sf-opt" data-idx="' + idx + '"><span class="sf-opt-name">' +
            highlight(s.name, q) + '</span>' + alias + '</button>';
          sel.push({ kind: 'style', style: s }); idx++;
        });
        if (includeCatch && q && matches.length === 0) {
          html += '<div class="sf-group">No standard match — file under a catch-all</div>';
          catchalls.forEach(function (c) {
            html += '<button type="button" class="sf-opt sf-opt-catch" data-idx="' + idx + '"><span class="sf-opt-name">' +
              esc(c.name) + '</span><span class="sf-catch-badge">catch-all</span></button>';
            sel.push({ kind: 'catch', style: c }); idx++;
          });
        }
        menu.innerHTML = html; menu.hidden = !html; active = -1;
      }
      function pick(i) {
        var e = sel[i]; if (!e) return;
        renderSpecific(e.style, label, { override: true });   // label stays verbatim
        if (!inline) hidePicker();
      }
      function setActive(n) {
        var opts = menu.querySelectorAll('.sf-opt');
        if (active >= 0 && opts[active]) opts[active].classList.remove('sf-active');
        active = n;
        if (active >= 0 && opts[active]) { opts[active].classList.add('sf-active'); opts[active].scrollIntoView({ block: 'nearest' }); }
      }
      pInput.addEventListener('input', function () { draw(pInput.value.trim()); });
      pInput.addEventListener('focus', function () { draw(pInput.value.trim()); });
      pInput.addEventListener('keydown', function (e) {
        if (menu.hidden) { if (e.key === 'ArrowDown') draw(pInput.value.trim()); return; }
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(active + 1, sel.length - 1)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(active - 1, 0)); }
        else if (e.key === 'Enter') { if (active >= 0) { e.preventDefault(); pick(active); } }
        else if (e.key === 'Escape') { e.preventDefault(); close(); }
      });
      menu.addEventListener('mousedown', function (e) {
        var b = e.target.closest('.sf-opt'); if (!b) return;
        e.preventDefault(); pick(parseInt(b.getAttribute('data-idx'), 10));
      });
    }

    // ---- main update --------------------------------------------------------
    function update() {
      var label = input.value.trim();
      var r = resolve(tax, label);
      if (r.state === 'empty') { card.hidden = true; hidePicker(); setHidden({}); return; }
      hidePicker();
      if (r.state === 'specific') renderSpecific(r.style, label, {});
      else if (r.state === 'group') renderGroup(r.gkind === 'parent' ? { gkind: 'parent', parent: r.parent } : { gkind: 'class', cls: r.cls }, label, null);
      else renderUnknown(label);
    }

    var t;
    input.addEventListener('input', function () { clearTimeout(t); t = setTimeout(update, 110); });
    // NOTE: no 'change' listener — it fires on blur and would rebuild chips between
    // a chip's mousedown and mouseup (the "click twice" bug). 'input' already covers
    // typing, paste, and autofill.
    if (input.value.trim()) update();
  }

  function init() {
    var nodes = document.querySelectorAll('[data-sf]');
    for (var i = 0; i < nodes.length; i++) enhance(nodes[i]);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
