<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('API Documentation');
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
	h4 {
		margin-top:2rem;
	}
	var {
		color: #579a4a;
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
					<a class="list-group-item list-group-item-action" href="#http-methods"><strong>HTTP Methods</strong></a>
					<a class="list-group-item list-group-item-action" href="#brewer"><strong>Brewer</strong></a>
					<a class="list-group-item list-group-item-action" href="#brewer-object">&gt; Brewer Object</a>
					<a class="list-group-item list-group-item-action" href="#brewer-create">&gt; Add a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-update">&gt; Update a Brewer (PUT)</a>
					<a class="list-group-item list-group-item-action" href="#brewer-patch">&gt; Update a Brewer (PATCH)</a>
					<a class="list-group-item list-group-item-action" href="#brewer-delete">&gt; Delete a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-retrieve">&gt; Retrieve a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-list-all">&gt; List all Brewers</a>
					<a class="list-group-item list-group-item-action" href="#brewer-count">&gt; Number of Brewers</a>
					<a class="list-group-item list-group-item-action" href="#brewer-search">&gt; Search Brewers</a>
					<a class="list-group-item list-group-item-action" href="#brewer-beers">&gt; List all Beers made by a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#brewer-locations">&gt; List all the Locations for a Brewer</a>
					<a class="list-group-item list-group-item-action" href="#beer"><strong>Beer</strong></a>
					<a class="list-group-item list-group-item-action" href="#beer-object">&gt; Beer Object</a>
					<a class="list-group-item list-group-item-action" href="#beer-create">&gt; Add a Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-update">&gt; Update a Beer (PUT)</a>
					<a class="list-group-item list-group-item-action" href="#beer-patch">&gt; Update a Beer (PATCH)</a>
					<a class="list-group-item list-group-item-action" href="#beer-delete">&gt; Delete a Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-retrieve">&gt; Retrieve a Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-list-all">&gt; List all Beer</a>
					<a class="list-group-item list-group-item-action" href="#beer-count">&gt; Number of Beers</a>
					<a class="list-group-item list-group-item-action" href="#beer-search">&gt; Search Beer</a>
					<a class="list-group-item list-group-item-action" href="#location"><strong>Location</strong></a>
					<a class="list-group-item list-group-item-action" href="#location-object">&gt; The Location Object</a>
					<a class="list-group-item list-group-item-action" href="#location-add">&gt; Add a Location</a>
					<a class="list-group-item list-group-item-action" href="#location-update">&gt; Update a Location (PUT)</a>
					<a class="list-group-item list-group-item-action" href="#location-patch">&gt; Update a Location (PATCH)</a>
					<a class="list-group-item list-group-item-action" href="#location-delete">&gt; Delete a Location</a>
					<a class="list-group-item list-group-item-action" href="#location-add-address">&gt; Add an Address to a Location</a>
					<a class="list-group-item list-group-item-action" href="#location-replace-address">&gt; Replace an Address (PUT)</a>
					<a class="list-group-item list-group-item-action" href="#location-retrieve">&gt; Retrieve a Location</a>
					<a class="list-group-item list-group-item-action" href="#nearby-locations">&gt; Find Nearby Locations</a>
					<a class="list-group-item list-group-item-action" href="#location-zip">&gt; Find Locations by ZIP Code</a>
					<a class="list-group-item list-group-item-action" href="#location-city">&gt; Find Locations by City</a>
					<a class="list-group-item list-group-item-action" href="#users"><strong>Users</strong></a>
					<a class="list-group-item list-group-item-action" href="#users-object">&gt; The User Object</a>
					<a class="list-group-item list-group-item-action" href="#users-retrieve">&gt; Retrieve a User</a>
					<a class="list-group-item list-group-item-action" href="#users-api-key">&gt; Get API Key</a>
					<a class="list-group-item list-group-item-action" href="#users-patch">&gt; Update a User (PATCH)</a>
					<a class="list-group-item list-group-item-action" href="#users-delete">&gt; Delete a User</a>
					<a class="list-group-item list-group-item-action" href="#users-reset-password">&gt; Request Password Reset</a>
					<a class="list-group-item list-group-item-action" href="#users-password-reset">&gt; Reset Password</a>
					<a class="list-group-item list-group-item-action" href="#us-address"><strong>US Addresses</strong></a>
					<a class="list-group-item list-group-item-action" href="#us-address-object">&gt; The US Address Object</a>
				</div>
			</div>
			<div class="col-md-8">
				<h1 id="top">API Reference</h1>
				<p>Last Updated: February 19, 2026</p>
				
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

				<p>Authentication to the API is performed via the <a href="https://en.wikipedia.org/wiki/Basic_access_authentication">'Basic' HTTP authentication</a> scheme. <strong>Provide your API key as the username value.</strong> You do not need to provide a password.</p>
				
				<p>When making a request using basic HTTP authentication, your request should contain a header field in the form of <code>Authorization: Basic &lt;credentials&gt;</code>, where <code>&lt;credentials&gt;</code> is the <code>base64_encode('username:password')</code> (Recall that your username in this case is your API Key and the password field should be left blank).</p>
				
				<p>For example, if your API Key is: <code>cadcbe6f-a80d-4e33-9f20-b53c2ed83845</code></p>
				
				<pre class="api-code">base64_encode('cadcbe6f-a80d&#8211;4e33&#8211;9f20-b53c2ed83845:')</pre>
				
				<p>Returning: <code>Y2FkY2JlNmYtYTgwZC00ZTMzLTlmMjAtYjUzYzJlZDgzODQ1Og==</code></p>
				
				<p>Then your cURL request will look something like:</p>
				
<pre class="api-code">curl --location --request GET 'https://api.catalog.beer/brewer'
--header 'Accept: application/json'
--header 'Authorization: Basic Y2FkY2JlNmYtYTgwZC00ZTMzLTlmMjAtYjUzYzJlZDgzODQ1Og=='
</pre>

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
    "short_description": "valid"
  },
  "valid_msg": {
    "name": "Please give us the name of the brewery you'd like to add.",
    "url": "",
    "description": "",
    "short_description": ""
  }
}
</pre>
				<p><a href="#top">^ Return to top</a></p>

				<h2 id="http-methods">HTTP Methods</h2>
				<hr>

				<p>The Catalog.beer API supports the following HTTP methods for creating, reading, updating, and deleting resources.</p>

				<table class="table">
					<thead>
						<tr>
							<th scope="col">Method</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong>GET</strong></td>
							<td>Retrieve a resource or list of resources.</td>
						</tr>
						<tr>
							<td><strong>POST</strong></td>
							<td>Create a new resource.</td>
						</tr>
						<tr>
							<td><strong>PUT</strong></td>
							<td>Full replacement of a resource. All required fields must be present. Omitted optional fields are <strong>cleared to null</strong>. If the resource does not exist, it will be created (returns <var>201 Created</var>). If the resource already exists, it will be replaced (returns <var>200 OK</var>).</td>
						</tr>
						<tr>
							<td><strong>PATCH</strong></td>
							<td>Partial update of a resource. Only the fields you provide will be modified; all other fields remain unchanged. The resource must already exist (returns <var>404 Not Found</var> otherwise).</td>
						</tr>
						<tr>
							<td><strong>DELETE</strong></td>
							<td>Remove a resource. Returns <var>204 No Content</var> with no response body on success.</td>
						</tr>
					</tbody>
				</table>

				<p>If you send a request using an HTTP method that is not supported by the endpoint, the API will return a <var>405 Method Not Allowed</var> response. The <code>Allow</code> header on the response will list the methods that the endpoint does support.</p>

				<p><a href="#top">^ Return to top</a></p>

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
							<td><var>last_modified</var></td>
							<td>integer</td>
							<td>A Unix timestamp indicating when the brewer was last modified.</td>
						</tr>
					</tbody>
				</table>
				
				<h4>Sample</h4>

