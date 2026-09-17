<?php
// --- CLI Only ---
if(php_sapi_name() !== 'cli'){
    http_response_code(403);
    exit('This script must be run from the command line.');
}

// --- Environment Argument ---
$environment = $argv[1] ?? 'production';
if(!in_array($environment, ['staging', 'production'])){
    exit("Invalid environment: $environment. Use 'staging' or 'production'.\n");
}

// --- Bootstrap (no initialize.php — no session, CSRF, or nav needed) ---
define('ROOT', dirname(__FILE__));
define('ENVIRONMENT', $environment);

require_once ROOT . '/config/config.php';

date_default_timezone_set('America/Los_Angeles');

spl_autoload_register(function ($class_name) {
    require_once ROOT . '/classes/' . $class_name . '.class.php';
});

// --- Configuration ---
if(ENVIRONMENT === 'staging'){
    $prefix = 'https://staging.catalog.beer/';
}else{
    $prefix = 'https://catalog.beer/';
}

// --- API Helper ---
$api = new API();

// One list request, retried on transport failure or a 5xx. On 14 Sep 2026 the
// 04:00 run hit a stall (the top of the hour is the box's most contended
// minute), every section timed out once and aborted, and the run published a
// 13-URL sitemap in place of 80,000. Three attempts with a pause between them
// ride out that kind of blip; a 4xx (bad cursor, auth) is not transient and
// is not retried.
function request($endpoint){
    global $api;
    $delays = [0, 5, 15];
    foreach($delays as $attempt => $delay){
        if($delay > 0){
            echo "  retrying $endpoint in {$delay}s (attempt " . ($attempt + 1) . ")\n";
            sleep($delay);
        }
        $api->error = false;
        $api->errorMsg = '';
        $response = $api->request('GET', $endpoint, '');
        if($api->error || $api->unavailable()){
            continue;
        }
        $data = json_decode($response);
        if(isset($data->error)){
            return false;
        }
        return $data;
    }
    return false;
}

// --- Helper: write a <url> entry ---
function writeUrl($file, $loc, $lastmod, $changefreq, $priority){
    fwrite($file, '<url>' . "\n");
    fwrite($file, '  <loc>' . $loc . '</loc>' . "\n");
    fwrite($file, '  <lastmod>' . date('c', $lastmod) . '</lastmod>' . "\n");
    fwrite($file, '  <changefreq>' . $changefreq . '</changefreq>' . "\n");
    fwrite($file, '  <priority>' . $priority . '</priority>' . "\n");
    fwrite($file, '</url>' . "\n");
}

// --- Helper: start a new numbered sitemap file ---
// Written as sitemapN.tmp.xml and swapped into place only at the end, once
// every section has succeeded and the URL count is sane (see publish below).
// The .tmp.xml suffix keeps the files inside the existing sitemap*.xml
// patterns in .gitignore and deploy.sh's excludes.
function tmpPath($number){
    return ROOT . '/sitemap' . $number . '.tmp.xml';
}
function openSitemapFile($number){
    $path = tmpPath($number);
    $file = fopen($path, 'w');
    if(!$file){
        exit("Error: Could not open $path for writing.\n");
    }
    fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
    return $file;
}

// --- Helper: close a sitemap file ---
function closeSitemapFile($file){
    fwrite($file, '</urlset>' . "\n");
    fclose($file);
}

// --- Helper: split sitemap at 50,000 URL limit ---
function checkSitemapLimit(&$file, &$urlCount, &$sitemapNumber){
    if($urlCount >= 50000){
        closeSitemapFile($file);
        $sitemapNumber++;
        $file = openSitemapFile($sitemapNumber);
        $urlCount = 0;
        echo "-- Started sitemap$sitemapNumber.xml --\n";
    }
}

// Count the <url> entries in whatever is published now: the numbered files
// when sitemap.xml is an index, else sitemap.xml itself. 0 on a first run.
function publishedUrlCount(){
    $total = 0;
    foreach(glob(ROOT . '/sitemap*.xml') as $path){
        $basename = basename($path);
        if($basename === 'sitemap.xml' || preg_match('/^sitemap\d+\.xml$/', $basename)){
            $total += substr_count(file_get_contents($path), '<url>');
        }
    }
    return $total;
}

// --- Start ---
echo "Starting sitemap generation ($environment)...\n";

// Section failures below are logged and skipped so one API hiccup doesn't
// zero out the whole sitemap — but cron must still hear about them, so any
// failure flips this and the script exits non-zero.
$hadErrors = false;

