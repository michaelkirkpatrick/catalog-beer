/* render-detail.js — fills a styles detail page from window.SP_DATA + SRM helper.
   Shared across detail directions; each direction supplies the element ids it
   uses (missing ids are skipped), so one renderer drives all layouts. */
function renderDetail(id) {
  var D = (window.SP_DATA && window.SP_DATA.full) ? window.SP_DATA.full[id] : null;
  if (!D) { return; }
  var parents = window.SP_DATA.parents || [];
  var classes = window.SP_DATA.classes || [];
  var parent = parents.filter(function (p) { return p.slug === D.parent; })[0] || { name: D.category, cls: null };
  var cls = classes.filter(function (c) { return c.slug === parent.cls; })[0] || null;

  function set(id, html) { var el = document.getElementById(id); if (el) el.innerHTML = html; }
  function esc(s){ return (s||'').replace(/[&<>]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c];}); }
  function paras(t){ return (t||'').split(/\n\n+/).map(function(p){return '<p>'+esc(p)+'</p>';}).join(''); }

  // --- title / lede / eyebrow ---
  set('t-name', esc(D.name));
  set('t-class', cls ? esc(cls.name) : '');
  set('t-parent', esc(parent.name));
  // a one-line lede: first sentence of description, set in italic serif
  var firstSentence = (D.description || '').split(/(?<=\.)\s/)[0];
  set('t-lede', esc(firstSentence));

  // --- SRM color device ---
  var srm = D.specs.srm || { min: 4, max: 8 };
  var mid = (srm.min + srm.max) / 2;
  var grad = window.SRM.gradient(srm.min, srm.max, '90deg');
  var el;
  if ((el = document.getElementById('t-srm'))) { el.style.background = grad; }
  set('t-srm-min', 'SRM ' + srm.min);
  set('t-srm-max', srm.max);
  // glass fill (vertical, lighter at the foamy top)
  if ((el = document.getElementById('t-glass'))) {
    el.style.background = window.SRM.gradient(Math.max(1, srm.min - 1), srm.max, '180deg');
  }
  // expose mid color as a page accent var
  document.documentElement.style.setProperty('--style-srm', window.SRM.hex(mid));
  document.documentElement.style.setProperty('--style-srm-on', window.SRM.onColor(window.SRM.hex(mid)));

  // --- description prose ---
  set('t-desc', paras(D.description));

  // --- AAFM ---
  var aafm = [['Appearance', D.appearance], ['Aroma', D.aroma], ['Flavor', D.flavor], ['Mouthfeel', D.mouthfeel]];
  set('t-aafm', aafm.filter(function (x) { return x[1]; }).map(function (x) {
    return '<div><div class="sp-aafm-k">' + x[0] + '</div><div class="sp-aafm-v">' + esc(x[1]) + '</div></div>';
  }).join(''));

  // --- spec range bars (ABV/IBU/OG/FG) — SRM handled separately ---
  var sp = D.specs;
  function bar(key, label, min, max, fmt, scaleMin, scaleMax) {
    if (min == null) return '';
    var lo = (min - scaleMin) / (scaleMax - scaleMin), hi = (max - scaleMin) / (scaleMax - scaleMin);
    lo = Math.max(0, Math.min(1, lo)); hi = Math.max(0, Math.min(1, hi));
    var left = (lo * 100).toFixed(1), w = Math.max(3, (hi - lo) * 100).toFixed(1);
    var v = (min === max) ? fmt(min) : fmt(min) + '–' + fmt(max);
    return '<div class="sp-spec"><div class="sp-spec-k">' + label + '</div>' +
      '<div class="sp-spec-track"><div class="sp-spec-fill" style="left:' + left + '%;width:' + w + '%;background:var(--style-srm);"></div></div>' +
      '<div class="sp-spec-v">' + v + '</div></div>';
  }
  var rows = '';
  if (sp.abv) rows += bar('abv', 'ABV', sp.abv.min, sp.abv.max, function (x) { return x + '%'; }, 0, 14);
  if (sp.ibu) rows += bar('ibu', 'IBU', sp.ibu.min, sp.ibu.max, function (x) { return '' + x; }, 0, 120);
  if (sp.og) rows += bar('og', 'OG', sp.og.min, sp.og.max, function (x) { return x.toFixed(3); }, 1.0, 1.12);
  if (sp.fg) rows += bar('fg', 'FG', sp.fg.min, sp.fg.max, function (x) { return x.toFixed(3); }, 0.995, 1.03);
  set('t-specs', rows);

  // --- history ---
  set('t-history', paras(D.history));

  // --- beers in style (illustrative teaser; production hits GET /beer?style=) ---
  var demoBeers = {
    'hazy-ipa': [['Julius', 'Tree House Brewing', '6.8%'], ['Focal Banger', 'The Alchemist', '7.0%'], ['Congress Street', 'Trillium Brewing', '7.2%'], ['Hazy Little Thing', 'Sierra Nevada', '6.7%'], ['Green', 'Tree House Brewing', '7.5%']],
    'american-ipa': [['Two Hearted Ale', "Bell's Brewery", '7.0%'], ['Blind Pig', 'Russian River', '6.1%'], ['Stone IPA', 'Stone Brewing', '6.9%'], ['Sculpin', 'Ballast Point', '7.0%']],
    'irish-dry-stout': [['Guinness Draught', 'Guinness', '4.2%'], ['Murphy\u2019s Irish Stout', 'Murphy\u2019s', '4.0%'], ['Beamish', 'Beamish & Crawford', '4.1%']],
    'czech-pilsner': [['Pilsner Urquell', 'Plze\u0148sk\u00FD Prazdroj', '4.4%'], ['Budweiser Budvar', 'Budvar', '5.0%'], ['Notch \u010Cesk\u00E9 Pivo', 'Notch Brewing', '4.4%']]
  };
  var beers = demoBeers[id] || [];
  var totals = { 'hazy-ipa': 2417, 'american-ipa': 5980, 'irish-dry-stout': 312, 'czech-pilsner': 489 };
  set('t-beercount', totals[id] ? '· ' + totals[id].toLocaleString() + ' catalogued' : '');
  set('t-beers', beers.map(function (b) {
    return '<a class="sp-beer-row" href="#"><span class="sp-beer-name">' + esc(b[0]) +
      ' <span class="sp-beer-brewer">' + esc(b[1]) + '</span></span><span class="sp-beer-abv">' + esc(b[2]) + '</span></a>';
  }).join('') + (totals[id] ? '<a class="sp-style-link" style="display:inline-block;margin-top:.75rem;font-family:var(--cb-mono);font-size:.8rem;" href="#">See all ' + totals[id].toLocaleString() + ' \u2192</a>' : ''));

  // --- sources ---
  var src = D.sources || {};
  var tags = [];
  if (src.brewers_association) tags.push('<span class="sp-source-tag">BA 2026</span>' + esc(src.brewers_association.name || D.name));
  if (src.bjcp) tags.push('<span class="sp-source-tag">BJCP ' + esc('' + (src.bjcp.code || '')) + '</span>' + esc(src.bjcp.name || ''));
  if (src.naba_2024) tags.push('<span class="sp-source-tag">NABA 2024</span>' + esc(src.naba_2024.name || ''));
  set('t-sources', tags.length ? '<div class="sp-section-h">Sources</div>' + tags.map(function (t) { return '<div style="margin-bottom:.3rem;">' + t + '</div>'; }).join('') : '');
}