<pre class="api-code">
{
  "id": "65911cdd-52e6-6305-3420-b9bbf6ea958d",
  "object": "brewer",
  "name": "HopSaint",
  "description": "HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn't be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That's at the heart of HopSaint - a community that fosters lasting relationships & enriches our hometown through the production of honest, real beer. A community built on craft beer.",
  "short_description": "A brewery in Torrance, CA.",
  "url": "https://www.hopsaint.com/",
  "cb_verified": true,
  "brewer_verified": false,
  "last_modified": 1736966600
}
</pre>
				<p><a href="#top">^ Return to top</a></p>

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
							<td>A short description of the brewer; max 160 characters. Describe the brewer as you might in a tweet. Short and sweet.</td>
						</tr>
						<tr>
							<td><var>url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s website.</td>
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
  -d '{"name":"HopSaint","description":"HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn\u2019t be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That\u2019s at the heart of HopSaint - a community that fosters lasting relationships & enriches our hometown through the production of honest, real beer. A community built on craft beer.","short_description":"A brewery in Torrance, CA.","url":"https:\/\/www.hopsaint.com\/"}'
</pre>
				<p><a href="#top">^ Return to top</a></p>

				<h3 id="brewer-update">Update a Brewer (PUT)</h3>

				<p>To replace a brewer&#8217;s data, send a <strong>PUT</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the brewer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#brewer-object">brewer object</a>.</p>

				<pre class="api-code">PUT https://api.catalog.beer/brewer/{brewer_id}</pre>

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
							<td>A description of the brewer. Cleared if omitted.</td>
						</tr>
						<tr>
							<td><var>short_description</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A short description of the brewer; max 160 characters. Cleared if omitted.</td>
						</tr>
						<tr>
							<td><var>url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s website. Cleared if omitted.</td>
						</tr>
					</tbody>
				</table>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/brewer/65911cdd-52e6-6305-3420-b9bbf6ea958d \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"HopSaint","description":"HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass.","short_description":"A brewery in Torrance, CA.","url":"https:\/\/www.hopsaint.com\/"}'
</pre>
				<p><a href="#top">^ Return to top</a></p>

				<h3 id="brewer-patch">Update a Brewer (PATCH)</h3>

				<p>To partially update a brewer, send a <strong>PATCH</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#brewer-object">brewer object</a>.</p>

				<pre class="api-code">PATCH https://api.catalog.beer/brewer/{brewer_id}</pre>

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
							<td><var>name</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The name of the brewer.</td>
						</tr>
						<tr>
							<td><var>description</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A description of the brewer.</td>
						</tr>
						<tr>
							<td><var>short_description</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>A short description of the brewer; max 160 characters.</td>
						</tr>
						<tr>
							<td><var>url</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>The URL of the brewer&#8217;s website.</td>
						</tr>
					</tbody>
				</table>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/brewer/65911cdd-52e6-6305-3420-b9bbf6ea958d \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"short_description":"A craft brewery in Torrance, CA."}'
