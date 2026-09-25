<?php
class API {

    // Variables
    private $masterAPIKey = '';
    private $usersAPIKey = '';
    private $apiKey = '';
    private $url = 'https://api.catalog.beer';

    public $error = false;
    public $errorMsg = '';
    public $httpcode = 0;

    function __construct(){
        // Establish Environment
        if(defined('ENVIRONMENT')){
            if(ENVIRONMENT === 'staging'){
                $this->url = 'https://api-staging.catalog.beer';
                $this->masterAPIKey = API_KEY_STAGING;
            }else{
                $this->masterAPIKey = API_KEY_PRODUCTION;
            }
        }
        
        // Default API Key
        $this->apiKey = $this->masterAPIKey;
        
        // Get API Key
        if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['userID'])){
            $apiKeyResp = $this->request('GET', '/users/' . $_SESSION['userID'] . '/api-key', '');
            if(!$this->error){
                $apiKeyJSON = json_decode($apiKeyResp);
                if(isset($apiKeyJSON->error)){
                    // Error
                    $this->error = true;
                    $this->errorMsg = $apiKeyJSON->error_msg;
                }else{
                    if(isset($apiKeyJSON->api_key)){
                        if(!empty($apiKeyJSON->api_key)){
                            // Save API Key
                            $this->apiKey = $apiKeyJSON->api_key;
                            $this->usersAPIKey = $apiKeyJSON->api_key;
                        }
                    }
                }
            }
        }
    }
    
    // Which key a request goes out under. The website reads the catalog on
    // behalf of whoever is looking at it, so public reads (GET /beer, /brewer,
    // /location, /style, ...) always use the master key: the API neither logs
    // nor counts master-key requests, so browsing the site never draws on the
    // visitor's own monthly allowance (and can't 402 the site once they're over
    // it), and master-only shapes like the list pages' ?enriched=1 come back
    // whether or not they're logged in. The user's key is presented only where
    // the API needs to know who is asking: every write (attribution, privilege
    // checks) and the handful of GETs that answer per key or gate on the key's
    // admin flag. A user calling the API directly is unaffected by any of this.
    private function keyFor($type, $endpoint){
        // Account plumbing the site does as itself, whatever the method.
        foreach(array('/login', '/users', '/error-log', '/location/map') as $prefix){
            if(strpos($endpoint, $prefix) === 0){ return $this->masterAPIKey; }
        }
        if(empty($this->usersAPIKey)){ return $this->masterAPIKey; }
        if($type !== 'GET'){ return $this->usersAPIKey; }
        // Per-user GETs: the key's own usage and billing, the admin-gated
        // dashboards and queues, and the caller's role on a brewer.
        foreach(array('/usage', '/billing', '/activity', '/metrics', '/review', '/brewer-lead') as $prefix){
            if(strpos($endpoint, $prefix) === 0){ return $this->usersAPIKey; }
        }
        if(preg_match('#^/brewer/[-0-9a-f]{36}/permissions#', $endpoint)){ return $this->usersAPIKey; }
        return $this->masterAPIKey;
    }

    public function request($type, $endpoint, $data){
        $this->apiKey = $this->keyFor($type, $endpoint);

        // Headers & Options
        $headerArray = array(
            "accept: application/json",
            "authorization: Basic " . base64_encode($this->apiKey . ":"),
        );
        if($type === 'POST' || $type === 'PUT' || $type === 'PATCH'){
            $headerArray[] = "content-type: application/json";
        }
        
        $optionsArray = array(
            CURLOPT_URL => $this->url . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_HTTPHEADER => $headerArray
        );
        
        // Request Type
        switch($type){
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $json = json_encode($data);
                $optionsArray[CURLOPT_POSTFIELDS] = $json;
                break;
        }
                
        // Create cURL Request
        $curl = curl_init();
        curl_setopt_array($curl, $optionsArray);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $this->httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if(!empty($err)){           
            // cURL Error
            $this->error = true;
            $this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
            
            // Log Error
            $errorLog = new LogError();
            $errorLog->errorNumber = 'C2';
            $errorLog->errorMsg = 'cURL Error';
            $errorLog->badData = $err;
            $errorLog->filename = 'API.class.php';
            $errorLog->write();
        }else{
            return $response;
        }
    }

    // True when the backend is unreachable or returned a server error — i.e. the
    // request did not complete with a usable response. httpcode is 0 on a cURL
    // transport failure and >= 500 on a server error; 4xx (not-found / auth /
    // validation) is intentionally NOT treated as "unavailable" so pages keep
    // handling those themselves. Reflects the most recent request(); call it
    // immediately after the request whose outcome you're checking.
    public function unavailable(): bool {
        return $this->httpcode === 0 || $this->httpcode >= 500;
    }
}
?>