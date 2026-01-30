<?php
class Database {
	
	// Public Variable
	public $result;
	public $error = false;
	public $errorMsg = '';
	public $insertID;
	
	// Private Variables
	private $mysqli;	
	
	function __construct(){
		// Establish Environment
		if(defined('ENVIRONMENT')){
			if(ENVIRONMENT === 'staging'){
				$password = DB_PASSWORD_STAGING;
			}elseif(ENVIRONMENT === 'production'){
				$password = DB_PASSWORD_PRODUCTION;
			}

			// Connect to Server
			$this->mysqli = new mysqli(DB_HOST, DB_USER, $password, DB_NAME);
			if($this->mysqli->connect_error){
				//die('Connect Error (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
				exit();
			}else{
				// Set Character Set
				if(!$this->mysqli->set_charset("utf8")){
					//printf("Error loading character set utf8: %s\n", $this->mysqli->error);
					exit();
				}
			}
		}else{
			// Environment Not Set
			exit();
		}
	}
	
	// ----- Query -----
	public function query($query){
		if(isset($this->mysqli)){
			// What type of query are we doing?
			$returnResult = array('SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN');
			$substring = substr($query, 0, 20);
			$exploded = explode(' ', $substring);

			if(in_array($exploded[0], $returnResult)){
				// Query With Result
				if($this->result = $this->mysqli->query($query)){
					// Successful Query
				}else{
					// Query Error
					$this->error = true;
				}
			}else{
				// True False Query
				if($this->mysqli->query($query) === true){
					// Successful Query
					if($exploded[0] === 'INSERT'){
						$this->insertID = $this->mysqli->insert_id;	
					}
				}else{
					// Query Error
					$this->error = true;
				}
			}

			if($this->error){
				// Log Error
				$errorLog = new LogError();
				$errorLog->errorNumber = 'C5';
				$errorLog->fileName = 'Database.class.php';
				$errorLog->errorMsg = 'Query Error';
				$errorLog->badData = 'Query: ' . $query . ' MySQL Error: ' . $this->mysqli->error;
				$errorLog->write();

				// Generic Error Message
				$this->errorMsg = 'Sorry, there was an internal error querying our database. I\'ve logged the error for our support team so they can diagnose and fix the issue.';
			}
		}else{
			// mysqli not set
			$this->error = true;
			$this->errorMsg = 'Whoops, looks like a bug on our end. We\'ve logged the issue and our support team will look into it.';
			
			// Log Error
			$errorLog = new LogError();
			$errorLog->errorNumber = 'C6';
			$errorLog->errorMsg = '$this->mysqli not set';
			$errorLog->badData = "Query: $query";
			$errorLog->filename = 'Database.class.php';
			$errorLog->write();
		}
	}
	
	// ----- Escape -----
	public function escape($string){
		$escaped = $this->mysqli->real_escape_string($string);
		$escaped = str_replace("%", "\%", $escaped);
		$escaped = str_replace("_", "\_", $escaped);
		return $escaped;
	}
	
	// ----- Result Arra -----
	public function resultArray(){
		$array = $this->result->fetch_array(MYSQLI_ASSOC);
		return $array;
	}
	
	// ----- Single Result -----
	public function singleResult($key){
		$array = $this->result->fetch_array(MYSQLI_ASSOC);
		return $array[$key];
	}
}
?>