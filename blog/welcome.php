<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Welcome to Catalog.beer');
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
				<h1>Welcome to Catalog.beer</h1>

				<p class="text-muted"><small>Thursday, November 23, 2017</small></p>

				<p>For digital entrepreneurs in the beer industry like myself, the key to getting started is having access to an authoritative, relatively complete database of beer and brewer data. With Catalog.beer, you will now have access to that trove of information for free.</p>

				<p>For beer consumers, enthusiasts, and researchers, access to information about beer and brewers can help educate and inform. This database is here to help in that mission.</p>

				<p>Today our catalog hosts <a href="/brewer">6,555 brewers</a> and <a href="/beer">70,713 beers</a>. I can&#8217;t wait to see how big it grows. And you can help. Catalog.beer has been designed as a platform that anyone can contribute to. Today, you can <a href="/brewer/add">add new brewers</a>, and add beers and locations to brewers. You can add and retrieve information both via the <a href="/">Catalog.beer</a> website and via our <a href="/api-docs">API</a>.</p>

				<p>If you&#8217;re a developer, you can also contribute on GitHub. The project has been written in PHP, MySQL, and HTML with CSS help from Bootstrap.</p>

				<ul>
				<li><a href="https://github.com/michaelkirkpatrick/catalog-beer">Catalog.beer - GitHub</a></li>
				<li><a href="https://github.com/michaelkirkpatrick/catalog-beer-api">Catalog.beer API - GitHub</a></li>
				<li><a href="https://github.com/michaelkirkpatrick/catalog-beer-mysql">Catalog.beer MySQL - GitHub</a></li>
				</ul>

				<p>There&#8217;s more work to be done. Editing and search capabilities are two upcoming features that are in the near-term pipeline.</p>

				<p>I welcome comments, questions, and most of all contributions.</p>

				<p>Thanks for being a part of the community, and Happy Thanksgiving.</p>

				<p>Sincerely,</p>

				<p>Michael Kirkpatrick<br/>Founder, Catalog.beer<br><a href="/contact">Contact</a> / <a href="https://twitter.com/mekirkpatrick">Twitter</a></p>
			</div>
			<div class="col-md-2"></div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>