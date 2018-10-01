<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('API Documentation: Catalog.beer');
echo $htmlHead->html;
?>
<style>
	.api-code {
		background-color: #f2f2f2;
		color: #202020;
		border-radius: 0.5rem;
		padding: 1rem;
	}
	h2, h3 {
		margin-top:5rem;
	}
</style>
<body>
	<?php
	// Navbar
	echo $nav->navbar('');
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="list-group">
					<a class="list-group-item list-group-item-action" href="#url"><strong>API Basics</strong></a>
					<a class="list-group-item list-group-item-action" href="#authentication"><strong>Authentication</strong></a>
					<a class="list-group-item list-group-item-action" href="#errors"><strong>Errors</strong></a>
					<a class="list-group-item list-group-item-action" href="#brewer"><strong>Brewer</strong></a>
					<a class="list-group-item list-group-item-action" href="#brewer-object">&gt; Brewer Object</a>
					<a class="list-group-item list-group-item-action" href="#brewer-create">&gt; Add a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-retrieve">&gt; Retrieve a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-list-all">&gt; List all Brewers</a>
					<a class="list-group-item list-group-item-action" href="#brewer-count">&gt; Number of Brewers</a>
					<a class="list-group-item list-group-item-action" href="#brewer-beers">&gt; List all Beers made by a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-locations">&gt; List all the Locations for a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#beer"><strong>Beer</strong></a>
					<a class="list-group-item list-group-item-action" href="#beer-object">&gt; Beer Object</a>
					<a class="list-group-item list-group-item-action" href="#beer-create">&gt; Add a Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-retrieve">&gt; Retrieve a Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-list-all">&gt; List all Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-count">&gt; Number of Beers</a>
					<a class="list-group-item list-group-item-action" href="#location"><strong>Location</strong></a>
					<a class="list-group-item list-group-item-action" href="#location-object">&gt; The Location Object</a>
					<a class="list-group-item list-group-item-action" href="#location-add">&gt; Add a Location</a>
					<a class="list-group-item list-group-item-action" href="#location-add-address">&gt; Add an Address to a Location</a>
					<a class="list-group-item list-group-item-action" href="#location-retrieve">&gt; Retrieve a Location</a>
					<a class="list-group-item list-group-item-action" href="#us-address"><strong>US Addresses</strong></a>
					<a class="list-group-item list-group-item-action" href="#us-address-object">&gt; The US Address Object</a>
				</div>
			</div>
			<div class="col-md-8">
				<h1>API Reference</h1>
				<p>Last Updated: November 22, 2017</p>
				
				<h2 id="url">API Basics</h2>
				<hr>
				
				<p>The Catalog.beer API is organized around REST. We use HTTP response codes to indicate the success or failure of your request. We use also use basic HTTP features like HTTP authentication and HTTP verbs.</p>

				<p>The Catalog.beer API can be accessed using the following root URL:</p>

				<pre class="api-code">https://api.catalog.beer</pre>

				<p>When making an API request, be sure to include an <code>accept: application/json</code> header. All data returned by the API will be in JSON format.</p>

				<p>Similarly, when making a PUT or POST request to the API, the body of your request must be in JSON as well. Be sure to include the <code>content-type: application/json</code> header in your request.</p>
				
				<h2 id="authentication">Authentication</h2>
				<hr>

				<p>Authenticate your account when using the API by including your secret API key in the request. You can find your API key on your <a href="/account">Account</a> page. Your API key carries many privileges, so be sure to keep it secret! Do not share your secret API key in publicly accessible areas such GitHub, client-side code, and so forth.</p>

				<p>Authentication to the API is performed via <a href="https://en.wikipedia.org/wiki/Basic_access_authentication">HTTP Basic Auth</a>. Provide your API key as the basic auth username value. You do not need to provide a password.</p>

				<p>All API requests must be made over HTTPS. Calls made over plain HTTP will fail. API requests without authentication will also fail.</p>
			
				<h2 id="errors">Errors</h2>
				<hr>
				
				<p>Catalog.beer uses conventional HTTP response codes to indicate the success or failure of an API request. In general, codes in the <var>2xx</var> range indicate success, codes in the <var>4xx</var> range indicate an error that failed given the information provided (e.g., a required parameter was omitted), and codes in the <var>5xx</var> range indicate an error with Catalog.beer&#8217;s servers.</p>

				<p>The following parameters are returned in JSON format when an error occurs</p>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>error</var></td>
							<td>Boolean</td>
							<td><var>true</var> or <var>false</var>. Indicates an error occurred in processing your request.</td>
						</tr>
						<tr>
							<td><var>error_msg</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A message relaying additional information about the error.</td>
						</tr>
						<tr>
							<td><var>valid_state</var><br><small class="text-muted">(optional)</small></td>
							<td>array</td>
							<td>An array containing the attribute names and their validation state. A field&#8217;s state is binary: either &#8216;valid&#8217; or &#8216;invalid&#8217;. You can use this parameter to help target which attributes are invalid.</td>
						</tr>
						<tr>
							<td><var>valid_msg</var><br><small class="text-muted">(optional)</small></td>
							<td>array</td>
							<td>An array containing the attribute names and the corresponding error message for that attribute. You can use this information to show help text next to the attributes that were invalid.</td>
						</tr>
					</tbody>
				</table>
				<p>Sample error JSON returned for a POST request to /brewer:</p>
