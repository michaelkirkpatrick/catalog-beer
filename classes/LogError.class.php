<?php
/* ---
Catalog.beer

// Log Error
$errorLog = new LogError();
$errorLog->errorNumber = 0;
$errorLog->errorMsg = '';
$errorLog->badData = '';
$errorLog->filename = '';
$errorLog->write();
--- */

class LogError {

	// Public Variables
	public $errorID = '';
	public $errorNumber = '';
	public $errorMsg = '';
	public $badData = '';
	public $URI = '';
	public $ipAddress = '';
	public $timestamp = 0;
	public $filename = '';
	public $resolved = false;

	// Write Error
	public function write(){

		// Generate UUID
		$uuid = new uuid();
		$errorID = $uuid->generate('error_log');
		if(!$uuid->error){
			// Connect to Database
			$db = new Database();

			// Add to Database
			$db->query("INSERT INTO error_log (id, errorNumber, errorMessage, badData, URI, ipAddress, timestamp, filename, resolved) VALUES(?, ?, ?, ?, ?, ?, ?, ?, 0)", [
				$errorID,
				$this->errorNumber,
				$this->errorMsg,
				serialize($this->badData),
				$_SERVER['REQUEST_URI'],
				$_SERVER['REMOTE_ADDR'],
				time(),
				$this->filename
			]);
		}
	}

	public function validate($errorID, $saveToClass){
		// Valid
		$valid = false;

		if(!empty($errorID)){
			// Query
			$db = new Database();
			$result = $db->query("SELECT errorNumber, errorMessage, badData, URI, ipAddress, timestamp, filename, resolved FROM error_log WHERE id = ?", [$errorID]);
			if(!$db->error){
				if($result->num_rows == 1){
					// Valid errorID
					$valid = true;

					// Save to Class?
					if($saveToClass){
						$array = $result->fetch_assoc();
						$this->errorID = $errorID;
						$this->errorNumber = $array['errorNumber'];
						$this->errorMsg = $array['errorMessage'];
						$this->badData = $array['badData'];
						$this->URI = $array['URI'];
						$this->ipAddress = $array['ipAddress'];
						$this->timestamp = $array['timestamp'];
						$this->filename = $array['filename'];
						$this->resolved = $array['resolved'];
					}
				}
			}
		}

		// Return
		return $valid;
	}

	public function getErrorIDs(){
		// Error IDs
		$errorIDs = array();

		// Query
		$db = new Database();
		$result = $db->query("SELECT id FROM error_log WHERE resolved = 0 ORDER BY timestamp DESC");
		if(!$db->error){
			if($result->num_rows > 0){
				while($array = $result->fetch_assoc()){
					$errorIDs[] = $array['id'];
				}
			}
		}

		// Return
		return $errorIDs;
	}

	public function resolved($errorID){
		if(!empty($errorID)){
			// Query
			$db = new Database();
			$db->query("UPDATE error_log SET resolved = 1 WHERE id = ?", [$errorID]);
		}else{
			$this->errorNumber = 'C4';
			$this->errorMsg = 'Missing errorID';
			$this->badData = '';
			$this->filename = 'LogError.class.php';
			$this->write();
		}
	}
}
?>
