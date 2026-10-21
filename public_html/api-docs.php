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
                    <a class="list-group-item list-group-item-action" href="#location"><strong>Location</strong></a>
                    <a class="list-group-item list-group-item-action" href="#location-object">&gt; The Location Object</a>
                    <a class="list-group-item list-group-item-action" href="#location-address">&gt; The Address Object</a>
                    <a class="list-group-item list-group-item-action" href="#location-add">&gt; Add a Location</a>
                    <a class="list-group-item list-group-item-action" href="#location-update">&gt; Update a Location (PUT)</a>
                    <a class="list-group-item list-group-item-action" href="#location-patch">&gt; Update a Location (PATCH)</a>
                    <a class="list-group-item list-group-item-action" href="#location-delete">&gt; Delete a Location</a>
                    <a class="list-group-item list-group-item-action" href="#location-retrieve">&gt; Retrieve a Location</a>
                    <a class="list-group-item list-group-item-action" href="#location-list-all">&gt; List all Locations</a>
                    <a class="list-group-item list-group-item-action" href="#nearby-locations">&gt; Find Nearby Locations</a>
                    <a class="list-group-item list-group-item-action" href="#location-zip">&gt; Find Locations by Postal Code</a>
                    <a class="list-group-item list-group-item-action" href="#location-city">&gt; Find Locations by City</a>
                    <a class="list-group-item list-group-item-action" href="#location-countries">&gt; Supported Countries</a>
                    <a class="list-group-item list-group-item-action" href="#location-subdivisions">&gt; Subdivisions</a>
                    <a class="list-group-item list-group-item-action" href="#styles"><strong>Styles</strong></a>
                    <a class="list-group-item list-group-item-action" href="#style-confidence">&gt; Style Confidence</a>
                    <a class="list-group-item list-group-item-action" href="#style-object">&gt; The Style Object</a>
                    <a class="list-group-item list-group-item-action" href="#style-list">&gt; List Styles</a>
                    <a class="list-group-item list-group-item-action" href="#style-detail">&gt; Retrieve a Style</a>
                    <a class="list-group-item list-group-item-action" href="#style-search">&gt; Search Styles</a>
                    <a class="list-group-item list-group-item-action" href="#style-parents">&gt; List Families</a>
                    <a class="list-group-item list-group-item-action" href="#style-classes">&gt; List Classes</a>
                    <a class="list-group-item list-group-item-action" href="#usage"><strong>Usage</strong></a>
                    <a class="list-group-item list-group-item-action" href="#usage-object">&gt; The Usage Object</a>
                    <a class="list-group-item list-group-item-action" href="#usage-my-usage">&gt; Get My Usage</a>
                    <a class="list-group-item list-group-item-action" href="#billing"><strong>Billing</strong></a>
                    <a class="list-group-item list-group-item-action" href="#billing-object">&gt; The Billing Object</a>
                    <a class="list-group-item list-group-item-action" href="#billing-retrieve">&gt; Get Billing Status</a>
                    <a class="list-group-item list-group-item-action" href="#billing-update">&gt; Update Spend Cap</a>
                    <a class="list-group-item list-group-item-action" href="#billing-disable">&gt; Disable Billing</a>
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
                <p>Last Updated: October 17, 2026</p>

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

                <p>Authentication to the API is performed via the <a href="https://en.wikipedia.org/wiki/Basic_access_authentication" target="_blank" rel="noopener">‘Basic’ HTTP authentication</a> scheme. <strong>Provide your API key as the username value.</strong> You do not need to provide a password.</p>

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

                <p>Catalog.beer uses conventional HTTP response codes to indicate the success or failure of an API request. In general, codes in the <var>2xx</var> range indicate success, codes in the <var>4xx</var> range indicate an error that failed given the information provided (e.g., a required parameter was omitted), and codes in the <var>5xx</var> range indicate an error with Catalog.beer’s servers.</p>

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
                                <td>An array containing the attribute names and their validation state. A field’s state is binary: either ‘valid’ or ‘invalid’. You can use this parameter to help target which attributes are invalid. ‘valid’ means the field passed validation — not that it was saved. When any attribute is ‘invalid’ the whole write is rejected and nothing is stored.</td>
                            </tr>
                            <tr>
                                <td><var>valid_msg</var><br><small class="text-muted">(optional)</small></td>
                                <td>array</td>
                                <td>An array containing the attribute names and the corresponding error message for that attribute. You can use this information to show help text next to the attributes that were invalid.</td>
                            </tr>
                            <tr>
                                <td><var>suggestions</var><br><small class="text-muted">(optional)</small></td>
                                <td>array</td>
                                <td>Keyed by attribute name, like <var>valid_state</var> and <var>valid_msg</var>. Where we can work out what an invalid value probably meant, this carries the values that would fix it, so you can retry without a second lookup. Present only when we have candidates — always treat it as optional. Currently returned for <var>style</var> on <a href="#beer">/beer</a> writes; see <a href="#styles">Styles</a>.</td>
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
                                <td>Partial update of a resource. Only the fields you provide will be modified; all other fields remain unchanged. The resource must already exist (returns <var>404 Not Found</var> otherwise). A PATCH is applied <strong>all or nothing</strong>: if any field in the request fails validation, the response is a <var>400 Bad Request</var> and none of the fields are written — including the ones <var>valid_state</var> reports as valid.</td>
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

                <p>Whether you’re retrieving information on a specific brewer, adding a new brewer, or updating an existing brewer, successful requests will return the brewer object in JSON format. That object has the following parameters.</p>

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
                                <td>A unique identifier for the brewer.</td>
                            </tr>
                            <tr>
                                <td><var>object</var></td>
                                <td>string</td>
                                <td>The type of object, in this case “brewer”.</td>
                            </tr>
                            <tr>
                                <td><var>name</var></td>
                                <td>string</td>
                                <td>The name of the brewer.</td>
                            </tr>
                            <tr>
                                <td><var>description</var></td>
                                <td>string</td>
                                <td>A description of the brewer. Plain text, which may contain new line characters — those are meaningful and worth preserving. It is not markup: catalog.beer displays it as written, with line breaks kept and nothing else interpreted.</td>
                            </tr>
                            <tr>
                                <td><var>short_description</var></td>
                                <td>string</td>
                                <td>A short, max 160 character, description of the brewer.</td>
                            </tr>
                            <tr>
                                <td><var>url</var></td>
                                <td>string</td>
                                <td>The URL of the brewer’s website. A homepage (e.g. northchair.com) can belong to only one brewer. However, a brewery’s URL may be a page on another brewery’s site (e.g. northchair.com/whetstone-beer) — an acquired brand that lives on its owner’s site — so a domain may be shared as long as no two brewers store the same page.</td>
                            </tr>
                            <tr>
                                <td><var>status</var></td>
                                <td>string</td>
                                <td>“active” or “closed”. A brewery that has stopped brewing keeps its record and its beers; this flag is how the catalog says so. A closed brewer accepts no new locations.</td>
                            </tr>
                            <tr>
                                <td><var>founded_year</var></td>
                                <td>integer</td>
                                <td>The year the brewery was founded, or <var>null</var> when unknown. Year only.</td>
                            </tr>
                            <tr>
                                <td><var>closed_year</var></td>
                                <td>integer</td>
                                <td>The year the brewery closed, or <var>null</var>. Only ever set when <var>status</var> is “closed”; a closure whose year is unknown is “closed” with a <var>null</var> year.</td>
                            </tr>
                            <tr>
                                <td><var>country_code</var></td>
                                <td>string</td>
                                <td>The ISO 3166–1 Alpha–2 Code for the country the brewer is based in, the same standard the <a href="#location-object">location object</a> uses. See <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank" rel="noopener">the full list of assigned codes</a> or the <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166</a> standard. The brewer may be anywhere; its locations may be in any of the <a href="#location-countries">supported countries</a>.</td>
                            </tr>
                            <tr>
                                <td><var>cb_verified</var></td>
                                <td>Boolean</td>
                                <td><var>true</var> when a Catalog.beer administrator has reviewed this brewer. An edit by a general user sets it back to <var>false</var> until an administrator reviews it again.</td>
                            </tr>
                            <tr>
                                <td><var>brewer_verified</var></td>
                                <td>Boolean</td>
                                <td><var>true</var> when the brewery itself has provided or confirmed this brewer. General users can’t edit it; see <a href="#brewer-permissions">Your Permissions for a Brewer</a>.</td>
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
  "status": "active",
  "founded_year": 2015,
  "closed_year": null,
  "country_code": "US",
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
                                <td>A description of the brewer. This should be like the “About” page posted by the brewer. A brief origin story coupled with who they are. Send plain text: we store the bytes you send, and catalog.beer displays them with your line breaks kept. Markup is not interpreted, so formatting syntax will show up literally.</td>
                            </tr>
                            <tr>
                                <td><var>short_description</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>A short description of the brewer; max 160 characters. Describe the brewer as you might in a tweet. Short and sweet.</td>
                            </tr>
                            <tr>
                                <td><var>url</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>The URL of the brewer’s website.</td>
                            </tr>
                            <tr>
                                <td><var>status</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>“active” (the default) or “closed”.</td>
                            </tr>
                            <tr>
                                <td><var>founded_year</var><br><small class="text-muted">(optional)</small></td>
                                <td>integer</td>
                                <td>A four-digit year, no later than this year. Send it only when the brewery or a named source states it.</td>
                            </tr>
                            <tr>
                                <td><var>closed_year</var><br><small class="text-muted">(optional)</small></td>
                                <td>integer</td>
                                <td>A four-digit year; requires <var>status</var> “closed” and cannot precede <var>founded_year</var>.</td>
                            </tr>
                            <tr>
                                <td><var>country_code</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>ISO 3166–1 Alpha–2 (<a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank" rel="noopener">the full list of assigned codes</a>); defaults to “US”. Case-insensitive; an unassigned code is a 400.</td>
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

                <p>To replace a brewer’s data, send a <strong>PUT</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the brewer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#brewer-object">brewer object</a>.</p>

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
                                <td>The URL of the brewer’s website. Cleared if omitted.</td>
                            </tr>
                            <tr>
                                <td><var>status</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>“active” or “closed”. Reset to “active” if omitted — a PUT that omits it reopens a closed brewer, so prefer PATCH to change anything else on one.</td>
                            </tr>
                            <tr>
                                <td><var>founded_year</var><br><small class="text-muted">(optional)</small></td>
                                <td>integer</td>
                                <td>A four-digit year, no later than this year. Cleared if omitted.</td>
                            </tr>
                            <tr>
                                <td><var>closed_year</var><br><small class="text-muted">(optional)</small></td>
                                <td>integer</td>
                                <td>A four-digit year; requires <var>status</var> “closed” and cannot precede <var>founded_year</var>. Cleared if omitted.</td>
                            </tr>
                            <tr>
                                <td><var>country_code</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>ISO 3166–1 Alpha–2 (<a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank" rel="noopener">the full list of assigned codes</a>); reset to “US” if omitted.</td>
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
                                <td>The URL of the brewer’s website.</td>
                            </tr>
                            <tr>
                                <td><var>status</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>“active” or “closed”. Patching a closed brewer back to “active” also clears its <var>closed_year</var>.</td>
                            </tr>
                            <tr>
                                <td><var>founded_year</var><br><small class="text-muted">(optional)</small></td>
                                <td>integer</td>
                                <td>A four-digit year, no later than this year. Send <var>null</var> to clear it.</td>
                            </tr>
                            <tr>
                                <td><var>closed_year</var><br><small class="text-muted">(optional)</small></td>
                                <td>integer</td>
                                <td>A four-digit year; requires <var>status</var> “closed” and cannot precede <var>founded_year</var>.</td>
                            </tr>
                            <tr>
                                <td><var>country_code</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>ISO 3166–1 Alpha–2 (<a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank" rel="noopener">the full list of assigned codes</a>). Case-insensitive; an unassigned code is a 400.</td>
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

                <p>To delete a brewer, send a <strong>DELETE</strong> request to the <code>/brewer</code> endpoint with the <var>brewer_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body. Deleting a brewer also permanently deletes all of its beers and locations. Deleting is limited to brewery staff — check <a href="#brewer-permissions">your permissions</a> before attempting it.</p>

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
                                <td>The type of object, in this case “list”.</td>
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
                                <td>The type of object, in this case “count”.</td>
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
                                <td>The type of object, in this case “list”.</td>
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
                                <td>An array of <a href="#brewer-object">brewer objects</a> matching the search query, sorted by relevance. Each result additionally carries a <var>match</var> field describing why it matched: <code>exact</code> (the name equals the query), <code>all_terms</code> (every query term matches the start of a word in the name), <code>partial</code> (at least one query term matches the name), or <code>description</code> (the name matched nothing — the hit is in the description text). When checking whether a brewer already exists, only the first three values are evidence of a name match.</td>
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
      "last_modified": 1588448205,
      "match": "all_terms"
    }
  ]
}
</pre>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-beers">List all Beers made by a Brewer</h3>

                <p>If you would like a list of all the beers made by a brewer, send a request to the <code>/brewer/{brewer_id}/beer</code> endpoint. Without query parameters it returns the brewery’s whole catalog in one response, however large.</p>

                <p>Three optional query parameters narrow the list. Any of them with an invalid value returns a <code>400</code> rather than being ignored.</p>

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
                                <td><var>parent</var></td>
                                <td>string</td>
                                <td>A style family id. Only beers filed in that family are returned; an unknown family returns an empty list.</td>
                            </tr>
                            <tr>
                                <td><var>exclude</var></td>
                                <td>string</td>
                                <td>A <var>beer_id</var> to leave out of the list.</td>
                            </tr>
                            <tr>
                                <td><var>count</var></td>
                                <td>integer</td>
                                <td>The maximum number of beers to return, from 1 to 500. When more exist, <var>has_more</var> is <var>true</var>. There is no cursor on this endpoint: the list is alphabetical, so narrow it with <var>parent</var> rather than paging through it.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>For example, <code>/brewer/{brewer_id}/beer?parent=ipa&amp;exclude={beer_id}&amp;count=6</code> returns up to six of the brewery’s other IPAs.</p>

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
                                <td>The type of object, in this case “list”.</td>
                            </tr>
                            <tr>
                                <td><var>url</var></td>
                                <td>string</td>
                                <td>The API endpoint accessed to retrieve this object. In this case: <code>/brewer/{brewer_id}/beer</code>.</td>
                            </tr>
                            <tr>
                                <td><var>has_more</var></td>
                                <td>Boolean</td>
                                <td><var>false</var> unless a <var>count</var> was given and the brewery has more matching beers than that.</td>
                            </tr>
                            <tr>
                                <td><var>brewer</var></td>
                                <td>object</td>
                                <td>A <a href="#brewer-object">brewer object</a> containing information for the requested <var>{brewer_id}</var>.</td>
                            </tr>
                            <tr>
                                <td><var>data</var></td>
                                <td>array</td>
                                <td>An array containing the beers associated with this brewer in the database (all of them, or those matching the query parameters) sorted alphabetically by name. Each array object has the following attributes: <var>id</var>, <var>name</var>, <var>style</var>, <var>style_id</var>, <var>parent</var>, <var>class</var>, <var>beverage_type</var>, <var>abv</var>, <var>cb_verified</var>, and <var>brewer_verified</var> described below.</td>
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
                                <td>The canonical family id.</td>
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
                                <td><var>true</var> when a Catalog.beer administrator has reviewed this beer. An edit by a general user sets it back to <var>false</var> until an administrator reviews it again.</td>
                            </tr>
                            <tr>
                                <td><var>brewer_verified</var></td>
                                <td>Boolean</td>
                                <td><var>true</var> when the brewery itself has provided or confirmed this beer. General users can’t edit it; see <a href="#brewer-permissions">Your Permissions for a Brewer</a>.</td>
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

                <p><small class="text-muted">Response truncated for documentation — this brewer has 81 beers in its <var>data</var> array.</small></p>
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
                                <td>The type of object, in this case “list”.</td>
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
                                <td>An array containing all the locations associated with this brewer in the database sorted alphabetically by name. Each array object is a <a href="#location-object">location object</a> — including its <var>address</var> block, <var>latitude</var>, and <var>longitude</var> — without the nested <var>brewer</var> object (the list’s top-level <var>brewer</var> already carries it).</td>
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
        "line1": "1462 N Milwaukee Ave",
        "line2": null,
        "locality": "Chicago",
        "dependent_locality": null,
        "sub_code": "US-IL",
        "postal_code": "60622",
        "formatted": ["1462 N Milwaukee Ave", "Chicago, IL 60622"],
        "language_code": "en",
        "telephone": null,
        "admin_area": {
          "sub_code": "US-IL",
          "name": "Illinois",
          "type": "state"
        }
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
        "line1": "5401 Linda Vista Rd",
        "line2": null,
        "locality": "San Diego",
        "dependent_locality": null,
        "sub_code": "US-CA",
        "postal_code": "92110",
        "formatted": ["5401 Linda Vista Rd", "San Diego, CA 92110"],
        "language_code": "en",
        "telephone": "+16192952337",
        "admin_area": {
          "sub_code": "US-CA",
          "name": "California",
          "type": "state"
        }
      }
    }
  ]
}
</pre>

                <p><small class="text-muted">Response truncated for documentation — this brewer has 9 locations in its <var>data</var> array.</small></p>
                <p><a href="#top">^ Return to top</a></p>

                <h3 id="brewer-permissions">Your Permissions for a Brewer</h3>

                <p>Before attempting to update or delete a brewer — or any of its beers or locations — you can ask what your API key is allowed to do by sending a <strong>GET</strong> request to the <code>/brewer/{brewer_id}/permissions</code> endpoint. This is the polite alternative to attempting a write and handling the <var>403</var>: agents and applications should preflight destructive or gated operations here.</p>

                <pre class="api-code">GET https://api.catalog.beer/brewer/{brewer_id}/permissions</pre>

                <p>The response is computed for the API key making the request, at the time of the request. Different keys receive different answers for the same brewer, and your answer can change (for example, if the brewer becomes verified, or your account is granted brewery-staff privileges) — so don’t cache it across keys or for long periods.</p>

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
                                <td>The type of object, in this case “permissions”.</td>
                            </tr>
                            <tr>
                                <td><var>brewer_id</var></td>
                                <td>string</td>
                                <td>The unique identifier of the brewer these permissions apply to.</td>
                            </tr>
                            <tr>
                                <td><var>role</var></td>
                                <td>string</td>
                                <td>Your relationship to this brewer. Possible values include <var>staff</var> (your account is associated with the brewery — e.g., your verified email address is at the brewery’s domain) and <var>general</var> (no association). Other values may be added over time; treat any value you don’t recognize as <var>general</var>.</td>
                            </tr>
                            <tr>
                                <td><var>edit</var></td>
                                <td>Boolean</td>
                                <td>Whether your key may <strong>PUT</strong>/<strong>PATCH</strong> this brewer. <var>staff</var> may always edit; <var>general</var> users may edit unless the brewer is <var>brewer_verified</var>. A <var>cb_verified</var> brewer is open to edits; a <var>general</var> edit clears its <var>cb_verified</var> flag until an administrator reviews it again.</td>
                            </tr>
                            <tr>
                                <td><var>delete</var></td>
                                <td>Boolean</td>
                                <td>Whether your key may <strong>DELETE</strong> this brewer. Deleting is limited to <var>staff</var>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>The same rules extend to everything under the brewer. If your role is <var>staff</var>, you may edit and delete the brewer’s beers and locations as well. If your role is <var>general</var>, you may edit a beer or location unless that specific entity is <var>brewer_verified</var> (check its own flag; editing a <var>cb_verified</var> entity is allowed and clears that flag), and you may not delete anything. <var>edit</var> and <var>delete</var> in this response describe the brewer itself.</p>

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
                                <td>A unique identifier for the beer.</td>
                            </tr>
                            <tr>
                                <td><var>object</var></td>
                                <td>string</td>
                                <td>The type of object, in this case “beer”.</td>
                            </tr>
                            <tr>
                                <td><var>name</var></td>
                                <td>string</td>
                                <td>The name of the beer.</td>
                            </tr>
                            <tr>
                                <td><var>style</var></td>
                                <td>string</td>
                                <td>The style of the beer as a human-readable label — the brewer’s own wording, preserved exactly.</td>
                            </tr>
                            <tr>
                                <td><var>style_id</var></td>
                                <td>string</td>
                                <td>The canonical <a href="#styles">style</a> the label resolved to, or <var>null</var> when the beer is filed at the family or class level rather than a specific style.</td>
                            </tr>
                            <tr>
                                <td><var>parent</var></td>
                                <td>string</td>
                                <td>The canonical family the beer belongs to. Derived from <var>style_id</var>, or set directly when filed at the family level.</td>
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
                                <td>A description of the beer. This field may contain a basic description, may contain tasting notes and/or brewer’s notes. Plain text, which may contain new line characters — those are meaningful and worth preserving. It is not markup: catalog.beer displays it as written, with line breaks kept and nothing else interpreted.</td>
                            </tr>
                            <tr>
                                <td><var>abv</var></td>
                                <td>float</td>
                                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Always present.</td>
                            </tr>
                            <tr>
                                <td><var>ibu</var></td>
                                <td>integer</td>
                                <td>The International Bitterness/Bittering Units (IBU) value of the beer, from <var>0</var> to <var>1000</var>. <var>null</var> when the bitterness has never been recorded — which is a different statement from <var>0</var>, meaning a beer with no measurable bitterness.</td>
                            </tr>
                            <tr>
                                <td><var>cb_verified</var></td>
                                <td>Boolean</td>
                                <td><var>true</var> when a Catalog.beer administrator has reviewed this beer. An edit by a general user sets it back to <var>false</var> until an administrator reviews it again.</td>
                            </tr>
                            <tr>
                                <td><var>brewer_verified</var></td>
                                <td>Boolean</td>
                                <td><var>true</var> when the brewery itself has provided or confirmed this beer. General users can’t edit it; see <a href="#brewer-permissions">Your Permissions for a Brewer</a>.</td>
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

                <p>The <var>style</var> you submit is kept exactly as you wrote it and matched to the catalog’s standard list of styles — for example, “West Coast IPA” resolves to the <var>west-coast-ipa</var> style, “IPA” to the <var>ipa</var> family, and “Lager” to the <var>lager</var> class. To name the classification yourself, send <var>style_id</var>, <var>parent</var>, or <var>class</var>; if you send more than one, the most specific wins. If the label doesn’t match and no classification is given, the request returns a <var>400 Bad Request</var> error. See <a href="#styles">Styles</a>.</p>

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
                                <td>The brewer’s style label, resolved to a canonical style/family/class (see note above). Required unless you supply <var>style_id</var>, <var>parent</var>, or <var>class</var>.</td>
                            </tr>
                            <tr>
                                <td><var>style_id</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>File at a specific canonical <a href="#styles">style</a>. Takes precedence over <var>style</var>.</td>
                            </tr>
                            <tr>
                                <td><var>parent</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>File at a family without choosing a specific style.</td>
                            </tr>
                            <tr>
                                <td><var>class</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>File at a super-class: <var>ale</var> or <var>lager</var>.</td>
                            </tr>
                            <tr>
                                <td><var>style_confidence</var><br><small class="text-muted">(optional)</small></td>
                                <td>string</td>
                                <td>How sure you are of the classification, used to prioritize editorial review. Usually omit it — we derive it. Send it only to record <em>less</em> certainty than the request implies. See <a href="#style-confidence">Style Confidence</a>.</td>
                            </tr>
                            <tr>
                                <td><var>description<br><small class="text-muted">(optional)</small></var></td>
                                <td>string</td>
                                <td>A description of the beer. This may be a basic description, or it can be detailed, containing tasting notes and brewer’s notes. Send plain text: we store the bytes you send, and catalog.beer displays them with your line breaks kept. Markup is not interpreted, so formatting syntax will show up literally.</td>
                            </tr>
                            <tr>
                                <td><var>abv</var></td>
                                <td>float</td>
                                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Required — send <var>0</var> for a non-alcoholic beer. Rounded to one decimal place.</td>
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