<pre class="api-code">
{
  "error": true,
  "error_msg": "",
  "valid_state": {
    "name": "invalid",
    "url": "valid",
    "description": "valid",
    "short_description": "valid",
    "facebook_url": "valid",
    "twitter_url": "",
    "instagram_url": "valid"
  },
  "valid_msg": {
    "name": "Please give us the name of the brewery you'd like to add.",
    "url": "",
    "description": "",
    "short_description": "",
    "facebook_url": "",
    "twitter_url": "",
    "instagram_url": ""
  }
}
</pre>
				
				<h2 id="brewer">Brewer</h2>
				<hr>

				<p>The brewer object is the central piece of the beer data puzzle. Brewers can have beers and locations associated with them. And in order to add a beer or location to the database, there must be a brewer to associated them with.</p>

				<h3 id="brewer-object">The Brewer Object</h3>

				<p>Whether you&#8217;re retrieving information on a specific brewer, adding a new brewer, or updating an existing brewer, successful requests will return the brewer object in JSON format. That object has the following parameters.</p>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>id</var></td>
							<td>string</td>
							<td>The brewer_id; a unique identifier for the brewer.</td>
						</tr>
						<tr>
							<td><var>object</var></td>
							<td>string</td>
							<td>The name of the object. In this case: &#8220;brewer&#8221;.</td>
						</tr>
						<tr>
							<td><var>name</var></td>
							<td>string</td>
							<td>The name of the brewer.</td>
						</tr>
						<tr>
							<td><var>description</var></td>
							<td>string</td>
							<td>A description of the brewer. Note that this field may contain <a href="https://daringfireball.net/projects/markdown/syntax">markdown</a> or new line characters.</td>
						</tr>
						<tr>
							<td><var>short_description</var></td>
							<td>string</td>
							<td>A short, max 160 character, description of the brewer.</td>
						</tr>
						<tr>
							<td><var>url</var></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s website.</td>
						</tr>
						<tr>
							<td><var>cb_verified</var></td>
							<td>Boolean</td>
							<td>A <var>true</var> or <var>false</var> value denoting whether or not a Catalog.beer administrator has verified the brewer&#8217;s information.</td>
						</tr>
						<tr>
							<td><var>brewer_verified</var></td>
							<td>Boolean</td>
							<td>A <var>true</var> or <var>false</var> value denoting whether or not the brewer themselves has contributed and verified their information.</td>
						</tr>
						<tr>
							<td><var>facebook_url</var></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s Facebook page.</td>
						</tr>
						<tr>
							<td><var>twitter_url</var></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s Twitter profile.</td>
						</tr>
						<tr>
							<td><var>instagram_url</var></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s Instagram profile.</td>
						</tr>
					</tbody>
				</table>
				
				<h4>Sample</h4>

<pre class="api-code">
{
  "id": "65911cdd-52e6-6305-3420-b9bbf6ea958d",
  "object": "brewer",
  "name": "HopSaint",
  "description": "HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn’t be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That’s at the heart of HopSaint - a community that fosters lasting relationships & enriches our hometown through the production of honest, real beer. A community built on craft beer.",
  "short_description": "A brewery in Torrance, CA.",
  "url": "https://www.hopsaint.com/",
  "cb_verified": true,
  "brewer_verified": false,
  "facebook_url": "https://www.facebook.com/hopsaintbrewingco",
  "twitter_url": "",
  "instagram_url": "https://www.instagram.com/hopsaintbrewco/"
}
</pre>

				<h3 id="brewer-create">Add a Brewer</h3>

				<p>To add a brewer, send a <strong>POST</strong> request to the <code>/brewer</code> endpoint with the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#brewer-object">brewer object</a>.</p>

				<pre class="api-code">POST https://api.catalog.beer/brewer</pre>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>name</var></td>
							<td>string</td>
							<td>The name of the brewer.</td>
						</tr>
						<tr>
							<td><var>description</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A description of the brewer. This should be like the &#8220;About&#8221; page posted by the brewer. A brief origin story coupled with who they are. This field supports <a href="https://daringfireball.net/projects/markdown/syntax">markdown</a>.</td>
						</tr>
						<tr>
							<td><var>short_description</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A short description of the brewer; max 160 characters. TDescribe the brewer as you might in a tweet. Short and sweet.</td>
						</tr>
						<tr>
							<td><var>url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s website.</td>
						</tr>
						<tr>
							<td><var>facebook_url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s Facebook page.</td>
						</tr>
						<tr>
							<td><var>twitter_url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s Twitter profile.</td>
						</tr>
						<tr>
							<td><var>instagram_url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s Instagram profile.</td>
						</tr>
					</tbody>
				</table>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/brewer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"","description":"HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn\u2019t be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That\u2019s at the heart of HopSaint - a community that fosters lasting relationships & enriches our hometown through the production of honest, real beer. A community built on craft beer.","short_description":"A brewery in Torrance, CA.","url":"https:\/\/www.hopsaint.com\/","facebook_url":"http:\/\/www.facebook.com\/hopsaintbrewingco","twitter_url":"","instagram_url":"http:\/\/instagram.com\/hopsaintbrewco"}'