$sitemapNumber = 0;
$urlCount = 0;
$file = openSitemapFile($sitemapNumber);

// --- (1) Top-Level Pages ---

// Public, indexable pages only. Auth-gated and account pages (/login, /signup,
// /account, /brewer/add) are Disallow'd in robots.txt — listing them here would
// contradict that and waste crawl budget on pages that just bounce to /login.
$pages = [
    ''          => ['file' => 'index.php',          'priority' => 0.7],
    'brewer'    => ['file' => 'brewer-list.php',    'priority' => 1],
    'beer'      => ['file' => 'beer-list.php',      'priority' => 1],
    'style'     => ['file' => 'style-list.php',     'priority' => 1],
    'map'       => ['file' => 'brewery-map.php',    'priority' => 0.5],
    'api-docs'  => ['file' => 'api-docs.php',       'priority' => 0.3],
    'api-pricing' => ['file' => 'api-pricing.php',  'priority' => 0.3],
    'api-usage' => ['file' => 'api-usage.php',      'priority' => 0.3],
    'ai'        => ['file' => 'ai.php',             'priority' => 0.3],
    'whats-new' => ['file' => 'whats-new.php',      'priority' => 0.3],
    'contact'   => ['file' => 'contact.php',        'priority' => 0.3],
    'privacy'   => ['file' => 'privacy.php',        'priority' => 0.3],
    'terms'     => ['file' => 'terms.php',          'priority' => 0.3],
];

foreach($pages as $slug => $info){
    $lastMod = filemtime(ROOT . '/' . $info['file']);
    writeUrl($file, $prefix . $slug, $lastMod, 'monthly', $info['priority']);
    $urlCount++;
}

echo "Top-level pages complete\n";

// --- (2)-(4) Brewers, locations, beers ---
//
// Pages of 5,000: the list endpoints project each row to id/name/last_modified
// and answer a 5,000-row page in ~0.3s, so this is ~20 requests for the whole
// catalog instead of ~165 at 500 -- fewer chances for a transient stall to
// abort a section. (Beer and brewer accept up to 1,000,000; location has no
// cap on the list route.)
function fetchList($endpoint, $pathPrefix, $changefreq, $priority){
    global $file, $urlCount, $sitemapNumber, $hadErrors, $prefix;
    $cursor = '';
    while(true){
        $url = '/' . $endpoint . '?count=5000';
        if(!empty($cursor)){
            $url .= '&cursor=' . $cursor;
        }

        $apiData = request($url);
        if(!$apiData || !isset($apiData->data)){
            echo "Error: Failed to fetch $endpoint list. Aborting $endpoint section.\n";
            $hadErrors = true;
            return;
        }

        foreach($apiData->data as $row){
            if(!isset($row->id, $row->last_modified)){
                echo "Warning: Skipping $endpoint with missing data\n";
                continue;
            }
            writeUrl($file, $prefix . $pathPrefix . $row->id, $row->last_modified, $changefreq, $priority);
            $urlCount++;
            checkSitemapLimit($file, $urlCount, $sitemapNumber);
        }

        if(empty($apiData->next_cursor)){
            return;
        }
        if($apiData->next_cursor === $cursor){
            // A cursor that doesn't advance means the API is ignoring it (this
            // happened when the /location rewrite lacked QSA) -- without this
            // check the loop refetches page one forever, filling sitemap files
            // with duplicates until the disk objects.
            echo "Error: next_cursor did not advance; aborting $endpoint section.\n";
            $hadErrors = true;
            return;
        }
        $cursor = $apiData->next_cursor;
    }
}

echo "Starting brewers...\n";
fetchList('brewer', 'brewer/', 'monthly', 0.5);
echo "Brewers complete\n";

echo "Starting locations...\n";
fetchList('location', 'location/', 'monthly', 0.5);
echo "Locations complete\n";

echo "Starting beers...\n";
fetchList('beer', 'beer/', 'yearly', 0.4);
echo "Beers complete\n";

// --- (5) Styles ---

echo "Starting styles...\n";

$apiData = request('/style');
if(!$apiData || !isset($apiData->data)){
    echo "Error: Failed to fetch style list. Aborting style section.\n";
    $hadErrors = true;
}else{
    // Styles have no per-row last_modified; content changes ship as deploys,
    // so the page file's mtime is the honest signal.
    $styleLastMod = filemtime(ROOT . '/style.php');
    foreach($apiData->data as $style){
        if(!isset($style->id)){
            echo "Warning: Skipping style with missing data\n";
            continue;
        }
        writeUrl($file, $prefix . 'style/' . $style->id, $styleLastMod, 'monthly', 0.6);
        $urlCount++;
        checkSitemapLimit($file, $urlCount, $sitemapNumber);
    }
    echo "Styles complete\n";
}