<p>To replace a beer’s data, send a <strong>PUT</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. All required fields must be present. Omitted optional fields will be cleared to null. If the beer does not exist, it will be created and a <var>201 Created</var> response will be returned. Successful requests return a <a href="#beer-object">beer object</a>.</p>

<pre class="api-code">PUT https://api.catalog.beer/beer/{beer_id}</pre>

<p>The style fields work the same as when <a href="#beer-create">adding a beer</a>: send the label as <var>style</var>, or name the classification with <var>style_id</var>, <var>parent</var>, or <var>class</var>. If the label doesn’t match and no classification is given, the request returns a <var>400 Bad Request</var> error — unless the label is unchanged from the beer’s current <var>style</var>, in which case the update succeeds and the beer keeps its current classification. See <a href="#styles">Styles</a>.</p>

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
                <td>The brewer’s style label, resolved to a canonical style/family/class. Required unless you supply <var>style_id</var>, <var>parent</var>, or <var>class</var>.</td>
            </tr>
            <tr>
                <td><var>style_id</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>File at a specific canonical <a href="#styles">style</a>. Takes precedence over <var>style</var>.</td>
            </tr>
            <tr>
                <td><var>parent</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>File at a family without choosing a specific style.</td>
            </tr>
            <tr>
                <td><var>class</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>File at a super-class: <var>ale</var> or <var>lager</var>.</td>
            </tr>
            <tr>
                <td><var>abv</var></td>
                <td>float</td>
                <td>The Alcohol by Volume (ABV) percentage of the beer, from <var>0</var> to <var>99.9</var>. Required — send <var>0</var> for a non-alcoholic beer. Rounded to one decimal place.</td>
            </tr>
            <tr>
                <td><var>description</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>A description of the beer. Cleared if omitted.</td>
            </tr>
            <tr>
                <td><var>ibu</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>The International Bitterness/Bittering Units (IBU) value of the beer, from <var>0</var> to <var>1000</var>. Whole numbers only. Cleared if omitted. Send <var>0</var> only to state that the beer has no measurable bitterness — a cleared field means the bitterness is unknown.</td>
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