</pre>

				<h3 id="brewer-retrieve">Retrieve a Brewer</h3>

				<p>To retrieve a brewer, send a <strong>GET</strong> request to the <code>/brewer</code> endpoint with the <var>{brewer_id}</var> parameter appended to the path.</p>

				<pre class="api-code">GET https://api.catalog.beer/brewer/{brewer_id}</pre>

				<p>A <a href="#brewer-object">brewer object</a> will be returned for successful requests.</p>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/65911cdd-52e6-6305-3420-b9bbf6ea958d \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

				<h3 id="brewer-list-all">List all Brewers</h3>

				<p>If you want a list of all the brewers in the database, send a <strong>GET</strong> request to the <code>/brewer</code> endpoint.</p>

				<pre class="api-code">GET https://api.catalog.beer/brewer</pre>
				
				<h4>Arguments</h4>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Argument</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
						</tr>
						<tr>
							<td><var>count</var><br><small class="text-muted">(optional)</small></td>
							<td>integer</td>
							<td>The number of results you would like returned from your request. The default value is 500.</td>
						</tr>
					</tbody>
				</table>
				<p>A sample request with arguments. Be sure to encode all non-alphanumeric characters except -_.</p>
				<pre class="api-code">GET https://api.catalog.beer/brewer?count=5&amp;cursor=NQ%3D%3D</pre>
				
				<h4>Returns</h4>
				<p>This request returns a list object with the following parameters.</p>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>object</var></td>
							<td>string</td>
							<td>The name of the object. In this case: &#8220;list&#8221;.</td>
						</tr>
						<tr>
							<td><var>url</var></td>
							<td>string</td>
							<td>The API endpoint accessed to retrieve this object. In this case: <code>/brewer</code>.</td>
						</tr>
						<tr>
							<td><var>has_more</var></td>
							<td>Boolean</td>
							<td>Whether or not there is more data available after this set. If <var>false</var>, you have reached the last items on the list.</td>
						</tr>
						<tr>
							<td><var>next_cursor</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>To retrieve the next set of results, provide this value as the <var>cursor</var> parameter on your subsequent API request.</td>
						</tr>
						<tr>
							<td><var>data</var></td>
							<td>array</td>
							<td>An array containing all the brewers in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var> and <var>name</var>, described below.</td>
						</tr>
						<tr>
							<td><var>id</var></td>
							<td>string</td>
							<td>The <var>brewer_id</var>.</td>
						</tr>
						<tr>
							<td><var>name</var></td>
							<td>string</td>
							<td>The name of the brewer.</td>
						</tr>
					</tbody>
				</table>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

				<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/brewer",
  "has_more": false,
  "data": [
    {
      "id": "e7fa4e64-a39e-fd06-f82a-37de2a7dfbda",
      "name": "Ballast Point"
    },
    {
      "id": "65911cdd-52e6-6305-3420-b9bbf6ea958d",
      "name": "HopSaint"
    }
  ]
}
</pre>

				<h3 id="brewer-count">Number of Brewers</h3>

				<p>You can retrieve the total number of brewers that are in the database by sending a <strong>GET</strong> request the endpoint <code>/brewer/count</code>. A JSON object with the following parameters will be returned.</p>

				<pre class="api-code">GET https://api.catalog.beer/brewer/count</pre>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>object</var></td>
							<td>string</td>
							<td>The name of the object. In this case: &#8220;count&#8221;.</td>
						</tr>
						<tr>
							<td><var>url</var></td>
							<td>string</td>
							<td>The API endpoint accessed to retrieve this object. In this case: <code>/brewer/count</code>.</td>
						</tr>
						<tr>
							<td><var>value</var></td>
							<td>integer</td>
							<td>The number of brewers in the database.</td>
						</tr>
					</tbody>
				</table>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/count \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

				<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "count",
  "url": "/brewer/count",
  "value": 3
}
</pre>

				<h3 id="brewer-beers">List all Beers made by a Brewer</h3>

				<p>If you would like a list of all the beers made by a brewer, send a request to the <code>/brewer/{brewer_id}/beer</code> endpoint. </p>

				<p>This request returns a list object with the following parameters.</p>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>object</var></td>
							<td>string</td>
							<td>The name of the object. In this case: &#8220;list&#8221;.</td>
						</tr>
						<tr>
							<td><var>url</var></td>
							<td>string</td>
							<td>The API endpoint accessed to retrieve this object. In this case: <code>/brewer/{brewer_id}/beer</code>.</td>
						</tr>
						<tr>
							<td><var>has_more</var></td>
							<td>Boolean</td>
							<td>As of this writing, this will always return <var>false</var>. In the future, if pagination of results is required, this value may become <var>true</var>.</td>
						</tr>
						<tr>
							<td><var>brewer</var></td>
							<td>object</td>
							<td>A <a href="#brewer-object">brewer object</a> containing information for the requested <var>{brewer_id}</var>.</td>
						</tr>
						<tr>
							<td><var>data</var></td>
							<td>array</td>
							<td>An array containing all the beers associated with this brewer in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, and <var>style</var> described below.</td>
						</tr>
						<tr>
							<td><var>id</var></td>
							<td>string</td>
							<td>The <var>beer_id</var>.</td>
						</tr>
						<tr>
							<td><var>name</var></td>
							<td>string</td>
							<td>The name of the beer.</td>
						</tr>
						<tr>
							<td><var>style</var></td>
							<td>string</td>
							<td>The style of the beer.</td>
						</tr>
					</tbody>
				</table>
				
				<h4>Sample Request</h4>
