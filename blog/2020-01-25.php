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
				<h1>Moving Forward</h1>

				<p class="text-muted"><small>Saturday, January 25, 2020</small></p>

				<h2>Where we&#8217;ve been</h2>

				<p>It&#8217;s been two years since Catalog.beer was born and a lot has changed in that time. I had originally envisioned this database feeding another project of mine, Interchange Design. Under the Interchange Design name, I had developed digital menus for breweries and restaurants where they could display both beer and food menu data to their patrons. Catalog.beer was to be the database that allowed bar and restaurant owners to quickly and easily add beers to their menus without having to type in all the details. For example, if they wanted to add Ballast Point&#8217;s Sculpin to the menu, all they would have to do would be to start typing in &#8220;Sculpin&#8221; and the rest of the form would auto-populate with the beer&#8217;s information. Easy.</p>

				<p>Since Catalog.beer&#8217;s inception, the biggest change &#8211; and the reason for little progress in the interim &#8211; has been that Interchange Design shut down and I moved on to new employment.</p>

				<h2>Where we are today</h2>

				<p>The basic functionality of Catalog.beer remains. Anyone in the world can:</p>

				<ul>
				<li><a href="/brewer/add">Create a brewery</a></li>
				<li>Add a beer to a brewery</li>
				<li>Add a location to a brewery &#8211; For example, a tasting room</li>
				</ul>

				<p>Here on the Catalog.beer website you can browse lists of <a href="/brewer">breweries</a> and <a href="/beer">beers</a>, and see brewery locations on their brewery page, like this page showcasing <a href="/brewer/878c9570-7742-f023-9cde-639d2f634f9c">Ballast Point Brewing Company</a>.</p>

				<p>And if you&#8217;re a software developer, you can leverage our <a href="/api-docs">API</a> for even more functionality.</p>

				<h2>What&#8217;s new?</h2>

				<p>Over the past month, I&#8217;ve been making a lot of updates to our <a href="api-docs">API</a>, mostly behind the scenes. While there aren&#8217;t any new API features to announce, the API has had quite the tune-up under the hood.</p>

				<ul>
				<li>Major refactoring across the <a href="https://github.com/michaelkirkpatrick/catalog-beer-api">API codebase</a></li>
				<li>Improved API Responses:

				<ul>
				<li>Better HTTP response codes to more accurately reflect successes and failures of API requests</li>
				<li>Improved error messages when you make an API request that fails</li>
				<li>Consistent output across the API with data types that match the kind of data being returned. For instance, if the API response includes a number (e.g. IBU value or latitude and longitude), you will always see those values reflected as numbers as opposed to strings.</li>
				</ul></li>
				<li>Bug fixes &#8211; When you unearth everything, there&#8217;s bound to be things that you find are buggy. Those bugs have been patched!</li>
				</ul>

				<p>I&#8217;ve also written hundreds of automated test cases in Postman against our API to ensure that as I continue to make updates, there aren&#8217;t any regressions and new development can quickly be tested.</p>

				<h2>What&#8217;s next?</h2>

				<p>For this database to truly be useful, a few improvements and new features need to be added.</p>

				<ul>
				<li>Breweries need to be able to validate their basic brewery information so they can get their brewerValidated badge.</li>
				<li>We, collectively, need to be able to edit brewery, beer, and location information.</li>
				<li>We should also be able to delete breweries, beers, and locations where appropriate.</li>
				</ul>

				<p>This is going to take time &#8211; it&#8217;s a side project for me. But I strongly believe all of this can be accomplished by summertime. I will be using this blog to announce when new features are available. You can also follow along on <a href="https://twitter.com/CatalogBeer">Twitter (@CatalogBeer)</a> where I&#8217;ll be posting mini-updates along the way. If you&#8217;d prefer to <a href="/contact">drop me an email</a>, please do. I read and reply to every email I get.</p>

				<p>I think having an open-source beer database is super cool. If you do too, get in touch. I&#8217;d love to hear from you.</p>
				
				<p style="margin-top: 3rem;"><em>-Michael</em></p>
				
				<p class="text-muted"><em>Michael Kirkpatrick<br/>Founder, Catalog.beer</em></p>
			</div>
			<div class="col-md-2"></div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>