<p>To change a beer’s style, send any of <var>style</var>, <var>style_id</var>, <var>parent</var>, or <var>class</var>. The style is re-resolved and all of the beer’s classification fields are updated together. Resending the beer’s current <var>style</var> unchanged never fails — the beer keeps its current classification. See <a href="#styles">Styles</a>.</p>

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
                <td>The brewer’s style label, re-resolved to a canonical style/family/class.</td>
            </tr>
            <tr>
                <td><var>style_id</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>File at a specific canonical <a href="#styles">style</a>.</td>
            </tr>
            <tr>
                <td><var>parent</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>File at a family.</td>
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

<p>To delete a beer, send a <strong>DELETE</strong> request to the <code>/beer</code> endpoint with the <var>beer_id</var> appended to the path. No request body is required. Successful requests return a <var>204 No Content</var> response with no body. Deleting is limited to brewery staff — check <a href="#brewer-permissions">your permissions for the beer’s brewer</a> before attempting it.</p>

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
                <td>The type of object, in this case “list”.</td>
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
                <td>The type of object, in this case “list”.</td>
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
                <td>An array of <a href="#beer-object">beer objects</a> matching the search query, sorted by relevance. Each beer object includes a nested <a href="#brewer-object">brewer object</a> and a <var>match</var> field describing why it matched: <code>exact</code>, <code>all_terms</code>, or <code>partial</code> (the beer’s name matched the query, fully or in part) or <code>description</code> (the name matched nothing — the hit is in the style or description text).</td>
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
      "match": "all_terms",
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

<h2 id="location">Location</h2>
<hr>

<p>Locations are public venues where you can drink a brewer’s beer — places like taprooms, brewpubs, and tasting rooms. We aren’t looking to catalog production facilities, offices, or locations where you can’t grab a pint.</p>

<h3 id="location-object">The Location Object</h3>

<p>The Location object has the following parameters:</p>

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
                <td>The location’s unique id.</td>
            </tr>
            <tr>
                <td><var>object</var></td>
                <td>string</td>
                <td>The type of object, in this case “location”.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string or null</td>
                <td>The location’s own name, or <var>null</var> when it has none. Most taprooms don’t have a unique name, they’re simply named for their city or town. If you’re building a label for locations with a <var>null</var> name, we suggest something like “{brewer_name} - {city/town_name}”</td>
            </tr>
            <tr>
                <td><var>url</var></td>
                <td>string or null</td>
                <td>A web page for this location, if it has one.</td>
            </tr>
            <tr>
                <td><var>country_code</var></td>
                <td>string</td>
                <td>The country, as an <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166–1 alpha-2</a> code.</td>
            </tr>
            <tr>
                <td><var>country_short_name</var></td>
                <td>string</td>
                <td>The country’s name.</td>
            </tr>
            <tr>
                <td><var>latitude</var><br><small class="text-muted">(optional)</small></td>
                <td>float</td>
                <td>The location’s latitude.</td>
            </tr>
            <tr>
                <td><var>longitude</var><br><small class="text-muted">(optional)</small></td>
                <td>float</td>
                <td>The location’s longitude.</td>
            </tr>
            <tr>
                <td><var>cb_verified</var></td>
                <td>Boolean</td>
                <td><var>true</var> when a Catalog.beer administrator has reviewed this location. An edit by a general user sets it back to <var>false</var> until an administrator reviews it again.</td>
            </tr>
            <tr>
                <td><var>brewer_verified</var></td>
                <td>Boolean</td>
                <td><var>true</var> when the brewery itself has provided or confirmed this location. General users can’t edit it; see <a href="#brewer-permissions">Your Permissions for a Brewer</a>.</td>
            </tr>
            <tr>
                <td><var>last_modified</var></td>
                <td>integer</td>
                <td>When the location last changed, as a Unix timestamp.</td>
            </tr>
            <tr>
                <td><var>address</var></td>
                <td>object</td>
                <td>The street address. See <a href="#location-address">The Address Object</a>. Every write carries one, so the field is absent only on the few locations added before that was required.</td>
            </tr>
            <tr>
                <td><var>brewer</var></td>
                <td>object</td>
                <td>The <a href="#brewer-object">brewer</a> this location belongs to.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Sample</h4>

<p>A location with an address.</p>

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
    "line1": "2215 India St",
    "line2": null,
    "locality": "San Diego",
    "dependent_locality": null,
    "sub_code": "US-CA",
    "postal_code": "92101-1725",
    "formatted": ["2215 India St", "San Diego, CA 92101-1725"],
    "language_code": "en",
    "telephone": "+16192557213",
    "admin_area": {
      "sub_code": "US-CA",
      "name": "California",
      "type": "state"
    }
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

<h3 id="location-address">The Address Object</h3>