<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/e7fa4e64-a39e-fd06-f82a-37de2a7dfbda/beer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

				<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/brewer/e7fa4e64-a39e-fd06-f82a-37de2a7dfbda/beer",
  "has_more": false,
  "brewer": {
    "id": "e7fa4e64-a39e-fd06-f82a-37de2a7dfbda",
    "object": "brewer",
    "name": "Ballast Point",
    "description": "## It Begins with the Search for Flavor\r\n\r\nThe perfect balance of taste and aroma. An obsession with ingredients. An exploration of techniques. And while we savor the result, we’re just as fascinated by the process to get there. What started as a small group of home brewers, who simply wanted to make a better beer, evolved into the adventurers known today as Ballast Point.\r\n\r\n## Where Science Meets Art\r\n\r\nWe live to add our own touch and ask if there’s a better way. As we tinkered, tested and tasted, we discovered that making beer was more art than science. And while we respect and honor tradition, we relish the opportunity to take it further. That freedom has allowed us to reinterpret brewing. And along the way help to reinvigorate the industry. From bringing a hoppy twist to a porter, or developing a proprietary yeast for our amber ale, to creating a breakthrough gold medal-winning IPA.\r\n\r\n## To Share Our Journey\r\nBut all of this would be wasted if we couldn’t share it. Whether serving up new flavors, collaborating with seasoned brewmasters, or training the next generation at Home Brew Mart, we not only want to challenge our own tastes, but expand yours.\r\nBallast Point. Dedicated to the craft.",
    "short_description": "Award wining brewery built in San Diego, California. Dedicated to the craft.",
    "url": "https://www.ballastpoint.com/",
    "cb_verified": true,
    "brewer_verified": false,
    "facebook_url": "https://www.facebook.com/BallastPoint",
    "twitter_url": "https://twitter.com/BallastPoint",
    "instagram_url": "https://www.instagram.com/ballastpointbrewing/"
  },
  "data": [
    {
      "id": "01fd323a-5984-a1c5-51a1-9b8abde3afb4",
      "name": "12 Year Old Elijah Craig Ballast Point Victory at Sea",
      "style": "Imperial Porter"
    },
    {
      "id": "e9a9936e-332c-aa6c-8dcf-70309b483db7",
      "name": "Abandon Ship",
      "style": "Smoked Lager"
    },
    {
      "id": "b4d1c9f3-cb4a-c364-f1f0-a71a94295ead",
      "name": "Abandon Ship with Chipotle",
      "style": "Smoked Lager"
    }
  ]
}
</pre>

				<h3 id="brewer-locations">List all the Locations for a Brewer</h3>

				<p>If you would like to know all the locations associated with a brewer, send a request to the <code>/brewer/{brewer_id}/locations</code> endpoint.</p>

				<p>This request returns a list object with the following parameters.</p>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>object</var></td>
							<td>string</td>
							<td>The name of the object. In this case: &#8220;list&#8221;.</td>
						</tr>
						<tr>
							<td><var>url</var></td>
							<td>string</td>
							<td>The API endpoint accessed to retrieve this object. In this case: <code>/brewer/{brewer_id}/locations</code>.</td>
						</tr>
						<tr>
							<td><var>has_more</var></td>
							<td>Boolean</td>
							<td>As of this writing, this will always return <var>false</var>. In the future, if pagination of results is required, this value may become <var>true</var>.</td>
						</tr>
						<tr>
							<td><var>data</var></td>
							<td>array</td>
							<td>An array containing all the locations associated with this brewer in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var> and <var>name</var> described below.</td>
						</tr>
						<tr>
							<td><var>id</var></td>
							<td>string</td>
							<td>The <var>location_id</var>.</td>
						</tr>
						<tr>
							<td><var>name</var></td>
							<td>string</td>
							<td>The name of the location.</td>
						</tr>
					</tbody>
				</table>
				
				<h4>Sample Request</h4>
<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/e7fa4e64-a39e-fd06-f82a-37de2a7dfbda/locations \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>
				
				<h4>Sample Response</h4>
