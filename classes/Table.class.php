<?php
class Table {
	
	public $tableStriped = true;
	
	public function startTable($headings){
		// Table Class
		if($this->tableStriped){
			$classAdd = ' table-striped';
		}else{
			$classAdd = '';
		}
		
		// Start Table
		$html = '<table class="table ' . $classAdd . '">' . "\n";
		$html .= '<thead>' . "\n";
		$html .= '<tr>' . "\n";
		
		// Loop through headings
		foreach($headings as &$heading){
			if(preg_match('/^[A-Za-z0-9 ]*$/', $heading)){
				$heading = htmlspecialchars($heading);
			}else{
				$heading = $heading;
			}
			$html .= '<th>' . $heading . '</th>' . "\n";
		}
		$html .= '</tr>' . "\n";
		$html .= '</thead>' . "\n";
		$html .= '<tbody>' . "\n";
		
		// Return
		return $html;
	}
	
	public function closeTable(){
		$html = '</tbody>' . "\n";
		$html .= '</table>' . "\n";
		return $html;
	}
	
}