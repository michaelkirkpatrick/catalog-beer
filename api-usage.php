<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('API Usage');
echo $htmlHead->html;
?>
<style>
	h2 {
		margin-top: 4rem;
	}
	h3 {
		margin-top: 3rem;
	}
	h4 {
		margin-top: 2rem;
		color:#4b555e;
	}
</style>
<body>
	<?php
	// Navbar
	echo $nav->navbar('');
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				<h1>API Usage</h1>

				<p class="lead">The Catalog.beer Application Programming Interface (API) has been designed to make it easy for you to access information about brewers, beers, and tasting rooms.</p>

				<p>When it comes to using the API, anyone is welcome to use it; all you have to do is <a href="/signup">create an account</a> and verify your email address. If you have questions about how to use the API, refer to our <a href="/api-docs">API Documentation</a> or <a href="/contact">drop us a line</a>.</p>

				<h2>Basic Rules</h2>
				<hr>

				<h3>Creative Commons License</h3>

				<p>All the content that is accessible via the API is licensed under a <a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International license (CC BY 4.0)</a>. This excludes a Brewery’s rights in its name, brand(s), and trademarks. What does that mean?</p>

				<h4>You are free to</h4>

				<ul>
				<li><strong>Share</strong> — copy and redistribute the material in any medium or format</li>
				<li><strong>Adapt</strong> — remix, transform, and build upon the material
				for any purpose, even commercially.</li>
				</ul>

				<h4>What&#8217;s required of you?</h4>

				<p>You must give appropriate credit to Catalog.beer, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.</p>

				<p>For example, if you reproduce the description for <a href="/brewer/375289f8-6cc5-2d38-3ce7-73ab50b8dff4">Long Beach Beer Lab</a>, a brewer, you should include an attribution like this:</p>

				<p>&#8220;<a href="/brewer/375289f8-6cc5-2d38-3ce7-73ab50b8dff4">Long Beach Beer Lab</a>&#8221; by <a href="https://catalog.beer">Catalog.beer</a> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a></p>

				<h3>Use the API responsibly</h3>

				<p>A few guidelines and notes:</p>

				<ul>
				<li>We may delete old accounts (more than a year old) without notice if we see that you haven&#8217;t used Catalog.beer in more than a year.</li>
				<li>If you have questions, <a href="/contact">ask them</a>. We respond quickly to messages sent via our website.</li>
				</ul>

				<h2>Pricing</h2>
				<hr>

				<p class="lead"><strong>tl;dr - It&#8217;s free.</strong></p>
				
				<p class="lead">If you make more than 1,000 API requests per month, at some point in the future we&#8217;ll charge you $0.50 per additional 1,000 requests. Right now we&#8217;re only monitoring usage.</p>

				<p>Catalog.beer was created to enable anyone to access brewery and beer information. To that end, our mission is to give anyone who wants it API access so they can poke around and contribute to the database. If you&#8217;re someone who enjoys beer and wants to build themselves a little application using the Catalog.beer API, we want it to be free for you to use. To that end, everyone with a Catalog.beer API key has 1,000 requests per month that are free.</p>

				<p>If you&#8217;re someone who&#8217;s making more than 1,000 API request per month, odds are you&#8217;re leveraging our database as part of another application that people are using. That&#8217;s great! We want you to do that! At some point, we will start charging you for the number of API requests you make over 1,000 each month. Right now we&#8217;re only monitoring usage and not charging for it.</p>

				<p>How much will it cost? $0.50 per additional 1,000 requests. We think that&#8217;s the cheapest rate out there for access to beer data. Our goal is cover our operating costs. If you have questions about pricing or just want to talk about it, <a href="/contact">send us a message</a>; we&#8217;d love to hear from you.</p>
			</div>
			<div class="col-md-2"></div>
		</div>
	</div>
	<?php echo $nav->footer(); ?> 
</body>
</html>