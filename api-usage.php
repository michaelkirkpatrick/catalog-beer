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

                <p class="lead">The Catalog.beer Application Programming Interface (API) has been designed to make it easy for you to access information about Brewers and their beers and tasting rooms.</p>

                <p>When it comes to using the API, anyone is welcome to use it; all you have to do is <a href="/signup">create an account</a> and verify your email address. If you have questions about how to use the API, refer to our <a href="/api-docs">API Documentation</a> or <a href="/contact">drop us a line</a>.</p>

                <h2>Pricing</h2>

                <p>Catalog.beer was created to enable anyone to access brewery and beer information. Whether you want to poke around, contribute to the database, or build an application, we want to make it easy to get started.</p>

                <h3>Free Tier</h3>

                <p>Every Catalog.beer account includes <strong>1,000 API requests per month</strong> at no cost. This resets on the first of each month. For most hobbyists, students, and personal projects, this is more than enough to build something cool.</p>

                <h3>Need More?</h3>

                <p>If you&#8217;re building something that requires more than 1,000 requests per month, we&#8217;d love to hear about it. Reach out to us via our <a href="/contact">contact form</a> or email us at michael@catalog.beer and we&#8217;ll work something out. Our goal is to cover our operating costs while keeping the API accessible.</p>

                <h2>Basic Rules</h2>

                <h3>Creative Commons License</h3>

                <p>All the content that is accessible via the API is licensed under a <a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International license (CC BY 4.0)</a>. This excludes a Brewery&#8217;s rights in its name, brand(s), and trademarks. What does that mean?</p>

                <h4>You are free to</h4>

                <ul>
                    <li><strong>Share</strong> — copy and redistribute the material in any medium or format</li>
                    <li><strong>Adapt</strong> — remix, transform, and build upon the material
                for any purpose, even commercially.</li>
                </ul>

                <h4>What&#8217;s required of you?</h4>

                <p>You must give appropriate credit to Catalog.beer, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.</p>

                <p>For example, if you reproduce the description for <a href="375289f8-6cc5-2d38-3ce7-73ab50b8dff4">Long Beach Beer Lab</a>, a brewer, you should include an attribution like this:</p>

                <p>&#8220;<a href="375289f8-6cc5-2d38-3ce7-73ab50b8dff4">Long Beach Beer Lab</a>&#8221; by <a href="https://catalog.beer">Catalog.beer</a> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a></p>

                <h3>Use the API Responsibly</h3>

                <p>A few guidelines and notes:</p>

                <ul>
                <li>We may delete old accounts (more than a year old) without notice if we see that you haven&#8217;t used Catalog.beer in more than a year.</li>
                <li>If you have questions, ask them. We respond quickly to messages sent via our website.</li>
                </ul>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
