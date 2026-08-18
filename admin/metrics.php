<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Admin Gate
if(!$userInfo->admin){
    header('location: /');
    exit;
}

// Fetch the metrics feed. The dashboard below inlines it as `const D`.
$api = new API();
$response = $api->request('GET', '/metrics', '');
$report = json_decode($response);

if(!is_object($report) || !empty($report->error) || !isset($report->history)){
    // API unreachable or errored — plain admin-chrome page with the message
    $htmlHead = new htmlHead('Metrics');
    echo $htmlHead->html;
    echo '<body>';
    echo $nav->navbar('');
    echo '<div class="container"><div class="row"><div class="col-12">';
    $nav->breadcrumbText = array('Admin', 'Metrics');
    $nav->breadcrumbLink = array('/admin/');
    echo $nav->breadcrumbs();
    echo '<h1>Metrics</h1>';
    $msg = (is_object($report) && isset($report->error_msg)) ? $report->error_msg : 'No response from the API.';
    echo '<div class="alert alert-danger">' . h($msg) . '</div>';
    echo '</div></div></div>';
    echo $nav->footer();
    echo '</body></html>';
    exit;
}

// The chart code was written against build-data.py's key names.
// json_encode escapes "/" by default, so "</script>" cannot occur in the blob.
$metricsJson = json_encode(array(
    'asOf' => $report->as_of,
    'history' => $report->history,
    'live' => $report->live,
    'latest' => $report->latest
));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Catalog.beer — catalog health metrics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<style>
:root {
  color-scheme: light;
  --page:            #f9f9f7;
  --surface-1:       #fcfcfb;
  --text-primary:    #0b0b0b;
  --text-secondary:  #52514e;
  --text-muted:      #898781;
  --grid:            #e1e0d9;
  --axis:            #c3c2b7;
  --border:          rgba(11,11,11,0.10);
  --good-text:       #006300;

  /* Chosen for colour-vision deficiency: this four-hue set is the strongest
     all-pairs result available from the palette that still keeps blue and
     keeps every line above 2:1 on the surface. Worst pair under simulated
     protanopia/deuteranopia is ΔE 13.0, worst normal-vision pair 16.3
     (target 8 / floor 15). Line style is a second, hue-free channel. */
  --series-1: #2a78d6;  /* blue   */
  --series-2: #4a3aa7;  /* violet */
  --series-3: #008300;  /* green  */
  --series-4: #e87ba4;  /* magenta */

  --ord-1: #86b6ef;  --ord-2: #5598e7;  --ord-3: #2a78d6;  --ord-4: #1c5cab;  --ord-5: #104281;

  --track:  #cde2fb;
  --pos:    #2a78d6;
  --neg:    #e34948;

  --status-good:     #0ca30c;
  --status-warning:  #fab219;
  --status-serious:  #ec835a;
  --status-critical: #d03b3b;
  --neutral:         #898781;
}
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    color-scheme: dark;
    --page:            #0d0d0d;
    --surface-1:       #1a1a19;
    --text-primary:    #ffffff;
    --text-secondary:  #c3c2b7;
    --text-muted:      #898781;
    --grid:            #2c2c2a;
    --axis:            #383835;
    --border:          rgba(255,255,255,0.10);
    --good-text:       #0ca30c;

    /* dark steps: violet collapses into blue on a dark surface (ΔE 1.9), so
       slot 2 takes yellow here — the only substitution that clears all pairs */
    --series-1: #3987e5;  --series-2: #c98500;  --series-3: #008300;  --series-4: #d55181;

    --ord-1: #cde2fb;  --ord-2: #9ec5f4;  --ord-3: #6da7ec;  --ord-4: #3987e5;  --ord-5: #184f95;

    --track:  #184f95;
    --pos:    #3987e5;
    --neg:    #e66767;
  }
}
:root[data-theme="dark"] {
  color-scheme: dark;
  --page:            #0d0d0d;
  --surface-1:       #1a1a19;
  --text-primary:    #ffffff;
  --text-secondary:  #c3c2b7;
  --text-muted:      #898781;
  --grid:            #2c2c2a;
  --axis:            #383835;
  --border:          rgba(255,255,255,0.10);
  --good-text:       #0ca30c;

  --series-1: #3987e5;  --series-2: #c98500;  --series-3: #008300;  --series-4: #d55181;

  --ord-1: #cde2fb;  --ord-2: #9ec5f4;  --ord-3: #6da7ec;  --ord-4: #3987e5;  --ord-5: #184f95;

  --track:  #184f95;
  --pos:    #3987e5;
  --neg:    #e66767;
}

* { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--page);
  color: var(--text-primary);
  font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif;
  -webkit-font-smoothing: antialiased;
}
.wrap { max-width: 1180px; margin: 0 auto; padding: 40px 24px 80px; }

header.top { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-end; justify-content: space-between; }
h1 { font-size: 20px; font-weight: 600; margin: 0 0 4px; letter-spacing: -0.01em; }
.asof { color: var(--text-secondary); font-size: 13px; margin: 0; }
.theme-toggle {
  font: inherit; font-size: 13px; color: var(--text-secondary);
  background: var(--surface-1); border: 1px solid var(--border); border-radius: 8px;
  padding: 6px 12px; cursor: pointer;
}
.theme-toggle:hover { color: var(--text-primary); }

.hero { margin: 30px 0 8px; }
.hero .figure { font-size: 56px; line-height: 1.05; font-weight: 600; letter-spacing: -0.02em; }
.hero .cap { color: var(--text-secondary); font-size: 14px; margin-top: 6px; max-width: 70ch; }

.callout {
  background: var(--surface-1); border: 1px solid var(--border); border-left: 3px solid var(--series-4);
  border-radius: 10px; padding: 14px 18px; margin: 24px 0 8px;
  color: var(--text-secondary); font-size: 13.5px;
}
.callout b { color: var(--text-primary); font-weight: 600; }
.callout p { margin: 0 0 8px; }
.callout p:last-child { margin-bottom: 0; }
.callout code, footer code, .lede code {
  font-size: 12px; background: var(--page); border: 1px solid var(--border);
  border-radius: 4px; padding: 1px 5px;
}

section { margin-top: 46px; }
section > h2 {
  font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--text-muted); margin: 0 0 5px;
}
section > .lede { color: var(--text-secondary); font-size: 14px; margin: 0 0 18px; max-width: 78ch; }

.grid { display: grid; gap: 18px; align-items: start; }
.grid.two { grid-template-columns: repeat(2, minmax(0,1fr)); }
.grid.three { grid-template-columns: repeat(3, minmax(0,1fr)); }
@media (max-width: 900px) { .grid.two, .grid.three { grid-template-columns: 1fr; } }

.card {
  background: var(--surface-1); border: 1px solid var(--border); border-radius: 12px;
  padding: 18px 20px 16px; min-width: 0;
}
.card h3 { font-size: 15px; font-weight: 600; margin: 0 0 2px; }
.card .sub { font-size: 13px; color: var(--text-secondary); margin: 0 0 12px; }
.card .note { font-size: 12.5px; color: var(--text-muted); margin: 14px 0 0; }
.plot { position: relative; width: 100%; }
.rowlab { font-size: 12.5px; font-weight: 600; margin: 10px 0 0; }