<p>A location has a street address, held in its <var>address</var> field. Send it inside the Location Object when you create or update a location. There is no separate address endpoint.</p>

<p>The address is validated before it is saved. What you get back is the corrected version: the street written out in full, the postal code in its standard form, and the map coordinates filled in. The same fields work in every country, and <var>formatted</var> gives you the address as display lines, already in the order that country uses.</p>

<h4>In a response</h4>

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
                <td><var>line1</var></td>
                <td>string</td>
                <td>The street line, spelled out and in the country’s order.</td>
            </tr>
            <tr>
                <td><var>line2</var></td>
                <td>string or null</td>
                <td>The suite, unit, floor or building, if any.</td>
            </tr>
            <tr>
                <td><var>locality</var></td>
                <td>string</td>
                <td>The city or town, as the postal service names it.</td>
            </tr>
            <tr>
                <td><var>dependent_locality</var></td>
                <td>string or null</td>
                <td>A district or village inside the locality, in countries that address that way. Usually <var>null</var>.</td>
            </tr>
            <tr>
                <td><var>sub_code</var></td>
                <td>string or null</td>
                <td>The state, province or region as an <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166–2</a> code. Always set in the US. Elsewhere, set only when the country uses regions in its addresses and one was found or given. Brussels has no province, so a Brussels address has <var>null</var> here.</td>
            </tr>
            <tr>
                <td><var>postal_code</var></td>
                <td>string or null</td>
                <td>The postal code as the country writes it, always a string, so leading zeros and letters matter (e.g. <var>01085</var>, <var>SW1A 1AA</var>).</td>
            </tr>
            <tr>
                <td><var>formatted</var></td>
                <td>array of strings</td>
                <td>The address as lines ready to display, in that country’s order and without the country name: <code>["2215 India St", "San Diego, CA 92101-1725"]</code> or <code>["Donkerstraat 12", "8640 Westvleteren"]</code>. Show them as they are. Add <var>country_short_name</var> from the location when the reader is in another country.</td>
            </tr>
            <tr>
                <td><var>language_code</var></td>
                <td>string</td>
                <td>The language the address is written in, as a <a href="https://en.wikipedia.org/wiki/IETF_language_tag" target="_blank" rel="noopener">BCP 47</a> tag. A Brussels address comes back in Dutch or French depending on what was sent, and this tells you which.</td>
            </tr>
            <tr>
                <td><var>telephone</var></td>
                <td>string or null</td>
                <td>The phone number as an <a href="https://en.wikipedia.org/wiki/E.164" target="_blank" rel="noopener">E.164</a> string: a plus sign, the country code and the number, no spaces. Works as-is in a <code>tel:</code> link.</td>
            </tr>
            <tr>
                <td><var>admin_area</var></td>
                <td>object or null</td>
                <td>The region behind <var>sub_code</var>, if any: its <var>sub_code</var>, its <var>name</var> in the local language and its <var>type</var> (<var>state</var>, <var>province</var>, <var>region</var>, <var>canton</var>, and so on).</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Samples</h4>

<p>A US address. The state is in <var>admin_area</var> and the ZIP+4 is in <var>postal_code</var>.</p>

<pre class="api-code">
{
  "line1": "2215 India St",
  "line2": null,
  "locality": "San Diego",
  "dependent_locality": null,
  "sub_code": "US-CA",
  "postal_code": "92101-1725",
  "formatted": ["2215 India St", "San Diego, CA 92101-1725"],
  "language_code": "en",
  "telephone": "+16192557213",
  "admin_area": {
    "sub_code": "US-CA",
    "name": "California",
    "type": "state"
  }
}
</pre>

<p>A Belgian address with a province, and a Brussels address with none.</p>

<pre class="api-code">
{
  "line1": "Donkerstraat 12",
  "line2": null,
  "locality": "Westvleteren",
  "dependent_locality": null,
  "sub_code": "BE-VWV",
  "postal_code": "8640",
  "formatted": ["Donkerstraat 12", "8640 Westvleteren"],
  "language_code": "nl",
  "telephone": "+3257400376",
  "admin_area": {
    "sub_code": "BE-VWV",
    "name": "West-Vlaanderen",
    "type": "province"
  }
}
</pre>

<pre class="api-code">
{
  "line1": "Rue Gheude 56",
  "line2": null,
  "locality": "Anderlecht",
  "dependent_locality": null,
  "sub_code": null,
  "postal_code": "1070",
  "formatted": ["Rue Gheude 56", "1070 Anderlecht"],
  "language_code": "fr",
  "telephone": null,
  "admin_area": null
}
</pre>

<h4 id="address-request">In a request</h4>

<p>Send these fields inside <var>address</var>, written the way the address is written locally. Each field means what it does in a response; the notes below cover only what is different about sending it.</p>

<p>The fields marked required are the minimum, not the goal. Send everything you have: each field you add is one less thing the validator has to guess.</p>

<p>The validator covers 40 countries; see <a href="#location-countries">Supported Countries</a>. If it cannot match the address to a specific building, the request fails with a <var>400</var> and nothing is saved. On a <strong>POST</strong>, that means no location is created.</p>

<p>Why more than the street: the validator itself needs only <var>line1</var>, and given only that it will pick a town. “Donkerstraat 12” alone lands in Sint-Niklaas, not Westvleteren. The town or the postal code pins it down, and in the US the state does the same for a city name that several states share.</p>

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
                <td><var>line1</var><br><small class="text-muted">(required)</small></td>
                <td>string</td>
                <td>The street line.</td>
            </tr>
            <tr>
                <td><var>line2</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The suite, unit, floor or building, if any (e.g. <var>Suite 101</var>, <var>bus 3</var>).</td>
            </tr>
            <tr>
                <td><var>locality</var><br><small class="text-muted">(required unless you send <var>postal_code</var>)</small></td>
                <td>string</td>
                <td>The city or town.</td>
            </tr>
            <tr>
                <td><var>dependent_locality</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>Usually leave it out; the validator fills it in.</td>
            </tr>
            <tr>
                <td><var>sub_code</var><br><small class="text-muted">(required in the US when <var>locality</var> stands in for <var>postal_code</var>; optional otherwise)</small></td>
                <td>string</td>
                <td>You can send just the suffix: <var>CA</var> on a US location becomes <var>US-CA</var>. It must belong to the location’s country; see <a href="#location-subdivisions">Subdivisions</a> for the list. If the validator finds a region itself, that one is kept.</td>
            </tr>
            <tr>
                <td><var>postal_code</var><br><small class="text-muted">(required unless you send <var>locality</var>)</small></td>
                <td>string</td>
                <td>A code of clearly the wrong shape is refused before validation. Otherwise the validated form is what gets saved.</td>
            </tr>
            <tr>
                <td><var>telephone</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The phone number, either with its country code (e.g. <var>+32 57 40 03 76</var>) or as dialed locally in the location’s country (e.g. <var>057 40 03 76</var>). It comes back as <var>+3257400376</var>.</td>
            </tr>
        </tbody>
    </table>
</div>

<p>Validation errors come back under <var>valid_state.address</var> and <var>valid_msg.address</var>, keyed by the seven fields above, next to the location’s own <var>brewer_id</var>, <var>name</var>, <var>url</var> and <var>country_code</var>.</p>

<h4>Sample Request</h4>

<p>A Belgian location created with its address. The validator reports no province for Belgium, so <var>sub_code</var> and <var>admin_area</var> stay <var>null</var> unless you send one.</p>

<pre class="api-code">
curl -X POST \
  https://api.catalog.beer/location \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"brewer_id":"30f24653-8c0e-4f0b-a1d2-5e6f7a8b9c0d","country_code":"BE","address":{"line1":"Donkerstraat 12","locality":"Westvleteren","postal_code":"8640","telephone":"057 40 03 76"}}'
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "id": "5fd1b7e0-3c2a-4b7e-9d41-0a8c1f6e2b90",
  "object": "location",
  "name": null,
  "url": null,
  "country_code": "BE",
  "country_short_name": "Belgium",
  "latitude": 50.8957083,
  "longitude": 2.7200225,
  "cb_verified": false,
  "brewer_verified": false,
  "last_modified": 1760400000,
  "address": {
    "line1": "Donkerstraat 12",
    "line2": null,
    "locality": "Westvleteren",
    "dependent_locality": null,
    "sub_code": null,
    "postal_code": "8640",
    "formatted": ["Donkerstraat 12", "8640 Westvleteren"],
    "language_code": "nl",
    "telephone": "+3257400376",
    "admin_area": null
  },
  "brewer": {
    "id": "30f24653-8c0e-4f0b-a1d2-5e6f7a8b9c0d",
    "object": "brewer",
    "name": "Brouwerij De Sint-Sixtusabdij van Westvleteren",
    "description": null,
    "short_description": null,
    "url": "https://sintsixtus.be/",
    "status": "active",
    "founded_year": 1838,
    "closed_year": null,
    "country_code": "BE",
    "cb_verified": false,
    "brewer_verified": false,
    "last_modified": 1760400000
  }
}
</pre>

<p>Changing only the phone of an existing location:</p>

<pre class="api-code">
curl -X PATCH \
  https://api.catalog.beer/location/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
  -H 'content-type: application/json' \
  -d '{"address":{"telephone":"(310) 833-9331"}}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-add">Add a Location</h3>

<p>To add a location, send a <strong>POST</strong> request to <code>/location</code> with the fields below as JSON. Send the address in the same request: it is validated first, and the location is created only if the address passes, so a refused street never leaves a location behind without one. A successful request returns the <a href="#location-object">location object</a>.</p>

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
                <td>The brewer this location belongs to.</td>
            </tr>
            <tr>
                <td><var>name</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The location’s own name, if it has one. Leave it out for a taproom that goes by its brewer and town, and don’t repeat the town here; the address already has it.</td>
            </tr>
            <tr>
                <td><var>url</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>A page for this location, with its hours or directions. It should not be the brewer’s main URL.</td>
            </tr>
            <tr>
                <td><var>country_code</var></td>
                <td>string</td>
                <td>The country, as an ISO 3166–1 alpha-2 code, from the <a href="#location-countries">supported list</a>. The address is validated in this country.</td>
            </tr>
            <tr>
                <td><var>address</var></td>
                <td>object</td>
                <td>The street address; see <a href="#address-request">The Address Object › In a request</a> for its fields. Without it, the request is refused with a <var>400</var> that names the fields the country needs. If the validator can’t place it, nothing is created.</td>
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
  -d '{"brewer_id":"9f96b99b-9865-4cf0-8950-bdc0d675be9f","name":"San Pedro","country_code":"US","address":{"line1":"110 E 22nd St","line2":"Warehouse No 9","locality":"San Pedro","sub_code":"US-CA","telephone":"3108339330"}}'
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
    "line1": "110 E 22nd St",
    "line2": "Warehouse No 9",
    "locality": "San Pedro",
    "dependent_locality": null,
    "sub_code": "US-CA",
    "postal_code": "90731-7202",
    "formatted": ["110 E 22nd St", "Warehouse No 9", "San Pedro, CA 90731-7202"],
    "language_code": "en",
    "telephone": "+13108339330",
    "admin_area": {
      "sub_code": "US-CA",
      "name": "California",
      "type": "state"
    }
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