<pre class="api-code">
{
  "object": "list",
  "url": "/brewer/e7fa4e64-a39e-fd06-f82a-37de2a7dfbda/locations",
  "has_more": false,
  "data": [
    {
      "id": "d94cdece-0b9f-cab6-9d6e-5fb3ba41d37c",
      "name": "Daleville, Virginia"
    },
    {
      "id": "618df467-d2a3-5fdd-9d9e-f2b338d3485e",
      "name": "Home Brew Mart"
    }
  ]
}
</pre>
				
				<h2 id="beer">Beer</h2>
				<hr>

				<p>Beer is at the heart of it all. From an API perspective, beers are associated with brewers via a <var>brewer_id</var>.</p>

				<h3 id="beer-object">The Beer Object</h3>

				<p>When you add a new beer, are looking for information on a specific beer, or are updating a beer in the database, successful requests will return the beer object in JSON format. That object has the following parameters.</p>
				
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>id</var></td>
							<td>string</td>
							<td>The beer_id; a unique identifier for the beer.</td>
						</tr>
						<tr>
							<td><var>object<var></td>
							<td>string</td>
							<td>The name of the object; in this case: &#8220;beer&#8221;.</td>
						</tr>
						<tr>
							<td><var>name<var></td>
							<td>string</td>
							<td>The name of the beer.</td>
						</tr>
						<tr>
							<td><var>style<var></td>
							<td>string</td>
							<td>The style of the beer.</td>
						</tr>
						<tr>
							<td><var>description<var></td>
							<td>string</td>
							<td>A description of the beer. This field may contain a basic description, may contain tasting notes and/or brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax">markdown</a> or new line characters.</td>
						</tr>
						<tr>
							<td><var>abv<var></td>
							<td>float</td>
							<td>The Alcohol by Volume (ABV) percentage of the beer.</td>
						</tr>
						<tr>
							<td><var>ibu<var></td>
							<td>integer</td>
							<td>The International Bitterness/Bittering Units (IBU) value of the beer.</td>
						</tr>
						<tr>
							<td><var>cb_verified<var></td>
							<td>Boolean</td>
							<td>A <var>true</var> or <var>false</var> value denoting whether or not a Catalog.beer administrator has verified the brewer’s information.</td>
						</tr>
						<tr>
							<td><var>brewer_verified<var></td>
							<td>Boolean</td>
							<td>A <var>true</var> or <var>false</var> value denoting whether or not the brewer themselves has contributed and verified their information.</td>
						</tr>
						<tr>
							<td><var>brewer<var></td>
							<td>object</td>
							<td>A <a href="#brewer-object">brewer object</a> containing information on the brewer.</td>
						</tr>
					</tbody>
				</table>

<h4>Sample</h4>

<pre class="api-code">
{
    "id": "bc2170df-eef7-8f6b-205b-63cbfeb4a901",
    "object": "beer",
    "name": "Schooner Wet Hop",
    "style": "Ale with Wet Hops",
    "description": "Wet Hops (fresh hops) are hops that are picked off the vine and used in the brewing process before they are dried and packaged like normal. To brew this beer we get Cascade Hops from Yakima, Washington. They are picked, shipped and put into the brew within 36 hours. The Wet Hops have a grassy, vegetal, chlorophyll flavor that is reminiscent of fresh cut Greens. We showcase this 100% Cascade Hop Beer with a very light and crisp grain bill including a small portion of rice. This allows the aroma and flavor from such a rare, very seasonal ingredient, to shine.",
    "abv": 5.5,
    "ibu": 25,
    "cb_verified": true,
    "brewer_verified": false,
    "brewer": {
        "id": "e7fa4e64-a39e-fd06-f82a-37de2a7dfbda",
        "object": "brewer",
        "name": "Ballast Point",
        "description": "## It Begins with the Search for Flavor\r\n\r\nThe perfect balance of taste and aroma. An obsession with ingredients. An exploration of techniques. And while we savor the result, we’re just as fascinated by the process to get there. What started as a small group of home brewers, who simply wanted to make a better beer, evolved into the adventurers known today as Ballast Point.\r\n\r\n## Where Science Meets Art\r\n\r\nWe live to add our own touch and ask if there’s a better way. As we tinkered, tested and tasted, we discovered that making beer was more art than science. And while we respect and honor tradition, we relish the opportunity to take it further. That freedom has allowed us to reinterpret brewing. And along the way help to reinvigorate the industry. From bringing a hoppy twist to a porter, or developing a proprietary yeast for our amber ale, to creating a breakthrough gold medal-winning IPA.\r\n\r\n## To Share Our Journey\r\nBut all of this would be wasted if we couldn’t share it. Whether serving up new flavors, collaborating with seasoned brewmasters, or training the next generation at Home Brew Mart, we not only want to challenge our own tastes, but expand yours.\r\nBallast Point. Dedicated to the craft.",
        "short_description": "Award wining brewery built in San Diego, California. Dedicated to the craft.",
        "url": "https://www.ballastpoint.com/",
        "cb_verified": true,
        "brewer_verified": false,
        "facebook_url": "https://www.facebook.com/BallastPoint",
        "twitter_url": "https://twitter.com/BallastPoint",
        "instagram_url": "https://www.instagram.com/ballastpointbrewing/"
    }
}
</pre>

				<h3 id="beer-create">Add a Beer</h3>

				<p>To add a beer to the database, send a <strong>POST</strong> request to the <code>/beer</code> endpoint with the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#beer-object">beer object</a>.</p>
								
								<pre class="api-code">POST https://api.catalog.beer/beer</pre>
								
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Parameter</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>brewer_id</var></td>
							<td>string</td>
							<td>The brewer_id for the brewer who makes the beer.</td>
						</tr>
						<tr>
							<td><var>name</var></td>
							<td>string</td>
							<td>The name of the beer.</td>
						</tr>
						<tr>
							<td><var>style</var></td>
							<td>string</td>
							<td>The style of the beer.</td>
						</tr>
						<tr>
							<td><var>description<br><small class="text-muted">(optional)</small></var></td>
							<td>string</td>
							<td>A description of the beer. This may be a basic description, or it can be detailed contain tasting notes and brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax">markdown</a> and new line characters.</td>
						</tr>
						<tr>
							<td><var>abv</var></td>
							<td>float</td>
							<td>The Alcohol by Volume (ABV) percentage of the beer.</td>
						</tr>
						<tr>
							<td><var>ibu<br><small class="text-muted">(optional)</small></var></td>
							<td>integer</td>
							<td>The International Bitterness/Bittering Units (IBU) value of the beer.</td>
						</tr>
					</tbody>
				</table>

