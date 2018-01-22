<?php
class API {
	
	// Variables
	private $masterAPIKey = '';
	private $apiKey = '';
	private $url = 'https://api.catalog.beer';
	
	public $error = false;
	public $errorMsg = '';
	public $httpCode = 0;
	
	function __construct(){
		// Establish Environment
		if(defined('ENVIRONMENT')){
			if(ENVIRONMENT == 'staging'){
				$this->url = 'https://api-staging.catalog.beer';
			}
		}
		
		// Default API Key
		$this->apiKey = $this->masterAPIKey;
		
		// Get API Key
		if(isset($_SESSION['userID'])){
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
						}
					}
				}
			}
		}
	}
	
	public function request($type, $endpoint, $data){
		
		// Admin Endpoint?
		// /login and /users
		if(substr($endpoint, 0, 6) == '/login' || substr($endpoint, 0, 6) == '/users' || substr($endpoint, 0, 21) == '/brewer/last-modified'){
			$this->apiKey = $this->masterAPIKey;
		}
		
		// Headers & Options
		$headerArray = array(
			"accept: application/json",
			"authorization: Basic " . base64_encode($this->apiKey . ":"),
		);
		
		$optionsArray = array(
			CURLOPT_URL => $this->url . $endpoint,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $type,
			CURLOPT_HTTPHEADER => $headerArray
		);
		
		// Request Type
		switch($type){
			case 'POST':
				$json = json_encode($data);
				$headerArray[] = "content-type: application/json";
				$optionsArray[CURLOPT_POSTFIELDS] = $json;
				break;
			case 'PUT':
				$json = json_encode($data);
				$headerArray[] = "content-type: application/json";
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
}
?>