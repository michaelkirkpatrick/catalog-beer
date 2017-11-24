<?php
/* ---
String Length: 36

// Generate UUID
$uuid = new uuid();
$var = $uuid->generate('db_table_name');
if(!$uuid->error){
	// Save to Class
	$this->ID = $var;
}else{
	// UUID Generation Error
	$this->error = true;
	$this->errorMsg = $uuid->errorMsg;
}
--- */

class uuid {
	
	public $uuid = '';
	
	public $error = false;
	public $errorMsg = '';
	
	
	// ----- Generate Unique UUID -----
	public function generate($table){
		// Default State
		$continue = true;
		
		// While Loop
		while($continue){
			// Create Code
			$this->createCode();
			
			// Check Unique
			$unique = $this->checkUnique($table);
			if($unique || $this->error){
				$continue = false;
			}
		}
		
		// Return UUID
		return $this->uuid;
	}
	
	// ----- Generate Code -----
	public function createCode(){
		// 16 bytes = 128 bits
		// Generate random string
		$bytes = random_bytes(16);

		// Convert to hexadecimal
		$hex = bin2hex($bytes);

		// Add in dashes for UUID 8-4-4-4-12
		$this->uuid = substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' .  substr($hex, 20, 12);
		
		return $this->uuid;
	}
	
	// ----- Check Unique -----
	private function checkUnique($table){
		// Default Return
		$unique = false;
		
		// Connect to database
		$db = new Database();
		$dbTable = $db->escape($table);
		$dbUUID = $db->escape($this->uuid);
		
		// Query
		$db->query("SELECT id FROM $table WHERE id='$dbUUID'");
		if(!$db->error){
			if($db->result->num_rows == 0){
				$unique = true;
			}
		}else{
			$this->error = true;
			$this->errorMsg = $db->errorMsg;
		}
		
		// Return
		return $unique;
	}
	
	// ----- Generate Alphanumeric String -----
	public function createAlpha($characters, $allCaps){
	
		// Setup Array
		$array = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

		if(!$allCaps){
			array_push($array, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		}

		$badWords = array_map('str_getcsv', file(ROOT . '/classes/resources/badwords.csv'));
		$continue = true;
		$badWordFlag = false;

		while($continue){

			// Generate Code
			$this->uuid = '';
			$arraySize = count($array);
			for($i=1;$i<=$characters;$i++){
				$rand = random_int(0,$arraySize-1);
				$this->uuid .= $array[$rand];
			}

			// Ensure no bad words
			foreach($badWords[0] as &$badWord){
				if(!empty($badWord)){
					if(strpos($this->uuid, $badWord) !== false) {
						$badWordFlag = true;
					}	
				}
			}

			// Check $badWordFlag
			if(!$badWordFlag){
				// Stop
				$continue = false;
			}
		}

		return $this->uuid;
	}
}