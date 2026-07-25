<?php
/* ---
Search — server-side Algolia client for the /search results page.

POSTs to Algolia's multi-query endpoint so one HTTP round trip carries the
main query plus the "disjunctive" companion queries (type-tab counts, and
per-facet-group counts computed without that group's own refinement — Algolia
computes facet counts AFTER filters, so filtering type:beer would otherwise
report zero brewers on the Brewers tab).

Uses ALGOLIA_SEARCH_API_KEY — the search-only key that already shipped to
browsers in the old SiteSearch modal; using it server-side grants nothing new.

Usage:
    $search = new Search();
    $results = $search->multiQuery(array(
        array('query'=>'hazy ipa', 'hitsPerPage'=>25, 'facets'=>array('type')),
        ...
    ));
    // $results = decoded array of result objects (one per query), or null.
    // Check $search->error / $search->errorMsg after calls.

Failures are logged (C23-C25) and return null — the page degrades to an
inline "search is temporarily unavailable" notice, never a broken page.
--- */
class Search {

    public $error = false;
    public $errorMsg = '';
    public $httpcode = 0;

    private $filename = 'Search.class.php';

    /*
    Run one or more queries against the `catalog` index in a single request.

    Each entry in $queries is an associative array of Algolia search
    parameters (query, filters, facetFilters, numericFilters, facets,
    hitsPerPage, page, ...). Array-valued parameters are JSON-encoded the way
    the REST API expects; everything is sent as a URL-encoded params string.

    Returns the decoded `results` array (positionally matching $queries), or
    null on any transport/decode failure.
    */
    public function multiQuery($queries){
        $errorLog = new LogError();
        $errorLog->filename = $this->filename;

        // Build the request payload
        $requests = array();
        foreach($queries as $params){
            $encoded = array();
            foreach($params as $key => $value){
                if(is_array($value)){
                    // facets, facetFilters, numericFilters — JSON, then URL-encoded
                    $encoded[$key] = json_encode($value);
                }elseif(is_bool($value)){
                    $encoded[$key] = $value ? 'true' : 'false';
                }else{
                    $encoded[$key] = $value;
                }
            }
            $requests[] = array(
                'indexName' => 'catalog',
                'params'    => http_build_query($encoded)
            );
        }

        // DSN host — Algolia's managed round-robin for search traffic
        $url = 'https://' . ALGOLIA_APPLICATION_ID . '-dsn.algolia.net/1/indexes/*/queries';

        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(array('requests'=>$requests)),
            CURLOPT_HTTPHEADER => array(
                'X-Algolia-Application-Id: ' . ALGOLIA_APPLICATION_ID,
                'X-Algolia-API-Key: ' . ALGOLIA_SEARCH_API_KEY,
                'Content-Type: application/json'
            ),
            // Search is interactive: fail fast and show the outage notice
            // rather than holding the page open.
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5
        ));

        // Execute
        $response = curl_exec($ch);

        if(curl_errno($ch)){
            // cURL Error
            $this->error = true;
            $this->errorMsg = curl_error($ch);

            $errorLog->errorNumber = 'C23';
            $errorLog->errorMsg = 'Algolia multi-query cURL error';
            $errorLog->badData = $this->errorMsg;
            $errorLog->write();
            curl_close($ch);
            return null;
        }

        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($this->httpcode < 200 || $this->httpcode >= 300){
            // HTTP Error
            $this->error = true;
            $this->errorMsg = "Algolia returned HTTP {$this->httpcode}";

            $errorLog->errorNumber = 'C24';
            $errorLog->errorMsg = $this->errorMsg;
            $errorLog->badData = substr((string)$response, 0, 1000);
            $errorLog->write();
            return null;
        }

        $decoded = json_decode($response, true);
        if(!is_array($decoded) || !isset($decoded['results'])){
            // Decode Error
            $this->error = true;
            $this->errorMsg = 'Unable to decode Algolia response.';

            $errorLog->errorNumber = 'C25';
            $errorLog->errorMsg = $this->errorMsg;
            $errorLog->badData = substr((string)$response, 0, 1000);
            $errorLog->write();
            return null;
        }

        return $decoded['results'];
    }
}
?>