.tiles { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
.tile {
  background: var(--surface-1); border: 1px solid var(--border); border-radius: 12px;
  padding: 14px 16px 14px;
}
.tile .label { font-size: 12.5px; color: var(--text-secondary); }
.tile .value { font-size: 26px; font-weight: 600; letter-spacing: -0.01em; margin-top: 3px; }
.tile .delta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.tile .delta.up { color: var(--good-text); }
.tile .delta.down { color: var(--neg); }
.tile .spark { position: relative; height: 30px; margin-top: 10px; }

/* Segmented controls. One row above the charts they scope — never inside a card. */
.ctlrow { display: flex; flex-wrap: wrap; gap: 10px 16px; align-items: center; margin: 0 0 18px; }
.ctlrow .ctllab { font-size: 12px; color: var(--text-muted); }
.ctl { display: inline-flex; background: var(--surface-1); border: 1px solid var(--border); border-radius: 8px; padding: 2px; }
.ctl button {
  font: inherit; font-size: 12.5px; line-height: 1; color: var(--text-secondary);
  background: none; border: 0; border-radius: 6px; padding: 6px 11px; cursor: pointer;
}
.ctl button:hover { color: var(--text-primary); }
.ctl button.is-on { background: var(--page); color: var(--text-primary); font-weight: 600; box-shadow: inset 0 0 0 1px var(--border); }
.ctl button:focus-visible { outline: 2px solid var(--series-1); outline-offset: 1px; }

details.tv { margin-top: 14px; }
details.tv summary {
  cursor: pointer; font-size: 12.5px; color: var(--text-muted); list-style: none;
  display: inline-flex; align-items: center; gap: 6px; padding: 2px 0;
}
details.tv summary::-webkit-details-marker { display: none; }
details.tv summary::before { content: "▸"; font-size: 10px; }
details.tv[open] summary::before { content: "▾"; }
details.tv summary:hover { color: var(--text-secondary); }
table { border-collapse: collapse; width: 100%; margin-top: 10px; font-size: 12.5px; }
th, td { text-align: right; padding: 5px 8px; border-bottom: 1px solid var(--grid); white-space: nowrap; }
th:first-child, td:first-child { text-align: left; }
thead th { color: var(--text-muted); font-weight: 500; }
td { font-variant-numeric: tabular-nums; color: var(--text-secondary); }
td:first-child { color: var(--text-primary); }
.scroller { overflow-x: auto; }

.offline {
  display: none; background: var(--surface-1); border: 1px solid var(--border);
  border-left: 3px solid var(--status-critical); border-radius: 10px;
  padding: 14px 18px; margin: 24px 0; font-size: 13.5px; color: var(--text-secondary);
}
footer { margin-top: 58px; padding-top: 20px; border-top: 1px solid var(--border); color: var(--text-muted); font-size: 12.5px; }
</style>
</head>
<body>
<div class="wrap">

<header class="top">
  <div>
    <a href="/admin/" style="font-size:.85rem;color:inherit">&larr; Admin</a>
    <h1>Catalog.beer — catalog health</h1>
    <p class="asof" id="asof"></p>
  </div>
  <button class="theme-toggle" id="themeBtn" type="button">Dark mode</button>
</header>

<div class="offline" id="offline">
  <b>Chart.js did not load.</b> This page pulls it from jsDelivr, so it needs a network
  connection. Every value is still readable — open the “Table view” under any chart.
</div>

<div class="hero">
  <div class="figure" id="heroFigure"></div>
  <div class="cap" id="heroCap"></div>
</div>

<div class="callout">
  <p><b>Two data regimes, and they are not comparable.</b> The size and growth family was
  replayed from <code>createdAt</code> back to Nov 2017 by <code>cron/backfill-metrics.php</code>;
  everything else is current-state only and begins at the first live snapshot.</p>
  <p><b>The long history is a survivor curve.</b> The backfill counts only records that still
  exist today, so a historical total runs low wherever something was later deleted — the line
  can never show a record that has since been removed.</p>
  <p><b>No chart needs colour vision to read.</b> Every line carries its own stroke pattern
  (solid, dashed, dotted, dash-dot) and its own end label; stacked segments are named inside
  the bar wherever the text fits; every chart has a table view. Hues were picked by running
  each candidate set through protanopia and deuteranopia simulation and keeping the widest
  separation available.</p>
</div>

<div id="kpis" class="tiles" style="margin-top:18px"></div>

<section id="s-growth">
  <h2>Size &amp; growth</h2>
  <p class="lede">The only family with real history. One day dominates it: the 2 May 2020 bulk
  import landed 60,307 beers and 6,154 brewers at once, so the whole-run view is read on a log
  axis — a linear one shows a single step and nothing else. The range below scopes the two
  history charts; <i>Created vs deleted</i> is live-window-only and ignores it.</p>
  <div class="ctlrow">
    <span class="ctllab">Range</span>
    <div class="ctl" id="rangeCtl" role="group" aria-label="Time range"></div>
    <span class="ctllab">Scale</span>
    <div class="ctl" id="scaleCtl" role="group" aria-label="Scale"></div>
  </div>
  <div class="grid"><div class="card" id="c-totals"></div></div>
  <div class="grid two" style="margin-top:18px">
    <div class="card" id="c-rate"></div>
    <div class="card" id="c-churn"></div>
  </div>
</section>

<section id="s-fresh">
  <h2>Freshness</h2>
  <p class="lede">How long since each record was last touched. The windows in the schema are
  nested (30 ⊂ 90 ⊂ 365 days), so they are differenced into exclusive bands that sum to the
  whole catalog.</p>
  <div class="grid"><div class="card" id="c-recency"></div></div>
  <div id="t-age" class="tiles" style="margin-top:18px"></div>
</section>

<section id="s-complete">
  <h2>Completeness</h2>
  <p class="lede">Share of records carrying each optional field. Bars are the same hue because
  the fields are names, not an ordered scale — length is the only thing being compared.</p>
  <div class="grid three">
    <div class="card" id="c-beer-fields"></div>
    <div class="card" id="c-brewer-fields"></div>
    <div class="card" id="c-loc-fields"></div>
  </div>
</section>

<section id="s-verify">
  <h2>Verification &amp; engagement</h2>
  <p class="lede">Two independent flags — a record can carry both — so these are grouped bars,
  not a stack.</p>
  <div class="grid two">
    <div class="card" id="c-verify"></div>
    <div class="card" id="c-users"></div>
  </div>
  <div id="t-engage" class="tiles" style="margin-top:18px"></div>
</section>

<section id="s-style">
  <h2>Style classification</h2>
  <p class="lede">The style taxonomy rollout is the most active thing in the live window.</p>
  <div class="grid two">
    <div class="card" id="c-conf"></div>
    <div class="card" id="c-conf-trend"></div>
  </div>
  <div class="grid two" style="margin-top:18px">
    <div class="card" id="c-resolved"></div>
    <div class="card" id="c-beverage"></div>
  </div>
</section>

<section id="s-urls">
  <h2>Brewer URL health</h2>
  <p class="lede">Nine verdicts from <code>cron/check-urls.php</code>. Severity is an ordered
  scale, so it is drawn as one hue getting darker — four status colours became a single olive
  under deuteranopia simulation, and no reader should have to tell &ldquo;healthy&rdquo; from
  &ldquo;dead&rdquo; by hue.</p>
  <div class="grid two">
    <div class="card" id="c-url"></div>
    <div class="card" id="c-url-trend"></div>
  </div>
</section>

<section id="s-api">
  <h2>API demand</h2>
  <p class="lede">Trailing-30-day request counts, snapshotted nightly because
  <code>api_logging</code> is pruned at three months. Master-key requests are never logged,
  so every number here is a floor.</p>
  <div class="grid two">
    <div class="card" id="c-api"></div>
    <div class="card" id="c-keys"></div>
  </div>
</section>

<footer>
  <p>Built from <code>metrics_daily.csv</code> (48,744 rows, 3,183 snapshot days). Metric
  definitions live in <code>classes/Metrics.class.php</code>. Every chart has a table view;
  no value is reachable only by hovering.</p>
</footer>

</div>

<script>
const D = <?php echo $metricsJson; ?>;

/* ============================ helpers ============================ */
const fmt  = n => n == null ? '—' : Math.round(n).toLocaleString('en-US');
const pct  = (n, d) => d ? (n / d * 100) : 0;
const fpct = (n, d) => d ? (n / d * 100).toFixed(1) + '%' : '—';
const FONT = 'system-ui, -apple-system, "Segoe UI", sans-serif';

const tok = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

/* Line style is a second identity channel that owes nothing to hue: the series
   stay tellable apart under any colour-vision deficiency, in grayscale print,
   and under forced-colors. Slot order matches --series-1..4. */
const DASH = [[], [7, 4], [2, 3], [11, 3, 2, 3]];
/* A line dataset in slot i: colour, dash and the legend key that mirrors both. */
function lineOf(label, color, data, slot) {
  return { label, data, borderColor: color, backgroundColor: color,
    borderDash: DASH[slot] || [], borderWidth: 2, borderCapStyle: 'round', borderJoinStyle: 'round',
    pointStyle: 'line', pointRadius: 0, pointHitRadius: 0, tension: 0 };
}

function el(tag, attrs, kids) {
  const n = document.createElement(tag);
  for (const k in (attrs || {})) {
    if (k === 'class') n.className = attrs[k];
    else if (k === 'text') n.textContent = attrs[k];   /* labels are untrusted data */
    else n.setAttribute(k, attrs[k]);
  }
  (kids || []).forEach(c => n.appendChild(c));
  return n;
}
const MONTH = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const shortDate = iso => MONTH[+iso.slice(5, 7) - 1] + ' ' + (+iso.slice(8)) + ', ' + iso.slice(0, 4);
const shortDay  = iso => MONTH[+iso.slice(5, 7) - 1] + ' ' + (+iso.slice(8));
function axisNum(v) {
  const a = Math.abs(v);
  if (a >= 1e6) return (v / 1e6).toFixed(a % 1e6 ? 1 : 0) + 'M';
  if (a >= 1e3) return (v / 1e3).toFixed(a % 1e3 ? 1 : 0) + 'K';
  return String(v);
}
/* ink that clears contrast on a given fill */
function ink(hex) {
  const h = hex.replace('#', '');
  const c = [0, 2, 4].map(i => parseInt(h.substr(i, 2), 16) / 255)
    .map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
  return (0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]) > 0.5 ? '#0b0b0b' : '#ffffff';
}

