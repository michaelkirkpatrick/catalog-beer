<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Blog');
echo $htmlHead->html;
?>
<body>
	<?php
	// Navbar
	echo $nav->navbar('');
	?>
	<div class="container">
    <div class="row">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				
				<h1>Blog</h1>
				
				<h2><a href="2020-01-25.php">Moving Forward</a></h2>
				
				<p class="text-muted"><small>Saturday, January 25, 2020</small></p>
				
				<p>Catalog.beer is just over two years old now, and there's more to look forward to as we get into our third year. A new version of the API has been released, and new features and functionality for Catalog.beer (both the website and the API) are on our roadmap.</p>
				
				<p style="margin-top:1rem;"><a href="2020-01-25.php">Read more...</a></p>
				
				<h2><a href="welcome.php">Welcome to Catalog.beer</a></h2>

				<p class="text-muted"><small>Thursday, November 23, 2017</small></p>

				<p>For digital entrepreneurs in the beer industry like myself, the key to getting started is having access to an authoritative, relatively complete database of beer and brewer data. With Catalog.beer, you will now have access to that trove of information for free.</p>

				<p style="margin-top:1rem;"><a href="welcome.php">Read more...</a></p>
			</div>
			<div class="col-md-2"></div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>