<h4>Sample Request</h4>

<pre class="api-code">curl -X POST
 https://api.catalog.beer/brewer
 -H &#8216;accept: application/json&#8217;
 -H &#8216;authorization: Basic {secret_key}&#8217;
 -H &#8216;content-type: application/json&#8217;
 -d &#8216;{&#8220;brewer_id&#8221;:&#8220;e7fa4e64-a39e-fd06-f82a&#8211;37de2a7dfbda&#8221;,&#8220;name&#8221;:&#8220;Schooner Wet Hop&#8221;,&#8220;style&#8221;:&#8220;Ale with Wet Hops&#8221;,&#8220;description&#8221;:&#8220;Wet Hops (fresh hops) are hops that are picked off the vine and used in the brewing process before they are dried and packaged like normal. To brew this beer we get Cascade Hops from Yakima, Washington. They are picked, shipped and put into the brew within 36 hours. The Wet Hops have a grassy, vegetal, chlorophyll flavor that is reminiscent of fresh cut Greens. We showcase this 100% Cascade Hop Beer with a very light and crisp grain bill including a small portion of rice. This allows the aroma and flavor from such a rare, very seasonal ingredient, to shine.&#8221;,&#8220;abv&#8221;:&#8220;5.5&#8221;,&#8220;ibu&#8221;:&#8220;25&#8221;}&#8217;</pre>

<h3 id="beer-retrieve">Retrieve a Beer</h3>

<p>To retrieve a beer, send a <strong>GET</strong> request to the <code>/beer</code> endpoint with the <var>{beer_id}</var> parameter appended to the path.</p>

<pre class="api-code">GET https://api.catalog.beer/beer/{beer_id}</pre>

<p>A <a href="#beer-object">beer object</a> will be returned for successful requests.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/beer/bc2170df-eef7-8f6b-205b-63cbfeb4a901 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h3 id="beer-list-all">List all Beer</h3>

<p>Retrieves a list of all the beer in the database. To access this data, send a <strong>GET</strong> request to the <code>/beer</code> endpoint.</p>

<pre class="api-code">GET https://api.catalog.beer/beer</pre>

				<h4>Arguments</h4>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Argument</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
						</tr>
						<tr>
							<td><var>count</var><br><small class="text-muted">(optional)</small></td>
							<td>integer</td>
							<td>The number of results you would like returned from your request. The default value is 500.</td>
						</tr>
					</tbody>
				</table>
				<p>A sample request with arguments. Be sure to encode all non-alphanumeric characters except -_.</p>
				<pre class="api-code">GET https://api.catalog.beer/beer?count=5&amp;cursor=NQ%3D%3D</pre>
				
				<h4>Returns</h4>
<p>This request returns a list object with the following parameters.</p>
								
<table class="table">
	<thead>
		<tr>
			<th scope="col">Parameter</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>object</var></td>
			<td>string</td>
			<td>The name of the object. In this case: “list”.</td>
		</tr>
		<tr>
			<td><var>url</var></td>
			<td>string</td>
			<td>The API endpoint accessed to retrieve this object. In this case: <code>/beer</code>.</td>
		</tr>
		<tr>
			<td><var>has_more</var></td>
			<td>Boolean</td>
			<td>Whether or not there is more data available after this set. If <var>false</var>, you have reached the last items on the list.</td>
		</tr>
		<tr>
			<td><var>next_cursor</var></td>
			<td>string</td>
			<td>To retrieve the next set of results, provide this value as the <var>cursor</var> parameter on your subsequent API request.</td>
		</tr>
		<tr>
			<td><var>data</var></td>
			<td>array</td>
			<td>An array containing all the beers in the database sorted alphabetically by name. Each array object has the following attributes: id and name, described below.</td>
		</tr>
		<tr>
			<td><var>id</var></td>
			<td>string</td>
			<td>The <var>beer_id</var>.</td>
		</tr>
		<tr>
			<td><var>name</var></td>
			<td>string</td>
			<td>The name of the beer.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/beer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/beer",
  "has_more": false,
  "data": [
    {
      "id": "01fd323a-5984-a1c5-51a1-9b8abde3afb4",
      "name": "12 Year Old Elijah Craig Ballast Point Victory at Sea"
     },
     {
      "id": "e9a9936e-332c-aa6c-8dcf-70309b483db7",
      "name": "Abandon Ship"
    },
    {
      "id": "b4d1c9f3-cb4a-c364-f1f0-a71a94295ead",
      "name": "Abandon Ship with Chipotle"
    }
  ]
}
</pre>

<h3 id="beer-count">Number of Beers</h3>

<p>To retrieve the total number of beers that are in the database, send a <strong>GET</strong> request to the <code>/beer/count</code> endpoint. A JSON object with the following parameters will be returned.</p>

								<pre class="api-code">GET https://api.catalog.beer/beer/count</pre>
								