/* ---------- card scaffolding ---------- */
function card(hostId, o) {
  const host = document.getElementById(hostId);
  host.textContent = '';
  host.appendChild(el('h3', { text: o.title }));
  if (o.sub) host.appendChild(el('p', { class: 'sub', text: o.sub }));
  const body = el('div', {});
  host.appendChild(body);
  return {
    host, body,
    plot(height) {
      const d = el('div', { class: 'plot', style: 'height:' + height + 'px' });
      body.appendChild(d);
      const cv = document.createElement('canvas');
      d.appendChild(cv);
      return cv;
    },
    rowLabel(t) { body.appendChild(el('p', { class: 'rowlab', text: t })); },
    done() { if (o.note) host.appendChild(el('p', { class: 'note', text: o.note })); }
  };
}
function tableView(host, cols, rows, label) {
  host.appendChild(el('details', { class: 'tv' }, [
    el('summary', { text: label || 'Table view' }),
    el('div', { class: 'scroller' }, [el('table', {}, [
      el('thead', {}, [el('tr', {}, cols.map(c => el('th', { text: c })))]),
      el('tbody', {}, rows.map(r => el('tr', {}, r.map(v => el('td', { text: v })))))
    ])])
  ]));
}
function tile(host, o) {
  const t = el('div', { class: 'tile' });
  t.appendChild(el('div', { class: 'label', text: o.label }));
  t.appendChild(el('div', { class: 'value', text: o.value }));
  if (o.delta !== undefined && o.delta !== null) {
    const good = o.upIsGood === false ? (o.delta < 0) : (o.delta > 0);
    t.appendChild(el('div', {
      class: 'delta' + (o.delta === 0 ? '' : (good ? ' up' : ' down')),
      text: (o.delta > 0 ? '+' : '') + fmt(o.delta) + ' ' + (o.deltaNote || 'since Jul 28')
    }));
  } else if (o.sub) {
    t.appendChild(el('div', { class: 'delta', text: o.sub }));
  }
  host.appendChild(t);
  if (o.spark) {
    const s = el('div', { class: 'spark' });
    t.appendChild(s);
    const cv = document.createElement('canvas');
    s.appendChild(cv);
    mk(cv, {
      type: 'line',
      data: { labels: o.spark.map((_, i) => i),
        datasets: [{ data: o.spark, borderColor: tok('--text-muted'), borderWidth: 1.5,
          pointRadius: o.spark.map((_, i) => i === o.spark.length - 1 ? 3.5 : 0),
          pointBackgroundColor: tok('--series-1'), pointBorderColor: tok('--surface-1'),
          pointBorderWidth: 2, tension: 0.25 }] },
      options: { animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { top: 4, bottom: 2, left: 2, right: 5 } },
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        events: [] }
    });
  }
}

/* ============================ Chart.js setup ============================ */
const HAS_CHART = typeof Chart !== 'undefined';
if (!HAS_CHART) document.getElementById('offline').style.display = 'block';
const charts = [];
/* Cards the range control can rebuild on their own collect into their own list,
   so a re-render destroys only what that card made — nothing else on the page. */
const owned = {};
let sink = charts;
function mk(canvas, cfg) {
  if (!HAS_CHART) { canvas.parentElement.style.height = '0'; return null; }
  const c = new Chart(canvas, cfg);
  sink.push(c);
  return c;
}
function rebuild(hostId, fn) {
  const list = owned[hostId] || (owned[hostId] = []);
  while (list.length) list.pop().destroy();
  sink = list;
  try { fn(); } finally { sink = charts; }
}

/* A vertical hairline at the hovered X — readers aim at a date, not at a 2px line. */
const crosshair = {
  id: 'crosshair',
  afterDatasetsDraw(chart) {
    const act = chart.tooltip && chart.tooltip.getActiveElements ? chart.tooltip.getActiveElements() : null;
    if (!act || !act.length) return;
    const x = act[0].element.x, a = chart.chartArea, ctx = chart.ctx;
    ctx.save();
    ctx.beginPath(); ctx.moveTo(x, a.top); ctx.lineTo(x, a.bottom);
    ctx.lineWidth = 1; ctx.strokeStyle = tok('--axis'); ctx.stroke();
    ctx.restore();
  }
};
/* Value at the end of each line \u2014 selective direct labelling, never every point.
   Converging lines would stack their labels on top of each other, so the text is
   nudged apart vertically while the dot stays on the true last point. */
const endLabels = {
  id: 'endLabels',
  afterDatasetsDraw(chart, args, opts) {
    if (!opts || !opts.enabled) return;
    const ctx = chart.ctx, area = chart.chartArea, GAP = 14, marks = [];
    chart.data.datasets.forEach((ds, i) => {
      const meta = chart.getDatasetMeta(i);
      if (meta.hidden) return;
      for (let k = ds.data.length - 1; k >= 0; k--) {
        if (ds.data[k] == null) continue;
        const p = meta.data[k];
        if (!p) break;
        marks.push({ x: p.x, y: p.y, ty: p.y, color: ds.borderColor, text: fmt(ds.data[k]) });
        break;
      }
    });
    /* one downward pass to open the gaps, then shift the block back if it overruns */
    marks.sort((a, b) => a.ty - b.ty);
    for (let i = 1; i < marks.length; i++)
      if (marks[i].ty - marks[i - 1].ty < GAP) marks[i].ty = marks[i - 1].ty + GAP;
    const over = marks.length ? marks[marks.length - 1].ty - (area.bottom - 2) : 0;
    if (over > 0) marks.forEach(m => { m.ty -= over; });

    ctx.save();
    ctx.font = '500 11.5px ' + FONT;
    ctx.textBaseline = 'middle';
    marks.forEach(m => {
      ctx.beginPath();
      ctx.arc(m.x, m.y, 4, 0, 6.284);
      ctx.fillStyle = m.color;
      ctx.fill();
      ctx.lineWidth = 2; ctx.strokeStyle = tok('--surface-1'); ctx.stroke();  /* surface ring */
      /* a hairline leader whenever the text had to leave its dot */
      if (Math.abs(m.ty - m.y) > 1) {
        ctx.beginPath();
        ctx.moveTo(m.x + 5, m.y); ctx.lineTo(m.x + 8, m.ty);
        ctx.lineWidth = 1; ctx.strokeStyle = tok('--axis'); ctx.stroke();
      }
      ctx.fillStyle = tok('--text-secondary');
      ctx.fillText(m.text, m.x + 9, m.ty);
    });
    ctx.restore();
  }
};
/* Percent inside a stacked segment — drawn only when it measurably fits. */
const segLabels = {
  id: 'segLabels',
  afterDatasetsDraw(chart, args, opts) {
    if (!opts || !opts.enabled) return;
    const ctx = chart.ctx;
    ctx.save();
    ctx.font = '600 11.5px ' + FONT;
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    chart.data.datasets.forEach((ds, i) => {
      chart.getDatasetMeta(i).data.forEach((bar, j) => {
        const v = ds.data[j];
        if (v == null || v <= 0) return;
        /* candidates run longest-first; the first that measurably fits is drawn */
        const cands = [].concat(opts.fmt(v, i, j) || []);
        const w = Math.abs(bar.x - bar.base);
        const txt = cands.find(t => t && ctx.measureText(t).width + 18 <= w);
        if (!txt) return;
        ctx.fillStyle = ink(ds.backgroundColor);
        ctx.fillText(txt, (bar.x + bar.base) / 2, bar.y);
      });
    });
    ctx.restore();
  }
};
/* Value past the tip of a horizontal bar, or flush right for a full-width track. */
const tipLabels = {
  id: 'tipLabels',
  afterDatasetsDraw(chart, args, opts) {
    if (!opts || !opts.enabled) return;
    const ctx = chart.ctx;
    ctx.save();
    ctx.font = '500 11.5px ' + FONT;
    ctx.fillStyle = tok('--text-secondary');
    ctx.textBaseline = 'middle';
    const ds = chart.data.datasets[opts.dataset || 0];
    chart.getDatasetMeta(opts.dataset || 0).data.forEach((bar, j) => {
      const txt = opts.fmt(ds.data[j], j);
      if (!txt) return;
      if (opts.atRight) { ctx.textAlign = 'right'; ctx.fillText(txt, chart.chartArea.right + 56, bar.y); }
      else { ctx.textAlign = 'left'; ctx.fillText(txt, bar.x + 9, bar.y); }
    });
    ctx.restore();
  }
};

function tooltipStyle(extra) {
  return Object.assign({
    backgroundColor: tok('--surface-1'),
    titleColor: tok('--text-secondary'),
    bodyColor: tok('--text-primary'),
    borderColor: tok('--axis'),
    borderWidth: 1,
    cornerRadius: 8,
    padding: 10,
    boxWidth: 12, boxHeight: 2, boxPadding: 5,
    titleFont: { size: 11.5, weight: '400', family: FONT },
    bodyFont: { size: 12.5, weight: '600', family: FONT },
    displayColors: true
  }, extra || {});
}
/* isLine: the key mirrors the mark — the line's own stroke for lines, a swatch for fills.
 *
 * The stock generateLabels builds a point-style key from `getStyle(0)`, which is the
 * POINT element's style — points carry no borderDash, so every key comes out solid and
 * the dash channel dies at the legend. Read the dash off the dataset instead. */
