<?php
class PostmarkSendEmail {
	
	public $from = 'Catalog.beer info@catalog.beer';
	public $to = '';
	public $subject = '';
	public $tag = '';
	public $HtmlBody = '';
	public $TextBody = '';
	public $TrackOpens = false;
	public $TrackLinks = 'None';
	public $ReplyTo = '';
	
	function generateBody($to, $subject, $tag, $htmlBody, $textBody, $replyTo){
		$this->to = $to;
		$this->subject = $subject;
		$this->tag = $tag;
		$this->HtmlBody = $htmlBody;
		$this->TextBody = $textBody;
		$this->ReplyTo = $replyTo;
	}
}	
?>