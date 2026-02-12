<?php
/*
$alert->type = 'success/info/warning/error';
$alert->dismissible = true;

$alert = new Alert();
$alert->msg = '';
echo $alert->display();
*/

class Alert {
	
	// Public
	public $type = 'error';
	public $dismissible = false;
	public $msg = '';
	
	public function display(){

		if(!empty($this->msg)){
			// Save as 'danger' per Bootstrap
			if($this->type == 'error'){$class = 'danger';}
			else{$class = $this->type;}

			// ----- Message -----
			$text = new Text(true, true, true);
			
			// ----- HTML Output -----
			$return = '<div class="alert alert-' . $class;
			if($this->dismissible){
				$return .= ' alert-dismissible fade show';
			}
			$return .= '" role="alert" style="margin-bottom:1em;">';
			$return .= $text->get($this->msg);
			if($this->dismissible){
				$return .= "\n" . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';	
			}
			$return .= '</div>';
		}else{
			// No Message
			$return = '';	 
		}
		
		// Return
		return $return;
	}
}
?>