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

			// Data
			$dbErrorID = $db->escape($errorID);
			$dbErrorNumber = $db->escape($this->errorNumber);
			$dbErrorMessage = $db->escape($this->errorMsg);
			$dbBadData = $db->escape(serialize($this->badData));
			$dbURI = $db->escape($_SERVER['REQUEST_URI']);
			$dbIPAddress = $db->escape($_SERVER['REMOTE_ADDR']);
			$dbTimestamp = $db->escape(time());
			$dbFilename = $db->escape($this->filename);

			// Add to Database
			$query = "INSERT INTO error_log (id, errorNumber, errorMessage, badData, URI, ipAddress, timestamp, filename, resolved) VALUES('$dbErrorID', '$dbErrorNumber', '$dbErrorMessage', '$dbBadData', '$dbURI', '$dbIPAddress', '$dbTimestamp', '$dbFilename', b'0')";
			$db->query($query);
		}
	}
	
	public function validate($errorID, $saveToClass){
		// Valid
		$valid = false;
		
		if(!empty($errorID)){
			// Prep for Database
			$db = new Database();
			$dbErrorID = $db->escape($errorID);
			
			// Query
			$db->query("SELECT errorNumber, errorMessage, badData, URI, ipAddress, timestamp, filename, resolved FROM error_log WHERE id='$dbErrorID'");
			if(!$db->error){
				if($db->result->num_rows == 1){
					// Valid errorID
					$valid = true;
					
					// Save to Class?
					if($saveToClass){
						$array = $db->resultArray();
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
		
		// Prep for Database
		$db = new Database();

		// Query
		$db->query("SELECT id FROM error_log WHERE resolved='0' ORDER BY timestamp DESC");
		if(!$db->error){
			if($db->result->num_rows > 0){
				while($array = $db->resultArray()){
					$errorIDs[] = $array['id'];
				}
			}	
		}
			
		// Return
		return $errorIDs;
	}
	
	public function resolved($errorID){
		if(!empty($errorID)){
			// Prep for Database
			$db = new Database();
			$dbErrorID = $db->escape($errorID);
			
			// Query			
			$db->query("UPDATE error_log SET resolved='1' WHERE id='$dbErrorID'");
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