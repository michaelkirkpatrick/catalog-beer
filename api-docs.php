<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('API Documentation');
$htmlHead->addStylesheet('/assets/css/api-docs.css');
echo $htmlHead->html;
?>
<body data-bs-spy="scroll" data-bs-target="#ad-toc" data-bs-root-margin="0px 0px -55%">
    <?php
    // Navbar
    echo $nav->navbar('');
    ?>
    <div class="cb-page">
        <div class="ad-layout">
            <button class="ad-toc-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#ad-toc" aria-expanded="false" aria-controls="ad-toc">On this page</button>
            <nav class="ad-toc collapse d-lg-block" id="ad-toc" aria-label="API reference sections">
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
                    <a class="list-group-item list-group-item-action" href="#brewer-permissions">&gt; Your Permissions for a Brewer</a>
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
                    <a class="list-group-item list-group-item-action" href="#style-confidence">&gt; Style Confidence</a>
                    <a class="list-group-item list-group-item-action" href="#style-object">&gt; The Style Object</a>
                    <a class="list-group-item list-group-item-action" href="#style-list">&gt; List Styles</a>
                    <a class="list-group-item list-group-item-action" href="#style-detail">&gt; Retrieve a Style</a>
                    <a class="list-group-item list-group-item-action" href="#style-search">&gt; Search Styles</a>
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
                    <a class="list-group-item list-group-item-action" href="#location-list-all">&gt; List all Locations</a>
                    <a class="list-group-item list-group-item-action" href="#nearby-locations">&gt; Find Nearby Locations</a>
                    <a class="list-group-item list-group-item-action" href="#location-zip">&gt; Find Locations by ZIP Code</a>
                    <a class="list-group-item list-group-item-action" href="#location-city">&gt; Find Locations by City</a>
                    <a class="list-group-item list-group-item-action" href="#usage"><strong>Usage</strong></a>
                    <a class="list-group-item list-group-item-action" href="#usage-object">&gt; The Usage Object</a>
                    <a class="list-group-item list-group-item-action" href="#usage-my-usage">&gt; Get My Usage</a>
                    <a class="list-group-item list-group-item-action" href="#billing"><strong>Billing</strong></a>
                    <a class="list-group-item list-group-item-action" href="#billing-object">&gt; The Billing Object</a>
                    <a class="list-group-item list-group-item-action" href="#billing-retrieve">&gt; Get Billing Status</a>
                    <a class="list-group-item list-group-item-action" href="#billing-checkout-session">&gt; Create a Checkout Session</a>
                    <a class="list-group-item list-group-item-action" href="#billing-portal-session">&gt; Create a Portal Session</a>
                    <a class="list-group-item list-group-item-action" href="#billing-update">&gt; Update Spend Cap</a>
                    <a class="list-group-item list-group-item-action" href="#billing-disable">&gt; Disable Billing</a>
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
            </nav>
            <div class="ad-content">
                <h1 id="top">API Reference</h1>
                <p>Last Updated: August 3, 2026</p>

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

                <div class="table-responsive">
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
                            <tr>
                                <td><var>suggestions</var><br><small class="text-muted">(optional)</small></td>
                                <td>array</td>
                                <td>Keyed by attribute name, like <var>valid_state</var> and <var>valid_msg</var>. Where we can work out what an invalid value probably meant, this carries the values that would fix it, so you can retry without a second lookup. Present only when we have candidates &mdash; always treat it as optional. Currently returned for <var>style</var> on <a href="#beer">/beer</a> writes; see <a href="#styles">Styles</a>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

                <div class="table-responsive">
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
                </div>

                <p>If you send a request using an HTTP method that is not supported by the endpoint, the API will return a <var>405 Method Not Allowed</var> response. The <code>Allow</code> header on the response will list the methods that the endpoint does support.</p>

                <p><a href="#top">^ Return to top</a></p>

                <h2 id="brewer">Brewer</h2>
                <hr>

                <p>The brewer object is the central piece of the beer data puzzle. Brewers can have beers and locations associated with them. And in order to add a beer or location to the database, there must be a brewer to associate them with.</p>

                <h3 id="brewer-object">The Brewer Object</h3>

                <p>Whether you&#8217;re retrieving information on a specific brewer, adding a new brewer, or updating an existing brewer, successful requests will return the brewer object in JSON format. That object has the following parameters.</p>

                <div class="table-responsive">
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
                </div>

                <h4>Sample</h4>

<pre class="api-code">
{
  "id": "ce7d83f5-0c3d-42f4-9162-ca97019e89d1",
  "object": "brewer",
  "name": "HopSaint Brewing Company",
  "description": "HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn't be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That's at the heart of HopSaint - a community that fosters lasting relationships &amp; enriches our hometown through the production of honest, real beer. A community built on craft beer.",
  "short_description": "A brewery in Torrance, CA.",
  "url": "https://www.hopsaint.com/",
  "cb_verified": true,
  "brewer_verified": false,
  "last_modified": 1783639445
}
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-create">Add a Brewer</h3>

                <p>To add a brewer, send a <strong>POST</strong> request to the <code>/brewer</code> endpoint with the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#brewer-object">brewer object</a>.</p>

                <pre class="api-code">POST https://api.catalog.beer/brewer</pre>

                <div class="table-responsive">
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
                </div>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/brewer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"HopSaint Brewing Company","description":"HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn\u0027t be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That\u0027s at the heart of HopSaint - a community that fosters lasting relationships &amp; enriches our hometown through the production of honest, real beer. A community built on craft beer.","short_description":"A brewery in Torrance, CA.","url":"https://www.hopsaint.com/"}'
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-update">Update a Brewer (PUT)</h3>

                <p>To replace a brewer&#8217;s data, send a <strong>PUT</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the brewer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#brewer-object">brewer object</a>.</p>

                <pre class="api-code">PUT https://api.catalog.beer/brewer/{brewer_id}</pre>

                <div class="table-responsive">
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
                </div>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/brewer/ce7d83f5-0c3d-42f4-9162-ca97019e89d1 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"HopSaint Brewing Company","description":"HopSaint was born after one too many late nights navigating a crowded bar just to have a great beer unceremoniously poured into a dirty pint glass. We believe fresh draft beer shouldn\u0027t be confined to the pub. You should choose when, where, how, and with whom you enjoy a fresh, crafted beer. That\u0027s at the heart of HopSaint - a community that fosters lasting relationships &amp; enriches our hometown through the production of honest, real beer. A community built on craft beer.","short_description":"A brewery in Torrance, CA.","url":"https://www.hopsaint.com/"}'
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-patch">Update a Brewer (PATCH)</h3>

                <p>To partially update a brewer, send a <strong>PATCH</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#brewer-object">brewer object</a>.</p>

                <pre class="api-code">PATCH https://api.catalog.beer/brewer/{brewer_id}</pre>

                <div class="table-responsive">
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
                </div>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/brewer/ce7d83f5-0c3d-42f4-9162-ca97019e89d1 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"short_description":"A brewery in Torrance, CA."}'
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-delete">Delete a Brewer</h3>

                <p>To delete a brewer, send a <strong>DELETE</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body. Deleting a brewer also permanently deletes all of its beers and locations. Deleting is limited to brewery staff &#8212; check <a href="#brewer-permissions">your permissions</a> before attempting it.</p>

                <pre class="api-code">DELETE https://api.catalog.beer/brewer/{brewer_id}</pre>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/brewer/ce7d83f5-0c3d-42f4-9162-ca97019e89d1 \
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
  https://api.catalog.beer/brewer/ce7d83f5-0c3d-42f4-9162-ca97019e89d1 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-list-all">List all Brewers</h3>

                <p>If you want a list of all the brewers in the database, send a <strong>GET</strong> request to the <code>/brewer</code> endpoint.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer</pre>

                <h4>Query Parameters</h4>
                <div class="table-responsive">
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
                </div>

                <p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer?count=5&amp;cursor=NQ%3D%3D</pre>

                <h4>Response</h4>

                <p>This request returns a list object with the following parameters.</p>

                <div class="table-responsive">
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
                </div>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/brewer?count=2' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

                <h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/brewer",
  "has_more": true,
  "next_cursor": "Mg==",
  "data": [
    {
      "id": "19555c52-ade8-43fe-a7a8-5888af0842e9",
      "name": "'t Hofbrouwerijke",
      "last_modified": 1588448205
    },
    {
      "id": "d150fff4-ff71-4171-bd36-fb6dc8a83377",
      "name": "(512) Brewing Company",
      "last_modified": 1588448205
    }
  ]
}
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-count">Number of Brewers</h3>

                <p>You can retrieve the total number of brewers that are in the database by sending a <strong>GET</strong> request the endpoint <code>/brewer/count</code>. A JSON object with the following parameters will be returned.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer/count</pre>

                <div class="table-responsive">
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
                </div>

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
  "value": 6760
}
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-search">Search Brewers</h3>

                <p>Search for brewers by name or description using full-text search. To search, send a <strong>GET</strong> request to the <code>/brewer/search</code> endpoint with a <var>q</var> query parameter.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer/search?q={query}</pre>

                <h4>Query Parameters</h4>
                <div class="table-responsive">
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
                </div>

                <p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer/search?q=stone+brewing&amp;count=5</pre>

                <h4>Response</h4>

                <p>This request returns a list object with the following parameters. Results are sorted by relevance to the search query.</p>

                <div class="table-responsive">
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
                </div>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/brewer/search?q=stone&amp;count=1' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

                <h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/brewer/search",
  "query": "stone",
  "has_more": true,
  "next_cursor": "MQ==",
  "data": [
    {
      "id": "a751e1bb-e738-4a3a-906b-6a707e082908",
      "object": "brewer",
      "name": "Stone Coast Brewing",
      "description": null,
      "short_description": null,
      "url": "http://www.stonecoast.com/",
      "cb_verified": false,
      "brewer_verified": false,
      "last_modified": 1588448205
    }
  ]
}
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-beers">List all Beers made by a Brewer</h3>

                <p>If you would like a list of all the beers made by a brewer, send a request to the <code>/brewer/{brewer_id}/beer</code> endpoint. </p>

                <p>This request returns a list object with the following parameters.</p>

                <div class="table-responsive">
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
                                <td>An array containing all the beers associated with this brewer in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, <var>style</var>, <var>style_id</var>, <var>parent</var>, <var>class</var>, <var>beverage_type</var>, <var>abv</var>, <var>cb_verified</var>, and <var>brewer_verified</var> described below.</td>
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
                            <tr>
                                <td><var>abv</var></td>
                                <td>number</td>
                                <td>The alcohol by volume (ABV) of the beer, expressed as a percentage.</td>
                            </tr>
                            <tr>
                                <td><var>cb_verified</var></td>
                                <td>Boolean</td>
                                <td>Whether the beer&#8217;s information has been verified by Catalog.beer against the brewery&#8217;s website.</td>
                            </tr>
                            <tr>
                                <td><var>brewer_verified</var></td>
                                <td>Boolean</td>
                                <td>Whether the beer&#8217;s information was provided by the brewery (first-party data).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4>Sample Request</h4>