<h3 id="location-update">Update a Location (PUT)</h3>

<p>To replace a location, send a <strong>PUT</strong> request to <code>/location/{location_id}</code>. Send every required field. Any optional field you leave out is cleared. If the location does not exist it is created, with a <var>201 Created</var> response. A successful request returns the <a href="#location-object">location object</a>.</p>

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
                <td>The brewer this location belongs to.</td>
            </tr>
            <tr>
                <td><var>name</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The location’s own name. Like any optional field on a PUT, leaving it out clears it.</td>
            </tr>
            <tr>
                <td><var>country_code</var></td>
                <td>string</td>
                <td>The country, as an ISO 3166–1 alpha-2 code, from the <a href="#location-countries">supported list</a>.</td>
            </tr>
            <tr>
                <td><var>address</var></td>
                <td>object</td>
                <td>The street address; see <a href="#address-request">The Address Object › In a request</a>. It replaces the stored address entirely: any field you leave out is cleared.</td>
            </tr>
            <tr>
                <td><var>url</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>A page for this location. Cleared if left out.</td>
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
  -d '{"brewer_id":"9f96b99b-9865-4cf0-8950-bdc0d675be9f","name":"San Pedro","country_code":"US","address":{"line1":"110 E 22nd St","line2":"Warehouse No 9","locality":"San Pedro","sub_code":"US-CA","telephone":"3108339330"}}'
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-patch">Update a Location (PATCH)</h3>

<p>To change part of a location, send a <strong>PATCH</strong> request to <code>/location/{location_id}</code> with only the fields you want to change. Everything else stays as it is. A successful request returns the <a href="#location-object">location object</a>.</p>

<p>You can send <var>address</var> too; its fields are in <a href="#address-request">The Address Object › In a request</a>. Only the address fields you send change. They are merged over the stored address and the result is validated as one, so <var>latitude</var> and <var>longitude</var> move with it. Send <var>null</var> for an optional field to clear it.</p>

<p>One rule to know: <var>locality</var> + <var>sub_code</var> and <var>postal_code</var> describe the same place two ways, so if you send one without the other, the stored other is dropped and worked out again from what you sent. That way an old ZIP can never contradict a new city. A patch that sends only <var>telephone</var> skips validation.</p>

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
                <td>The brewer this location belongs to.</td>
            </tr>
            <tr>
                <td><var>name</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The location’s name. Send <var>null</var> or an empty string to clear it.</td>
            </tr>
            <tr>
                <td><var>country_code</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The country, as an ISO 3166–1 alpha-2 code. To change it, send the address too, so it can be validated in the new country; see <a href="#address-request">The Address Object › In a request</a>.</td>
            </tr>
            <tr>
                <td><var>url</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>A page for this location.</td>
            </tr>
            <tr>
                <td><var>address</var><br><small class="text-muted">(optional)</small></td>
                <td>object</td>
                <td>The street address, merged over the stored one; see <a href="#address-request">The Address Object › In a request</a>. Leave it out to keep the stored address. It can’t be removed, so <var>null</var> is refused.</td>
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

<p>To delete a location, send a <strong>DELETE</strong> request to <code>/location/{location_id}</code>. No body is needed. A successful request returns <var>204 No Content</var>. Only brewery staff and administrators can delete; check <a href="#brewer-permissions">your permissions</a> for the brewer first.</p>

<pre class="api-code">DELETE https://api.catalog.beer/location/{location_id}</pre>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X DELETE \
  https://api.catalog.beer/location/9c6a2a29-6c1d-4fdc-ba34-b9c7e2c1e14b \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<p><a href="#top">^ Return to top</a></p>

<h3 id="location-retrieve">Retrieve a Location</h3>

<p>To get one location, send a <strong>GET</strong> request to <code>/location/{location_id}</code>.</p>

<pre class="api-code">GET https://api.catalog.beer/location/{location_id}</pre>

<p>It returns the <a href="#location-object">location object</a>.</p>

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

<p>To list every location, send a <strong>GET</strong> request to <code>/location</code>.</p>

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
                <td>Where to start. Pass the <var>next_cursor</var> value from the previous page.</td>
            </tr>
            <tr>
                <td><var>count</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How many results to return. Default 500.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Response</h4>
<p>The response is a list object:</p>

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
                <td>The type of object, in this case “list”.</td>
            </tr>
            <tr>
                <td><var>url</var></td>
                <td>string</td>
                <td>The endpoint: <code>/location</code>.</td>
            </tr>
            <tr>
                <td><var>has_more</var></td>
                <td>Boolean</td>
                <td><var>true</var> when there are more results after this page.</td>
            </tr>
            <tr>
                <td><var>next_cursor</var></td>
                <td>string</td>
                <td>Pass this as <var>cursor</var> to get the next page.</td>
            </tr>
            <tr>
                <td><var>data</var></td>
                <td>array</td>
                <td>Every location, sorted by name. Each entry has <var>id</var>, <var>name</var> and <var>last_modified</var>.</td>
            </tr>
            <tr>
                <td><var>id</var></td>
                <td>string</td>
                <td>The location’s id.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The location’s name, or <var>null</var>. If you need a title, build one from the brewer name and the town.</td>
            </tr>
            <tr>
                <td><var>last_modified</var></td>
                <td>integer</td>
                <td>When the location last changed, as a Unix timestamp.</td>
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

<p>The question people ask most is “where is the nearest brewery?” This endpoint answers it.</p>

<p>Send a <strong>GET</strong> request to <code>/location/nearby</code> with <var>latitude</var> and <var>longitude</var> as query parameters.</p>

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
                <td>The latitude of the center of your search.</td>
            </tr>
            <tr>
                <td><var>longitude</var></td>
                <td>float</td>
                <td>The longitude of the center of your search.</td>
            </tr>
            <tr>
                <td><var>search_radius</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How far to search from the center. Default 25, in the units set by <var>metric</var>.</td>
            </tr>
            <tr>
                <td><var>metric</var><br><small class="text-muted">(optional)</small></td>
                <td>boolean</td>
                <td><var>true</var> for kilometers, <var>false</var> for miles. Default <var>false</var>.</td>
            </tr>
            <tr>
                <td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>Where to start. Pass the <var>next_cursor</var> value from the previous page.</td>
            </tr>
            <tr>
                <td><var>count</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How many results to return. Default 100.</td>
            </tr>
        </tbody>
    </table>
</div>

<p>A sample request with query parameters.</p>

<pre class="api-code">GET https://api.catalog.beer/location/nearby?latitude={latitude}&amp;longitude={longitude}</pre>

<h4>Response</h4>
<p>The response is a list object:</p>

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
                <td>The type of object, in this case “list”.</td>
            </tr>
            <tr>
                <td><var>url</var></td>
                <td>string</td>
                <td>The endpoint: <code>/location/nearby</code>.</td>
            </tr>
            <tr>
                <td><var>has_more</var></td>
                <td>Boolean</td>
                <td><var>true</var> when there are more results after this page.</td>
            </tr>
            <tr>
                <td><var>next_cursor</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>Pass this as <var>cursor</var> to get the next page. Only present when <var>has_more</var> is <var>true</var>.</td>
            </tr>
            <tr>
                <td><var>data</var></td>
                <td>array</td>
                <td>
                    <p>The locations in range. Each result has three objects:</p>
                    <ul>
                        <li><var>location</var>: <var>id</var>, <var>object</var>, <var>name</var>, <var>brewer_id</var>, <var>url</var>, <var>country_code</var>, <var>country_short_name</var>, <var>latitude</var>, <var>longitude</var> and the nested <a href="#location-address"><var>address</var></a></li>
                        <li><var>distance</var>, described below</li>
                        <li><var>brewer</var>: <var>id</var>, <var>object</var>, <var>name</var>, <var>description</var>, <var>short_description</var>, <var>url</var>, <var>status</var>, <var>founded_year</var>, <var>closed_year</var>, <var>country_code</var>, <var>cb_verified</var>, <var>brewer_verified</var></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td><var>distance</var></td>
                <td>float</td>
                <td>The straight-line distance from your point to the location, to one decimal place.</td>
            </tr>
            <tr>
                <td><var>units</var></td>
                <td>string</td>
                <td>“miles” or “kilometers”, following <var>metric</var>. The default here is miles; the postal code and city lookups follow the country.</td>
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
        "address": {
          "line1": "3812 Grim Ave",
          "line2": null,
          "locality": "San Diego",
          "dependent_locality": null,
          "sub_code": "US-CA",
          "postal_code": "92104-3602",
          "formatted": ["3812 Grim Ave", "San Diego, CA 92104-3602"],
          "language_code": "en",
          "telephone": "+16192557136",
          "admin_area": {
            "sub_code": "US-CA",
            "name": "California",
            "type": "state"
          }
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
        "status": "active",
        "founded_year": null,
        "closed_year": null,
        "country_code": "US",
        "cb_verified": true,
        "brewer_verified": false
      }
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- LOCATION: ZIP CODE ----->

<h3 id="location-zip">Find Locations by Postal Code</h3>

<p>Find locations near a postal code. The code is geocoded within its country, and the search then works like <a href="#nearby-locations">Find Nearby Locations</a>.</p>

