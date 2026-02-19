<?php
/* ---
// HTML Head
$htmlHead = new htmlHead('PageName');
echo $htmlHead->html;
--- */
class htmlHead {
	
	public $html;
	
	function __construct($pageTitle){
		// HTML Header
		$html = file_get_contents(ROOT . '/classes/resources/head.html');
		$text = new Text(false, false, true);
		$pageTitle = $text->get($pageTitle);
		$html = str_replace('##PAGETITLE##', $pageTitle, $html);

		// Fathom Analytics (production only)
		$fathom = '';
		if(defined('ENVIRONMENT') && ENVIRONMENT === 'production'){
			$fathom = "<!-- Fathom Analytics -->\n\t" . '<script src="https://cdn.usefathom.com/script.js" data-site="YRZMNYXM" defer></script>';
		}
		$this->html = str_replace('##FATHOM##', $fathom, $html);
	}
	
	function addDescription($description){
		if(!empty($description)){
			$text = new Text(false, false, true);
			$description = $text->get($description);
			$metaDescription = '<meta charset="UTF-8">' . "\n\t" . '<meta name="description" content="' . $description . '" />';
			$this->html = str_replace('<meta charset="utf-8">', $metaDescription, $this->html);
		}
	}
}
?>