<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/ab94abb7-a3e8-4cce-8945-4758cac66a53/beer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

                <h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/brewer/ab94abb7-a3e8-4cce-8945-4758cac66a53/beer",
  "has_more": false,
  "brewer": {
    "id": "ab94abb7-a3e8-4cce-8945-4758cac66a53",
    "object": "brewer",
    "name": "Ballast Point Brewing Company",
    "description": null,
    "short_description": null,
    "url": "https://www.ballastpoint.com/",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1541544607
  },
  "data": [
    {
      "id": "650f8973-7c09-4cbf-99cd-857e095d84b0",
      "name": "Abandon Ship With Chipotle",
      "style": "Smoke Beer (Lager or Ale)",
      "style_id": "smoke-beer",
      "parent": "smoked-beer",
      "class": null,
      "beverage_type": "beer",
      "abv": 7,
      "cb_verified": false,
      "brewer_verified": false
    },
    {
      "id": "36ebef31-f05b-4c78-b7c1-731ed136fe11",
      "name": "Ballast Point Belgian-Style Tripel",
      "style": "Belgian-Style Tripel",
      "style_id": "belgian-tripel",
      "parent": "belgian-strong-ale",
      "class": "ale",
      "beverage_type": "beer",
      "abv": 9.7,
      "cb_verified": false,
      "brewer_verified": false
    },
    {
      "id": "e778b401-0c2b-4bc0-8b53-ccb59860c1bf",
      "name": "Ballast Point Red Velvet Cake",
      "style": "Chocolate / Cocoa-Flavored Beer",
      "style_id": "chocolate-beer",
      "parent": "flavored-beer",
      "class": null,
      "beverage_type": "beer",
      "abv": 5.5,
      "cb_verified": false,
      "brewer_verified": false
    }
  ]
}
</pre>

                <p><small class="text-muted">Response truncated for documentation &#8212; this brewer has 81 beers in its <var>data</var> array.</small></p>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-locations">List all the Locations for a Brewer</h3>

                <p>If you would like to know all the locations associated with a brewer, send a request to the <code>/brewer/{brewer_id}/locations</code> endpoint.</p>

                <p>This request returns a list object with the following parameters.</p>

                <div class="table-responsive">
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
                                <td>An array containing all the locations associated with this brewer in the database sorted alphabetically by name. Each array object is a <a href="#location-object">location object</a> &#8212; including its <var>address</var> block, <var>latitude</var>, and <var>longitude</var> &#8212; without the nested <var>brewer</var> object (the list&#8217;s top-level <var>brewer</var> already carries it).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4>Sample Request</h4>
<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/ab94abb7-a3e8-4cce-8945-4758cac66a53/locations \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

                <h4>Sample Response</h4>
<pre class="api-code">
{
  "object": "list",
  "url": "/brewer/ab94abb7-a3e8-4cce-8945-4758cac66a53/locations",
  "has_more": false,
  "brewer": {
    "id": "ab94abb7-a3e8-4cce-8945-4758cac66a53",
    "object": "brewer",
    "name": "Ballast Point Brewing Company",
    "description": null,
    "short_description": null,
    "url": "https://www.ballastpoint.com/",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1541544607
  },
  "data": [
    {
      "id": "76b558cd-a5f7-4fbe-820a-7a9dc9a924fe",
      "object": "location",
      "name": "Chicago",
      "url": null,
      "country_code": "US",
      "country_short_name": "United States of America",
      "latitude": 41.9048372,
      "longitude": -87.6483162,
      "cb_verified": false,
      "brewer_verified": false,
      "last_modified": 1541544607,
      "address": {
        "address1": null,
        "address2": "1462 N Milwaukee Ave",
        "city": "Chicago",
        "sub_code": "US-IL",
        "state_short": "IL",
        "state_long": "Illinois",
        "zip5": "60622",
        "zip4": null,
        "telephone": null
      }
    },
    {
      "id": "4ece62c7-38c2-41a2-a4db-02048e46fe55",
      "object": "location",
      "name": "Home Brew Mart",
      "url": "https://ballastpoint.com/location/home-brew-mart/",
      "country_code": "US",
      "country_short_name": "United States of America",
      "latitude": 32.7477731,
      "longitude": -117.1955504,
      "cb_verified": false,
      "brewer_verified": false,
      "last_modified": 1541544607,
      "address": {
        "address1": null,
        "address2": "5401 Linda Vista Rd",
        "city": "San Diego",
        "sub_code": "US-CA",
        "state_short": "CA",
        "state_long": "California",
        "zip5": "92110",
        "zip4": null,
        "telephone": 6192952337
      }
    }
  ]
}
</pre>

                <p><small class="text-muted">Response truncated for documentation &#8212; this brewer has 9 locations in its <var>data</var> array.</small></p>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-permissions">Your Permissions for a Brewer</h3>

                <p>Before attempting to update or delete a brewer &#8212; or any of its beers or locations &#8212; you can ask what your API key is allowed to do by sending a <strong>GET</strong> request to the <code>/brewer/{brewer_id}/permissions</code> endpoint. This is the polite alternative to attempting a write and handling the <var>403</var>: agents and applications should preflight destructive or gated operations here.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer/{brewer_id}/permissions</pre>

                <p>The response is computed for the API key making the request, at the time of the request. Different keys receive different answers for the same brewer, and your answer can change (for example, if the brewer becomes verified, or your account is granted brewery-staff privileges) &#8212; so don&#8217;t cache it across keys or for long periods.</p>

                <div class="table-responsive">
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
                                <td>The name of the object. In this case: &#8220;permissions&#8221;.</td>
                            </tr>
                            <tr>
                                <td><var>brewer_id</var></td>
                                <td>string</td>
                                <td>The unique identifier of the brewer these permissions apply to.</td>
                            </tr>
                            <tr>
                                <td><var>role</var></td>
                                <td>string</td>
                                <td>Your relationship to this brewer. Possible values include <var>staff</var> (your account is associated with the brewery &#8212; e.g., your verified email address is at the brewery&#8217;s domain) and <var>general</var> (no association). Other values may be added over time; treat any value you don&#8217;t recognize as <var>general</var>.</td>
                            </tr>
                            <tr>
                                <td><var>edit</var></td>
                                <td>Boolean</td>
                                <td>Whether your key may <strong>PUT</strong>/<strong>PATCH</strong> this brewer. <var>staff</var> may always edit; <var>general</var> users may edit only while the brewer is neither <var>cb_verified</var> nor <var>brewer_verified</var>.</td>
                            </tr>
                            <tr>
                                <td><var>delete</var></td>
                                <td>Boolean</td>
                                <td>Whether your key may <strong>DELETE</strong> this brewer. Deleting is limited to <var>staff</var>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>The same rules extend to everything under the brewer. If your role is <var>staff</var>, you may edit and delete the brewer&#8217;s beers and locations as well. If your role is <var>general</var>, you may edit a beer or location only while that specific entity is unverified (check its own <var>cb_verified</var> / <var>brewer_verified</var> flags), and you may not delete anything. <var>edit</var> and <var>delete</var> in this response describe the brewer itself.</p>

                <h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/brewer/5b58ea77-62f1-e677-9d16-a4a25d68e37c/permissions \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

                <h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "permissions",
  "brewer_id": "5b58ea77-62f1-e677-9d16-a4a25d68e37c",
  "role": "general",
  "edit": true,
  "delete": false
}
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h2 id="beer">Beer</h2>
                <hr>

                <p>Beer is at the heart of it all. From an API perspective, beers are associated with brewers via a <var>brewer_id</var>.</p>

                <h3 id="beer-object">The Beer Object</h3>

                <p>When you add a new beer, are looking for information on a specific beer, or are updating a beer in the database, successful requests will return the beer object in JSON format. That object has the following parameters.</p>

                <div class="table-responsive">
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
                                <td><var>description</var></td>
                                <td>string</td>
                                <td>A description of the beer. This field may contain a basic description, may contain tasting notes and/or brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank" rel="noopener">markdown</a> or new line characters.</td>
                            </tr>
                            <tr>
                                <td><var>abv</var></td>
                                <td>float</td>
                                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Always present.</td>
                            </tr>
                            <tr>
                                <td><var>ibu</var></td>
                                <td>integer</td>
                                <td>The International Bitterness/Bittering Units (IBU) value of the beer, from <var>0</var> to <var>1000</var>. <var>null</var> when the bitterness has never been recorded &#8212; which is a different statement from <var>0</var>, meaning a beer with no measurable bitterness.</td>
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
                </div>

<h4>Sample</h4>

