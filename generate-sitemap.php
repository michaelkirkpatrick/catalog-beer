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

$sitemapNumber = 0;
$urlCount = 0;
$file = openSitemapFile($sitemapNumber);

// --- (1) Top-Level Pages ---

$pages = [
	''         => ['file' => 'index.php',          'priority' => 0.7],
	'brewer'   => ['file' => 'brewer-list.php',     'priority' => 1],
	'beer'     => ['file' => 'beer-list.php',       'priority' => 1],
	'brewer/add' => ['file' => 'brewer-add.php',    'priority' => 0.3],
	'api-docs' => ['file' => 'api-docs.php',        'priority' => 0.3],
	'contact'  => ['file' => 'contact.php',         'priority' => 0.3],
	'privacy'  => ['file' => 'privacy.php',         'priority' => 0.3],
	'terms'    => ['file' => 'terms.php',           'priority' => 0.3],
	'signup'   => ['file' => 'create-account.php',  'priority' => 0.3],
	'login'    => ['file' => 'login.php',           'priority' => 0.3],
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

// --- (3) Beers ---

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
?>