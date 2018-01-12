<?php
// Page Not Found
http_response_code(404);

// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Page Not Found on Catalog.beer');
echo $htmlHead->html;
?>
<body>
	<?php echo $nav->navbar(''); ?>
	<div class="container">
    <div class="jumbotron">
			<h1 class="display-4">We couldn&#8217;t find what you&#8217;re looking for&#8230;</h1>
			<p>We couldn&#8217;t find what you&#8217;re looking for&#8230;</p>
			<p class="lead">Sorry about that. We&#8217;ve logged the error so we can see if what you&#8217;re looking for got stuck somewhere.</p>
			<p><a class="btn btn-primary btn-lg" href="/" role="button">Back to the homepage</a></p>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>