<pre class="api-code">
{
  "id": "6a7119c6-92a2-40d2-b87a-2e4529c8577a",
  "object": "beer",
  "name": "Sculpin IPA",
  "style": "American-Style India Pale Ale",
  "style_id": "american-ipa",
  "parent": "ipa",
  "class": "ale",
  "beverage_type": "beer",
  "description": "Our Sculpin IPA is a great example of what got us into brewing in the first place. After years of experimenting, we knew hopping an ale at five separate stages would produce something special. The result ended up being this gold-medal winning IPA, whose inspired use of hops creates hints of apricot, peach, mango and lemon flavors.",
  "abv": 7,
  "ibu": 70,
  "cb_verified": true,
  "brewer_verified": false,
  "last_modified": 1783642824,
  "brewer": {
    "id": "ab94abb7-a3e8-4cce-8945-4758cac66a53",
    "object": "brewer",
    "name": "Ballast Point Brewing Company",
    "description": null,
    "short_description": null,
    "url": "https://www.ballastpoint.com/",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1541544607
  }
}
</pre>
                                <p><a href="#top">^ Return to top</a></p>

                <h3 id="beer-create">Add a Beer</h3>

                <p>To add a beer to the database, send a <strong>POST</strong> request to the <code>/beer</code> endpoint with the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#beer-object">beer object</a>.</p>

                                <pre class="api-code">POST https://api.catalog.beer/beer</pre>

                <p>The <var>style</var> you submit is kept exactly as you wrote it and matched to the catalog&#8217;s standard list of styles &#8212; for example, &#8220;West Coast IPA&#8221; resolves to the <var>west-coast-ipa</var> style, &#8220;IPA&#8221; to the <var>ipa</var> family, and &#8220;Lager&#8221; to the <var>lager</var> class. To name the classification yourself, send <var>style_id</var>, <var>parent</var>, or <var>class</var>; if you send more than one, the most specific wins. If the label doesn&#8217;t match and no classification is given, the request returns a <var>400 Bad Request</var> error. See <a href="#styles">Styles</a>.</p>

                <div class="table-responsive">
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
                                <td>How sure you are of the classification, used to prioritise editorial review. Usually omit it &#8212; we derive it. Send it only to record <em>less</em> certainty than the request implies. See <a href="#style-confidence">Style Confidence</a>.</td>
                            </tr>
                            <tr>
                                <td><var>description<br><small class="text-muted">(optional)</small></var></td>
                                <td>string</td>
                                <td>A description of the beer. This may be a basic description, or it can be detailed, containing tasting notes and brewer&#8217;s notes. This field may contain <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank" rel="noopener">markdown</a> and new line characters.</td>
                            </tr>
                            <tr>
                                <td><var>abv</var></td>
                                <td>float</td>
                                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Required &#8212; send <var>0</var> for a non-alcoholic beer. Rounded to one decimal place.</td>
                            </tr>
                            <tr>
                                <td><var>ibu<br><small class="text-muted">(optional)</small></var></td>
                                <td>integer</td>
                                <td>The International Bitterness/Bittering Units (IBU) value of the beer, from <var>0</var> to <var>1000</var>. Whole numbers only. Omit it, or send <var>null</var>, when the bitterness is unknown; send <var>0</var> only to state that the beer has no measurable bitterness.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/beer \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id": "ab94abb7-a3e8-4cce-8945-4758cac66a53", "name": "Sculpin IPA", "style": "American-Style India Pale Ale", "description": "Our Sculpin IPA is a great example of what got us into brewing in the first place. After years of experimenting, we knew hopping an ale at five separate stages would produce something special. The result ended up being this gold-medal winning IPA, whose inspired use of hops creates hints of apricot, peach, mango and lemon flavors.", "abv": 7, "ibu": 70}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-update">Update a Beer (PUT)</h3>

<p>To replace a beer&#8217;s data, send a <strong>PUT</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the beer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/beer/{beer_id}</pre>

<p>The style fields work the same as when <a href="#beer-create">adding a beer</a>: send the label as <var>style</var>, or name the classification with <var>style_id</var>, <var>parent</var>, or <var>class</var>. If the label doesn&#8217;t match and no classification is given, the request returns a <var>400 Bad Request</var> error &#8212; unless the label is unchanged from the beer&#8217;s current <var>style</var>, in which case the update succeeds and the beer keeps its current classification. See <a href="#styles">Styles</a>.</p>

<div class="table-responsive">
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
                <td><var>abv</var></td>
                <td>float</td>
                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Required &#8212; send <var>0</var> for a non-alcoholic beer. Rounded to one decimal place.</td>
            </tr>
            <tr>
                <td><var>description</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>A description of the beer. Cleared if omitted.</td>
            </tr>
            <tr>
                <td><var>ibu</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>The International Bitterness/Bittering Units (IBU) value of the beer, from <var>0</var> to <var>1000</var>. Whole numbers only. Cleared if omitted. Send <var>0</var> only to state that the beer has no measurable bitterness &#8212; a cleared field means the bitterness is unknown.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/beer/6a7119c6-92a2-40d2-b87a-2e4529c8577a \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id": "ab94abb7-a3e8-4cce-8945-4758cac66a53", "name": "Sculpin IPA", "style": "American-Style India Pale Ale", "description": "Our Sculpin IPA is a great example of what got us into brewing in the first place. After years of experimenting, we knew hopping an ale at five separate stages would produce something special. The result ended up being this gold-medal winning IPA, whose inspired use of hops creates hints of apricot, peach, mango and lemon flavors.", "abv": 7, "ibu": 70}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-patch">Update a Beer (PATCH)</h3>

<p>To partially update a beer, send a <strong>PATCH</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PATCH https://api.catalog.beer/beer/{beer_id}</pre>

<p>To change a beer&#8217;s style, send any of <var>style</var>, <var>style_id</var>, <var>parent</var>, or <var>class</var>. The style is re-resolved and all of the beer&#8217;s classification fields are updated together. Resending the beer&#8217;s current <var>style</var> unchanged never fails &#8212; the beer keeps its current classification. See <a href="#styles">Styles</a>.</p>

<div class="table-responsive">
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
                <td><var>description</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>A description of the beer.</td>
            </tr>
            <tr>
                <td><var>abv</var><br><small class="text-muted">(optional)</small></td>
                <td>float</td>
                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Rounded to one decimal place. Omit the field to leave it unchanged; <var>null</var> is rejected, since every beer has an ABV on record.</td>
            </tr>
            <tr>
                <td><var>ibu</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>The International Bitterness/Bittering Units (IBU) value of the beer, from <var>0</var> to <var>1000</var>. Whole numbers only. Omit the field to leave it unchanged, send <var>null</var> to clear it back to unknown, or send <var>0</var> to state that the beer has no measurable bitterness.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/beer/6a7119c6-92a2-40d2-b87a-2e4529c8577a \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"abv":7,"ibu":70}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-delete">Delete a Beer</h3>

<p>To delete a beer, send a <strong>DELETE</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body. Deleting is limited to brewery staff &#8212; check <a href="#brewer-permissions">your permissions for the beer&#8217;s brewer</a> before attempting it.</p>

<pre class="api-code">DELETE https://api.catalog.beer/beer/{beer_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/beer/6a7119c6-92a2-40d2-b87a-2e4529c8577a \
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
  https://api.catalog.beer/beer/6a7119c6-92a2-40d2-b87a-2e4529c8577a \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-list-all">List all Beer</h3>

<p>Retrieves a list of all the beer in the database. To access this data, send a <strong>GET</strong> request to the <code>/beer</code> endpoint.</p>

<pre class="api-code">GET https://api.catalog.beer/beer</pre>

                <h4>Query Parameters</h4>
                <div class="table-responsive">
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
                </div>
                <p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except -_.</p>
                <pre class="api-code">GET https://api.catalog.beer/beer?count=5&amp;cursor=NQ%3D%3D</pre>

                <h4>Response</h4>
<p>This request returns a list object with the following parameters.</p>

