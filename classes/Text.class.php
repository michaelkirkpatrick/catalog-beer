<?php
/*
$text = new Text(markdown, smartypants, removeParagraphs);
$html = $text->get($string);
*/
class Text {
	
	public $text = '';
	public $markdown = true;
	public $smartyPants = true;
	public $removeParagraphs = false;
	
	function __construct($markdown, $smartyPants, $removeParagraphs){
		// Save to Class
		$this->markdown = $markdown;
		$this->smartyPants = $smartyPants;
		$this->removeParagraphs = $removeParagraphs;
	}
	
	function get($text){
		
		// Save to Class
		$this->text = $text;
		
		// Use Markdown?
		if($this->markdown){
			// Convert to HTML using Markdown
			$this->text = Markdown::defaultTransform($this->text);
			
			// Remove Paragraph Tags?
			if($this->removeParagraphs){
				// Remove Paragraph Tags
				$this->text = str_replace(array('<p>', '</p>'), array('', ''), $this->text);
			}
		}
			
		// Smarty Pants
		if($this->smartyPants){
			$this->text = SmartyPants::defaultTransform($this->text);
		}

		// HTML Purifier
		$htmlpConfig = HTMLPurifier_Config::createDefault();
		$htmlpConfig->set('Core.Encoding', 'UTF-8');
		$htmlpConfig->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$purifier = new HTMLPurifier($htmlpConfig);
		$this->text = $purifier->purify($this->text);

		// Return
		return $this->text;
	}
}
?>