function legendStyle(isLine) {
  return {
    display: true, position: 'top', align: 'start',
    labels: {
      color: tok('--text-secondary'),
      usePointStyle: !!isLine,
      boxWidth: isLine ? 26 : 11, boxHeight: isLine ? 26 : 11,
      padding: 16, font: { size: 12.5, family: FONT },
      generateLabels: !isLine ? undefined : chart => chart.data.datasets.map((ds, i) => ({
        text: ds.label,
        strokeStyle: ds.borderColor,
        fillStyle: ds.borderColor,
        lineWidth: 2,
        lineDash: ds.borderDash && ds.borderDash.length ? ds.borderDash : [],
        lineCap: 'butt',          /* round caps blur a short dash back into a solid line */
        lineDashOffset: 0,
        pointStyle: 'line',
        hidden: !chart.isDatasetVisible(i),
        datasetIndex: i
      }))
    }
  };
}
const gridX = () => ({ grid: { display: false }, border: { color: tok('--axis') },
  ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, maxRotation: 0, autoSkip: false } });
const gridY = (extra) => Object.assign({
  grid: { color: tok('--grid'), drawTicks: false },
  border: { display: false },
  ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, padding: 8, maxTicksLimit: 6,
    callback: v => axisNum(v) }
}, extra || {});

/* Round up to the next clean number, so a symmetric axis doesn't overshoot. */
function niceCeil(v) {
  if (v <= 0) return 1;
  const mag = Math.pow(10, Math.floor(Math.log10(v))), n = v / mag;
  return mag * [1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10].find(s => n <= s + 1e-9);
}

/* Show a date label only on chosen indices, so ticks land on real boundaries. */
function dateTicks(dates, pick, label) {
  return v => pick(dates[v], v) ? label(dates[v]) : '';
}

/* Date ticks sized to the window: years across a decade, months across a quarter.
   One helper so every chart the range control scopes labels its axis the same way. */
function spanTicks(dates) {
  const n = dates.length;
  if (n > 1200) return dateTicks(dates, d => d.slice(5) === '01-01', d => d.slice(0, 4));
  const step = n > 500 ? 3 : n > 200 ? 2 : 1;
  return dateTicks(dates,
    d => d.slice(8) === '01' && (+d.slice(5, 7) - 1) % step === 0,
    d => MONTH[+d.slice(5, 7) - 1] + (+d.slice(5, 7) === 1 || step > 1 ? ' \u2019' + d.slice(2, 4) : ''));
}

/* Log axes label 1, 2 and 5 per decade. Powers of ten alone leave a window that
   spans less than a decade with a single gridline label. */
const logTick = v => {
  const m = v / Math.pow(10, Math.floor(Math.log10(v) + 1e-9));
  return Math.abs(m - 1) < 1e-9 || Math.abs(m - 2) < 1e-9 || Math.abs(m - 5) < 1e-9 ? axisNum(v) : '';
};

/* ============================ data accessors ============================ */
const H = D.history, L = D.live;
const hist = k => H.series[k];
const live = k => L.series[k] || [];
const now  = k => D.latest[k] != null ? D.latest[k] : 0;
const first = k => { const a = live(k); return a.length && a[0] != null ? a[0] : 0; };
const delta = k => now(k) - first(k);
const LD = L.dates, HD = H.dates;
const FIRST = LD[0];
const ENT = [['brewer', 'Brewers'], ['beer', 'Beers'], ['location', 'Locations']];
/* chart order for the growth lines: slot 1 = beers, 2 = brewers, 3 = locations */
const ENTC = [{ key: 'beer', label: 'Beers' }, { key: 'brewer', label: 'Brewers' }, { key: 'location', label: 'Locations' }];
const hAt = (k, d) => hist(k)[HD.indexOf(d)];