<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/beer/count \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "count",
  "url": "/beer/count",
  "value": 87
}
</pre>
								
<h2 id="location">Location</h2>

<p>Brewers can have multiple locations associated with them. These should be public locations at which beer is served as opposed to a production or office space that does not offer beer tasting.</p>

<h3 id="location-object">The Location Object</h3>

<p>When you add an address to a location, are looking for information on a specific location, or are updating a location in the database, successful requests will return the location object in JSON format. That object has the following parameters.</p>
								
<table class="table">
	<thead>
		<tr>
			<th scope="col">Parameter</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>id</var></td>
			<td>string</td>
			<td>The location_id; a unique identifier for the location.</td>
		</tr>
		<tr>
			<td><var>object</var></td>
			<td>string</td>
			<td>The name of the object; in this case: &#8220;location&#8221;.</td>
		</tr>
		<tr>
			<td><var>name</var></td>
			<td>string</td>
			<td>The name of the location.</td>
		</tr>
		<tr>
			<td><var>brewer_id</var></td>
			<td>string</td>
			<td>The unique identifier for the brewer the location is associated with.</td>
		</tr>
		<tr>
			<td><var>url</var></td>
			<td>string</td>
			<td>A URL that is specific to the location.</td>
		</tr>
		<tr>
			<td><var>country_code</var></td>
			<td>string</td>
			<td>The ISO 3166&#8211;1 Alpha&#8211;2 Code for the country in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html">ISO 3166</a> standard.</td>
		</tr>
		<tr>
			<td><var>country_short_name</var></td>
			<td>string</td>
			<td>The ISO 3166&#8211;1 short name for the country, in title case. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html">ISO 3166</a> standard.</td>
		</tr>
		<tr>
			<td><var>latitude</var><br><small class="text-muted">(optional)</small></td>
			<td>float</td>
			<td>The latitude of the location.</td>
		</tr>
		<tr>
			<td><var>longitude</var><br><small class="text-muted">(optional)</small></td>
			<td>float</td>
			<td>The longitude of the location.</td>
		</tr>
		<tr>
			<td><var>telephone</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>An unformatted integer string representing the telephone number of the location. Does not include the country code. For example, a US telephone number written in the international format as +1 (555) 444&#8211;3333 is stored as 5554443333 in our database. Country codes, if required, can be mapped from the <var>country_code</var> field.</td>
		</tr>
		<tr>
			<td><var>address</var><br><small class="text-muted">(optional)</small></td>
			<td>object</td>
			<td>At this time, the database supports addresses for locations in the United States. See the <a href="#us-address-object">US Addresses</a> object.</td>
		</tr>
	</tbody>
</table>

<h4>Sample</h4>

<p>Sample location object with a US address.</p>

<pre class="api-code">
{
    "id": "4920c879-76a6-4bb7-dccf-5f2642e28c08",
    "object": "location",
    "name": "San Diego",
    "brewer_id": "634996c1-5ffb-9099-b015-11cbfe8bb53f",
    "url": "",
    "country_code": "US",
    "country_short_name": "United States of America",
    "latitude": 32.8882179,
    "longitude": -117.1498184,
    "telephone": 8585499888,
    "address": {
        "address1": "",
        "address2": "9990 Alesmith Ct",
        "city": "San Diego",
        "sub_code": "US-CA",
        "state_short": "CA",
        "state_long": "California",
        "zip5": 92126,
        "zip4": 4200
    }
}
</pre>

<h3 id="location-add">Add a Location</h3>

<p>To add a location for a brewer, send a <strong>POST</strong> request to the <code>/location</code> endpoint with the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">POST https://api.catalog.beer/location</pre>
								
<table class="table">
	<thead>
		<tr>
			<th scope="col">Parameter</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>brewer_id</var></td>
			<td>string</td>
			<td>The brewer_id for the brewer you would like to associate the location with.</td>
		</tr>
		<tr>
			<td><var>name</var></td>
			<td>string</td>
			<td>The name of the location. This can be generic and mirror the name of the city in which it is located or can be specific.</td>
		</tr>
		<tr>
			<td><var>url</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A URL specific to the location. This URL should provide visitors additional information on the beer tasting location (e.g. address, hours, or more information). It should not be the same as the brewer&#8217;s URL.</td>
		</tr>
		<tr>
			<td><var>country_code</var></td>
			<td>string</td>
			<td>The ISO 3166&#8211;1 Alpha&#8211;2 Code for the country in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html">ISO 3166</a> standard.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/location \
  -H 'accept: application/json' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"050a11d3-0364-1eef-442f-82909ecadb1b","name":"Wrigley","url":"","country_code":"US"}'
  </pre>

<h4>Sample Response</h4>
 <pre class="api-code">
 {
 &#8220;id&#8221;: &#8220;e00c24d6-de81&#8211;2ea4-dfd8-cd6f2cb5f1e7&#8221;,
 &#8220;object&#8221;: &#8220;location&#8221;,
 &#8220;name&#8221;: &#8220;Wrigley&#8221;,
 &#8220;brewer_id&#8221;: &#8220;050a11d3&#8211;0364&#8211;1eef&#8211;442f&#8211;82909ecadb1b&#8221;,
 &#8220;url&#8221;: &#8220;&#8221;,
 &#8220;country_code&#8221;: &#8220;US&#8221;,
 &#8220;country_short_name&#8221;: &#8220;United States of America&#8221;
}
</pre>

