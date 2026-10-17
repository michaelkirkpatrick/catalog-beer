<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Claim Your Brewery');
$htmlHead->addDescription('Brewery staff can keep their brewery, beers, and taprooms up to date on Catalog.beer. Sign up with your brewery email address and your edits carry a Verified by the brewer mark.');
echo $htmlHead->html;
?>
<style>
    h2 {
        margin-top: 4rem;
    }
    h3 {
        margin-top: 3rem;
    }
</style>
<body>
    <?php
    // Navbar
    echo $nav->navbar('');
    ?>
    <div class="cb-page cb-page--narrow">
        <h1>Claim Your Brewery</h1>

        <p class="lead">Catalog.beer is an open database of breweries, beers, and taprooms that apps, websites, and AI assistants use to answer questions about beer. If you work at a brewery, you can make sure what they say about you is right.</p>

        <h2>How to claim your brewery</h2>

        <p>There’s no form to fill out and nothing to pay. Catalog.beer recognizes brewery staff by email address:</p>

        <ol>
            <li><strong>Check your website.</strong> Find your brewery on Catalog.beer and make sure the website on file is yours. We match staff to breweries using that website’s domain.</li>
            <li><strong>Sign up with your brewery email.</strong> <a href="/signup">Create an account</a> using an email address on the same domain—if your website is <var>yourbrewery.com</var>, sign up as <var>you@yourbrewery.com</var>.</li>
            <li><strong>Verify your email.</strong> Click the link we send you. That’s it: your account is now staff for your brewery.</li>
        </ol>

        <p>Anyone else at your brewery with an email address on the same domain can do the same. If your brewery shares a website with a parent company or sister brands, staff with that domain’s email addresses are staff for each brewery that uses it.</p>

        <h3>No brewery email? No website on file?</h3>

        <p>If you use a personal email address, your brewery isn’t on Catalog.beer yet, or the website on file is missing or wrong, <a href="/contact">contact us</a>. We’ll confirm you work there and set you up by hand.</p>

        <h2>What staff can do</h2>

        <ul>
            <li><strong>Keep your profile current.</strong> Edit your brewery’s description, website, founding year, and whether you’re open or closed.</li>
            <li><strong>Manage your beers and taprooms.</strong> Add new beers and locations, update existing ones, and delete the ones that don’t belong.</li>
            <li><strong>Mark records as verified.</strong> Anything you add or edit is marked <em>Verified by the brewer</em> on the site and in the API, so the people and apps using it know the information came from you.</li>
            <li><strong>Protect what you’ve verified.</strong> Once a record is verified, only your brewery’s staff and Catalog.beer can change it.</li>
        </ul>

        <p>You can do all of this on the website, or through the <a href="/api-docs">API</a>—including with an <a href="/ai">AI assistant</a> using your API key, if you’d rather tell it what’s on tap than type it in yourself.</p>

        <h2>What claiming doesn’t mean</h2>

        <ul>
            <li><strong>The data stays open.</strong> Everything on Catalog.beer, including what you add, is published under a <a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 license</a>, so anyone can use it. That’s the point: keep it accurate here and it’s accurate everywhere it’s used.</li>
            <li><strong>Your name and brands stay yours.</strong> The license covers the data, not your trademarks. Listing your brewery doesn’t give anyone the right to use your name or logo, or imply that you endorse them.</li>
            <li><strong>Catalog.beer still reviews.</strong> Claiming lets you maintain your records, not own them. We may correct information that’s wrong, and we may change or remove staff access if it’s misused.</li>
        </ul>

        <h2>Good to know</h2>

        <ul>
            <li>Staff access comes from your email address. If you change your account’s email to a different domain—or leave the brewery and change it to a personal address—staff access ends with it.</li>
            <li>By using staff access, you’re confirming that your brewery has authorized you to maintain its information. See Section 5 of our <a href="/terms">Terms of Use</a>.</li>
            <li>Questions? <a href="/contact">Get in touch</a>. We’re happy to help.</li>
        </ul>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