/* ============================ build ============================ */
function renderAll() {
  while (charts.length) charts.pop().destroy();

  document.getElementById('asof').textContent =
    'Snapshot ' + shortDate(D.asOf) + ' · history replayed from ' + shortDate(HD[0]) +
    ' · live metrics since ' + shortDate(FIRST) + ' (' + LD.length + ' days)';
  document.getElementById('heroFigure').textContent = fmt(now('total_beer')) + ' beers';
  document.getElementById('heroCap').textContent =
    'from ' + fmt(now('total_brewer')) + ' brewers across ' + fmt(now('total_location')) +
    ' locations · ' + fmt(now('total_beer') - now('beer_style_confidence|none')) +
    ' beers now carry a resolved style, up from ' + fmt(hAt('total_beer', FIRST) - first('beer_style_confidence|none')) +
    ' two weeks ago';

  /* ---------- KPI row ---------- */
  const k = document.getElementById('kpis');
  k.textContent = '';
  /* totals live in the history series, not the live one — difference them there */
  [['Beers', 'total_beer'], ['Brewers', 'total_brewer'], ['Locations', 'total_location']].forEach(([label, key]) => {
    tile(k, { label, value: fmt(now(key)), delta: now(key) - hAt(key, FIRST),
      spark: LD.map(d => hAt(key, d)) });
  });
  tile(k, { label: 'Registered users', value: fmt(now('users_total')), delta: delta('users_total'), spark: live('users_total') });
  tile(k, { label: 'GET requests (30d)', value: fmt(now('api_get_30d')), delta: delta('api_get_30d'), spark: live('api_get_30d') });
  tile(k, { label: 'Writes (30d)', value: fmt(now('api_write_30d')), delta: delta('api_write_30d'), spark: live('api_write_30d') });

  /* ---------- 1.1 totals ---------- */
  rebuild('c-totals', renderTotals);

  /* ---------- 1.2 creation rate ---------- */
  rebuild('c-rate', renderRate);

  /* ---------- 1.3 created vs deleted ---------- */
  {
    const c = card('c-churn', {
      title: 'Created vs deleted, daily',
      sub: 'Above the line created, below deleted. The live window is the only place deletions are knowable.',
      note: 'Deletions are inferred by differencing yesterday’s total, so a record created and deleted on the same day is invisible to it. Aug 3 is the URL cleanup: 183 brewers removed, and 2,817 beers gone the same day — consistent with the cascade from those brewers.'
    });
    ENT.forEach(([e, label], n) => {
      c.rowLabel(label);
      const up = LD.map(d => hAt('created_' + e + '_1d', d));
      const down = live('deleted_' + e + '_1d').map(v => -(v || 0));
      /* symmetric bounds so up and down are read on the same scale */
      const bound = niceCeil(Math.max(...up, ...down.map(Math.abs), 1));
      mk(c.plot(112), {
        type: 'bar',
        data: { labels: LD, datasets: [
          { label: 'Created', data: up, backgroundColor: tok('--pos'), borderRadius: 4, borderSkipped: 'middle', maxBarThickness: 18 },
          { label: 'Deleted', data: down, backgroundColor: tok('--neg'), borderRadius: 4, borderSkipped: 'middle', maxBarThickness: 18 }
        ] },
        options: {
          animation: false, responsive: true, maintainAspectRatio: false,
          layout: { padding: { top: 2 } },
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: n === 0, position: 'top', align: 'start',
              labels: { color: tok('--text-secondary'), boxWidth: 11, boxHeight: 11, padding: 14, font: { size: 12.5, family: FONT } } },
            tooltip: tooltipStyle({ callbacks: {
              title: it => shortDate(LD[it[0].dataIndex]),
              label: ct => fmt(Math.abs(ct.parsed.y)) + '  ' + ct.dataset.label.toLowerCase() } })
          },
          scales: {
            x: Object.assign(gridX(), { stacked: true, ticks: Object.assign(gridX().ticks, {
              callback: dateTicks(LD, (d, i) => i % 4 === 0, shortDay) }) }),
            y: gridY({ stacked: true, min: -bound, max: bound, ticks: {
              color: tok('--text-muted'), font: { size: 11, family: FONT }, padding: 8, stepSize: bound / 2,
              callback: v => v === 0 ? '0' : axisNum(Math.abs(v)) } })
          }
        },
        plugins: [crosshair]
      });
    });
    c.done();
    tableView(c.host, ['Date', 'Beer +', 'Beer −', 'Brewer +', 'Brewer −', 'Loc +', 'Loc −'],
      LD.map((d, i) => [d,
        fmt(hAt('created_beer_1d', d)), fmt(live('deleted_beer_1d')[i]),
        fmt(hAt('created_brewer_1d', d)), fmt(live('deleted_brewer_1d')[i]),
        fmt(hAt('created_location_1d', d)), fmt(live('deleted_location_1d')[i])]));
  }

  /* ---------- 2.1 recency bands ---------- */
  {
    const c = card('c-recency', {
      title: 'When was each record last touched?',
      sub: 'Nested windows differenced into exclusive bands. Each row sums to that entity’s total.',
      note: 'Locations are almost entirely fresh because the address backfill rewrote them. Beers are not: 92.8% have not been touched in two years.'
    });
    const bands = [
      { label: '≤ 30 days', color: tok('--ord-5') },
      { label: '30 – 90 days', color: tok('--ord-4') },
      { label: '90 – 365 days', color: tok('--ord-3') },
      { label: '1 – 2 years', color: tok('--ord-2') },
      { label: 'over 2 years', color: tok('--ord-1') }
    ];
    const raw = ENT.map(([e]) => {
      const total = now('total_' + e), t30 = now('touched_' + e + '_30d'),
            t90 = now('touched_' + e + '_90d'), t365 = now('touched_' + e + '_365d'),
            st = now('stale_' + e + '_2yr');
      return { total, v: [t30, t90 - t30, t365 - t90, Math.max(0, total - t365 - st), st] };
    });
    mk(c.plot(210), {
      type: 'bar',
      data: {
        labels: ENT.map(([e, l]) => l),
        datasets: bands.map((b, bi) => ({
          label: b.label, backgroundColor: b.color,
          data: raw.map(r => pct(r.v[bi], r.total)),
          borderColor: tok('--surface-1'), borderWidth: 2, borderRadius: 4, borderSkipped: false,
          maxBarThickness: 34
        }))
      },
      options: {
        indexAxis: 'y', animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { right: 4 } },
        interaction: { mode: 'nearest', intersect: true },
        plugins: {
          legend: legendStyle(),
          tooltip: tooltipStyle({ callbacks: {
            title: it => ENT[it[0].dataIndex][1] + ' · ' + it[0].dataset.label,
            label: ct => fmt(raw[ct.dataIndex].v[ct.datasetIndex]) + '  records  (' +
              ct.parsed.x.toFixed(1) + '%)' } }),
          segLabels: { enabled: true, fmt: (v, i) => [bands[i].label + '  ' + v.toFixed(1) + '%', v.toFixed(1) + '%'] }
        },
        scales: {
          x: { stacked: true, max: 100, grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, callback: v => v + '%' } },
          y: { stacked: true, grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-primary'), font: { size: 12.5, weight: '600', family: FONT } } }
        }
      },
      plugins: [segLabels]
    });
    c.done();
    tableView(c.host, ['Entity', 'Total', ...bands.map(b => b.label)],
      ENT.map(([e, l], i) => [l, fmt(raw[i].total), ...raw[i].v.map(v => fmt(v))]));
  }
  {
    const t = document.getElementById('t-age');
    t.textContent = '';
    ENT.forEach(([e, label]) => {
      tile(t, { label: label + ' — median age', value: fmt(now('age_' + e + '_p50_days')) + ' d',
        sub: '90th percentile ' + fmt(now('age_' + e + '_p90_days')) + ' days',
        spark: live('age_' + e + '_p50_days') });
    });
    tile(t, { label: 'Brewers with a stale catalog', value: fmt(now('brewer_stale_catalog_2yr')),
      sub: 'newest beer over 2 years old', delta: delta('brewer_stale_catalog_2yr'), upIsGood: false,
      spark: live('brewer_stale_catalog_2yr') });
  }

  /* ---------- meters (completeness / resolution) ---------- */
  function meterCard(id, o) {
    const c = card(id, o);
    const items = o.items;
    mk(c.plot(Math.max(120, items.length * 44 + 26)), {
      type: 'bar',
      data: {
        labels: items.map(i => i.label),
        datasets: [
          { label: 'filled', backgroundColor: tok('--series-1'), data: items.map(i => pct(i.value, i.total)),
            borderColor: tok('--surface-1'), borderWidth: { right: 2 }, borderRadius: 5, borderSkipped: false, maxBarThickness: 11 },
          { label: 'empty', backgroundColor: tok('--track'), data: items.map(i => 100 - pct(i.value, i.total)),
            borderRadius: 5, borderSkipped: false, maxBarThickness: 11 }
        ]
      },
      options: {
        indexAxis: 'y', animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { right: 62, top: 2 } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: tooltipStyle({ filter: ct => ct.datasetIndex === 0, displayColors: false, callbacks: {
            title: it => items[it[0].dataIndex].label,
            label: ct => fmt(items[ct.dataIndex].value) + ' of ' + fmt(items[ct.dataIndex].total) +
              '  (' + fpct(items[ct.dataIndex].value, items[ct.dataIndex].total) + ')' } }),
          tipLabels: { enabled: true, dataset: 0, atRight: true,
            fmt: (v, j) => fpct(items[j].value, items[j].total) }
        },
        scales: {
          x: { stacked: true, max: 100, display: false },
          y: { stacked: true, grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-primary'), font: { size: 12.5, family: FONT } } }
        }
      },
      plugins: [tipLabels]
    });
    c.done();
    tableView(c.host, [o.thing || 'Field', 'Filled', 'Total', 'Share'],
      items.map(i => [i.label, fmt(i.value), fmt(i.total), fpct(i.value, i.total)]));
  }

  const TB = now('total_beer'), TBR = now('total_brewer'), TL = now('total_location');
  meterCard('c-beer-fields', {
    title: 'Beer fields', sub: 'Share of ' + fmt(TB) + ' beers',
    note: 'ibu = 0 now means “no measurable bitterness” (' + fmt(now('beer_ibu_zero')) +
          ' beers). abv has no NULL, so a 0 there is either a genuine non-alcoholic beer or an ' +
          'unfilled placeholder — ' + fmt(now('beer_abv_zero')) + (now('beer_abv_zero') === 1 ? ' row is' : ' rows are') +
          ' ambiguous today.',
    items: [
      { label: 'ABV recorded (> 0)', value: now('beer_with_abv'), total: TB },
      { label: 'IBU recorded (not null)', value: now('beer_with_ibu'), total: TB },
      { label: 'Description', value: now('beer_with_description'), total: TB }
    ]
  });
  meterCard('c-brewer-fields', {
    title: 'Brewer fields', sub: 'Share of ' + fmt(TBR) + ' brewers',
    note: 'url and domainName track each other exactly — the domain is derived from the URL, so clearing one clears both.',
    items: [
      { label: 'Website URL', value: now('brewer_with_url'), total: TBR },
      { label: 'Domain name', value: now('brewer_with_domain'), total: TBR },
      { label: 'Has a beer', value: now('brewer_with_beer'), total: TBR },
      { label: 'Has a location', value: now('brewer_with_location'), total: TBR },
      { label: 'Description', value: now('brewer_with_description'), total: TBR },
      { label: 'Short description', value: now('brewer_with_short_description'), total: TBR }
    ]
  });
  meterCard('c-loc-fields', {
    title: 'Location fields', sub: 'Share of ' + fmt(TL) + ' locations',
    note: 'name is optional by design (API v3.1.0) — a location without one is a valid record, not a gap.',
    items: [
      { label: 'Verified US address', value: now('location_with_address'), total: TL },
      { label: 'Latitude / longitude', value: now('location_with_latlng'), total: TL },
      { label: 'Website URL', value: now('location_with_url'), total: TL },
      { label: 'Name', value: now('location_with_name'), total: TL }
    ]
  });

  /* ---------- 4 verification ---------- */
  {
    const c = card('c-verify', {
      title: 'Verification coverage',
      sub: 'Percent of each entity carrying the flag. Both bars share one axis and both are labelled.',
      note: 'Brewery-staff verification is effectively unused — 28 beers, 3 brewers and 1 location catalog-wide, which is why those bars are hairlines rather than an error.'
    });
    const cb = ENT.map(([e]) => pct(now('cb_verified_' + e), now('total_' + e)));
    const bv = ENT.map(([e]) => pct(now('brewer_verified_' + e), now('total_' + e)));
    mk(c.plot(210), {
      type: 'bar',
      data: {
        labels: ENT.map(([e, l]) => l),
        datasets: [
          { label: 'cbVerified (admin)', data: cb, backgroundColor: tok('--series-1'),
            borderRadius: 4, borderSkipped: 'middle', maxBarThickness: 16 },
          { label: 'brewerVerified (staff)', data: bv, backgroundColor: tok('--series-2'),
            borderRadius: 4, borderSkipped: 'middle', maxBarThickness: 16 }
        ]
      },
      options: {
        indexAxis: 'y', animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { right: 96, top: 2 } },
        interaction: { mode: 'nearest', intersect: true },
        plugins: {
          legend: legendStyle(),
          tooltip: tooltipStyle({ callbacks: {
            title: it => ENT[it[0].dataIndex][1] + ' · ' + it[0].dataset.label,
            label: ct => {
              const key = (ct.datasetIndex === 0 ? 'cb_verified_' : 'brewer_verified_') + ENT[ct.dataIndex][0];
              return fmt(now(key)) + ' of ' + fmt(now('total_' + ENT[ct.dataIndex][0])) +
                '  (' + ct.parsed.x.toFixed(1) + '%)';
            } } }),
          tipLabels: { enabled: true, dataset: 0, fmt: (v, j) => v.toFixed(1) + '%  ·  ' + fmt(now('cb_verified_' + ENT[j][0])) }
        },
        scales: {
          x: { grid: { color: tok('--grid'), drawTicks: false }, border: { display: false }, beginAtZero: true,
            ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, callback: v => v + '%' } },
          y: { grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-primary'), font: { size: 12.5, weight: '600', family: FONT } } }
        }
      },
      plugins: [tipLabels, {
        id: 'staffLabels',   /* the staff bars are too thin to label from their own tip */
        afterDatasetsDraw(chart) {
          const ctx = chart.ctx, meta = chart.getDatasetMeta(1);
          ctx.save();
          ctx.font = '500 11.5px ' + FONT; ctx.fillStyle = tok('--text-secondary');
          ctx.textBaseline = 'middle'; ctx.textAlign = 'left';
          meta.data.forEach((bar, j) => {
            ctx.fillText(bv[j].toFixed(2) + '%  ·  ' + fmt(now('brewer_verified_' + ENT[j][0])), bar.x + 9, bar.y);
          });
          ctx.restore();
        }
      }]
    });
    c.done();
    tableView(c.host, ['Entity', 'Total', 'cbVerified', 'brewerVerified'],
      ENT.map(([e, l]) => [l, fmt(now('total_' + e)), fmt(now('cb_verified_' + e)), fmt(now('brewer_verified_' + e))]));
  }
  meterCard('c-users', {
    title: 'Accounts', sub: 'Share of ' + fmt(now('users_total')) + ' registered users',
    thing: 'Measure',
    note: 'Only ' + fmt(now('privileges_total')) + ' explicit privilege row exists; brewery-staff access otherwise comes from an email-domain match. User growth is in the tile at the top of the page.',
    items: [{ label: 'Email verified', value: now('users_email_verified'), total: now('users_total') }]
  });
  {
    const t = document.getElementById('t-engage');
    t.textContent = '';
    tile(t, { label: 'Breweries that touched their own data', value: fmt(now('brewers_engaged')),
      sub: 'of ' + fmt(TBR) + ' brewers' });
    tile(t, { label: 'Privilege grants', value: fmt(now('privileges_total')),
      sub: fmt(now('privileges_users')) + ' distinct user' });
    tile(t, { label: 'API keys active (30d)', value: fmt(now('api_keys_active_30d')),
      delta: delta('api_keys_active_30d'), spark: live('api_keys_active_30d') });
  }

  /* ---------- 5 style ---------- */
  /* "none" is the absence of a class, so it takes the neutral rather than
     spending a hue — the same grey that means "not yet checked" under URLs. */
  const CONF = [
    { key: 'none', label: 'none (unclassified)', color: tok('--neutral') },
    { key: 'catch-all', label: 'catch-all', color: tok('--series-1') },
    { key: 'family', label: 'family', color: tok('--series-2') },
    { key: 'confident', label: 'confident', color: tok('--series-3') },
    { key: 'override', label: 'override (human)', color: tok('--series-4') }
  ];
  {
    const c = card('c-conf', {
      title: 'Style confidence, today',
      sub: 'Every beer sits in exactly one bucket, so this is part-to-whole.',
      note: 'Segments too narrow to hold a label are left unlabelled by measurement, not by guess — their values are in the tooltip and the table.'
    });
    const vals = CONF.map(x => now('beer_style_confidence|' + x.key));
    mk(c.plot(130), {
      type: 'bar',
      data: { labels: ['Beers'], datasets: CONF.map((x, i) => ({
        label: x.label, backgroundColor: x.color, data: [pct(vals[i], TB)],
        borderColor: tok('--surface-1'), borderWidth: 2, borderRadius: 4, borderSkipped: false, maxBarThickness: 38
      })) },
      options: {
        indexAxis: 'y', animation: false, responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'nearest', intersect: true },
        plugins: {
          legend: legendStyle(),
          tooltip: tooltipStyle({ callbacks: {
            title: it => it[0].dataset.label,
            label: ct => fmt(vals[ct.datasetIndex]) + ' beers  (' + ct.parsed.x.toFixed(1) + '%)' } }),
          segLabels: { enabled: true, fmt: (v, i) => [CONF[i].label + '  ' + v.toFixed(1) + '%', v.toFixed(1) + '%'] }
        },
        scales: {
          x: { stacked: true, max: 100, grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, callback: v => v + '%' } },
          y: { stacked: true, display: false }
        }
      },
      plugins: [segLabels]
    });
    c.done();
    tableView(c.host, ['Bucket', 'Beers', 'Share'], CONF.map((x, i) => [x.label, fmt(vals[i]), fpct(vals[i], TB)]));
  }
  {
    const c = card('c-conf-trend', {
      title: 'Classification campaign, last 14 days',
      sub: 'Beers moving out of “none” into a resolved bucket. Four series, one axis, same unit.',
      note: 'End labels are omitted here because confident (1,447) and override (1,503) converge — the legend, tooltip and table carry those values instead.'
    });
    mk(c.plot(230), {
      type: 'line',
      data: { labels: LD, datasets: CONF.filter(x => x.key !== 'none').map((x, i) =>
        Object.assign(lineOf(x.label, x.color, live('beer_style_confidence|' + x.key).map(v => v || 0), i),
          { tension: 0.2 })) },
      options: {
        animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { top: 6, right: 10 } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: legendStyle(true),
          tooltip: tooltipStyle({ callbacks: {
            title: it => shortDate(LD[it[0].dataIndex]),
            label: ct => fmt(ct.parsed.y) + '  ' + ct.dataset.label } })
        },
        scales: {
          x: Object.assign(gridX(), { ticks: Object.assign(gridX().ticks, {
            callback: dateTicks(LD, (d, i) => i % 3 === 0, shortDay) }) }),
          y: gridY({ beginAtZero: true })
        }
      },
      plugins: [crosshair]
    });
    c.done();
    tableView(c.host, ['Date', ...CONF.map(x => x.label)],
      LD.map((d, i) => [d, ...CONF.map(x => fmt(live('beer_style_confidence|' + x.key)[i] || 0))]));
  }
  meterCard('c-resolved', {
    title: 'Taxonomy fields resolved', sub: 'Share of ' + fmt(TB) + ' beers', thing: 'Column',
    note: 'Three separate columns, not nested stages — class is resolved least often.',
    items: [
      { label: 'parent', value: now('beer_parent_resolved'), total: TB },
      { label: 'style_id', value: now('beer_style_id_resolved'), total: TB },
      { label: 'class', value: now('beer_class_resolved'), total: TB }
    ]
  });
  {
    const types = ['beer', 'cider', 'mead', 'perry'].map(key => ({ key, v: now('beer_beverage_type|' + key) }));
    const total = types.reduce((a, b) => a + b.v, 0);
    const others = types.slice(1);
    const c = card('c-beverage', {
      title: 'Beverage type',
      sub: 'Beer is ' + fpct(types[0].v, total) + ' of the catalog, so the other three are charted alone.',
      note: 'A part-to-whole chart of all four would render three invisible slivers; the table keeps the whole picture.'
    });
    mk(c.plot(180), {
      type: 'bar',
      data: { labels: others.map(t => t.key), datasets: [{
        label: 'records', data: others.map(t => t.v), backgroundColor: tok('--series-1'),
        borderRadius: 4, borderSkipped: 'middle', maxBarThickness: 20 }] },
      options: {
        indexAxis: 'y', animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { right: 74, top: 4 } },
        interaction: { mode: 'nearest', intersect: true },
        plugins: {
          legend: { display: false },
          tooltip: tooltipStyle({ displayColors: false, callbacks: {
            title: it => it[0].label,
            label: ct => fmt(ct.parsed.x) + ' records  (' + fpct(ct.parsed.x, total) + ' of catalog)' } }),
          tipLabels: { enabled: true, dataset: 0, fmt: v => fmt(v) }
        },
        scales: {
          x: { grid: { color: tok('--grid'), drawTicks: false }, border: { display: false }, beginAtZero: true,
            ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, callback: axisNum } },
          y: { grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-primary'), font: { size: 12.5, family: FONT } } }
        }
      },
      plugins: [tipLabels]
    });
    c.done();
    tableView(c.host, ['Type', 'Records', 'Share'], types.map(t => [t.key, fmt(t.v), fpct(t.v, total)]));
  }

  /* ---------- 6 URL health ---------- */
  /* Severity is an ordered scale, not a set of identities — a status-colour
     stack collapsed into one hue under deuteranopia simulation, so it is an
     ordinal ramp with every verdict named on the axis instead. */
  const SEV = { healthy: tok('--ord-2'), redirected: tok('--ord-3'),
                unreachable: tok('--ord-4'), 'dead or wrong': tok('--ord-5') };
  const URLB = [
    { key: 'ok', label: 'ok', grp: 'healthy' },
    { key: 'moved', label: 'moved', grp: 'redirected' },
    { key: 'blocked', label: 'blocked', grp: 'unreachable' },
    { key: 'no_answer', label: 'no answer', grp: 'unreachable' },
    { key: 'server_error', label: 'server error', grp: 'unreachable' },
    { key: 'parked', label: 'parked', grp: 'dead or wrong' },
    { key: 'gone', label: 'gone', grp: 'dead or wrong' },
    { key: 'url_wrong', label: 'wrong URL', grp: 'dead or wrong' },
    { key: 'unverified', label: 'not yet checked', grp: 'not yet checked' }
  ];
  {
    const vals = {};
    URLB.forEach(b => { vals[b.key] = now('brewer_url_status|' + b.key); });
    const backlog = vals.unverified, checked = TBR - backlog;
    const rows = URLB.filter(b => b.key !== 'unverified');
    const c = card('c-url', {
      title: 'URL verdicts, brewers checked so far',
      sub: 'One row per verdict for the ' + fmt(checked) + ' brewers the monitor has reached, ' +
           'worst last. Shade carries severity; the name on the axis carries the identity.',
      note: 'A further ' + fmt(backlog) + ' brewers are still unchecked — see the crawl chart. ' +
            fmt(checked - vals.ok) + ' of the ' + fmt(checked) + ' checked, ' +
            fpct(checked - vals.ok, checked) + ', came back as something other than ok.'
    });
    mk(c.plot(300), {
      type: 'bar',
      data: { labels: rows.map(b => b.label), datasets: [{
        label: 'brewers', data: rows.map(b => vals[b.key]),
        backgroundColor: rows.map(b => SEV[b.grp]),
        borderRadius: 4, borderSkipped: 'middle', maxBarThickness: 18 }] },
      options: {
        indexAxis: 'y', animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { right: 104, top: 4 } },
        interaction: { mode: 'nearest', intersect: true },
        plugins: {
          legend: { display: false },
          tooltip: tooltipStyle({ displayColors: false, callbacks: {
            title: it => it[0].label + ' · ' + rows[it[0].dataIndex].grp,
            label: ct => fmt(ct.parsed.x) + ' brewers  (' + fpct(ct.parsed.x, checked) + ' of checked)' } }),
          tipLabels: { enabled: true, dataset: 0,
            fmt: (v, j) => fmt(v) + '  ·  ' + fpct(v, checked) }
        },
        scales: {
          x: { grid: { color: tok('--grid'), drawTicks: false }, border: { display: false }, beginAtZero: true,
            ticks: { color: tok('--text-muted'), font: { size: 11, family: FONT }, callback: axisNum } },
          y: { grid: { display: false }, border: { display: false },
            ticks: { color: tok('--text-primary'), font: { size: 12.5, family: FONT } } }
        }
      },
      plugins: [tipLabels]
    });
    c.done();
    tableView(c.host, ['Verdict', 'Severity', 'Brewers', 'Of all', 'Of checked'],
      URLB.map(b => [b.label, b.grp, fmt(vals[b.key]), fpct(vals[b.key], TBR),
        b.key === 'unverified' ? '—' : fpct(vals[b.key], checked)]));
  }
  {
    const c = card('c-url-trend', {
      title: 'Crawl progress',
      sub: 'Brewers still unchecked, and those the monitor has cleared as healthy.',
      note: 'The monitor began from a standing start on ' + shortDay(FIRST) + ', when all ' +
            fmt(first('brewer_url_status|unverified')) + ' brewers were unchecked. It has since cleared ' +
            fmt(now('brewer_url_status|ok')) + ' and flagged ' +
            fmt(TBR - now('brewer_url_status|unverified') - now('brewer_url_status|ok')) + ' as needing attention.'
    });
    mk(c.plot(230), {
      type: 'line',
      data: { labels: LD, datasets: [
        Object.assign(lineOf('not yet checked', tok('--neutral'),
          live('brewer_url_status|unverified').map(v => v || 0), 1), { tension: 0.2 }),
        Object.assign(lineOf('ok', tok('--status-good'),
          live('brewer_url_status|ok').map(v => v || 0), 0), { tension: 0.2 })
      ] },
      options: {
        animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { top: 6, right: 58 } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: legendStyle(true),
          tooltip: tooltipStyle({ callbacks: {
            title: it => shortDate(LD[it[0].dataIndex]),
            label: ct => fmt(ct.parsed.y) + '  ' + ct.dataset.label } }),
          endLabels: { enabled: true }
        },
        scales: {
          x: Object.assign(gridX(), { ticks: Object.assign(gridX().ticks, {
            callback: dateTicks(LD, (d, i) => i % 3 === 0, shortDay) }) }),
          y: gridY({ beginAtZero: true })
        }
      },
      plugins: [crosshair, endLabels]
    });
    c.done();
    tableView(c.host, ['Date', ...URLB.map(b => b.label)],
      LD.map((d, i) => [d, ...URLB.map(b => fmt(live('brewer_url_status|' + b.key)[i] || 0))]));
  }

  /* ---------- 7 API ---------- */
  {
    const c = card('c-api', {
      title: 'Requests, trailing 30 days',
      sub: 'Reads and writes share one axis — same unit, comparable magnitudes.',
      note: 'Writes climbed from ' + fmt(first('api_write_30d')) + ' to ' + fmt(now('api_write_30d')) +
            ' as the classification and address work ran; read traffic is flat.'
    });
    mk(c.plot(230), {
      type: 'line',
      data: { labels: LD, datasets: [
        Object.assign(lineOf('GET', tok('--series-1'), live('api_get_30d'), 0), { tension: 0.2 }),
        Object.assign(lineOf('Writes (POST/PUT/PATCH/DELETE)', tok('--series-2'), live('api_write_30d'), 1), { tension: 0.2 })
      ] },
      options: {
        animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { top: 6, right: 58 } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: legendStyle(true),
          tooltip: tooltipStyle({ callbacks: {
            title: it => shortDate(LD[it[0].dataIndex]),
            label: ct => fmt(ct.parsed.y) + '  ' + ct.dataset.label } }),
          endLabels: { enabled: true }
        },
        scales: {
          x: Object.assign(gridX(), { ticks: Object.assign(gridX().ticks, {
            callback: dateTicks(LD, (d, i) => i % 3 === 0, shortDay) }) }),
          y: gridY({ beginAtZero: true })
        }
      },
      plugins: [crosshair, endLabels]
    });
    c.done();
    tableView(c.host, ['Date', 'GET', 'Writes', 'Active keys'],
      LD.map((d, i) => [d, fmt(live('api_get_30d')[i]), fmt(live('api_write_30d')[i]), fmt(live('api_keys_active_30d')[i])]));
  }
  {
    const c = card('c-keys', {
      title: 'Distinct API keys used, trailing 30 days',
      sub: 'One series, so no legend box — the title names it.',
      note: 'Master-key traffic is never written to api_logging, so this undercounts by however many master keys were in use.'
    });
    mk(c.plot(230), {
      type: 'line',
      data: { labels: LD, datasets: [
        Object.assign(lineOf('Active keys', tok('--series-1'), live('api_keys_active_30d'), 0), { tension: 0.2 })] },
      options: {
        animation: false, responsive: true, maintainAspectRatio: false,
        layout: { padding: { top: 6, right: 46 } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: tooltipStyle({ displayColors: false, callbacks: {
            title: it => shortDate(LD[it[0].dataIndex]),
            label: ct => fmt(ct.parsed.y) + '  distinct keys' } }),
          endLabels: { enabled: true }
        },
        scales: {
          x: Object.assign(gridX(), { ticks: Object.assign(gridX().ticks, {
            callback: dateTicks(LD, (d, i) => i % 3 === 0, shortDay) }) }),
          y: gridY({ beginAtZero: true })
        }
      },
      plugins: [crosshair, endLabels]
    });
    c.done();
    tableView(c.host, ['Date', 'Active keys'], LD.map((d, i) => [d, fmt(live('api_keys_active_30d')[i])]));
  }
}