<p>Send a <strong>GET</strong> request to <code>/location/zip</code> with <var>postal_code</var>, plus <var>country</var> for anywhere outside the US.</p>

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
                <td><var>postal_code</var></td>
                <td>string</td>
                <td>A postal code as the country writes it.</td>
            </tr>
            <tr>
                <td><var>country</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The country of the postal code, as an ISO 3166–1 alpha-2 code. Default <var>US</var>.</td>
            </tr>
            <tr>
                <td><var>search_radius</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How far to search from the postal code. Default 25, in the units set by <var>metric</var>.</td>
            </tr>
            <tr>
                <td><var>metric</var><br><small class="text-muted">(optional)</small></td>
                <td>boolean</td>
                <td><var>true</var> for kilometers, <var>false</var> for miles. If left out, miles in the US and kilometers everywhere else.</td>
            </tr>
            <tr>
                <td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>Where to start. Pass the <var>next_cursor</var> value from the previous page.</td>
            </tr>
            <tr>
                <td><var>count</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How many results to return. Default 100.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Response</h4>
<p>The response is the same as <a href="#nearby-locations">Find Nearby Locations</a>; <var>url</var> is <code>/location/zip</code>.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location/zip?postal_code=92104&amp;search_radius=10&amp;count=1' \
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
        "address": {
          "line1": "3000 Upas St",
          "line2": null,
          "locality": "San Diego",
          "dependent_locality": null,
          "sub_code": "US-CA",
          "postal_code": "92104-4221",
          "formatted": ["3000 Upas St", "San Diego, CA 92104-4221"],
          "language_code": "en",
          "telephone": "+16192695222",
          "admin_area": {
            "sub_code": "US-CA",
            "name": "California",
            "type": "state"
          }
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
        "status": "active",
        "founded_year": null,
        "closed_year": null,
        "country_code": "US",
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

<p>Find locations near a city. The city is geocoded within its country, and the search then works like <a href="#nearby-locations">Find Nearby Locations</a>.</p>

<p>Send a <strong>GET</strong> request to <code>/location/city</code> with <var>city</var>, plus <var>region</var> in the US and <var>country</var> anywhere else.</p>

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
                <td>The city to search around.</td>
            </tr>
            <tr>
                <td><var>region</var><br><small class="text-muted">(required in the US, optional elsewhere)</small></td>
                <td>string</td>
                <td>The state, province or region, by name or abbreviation. Required in the US, where a city name alone could be in any of fifty states. Optional elsewhere.</td>
            </tr>
            <tr>
                <td><var>country</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The country of the city, as an ISO 3166–1 alpha-2 code. Default <var>US</var>.</td>
            </tr>
            <tr>
                <td><var>search_radius</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How far to search from the city. Default 25, in the units set by <var>metric</var>.</td>
            </tr>
            <tr>
                <td><var>metric</var><br><small class="text-muted">(optional)</small></td>
                <td>boolean</td>
                <td><var>true</var> for kilometers, <var>false</var> for miles. If left out, miles in the US and kilometers everywhere else.</td>
            </tr>
            <tr>
                <td><var>cursor</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>Where to start. Pass the <var>next_cursor</var> value from the previous page.</td>
            </tr>
            <tr>
                <td><var>count</var><br><small class="text-muted">(optional)</small></td>
                <td>integer</td>
                <td>How many results to return. Default 100.</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Response</h4>
<p>The response is the same as <a href="#nearby-locations">Find Nearby Locations</a>; <var>url</var> is <code>/location/city</code>.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location/city?city=San%20Diego&amp;region=CA&amp;search_radius=10&amp;count=1' \
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
        "address": {
          "line1": "627 8th Ave",
          "line2": null,
          "locality": "San Diego",
          "dependent_locality": null,
          "sub_code": "US-CA",
          "postal_code": "92101-6453",
          "formatted": ["627 8th Ave", "San Diego, CA 92101-6453"],
          "language_code": "en",
          "telephone": "+16197701844",
          "admin_area": {
            "sub_code": "US-CA",
            "name": "California",
            "type": "state"
          }
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
        "status": "active",
        "founded_year": null,
        "closed_year": null,
        "country_code": "US",
        "cb_verified": true,
        "brewer_verified": false
      }
    }
  ]
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- LOCATION: COUNTRIES ----->

<h3 id="location-countries">Supported Countries</h3>

<p>A location’s <var>country_code</var> must be one of the countries the validator covers. To list them, send a <strong>GET</strong> request to <code>/location/countries</code>. Each entry has the country’s name, how many <a href="#location-subdivisions">subdivisions</a> the catalog holds for it (zero means there is no region list to offer) and its telephone calling code.</p>

<pre class="api-code">GET https://api.catalog.beer/location/countries</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/location/countries",
  "has_more": false,
  "data": [
    {
      "country_code": "AR",
      "country_short_name": "Argentina",
      "subdivisions": 24,
      "calling_code": "+54"
    },
    {
      "country_code": "AT",
      "country_short_name": "Austria",
      "subdivisions": 9,
      "calling_code": "+43"
    },
    {
      "country_code": "BE",
      "country_short_name": "Belgium",
      "subdivisions": 13,
      "calling_code": "+32"
    }
  ]
}
</pre>

<p><small class="text-muted">Response truncated for documentation — the list holds 40 countries: AR, AT, AU, BE, BG, BR, CA, CH, CL, CO, CZ, DE, DK, EE, ES, FI, FR, GB, HR, HU, IE, IN, IT, JP, LT, LU, LV, MX, MY, NL, NO, NZ, PL, PR, PT, SE, SG, SI, SK and US.</small></p>

<p><a href="#top">^ Return to top</a></p>

<!----- LOCATION: SUBDIVISIONS ----->

<h3 id="location-subdivisions">Subdivisions</h3>

<p>The states, provinces and regions the catalog knows for a country, as <a href="https://www.iso.org/iso-3166-country-codes.html" target="_blank" rel="noopener">ISO 3166–2</a> codes with their names in the local language. These are the values <var>sub_code</var> accepts. To list them, send a <strong>GET</strong> request to <code>/location/subdivisions</code> with the <var>country</var> query parameter.</p>

<pre class="api-code">GET https://api.catalog.beer/location/subdivisions?country={country_code}</pre>

<p>Rows are in code order. Where one level sits under another, <var>parent_code</var> says so: a Belgian province under its region, a Spanish province under its autonomous community.</p>

<h4>Sample Request</h4>

<pre class="api-code">
curl -X GET \
  'https://api.catalog.beer/location/subdivisions?country=BE' \
  -H 'accept: application/json' \
  -H 'authorization: Basic {secret_key}' \
</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "list",
  "url": "/location/subdivisions",
  "country_code": "BE",
  "has_more": false,
  "data": [
    {
      "sub_code": "BE-BRU",
      "name": "Région de Bruxelles-Capitale",
      "type": "region",
      "parent_code": null
    },
    {
      "sub_code": "BE-VAN",
      "name": "Antwerpen",
      "type": "province",
      "parent_code": "BE-VLG"
    },
    {
      "sub_code": "BE-VBR",
      "name": "Vlaams-Brabant",
      "type": "province",
      "parent_code": "BE-VLG"
    }
  ]
}
</pre>

<p><small class="text-muted">Response truncated for documentation — Belgium has 13 rows: 3 regions and 10 provinces.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h2 id="styles">Styles</h2>
<hr>

<p>Catalog.beer records a beer’s style in two parts: the brewer’s own label and a canonical classification. The label (<var>beer.style</var>) is plain text, stored and returned exactly as submitted. The classification files the beer against a standard vocabulary of styles, which is what allows the catalog to be sorted, filtered, and counted by style. The vocabulary is drawn from the <a href="https://www.brewersassociation.org/edu/brewers-association-beer-style-guidelines/" target="_blank" rel="noopener">Brewers Association (BA)</a> and <a href="https://www.bjcp.org/bjcp-style-guidelines/" target="_blank" rel="noopener">Beer Judge Certification Program (BJCP)</a> style guidelines.</p>

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

<p>A beer may be filed at any tier. The API derives the broader tiers from the one you provide — file a beer as <var>west-coast-ipa</var> and its <var>parent</var> is set to <var>ipa</var> and its <var>class</var> to <var>ale</var>. The API never fills in a more specific tier than the one you gave it.</p>

<p>When <a href="#beer-create">adding</a> or <a href="#beer-patch">updating</a> a beer, there are two ways to set the classification:</p>

<ol>
    <li>Send the label as <var>style</var> and let the API match it. The label is checked against every canonical name and alias in the vocabulary — first as an exact style name, then as a class alias, a family alias, and a style alias. For example, “NEIPA,” “New England IPA,” and “Juicy IPA” all resolve to the same style.</li>
    <li>Name the classification directly with <var>style_id</var>, <var>parent</var>, or <var>class</var>. These take precedence over <var>style</var>; if you send more than one, the most specific wins. Your label is still kept verbatim.</li>
</ol>

<p>If the label doesn’t match anything in the vocabulary and no classification is given, the request returns a <var>400 Bad Request</var> error. The vocabulary includes catch-all styles (<var>catch_all: true</var> — e.g. <var>specialty-beer</var>) for beers that don’t fit a more specific style. One exception: when updating an existing beer, resending its current label unchanged never fails — the beer keeps its current classification, even if that label wouldn’t resolve on its own.</p>

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
                <td>Your label is this style’s name or one of its aliases. Safe to take as the answer.</td>
            </tr>
            <tr>
                <td><var>all_terms</var></td>
                <td>Every word in your label appears in this style’s name or aliases. Usually right; worth a glance.</td>
            </tr>
            <tr>
                <td><var>partial</var></td>
                <td>Some of your words matched. <strong>Do not take the first entry on faith.</strong> Read the list, and consider filing at <var>parent</var> or <var>class</var> instead.</td>
            </tr>
            <tr>
                <td><var>description</var></td>
                <td>Nothing matched the name or aliases — only our written description of the style. The weakest signal we return.</td>
            </tr>
        </tbody>
    </table>
</div>

<p>Within a <var>match</var> level, candidates are ordered by how well they match and then by how many beers we hold in each, so a populous style can still outrank a better-fitting rare one. That is the response above: every candidate for “Cali Pilsner” is <var>partial</var>, and the closest one, <var>contemporary-american-pilsner</var>, comes back <em>last</em>. A list that is all <var>partial</var> is the API telling you it did not recognize your label. <var>class</var> may be <var>null</var> where the family sits outside the ale/lager split.</p>

<p><var>families</var> and <var>classes</var> appear alongside <var>styles</var> when your label names a family or a super-class but no style matched it outright — “Crisp American Lager” returns the <var>lager</var> class. Filing one tier up is often the honest answer, and it is better than picking a specific style that happens to share a word with your label. Send the <var>parent</var> or <var>class</var> value back the same way you would a <var>style_id</var>.</p>

