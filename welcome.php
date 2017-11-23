<?php
// Initialize
$guest = false;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Catalog.beer: The Internet\'s Beer Database');
echo $htmlHead->html;
?>
<body>
	<?php
	// Navbar
	echo $nav->navbar('Brewers');
	
	// First Name
	$exploded = explode(' ', $userInfo->name);
	if(count($exploded) == 2){
		// Traditional Name
		$firstName = $exploded[0];
		$text = new Text(false, false, true);
		$firstName = $text->get($firstName);
	}else{
		// Untraditional Name, let's log it
		$errorLog = new LogError();
		$errorLog->errorNumber = 'C3';
		$errorLog->errorMsg = 'Untraditional Name';
		$errorLog->badData = $userInfo->name;
		$errorLog->filename = 'welcome.php';
		$errorLog->write();
	}
	
	// Email Text
	if($userInfo->emailVerified){
		$emailHTML = '<p>Your email address has been confirmed so you&#8217;re ready to go! Feel free to start browsing, adding beers, and updating information.</p>';
	}else{
		$emailHTML = '<p>You&#8217;re one step away from full-fledged membership. We&#8217;d like you to confirm your email for us so that we know you&#8217;re not a computer. Check your email. Once you&#8217;ve confirmed your email, you&#8217;ll be ready to add beers and update information across the site.</p>';
	}
	?>
	<div class="container">
    <div class="jumbotron">
			<h1 class="display-3">Welcome, <?php echo $firstName; ?>!</h1>
			<p class="lead">It&#8217;s great to have you as a part of the Catalog.beer community.</p>
			<hr class="my-4">
			<?php echo $emailHTML; ?>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>