<div class="table-responsive">
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/beer?count=3' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/beer",
  "has_more": true,
  "next_cursor": "Mw==",
  "data": [
    {
      "id": "ed8d5bcd-9016-4954-9f4c-81855dbad55a",
      "name": "¡Ándale! Pale Ale",
      "last_modified": 1588448205
    },
    {
      "id": "64cd7a20-ebf3-4358-bacf-180d1b7d9b96",
      "name": "¡Guava Libre!",
      "last_modified": 1588448205
    },
    {
      "id": "5e4bf0c1-c743-4438-9289-6e77c17ad467",
      "name": "¡Magnifico!",
      "last_modified": 1588448205
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
  "value": 60605
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="beer-search">Search Beer</h3>

<p>Search for beer by name, style, or description using full-text search. To search, send a <strong>GET</strong> request to the <code>/beer/search</code> endpoint with a <var>q</var> query parameter.</p>

<pre class="api-code">GET https://api.catalog.beer/beer/search?q={query}</pre>

<h4>Query Parameters</h4>
<div class="table-responsive">
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
</div>

<p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>

<pre class="api-code">GET https://api.catalog.beer/beer/search?q=ipa&amp;count=5</pre>

<h4>Response</h4>

<p>This request returns a list object with the following parameters. Results are sorted by relevance to the search query. Each result is a full <a href="#beer-object">beer object</a> including the nested <a href="#brewer-object">brewer object</a>, so you can display results without making additional API calls.</p>

<div class="table-responsive">
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/beer/search?q=sculpin&amp;count=1' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/beer/search",
  "query": "sculpin",
  "has_more": true,
  "next_cursor": "MQ==",
  "data": [
    {
      "id": "6a7119c6-92a2-40d2-b87a-2e4529c8577a",
      "object": "beer",
      "name": "Sculpin IPA",
      "style": "American-Style India Pale Ale",
      "style_id": "american-ipa",
      "parent": "ipa",
      "class": "ale",
      "beverage_type": "beer",
      "description": "Our Sculpin IPA is a great example of what got us into brewing in the first place. After years of experimenting, we knew hopping an ale at five separate stages would produce something special. The result ended up being this gold-medal winning IPA, whose inspired use of hops creates hints of apricot, peach, mango and lemon flavors.",
      "abv": 7,
      "ibu": 70,
      "cb_verified": true,
      "brewer_verified": false,
      "last_modified": 1783642824,
      "brewer": {
        "id": "ab94abb7-a3e8-4cce-8945-4758cac66a53",
        "object": "brewer",
        "name": "Ballast Point Brewing Company",
        "description": null,
        "short_description": null,
        "url": "https://www.ballastpoint.com/",
        "cb_verified": false,
        "brewer_verified": false,
        "last_modified": 1541544607
      }
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h2 id="styles">Styles</h2>
<hr>

<p>Catalog.beer records a beer&#8217;s style in two parts: the brewer&#8217;s own label and a canonical classification. The label (<var>beer.style</var>) is plain text, stored and returned exactly as submitted. The classification files the beer against a standard vocabulary of styles, which is what allows the catalog to be sorted, filtered, and counted by style. The vocabulary is drawn from the <a href="https://www.brewersassociation.org/edu/brewers-association-beer-style-guidelines/" target="_blank" rel="noopener">Brewers Association (BA)</a> and <a href="https://www.bjcp.org/bjcp-style-guidelines/" target="_blank" rel="noopener">Beer Judge Certification Program (BJCP)</a> style guidelines.</p>

<p>The vocabulary has three tiers, from broadest to most specific. Each tier corresponds to a field on the <a href="#beer-object">beer object</a>.</p>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Tier</th>
                <th scope="col">Beer Field</th>
                <th scope="col">Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Class</td>
                <td><var>class</var></td>
                <td>The broadest grouping: <var>ale</var> or <var>lager</var>. Some families (e.g. wheat beers, sours, ciders, meads) do not roll up to a class; for those, <var>class</var> is <var>null</var>.</td>
            </tr>
            <tr>
                <td>Family</td>
                <td><var>parent</var></td>
                <td>A group of related styles, such as <var>ipa</var>, <var>stout</var>, or <var>pilsner</var>.</td>
            </tr>
            <tr>
                <td>Style</td>
                <td><var>style_id</var></td>
                <td>One specific style, such as <var>american-ipa</var> or <var>west-coast-ipa</var>.</td>
            </tr>
        </tbody>
    </table>
</div>

<p>A beer may be filed at any tier. The API derives the broader tiers from the one you provide &#8212; file a beer as <var>west-coast-ipa</var> and its <var>parent</var> is set to <var>ipa</var> and its <var>class</var> to <var>ale</var>. The API never fills in a more specific tier than the one you gave it.</p>

<p>When <a href="#beer-create">adding</a> or <a href="#beer-patch">updating</a> a beer, there are two ways to set the classification:</p>

<ol>
    <li>Send the label as <var>style</var> and let the API match it. The label is checked against every canonical name and alias in the vocabulary &#8212; first as an exact style name, then as a class alias, a family alias, and a style alias. For example, &#8220;NEIPA,&#8221; &#8220;New England IPA,&#8221; and &#8220;Juicy IPA&#8221; all resolve to the same style.</li>
    <li>Name the classification directly with <var>style_id</var>, <var>parent</var>, or <var>class</var>. These take precedence over <var>style</var>; if you send more than one, the most specific wins. Your label is still kept verbatim.</li>
</ol>

<p>If the label doesn&#8217;t match anything in the vocabulary and no classification is given, the request returns a <var>400 Bad Request</var> error. The vocabulary includes catch-all styles (<var>catch_all: true</var> &#8212; e.g. <var>specialty-beer</var>) for beers that don&#8217;t fit a more specific style. One exception: when updating an existing beer, resending its current label unchanged never fails &#8212; the beer keeps its current classification, even if that label wouldn&#8217;t resolve on its own.</p>

<p>That <var>400</var> comes back with a <var>suggestions</var> object holding the closest styles, so you can recover without a second lookup. Resend the same label alongside one of the suggested values: the classification comes from the field you name, and your label is stored exactly as you wrote it.</p>

<pre class="api-code">
{
  "error": true,
  "valid_state": {
    "style": "invalid"
  },
  "valid_msg": {
    "style": "We couldn't match \"Cali Pilsner\" to a known style, family, or class. Choose the closest match, or a catch-all so nothing is lost, and send it back with your label unchanged."
  },
  "suggestions": {
    "style": {
      "styles": [
        {
          "style_id": "german-pilsner",
          "name": "German-Style Pilsener",
          "parent": "pilsner",
          "class": "lager",
          "catch_all": false,
          "match": "partial",
          "aliases": ["German Pils", "German Pilsner"]
        },
        {
          "style_id": "pre-prohibition-lager",
          "name": "Pre-Prohibition Lager",
          "parent": "historical",
          "class": null,
          "catch_all": false,
          "match": "partial",
          "aliases": ["Classic American Pilsner", "CAP"]
        },
        {
          "style_id": "czech-pilsner",
          "name": "Czech-Style Pale Lager",
          "parent": "pilsner",
          "class": "lager",
          "catch_all": false,
          "match": "partial",
          "aliases": ["Bohemian Pilsner", "Czech Pilsner"]
        },
        {
          "style_id": "international-pilsner",
          "name": "International-Style Pilsener",
          "parent": "pilsner",
          "class": "lager",
          "catch_all": false,
          "match": "partial",
          "aliases": ["Euro Pale Lager", "International Pilsner"]
        },
        {
          "style_id": "italian-pilsner",
          "name": "Italian-Style Pilsener",
          "parent": "pilsner",
          "class": "lager",
          "catch_all": false,
          "match": "partial",
          "aliases": ["Italian Pils", "Italian Pilsner"]
        },
        {
          "style_id": "contemporary-american-pilsner",
          "name": "Contemporary American-Style Pilsener",
          "parent": "pilsner",
          "class": "lager",
          "catch_all": false,
          "match": "partial",
          "aliases": ["American Hoppy Pilsner", "Craft American Pilsner"]
        }
      ],
      "families": [
        {
          "parent": "pilsner",
          "name": "Pilsner",
          "class": "lager",
          "beverage_type": "beer"
        }
      ]
    }
  }
}
</pre>

<p><small class="text-muted">Alias lists are abridged in the sample above; the API returns all of them.</small></p>

<p><strong>Check <var>match</var> before you trust the order.</strong> It tells you how each candidate was reached, and it is the difference between a recommendation and a guess:</p>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Value</th>
                <th>Meaning</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><var>exact</var></td>
                <td>Your label is this style&#8217;s name or one of its aliases. Safe to take as the answer.</td>
            </tr>
            <tr>
                <td><var>all_terms</var></td>
                <td>Every word in your label appears in this style&#8217;s name or aliases. Usually right; worth a glance.</td>
            </tr>
            <tr>
                <td><var>partial</var></td>
                <td>Some of your words matched. <strong>Do not take the first entry on faith.</strong> Read the list, and consider filing at <var>parent</var> or <var>class</var> instead.</td>
            </tr>
            <tr>
                <td><var>description</var></td>
                <td>Nothing matched the name or aliases &#8212; only our written description of the style. The weakest signal we return.</td>
            </tr>
        </tbody>
    </table>
</div>

<p>Within a <var>match</var> level, candidates are ordered by how well they match and then by how many beers we hold in each, so a populous style can still outrank a better-fitting rare one. That is the response above: every candidate for &#8220;Cali Pilsner&#8221; is <var>partial</var>, and the closest one, <var>contemporary-american-pilsner</var>, comes back <em>last</em>. A list that is all <var>partial</var> is the API telling you it did not recognise your label. <var>class</var> may be <var>null</var> where the family sits outside the ale/lager split.</p>

<p><var>families</var> and <var>classes</var> appear alongside <var>styles</var> when your label names a family or a super-class but no style matched it outright &#8212; &#8220;Crisp American Lager&#8221; returns the <var>lager</var> class. Filing one tier up is often the honest answer, and it is better than picking a specific style that happens to share a word with your label. Send the <var>parent</var> or <var>class</var> value back the same way you would a <var>style_id</var>.</p>

<p>A <var>matched_on</var> key appears when we could not match your whole label and fell back to the last two words of it &#8212; &#8220;Crisp American Lager&#8221; is matched on &#8220;American Lager.&#8221; The candidates describe that shorter phrase, not what you sent, so read it before trusting an <var>exact</var> on the list.</p>

<p>Retrying the example above as <code>{"style": "Cali Pilsner", "style_id": "contemporary-american-pilsner"}</code> succeeds, and the beer keeps &#8220;Cali Pilsner&#8221; as its label. Sending <var>style_id</var>, <var>parent</var>, or <var>class</var> <em>without</em> <var>style</var> stores our canonical name as the label instead of the brewery&#8217;s own wording, so send both.</p>

<p>The beer&#8217;s <var>beverage_type</var> (<var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>) is set from the resolved classification. It cannot be set directly.</p>

<h3 id="style-confidence">Style Confidence</h3>

<p>Classifying a beer sometimes means a judgement call, and those are the ones worth a second look. The optional <var>style_confidence</var> field on <a href="#beer-create">POST</a>, <a href="#beer-update">PUT</a>, and <a href="#beer-patch">PATCH</a> records how firm the classification is, so editorial review can go where it&#8217;s needed instead of everywhere. It is stored for our own review and is never returned in a beer object.</p>

<p><strong>In most cases, leave it out.</strong> We derive it from what we can check &#8212; whether your label matched the vocabulary on its own, and whether you filed under a catch-all:</p>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr><th scope="col">Value</th><th scope="col">Derived when</th></tr>
        </thead>
        <tbody>
            <tr><td><var>confident</var></td><td>Your label matched a canonical name or alias, whether or not you also named a tier.</td></tr>
            <tr><td><var>override</var></td><td>You named a specific <var>style_id</var> for a label that doesn&#8217;t match it &#8212; a mapping only you can vouch for.</td></tr>
            <tr><td><var>catch-all</var></td><td>As above, but the style you chose is a catch-all (<var>catch_all: true</var>).</td></tr>
            <tr><td><var>family</var></td><td>You filed at the family or class tier rather than at a specific style.</td></tr>
        </tbody>
    </table>
</div>

<p>Send the field yourself only to record <strong>less</strong> certainty than we would otherwise assume &#8212; for example, you inferred the style from the beer&#8217;s name rather than a stated one, or picked the least-wrong of two plausible families. Sending <var>catch-all</var> or <var>family</var> where we would have derived <var>override</var> is an honest downgrade, and we keep it.</p>

<p>You cannot use the field to claim more certainty than the request supports. Whether your label matches the vocabulary is something we check rather than take on trust, so pairing an unmatched label with <var>style_confidence: confident</var> is reduced to <var>override</var>. This happens silently &#8212; your write still succeeds.</p>

<p>The <code>/style</code> endpoints are read-only. Use them to browse the vocabulary, build a style picker, or check a label before submitting a beer.</p>

<h3 id="style-object">The Style Object</h3>

<p>The <code>/style</code> endpoints return style objects. <a href="#style-list">List Styles</a> returns a compact form of each; <a href="#style-detail">Retrieve a Style</a> returns the full object, adding <var>parent_name</var>, <var>source</var>, and <var>specs</var>.</p>

<div class="table-responsive">
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
            <tr><td><var>class</var></td><td>string</td><td>The slug of the <a href="#style-classes">class</a> the style rolls up to (<var>ale</var> or <var>lager</var>), or <var>null</var> for families that do not roll up to one.</td></tr>
            <tr><td><var>parent_name</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The display name of the parent family (e.g. &#8220;India Pale Ale&#8221;).</td></tr>
            <tr><td><var>source</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The primary guideline the style is drawn from: <var>BA-2026</var>, <var>BJCP-2021</var>, <var>OCB-2012</var>, or <var>NABA-2024</var>.</td></tr>
            <tr><td><var>catch_all</var></td><td>Boolean</td><td><var>true</var> for non-standard &#8220;catch-all&#8221; styles (e.g. <var>specialty-beer</var>) used when nothing more specific fits. Use it to separate fallback buckets from standard styles &#8212; for example, keeping catch-alls out of a picker&#8217;s ranked matches.</td></tr>
            <tr><td><var>aliases</var></td><td>array</td><td>Other names and spellings that resolve to this style, excluding the canonical <var>name</var>. Use these to match user-entered labels client-side &#8212; for example, in a typeahead &#8212; without an API request per keystroke.</td></tr>
            <tr><td><var>srm</var><br><small class="text-muted">(list only)</small></td><td>object</td><td>The style&#8217;s <a href="https://en.wikipedia.org/wiki/Standard_Reference_Method" target="_blank" rel="noopener">SRM</a> color range as a <code>{ "min": &#8230;, "max": &#8230; }</code> object, or <var>null</var> when the guideline gives no color (cider, mead, perry, and catch-alls). The one spec included in list rows &#8212; enough to render color swatches without a request per style. On the detail endpoint the same range lives in <var>specs.srm</var>.</td></tr>
            <tr><td><var>specs</var><br><small class="text-muted">(detail only)</small></td><td>object</td><td>The style&#8217;s guideline ranges &#8212; <var>abv</var>, <var>ibu</var>, <var>srm</var>, <var>og</var>, and <var>fg</var>. See <a href="#style-specs">the <var>specs</var> field</a> below.</td></tr>
            <tr><td><var>description</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>An editorial description of the style &#8212; what it is and what to expect in the glass. Plain prose; paragraphs separated by blank lines.</td></tr>
            <tr><td><var>appearance</var>, <var>aroma</var>, <var>flavor</var>, <var>mouthfeel</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>Short tasting-note fields describing the style in the glass. <var>null</var> for catch-all styles, which have no fixed sensory profile.</td></tr>
            <tr><td><var>history</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The style&#8217;s origin and evolution &#8212; editorial prose with claims anchored to the citations in <var>sources.history_sources</var>.</td></tr>
            <tr><td><var>notes</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>Occasional editorial notes &#8212; e.g. how this entry relates to neighboring styles. <var>null</var> when there&#8217;s nothing to add.</td></tr>
            <tr><td><var>commercial_examples</var><br><small class="text-muted">(detail only)</small></td><td>array</td><td>Classic or defining commercial examples of the style, as beer names (strings). Curated &#8212; not derived from the Catalog.beer database.</td></tr>
            <tr><td><var>sources</var><br><small class="text-muted">(detail only)</small></td><td>object</td><td>Provenance: the style&#8217;s entry in each guideline it appears in (<var>brewers_association</var>, <var>bjcp</var> with year and code, <var>naba_2024</var>; the one marked <code>"primary": true</code> supplies the canonical name), plus <var>history_sources</var> &#8212; an array of <code>{ "citation", "url" }</code> objects backing the <var>history</var> prose.</td></tr>
        </tbody>
    </table>
</div>

<h4 id="style-specs">The <var>specs</var> Field</h4>

<p>The <var>specs</var> field (detail only) holds the style&#8217;s guideline ranges, drawn from the same <var>source</var> as the style. Each field is a <code>{ "min": &#8230;, "max": &#8230; }</code> object, or <var>null</var> when the guideline doesn&#8217;t specify that measurement. An individual <var>min</var> or <var>max</var> may also be <var>null</var> when the guideline gives only one bound.</p>

<div class="table-responsive">
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
</div>

<h4>Sample</h4>

<pre class="api-code">
{
  "id": "american-ipa",
  "object": "style",
  "name": "American-Style India Pale Ale",
  "beverage_type": "beer",
  "parent": "ipa",
  "class": "ale",
  "parent_name": "India Pale Ale",
  "source": "BA-2026",
  "catch_all": false,
  "aliases": ["American India Pale Ale", "American IPA"],
  "specs": {
    "abv": { "min": 6.3, "max": 7.5 },
    "ibu": { "min": 50, "max": 70 },
    "srm": { "min": 4, "max": 12 },
    "og":  { "min": 1.06, "max": 1.07 },
    "fg":  { "min": 1.01, "max": 1.016 }
  },
  "description": "The defining style of American craft beer &#8212; medium-bodied, golden to copper-colored, and built around the tropical, citrus, pine, and resinous character of American hops. [&#8230;]",
  "appearance": "Gold to deep copper, clear to slightly hazy, with a long-lasting off-white head.",
  "aroma": "Prominent American or New World hop character &#8212; citrus, grapefruit, pine, tropical fruit, stone fruit, or resinous notes. [&#8230;]",
  "flavor": "Medium to high hop bitterness with matching hop flavor. [&#8230;]",
  "mouthfeel": "Medium body, medium carbonation. Smooth without being heavy; alcohol is not prominent.",
  "history": "The American IPA lineage begins with the post-Prohibition remnants of the older East Coast tradition. [&#8230;]",
  "notes": "The clear West Coast and soft Hazy interpretations are now treated as separate entries in the catalog [&#8230;]",
  "commercial_examples": ["Bell's Two Hearted Ale", "Stone IPA", "Russian River Blind Pig", "Lagunitas IPA", "Founders Centennial IPA"],
  "sources": {
    "brewers_association": { "name": "American-Style India Pale Ale", "category_group": "IPA", "primary": true },
    "bjcp": { "year": 2021, "code": "21A", "name": "American IPA" },
    "naba_2024": { "name": "American-Style India Pale Ale" },
    "history_sources": [
      { "citation": "Oliver, Garrett, ed. The Oxford Companion to Beer. New York: Oxford University Press, 2012." },
      { "citation": "All About Beer. \"A Bitter Beginning: The First Anchor Liberty Ale Bottles.\" Accessed April 23, 2026.", "url": "https://allaboutbeer.com/anchor-liberty-ale/" }
    ]
  }
}
</pre>

<p><small class="text-muted">Prose fields truncated for documentation ([&#8230;]) &#8212; the live response returns the full text.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-list">List Styles</h3>

<p>To list every style in the vocabulary, send a <strong>GET</strong> request to the <code>/style</code> endpoint. Styles are returned alphabetically by name as compact <a href="#style-object">style objects</a>. The response also includes a <var>version</var> field identifying the current version of the vocabulary; when it changes, refresh any cached copy.</p>

<pre class="api-code">GET https://api.catalog.beer/style</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/style \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>
<pre class="api-code">
{
  "object": "list",
  "url": "/style",
  "version": "2.3.0",
  "has_more": false,
  "data": [
    {
      "id": "american-ipa",
      "object": "style",
      "name": "American-Style India Pale Ale",
      "beverage_type": "beer",
      "parent": "ipa",
      "class": "ale",
      "catch_all": false,
      "aliases": ["American India Pale Ale", "American IPA"],
      "srm": { "min": 4, "max": 12 }
    }
  ]
}
</pre>

<p><small class="text-muted">Response truncated for documentation &#8212; the full response contains every style in the vocabulary.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-detail">Retrieve a Style</h3>

<p>To retrieve a single style, send a <strong>GET</strong> request to the <code>/style</code> endpoint with the <var>{style_id}</var> parameter appended to the path. The <var>style_id</var> is the style&#8217;s slug (e.g. <var>american-ipa</var>), not a UUID. Successful requests return a full <a href="#style-object">style object</a>, including its <a href="#style-specs">specs</a>.</p>

<pre class="api-code">GET https://api.catalog.beer/style/{style_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/style/american-ipa \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>
<pre class="api-code">
{
  "id": "american-ipa",
  "object": "style",
  "name": "American-Style India Pale Ale",
  "beverage_type": "beer",
  "parent": "ipa",
  "class": "ale",
  "parent_name": "India Pale Ale",
  "source": "BA-2026",
  "catch_all": false,
  "aliases": ["American India Pale Ale", "American IPA"],
  "specs": {
    "abv": { "min": 6.3, "max": 7.5 },
    "ibu": { "min": 50, "max": 70 },
    "srm": { "min": 4, "max": 12 },
    "og":  { "min": 1.06, "max": 1.07 },
    "fg":  { "min": 1.01, "max": 1.016 }
  },
  "description": "The defining style of American craft beer &#8212; medium-bodied, golden to copper-colored, and built around the tropical, citrus, pine, and resinous character of American hops. [&#8230;]",
  "appearance": "Gold to deep copper, clear to slightly hazy, with a long-lasting off-white head.",
  "aroma": "Prominent American or New World hop character &#8212; citrus, grapefruit, pine, tropical fruit, stone fruit, or resinous notes. [&#8230;]",
  "flavor": "Medium to high hop bitterness with matching hop flavor. [&#8230;]",
  "mouthfeel": "Medium body, medium carbonation. Smooth without being heavy; alcohol is not prominent.",
  "history": "The American IPA lineage begins with the post-Prohibition remnants of the older East Coast tradition. [&#8230;]",
  "notes": "The clear West Coast and soft Hazy interpretations are now treated as separate entries in the catalog [&#8230;]",
  "commercial_examples": ["Bell's Two Hearted Ale", "Stone IPA", "Russian River Blind Pig", "Lagunitas IPA", "Founders Centennial IPA"],
  "sources": {
    "brewers_association": { "name": "American-Style India Pale Ale", "category_group": "IPA", "primary": true },
    "bjcp": { "year": 2021, "code": "21A", "name": "American IPA" },
    "naba_2024": { "name": "American-Style India Pale Ale" },
    "history_sources": [
      { "citation": "Oliver, Garrett, ed. The Oxford Companion to Beer. New York: Oxford University Press, 2012." },
      { "citation": "All About Beer. \"A Bitter Beginning: The First Anchor Liberty Ale Bottles.\" Accessed April 23, 2026.", "url": "https://allaboutbeer.com/anchor-liberty-ale/" }
    ]
  }
}
</pre>

<p><small class="text-muted">Prose fields truncated for documentation ([&#8230;]) &#8212; the live response returns the full text.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-search">Search Styles</h3>

<p>Search the style vocabulary by canonical name, alias, or description using full-text search. To search, send a <strong>GET</strong> request to the <code>/style/search</code> endpoint with a <var>q</var> query parameter. Alias matches are first-class: a search for &#8220;NEIPA&#8221; or &#8220;Juicy IPA&#8221; finds <var>hazy-ipa</var> even though neither term appears in the canonical name.</p>

<pre class="api-code">GET https://api.catalog.beer/style/search?q={query}</pre>

<h4>Query Parameters</h4>
<div class="table-responsive">
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
</div>

<p>A sample request with query parameters. Be sure to encode all non-alphanumeric characters except <code>-_</code>.</p>

<pre class="api-code">GET https://api.catalog.beer/style/search?q=oktoberfest&amp;count=5</pre>

<h4>Response</h4>

<p>This request returns a list object with the following parameters. Results are ranked in three tiers, and relevance orders results within a tier rather than across tiers:</p>

<ol>
    <li><strong>Exact match</strong> &#8212; the query is exactly the style&#8217;s name or one of its aliases. Searching &#8220;American IPA&#8221; puts <var>american-ipa</var> first.</li>
    <li><strong>Full name match</strong> &#8212; every term in the query appears in the style&#8217;s name or aliases. Aliases count as part of the name, which is why &#8220;NEIPA&#8221; finds <var>hazy-ipa</var> and &#8220;IPA&#8221; finds styles whose names spell out &#8220;India Pale Ale&#8221;.</li>
    <li><strong>Partial name match</strong> &#8212; some but not all query terms appear in the name or aliases.</li>
    <li><strong>Description match</strong> &#8212; the query appears only in the style&#8217;s editorial description. A style that merely mentions a term never outranks one named for it.</li>
</ol>

<p>Within a tier, styles are ordered by how many catalogued beers use them, so a search for a broad term surfaces styles that are actually brewed ahead of ones that exist only in the vocabulary. This ordering applies only between styles that match the query equally well &#8212; a precise match on a rare style always outranks a partial match on a common one.</p>

<p>Broad queries often have no single correct style answer &#8212; &#8220;IPA&#8221; is not one style but twelve. When a query exactly names a style family, by slug, name, or alias, that family is returned separately in <var>families</var> so you can offer the group rather than an arbitrary member of it. Searching &#8220;ipa&#8221; returns the <var>ipa</var> family alongside the individual IPA styles.</p>

<div class="table-responsive">
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
                <td>The API endpoint accessed to retrieve this object. In this case: <code>/style/search</code>.</td>
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
                <td><var>families</var></td>
                <td>array</td>
                <td>An array of <a href="#style-parents">style family objects</a> whose slug, name, or alias <em>exactly</em> matches the query. Empty when no family matches. Families are never paginated &#8212; there are only 26 in total, and <var>has_more</var> and <var>next_cursor</var> describe <var>data</var> only. They are returned on the first page only, so an empty array on a later page means &#8220;not repeated here&#8221;, not &#8220;no family matched&#8221;. These rows omit <var>aliases</var>; use <a href="#style-parents">List Style Families</a> for the full object.</td>
            </tr>
            <tr>
                <td><var>data</var></td>
                <td>array</td>
                <td>An array of compact <a href="#style-object">style objects</a> matching the search query, in ranked order &#8212; the same shape as <a href="#style-list">List Styles</a> rows, including <var>aliases</var> and <var>srm</var>.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/style/search?q=NEIPA' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/style/search",
  "query": "NEIPA",
  "has_more": false,
  "families": [],
  "data": [
    {
      "id": "hazy-ipa",
      "object": "style",
      "name": "Juicy or Hazy India Pale Ale",
      "beverage_type": "beer",
      "parent": "ipa",
      "class": "ale",
      "catch_all": false,
      "aliases": ["Hazy IPA", "Hazy/Juicy IPA", "Juicy IPA", "NE IPA", "NEIPA", "New England IPA"],
      "srm": { "min": 3, "max": 7 }
    }
  ]
}
</pre>

<h4>Sample Response &#8212; Broad Query</h4>

<p>A query naming a whole family returns that family in <var>families</var> alongside its member styles in <var>data</var>. Offering the family is usually a better answer than the top-ranked individual style.</p>

<pre class="api-code">GET https://api.catalog.beer/style/search?q=ipa

{
  "object": "list",
  "url": "/style/search",
  "query": "ipa",
  "has_more": true,
  "next_cursor": "Mw==",
  "families": [
    {
      "slug": "ipa",
      "object": "style_parent",
      "name": "India Pale Ale",
      "beverage_type": "beer",
      "class": "ale",
      "description": "...",
      "sort_order": 2
    }
  ],
  "data": [ ... ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-parents">List Families</h3>

<p>To list the families in the vocabulary (the <var>parent</var> tier), send a <strong>GET</strong> request to the <code>/style/parent</code> endpoint. Families are returned in display order. Each family object has the following parameters.</p>

<pre class="api-code">GET https://api.catalog.beer/style/parent</pre>

<div class="table-responsive">
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
                <td><var>slug</var></td>
                <td>string</td>
                <td>The family&#8217;s slug (e.g. <var>ipa</var>) &#8212; the value used as <var>parent</var> on a beer.</td>
            </tr>
            <tr>
                <td><var>object</var></td>
                <td>string</td>
                <td>The name of the object; in this case: &#8220;style_parent&#8221;.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The name of the family (e.g. &#8220;India Pale Ale&#8221;).</td>
            </tr>
            <tr>
                <td><var>beverage_type</var></td>
                <td>string</td>
                <td>One of <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>.</td>
            </tr>
            <tr>
                <td><var>class</var></td>
                <td>string</td>
                <td>The slug of the <a href="#style-classes">class</a> this family rolls up to (<var>ale</var> or <var>lager</var>), or <var>null</var> if it does not roll up to one.</td>
            </tr>
            <tr>
                <td><var>description</var></td>
                <td>string</td>
                <td>A brief description of the family.</td>
            </tr>
            <tr>
                <td><var>sort_order</var></td>
                <td>integer</td>
                <td>The family&#8217;s position in display order.</td>
            </tr>
            <tr>
                <td><var>aliases</var></td>
                <td>array</td>
                <td>Names and spellings that resolve to this family when sent as a beer&#8217;s <var>style</var>.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/style/parent \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

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
      "description": "The hop showcase of brewing: pale, bitter, and intensely aromatic ales ranging from crisp, resinous West Coast versions to soft, juicy hazy ones, plus session, double, Belgian, and black variations. Hops drive the aroma, flavor, and bitterness, with malt kept in a supporting role.",
      "sort_order": 2,
      "aliases": ["India Pale Ale", "IPA", "IPAs"]
    }
  ]
}
</pre>

<p><small class="text-muted">Response truncated for documentation &#8212; the full response contains every family.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-classes">List Classes</h3>

<p>To list the classes in the vocabulary &#8212; currently <var>ale</var> and <var>lager</var> &#8212; send a <strong>GET</strong> request to the <code>/style/class</code> endpoint. Classes are returned in display order. Each class object has the following parameters.</p>

<pre class="api-code">GET https://api.catalog.beer/style/class</pre>

<div class="table-responsive">
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
                <td><var>slug</var></td>
                <td>string</td>
                <td>The class&#8217;s slug (e.g. <var>ale</var>) &#8212; the value used as <var>class</var> on a beer.</td>
            </tr>
            <tr>
                <td><var>object</var></td>
                <td>string</td>
                <td>The name of the object; in this case: &#8220;style_class&#8221;.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The name of the class (e.g. &#8220;Ale&#8221;).</td>
            </tr>
            <tr>
                <td><var>beverage_type</var></td>
                <td>string</td>
                <td>One of <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>.</td>
            </tr>
            <tr>
                <td><var>sort_order</var></td>
                <td>integer</td>
                <td>The class&#8217;s position in display order.</td>
            </tr>
            <tr>
                <td><var>aliases</var></td>
                <td>array</td>
                <td>Names and spellings that resolve to this class when sent as a beer&#8217;s <var>style</var>.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/style/class \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

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

<div class="table-responsive">
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
                <td>string or null</td>
                <td>The name of the location, or <var>null</var> when the location has no name of its own. Most taprooms aren&#8217;t named separately from the brewer and the community they sit in, so expect <var>null</var> here and compose a label from the nested <var>brewer</var> object and the <var>address</var> block (for example, &#8220;Avery Brewing &#8211; Boulder&#8221;).</td>
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
</div>

<h4>Sample</h4>

<p>Sample location object with a US address.</p>

<pre class="api-code">
{
  "id": "972dc9a3-4462-48b5-9a0f-eb0ab6f94738",
  "object": "location",
  "name": "Little Italy",
  "url": "https://www.ballastpoint.com/location/ballast-point-little-italy/",
  "country_code": "US",
  "country_short_name": "United States of America",
  "latitude": 32.7276344,
  "longitude": -117.1697159,
  "cb_verified": false,
  "brewer_verified": false,
  "last_modified": 1589147029,
  "address": {
    "address1": null,
    "address2": "2215 India St",
    "city": "San Diego",
    "sub_code": "US-CA",
    "state_short": "CA",
    "state_long": "California",
    "zip5": "92101",
    "zip4": "1725",
    "telephone": 6192557213
  },
  "brewer": {
    "id": "ab94abb7-a3e8-4cce-8945-4758cac66a53",
    "object": "brewer",
    "name": "Ballast Point Brewing Company",
    "description": null,
    "short_description": null,
    "url": "https://www.ballastpoint.com/",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1541544607
  }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-add">Add a Location</h3>

<p>To add a location for a brewer, send a <strong>POST</strong> request to the <code>/location</code> endpoint with the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">POST https://api.catalog.beer/location</pre>

<div class="table-responsive">
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
                <td><var>name</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The name of the location, if it has one of its own. Omit it for a taproom that simply goes by its brewer and city &#8212; don&#8217;t repeat the city here, since the address already records it.</td>
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/location \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"9f96b99b-9865-4cf0-8950-bdc0d675be9f","name":"San Pedro","country_code":"US"}'
  </pre>

<h4>Sample Response</h4>
 <pre class="api-code">
{
  "id": "9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b",
  "object": "location",
  "name": "San Pedro",
  "url": null,
  "country_code": "US",
  "country_short_name": "United States of America",
  "latitude": null,
  "longitude": null,
  "cb_verified": false,
  "brewer_verified": false,
  "last_modified": 1589147029,
  "brewer": {
    "id": "9f96b99b-9865-4cf0-8950-bdc0d675be9f",
    "object": "brewer",
    "name": "Brouwerij West",
    "description": null,
    "short_description": null,
    "url": "https://www.brouwerijwest.com/",
    "cb_verified": true,
    "brewer_verified": false,
    "last_modified": 1517001234
  }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-update">Update a Location (PUT)</h3>

<p>To replace a location&#8217;s data, send a <strong>PUT</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the location does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/location/{location_id}</pre>

<div class="table-responsive">
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
                <td><var>name</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The name of the location, if it has one of its own. As with any optional field on a <strong>PUT</strong>, omitting it clears the stored name to <var>null</var>.</td>
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/location/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"9f96b99b-9865-4cf0-8950-bdc0d675be9f","name":"San Pedro","country_code":"US"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-patch">Update a Location (PATCH)</h3>

<p>To partially update a location, send a <strong>PATCH</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path. Only the fields you include will be updated; all other fields remain unchanged. Successful requests return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">PATCH https://api.catalog.beer/location/{location_id}</pre>

<div class="table-responsive">
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
                <td>The name of the location. Send <var>null</var> or an empty string to clear a name that&#8217;s already stored.</td>
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/location/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"name":"San Pedro"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-delete">Delete a Location</h3>

<p>To delete a location, send a <strong>DELETE</strong> request to the <code>/location</code> endpoint with the <var>location_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body. Deleting is limited to brewery staff &#8212; check <a href="#brewer-permissions">your permissions for the location&#8217;s brewer</a> before attempting it.</p>

<pre class="api-code">DELETE https://api.catalog.beer/location/{location_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/location/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-add-address">Add an Address to a Location</h3>

<p>Addresses and telephone numbers are stored separately from locations to allow for country specific addressing schemes and telephone numbers. Hence the need to make a second request to add an address to a location.</p>

                                <p>To add an address or telephone number for a location, send a <strong>POST</strong> request to the <code>/address</code> endpoint with the <var>location_id</var> appended to the path and the following parameters encoded in the body of the request as JSON. Successful requests will return a <a href="#location-object">location object</a>.</p>

<p>Currently, only US addresses are supported. This documentation will be updated once support for other countries has been added.</p>

<pre class="api-code">POST https://api.catalog.beer/address/{location_id}</pre>

<div class="table-responsive">
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/address/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"address1":"Warehouse No 9","address2":"110 E 22nd St","city":"San Pedro","sub_code":"US-CA","telephone":"3108339330"}'
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "id": "9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b",
  "object": "location",
  "name": "San Pedro",
  "url": null,
  "country_code": "US",
  "country_short_name": "United States of America",
  "latitude": 33.7273445,
  "longitude": -118.2799377,
  "cb_verified": false,
  "brewer_verified": false,
  "last_modified": 1589147029,
  "address": {
    "address1": "Warehouse No 9",
    "address2": "110 E 22nd St",
    "city": "San Pedro",
    "sub_code": "US-CA",
    "state_short": "CA",
    "state_long": "California",
    "zip5": "90731",
    "zip4": "7202",
    "telephone": 3108339330
  },
  "brewer": {
    "id": "9f96b99b-9865-4cf0-8950-bdc0d675be9f",
    "object": "brewer",
    "name": "Brouwerij West",
    "description": null,
    "short_description": null,
    "url": "https://www.brouwerijwest.com/",
    "cb_verified": true,
    "brewer_verified": false,
    "last_modified": 1517001234
  }
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-replace-address">Replace an Address</h3>

<p>To create or replace an address for a location, send a <strong>PUT</strong> request to the <code>/address</code> endpoint with the <var>location_id</var> appended to the path. This will completely replace any existing address data for the location. Omitted optional fields will be cleared to null. Successful requests will return a <a href="#location-object">location object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/address/{location_id}</pre>

<div class="table-responsive">
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
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PUT \
  https://api.catalog.beer/address/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"address1":"Warehouse No 9","address2":"110 E 22nd St","city":"San Pedro","sub_code":"US-CA","zip5":"90731","zip4":"7202","telephone":"3108339330"}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-retrieve">Retrieve a Location</h3>

                                <p>To retrieve a location, send a <strong>GET</strong> request to the <code>/location</code> endpoint with the <var>{location_id}</var> parameter appended to the path.</p>

<pre class="api-code">GET https://api.catalog.beer/location/{location_id}</pre>

<p>A <a href="#location-object">location object</a> will be returned for successful requests.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/location/972dc9a3-4462-48b5-9a0f-eb0ab6f94738 \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- /LOCATION (LIST) ----->

<h3 id="location-list-all">List all Locations</h3>

<p>Retrieves a list of all the locations in the database. To access this data, send a <strong>GET</strong> request to the <code>/location</code> endpoint.</p>

<pre class="api-code">GET https://api.catalog.beer/location</pre>

<h4>Query Parameters</h4>
<div class="table-responsive">
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
</div>

<h4>Response</h4>
<p>This request returns a list object with the following parameters.</p>

<div class="table-responsive">
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
                <td>The name of the object. In this case: &ldquo;list&rdquo;.</td>
            </tr>
            <tr>
                <td><var>url</var></td>
                <td>string</td>
                <td>The API endpoint accessed to retrieve this object. In this case: <code>/location</code>.</td>
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
                <td>An array containing all the locations in the database sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, and <var>last_modified</var>, described below.</td>
            </tr>
            <tr>
                <td><var>id</var></td>
                <td>string</td>
                <td>The <var>location_id</var>.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The name of the location. May be <var>null</var> &#8212; a location&#8217;s name is optional, and consumers that need a display title should compose one (for example, from the brewer name and city).</td>
            </tr>
            <tr>
                <td><var>last_modified</var></td>
                <td>integer</td>
                <td>A Unix timestamp representing the date and time the location was last modified.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location?count=3' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/location",
  "has_more": true,
  "next_cursor": "Mw==",
  "data": [
    {
      "id": "972dc9a3-4462-48b5-9a0f-eb0ab6f94738",
      "name": "10 Barrel Brewing Bend Pub",
      "last_modified": 1721860205
    },
    {
      "id": "3778262a-5b70-4f0c-b2a2-49831437eb3f",
      "name": "10 Barrel Brewing East Bend",
      "last_modified": 1721860205
    },
    {
      "id": "f9d13aad-fce6-4c86-9f2b-c85dbcf12a45",
      "name": "10 Barrel Brewing Portland",
      "last_modified": 1721860205
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- /LOCATIONS/NEARBY ----->

<h3 id="nearby-locations">Find Nearby Locations</h3>

<p>One of the questions that gets asked most is &#8220;where is the nearest brewery?&#8221; or &#8220;I&#8217;m heading to Acme town, what breweries are local?&#8221;. To answer those questions or questions like them, use this endpoint.</p>

<p>To retrieve a location, send a <strong>GET</strong> request to the <code>/location/nearby </code> endpoint with the <var>{latitude}</var> and <var>{longitude}</var> query parameters appended to the path.</p>

<h4>Query Parameters</h4>
<div class="table-responsive">
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
</div>

<p>A sample request with query parameters.</p>

<pre class="api-code">GET https://api.catalog.beer/location/nearby?latitude={latitude}&amp;longitude={longitude}</pre>

<h4>Response</h4>
<p>This request returns a list object with the following parameters.</p>

<div class="table-responsive">
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
</div>

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
        "id": "0b4ee673-8db0-4952-bbd6-cc0b69994c10",
        "object": "location",
        "name": "North Park",
        "brewer_id": "7205f9f1-6bf0-4241-9f0f-35a2351d8450",
        "url": null,
        "country_code": "US",
        "country_short_name": "United States of America",
        "latitude": 32.7476883,
        "longitude": -117.12854,
        "telephone": 6192557136,
        "address": {
          "address1": null,
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
        "id": "7205f9f1-6bf0-4241-9f0f-35a2351d8450",
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
<div class="table-responsive">
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
</div>

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
        "id": "95a881ef-794a-4f2a-bd5a-ed88cfd1988d",
        "object": "location",
        "name": "North Park Flavordome",
        "brewer_id": "69e7ae7d-9a37-46b3-aa5d-7bf5d092b976",
        "url": "https://www.moderntimesbeer.com/locations/north-park-flavordome/",
        "country_code": "US",
        "country_short_name": "United States of America",
        "latitude": 32.7414894,
        "longitude": -117.129921,
        "telephone": 6192695222,
        "address": {
          "address1": null,
          "address2": "3000 Upas St",
          "city": "San Diego",
          "sub_code": "US-CA",
          "state_short": "CA",
          "state_long": "California",
          "zip5": "92104",
          "zip4": "4221"
        }
      },
      "distance": {
        "distance": 0.6,
        "units": "miles"
      },
      "brewer": {
        "id": "69e7ae7d-9a37-46b3-aa5d-7bf5d092b976",
        "object": "brewer",
        "name": "Modern Times Beer",
        "description": null,
        "short_description": null,
        "url": "http://www.moderntimesbeer.com/",
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
<div class="table-responsive">
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
</div>

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
        "id": "7abe715f-0ddf-405e-b730-e2ab1abaef13",
        "object": "location",
        "name": "East Village",
        "brewer_id": "880a8e73-ae38-4466-b6af-f84141ae6996",
        "url": "https://baycitybrewingco.com/visit/tasting-room/",
        "country_code": "US",
        "country_short_name": "United States of America",
        "latitude": 32.7119141,
        "longitude": -117.1571503,
        "telephone": 6197701844,
        "address": {
          "address1": null,
          "address2": "627 8th Ave",
          "city": "San Diego",
          "sub_code": "US-CA",
          "state_short": "CA",
          "state_long": "California",
          "zip5": "92101",
          "zip4": "6453"
        }
      },
      "distance": {
        "distance": 0.3,
        "units": "miles"
      },
      "brewer": {
        "id": "880a8e73-ae38-4466-b6af-f84141ae6996",
        "object": "brewer",
        "name": "Bay City Brewing",
        "description": "Our brewing facility is located in the heart of San Diego between Mission Bay and San Diego Bay. From craft beer and cocktails to delicious food and wine, we strive to make every experience memorable.\r\n\r\nThe Bay City Brewing Company was established in San Diego, California in 1912 (incorporated in 1911). It was initially controlled by F.C. and August Lang, J.H. Zitt’s father-in-law and brother-in-law. Today, the Bay City Brewing Company is owned and operated by its partners Chad Robley, Ben Dubois, and Greg Anderson – all local San Diegans.",
        "short_description": null,
        "url": "http://baycitybrewingco.com/",
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

<div class="table-responsive">
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
</div>

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

<p>When your usage exceeds your <var>request_limit</var> + <var>request_buffer</var>, the API will return a <var>429 Too Many Requests</var> response for all endpoints except <code>/usage</code> and <code>/billing</code>. Your count resets on the first of each month. To keep going past the free tier, add a payment method&#8212;see <a href="#billing">Billing</a>.</p>

<p><a href="#top">^ Return to top</a></p>

<!---------- BILLING ---------->

<h2 id="billing">Billing</h2>

<p>Every account includes 1,000 free API requests per month. With a payment method on file, your key keeps working past the free tier and usage is billed at <strong>$1 per 1,000 requests</strong>, rounded up to whole blocks of 1,000. Billing is powered by Stripe: cards are collected on a Stripe-hosted checkout page and never touch Catalog.beer&#8217;s servers.</p>

<p>A few things to know:</p>

<ul>
    <li><strong>Invoicing.</strong> Overage is invoiced on the first of the following month and charged automatically to your saved card. Balances under $5 roll forward to a later invoice rather than being charged immediately.</li>
    <li><strong>Spend cap.</strong> Each key has a monthly spend cap ($50 by default) as protection against runaway usage. Once the month&#8217;s usage would cost more than the cap, further requests receive a <var>429</var> response until the month resets. You can adjust the cap between $1 and $1,000, or set it to $0 to block all paid usage.</li>
    <li><strong>Exempt endpoints.</strong> The <code>/billing</code> and <code>/usage</code> endpoints are never rate limited and don&#8217;t count toward your usage, so you can always check your status and manage billing&#8212;even when your key is over its cap.</li>
</ul>

<!----- BILLING: OBJECT ----->

<h3 id="billing-object">The Billing Object</h3>

<p>Your billing status is represented as an object with the following parameters.</p>

<div class="table-responsive">
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
                <td>The type of object returned. Value will be <code>billing</code>.</td>
            </tr>
            <tr>
                <td><var>api_key</var></td>
                <td>string</td>
                <td>The API key this billing status applies to.</td>
            </tr>
            <tr>
                <td><var>billing_enabled</var></td>
                <td>boolean</td>
                <td>Whether usage past the free tier is enabled for this key. Becomes <code>true</code> automatically once a payment method has been saved.</td>
            </tr>
            <tr>
                <td><var>monthly_spend_cap_cents</var></td>
                <td>integer</td>
                <td>Your monthly spend cap, in cents. Each $1 of cap covers 1,000 requests past the free tier.</td>
            </tr>
            <tr>
                <td><var>card</var></td>
                <td>object</td>
                <td>The default payment method on file (<var>brand</var>, <var>last4</var>, <var>exp_month</var>, <var>exp_year</var>), or <code>null</code> when billing is not enabled.</td>
            </tr>
            <tr>
                <td><var>year</var>, <var>month</var></td>
                <td>integer</td>
                <td>The current usage period.</td>
            </tr>
            <tr>
                <td><var>count</var></td>
                <td>integer</td>
                <td>Requests made so far this month.</td>
            </tr>
            <tr>
                <td><var>request_limit</var></td>
                <td>integer</td>
                <td>Free requests included each month.</td>
            </tr>
            <tr>
                <td><var>billable_requests</var></td>
                <td>integer</td>
                <td>Requests past the free tier so far this month.</td>
            </tr>
            <tr>
                <td><var>estimated_charge_cents</var></td>
                <td>integer</td>
                <td>What this month&#8217;s usage would cost so far, in cents ($1 per 1,000 billable requests, rounded up, capped at your spend cap). <code>0</code> when billing is not enabled.</td>
            </tr>
            <tr>
                <td><var>unbilled_balance_cents</var></td>
                <td>integer</td>
                <td>Charges from earlier months that haven&#8217;t been invoiced yet because they&#8217;re under the $5 invoice minimum.</td>
            </tr>
        </tbody>
    </table>
</div>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: RETRIEVE ----->

<h3 id="billing-retrieve">Get Billing Status</h3>

<p>To retrieve your billing status, send a <strong>GET</strong> request to the <code>/billing</code> endpoint. No special permissions are required&#8212;the endpoint returns data for the authenticated API key.</p>

<pre class="api-code">GET https://api.catalog.beer/billing</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  https://api.catalog.beer/billing \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}'
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "billing",
  "api_key": "cadcbe6f-a80d-4e33-9f20-b53c2ed83845",
  "billing_enabled": true,
  "monthly_spend_cap_cents": 5000,
  "card": {
    "brand": "visa",
    "last4": "4242",
    "exp_month": 12,
    "exp_year": 2034
  },
  "year": 2026,
  "month": 7,
  "count": 4215,
  "request_limit": 1000,
  "billable_requests": 3215,
  "estimated_charge_cents": 400,
  "unbilled_balance_cents": 0
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: CHECKOUT SESSION ----->

<h3 id="billing-checkout-session">Create a Checkout Session</h3>

<p>To add a payment method, send a <strong>POST</strong> request to the <code>/billing/checkout-session</code> endpoint, then redirect the user to the returned <var>url</var>&#8212;a Stripe-hosted checkout page that collects the card. Nothing is charged at checkout; once the card is saved, billing is enabled for your key automatically.</p>

<pre class="api-code">POST https://api.catalog.beer/billing/checkout-session</pre>

<div class="table-responsive">
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
                <td><var>success_url</var></td>
                <td>string</td>
                <td>Where Stripe sends the user after a card is saved. Must be an HTTPS URL on catalog.beer or one of its subdomains.</td>
            </tr>
            <tr>
                <td><var>cancel_url</var></td>
                <td>string</td>
                <td>Where Stripe sends the user if they back out of checkout. Same URL restrictions as <var>success_url</var>.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/billing/checkout-session \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{
  "success_url": "https://catalog.beer/billing?checkout=success",
  "cancel_url": "https://catalog.beer/billing?checkout=cancelled"
}'
</pre>

<h4>Sample Response</h4>

<p>Returns <var>201 Created</var> on success.</p>

<pre class="api-code">
{
  "object": "checkout_session",
  "id": "cs_live_a1b2c3...",
  "url": "https://checkout.stripe.com/c/pay/cs_live_a1b2c3..."
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: PORTAL SESSION ----->

<h3 id="billing-portal-session">Create a Portal Session</h3>

<p>To let a user manage their saved payment methods and view past invoices, send a <strong>POST</strong> request to the <code>/billing/portal-session</code> endpoint and redirect them to the returned <var>url</var>&#8212;a Stripe-hosted billing portal. Requires that a billing account already exists (i.e., a checkout session has been created before).</p>

<pre class="api-code">POST https://api.catalog.beer/billing/portal-session</pre>

<div class="table-responsive">
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
                <td><var>return_url</var></td>
                <td>string</td>
                <td>Where Stripe sends the user when they leave the portal. Must be an HTTPS URL on catalog.beer or one of its subdomains.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Response</h4>

<p>Returns <var>201 Created</var> on success.</p>

<pre class="api-code">
{
  "object": "portal_session",
  "url": "https://billing.stripe.com/p/session/..."
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: UPDATE ----->

<h3 id="billing-update">Update Spend Cap</h3>

<p>To change your monthly spend cap, send a <strong>PATCH</strong> request to the <code>/billing</code> endpoint.</p>

<pre class="api-code">PATCH https://api.catalog.beer/billing</pre>

<div class="table-responsive">
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
                <td><var>monthly_spend_cap_cents</var></td>
                <td>integer</td>
                <td>The new cap, in cents. Either <code>0</code> (block all usage past the free tier) or a value between <code>100</code> ($1) and <code>100000</code> ($1,000).</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/billing \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{
  "monthly_spend_cap_cents": 10000
}'
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "billing",
  "api_key": "cadcbe6f-a80d-4e33-9f20-b53c2ed83845",
  "billing_enabled": true,
  "monthly_spend_cap_cents": 10000
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: DISABLE ----->

<h3 id="billing-disable">Disable Billing</h3>

<p>To turn off billing and return your key to the free tier, send a <strong>DELETE</strong> request to the <code>/billing</code> endpoint. Your card stays saved with Stripe, and usage already accrued past the free tier will still be invoiced. To re-enable billing, complete a new <a href="#billing-checkout-session">checkout session</a>.</p>

<pre class="api-code">DELETE https://api.catalog.beer/billing</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "billing",
  "api_key": "cadcbe6f-a80d-4e33-9f20-b53c2ed83845",
  "billing_enabled": false,
  "monthly_spend_cap_cents": 5000
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!---------- US ADDRESSES ---------->

<h2 id="us-address">US Addresses</h2>

<p>For locations in the United States, data is stored and captured using the US Addresses data structure.</p>

<!----- US ADDRESSES: OBJECT ----->

<h3 id="us-address-object">The US Address Object</h3>

<p>Addresses for the United States are stored in an object with the following parameters.</p>

<div class="table-responsive">
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
                <td>string</td>
                <td>The traditional 5-digit ZIP Code for the location. Returned as a string because ZIP Codes have significant leading zeros (e.g. <samp>01085</samp>).</td>
            </tr>
            <tr>
                <td><var>zip4</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The additional ZIP+4 Code used by the US Postal Service. More on the <a href="https://faq.usps.com/s/article/ZIP-Code-The-Basics" target="_blank" rel="noopener">ZIP+4 Code</a>.</td>
            </tr>
            <tr>
                <td><var>telephone</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>An unformatted integer representing the telephone number of the location. Does not include the country code. For example, a US telephone number written in the international format as +1 (555) 444&#8211;3333 is stored as 5554443333 in our database.</td>
            </tr>
        </tbody>
    </table>
</div>

<p><a href="#top">^ Return to top</a></p>

<!---------- USERS ---------->

<h2 id="users">Users</h2>
<hr>

<p>The users endpoints allow you to manage your own account. User accounts are created through the <a href="https://catalog.beer">Catalog.beer</a> website, not directly via the API.</p>

<!----- USERS: OBJECT ----->

<h3 id="users-object">The User Object</h3>

<p>Successful requests to user endpoints will return the user object in JSON format. That object has the following parameters.</p>

<div class="table-responsive">
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
</div>

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

<div class="table-responsive">
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
</div>

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

<div class="table-responsive">
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
</div>

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

<div class="table-responsive">
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
</div>

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
