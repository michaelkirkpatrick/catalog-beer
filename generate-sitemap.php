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

require_once ROOT . '/classes/htmlpurifier/HTMLPurifier.auto.php';

// --- Configuration ---
if(ENVIRONMENT === 'staging'){
    $prefix = 'https://staging.catalog.beer/';
}else{
    $prefix = 'https://catalog.beer/';
}

// --- API Helper ---
$api = new API();

function request($endpoint){
    global $api;
    $api->error = false;
    $api->errorMsg = '';
    $response = $api->request('GET', $endpoint, '');
    if($api->error){
        return false;
    }
    $data = json_decode($response);
    if(isset($data->error)){
        return false;
    }
    return $data;
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
function openSitemapFile($number){
    $path = ROOT . '/sitemap' . $number . '.xml';
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

// --- (2) Brewers ---

echo "Starting brewers...\n";

$cursor = '';
$count = 500;

while(true){
    $url = '/brewer?count=' . $count;
    if(!empty($cursor)){
        $url .= '&cursor=' . $cursor;
    }

    $apiData = request($url);
    if(!$apiData || !isset($apiData->data)){
        echo "Error: Failed to fetch brewer list. Aborting brewer section.\n";
        $hadErrors = true;
        break;
    }

    foreach($apiData->data as $brewer){
        if(!isset($brewer->id, $brewer->last_modified)){
            echo "Warning: Skipping brewer with missing data\n";
            continue;
        }

        writeUrl($file, $prefix . 'brewer/' . $brewer->id, $brewer->last_modified, 'monthly', 0.5);
        $urlCount++;
        checkSitemapLimit($file, $urlCount, $sitemapNumber);
    }

    if(!empty($apiData->next_cursor)){
        $cursor = $apiData->next_cursor;
    }else{
        break;
    }
}

echo "Brewers complete\n";

// --- (3) Locations ---

echo "Starting locations...\n";

$cursor = '';

while(true){
    $url = '/location?count=' . $count;
    if(!empty($cursor)){
        $url .= '&cursor=' . $cursor;
    }

    $apiData = request($url);
    if(!$apiData || !isset($apiData->data)){
        // NOTE: GET /location is the newest list endpoint (Jul 2026) — if the
        // deployed API predates it, this section fails until the API deploys.
        echo "Error: Failed to fetch location list. Aborting location section.\n";
        $hadErrors = true;
        break;
    }

    foreach($apiData->data as $location){
        if(!isset($location->id, $location->last_modified)){
            echo "Warning: Skipping location with missing data\n";
            continue;
        }

        writeUrl($file, $prefix . 'location/' . $location->id, $location->last_modified, 'monthly', 0.5);
        $urlCount++;
        checkSitemapLimit($file, $urlCount, $sitemapNumber);
    }

    if(!empty($apiData->next_cursor)){
        $cursor = $apiData->next_cursor;
    }else{
        break;
    }
}

echo "Locations complete\n";

// --- (4) Beers ---

echo "Starting beers...\n";

$cursor = '';

while(true){
    $url = '/beer?count=' . $count;
    if(!empty($cursor)){
        $url .= '&cursor=' . $cursor;
    }

    $apiData = request($url);
    if(!$apiData || !isset($apiData->data)){
        echo "Error: Failed to fetch beer list. Aborting beer section.\n";
        $hadErrors = true;
        break;
    }

    foreach($apiData->data as $beer){
        if(!isset($beer->id, $beer->last_modified)){
            echo "Warning: Skipping beer with missing data\n";
            continue;
        }

        writeUrl($file, $prefix . 'beer/' . $beer->id, $beer->last_modified, 'yearly', 0.4);
        $urlCount++;
        checkSitemapLimit($file, $urlCount, $sitemapNumber);
    }

    if(!empty($apiData->next_cursor)){
        $cursor = $apiData->next_cursor;
    }else{
        break;
    }
}

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

// --- Generate sitemap.xml ---
$totalFiles = $sitemapNumber + 1;

if($totalFiles === 1){
    // Single file — just rename to sitemap.xml
    rename(ROOT . '/sitemap0.xml', ROOT . '/sitemap.xml');
    echo "Sitemap generation complete: sitemap.xml\n";
}else{
    // Multiple files — write a sitemap index
    $index = fopen(ROOT . '/sitemap.xml', 'w');
    if(!$index){
        exit("Error: Could not open sitemap.xml for writing.\n");
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
    echo "Sitemap generation complete: sitemap index with $totalFiles sitemap files\n";
}

// --- Remove stale numbered files from previous runs ---
// If a run ever produces fewer files than the last one (or collapses to a
// single sitemap.xml), the leftover sitemapN.xml would otherwise sit on the
// server forever: deploys can't clean it (sitemap*.xml is rsync-excluded,
// which also PROTECTS it from --delete) and crawlers keep fetching it.
foreach(glob(ROOT . '/sitemap*.xml') as $path){
    $basename = basename($path);
    if(preg_match('/^sitemap(\d+)\.xml$/', $basename, $matches)){
        $number = intval($matches[1]);
        // In the single-file case sitemap0.xml was renamed away, so every
        // surviving numbered file is stale; otherwise anything >= the count.
        if($totalFiles === 1 || $number >= $totalFiles){
            unlink($path);
            echo "Removed stale $basename\n";
        }
    }
}

// Non-zero exit so cron surfaces a partial sitemap instead of silently
// publishing it. The files written above are still in place — a partial
// sitemap beats none — but the failure must be visible.
if($hadErrors){
    echo "COMPLETED WITH ERRORS — one or more sections were skipped.\n";
    exit(1);
}
exit(0);
?>