<h3 id="location-add-address">Add an Address to a Location</h3>

<p>Addresses and telephone numbers are stored separately from locations to allow for country specific addressing schemes and telephone numbers. Hence the need to make a second request to add an address to a location.</p>

								<p>To add an address or telephone number for a location, send a <strong>POST</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path and following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#location-object">location object</a>.</p>

<p>Currently, only US addresses are supported. This documentation will be updated once support for other countries has been added.</p>

<pre class="api-code">POST https://api.catalog.beer/location/{location_id}</pre>
								
<table class="table">
	<thead>
		<tr>
			<th scope="col">Parameter</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>address1</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The suite or unit number of the location (if applicable - e.g. Suite 101).</td>
		</tr>
		<tr>
			<td><var>address2</var></td>
			<td>string</td>
			<td>The street address of the location.</td>
		</tr>
		<tr>
			<td><var>city</var><br><small class="text-muted">(optional) Either the <var>city</var> and <var>sub_code</var> must be provided OR the <var>zip5</var> must be provided.</small></td>
			<td>string</td>
			<td>The name of the city.</td>
		</tr>
		<tr>
			<td><var>sub_code</var><br><small class="text-muted">(optional) Either the <var>city</var> and <var>sub_code</var> must be provided OR the <var>zip5</var> must be provided.</small></td>
			<td>string</td>
			<td>The ISO 3166&#8211;2 Code for the subdivision in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html">ISO 3166</a> standard. (e.g. &#8220;US-CA&#8221; for California)</td>
		</tr>
		<tr>
			<td><var>zip5</var><br><small class="text-muted">(optional) Either the <var>city</var> and <var>sub_code</var> must be provided OR the <var>zip5</var> must be provided.</small></td>
			<td>string</td>
			<td>The traditional 5-digit ZIP Code for the location.</td>
		</tr>
		<tr>
			<td><var>zip4</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The additional ZIP+4 Code used by the US Postal Service. More on the <a href="https://about.usps.com/publications/pub100/pub100_044.htm">ZIP+4 Code</a>.</td>
		</tr>
		<tr>
			<td><var>telephone</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The 10-digit telephone number for the location. You may submit the number with formatting (e.g. (555) 444&#8211;3333) or as an integer. Formatting will be stripped when the data is processed and an integer will be returned.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/location/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'content-type: application/json' \
  -d '{"address1":"","address2":"518 W Willow St","city":"Long Beach","sub_code":"US-CA","zip5":"","zip4":"","telephone":""}'
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "id": "e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7",
  "object": "location",
  "name": "Wrigley",
  "brewer_id": "050a11d3-0364-1eef-442f-82909ecadb1b",
  "url": "",
  "country_code": "US",
  "country_short_name": "United States of America",
  "telephone": 0,
  "address": {
    "address1": "",
    "address2": "518 W Willow St",
    "city": "Long Beach",
    "sub_code": "US-CA",
    "state_short": "CA",
    "state_long": "California",
    "zip5": 90806,
    "zip4": 0
  }
}
</pre>

<h3 id="location-retrieve">Retrieve a Location</h3>

								<p>To retrieve a location, send a GET request to the <code>/location</code> endpoint with the <var>{location_id}</var> parameter appended to the path.</p>

<pre class="api-code">GET https://api.catalog.beer/location/{location_id}</pre>

<p>A <a href="#location-object">location object</a> will be returned for successful requests.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/location/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h2 id="us-address">US Address</h2>

<p>For locations in the United States, data is stored and captured using the US Addresses data structure.</p>

<h3 id="us-address-object">The US Address Object</h3>

<p>Addresses for the United States are stored in an object with the following parameters.</p>
								
<table class="table">
	<thead>
		<tr>
			<th scope="col">Parameter</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>address1</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The apartment, suite, or unit number of the location (if applicable).</td>
		</tr>
		<tr>
			<td><var>address2</var></td>
			<td>string</td>
			<td>The street address of the location.</td>
		</tr>
		<tr>
			<td><var>city</var></td>
			<td>string</td>
			<td>The name of the city</td>
		</tr>
		<tr>
			<td><var>sub_code</var></td>
			<td>string</td>
			<td>The ISO 3166&#8211;2 Code for the subdivision in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html">ISO 3166</a> standard. (e.g. US-CA)</td>
		</tr>
		<tr>
			<td><var>state_short</var></td>
			<td>string</td>
			<td>The two character all-caps notation for the state (e.g. CA).</td>
		</tr>
		<tr>
			<td><var>state_long</var></td>
			<td>string</td>
			<td>The full name of the state or district (e.g. California)</td>
		</tr>
		<tr>
			<td><var>zip5</var></td>
			<td>integer</td>
			<td>The traditional 5-digit ZIP Code for the location.</td>
		</tr>
		<tr>
			<td><var>zip4</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The additional ZIP+4 Code used by the US Postal Service. More on the <a href="https://about.usps.com/publications/pub100/pub100_044.htm">ZIP+4 Code</a>.</td>
		</tr>
	</tbody>
</table>
			</div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>