/* ==================== size & growth: range + scale ==================== */
/* The size family is the only one with real history, so it is the only one that
   can be windowed. RANGE is in days; 0 means the whole run. */
const RANGES = [{ id: '3m', label: '3M', days: 90 }, { id: '1y', label: '1Y', days: 365 },
                { id: '5y', label: '5Y', days: 1825 }, { id: 'all', label: 'All', days: 0 }];
const SCALES = [{ id: 'abs', label: 'Absolute' }, { id: 'idx', label: 'Indexed' }];
/* 1Y by default: long enough to hold a season, short enough that the last month
   of activity is not a hairline against nine years of backfill. */
let gRange = '1y', gScale = 'abs';

/* Indices of the chosen window within the history series. */
function windowSlice() {
  const days = (RANGES.find(r => r.id === gRange) || RANGES[3]).days;
  return (!days || days >= HD.length) ? 0 : HD.length - days;
}

function renderTotals() {
  const from = windowSlice(), dates = HD.slice(from), whole = from === 0;
  const abs = ENTC.map(e => hist('total_' + e.key).slice(from).map(v => v > 0 ? v : null));
  const idx = gScale === 'idx';
  /* Base each line at its own first in-window value, so the lines answer
     "how much has each grown since then", not "which one is biggest". */
  const base = abs.map(a => a.find(v => v != null) || null);
  const plotted = idx ? abs.map((a, i) => base[i] ? a.map(v => v == null ? null : v / base[i] * 100) : a) : abs;

  const flat = plotted.flat().filter(v => v != null && v > 0);
  const spread = flat.length ? Math.max.apply(null, flat) / Math.min.apply(null, flat) : 1;
  /* Absolute totals always need a log axis — three entities two decades apart.
     Indexed values usually sit in a narrow band, where linear reads the small
     changes better; it only needs a log axis when the window spans a big multiple. */
  const useLog = !idx || spread > 30;

  const c = card('c-totals', {
    title: 'Catalog size, ' + shortDate(dates[0]) + ' \u2192 ' + shortDate(dates[dates.length - 1]),
    sub: idx
      ? 'Each entity indexed to 100 at the start of the window. ' +
        (useLog ? 'Log scale — the window spans a large multiple.' : 'Linear scale.')
      : 'Total records per entity. Log scale — the entities sit two orders of magnitude apart.',
    note: (idx
      ? 'Indexed values are ratios, not counts: a line at 140 means that entity is 1.4\u00d7 its size at the start of the window. Tooltips and the table below carry the real counts. '
      : 'Locations begin at their first record in May 2020; zero is not plottable on a log axis, so each line starts at its first record. ') +
      'Backfilled from createdAt, so records deleted since are absent from every earlier point.'
  });

  const sets = ENTC.map((e, i) => lineOf(e.label, tok('--series-' + (i + 1)), plotted[i], i));

  const importIdx = HD.indexOf('2020-05-02') - from;
  /* the import rule + its caption live in a reserved band above the plot */
  const importLine = {
    id: 'importLine',
    beforeDatasetsDraw(chart) {
      if (importIdx < 0) return;
      const x = chart.scales.x.getPixelForValue(importIdx), a = chart.chartArea, ctx = chart.ctx;
      ctx.save();
      ctx.beginPath(); ctx.moveTo(x, a.top); ctx.lineTo(x, a.bottom);
      ctx.lineWidth = 1; ctx.strokeStyle = tok('--axis'); ctx.stroke();
      ctx.font = '11.5px ' + FONT; ctx.fillStyle = tok('--text-secondary');
      ctx.textBaseline = 'alphabetic';
      ctx.fillText('2 May 2020 bulk import', x + 7, a.top - 8);
      ctx.restore();
    }
  };

  mk(c.plot(320), {
    type: 'line',
    data: { labels: dates, datasets: sets },
    options: {
      animation: false, responsive: true, maintainAspectRatio: false,
      layout: { padding: { top: importIdx >= 0 ? 20 : 6, right: 62 } },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: legendStyle(true),
        tooltip: tooltipStyle({ callbacks: {
          title: it => shortDate(dates[it[0].dataIndex]),
          label: ct => idx
            ? fmt(ct.parsed.y) + '  ' + ct.dataset.label + ' (' + fmt(abs[ct.datasetIndex][ct.dataIndex]) + ')'
            : fmt(ct.parsed.y) + '  ' + ct.dataset.label } }),
        endLabels: { enabled: true }
      },
      scales: {
        x: Object.assign(gridX(), { ticks: Object.assign(gridX().ticks, { callback: spanTicks(dates) }) }),
        y: useLog
          ? gridY({ type: 'logarithmic', ticks: {
              color: tok('--text-muted'), font: { size: 11, family: FONT }, padding: 8, callback: logTick } })
          : gridY({})
      }
    },
    plugins: [crosshair, endLabels, importLine]
  });

  /* One row per period end \u2014 years over the whole run, months inside a window.
     Keeping the LAST index of each period means the table ends where the chart does. */
  const last = new Map();
  dates.forEach((d, i) => last.set(whole ? d.slice(0, 4) : d.slice(0, 7), i));
  c.done();
  tableView(c.host, ['Date', 'Beers', 'Brewers', 'Locations'],
    [...last.values()].map(i => [dates[i], fmt(abs[0][i]), fmt(abs[1][i]), fmt(abs[2][i])]),
    whole ? 'Table view (year-end counts)' : 'Table view (month-end counts)');
}

