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
                    <a class="list-group-item list-group-item-action" href="#styles"><strong>Styles</strong></a>
                    <a class="list-group-item list-group-item-action" href="#style-object">&gt; The Style Object</a>
                    <a class="list-group-item list-group-item-action" href="#style-specs">&gt; The Specs Object</a>
                    <a class="list-group-item list-group-item-action" href="#style-list">&gt; List Styles</a>
                    <a class="list-group-item list-group-item-action" href="#style-detail">&gt; Retrieve a Style</a>
                    <a class="list-group-item list-group-item-action" href="#style-parents">&gt; List Families</a>
                    <a class="list-group-item list-group-item-action" href="#style-classes">&gt; List Classes</a>
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
                    <a class="list-group-item list-group-item-action" href="#usage"><strong>Usage</strong></a>
                    <a class="list-group-item list-group-item-action" href="#usage-object">&gt; The Usage Object</a>
                    <a class="list-group-item list-group-item-action" href="#usage-my-usage">&gt; Get My Usage</a>
                    <a class="list-group-item list-group-item-action" href="#us-address"><strong>US Addresses</strong></a>
                    <a class="list-group-item list-group-item-action" href="#us-address-object">&gt; The US Address Object</a>
                    <a class="list-group-item list-group-item-action" href="#users"><strong>Users</strong></a>
                    <a class="list-group-item list-group-item-action" href="#users-object">&gt; The User Object</a>
                    <a class="list-group-item list-group-item-action" href="#users-retrieve">&gt; Retrieve a User</a>
                    <a class="list-group-item list-group-item-action" href="#users-api-key">&gt; Get API Key</a>
                    <a class="list-group-item list-group-item-action" href="#users-patch">&gt; Update a User (PATCH)</a>
                    <a class="list-group-item list-group-item-action" href="#users-delete">&gt; Delete a User</a>
                    <a class="list-group-item list-group-item-action" href="#users-reset-password">&gt; Request Password Reset</a>
                    <a class="list-group-item list-group-item-action" href="#users-password-reset">&gt; Reset Password</a>
                </div>
            </div>
            <div class="col-md-8">
                <h1 id="top">API Reference</h1>
                <p>Last Updated: June 25, 2026</p>

                <h2 id="url">API Basics</h2>
                <hr>

                <p>The Catalog.beer API is organized around REST. We use HTTP response codes to indicate the success or failure of your request. We also use basic HTTP features like HTTP authentication and HTTP verbs.</p>

                <p>The Catalog.beer API can be accessed using the following root URL:</p>

                <pre class="api-code">https://api.catalog.beer</pre>

                <p>When making an API request, be sure to include an <code>accept: application/json</code> header. All data returned by the API will be in JSON format.</p>

                <p>Similarly, when making a PUT or POST request to the API, the body of your request must be in JSON as well. Be sure to include the <code>content-type: application/json</code> header in your request.</p>

                <h2 id="authentication">Authentication</h2>
                <hr>

                <p>Authenticate your account when using the API by including your secret API key in the request. You can find your API key on your <a href="/account">Account</a> page. Your API key carries many privileges, so be sure to keep it secret! Do not share your secret API key in publicly accessible areas such GitHub, client-side code, and so forth.</p>

                <p>Authentication to the API is performed via the <a href="https://en.wikipedia.org/wiki/Basic_access_authentication" target="_blank" rel="noopener">'Basic' HTTP authentication</a> scheme. <strong>Provide your API key as the username value.</strong> You do not need to provide a password.</p>

                <p>When making a request using basic HTTP authentication, your request should contain a header field in the form of <code>Authorization: Basic &lt;credentials&gt;</code>, where <code>&lt;credentials&gt;</code> is the <code>base64_encode('username:password')</code> (Recall that your username in this case is your API Key and the password field should be left blank).</p>

                <p>For example, if your API Key is: <code>cadcbe6f-a80d-4e33-9f20-b53c2ed83845</code></p>

                <pre class="api-code">base64_encode('cadcbe6f-a80d-4e33-9f20-b53c2ed83845:')</pre>

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

                <p>The brewer object is the central piece of the beer data puzzle. Brewers can have beers and locations associated with them. And in order to add a beer or location to the database, there must be a brewer to associate them with.</p>

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
                            <td>A description of the brewer. Note that this field may contain <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank" rel="noopener">markdown</a> or new line characters.</td>
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
  "description": "HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn't be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That's at the heart of HopSaint - a community that fosters lasting relationships &amp; enriches our hometown through the production of honest, real beer. A community built on craft beer.",
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
                            <td>A description of the brewer. This should be like the &#8220;About&#8221; page posted by the brewer. A brief origin story coupled with who they are. This field supports <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank" rel="noopener">markdown</a>.</td>
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
  -d '{"name":"HopSaint","description":"HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn\u2019t be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That\u2019s at the heart of HopSaint - a community that fosters lasting relationships &amp; enriches our hometown through the production of honest, real beer. A community built on craft beer.","short_description":"A brewery in Torrance, CA.","url":"https:\/\/www.hopsaint.com\/"}'
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
                            <td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
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
                            <td>An array containing all the beers associated with this brewer in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, <var>style</var>, <var>style_id</var>, <var>parent</var>, <var>class</var>, and <var>beverage_type</var> described below.</td>
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
                            <td>The style of the beer (human-readable label).</td>
                        </tr>
                        <tr>
                            <td><var>style_id</var></td>
                            <td>string</td>
                            <td>The canonical <a href="#styles">style</a> id, or <var>null</var> if filed at the family/class level.</td>
                        </tr>
                        <tr>
                            <td><var>parent</var></td>
                            <td>string</td>
                            <td>The canonical family id (e.g. <var>porter</var>).</td>
                        </tr>
                        <tr>
                            <td><var>class</var></td>
                            <td>string</td>
                            <td>The super-class (<var>ale</var>/<var>lager</var>), or <var>null</var>.</td>
                        </tr>
                        <tr>
                            <td><var>beverage_type</var></td>
                            <td>string</td>
                            <td><var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>.</td>
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
    "short_description": "Award winning brewery built in San Diego, California. Dedicated to the craft.",
    "url": "https://www.ballastpoint.com/",
    "cb_verified": true,
    "brewer_verified": false,
    "last_modified": 1736356500
  },
  "data": [
    {
      "id": "01fd323a-5984-a1c5-51a1-9b8abde3afb4",
      "name": "12 Year Old Elijah Craig Ballast Point Victory at Sea",
      "style": "Imperial Porter",
      "style_id": "american-imperial-porter",
      "parent": "porter",
      "class": "ale",
      "beverage_type": "beer"
    },
    {
      "id": "e9a9936e-332c-aa6c-8dcf-70309b483db7",
      "name": "Abandon Ship",
      "style": "Smoked Beer",
      "style_id": null,
      "parent": "smoked-beer",
      "class": null,
      "beverage_type": "beer"
    },
    {
      "id": "b4d1c9f3-cb4a-c364-f1f0-a71a94295ead",
      "name": "Abandon Ship with Chipotle",
      "style": "Smoked Beer",
      "style_id": null,
      "parent": "smoked-beer",
      "class": null,
      "beverage_type": "beer"
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
                            <td>The style of the beer as a human-readable label &#8212; the brewer&#8217;s own wording, preserved exactly (e.g. &#8220;West Coast IPA&#8221;).</td>
                        </tr>
                        <tr>
                            <td><var>style_id</var></td>
                            <td>string</td>
                            <td>The canonical <a href="#styles">style</a> the label resolved to (e.g. <var>american-ipa</var>), or <var>null</var> when the beer is filed at the family or class level rather than a specific style.</td>
                        </tr>
                        <tr>
                            <td><var>parent</var></td>
                            <td>string</td>
                            <td>The canonical family (e.g. <var>ipa</var>) the beer belongs to. Derived from <var>style_id</var>, or set directly when filed at the family level.</td>
                        </tr>
                        <tr>
                            <td><var>class</var></td>
                            <td>string</td>
                            <td>The super-class the beer rolls up to: <var>ale</var> or <var>lager</var>, or <var>null</var> for families that do not roll up to one (wheat, sour, cider, mead, etc.).</td>
                        </tr>
                        <tr>
                            <td><var>beverage_type</var></td>
                            <td>string</td>
                            <td>One of <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>. Derived from the resolved style; never trusted from client input.</td>
                        </tr>
                        <tr>
                            <td><var>style_confidence</var></td>
                            <td>string</td>
                            <td>How <var>style_id</var> was arrived at: <var>confident</var> (exact match), <var>override</var> (chosen by a person despite a different label), <var>family</var> (filed at a family or class, no specific style), or <var>catch-all</var>. May be <var>null</var> on older records.</td>
                        </tr>
                        <tr>
                            <td><var>description</var></td>
                            <td>string</td>
                            <td>A description of the beer. This field may contain a basic description, may contain tasting notes and/or brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank" rel="noopener">markdown</a> or new line characters.</td>
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
    "style": "West Coast IPA",
    "style_id": "west-coast-ipa",
    "parent": "ipa",
    "class": "ale",
    "beverage_type": "beer",
    "style_confidence": "confident",
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
        "short_description": "Award winning brewery built in San Diego, California. Dedicated to the craft.",
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

                <div class="alert alert-info" role="alert">
                    <strong>Styles are resolved to a canonical value.</strong> Send the brewer&#8217;s wording as <var>style</var> and the API resolves it to a canonical <a href="#styles">style</a>, family, or class &#8212; for example <code>"West Coast IPA"</code> &#8594; <var>west-coast-ipa</var>, <code>"IPA"</code> &#8594; the <var>ipa</var> family, <code>"Lager"</code> &#8594; the <var>lager</var> class. Your exact wording is preserved in the returned <var>style</var>. You may instead (or additionally) pass <var>style_id</var>, <var>parent</var>, or <var>class</var> to file at a specific tier; the most specific wins. If nothing resolves and no tier is given, the request returns <var>400</var> with a list of valid choices &#8212; pick a closer match or a catch-all (e.g. <code>specialty-beer</code>).</div>

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
                            <td>The brewer&#8217;s style label, resolved to a canonical style/family/class (see note above). Required unless you supply <var>style_id</var>, <var>parent</var>, or <var>class</var>.</td>
                        </tr>
                        <tr>
                            <td><var>style_id</var><br><small class="text-muted">(optional)</small></td>
                            <td>string</td>
                            <td>File at a specific canonical <a href="#styles">style</a> (e.g. <var>american-ipa</var>). Takes precedence over <var>style</var>.</td>
                        </tr>
                        <tr>
                            <td><var>parent</var><br><small class="text-muted">(optional)</small></td>
                            <td>string</td>
                            <td>File at a family (e.g. <var>ipa</var>) without choosing a specific style.</td>
                        </tr>
                        <tr>
                            <td><var>class</var><br><small class="text-muted">(optional)</small></td>
                            <td>string</td>
                            <td>File at a super-class: <var>ale</var> or <var>lager</var>.</td>
                        </tr>
                        <tr>
                            <td><var>style_confidence</var><br><small class="text-muted">(optional)</small></td>
                            <td>string</td>
                            <td>How the style was resolved, for provenance: <var>confident</var>, <var>override</var>, <var>family</var>, or <var>catch-all</var>. Normally set by the guided style picker; if omitted, the API derives it from the resolved tier.</td>
                        </tr>
                        <tr>
                            <td><var>description<br><small class="text-muted">(optional)</small></var></td>
                            <td>string</td>
                            <td>A description of the beer. This may be a basic description, or it can be detailed, containing tasting notes and brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank" rel="noopener">markdown</a> and new line characters.</td>
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
  -d '{"brewer_id":"e7fa4e64-a39e-fd06-f82a-37de2a7dfbda","name":"Schooner Wet Hop","style":"West Coast IPA","description":"Wet Hops (fresh hops) are hops that are picked off the vine and used in the brewing process before they are dried and packaged like normal.","abv":5.5,"ibu":25}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-update">Update a Beer (PUT)</h3>

<p>To replace a beer&#8217;s data, send a <strong>PUT</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the beer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/beer/{beer_id}</pre>

<div class="alert alert-info" role="alert"><strong>Styles are resolved to a canonical value</strong> &#8212; see <a href="#styles">Styles</a>. Send the brewer&#8217;s wording as <var>style</var>, or file at a tier with <var>style_id</var> / <var>parent</var> / <var>class</var>. Unresolvable input with no tier returns <var>400</var>.</div>

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
            <td>The brewer&#8217;s style label, resolved to a canonical style/family/class. Required unless you supply <var>style_id</var>, <var>parent</var>, or <var>class</var>.</td>
        </tr>
        <tr>
            <td><var>style_id</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>File at a specific canonical <a href="#styles">style</a> (e.g. <var>american-ipa</var>). Takes precedence over <var>style</var>.</td>
        </tr>
        <tr>
            <td><var>parent</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>File at a family (e.g. <var>ipa</var>) without choosing a specific style.</td>
        </tr>
        <tr>
            <td><var>class</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>File at a super-class: <var>ale</var> or <var>lager</var>.</td>
        </tr>
        <tr>
            <td><var>style_confidence</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>How the style was resolved, for provenance: <var>confident</var>, <var>override</var>, <var>family</var>, or <var>catch-all</var>. Normally set by the guided style picker; if omitted, the API derives it from the resolved tier.</td>
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
  -d '{"brewer_id":"e7fa4e64-a39e-fd06-f82a-37de2a7dfbda","name":"Schooner Wet Hop","style":"West Coast IPA","abv":5.5,"description":"Wet Hops (fresh hops) are hops that are picked off the vine and used in the brewing process before they are dried and packaged like normal.","ibu":25}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-patch">Update a Beer (PATCH)</h3>

<p>To partially update a beer, send a <strong>PATCH</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PATCH https://api.catalog.beer/beer/{beer_id}</pre>

<div class="alert alert-info" role="alert">Changing the style? Send any of <var>style</var> (label), <var>style_id</var>, <var>parent</var>, or <var>class</var> &#8212; the beer is re-resolved and its tier fields updated together. See <a href="#styles">Styles</a>.</div>

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
            <td>The brewer&#8217;s style label, re-resolved to a canonical style/family/class.</td>
        </tr>
        <tr>
            <td><var>style_id</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>File at a specific canonical <a href="#styles">style</a> (e.g. <var>american-ipa</var>).</td>
        </tr>
        <tr>
            <td><var>parent</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>File at a family (e.g. <var>ipa</var>).</td>
        </tr>
        <tr>
            <td><var>class</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>File at a super-class: <var>ale</var> or <var>lager</var>.</td>
        </tr>
        <tr>
            <td><var>style_confidence</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>How the style was resolved, for provenance: <var>confident</var>, <var>override</var>, <var>family</var>, or <var>catch-all</var>. Normally set by the guided style picker; if omitted, the API derives it from the resolved tier.</td>
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
                            <td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
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
  'https://api.catalog.beer/beer/search?q=ipa&amp;count=2' \
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
      "style": "West Coast IPA",
      "style_id": "west-coast-ipa",
      "parent": "ipa",
      "class": "ale",
      "beverage_type": "beer",
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

<h2 id="styles">Styles</h2>

<p>Every beer has a <strong>style</strong> &#8212; the kind of beer it is, like an IPA, a stout, or a lager. Catalog.beer records a beer&#8217;s style in two parts, so we get the best of both worlds: the brewer&#8217;s own words, and a tidy category we can sort and count by.</p>

<p><strong>Part 1: Call it whatever you like.</strong> When you add or edit a beer, the <var>beer.style</var> field is plain text &#8212; type the name exactly as the brewer uses it. Want to call your beer a &#8220;Súper Lager&#8221; or a &#8220;Hazy Double&#8221;? Go for it. We keep that label exactly as you wrote it and show it to everyone, untouched.</p>

<p><strong>Part 2: Tell us which category it fits.</strong> A name like &#8220;Hazy Double&#8221; reads well to a person, but a computer can&#8217;t tell that it&#8217;s a kind of IPA. So we also match each beer to one entry on our standard list of styles &#8212; its <em>canonical style</em>. That&#8217;s what lets the catalog answer questions like &#8220;show me every IPA&#8221; or &#8220;how many stouts are there?&#8221; Our standard list is drawn from the <a href='https://www.brewersassociation.org/edu/brewers-association-beer-style-guidelines/'>Brewers Association (BA) guidelines</a> and the <a href='https://www.bjcp.org/bjcp-style-guidelines/'>Beer Judge Certification Program (BJCP) guidelines</a>.</p>

<p>You don&#8217;t have to get the category exactly right on your own. When you send just a label, the API tries to recognize it and match it for you &#8212; it knows, for example, that &#8220;NEIPA,&#8221; &#8220;New England IPA,&#8221; and &#8220;Juicy IPA&#8221; all point to the same style. If it recognizes your label, you&#8217;re done. If it doesn&#8217;t, you can name the category yourself.</p>

<p>The standard list is organized into three levels, from the broadest bucket to the most specific:</p>

<ul>
    <li><strong>Class</strong> &#8212; the broadest grouping: <var>ale</var> or <var>lager</var>. (A few kinds of beer, like wheat beers and sours, don&#8217;t fall neatly into either one. That&#8217;s fine &#8212; they simply don&#8217;t have a class.)</li>
    <li><strong>Family</strong> &#8212; a middle grouping of related styles, such as <var>ipa</var>, <var>stout</var>, or <var>pilsner</var>. (In the data, this field is named <var>parent</var>.)</li>
    <li><strong>Style</strong> &#8212; one specific entry, such as <var>american-ipa</var> or <var>west-coast-ipa</var>.</li>
</ul>

<p>File a beer at whichever level matches how much you actually know. Know it&#8217;s a West Coast IPA? File it as that exact style. Only know it&#8217;s &#8220;some kind of IPA&#8221;? File it at the IPA family. All you know is that it&#8217;s an ale? File it as the ale class. Whichever level you pick, the API fills in the broader ones for you &#8212; tell it &#8220;West Coast IPA&#8221; and it knows that belongs to the IPA family and the ale class. It never invents detail you didn&#8217;t give it.</p>

<p>So in a request, you have two ways to set the category:</p>

<ul>
    <li>Send just <var>beer.style</var> (the brewer&#8217;s wording) and let the API match it for you.</li>
    <li>Or name the category directly with <var>style_id</var> (a specific style), <var>parent</var> (a family), or <var>class</var>. This is handy when you already know exactly where the beer belongs, or when the API couldn&#8217;t recognize your label. If you send more than one, the most specific one wins.</li>
</ul>

<p>If the API can&#8217;t recognize your label and you haven&#8217;t named a category, it won&#8217;t guess &#8212; it returns an error and points you to the list, where there&#8217;s always a catch-all (like <em>Specialty</em>) so an unusual beer that fits nowhere else still has a home.</p>

<p>One more field comes along automatically: <var>beverage_type</var> &#8212; <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>. You never set this yourself; it&#8217;s taken from whatever style the beer ends up matching.</p>

<p>The <code>/style</code> endpoints below are read-only. Use them to browse the full standard list &#8212; for example, to build your own style picker or to check a label before you submit it.</p>

<h3 id="style-object">The Style Object</h3>

<p>The <code>/style</code> endpoints return style objects. <a href="#style-list">List Styles</a> returns a compact form of each; <a href="#style-detail">Retrieve a Style</a> returns the full object, adding <var>parent_name</var>, <var>source</var>, and <var>specs</var>.</p>

<table class="table">
    <thead>
        <tr>
            <th scope="col">Field</th>
            <th scope="col">Type</th>
            <th scope="col">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><var>id</var></td><td>string</td><td>The style&#8217;s slug (e.g. <var>american-ipa</var>) &#8212; the value used as <var>style_id</var> on a beer.</td></tr>
        <tr><td><var>object</var></td><td>string</td><td>Always <var>style</var>.</td></tr>
        <tr><td><var>name</var></td><td>string</td><td>The canonical style name (e.g. &#8220;American-Style India Pale Ale&#8221;).</td></tr>
        <tr><td><var>beverage_type</var></td><td>string</td><td>One of <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>.</td></tr>
        <tr><td><var>parent</var></td><td>string</td><td>The slug of the <a href="#style-parents">family</a> this style belongs to (e.g. <var>ipa</var>) &#8212; the canonical family tier you can file a beer at.</td></tr>
        <tr><td><var>parent_name</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The display name of the parent family (e.g. &#8220;India Pale Ale&#8221;).</td></tr>
        <tr><td><var>source</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The primary guideline the style is drawn from: <var>BA-2026</var>, <var>BJCP-2021</var>, <var>OCB-2012</var>, or <var>NABA-2024</var>.</td></tr>
        <tr><td><var>catch_all</var></td><td>Boolean</td><td><var>true</var> for non-standard &#8220;catch-all&#8221; styles (e.g. <var>specialty-beer</var>) used when nothing more specific fits.</td></tr>
        <tr><td><var>aliases</var></td><td>array</td><td>Other names and spellings that resolve to this style, excluding the canonical <var>name</var>.</td></tr>
        <tr><td><var>specs</var><br><small class="text-muted">(detail only)</small></td><td>object</td><td>The style&#8217;s guideline ranges &#8212; <var>abv</var>, <var>ibu</var>, <var>srm</var>, <var>og</var>, and <var>fg</var>. See <a href="#style-specs">The Specs Object</a>.</td></tr>
    </tbody>
</table>

<p><a href="#top">^ Return to top</a></p>

<!----- STYLES: SPECS OBJECT ----->

<h3 id="style-specs">The Specs Object</h3>

<p>A <a href="#style-object">style</a> object&#8217;s <var>specs</var> field (detail only) holds the style&#8217;s guideline ranges, drawn from the same <var>source</var> as the style. Each field is a <code>{ "min": &#8230;, "max": &#8230; }</code> object, or <var>null</var> when the guideline doesn&#8217;t specify that measurement. An individual <var>min</var> or <var>max</var> may also be <var>null</var> when the guideline gives only one bound.</p>

<table class="table">
    <thead>
        <tr>
            <th scope="col">Field</th>
            <th scope="col">Type</th>
            <th scope="col">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><var>abv</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Alcohol_by_volume" target="_blank" rel="noopener">Alcohol by Volume</a> range, as a percentage (e.g. <code>6.3</code> means 6.3%).<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
        <tr><td><var>ibu</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Beer_measurement#Bitterness" target="_blank" rel="noopener">International Bitterness Units</a> range &#8212; the measure of hop bitterness.<br><span class="text-muted"><var>min</var> and <var>max</var> are integers.</span></td></tr>
        <tr><td><var>srm</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Standard_Reference_Method" target="_blank" rel="noopener">Standard Reference Method</a> range &#8212; beer color, from pale straw (low) to black (high).<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
        <tr><td><var>og</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Gravity_(alcoholic_beverage)" target="_blank" rel="noopener">Original Gravity</a> range &#8212; the wort&#8217;s specific gravity before fermentation.<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
        <tr><td><var>fg</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Gravity_(alcoholic_beverage)" target="_blank" rel="noopener">Final Gravity</a> range &#8212; the beer&#8217;s specific gravity after fermentation.<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
    </tbody>
</table>

<h4>Sample</h4>
<pre class="api-code">
"specs": {
    "abv": { "min": 6.3, "max": 7.5 },
    "ibu": { "min": 50, "max": 70 },
    "srm": { "min": 4, "max": 12 },
    "og":  { "min": 1.06, "max": 1.07 },
    "fg":  { "min": 1.01, "max": 1.016 }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-list">List Styles</h3>

<p>Returns every canonical style. The <var>version</var> field reflects the seeded vocabulary version &#8212; watch it to know when to refresh a cached copy.</p>

<pre class="api-code">GET https://api.catalog.beer/style</pre>

<h4>Sample Response</h4>
<pre class="api-code">
{
  "object": "list",
  "url": "/style",
  "version": "2.2.2",
  "has_more": false,
  "data": [
    {
      "id": "american-ipa",
      "object": "style",
      "name": "American-Style India Pale Ale",
      "beverage_type": "beer",
      "parent": "ipa",
      "catch_all": false,
      "aliases": ["American IPA", "American India Pale Ale"]
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-detail">Retrieve a Style</h3>

<p>Returns one style with its full detail, including style guideline specs (ABV, IBU, SRM, OG, FG). The <var>{style_id}</var> is a slug, not a UUID.</p>

<pre class="api-code">GET https://api.catalog.beer/style/{style_id}</pre>

<h4>Sample Response</h4>
<pre class="api-code">
{
  "id": "american-ipa",
  "object": "style",
  "name": "American-Style India Pale Ale",
  "beverage_type": "beer",
  "parent": "ipa",
  "parent_name": "India Pale Ale",
  "source": "BA-2026",
  "catch_all": false,
  "aliases": ["American IPA", "American India Pale Ale"],
  "specs": {
    "abv": { "min": 6.3, "max": 7.5 },
    "ibu": { "min": 50, "max": 70 },
    "srm": { "min": 4, "max": 12 },
    "og":  { "min": 1.06, "max": 1.07 },
    "fg":  { "min": 1.01, "max": 1.016 }
  }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-parents">List Families</h3>

<p>Returns the family tier (the <var>parent</var> groupings), each with its <var>class</var> rollup and the aliases that resolve to it.</p>

<pre class="api-code">GET https://api.catalog.beer/style/parent</pre>

<h4>Sample Response</h4>
<pre class="api-code">
{
  "object": "list",
  "url": "/style/parent",
  "has_more": false,
  "data": [
    {
      "slug": "ipa",
      "object": "style_parent",
      "name": "India Pale Ale",
      "beverage_type": "beer",
      "class": "ale",
      "description": "The hop showcase of brewing...",
      "sort_order": 2,
      "aliases": ["IPA", "India Pale Ale", "IPAs"]
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-classes">List Classes</h3>

<p>Returns the super-class tier &#8212; currently <var>ale</var> and <var>lager</var> &#8212; with the aliases that resolve to each.</p>

<pre class="api-code">GET https://api.catalog.beer/style/class</pre>

<h4>Sample Response</h4>
<pre class="api-code">
{
  "object": "list",
  "url": "/style/class",
  "has_more": false,
  "data": [
    {
      "slug": "ale",
      "object": "style_class",
      "name": "Ale",
      "beverage_type": "beer",
      "sort_order": 1,
      "aliases": ["Ale", "Ales"]
    },
    {
      "slug": "lager",
      "object": "style_class",
      "name": "Lager",
      "beverage_type": "beer",
      "sort_order": 2,
      "aliases": ["Lager", "Lagers"]
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
            <td>The ISO 3166&#8211;1 Alpha&#8211;2 Code for the country in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard.</td>
        </tr>
        <tr>
            <td><var>country_short_name</var></td>
            <td>string</td>
            <td>The ISO 3166&#8211;1 short name for the country, in title case. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard.</td>
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
            <td>The ISO 3166&#8211;1 Alpha&#8211;2 Code for the country in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard.</td>
        </tr>
    </tbody>
</table>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/location \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
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
            <td>The ISO 3166&#8211;2 Code for the subdivision in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard. (e.g. &#8220;US-CA&#8221; for California)</td>
        </tr>
        <tr>
            <td><var>zip5</var><br><small class="text-muted">(optional) Either the <var>city</var> and <var>sub_code</var> must be provided OR the <var>zip5</var> must be provided.</small></td>
            <td>string</td>
            <td>The traditional 5-digit ZIP Code for the location.</td>
        </tr>
        <tr>
            <td><var>zip4</var><br><small class="text-muted">(optional)</small></td>
            <td>string</td>
            <td>The additional ZIP+4 Code used by the US Postal Service. More on the <a href="https://faq.usps.com/s/article/ZIP-Code-The-Basics" target="_blank" rel="noopener">ZIP+4 Code</a>.</td>
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
  -H 'authorization: Basic {secret_key}' \
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
            <td>The ISO 3166&#8211;2 Code for the subdivision in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard. (e.g. &#8220;US-CA&#8221; for California)</td>
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
            <td>An opaque string value that indicates where the results should start from. This value is returned as <var>next_cursor</var> after an initial query to the endpoint.</td>
        </tr>
        <tr>
            <td><var>count</var><br><small class="text-muted">(optional)</small></td>
            <td>integer</td>
            <td>The number of results you would like returned from your request. The default value is 100.</td>
        </tr>
    </tbody>
</table>

<p>A sample request with query parameters.</p>

<pre class="api-code">GET https://api.catalog.beer/location/nearby?latitude={latitude}&amp;longitude={longitude}</pre>

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
  'https://api.catalog.beer/location/nearby?latitude=32.748482&amp;longitude=-117.130094&amp;search_radius=10&amp;count=1' \
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
  'https://api.catalog.beer/location/zip?zip_code=92104&amp;search_radius=10&amp;count=1' \
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
  'https://api.catalog.beer/location/city?city=San%20Diego&amp;state=CA&amp;search_radius=10&amp;count=1' \
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

<!---------- USAGE ---------->

<h2 id="usage">Usage</h2>

<p>The usage endpoints allow you to check your API usage for the current billing period. Each API key has a monthly request limit (default: 1,000 requests). Requests to the <code>/usage</code> endpoints are not counted against your limit.</p>

<!----- USAGE: OBJECT ----->

<h3 id="usage-object">The Usage Object</h3>

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
            <td>The name of the object. In this case: &#8220;usage&#8221;.</td>
        </tr>
        <tr>
            <td><var>api_key</var></td>
            <td>string</td>
            <td>The API key associated with this usage record.</td>
        </tr>
        <tr>
            <td><var>year</var></td>
            <td>integer</td>
            <td>The year of the current billing period (e.g. 2026).</td>
        </tr>
        <tr>
            <td><var>month</var></td>
            <td>integer</td>
            <td>The month of the current billing period (1&#8211;12).</td>
        </tr>
        <tr>
            <td><var>count</var></td>
            <td>integer</td>
            <td>The number of API requests made during the current billing period.</td>
        </tr>
        <tr>
            <td><var>request_limit</var></td>
            <td>integer</td>
            <td>The maximum number of requests allowed per month for this API key.</td>
        </tr>
        <tr>
            <td><var>request_buffer</var></td>
            <td>integer</td>
            <td>A grace zone beyond the request limit. Requests will not be blocked until <var>count</var> exceeds <var>request_limit</var> + <var>request_buffer</var>.</td>
        </tr>
        <tr>
            <td><var>resets_on</var></td>
            <td>string</td>
            <td>The date your usage count resets, formatted as <var>YYYY-MM-DD</var> (always the first of the next month).</td>
        </tr>
        <tr>
            <td><var>last_updated</var></td>
            <td>integer</td>
            <td>Unix timestamp of the last time the usage counter was updated. Returns <var>0</var> if no requests have been made this month.</td>
        </tr>
    </tbody>
</table>

<p><a href="#top">^ Return to top</a></p>

<!----- USAGE: MY USAGE ----->

<h3 id="usage-my-usage">Get My Usage</h3>

<p>To retrieve your current API usage and limits, send a <strong>GET</strong> request to the <code>/usage/my-usage</code> endpoint. This returns your usage for the current billing period. No special permissions are required&#8212;the endpoint returns data for the authenticated API key.</p>

<pre class="api-code">GET https://api.catalog.beer/usage/my-usage</pre>

<p>A <a href="#usage-object">usage object</a> will be returned for successful requests.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/usage/my-usage \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}'
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "usage",
  "api_key": "cadcbe6f-a80d-4e33-9f20-b53c2ed83845",
  "year": 2026,
  "month": 3,
  "count": 142,
  "request_limit": 1000,
  "request_buffer": 50,
  "resets_on": "2026-04-01",
  "last_updated": 1741723456
}
</pre>

<h4>Rate Limiting</h4>

<p>When your usage exceeds your <var>request_limit</var> + <var>request_buffer</var>, the API will return a <var>429 Too Many Requests</var> response for all non-usage endpoints. Your count resets on the first of each month. To request a higher limit, <a href="/contact">contact us</a>.</p>

<p><a href="#top">^ Return to top</a></p>

<!---------- US ADDRESSES ---------->

<h2 id="us-address">US Addresses</h2>

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
            <td>The ISO 3166&#8211;2 Code for the subdivision in which the location is located. Reference the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard. (e.g. US-CA)</td>
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
            <td>The additional ZIP+4 Code used by the US Postal Service. More on the <a href="https://faq.usps.com/s/article/ZIP-Code-The-Basics" target="_blank" rel="noopener">ZIP+4 Code</a>.</td>
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
