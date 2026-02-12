<?php
class Database {

	// Properties
	private mysqli $mysqli;

	// Error Handling
	public bool $error = false;
	public string $errorMsg = '';

	// Recursion Guard
	private static bool $loggingError = false;

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
				exit();
			}else{
				// Set Character Set
				if(!$this->mysqli->set_charset("utf8")){
					exit();
				}
			}
		}else{
			// Environment Not Set
			exit();
		}
	}

	// ----- Query -----
	public function query(string $sql, array $params = []): ?mysqli_result {
		// Prepare Statement
		$stmt = $this->mysqli->prepare($sql);
		if(!$stmt){
			$this->error = true;
			$this->errorMsg = 'Sorry, there was an internal error querying our database. I\'ve logged the error for our support team so they can diagnose and fix the issue.';
			$this->logQueryError('SQL Prepare Error: ' . $this->mysqli->error, $sql);
			return null;
		}

		// Bind Parameters
		if(!empty($params)){
			$types = '';
			foreach($params as $param){
				if(is_int($param)){
					$types .= 'i';
				}elseif(is_float($param)){
					$types .= 'd';
				}elseif(is_string($param)){
					$types .= 's';
				}else{
					$types .= 'b';
				}
			}
			$stmt->bind_param($types, ...$params);
		}

		// Execute
		if(!$stmt->execute()){
			$this->error = true;
			$this->errorMsg = 'Sorry, there was an internal error querying our database. I\'ve logged the error for our support team so they can diagnose and fix the issue.';
			$this->logQueryError('SQL Execution Error: ' . $stmt->error, $sql);
			$stmt->close();
			return null;
		}

		// Get Result
		$result = $stmt->get_result();
		$stmt->close();

		// SELECT returns mysqli_result; INSERT/UPDATE/DELETE return false
		return $result !== false ? $result : null;
	}

	// ----- Get Insert ID -----
	public function getInsertId(): int {
		return $this->mysqli->insert_id;
	}

	// ----- Get Num Rows -----
	public function getNumRows(mysqli_result $result): int {
		return $result->num_rows;
	}

	// ----- Close -----
	public function close(): void {
		$this->mysqli->close();
	}

	// ----- Log Query Error -----
	private function logQueryError(string $errorDetail, string $sql): void {
		// Prevent infinite recursion: Database error → LogError → Database error → ...
		if(self::$loggingError){
			return;
		}
		self::$loggingError = true;

		$errorLog = new LogError();
		$errorLog->errorNumber = 'C5';
		$errorLog->filename = 'Database.class.php';
		$errorLog->errorMsg = 'Query Error';
		$errorLog->badData = $errorDetail . ' | Query: ' . $sql;
		$errorLog->write();

		self::$loggingError = false;
	}
}
?>
