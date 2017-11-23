<?php
function request($endpoint){
		
	$url = 'https://api.catalog.beer';
	$apiKey = '';

	// Headers & Options
	$headerArray = array(
		"accept: application/json",
		"authorization: Basic " . base64_encode($apiKey . ":"),
	);

	$optionsArray = array(
		CURLOPT_URL => $url . $endpoint,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => $headerArray
	);

	// Create cURL Request
	$curl = curl_init();
	curl_setopt_array($curl, $optionsArray);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if(empty($err)){			
		return $response;
	}
}

echo "Starting sitemap generation...\n";

// HTTP Prefix
$prefix = 'https://catalog.beer/';

// Path to File
$sitemap_path = '/var/www/html/catalog.beer/public_html/sitemap.xml';
$file = fopen($sitemap_path, "w+");
$urlCount = 0;
$sitemapCount = 1;

// Cursor Start
$cursor = '';
	
// Preamble
fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

// Current time
$current_time = time();

// --- (1) Top Level Pages ---

$url = array('', 'brewer', 'beer', 'brewer/add', 'api-docs', 'contact', 'privacy', 'terms', 'signup', 'login');
$filenames = array('index.php', 'brewer-list.php', 'beer-list.php', 'brewer-add.php', 'api-docs.php', 'contact.php', 'privacy.php', 'terms.php', 'create-account.php', 'login.php');

for($i = 0; $i < count($url); $i++){
	switch($url[$i]){
		case 'brewer':
			$apiResp = request('/brewer/last-modified');
			$apiData = json_decode($apiResp);
			$lastMod = $apiData->last_modified;
			$priority = 1;
			break;
		case 'beer':
			$apiResp = request('/beer/last-modified');
			$apiData = json_decode($apiResp);
			$lastMod = $apiData->last_modified;
			$priority = 1;
			break;
		case '':
			// Priority
			$priority = 0.7;
			
			// Last Brewer Modified
			$apiResp = request('/brewer/last-modified');
			$apiData = json_decode($apiResp);
			$brewerLastMod = $apiData->last_modified;
			
			// Last Beer Modified
			$apiResp = request('/beer/last-modified');
			$apiData = json_decode($apiResp);
			$beerLastMod = $apiData->last_modified;
			
			// Last Modified
			$lastMod = max($brewerLastMod, $beerLastMod);
			break;
		default:
			$lastMod = filemtime($filenames[$i]);
			$priority = 0.3;
	}
	
	fwrite($file, '<url>' . "\n");
	fwrite($file, '  <loc>' . $prefix . $url[$i] . '</loc>' . "\n");
	fwrite($file, '  <lastmod>' . date ('c', $lastMod) . '</lastmod>' . "\n");
	fwrite($file, '  <changefreq>monthly</changefreq>' . "\n");
	fwrite($file, '  <priority>' . $priority . '</priority>' . "\n");
	fwrite($file, '</url>' . "\n");
	$urlCount++;
}

echo "Basics complete\n";

// --- (1) Brewers ---

// Get total number of brewers
$apiResp = request('/brewer/count');
$apiData = json_decode($apiResp);
$numBrewers = $apiData->value;
$count = 500;
$totalPages = round($numBrewers/$count, 0, PHP_ROUND_HALF_UP);

echo "Starting brewers...\n";

// Loop through Pages
for($i=1; $i<=$totalPages; $i++){
	echo "Page $i of $totalPages\n";
	
	// Set Endpoint
	if(!empty($cursor)){
		$url = '/brewer?count=' . $count . '&cursor=' . $cursor;
	}else{
		$url = '/brewer?count=' . $count;
	}

	// Peform API Request
	$apiResp = request($url);
	$apiData = json_decode($apiResp);
	
	// Set Cursor
	if(isset($apiData->next_cursor)){
		$cursor = $apiData->next_cursor;	
	}else{
		$cursor = '';
	}
	
	// Loop through results
	for($j=0; $j<count($apiData->data); $j++){
		// Get Last Mod
		$apiResp2 = request('/brewer/last-modified/' . $apiData->data[$j]->id);
		$apiData2 = json_decode($apiResp2);
		$brewerLastMod = $apiData2->last_modified;
		
		// Save to Sitemap Variable
		fwrite($file, '<url>' . "\n");
		fwrite($file, '  <loc>' . $prefix . 'brewer/' . $apiData->data[$j]->id . '</loc>' . "\n");
		fwrite($file, '  <lastmod>' . date ('c', $brewerLastMod) . '</lastmod>' . "\n");
		fwrite($file, '  <changefreq>monthly</changefreq>' . "\n");
		fwrite($file, '  <priority>0.5</priority>' . "\n");
		fwrite($file, '</url>' . "\n");
		$urlCount++;
		
		if($urlCount == 50000){
			// Reset Count
			$urlCount = 0;
			
			// Close Current File
			fwrite($file, '</urlset>' . "\n");
			fclose($file);
			
			// Start New File
			echo "-- Creating new sitemap --\n";
			$sitemap_path = '/var/www/html/catalog.beer/public_html/sitemap' . $sitemapCount . '.xml';
			$file = fopen($sitemap_path, "w+");
			$sitemapCount++;
			fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
			fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
		}
	}
}

echo "***** Brewers complete *****\n";
echo "Starting beers...\n";

// --- (2) Beers ---

// Get total number of beers
$apiResp = request('/beer/count');
$apiData = json_decode($apiResp);
$numBeers = $apiData->value;
$count = 500;
$totalPages = round($numBeers/$count, 0, PHP_ROUND_HALF_UP);

// Loop through Pages
for($i=1; $i<=$totalPages; $i++){
	echo "Page $i of $totalPages\n";
	
	// Set Endpoint
	if(!empty($cursor)){
		$url = '/beer?count=' . $count . '&cursor=' . $cursor;
	}else{
		$url = '/beer?count=' . $count;
	}

	// Peform API Request
	$apiResp = request($url);
	$apiData = json_decode($apiResp);
	
	// Set Cursor
	if(isset($apiData->next_cursor)){
		$cursor = $apiData->next_cursor;	
	}else{
		$cursor = '';
	}
	
	// Loop through results
	for($j=0; $j<count($apiData->data); $j++){
		// Get Last Mod
		$apiResp2 = request('/beer/last-modified/' . $apiData->data[$j]->id);
		$apiData2 = json_decode($apiResp2);
		$brewerLastMod = $apiData2->last_modified;
		
		// Save to Sitemap Variable
		fwrite($file, '<url>' . "\n");
		fwrite($file, '  <loc>' . $prefix . 'beer/' . $apiData->data[$j]->id . '</loc>' . "\n");
		fwrite($file, '  <lastmod>' . date ('c', $brewerLastMod) . '</lastmod>' . "\n");
		fwrite($file, '  <changefreq>yearly</changefreq>' . "\n");
		fwrite($file, '  <priority>0.4</priority>' . "\n");
		fwrite($file, '</url>' . "\n");
		$urlCount++;
		
		if($urlCount == 50000){
			// Reset Count
			$urlCount = 0;
			
			// Close Current File
			fwrite($file, '</urlset>' . "\n");
			fclose($file);
			
			// Start New File
			echo "-- Creating new sitemap --\n";
			$sitemap_path = '/var/www/html/catalog.beer/public_html/sitemap' . $sitemapCount . '.xml';
			$file = fopen($sitemap_path, "w+");
			$sitemapCount++;
			fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
			fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
		}
	}
}
echo "***** Beer complete *****\n";

// ----- Close XML -----
fwrite($file, '</urlset>' . "\n");

// Close File
fclose($file);

echo "Complete\n";
?>