</pre>
				<p><a href="#top">^ Return to top</a></p>

				<h3 id="brewer-delete">Delete a Brewer</h3>

				<p>To delete a brewer, send a <strong>DELETE</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body.</p>

				<pre class="api-code">DELETE https://api.catalog.beer/brewer/{brewer_id}</pre>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/brewer/65911cdd-52e6-6305-3420-b9bbf6ea958d \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>
				<p><a href="#top">^ Return to top</a></p>

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
				<p><a href="#top">^ Return to top</a></p>

				<h3 id="brewer-list-all">List all Brewers</h3>

				<p>If you want a list of all the brewers in the database, send a <strong>GET</strong> request to the <code>/brewer</code> endpoint.</p>

				<pre class="api-code">GET https://api.catalog.beer/brewer</pre>
				
				<h4>Query Parameters</h4>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Name</th>
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
				
				<p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>
				
				<pre class="api-code">GET https://api.catalog.beer/brewer?count=5&amp;cursor=NQ%3D%3D</pre>
				
				<h4>Response</h4>
				
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
							<td>An array containing all the brewers in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, and <var>last_modified</var>, described below.</td>
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
						<tr>
							<td><var>last_modified</var></td>
							<td>integer</td>
							<td>A Unix timestamp representing the date and time the brewer was last modified.</td>
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
      "name": "Ballast Point",
      "last_modified": 1708905600
    },
    {
      "id": "65911cdd-52e6-6305-3420-b9bbf6ea958d",
      "name": "HopSaint",
      "last_modified": 1706313600
    }
  ]
}
</pre>
				<p><a href="#top">^ Return to top</a></p>

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
				<p><a href="#top">^ Return to top</a></p>

				<h3 id="brewer-search">Search Brewers</h3>

				<p>Search for brewers by name or description using full-text search. To search, send a <strong>GET</strong> request to the <code>/brewer/search</code> endpoint with a <var>q</var> query parameter.</p>

				<pre class="api-code">GET https://api.catalog.beer/brewer/search?q={query}</pre>

				<h4>Query Parameters</h4>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Name</th>
							<th scope="col">Type</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><var>q</var></td>
							<td>string</td>
							<td>The search query string. Maximum 255 characters.</td>
						</tr>
						<tr>
							<td><var>count</var><br><small class="text-muted">(optional)</small></td>
							<td>integer</td>
							<td>The number of results you would like returned. The default value is 25. Maximum is 100.</td>
						</tr>
						<tr>
							<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
						</tr>
					</tbody>
				</table>

				<p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>

				<pre class="api-code">GET https://api.catalog.beer/brewer/search?q=stone+brewing&amp;count=5</pre>

				<h4>Response</h4>

				<p>This request returns a list object with the following parameters. Results are sorted by relevance to the search query.</p>

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
							<td>The API endpoint accessed to retrieve this object. In this case: <code>/brewer/search</code>.</td>
						</tr>
						<tr>
							<td><var>query</var></td>
							<td>string</td>
							<td>The search query that was submitted.</td>
						</tr>
						<tr>
							<td><var>has_more</var></td>
							<td>Boolean</td>
							<td>Whether or not there are more results available. If <var>false</var>, you have reached the last items in the result set.</td>
						</tr>
						<tr>
							<td><var>next_cursor</var><br><small class="text-muted">(optional)</small></td>
							<td>string</td>
							<td>To retrieve the next set of results, provide this value as the <var>cursor</var> parameter on your subsequent API request. Only present when <var>has_more</var> is <var>true</var>.</td>
						</tr>
						<tr>
							<td><var>data</var></td>
							<td>array</td>
							<td>An array of <a href="#brewer-object">brewer objects</a> matching the search query, sorted by relevance.</td>
						</tr>
					</tbody>
				</table>

				<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/brewer/search?q=stone' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

				<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/brewer/search",
  "query": "stone",
  "has_more": false,
  "data": [
    {
      "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
      "object": "brewer",
      "name": "Stone Brewing",
      "description": "Stone Brewing, founded in 1996 in San Diego...",
      "short_description": "San Diego craft brewery",
      "url": "https://www.stonebrewing.com/",
      "cb_verified": true,
      "brewer_verified": false,
      "last_modified": 1737234000
    }
  ]
}
</pre>
				<p><a href="#top">^ Return to top</a></p>

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
    "description": "## It Begins with the Search for Flavor\r\n\r\nThe perfect balance of taste and aroma. An obsession with ingredients. An exploration of techniques. And while we savor the result, we're just as fascinated by the process to get there. What started as a small group of home brewers, who simply wanted to make a better beer, evolved into the adventurers known today as Ballast Point.\r\n\r\n## Where Science Meets Art\r\n\r\nWe live to add our own touch and ask if there's a better way. As we tinkered, tested and tasted, we discovered that making beer was more art than science. And while we respect and honor tradition, we relish the opportunity to take it further. That freedom has allowed us to reinterpret brewing. And along the way help to reinvigorate the industry. From bringing a hoppy twist to a porter, or developing a proprietary yeast for our amber ale, to creating a breakthrough gold medal-winning IPA.\r\n\r\n## To Share Our Journey\r\nBut all of this would be wasted if we couldn't share it. Whether serving up new flavors, collaborating with seasoned brewmasters, or training the next generation at Home Brew Mart, we not only want to challenge our own tastes, but expand yours.\r\nBallast Point. Dedicated to the craft.",
    "short_description": "Award wining brewery built in San Diego, California. Dedicated to the craft.",
    "url": "https://www.ballastpoint.com/",
    "cb_verified": true,
    "brewer_verified": false,
    "last_modified": 1736356500
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
				<p><a href="#top">^ Return to top</a></p>

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
							<td><var>brewer</var></td>
							<td>object</td>
							<td>A <a href="#brewer-object">brewer object</a> for the requested brewer.</td>
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
  "brewer": {
    "id": "e7fa4e64-a39e-fd06-f82a-37de2a7dfbda",
    "object": "brewer",
    "name": "Ballast Point",
    "description": null,
    "short_description": null,
    "url": "https://www.ballastpoint.com/",
    "cb_verified": true,
    "brewer_verified": false,
    "last_modified": 1737234000
  },
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
				<p><a href="#top">^ Return to top</a></p>
				
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
							<td><var>object</var></td>
							<td>string</td>
							<td>The name of the object; in this case: &#8220;beer&#8221;.</td>
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
							<td><var>description</var></td>
							<td>string</td>
							<td>A description of the beer. This field may contain a basic description, may contain tasting notes and/or brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax">markdown</a> or new line characters.</td>
						</tr>
						<tr>
							<td><var>abv</var></td>
							<td>float</td>
							<td>The Alcohol by Volume (ABV) percentage of the beer.</td>
						</tr>
						<tr>
							<td><var>ibu</var></td>
							<td>integer</td>
							<td>The International Bitterness/Bittering Units (IBU) value of the beer.</td>
						</tr>
						<tr>
							<td><var>cb_verified</var></td>
							<td>Boolean</td>
							<td>A <var>true</var> or <var>false</var> value denoting whether or not a Catalog.beer administrator has verified the brewer's information.</td>
						</tr>
						<tr>
							<td><var>brewer_verified</var></td>
							<td>Boolean</td>
							<td>A <var>true</var> or <var>false</var> value denoting whether or not the brewer themselves has contributed and verified their information.</td>
						</tr>
						<tr>
							<td><var>last_modified</var></td>
							<td>integer</td>
							<td>A Unix timestamp indicating when the beer was last modified.</td>
						</tr>
						<tr>
							<td><var>brewer</var></td>
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
    "last_modified": 1736547720,
    "brewer": {
        "id": "e7fa4e64-a39e-fd06-f82a-37de2a7dfbda",
        "object": "brewer",
        "name": "Ballast Point",
        "description": "## It Begins with the Search for Flavor\r\n\r\nThe perfect balance of taste and aroma. An obsession with ingredients. An exploration of techniques. And while we savor the result, we're just as fascinated by the process to get there. What started as a small group of home brewers, who simply wanted to make a better beer, evolved into the adventurers known today as Ballast Point.\r\n\r\n## Where Science Meets Art\r\n\r\nWe live to add our own touch and ask if there's a better way. As we tinkered, tested and tasted, we discovered that making beer was more art than science. And while we respect and honor tradition, we relish the opportunity to take it further. That freedom has allowed us to reinterpret brewing. And along the way help to reinvigorate the industry. From bringing a hoppy twist to a porter, or developing a proprietary yeast for our amber ale, to creating a breakthrough gold medal-winning IPA.\r\n\r\n## To Share Our Journey\r\nBut all of this would be wasted if we couldn't share it. Whether serving up new flavors, collaborating with seasoned brewmasters, or training the next generation at Home Brew Mart, we not only want to challenge our own tastes, but expand yours.\r\nBallast Point. Dedicated to the craft.",
        "short_description": "Award wining brewery built in San Diego, California. Dedicated to the craft.",
        "url": "https://www.ballastpoint.com/",
        "cb_verified": true,
        "brewer_verified": false,
        "last_modified": 1736356500
    }
}
</pre>
								<p><a href="#top">^ Return to top</a></p>

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

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/beer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"e7fa4e64-a39e-fd06-f82a-37de2a7dfbda","name":"Schooner Wet Hop","style":"Ale with Wet Hops","description":"Wet Hops (fresh hops) are hops that are picked off the vine and used in the brewing process before they are dried and packaged like normal.","abv":"5.5","ibu":"25"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-update">Update a Beer (PUT)</h3>