<p>A <var>matched_on</var> key appears when we could not match your whole label and fell back to the last two words of it — “Crisp American Lager” is matched on “American Lager.” The candidates describe that shorter phrase, not what you sent, so read it before trusting an <var>exact</var> on the list.</p>

<p>Retrying the example above as <code>{"style": "Cali Pilsner", "style_id": "contemporary-american-pilsner"}</code> succeeds, and the beer keeps “Cali Pilsner” as its label. Sending <var>style_id</var>, <var>parent</var>, or <var>class</var> <em>without</em> <var>style</var> stores our canonical name as the label instead of the brewery’s own wording, so send both.</p>

<p>The beer’s <var>beverage_type</var> (<var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>) is set from the resolved classification. It cannot be set directly.</p>

<h3 id="style-confidence">Style Confidence</h3>

<p>Classifying a beer sometimes means a judgment call, and those are the ones worth a second look. The optional <var>style_confidence</var> field on <a href="#beer-create">POST</a>, <a href="#beer-update">PUT</a>, and <a href="#beer-patch">PATCH</a> records how firm the classification is, so editorial review can go where it’s needed instead of everywhere. It is stored for our own review and is never returned in a beer object.</p>

<p><strong>In most cases, leave it out.</strong> We derive it from what we can check — whether your label matched the vocabulary on its own, and whether you filed under a catch-all:</p>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr><th scope="col">Value</th><th scope="col">Derived when</th></tr>
        </thead>
        <tbody>
            <tr><td><var>confident</var></td><td>Your label matched a canonical name or alias, whether or not you also named a tier.</td></tr>
            <tr><td><var>override</var></td><td>You named a specific <var>style_id</var> for a label that doesn’t match it — a mapping only you can vouch for.</td></tr>
            <tr><td><var>catch-all</var></td><td>As above, but the style you chose is a catch-all (<var>catch_all: true</var>).</td></tr>
            <tr><td><var>family</var></td><td>You filed at the family or class tier rather than at a specific style.</td></tr>
        </tbody>
    </table>
</div>

<p>Send the field yourself only to record <strong>less</strong> certainty than we would otherwise assume — for example, you inferred the style from the beer’s name rather than a stated one, or picked the least-wrong of two plausible families. Sending <var>catch-all</var> or <var>family</var> where we would have derived <var>override</var> is an honest downgrade, and we keep it.</p>

<p>You cannot use the field to claim more certainty than the request supports. Whether your label matches the vocabulary is something we check rather than take on trust, so pairing an unmatched label with <var>style_confidence: confident</var> is reduced to <var>override</var>. This happens silently — your write still succeeds.</p>

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
            <tr><td><var>id</var></td><td>string</td><td>The style’s slug — the value used as <var>style_id</var> on a beer.</td></tr>
            <tr><td><var>object</var></td><td>string</td><td>The type of object, in this case “style”.</td></tr>
            <tr><td><var>name</var></td><td>string</td><td>The canonical style name.</td></tr>
            <tr><td><var>beverage_type</var></td><td>string</td><td>One of <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>.</td></tr>
            <tr><td><var>parent</var></td><td>string</td><td>The slug of the <a href="#style-parents">family</a> this style belongs to — the canonical family tier you can file a beer at.</td></tr>
            <tr><td><var>class</var></td><td>string</td><td>The slug of the <a href="#style-classes">class</a> the style rolls up to (<var>ale</var> or <var>lager</var>), or <var>null</var> for families that do not roll up to one.</td></tr>
            <tr><td><var>parent_name</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The display name of the parent family.</td></tr>
            <tr><td><var>source</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The primary guideline the style is drawn from: <var>BA-2026</var>, <var>BJCP-2021</var>, <var>OCB-2012</var>, or <var>NABA-2024</var>.</td></tr>
            <tr><td><var>catch_all</var></td><td>Boolean</td><td><var>true</var> for non-standard “catch-all” styles (e.g. <var>specialty-beer</var>) used when nothing more specific fits. Use it to separate fallback buckets from standard styles — for example, keeping catch-alls out of a picker’s ranked matches.</td></tr>
            <tr><td><var>aliases</var></td><td>array</td><td>Other names and spellings that resolve to this style, excluding the canonical <var>name</var>. Use these to match user-entered labels client-side — for example, in a typeahead — without an API request per keystroke.</td></tr>
            <tr><td><var>srm</var><br><small class="text-muted">(list only)</small></td><td>object</td><td>The style’s <a href="https://en.wikipedia.org/wiki/Standard_Reference_Method" target="_blank" rel="noopener">SRM</a> color range as a <code>{ "min": …, "max": … }</code> object, or <var>null</var> when the guideline gives no color (cider, mead, perry, and catch-alls). The one spec included in list rows — enough to render color swatches without a request per style. On the detail endpoint the same range lives in <var>specs.srm</var>.</td></tr>
            <tr><td><var>specs</var><br><small class="text-muted">(detail only)</small></td><td>object</td><td>The style’s guideline ranges — <var>abv</var>, <var>ibu</var>, <var>srm</var>, <var>og</var>, and <var>fg</var>. See <a href="#style-specs">the <var>specs</var> field</a> below.</td></tr>
            <tr><td><var>description</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>An editorial description of the style — what it is and what to expect in the glass. Plain prose; paragraphs separated by blank lines.</td></tr>
            <tr><td><var>appearance</var>, <var>aroma</var>, <var>flavor</var>, <var>mouthfeel</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>Short tasting-note fields describing the style in the glass. <var>null</var> for catch-all styles, which have no fixed sensory profile.</td></tr>
            <tr><td><var>history</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>The style’s origin and evolution — editorial prose with claims anchored to the citations in <var>sources.history_sources</var>.</td></tr>
            <tr><td><var>notes</var><br><small class="text-muted">(detail only)</small></td><td>string</td><td>Occasional editorial notes — e.g. how this entry relates to neighboring styles. <var>null</var> when there’s nothing to add.</td></tr>
            <tr><td><var>commercial_examples</var><br><small class="text-muted">(detail only)</small></td><td>array</td><td>Classic or defining commercial examples of the style, as beer names (strings). Curated — not derived from the Catalog.beer database.</td></tr>
            <tr><td><var>sources</var><br><small class="text-muted">(detail only)</small></td><td>object</td><td>Provenance: the style’s entry in each guideline it appears in (<var>brewers_association</var>, <var>bjcp</var> with year and code, <var>naba_2024</var>; the one marked <code>"primary": true</code> supplies the canonical name), plus <var>history_sources</var> — an array of <code>{ "citation", "url" }</code> objects backing the <var>history</var> prose.</td></tr>
        </tbody>
    </table>
</div>

<h4 id="style-specs">The <var>specs</var> Field</h4>

<p>The <var>specs</var> field (detail only) holds the style’s guideline ranges, drawn from the same <var>source</var> as the style. Each field is a <code>{ "min": …, "max": … }</code> object, or <var>null</var> when the guideline doesn’t specify that measurement. An individual <var>min</var> or <var>max</var> may also be <var>null</var> when the guideline gives only one bound.</p>

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
            <tr><td><var>ibu</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Beer_measurement#Bitterness" target="_blank" rel="noopener">International Bitterness Units</a> range — the measure of hop bitterness.<br><span class="text-muted"><var>min</var> and <var>max</var> are integers.</span></td></tr>
            <tr><td><var>srm</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Standard_Reference_Method" target="_blank" rel="noopener">Standard Reference Method</a> range — beer color, from pale straw (low) to black (high).<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
            <tr><td><var>og</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Gravity_(alcoholic_beverage)" target="_blank" rel="noopener">Original Gravity</a> range — the wort’s specific gravity before fermentation.<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
            <tr><td><var>fg</var></td><td>object</td><td><a href="https://en.wikipedia.org/wiki/Gravity_(alcoholic_beverage)" target="_blank" rel="noopener">Final Gravity</a> range — the beer’s specific gravity after fermentation.<br><span class="text-muted"><var>min</var> and <var>max</var> are floats.</span></td></tr>
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
  "description": "The defining style of American craft beer — medium-bodied, golden to copper-colored, and built around the tropical, citrus, pine, and resinous character of American hops. […]",
  "appearance": "Gold to deep copper, clear to slightly hazy, with a long-lasting off-white head.",
  "aroma": "Prominent American or New World hop character — citrus, grapefruit, pine, tropical fruit, stone fruit, or resinous notes. […]",
  "flavor": "Medium to high hop bitterness with matching hop flavor. […]",
  "mouthfeel": "Medium body, medium carbonation. Smooth without being heavy; alcohol is not prominent.",
  "history": "The American IPA lineage begins with the post-Prohibition remnants of the older East Coast tradition. […]",
  "notes": "The clear West Coast and soft Hazy interpretations are now treated as separate entries in the catalog […]",
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

<p><small class="text-muted">Prose fields truncated for documentation ([…]) — the live response returns the full text.</small></p>

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

<p><small class="text-muted">Response truncated for documentation — the full response contains every style in the vocabulary.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-detail">Retrieve a Style</h3>

<p>To retrieve a single style, send a <strong>GET</strong> request to the <code>/style</code> endpoint with the <var>{style_id}</var> parameter appended to the path. The <var>style_id</var> is the style’s slug (e.g. <var>american-ipa</var>), not a UUID. Successful requests return a full <a href="#style-object">style object</a>, including its <a href="#style-specs">specs</a>.</p>

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
  "description": "The defining style of American craft beer — medium-bodied, golden to copper-colored, and built around the tropical, citrus, pine, and resinous character of American hops. […]",
  "appearance": "Gold to deep copper, clear to slightly hazy, with a long-lasting off-white head.",
  "aroma": "Prominent American or New World hop character — citrus, grapefruit, pine, tropical fruit, stone fruit, or resinous notes. […]",
  "flavor": "Medium to high hop bitterness with matching hop flavor. […]",
  "mouthfeel": "Medium body, medium carbonation. Smooth without being heavy; alcohol is not prominent.",
  "history": "The American IPA lineage begins with the post-Prohibition remnants of the older East Coast tradition. […]",
  "notes": "The clear West Coast and soft Hazy interpretations are now treated as separate entries in the catalog […]",
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

<p><small class="text-muted">Prose fields truncated for documentation ([…]) — the live response returns the full text.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-search">Search Styles</h3>