function renderRate() {
  const from = windowSlice(), dates = HD.slice(from);
  const label = (RANGES.find(r => r.id === gRange) || RANGES[3]).label;
  const c = card('c-rate', {
    title: 'Creation rate, ' + (gRange === 'all' ? 'whole run' : 'last ' + label),
    sub: 'Trailing-30-day new records, on a linear axis.' +
      (gRange === 'all' ? ' The 2020 import dwarfs everything else at this range.' : ''),
    note: 'August 2026 is the busiest stretch since the import: ' + fmt(now('created_beer_30d')) +
          ' beers and ' + fmt(now('created_location_30d')) + ' locations in the last 30 days.'
  });
  const sets = ENTC.map((e, i) =>
    lineOf(e.label, tok('--series-' + (i + 1)), hist('created_' + e.key + '_30d').slice(from), i));
  mk(c.plot(240), {
    type: 'line',
    data: { labels: dates, datasets: sets },
    options: {
      animation: false, responsive: true, maintainAspectRatio: false,
      layout: { padding: { top: 6, right: 58 } },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: legendStyle(true),
        tooltip: tooltipStyle({ callbacks: {
          title: it => shortDate(dates[it[0].dataIndex]),
          label: ct => fmt(ct.parsed.y) + '  ' + ct.dataset.label } }),
        endLabels: { enabled: true }
      },
      scales: {
        x: Object.assign(gridX(), { ticks: Object.assign(gridX().ticks, { callback: spanTicks(dates) }) }),
        y: gridY({ beginAtZero: true })
      }
    },
    plugins: [crosshair, endLabels]
  });
  /* The other three windows are one number each — a table, not four more lines. */
  const ORDER = [['beer', 'Beers'], ['brewer', 'Brewers'], ['location', 'Locations']];
  c.body.appendChild(el('p', { class: 'rowlab', style: 'margin-top:18px',
    text: 'New records by window, as of ' + shortDate(D.asOf) }));
  c.body.appendChild(el('div', { class: 'scroller' }, [el('table', {}, [
    el('thead', {}, [el('tr', {}, ['Window', ...ORDER.map(o => o[1])].map(t => el('th', { text: t })))]),
    el('tbody', {}, [1, 7, 30, 365].map(w => el('tr', {}, [
      el('td', { text: 'last ' + w + (w === 1 ? ' day' : ' days') }),
      ...ORDER.map(([e]) => el('td', { text: fmt(now('created_' + e + '_' + w + 'd')) }))
    ])))
  ])]));
  c.done();
  c.host.appendChild(el('p', { class: 'note',
    text: 'The 365-day and 30-day beer figures nearly agree (' + fmt(now('created_beer_365d')) + ' vs ' +
          fmt(now('created_beer_30d')) + '): almost everything added in the past year arrived in the past month.' }));
  tableView(c.host, ['Date', 'Beers', 'Brewers', 'Locations'],
    LD.map(d => [d, fmt(hAt('created_beer_30d', d)), fmt(hAt('created_brewer_30d', d)), fmt(hAt('created_location_30d', d))]),
    'Table view (trailing 30-day series, live window)');
}