<p>To replace a beer&#8217;s data, send a <strong>PUT</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the beer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/beer/{beer_id}</pre>

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
			<td><var>abv</var></td>
			<td>float</td>
			<td>The Alcohol by Volume (ABV) percentage of the beer.</td>
		</tr>
		<tr>
			<td><var>description</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A description of the beer. Cleared if omitted.</td>
		</tr>
		<tr>
			<td><var>ibu</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The International Bitterness/Bittering Units (IBU) value of the beer. Cleared if omitted.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/beer/bc2170df-eef7-8f6b-205b-63cbfeb4a901 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"e7fa4e64-a39e-fd06-f82a-37de2a7dfbda","name":"Schooner Wet Hop","style":"Ale with Wet Hops","abv":5.5,"description":"Wet Hops (fresh hops) are hops that are picked off the vine and used in the brewing process before they are dried and packaged like normal.","ibu":25}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-patch">Update a Beer (PATCH)</h3>

<p>To partially update a beer, send a <strong>PATCH</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PATCH https://api.catalog.beer/beer/{beer_id}</pre>

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
			<td><var>brewer_id</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The brewer_id for the brewer who makes the beer.</td>
		</tr>
		<tr>
			<td><var>name</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The name of the beer.</td>
		</tr>
		<tr>
			<td><var>style</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The style of the beer.</td>
		</tr>
		<tr>
			<td><var>description</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A description of the beer.</td>
		</tr>
		<tr>
			<td><var>abv</var><br><small class="text-muted">(optional)</small></td>
			<td>float</td>
			<td>The Alcohol by Volume (ABV) percentage of the beer.</td>
		</tr>
		<tr>
			<td><var>ibu</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The International Bitterness/Bittering Units (IBU) value of the beer.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/beer/bc2170df-eef7-8f6b-205b-63cbfeb4a901 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"abv":5.8,"ibu":30}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-delete">Delete a Beer</h3>

<p>To delete a beer, send a <strong>DELETE</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body.</p>

<pre class="api-code">DELETE https://api.catalog.beer/beer/{beer_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/beer/bc2170df-eef7-8f6b-205b-63cbfeb4a901 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

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
							
<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-list-all">List all Beer</h3>

<p>Retrieves a list of all the beer in the database. To access this data, send a <strong>GET</strong> request to the <code>/beer</code> endpoint.</p>

