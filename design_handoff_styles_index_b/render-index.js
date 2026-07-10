/* render-index.js — builds the Styles index from window.SP_DATA as a
   Class → Parent → Style tree. Two layouts share this data:
   renderIndexOutline('#mount')  — editorial table-of-contents
   renderIndexCards('#mount')    — color-forward parent cards
   Per-style SRM isn't in the lite dataset, so each PARENT carries a
   representative SRM (illustrative) to drive the color device. */
(function (g) {
  // representative mid-SRM per parent slug (illustrative for the prototype)
  var PSRM = {
    'pale-ale':6,'ipa':6,'amber-red-ale':14,'brown-ale':19,'bitter-mild':12,'scottish-ale':17,
    'strong-ale-barleywine':18,'porter':30,'stout':36,'wheat-beer':4,'belgian-ale':6,'belgian-strong-ale':13,
    'pale-lager':4,'pilsner':3,'amber-lager':13,'dark-lager':22,'bock-strong-lager':20,'hybrid':8,
    'sour-wild':8,'smoked-beer':21,'historical':10,'flavored-beer':14,'cider':3,'perry':3,'mead':5,'specialty':9
  };
  function esc(s){ return (s||'').replace(/[&<>]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c];}); }

  function tree() {
    var D = g.SP_DATA;
    var byParent = {};
    D.styles.forEach(function (s) { if (s.ca) return; (byParent[s.parent] = byParent[s.parent] || []).push(s); });
    Object.keys(byParent).forEach(function (k) { byParent[k].sort(function (a, b) { return a.name.localeCompare(b.name); }); });
    var parents = D.parents.slice().sort(function (a, b) { return (a.sort || 99) - (b.sort || 99); });
    // group parents by class; classless parents go in an "Other" bucket at the end
    var order = D.classes.map(function (c) { return c.slug; });
    var groups = {};
    parents.forEach(function (p) {
      var key = p.cls || '_other';
      (groups[key] = groups[key] || []).push(p);
    });
    var out = [];
    order.forEach(function (slug) {
      var c = D.classes.filter(function (x) { return x.slug === slug; })[0];
      if (groups[slug]) out.push({ name: c.name, slug: slug, parents: groups[slug] });
    });
    if (groups._other) out.push({ name: 'Other Beers', slug: '_other', parents: groups._other });
    return { groups: out, byParent: byParent };
  }

  g.renderIndexOutline = function (sel) {
    var t = tree(), html = '';
    t.groups.forEach(function (grp) {
      html += '<section class="ix-class"><h2 class="ix-class-h sp-class-h">' + esc(grp.name) + '</h2>';
      grp.parents.forEach(function (p) {
        var kids = t.byParent[p.slug] || [];
        var dot = window.SRM.hex(PSRM[p.slug] || 8);
        html += '<div class="ix-parent">' +
          '<div class="ix-parent-head">' +
            '<span class="ix-dot" style="background:' + dot + '"></span>' +
            '<a class="ix-parent-name" href="#">' + esc(p.name) + '</a>' +
            '<span class="sp-count">' + kids.length + '</span>' +
          '</div>' +
          '<div class="ix-styles">' +
            kids.map(function (s) { return '<a class="sp-style-link" href="#">' + esc(s.name) + '</a>'; }).join('<span class="ix-sep">·</span>') +
          '</div></div>';
      });
      html += '</section>';
    });
    document.querySelector(sel).innerHTML = html;
  };

  g.renderIndexCards = function (sel) {
    var t = tree(), html = '';
    t.groups.forEach(function (grp) {
      html += '<section class="ix-class"><h2 class="ix-class-h sp-class-h">' + esc(grp.name) +
        ' <span class="sp-count">' + grp.parents.length + ' families</span></h2><div class="ix-card-grid">';
      grp.parents.forEach(function (p) {
        var kids = t.byParent[p.slug] || [];
        var mid = PSRM[p.slug] || 8;
        var swatches = '';
        var n = Math.min(kids.length, 8);
        for (var i = 0; i < n; i++) {
          var v = mid + (i - n / 2) * 1.6; // spread around the parent's color
          swatches += '<span class="ix-sw" style="background:' + window.SRM.hex(v) + '"></span>';
        }
        html += '<a class="ix-card" href="#" style="--pc:' + window.SRM.hex(mid) + '">' +
          '<div class="ix-card-top"><span class="ix-card-name">' + esc(p.name) + '</span>' +
          '<span class="sp-count">' + kids.length + '</span></div>' +
          '<div class="ix-sw-row">' + swatches + '</div>' +
          '<div class="ix-card-styles">' + kids.slice(0, 3).map(function (s) { return esc(s.name); }).join(' · ') +
          (kids.length > 3 ? ' · +' + (kids.length - 3) + ' more' : '') + '</div></a>';
      });
      html += '</div></section>';
    });
    document.querySelector(sel).innerHTML = html;
  };
})(window);