<p>Search the style vocabulary by canonical name, alias, or description using full-text search. To search, send a <strong>GET</strong> request to the <code>/style/search</code> endpoint with a <var>q</var> query parameter. Alias matches are first-class: a search for “NEIPA” or “Juicy IPA” finds <var>hazy-ipa</var> even though neither term appears in the canonical name.</p>

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
    <li><strong>Exact match</strong> — the query is exactly the style’s name or one of its aliases. Searching “American IPA” puts <var>american-ipa</var> first.</li>
    <li><strong>Full name match</strong> — every term in the query appears in the style’s name or aliases. Aliases count as part of the name, which is why “NEIPA” finds <var>hazy-ipa</var> and “IPA” finds styles whose names spell out “India Pale Ale”.</li>
    <li><strong>Partial name match</strong> — some but not all query terms appear in the name or aliases.</li>
    <li><strong>Description match</strong> — the query appears only in the style’s editorial description. A style that merely mentions a term never outranks one named for it.</li>
</ol>

<p>Within a tier, styles are ordered by how many cataloged beers use them, so a search for a broad term surfaces styles that are actually brewed ahead of ones that exist only in the vocabulary. This ordering applies only between styles that match the query equally well — a precise match on a rare style always outranks a partial match on a common one.</p>

<p>Broad queries often have no single correct style answer — “IPA” is not one style but twelve. When a query exactly names a style family, by slug, name, or alias, that family is returned separately in <var>families</var> so you can offer the group rather than an arbitrary member of it. Searching “ipa” returns the <var>ipa</var> family alongside the individual IPA styles.</p>

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
                <td>The type of object, in this case “list”.</td>
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
                <td>An array of <a href="#style-parents">style family objects</a> whose slug, name, or alias <em>exactly</em> matches the query. Empty when no family matches. Families are never paginated — there are only 26 in total, and <var>has_more</var> and <var>next_cursor</var> describe <var>data</var> only. They are returned on the first page only, so an empty array on a later page means “not repeated here”, not “no family matched”. These rows omit <var>aliases</var>; use <a href="#style-parents">List Style Families</a> for the full object.</td>
            </tr>
            <tr>
                <td><var>data</var></td>
                <td>array</td>
                <td>An array of compact <a href="#style-object">style objects</a> matching the search query, in ranked order — the same shape as <a href="#style-list">List Styles</a> rows, including <var>aliases</var> and <var>srm</var>.</td>
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

<h4>Sample Response — Broad Query</h4>

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
                <td>The family’s slug — the value used as <var>parent</var> on a beer.</td>
            </tr>
            <tr>
                <td><var>object</var></td>
                <td>string</td>
                <td>The type of object, in this case “style_parent”.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The name of the family.</td>
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
                <td>The family’s position in display order.</td>
            </tr>
            <tr>
                <td><var>aliases</var></td>
                <td>array</td>
                <td>Names and spellings that resolve to this family when sent as a beer’s <var>style</var>.</td>
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

<p><small class="text-muted">Response truncated for documentation — the full response contains every family.</small></p>

<p><a href="#top">^ Return to top</a></p>

<h3 id="style-classes">List Classes</h3>

<p>To list the classes in the vocabulary — currently <var>ale</var> and <var>lager</var> — send a <strong>GET</strong> request to the <code>/style/class</code> endpoint. Classes are returned in display order. Each class object has the following parameters.</p>

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
                <td>The class’s slug — the value used as <var>class</var> on a beer.</td>
            </tr>
            <tr>
                <td><var>object</var></td>
                <td>string</td>
                <td>The type of object, in this case “style_class”.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The name of the class.</td>
            </tr>
            <tr>
                <td><var>beverage_type</var></td>
                <td>string</td>
                <td>One of <var>beer</var>, <var>cider</var>, <var>perry</var>, or <var>mead</var>.</td>
            </tr>
            <tr>
                <td><var>sort_order</var></td>
                <td>integer</td>
                <td>The class’s position in display order.</td>
            </tr>
            <tr>
                <td><var>aliases</var></td>
                <td>array</td>
                <td>Names and spellings that resolve to this class when sent as a beer’s <var>style</var>.</td>
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

<!---------- USAGE ---------->

<h2 id="usage">Usage</h2>
<hr>

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
                <td>The type of object, in this case “usage”.</td>
            </tr>
            <tr>
                <td><var>year</var></td>
                <td>integer</td>
                <td>The year of the current billing period.</td>
            </tr>
            <tr>
                <td><var>month</var></td>
                <td>integer</td>
                <td>The month of the current billing period (1–12).</td>
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

<p>The response does not include the API key itself—you authenticated with it, and nothing in the usage or <a href="#billing-object">billing</a> objects will echo it back.</p>

<p><a href="#top">^ Return to top</a></p>

<!----- USAGE: MY USAGE ----->

<h3 id="usage-my-usage">Get My Usage</h3>

<p>To retrieve your current API usage and limits, send a <strong>GET</strong> request to the <code>/usage/my-usage</code> endpoint. This returns your usage for the current billing period. No special permissions are required—the endpoint returns data for the authenticated API key.</p>

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
  "year": 2026,
  "month": 3,
  "count": 142,
  "request_limit": 1000,
  "request_buffer": 50,
  "resets_on": "2026-04-01",
  "last_updated": 1741723456
}
</pre>

<h4>Usage Limits</h4>

<p>When your usage exceeds your <var>request_limit</var> + <var>request_buffer</var>, the API will return a <var>402 Payment Required</var> response for all endpoints except <code>/usage</code> and <code>/billing</code>. Your count resets on the first of each month. To keep going past the free tier, add a payment method—see <a href="#billing">Billing</a>.</p>

<p>Note that this is a monthly allowance, not a rate limit: there is no per-second or per-minute throttle, and retrying a <var>402</var> will not clear it. Either add a payment method or wait for the monthly reset.</p>

<p><a href="#top">^ Return to top</a></p>

<!---------- BILLING ---------->

<h2 id="billing">Billing</h2>
<hr>

<p>Every account includes 1,000 free API requests per month. With a payment method on file, your key keeps working past the free tier and usage is billed at <strong>$1 per 1,000 requests</strong>, rounded up to whole blocks of 1,000. Billing is powered by Stripe: cards are collected on a Stripe-hosted checkout page and never touch Catalog.beer’s servers. Add or manage your payment method on your <a href="/billing">Billing page</a>; the endpoints below let you check your status and manage your spend cap programmatically.</p>

<p>A few things to know:</p>

<ul>
    <li><strong>Invoicing.</strong> Overage is invoiced on the first of the following month and charged automatically to your saved card. Balances under $5 roll forward to a later invoice rather than being charged immediately.</li>
    <li><strong>Spend cap.</strong> Each key has a monthly spend cap ($50 by default) as protection against runaway usage. Once the month’s usage would cost more than the cap, further requests receive a <var>402</var> response until the month resets. You can adjust the cap between $1 and $1,000, or set it to $0 to block all paid usage.</li>
    <li><strong>Exempt endpoints.</strong> The <code>/billing</code> and <code>/usage</code> endpoints are never blocked by the usage limit and don’t count toward your usage, so you can always check your status and manage billing—even when your key is over its cap.</li>
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
                <td>The type of object, in this case “billing”.</td>
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
                <td>What this month’s usage would cost so far, in cents ($1 per 1,000 billable requests, rounded up, capped at your spend cap). <code>0</code> when billing is not enabled.</td>
            </tr>
            <tr>
                <td><var>unbilled_balance_cents</var></td>
                <td>integer</td>
                <td>Charges from earlier months that haven’t been invoiced yet because they’re under the $5 invoice minimum.</td>
            </tr>
        </tbody>
    </table>
</div>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: RETRIEVE ----->

<h3 id="billing-retrieve">Get Billing Status</h3>

<p>To retrieve your billing status, send a <strong>GET</strong> request to the <code>/billing</code> endpoint. No special permissions are required—the endpoint returns data for the authenticated API key.</p>

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
  "billing_enabled": true,
  "monthly_spend_cap_cents": 10000
}
</pre>

<p><a href="#top">^ Return to top</a></p>

<!----- BILLING: DISABLE ----->

<h3 id="billing-disable">Disable Billing</h3>

<p>To turn off billing and return your key to the free tier, send a <strong>DELETE</strong> request to the <code>/billing</code> endpoint. Your card stays saved with Stripe, and usage already accrued past the free tier will still be invoiced. To re-enable billing, add a payment method again from your <a href="/billing">billing page</a>.</p>

<pre class="api-code">DELETE https://api.catalog.beer/billing</pre>

<h4>Sample Response</h4>

<pre class="api-code">
{
  "object": "billing",
  "billing_enabled": false,
  "monthly_spend_cap_cents": 5000
}
</pre>

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
                <td>The type of object, in this case “users”.</td>
            </tr>
            <tr>
                <td><var>name</var></td>
                <td>string</td>
                <td>The user’s name.</td>
            </tr>
            <tr>
                <td><var>email</var></td>
                <td>string</td>
                <td>The user’s email address.</td>
            </tr>
            <tr>
                <td><var>email_verified</var></td>
                <td>Boolean</td>
                <td>A <var>true</var> or <var>false</var> value denoting whether the user’s email address has been verified.</td>
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
                <td>The type of object, in this case “api_key”.</td>
            </tr>
            <tr>
                <td><var>user_id</var></td>
                <td>string</td>
                <td>The user_id associated with the API key.</td>
            </tr>
            <tr>
                <td><var>api_key</var></td>
                <td>string/null</td>
                <td>The API key, or <var>null</var> if the user’s email has not been verified.</td>
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
                <td>The user’s name. Max 255 characters.</td>
            </tr>
            <tr>
                <td><var>email</var><br><small class="text-muted">(optional)</small></td>
                <td>string</td>
                <td>The user’s email address. Changing your email to a different domain will reset your email verification status and any brewery staff privileges associated with the previous domain.</td>
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

<p>To request a password reset, send a <strong>POST</strong> request to the <code>/users/{user_id}/reset-password</code> endpoint. The user’s email must be verified. A password reset email will be sent to the email address on file. This endpoint is rate limited to one request per 15 minutes; requests made too soon return a <var>429 Too Many Requests</var> response with a <var>Retry-After</var> header giving the seconds remaining. Successful requests return a <var>204 No Content</var> response.</p>

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