/* One row of buttons per control; the pressed one is the current state. */
function segmented(hostId, items, get, set) {
  const host = document.getElementById(hostId);
  host.textContent = '';
  items.forEach(it => {
    const b = el('button', { type: 'button', text: it.label });
    const paint = () => {
      const on = get() === it.id;
      b.className = on ? 'is-on' : '';
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    };
    b.addEventListener('click', () => {
      if (get() === it.id) return;
      set(it.id);
      host.parentElement.querySelectorAll('.ctl button').forEach(x => x.dispatchEvent(new Event('repaint')));
      rebuild('c-totals', renderTotals);
      rebuild('c-rate', renderRate);
    });
    b.addEventListener('repaint', paint);
    paint();
    host.appendChild(b);
  });
}

/* ============================ theme ============================ */
const btn = document.getElementById('themeBtn');
const currentDark = () => {
  const stamp = document.documentElement.getAttribute('data-theme');
  return stamp ? stamp === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
};
const syncBtn = () => { btn.textContent = currentDark() ? 'Light mode' : 'Dark mode'; };
btn.addEventListener('click', () => {
  document.documentElement.setAttribute('data-theme', currentDark() ? 'light' : 'dark');
  syncBtn(); renderAll();          /* dark is selected, not flipped — rebuild against its own steps */
});
matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  if (!document.documentElement.getAttribute('data-theme')) { syncBtn(); renderAll(); }
});

if (HAS_CHART) {
  Chart.defaults.font.family = FONT;
  Chart.defaults.font.size = 12;
  Chart.defaults.animation = false;
}
syncBtn();
segmented('rangeCtl', RANGES, () => gRange, v => { gRange = v; });
segmented('scaleCtl', SCALES, () => gScale, v => { gScale = v; });
renderAll();
</script>
</body>
</html>
