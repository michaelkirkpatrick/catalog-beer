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

$sitemap_path = ROOT . '/sitemap.xml';

// --- API Helper ---
$api = new API();

function request($endpoint){
	global $api;
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

// --- Open File ---
$file = fopen($sitemap_path, 'w');
if(!$file){
	exit("Error: Could not open $sitemap_path for writing.\n");
}

echo "Starting sitemap generation ($environment)...\n";

$urlCount = 0;
$sitemapCount = 1;

// Preamble
fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

// --- Helper: write a <url> entry ---
function writeUrl($file, $loc, $lastmod, $changefreq, $priority){
	fwrite($file, '<url>' . "\n");
	fwrite($file, '  <loc>' . $loc . '</loc>' . "\n");
	fwrite($file, '  <lastmod>' . date('c', $lastmod) . '</lastmod>' . "\n");
	fwrite($file, '  <changefreq>' . $changefreq . '</changefreq>' . "\n");
	fwrite($file, '  <priority>' . $priority . '</priority>' . "\n");
	fwrite($file, '</url>' . "\n");
}

// --- Helper: split sitemap at 50,000 URL limit ---
function checkSitemapLimit(&$file, &$urlCount, &$sitemapCount, $sitemap_base){
	if($urlCount >= 50000){
		fwrite($file, '</urlset>' . "\n");
		fclose($file);

		$new_path = $sitemap_base . '/sitemap' . $sitemapCount . '.xml';
		$file = fopen($new_path, 'w');
		if(!$file){
			exit("Error: Could not open $new_path for writing.\n");
		}
		$sitemapCount++;
		$urlCount = 0;

		fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
		echo "-- Created new sitemap file --\n";
	}
}

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

// Fetch collection last-modified timestamps (used for homepage, /brewer, /beer)
$brewerListLastMod = null;
$beerListLastMod = null;

$data = request('/brewer/last-modified');
if($data && isset($data->last_modified)){
	$brewerListLastMod = $data->last_modified;
}else{
	echo "Warning: Could not fetch /brewer/last-modified\n";
}

$data = request('/beer/last-modified');
if($data && isset($data->last_modified)){
	$beerListLastMod = $data->last_modified;
}else{
	echo "Warning: Could not fetch /beer/last-modified\n";
}

foreach($pages as $slug => $info){
	switch($slug){
		case '':
			if($brewerListLastMod !== null && $beerListLastMod !== null){
				$lastMod = max($brewerListLastMod, $beerListLastMod);
			}else{
				$lastMod = filemtime(ROOT . '/' . $info['file']);
			}
			break;
		case 'brewer':
			$lastMod = $brewerListLastMod ?? filemtime(ROOT . '/' . $info['file']);
			break;
		case 'beer':
			$lastMod = $beerListLastMod ?? filemtime(ROOT . '/' . $info['file']);
			break;
		default:
			$lastMod = filemtime(ROOT . '/' . $info['file']);
	}

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
		checkSitemapLimit($file, $urlCount, $sitemapCount, ROOT);
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
		checkSitemapLimit($file, $urlCount, $sitemapCount, ROOT);
	}

	if(!empty($apiData->next_cursor)){
		$cursor = $apiData->next_cursor;
	}else{
		break;
	}
}

echo "Beers complete\n";

// --- Close ---
fwrite($file, '</urlset>' . "\n");
fclose($file);

echo "Sitemap generation complete. File: $sitemap_path\n";
?>