<pre class="api-code">GET https://api.catalog.beer/beer</pre>

				<h4>Query Parameters</h4>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">Name</th>
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
				<p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except -_.</p>
				<pre class="api-code">GET https://api.catalog.beer/beer?count=5&amp;cursor=NQ%3D%3D</pre>
				
				<h4>Response</h4>
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
			<td>An array containing all the beers in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, and <var>last_modified</var>, described below.</td>
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
			<td><var>last_modified</var></td>
			<td>integer</td>
			<td>A Unix timestamp representing the date and time the beer was last modified.</td>
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
      "name": "12 Year Old Elijah Craig Ballast Point Victory at Sea",
      "last_modified": 1708905600
     },
     {
      "id": "e9a9936e-332c-aa6c-8dcf-70309b483db7",
      "name": "Abandon Ship",
      "last_modified": 1706313600
    },
    {
      "id": "b4d1c9f3-cb4a-c364-f1f0-a71a94295ead",
      "name": "Abandon Ship with Chipotle",
      "last_modified": 1706313600
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

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
								
<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-search">Search Beer</h3>

<p>Search for beer by name, style, or description using full-text search. To search, send a <strong>GET</strong> request to the <code>/beer/search</code> endpoint with a <var>q</var> query parameter.</p>

<pre class="api-code">GET https://api.catalog.beer/beer/search?q={query}</pre>

<h4>Query Parameters</h4>
<table class="table">
	<thead>
		<tr>
			<th scope="col">Name</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>q</var></td>
			<td>string</td>
			<td>The search query string. Maximum 255 characters.</td>
		</tr>
		<tr>
			<td><var>count</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The number of results you would like returned. The default value is 25. Maximum is 100.</td>
		</tr>
		<tr>
			<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
		</tr>
	</tbody>
</table>

<p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>

<pre class="api-code">GET https://api.catalog.beer/beer/search?q=ipa&amp;count=5</pre>

<h4>Response</h4>

<p>This request returns a list object with the following parameters. Results are sorted by relevance to the search query. Each result is a full <a href="#beer-object">beer object</a> including the nested <a href="#brewer-object">brewer object</a>, so you can display results without making additional API calls.</p>

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
			<td>The API endpoint accessed to retrieve this object. In this case: <code>/beer/search</code>.</td>
		</tr>
		<tr>
			<td><var>query</var></td>
			<td>string</td>
			<td>The search query that was submitted.</td>
		</tr>
		<tr>
			<td><var>has_more</var></td>
			<td>Boolean</td>
			<td>Whether or not there are more results available. If <var>false</var>, you have reached the last items in the result set.</td>
		</tr>
		<tr>
			<td><var>next_cursor</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>To retrieve the next set of results, provide this value as the <var>cursor</var> parameter on your subsequent API request. Only present when <var>has_more</var> is <var>true</var>.</td>
		</tr>
		<tr>
			<td><var>data</var></td>
			<td>array</td>
			<td>An array of <a href="#beer-object">beer objects</a> matching the search query, sorted by relevance. Each beer object includes a nested <a href="#brewer-object">brewer object</a>.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/beer/search?q=ipa&count=2' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/beer/search",
  "query": "ipa",
  "has_more": true,
  "next_cursor": "Mg==",
  "data": [
    {
      "id": "e9a9936e-332c-aa6c-8dcf-70309b483db7",
      "object": "beer",
      "name": "Stone IPA",
      "style": "India Pale Ale",
      "description": "A well-hopped West Coast IPA...",
      "abv": 6.9,
      "ibu": 77,
      "cb_verified": true,
      "brewer_verified": false,
      "last_modified": 1737234000,
      "brewer": {
        "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "object": "brewer",
        "name": "Stone Brewing",
        "description": "Stone Brewing, founded in 1996...",
        "short_description": "San Diego craft brewery",
        "url": "https://www.stonebrewing.com/",
        "cb_verified": true,
        "brewer_verified": false,
        "last_modified": 1737234000
      }
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

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
			<td><var>cb_verified</var></td>
			<td>Boolean</td>
			<td>A <var>true</var> or <var>false</var> value denoting whether or not a Catalog.beer administrator has verified the location&#8217;s information.</td>
		</tr>
		<tr>
			<td><var>brewer_verified</var></td>
			<td>Boolean</td>
			<td>A <var>true</var> or <var>false</var> value denoting whether or not the brewer themselves has contributed and verified the location&#8217;s information.</td>
		</tr>
		<tr>
			<td><var>last_modified</var></td>
			<td>integer</td>
			<td>A Unix timestamp indicating when the location was last modified.</td>
		</tr>
		<tr>
			<td><var>address</var><br><small class="text-muted">(optional)</small></td>
			<td>object</td>
			<td>At this time, the database supports addresses for locations in the United States. See the <a href="#us-address-object">US Addresses</a> object. The <var>telephone</var> field is included within this object.</td>
		</tr>
		<tr>
			<td><var>brewer</var></td>
			<td>object</td>
			<td>A <a href="#brewer-object">brewer object</a> containing information on the brewer associated with this location.</td>
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
    "url": "",
    "country_code": "US",
    "country_short_name": "United States of America",
    "latitude": 32.8882179,
    "longitude": -117.1498184,
    "cb_verified": true,
    "brewer_verified": false,
    "last_modified": 1736729100,
    "address": {
        "address1": "",
        "address2": "9990 Alesmith Ct",
        "city": "San Diego",
        "sub_code": "US-CA",
        "state_short": "CA",
        "state_long": "California",
        "zip5": 92126,
        "zip4": 4200,
        "telephone": 8585499888
    },
    "brewer": {
        "id": "634996c1-5ffb-9099-b015-11cbfe8bb53f",
        "object": "brewer",
        "name": "AleSmith Brewing Company",
        "description": "",
        "short_description": "",
        "url": "https://alesmith.com/",
        "cb_verified": true,
        "brewer_verified": false,
        "last_modified": 1736103600
    }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

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
  "id": "e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7",
  "object": "location",
  "name": "Wrigley",
  "url": "",
  "country_code": "US",
  "country_short_name": "United States of America",
  "latitude": null,
  "longitude": null,
  "cb_verified": false,
  "brewer_verified": false,
  "last_modified": 1738429200,
  "brewer": {
    "id": "050a11d3-0364-1eef-442f-82909ecadb1b",
    "object": "brewer",
    "name": "Brouwerij West",
    "description": "",
    "short_description": "",
    "url": "",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1738103400
  }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-update">Update a Location (PUT)</h3>

<p>To replace a location&#8217;s data, send a <strong>PUT</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the location does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/location/{location_id}</pre>

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
			<td>The name of the location.</td>
		</tr>
		<tr>
			<td><var>country_code</var></td>
			<td>string</td>
			<td>The ISO 3166&#8211;1 Alpha&#8211;2 Code for the country in which the location is located.</td>
		</tr>
		<tr>
			<td><var>url</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A URL specific to the location. Cleared if omitted.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/location/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"050a11d3-0364-1eef-442f-82909ecadb1b","name":"Wrigley","country_code":"US","url":"https://www.brouwerijwest.com/wrigley"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-patch">Update a Location (PATCH)</h3>

<p>To partially update a location, send a <strong>PATCH</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">PATCH https://api.catalog.beer/location/{location_id}</pre>

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
			<td><var>brewer_id</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The brewer_id for the brewer you would like to associate the location with.</td>
		</tr>
		<tr>
			<td><var>name</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The name of the location.</td>
		</tr>
		<tr>
			<td><var>country_code</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The ISO 3166&#8211;1 Alpha&#8211;2 Code for the country in which the location is located.</td>
		</tr>
		<tr>
			<td><var>url</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A URL specific to the location.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/location/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"Wrigley Taproom"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-delete">Delete a Location</h3>

<p>To delete a location, send a <strong>DELETE</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body.</p>

<pre class="api-code">DELETE https://api.catalog.beer/location/{location_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/location/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-add-address">Add an Address to a Location</h3>

<p>Addresses and telephone numbers are stored separately from locations to allow for country specific addressing schemes and telephone numbers. Hence the need to make a second request to add an address to a location.</p>

								<p>To add an address or telephone number for a location, send a <strong>POST</strong> request to the <code>/address</code> endpoint with the <var>location_id</var> appended to the path and the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#location-object">location object</a>.</p>

<p>Currently, only US addresses are supported. This documentation will be updated once support for other countries has been added.</p>

<pre class="api-code">POST https://api.catalog.beer/address/{location_id}</pre>
								
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
  https://api.catalog.beer/address/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
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
  "url": "",
  "country_code": "US",
  "country_short_name": "United States of America",
  "latitude": 33.7946519,
  "longitude": -118.1891897,
  "cb_verified": false,
  "brewer_verified": false,
  "last_modified": 1738429500,
  "address": {
    "address1": "",
    "address2": "518 W Willow St",
    "city": "Long Beach",
    "sub_code": "US-CA",
    "state_short": "CA",
    "state_long": "California",
    "zip5": 90806,
    "zip4": null,
    "telephone": null
  },
  "brewer": {
    "id": "050a11d3-0364-1eef-442f-82909ecadb1b",
    "object": "brewer",
    "name": "Brouwerij West",
    "description": "",
    "short_description": "",
    "url": "",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1738103400
  }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-replace-address">Replace an Address</h3>

<p>To create or replace an address for a location, send a <strong>PUT</strong> request to the <code>/address</code> endpoint with the <var>location_id</var> appended to the path. This will completely replace any existing address data for the location. Omitted optional fields will be cleared to null. Successful requests will return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/address/{location_id}</pre>

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
			<td>The suite or unit number of the location (if applicable - e.g. Suite 101). Cleared if omitted.</td>
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
			<td>The additional ZIP+4 Code used by the US Postal Service. Cleared if omitted.</td>
		</tr>
		<tr>
			<td><var>telephone</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The 10-digit telephone number for the location. Cleared if omitted.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/address/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"address2":"518 W Willow St","city":"Long Beach","sub_code":"US-CA"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-retrieve">Retrieve a Location</h3>

								<p>To retrieve a location, send a <strong>GET</strong> request to the <code>/location</code> endpoint with the <var>{location_id}</var> parameter appended to the path.</p>

<pre class="api-code">GET https://api.catalog.beer/location/{location_id}</pre>

<p>A <a href="#location-object">location object</a> will be returned for successful requests.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/location/e00c24d6-de81-2ea4-dfd8-cd6f2cb5f1e7 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>
								
<p><a href="#top">^ Return to top</a></p>
								
<!----- /LOCATIONS/NEARBY ----->

<h3 id="nearby-locations">Find Nearby Locations</h3>

<p>One of the questions that gets asked most is &#8220;where is the nearest brewery?&#8221; or &#8220;I&#8217;m heading to Acme town, what breweries are local?&#8221;. To answer those questions or questions like them, use this endpoint.</p>

<p>To retrieve a location, send a <strong>GET</strong> request to the <code>/location/nearby </code> endpoint with the <var>{latitude}</var> and <var>{longitude}</var> query parameters appended to the path.</p>
								
<h4>Query Parameters</h4>
<table class="table">
	<thead>
		<tr>
			<th scope="col">Name</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>latitude</var></td>
			<td>float</td>
			<td>The latitude of the location where you would like to search around. Combined with longitude, this pair describes the center of your search radius.</td>
		</tr>
		<tr>
			<td><var>longitude</var></td>
			<td>float</td>
			<td>The longitude of the location where you would like to search around. Combined with latitude, this pair describes the center of your search radius.</td>
		</tr>
		<tr>
			<td><var>search_radius</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The radius of the search circle, centered at the provided latitude and longitude. If left empty, the default value of <var>25</var> will be used. The default units are miles. Use the <var>metric</var> flag to search in kilometers.</td>
		</tr>
		<tr>
			<td><var>metric</var><br><small class="text-muted">(optional)</small></td>
			<td>boolean</td>
			<td>Set this value to <var>true</var> if you would like your search radius and results to be measured in kilometers. The default value for this variable is <var>false</var>, yielding a search radius and results measured in miles, though you can state it explicitly.</td>
		</tr>
		<tr>
			<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
		</tr>
		<tr>
			<td><var>count</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The number of results you would like returned from your request. The default value is 100.</td>
		</tr>
	</tbody>
</table>
								
<p>A sample request with query parameters.</p>

<pre class="api-code">GET https://api.catalog.beer/location/nearby?latitude={latitude}&longitude={longitude}</pre>
								
<h4>Response</h4>
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
			<td>The API endpoint accessed to retrieve this object. In this case: <code>/location/nearby</code>.</td>
		</tr>
		<tr>
			<td><var>has_more</var></td>
			<td>Boolean</td>
			<td>Whether or not there is more data available after this set. If <var>false</var>, you have reached the last items on the list.</td>
		</tr>
		<tr>
			<td><var>next_cursor</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>To retrieve the next set of results, provide this value as the <var>cursor</var> parameter on your subsequent API request. Only present when <var>has_more</var> is <var>true</var>.</td>
		</tr>
		<tr>
			<td><var>data</var></td>
			<td>array</td>
			<td>
				<p>An array containing all the locations that match your query parameters. Each result contains three objects:</p>
				<ul>
					<li>A <var>location</var> object containing: <var>id</var>, <var>object</var>, <var>name</var>, <var>brewer_id</var>, <var>url</var>, <var>country_code</var>, <var>country_short_name</var>, <var>latitude</var>, <var>longitude</var>, <var>telephone</var>, and a nested <var>address</var> object</li>
					<li>A <var>distance</var> object described below</li>
					<li>A <var>brewer</var> object containing: <var>id</var>, <var>object</var>, <var>name</var>, <var>description</var>, <var>short_description</var>, <var>url</var>, <var>cb_verified</var>, <var>brewer_verified</var></li>
				</ul>
			</td>
		</tr>
		<tr>
			<td><var>distance</var></td>
			<td>float</td>
			<td>The straight line distance from the query <var>latitude</var> and <var>longitude</var> to the brewery, rounded to the tenths place (a single decimal place).</td>
		</tr>
		<tr>
			<td><var>units</var></td>
			<td>string</td>
			<td>The unit of distance. The value of this field will be either &#8220;miles&#8221; (the default value) or &#8220;kilometers&#8221; if the <var>metric</var> query parameter is set to <var>true</var>.</td>
		</tr>
	</tbody>
</table>
								
<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location/nearby?latitude=32.748482&longitude=-117.130094&search_radius=10&count=1' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/location/nearby",
  "has_more": true,
  "next_cursor": "MQ==",
  "data": [
    {
      "location": {
        "id": "d23d1ef7-4659-23e9-9ddb-405ece1223e9",
        "object": "location",
        "name": "North Park",
        "brewer_id": "008fdcf3-b59d-9d7e-6b14-540a88bb36fa",
        "url": "",
        "country_code": "US",
        "country_short_name": "United States of America",
        "latitude": 32.7476883,
        "longitude": -117.12854,
        "telephone": "6192557136",
        "address": {
          "address1": "",
          "address2": "3812 Grim Ave",
          "city": "San Diego",
          "sub_code": "US-CA",
          "state_short": "CA",
          "state_long": "California",
          "zip5": "92104",
          "zip4": "3602"
        }
      },
      "distance": {
        "distance": 0.1,
        "units": "miles"
      },
      "brewer": {
        "id": "008fdcf3-b59d-9d7e-6b14-540a88bb36fa",
        "object": "brewer",
        "name": "Mike Hess Brewing Co.",
        "description": null,
        "short_description": null,
        "url": "https://www.mikehessbrewing.com/",
        "cb_verified": true,
        "brewer_verified": false
      }
    }
  ]
}
</pre>
					
<p><a href="#top">^ Return to top</a></p>

<!----- LOCATION: ZIP CODE ----->

<h3 id="location-zip">Find Locations by ZIP Code</h3>

<p>Find nearby brewery locations by providing a US ZIP code. This endpoint geocodes the ZIP code and returns locations within the search radius, just like the <a href="#nearby-locations">Find Nearby Locations</a> endpoint.</p>

<p>Send a <strong>GET</strong> request to the <code>/location/zip</code> endpoint with the <var>zip_code</var> query parameter.</p>

<h4>Query Parameters</h4>
<table class="table">
	<thead>
		<tr>
			<th scope="col">Name</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>zip_code</var></td>
			<td>string</td>
			<td>A 5-digit US ZIP code (e.g., <var>92104</var>).</td>
		</tr>
		<tr>
			<td><var>search_radius</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The radius of the search circle, centered at the provided ZIP code. If left empty, the default value of <var>25</var> will be used. The default units are miles. Use the <var>metric</var> flag to search in kilometers.</td>
		</tr>
		<tr>
			<td><var>metric</var><br><small class="text-muted">(optional)</small></td>
			<td>boolean</td>
			<td>Set this value to <var>true</var> if you would like your search radius and results to be measured in kilometers. The default value for this variable is <var>false</var>, yielding a search radius and results measured in miles.</td>
		</tr>
		<tr>
			<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
		</tr>
		<tr>
			<td><var>count</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The number of results you would like returned from your request. The default value is 100.</td>
		</tr>
	</tbody>
</table>

<h4>Response</h4>
<p>The response format is identical to the <a href="#nearby-locations">Find Nearby Locations</a> endpoint, returning a list object with <var>location</var>, <var>distance</var>, and <var>brewer</var> objects for each result. The <var>url</var> field will be <code>/location/zip</code>.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location/zip?zip_code=92104&search_radius=10&count=1' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/location/zip",
  "has_more": true,
  "next_cursor": "MQ==",
  "data": [
    {
      "location": {
        "id": "d23d1ef7-4659-23e9-9ddb-405ece1223e9",
        "object": "location",
        "name": "North Park",
        "brewer_id": "008fdcf3-b59d-9d7e-6b14-540a88bb36fa",
        "url": "",
        "country_code": "US",
        "country_short_name": "United States of America",
        "latitude": 32.7476883,
        "longitude": -117.12854,
        "telephone": "6192557136",
        "address": {
          "address1": "",
          "address2": "3812 Grim Ave",
          "city": "San Diego",
          "sub_code": "US-CA",
          "state_short": "CA",
          "state_long": "California",
          "zip5": "92104",
          "zip4": "3602"
        }
      },
      "distance": {
        "distance": 0.1,
        "units": "miles"
      },
      "brewer": {
        "id": "008fdcf3-b59d-9d7e-6b14-540a88bb36fa",
        "object": "brewer",
        "name": "Mike Hess Brewing Co.",
        "description": null,
        "short_description": null,
        "url": "https://www.mikehessbrewing.com/",
        "cb_verified": true,
        "brewer_verified": false
      }
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- LOCATION: CITY ----->

<h3 id="location-city">Find Locations by City</h3>

<p>Find nearby brewery locations by providing a city and state. This endpoint geocodes the city and returns locations within the search radius, just like the <a href="#nearby-locations">Find Nearby Locations</a> endpoint.</p>

<p>Send a <strong>GET</strong> request to the <code>/location/city</code> endpoint with the <var>city</var> and <var>state</var> query parameters.</p>

<h4>Query Parameters</h4>
<table class="table">
	<thead>
		<tr>
			<th scope="col">Name</th>
			<th scope="col">Type</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><var>city</var></td>
			<td>string</td>
			<td>The name of the city to search around (e.g., <var>San Diego</var>).</td>
		</tr>
		<tr>
			<td><var>state</var></td>
			<td>string</td>
			<td>The state name or abbreviation (e.g., <var>California</var> or <var>CA</var>).</td>
		</tr>
		<tr>
			<td><var>search_radius</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The radius of the search circle, centered at the provided city. If left empty, the default value of <var>25</var> will be used. The default units are miles. Use the <var>metric</var> flag to search in kilometers.</td>
		</tr>
		<tr>
			<td><var>metric</var><br><small class="text-muted">(optional)</small></td>
			<td>boolean</td>
			<td>Set this value to <var>true</var> if you would like your search radius and results to be measured in kilometers. The default value for this variable is <var>false</var>, yielding a search radius and results measured in miles.</td>
		</tr>
		<tr>
			<td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
		</tr>
		<tr>
			<td><var>count</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>The number of results you would like returned from your request. The default value is 100.</td>
		</tr>
	</tbody>
</table>

<h4>Response</h4>
<p>The response format is identical to the <a href="#nearby-locations">Find Nearby Locations</a> endpoint, returning a list object with <var>location</var>, <var>distance</var>, and <var>brewer</var> objects for each result. The <var>url</var> field will be <code>/location/city</code>.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location/city?city=San%20Diego&state=CA&search_radius=10&count=1' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/location/city",
  "has_more": true,
  "next_cursor": "MQ==",
  "data": [
    {
      "location": {
        "id": "d23d1ef7-4659-23e9-9ddb-405ece1223e9",
        "object": "location",
        "name": "North Park",
        "brewer_id": "008fdcf3-b59d-9d7e-6b14-540a88bb36fa",
        "url": "",
        "country_code": "US",
        "country_short_name": "United States of America",
        "latitude": 32.7476883,
        "longitude": -117.12854,
        "telephone": "6192557136",
        "address": {
          "address1": "",
          "address2": "3812 Grim Ave",
          "city": "San Diego",
          "sub_code": "US-CA",
          "state_short": "CA",
          "state_long": "California",
          "zip5": "92104",
          "zip4": "3602"
        }
      },
      "distance": {
        "distance": 2.3,
        "units": "miles"
      },
      "brewer": {
        "id": "008fdcf3-b59d-9d7e-6b14-540a88bb36fa",
        "object": "brewer",
        "name": "Mike Hess Brewing Co.",
        "description": null,
        "short_description": null,
        "url": "https://www.mikehessbrewing.com/",
        "cb_verified": true,
        "brewer_verified": false
      }
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!---------- US ADDRESSES ---------->
								
<h2 id="us-address">US Address</h2>

<p>For locations in the United States, data is stored and captured using the US Addresses data structure.</p>
								
<!----- US ADDRESSES: OBJECT ----->

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
		<tr>
			<td><var>telephone</var><br><small class="text-muted">(optional)</small></td>
			<td>integer</td>
			<td>An unformatted integer representing the telephone number of the location. Does not include the country code. For example, a US telephone number written in the international format as +1 (555) 444&#8211;3333 is stored as 5554443333 in our database.</td>
		</tr>
	</tbody>
</table>

<p><a href="#top">^ Return to top</a></p>

<!---------- USERS ---------->

<h2 id="users">Users</h2>
<hr>

<p>The users endpoints allow you to manage your own account. User accounts are created through the <a href="https://catalog.beer">Catalog.beer</a> website, not directly via the API.</p>

<!----- USERS: OBJECT ----->

<h3 id="users-object">The User Object</h3>

<p>Successful requests to user endpoints will return the user object in JSON format. That object has the following parameters.</p>

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
			<td>The user_id; a unique identifier for the user.</td>
		</tr>
		<tr>
			<td><var>object</var></td>
			<td>string</td>
			<td>The name of the object. In this case: &#8220;users&#8221;.</td>
		</tr>
		<tr>
			<td><var>name</var></td>
			<td>string</td>
			<td>The user&#8217;s name.</td>
		</tr>
		<tr>
			<td><var>email</var></td>
			<td>string</td>
			<td>The user&#8217;s email address.</td>
		</tr>
		<tr>
			<td><var>email_verified</var></td>
			<td>Boolean</td>
			<td>A <var>true</var> or <var>false</var> value denoting whether the user&#8217;s email address has been verified.</td>
		</tr>
		<tr>
			<td><var>email_auth</var></td>
			<td>string/null</td>
			<td>The email verification code, or <var>null</var> if the email has already been verified.</td>
		</tr>
		<tr>
			<td><var>email_auth_sent</var></td>
			<td>integer/null</td>
			<td>A Unix timestamp indicating when the email verification was sent, or <var>null</var>.</td>
		</tr>
		<tr>
			<td><var>admin</var></td>
			<td>Boolean</td>
			<td>A <var>true</var> or <var>false</var> value denoting whether the user has administrator privileges.</td>
		</tr>
	</tbody>
</table>

<h4>Sample</h4>

<pre class="api-code">
{
  "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "object": "users",
  "name": "Jane Doe",
  "email": "jane@example.com",
  "email_verified": true,
  "email_auth": null,
  "email_auth_sent": null,
  "admin": false
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- USERS: RETRIEVE ----->

<h3 id="users-retrieve">Retrieve a User</h3>

<p>To retrieve your user information, send a <strong>GET</strong> request to the <code>/users</code> endpoint with your <var>{user_id}</var> appended to the path. You can only retrieve your own user information.</p>

<pre class="api-code">GET https://api.catalog.beer/users/{user_id}</pre>

<p>A <a href="#users-object">user object</a> will be returned for successful requests.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/users/a1b2c3d4-e5f6-7890-abcd-ef1234567890 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- USERS: API KEY ----->

<h3 id="users-api-key">Get API Key</h3>

<p>To retrieve your API key, send a <strong>GET</strong> request to the <code>/users/{user_id}/api-key</code> endpoint. You can only retrieve your own API key. The API key will be <var>null</var> if your email address has not yet been verified.</p>

<pre class="api-code">GET https://api.catalog.beer/users/{user_id}/api-key</pre>

<h4>Response</h4>

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
			<td>The name of the object. In this case: &#8220;api_key&#8221;.</td>
		</tr>
		<tr>
			<td><var>user_id</var></td>
			<td>string</td>
			<td>The user_id associated with the API key.</td>
		</tr>
		<tr>
			<td><var>api_key</var></td>
			<td>string/null</td>
			<td>The API key, or <var>null</var> if the user&#8217;s email has not been verified.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/users/a1b2c3d4-e5f6-7890-abcd-ef1234567890/api-key \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "api_key",
  "user_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "api_key": "cadcbe6f-a80d-4e33-9f20-b53c2ed83845"
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- USERS: PATCH ----->

<h3 id="users-patch">Update a User (PATCH)</h3>

<p>To update your account, send a <strong>PATCH</strong> request to the <code>/users</code> endpoint with your <var>user_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#users-object">user object</a>.</p>

<pre class="api-code">PATCH https://api.catalog.beer/users/{user_id}</pre>

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
			<td><var>name</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The user&#8217;s name. Max 255 characters.</td>
		</tr>
		<tr>
			<td><var>email</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>The user&#8217;s email address. Changing your email to a different domain will reset your email verification status and any brewery staff privileges associated with the previous domain.</td>
		</tr>
		<tr>
			<td><var>password</var><br><small class="text-muted">(optional)</small></td>
			<td>string</td>
			<td>A new password for the account. Must be at least 8 characters.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/users/a1b2c3d4-e5f6-7890-abcd-ef1234567890 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"Jane Smith"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- USERS: DELETE ----->

<h3 id="users-delete">Delete a User</h3>

<p>To delete your account, send a <strong>DELETE</strong> request to the <code>/users</code> endpoint with your <var>user_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body. This action permanently deletes your account and associated data.</p>

<pre class="api-code">DELETE https://api.catalog.beer/users/{user_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/users/a1b2c3d4-e5f6-7890-abcd-ef1234567890 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- USERS: REQUEST PASSWORD RESET ----->

<h3 id="users-reset-password">Request Password Reset</h3>

<p>To request a password reset, send a <strong>POST</strong> request to the <code>/users/{user_id}/reset-password</code> endpoint. The user&#8217;s email must be verified. A password reset email will be sent to the email address on file. This endpoint is rate limited to one request per 15 minutes. Successful requests return a <var>204 No Content</var> response.</p>

<pre class="api-code">POST https://api.catalog.beer/users/{user_id}/reset-password</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/users/a1b2c3d4-e5f6-7890-abcd-ef1234567890/reset-password \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- USERS: RESET PASSWORD ----->

<h3 id="users-password-reset">Reset Password</h3>

<p>To reset your password using a password reset key (received via email), send a <strong>POST</strong> request to the <code>/users/password-reset/{password_reset_key}</code> endpoint with your new password in the request body. Successful requests return a <var>204 No Content</var> response.</p>

<pre class="api-code">POST https://api.catalog.beer/users/password-reset/{password_reset_key}</pre>

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
			<td><var>password</var></td>
			<td>string</td>
			<td>The new password. Must be at least 8 characters.</td>
		</tr>
	</tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/users/password-reset/e5f67890-abcd-1234-5678-90abcdef1234 \
  -H 'accept: application/json' \
  -H 'content-type: application/json' \
  -d '{"password":"mynewpassword123"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

			</div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>