// Family pages
$apiData = request('/style/parent');
if(!$apiData || !isset($apiData->data)){
    echo "Error: Failed to fetch family list. Aborting family section.\n";
    $hadErrors = true;
}else{
    $familyLastMod = filemtime(ROOT . '/style-family.php');
    foreach($apiData->data as $family){
        if(!isset($family->slug)){
            echo "Warning: Skipping family with missing data\n";
            continue;
        }
        writeUrl($file, $prefix . 'style/family/' . $family->slug, $familyLastMod, 'monthly', 0.6);
        $urlCount++;
        checkSitemapLimit($file, $urlCount, $sitemapNumber);
    }
    echo "Families complete\n";
}

// --- Close final sitemap file ---
closeSitemapFile($file);
$totalFiles = $sitemapNumber + 1;
$totalUrls = $sitemapNumber * 50000 + $urlCount;

// --- Publish, or don't ---
//
// A failed run must never replace a good sitemap. The old version of this
// script wrote straight to sitemapN.xml and then deleted "stale" files, so
// when every API section failed on 14 Sep 2026 it published the 13 static
// pages and removed the 75,000 catalog URLs. Now: any section error, or a
// URL count that fell well below the previous run's, leaves the published
// files untouched and exits non-zero. --force publishes regardless, for a
// deliberate shrink.
$previousUrls = publishedUrlCount();
$force = in_array('--force', $argv, true);
$shrunk = ($previousUrls > 0 && $totalUrls < 0.8 * $previousUrls);
if($shrunk){
    echo "Error: $totalUrls URLs is under 80% of the published $previousUrls.\n";
}
if(($hadErrors || $shrunk) && !$force){
    foreach(glob(ROOT . '/sitemap*.tmp.xml') as $path){
        unlink($path);
    }
    echo "NOT PUBLISHED -- the previous sitemap is still in place. Re-run with --force to publish anyway.\n";
    exit(1);
}

if($totalFiles === 1){
    // Single file -- becomes sitemap.xml itself, no index
    rename(tmpPath(0), ROOT . '/sitemap.xml');
}else{
    for($i = 0; $i < $totalFiles; $i++){
        rename(tmpPath($i), ROOT . '/sitemap' . $i . '.xml');
    }
    $index = fopen(ROOT . '/sitemap.tmp.xml', 'w');
    if(!$index){
        exit("Error: Could not open sitemap.tmp.xml for writing.\n");
    }
    fwrite($index, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($index, '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
    for($i = 0; $i < $totalFiles; $i++){
        fwrite($index, '  <sitemap>' . "\n");
        fwrite($index, '    <loc>' . $prefix . 'sitemap' . $i . '.xml</loc>' . "\n");
        fwrite($index, '  </sitemap>' . "\n");
    }
    fwrite($index, '</sitemapindex>' . "\n");
    fclose($index);
    rename(ROOT . '/sitemap.tmp.xml', ROOT . '/sitemap.xml');
}
echo "Published: $totalUrls URLs in " . ($totalFiles === 1 ? 'sitemap.xml' : "a sitemap index with $totalFiles files") . "\n";

// --- Remove stale numbered files from previous runs ---
// If a run ever produces fewer files than the last one (or collapses to a
// single sitemap.xml), the leftover sitemapN.xml would otherwise sit on the
// server forever: deploys can't clean it (sitemap*.xml is rsync-excluded,
// which also PROTECTS it from --delete) and crawlers keep fetching it.
foreach(glob(ROOT . '/sitemap*.xml') as $path){
    $basename = basename($path);
    if(preg_match('/^sitemap(\d+)\.xml$/', $basename, $matches)){
        $number = intval($matches[1]);
        // In the single-file case sitemap0 became sitemap.xml, so every
        // surviving numbered file is stale; otherwise anything >= the count.
        if($totalFiles === 1 || $number >= $totalFiles){
            unlink($path);
            echo "Removed stale $basename\n";
        }
    }
}

if($hadErrors){
    // Only reachable with --force: published, but say so loudly.
    echo "PUBLISHED WITH ERRORS (--force) -- one or more sections were skipped.\n";
    exit(1);
}
exit(0);

?>
