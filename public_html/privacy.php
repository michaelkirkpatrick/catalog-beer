<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Privacy Policy');
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
    <div class="cb-page cb-page--narrow">
        <h1>Privacy Policy</h1>

        <?php // ACTION REQUIRED: same effective date as /terms. ?>
        <p>Effective [EFFECTIVE DATE]</p>

        <p>Your privacy is important to us at Catalog.beer. This Privacy Policy explains what information we collect, how we use it, who we share it with, and the choices you have. Catalog.beer is operated by Michael Eason Kirkpatrick, an individual doing business as Catalog.beer, in San Diego, California; “Catalog.beer,” “we,” “us,” and “our” refer to him in that capacity, as in our <a href="/terms">Terms of Use</a>. Please <a href="/contact">let us know</a> if you have any questions.</p>

        <h2>Information We Collect</h2>
        <hr>

        <h3>Information you give us</h3>

        <ul>
            <li><strong>Account information.</strong> When you create an account, we collect your name, your email address, and a password. Your password is stored only as a one-way hash, so we can check it but cannot read it.</li>
            <li><strong>Brewery staff access.</strong> If your email address is on the same domain as a brewery’s website, or we grant you staff access manually, we record that your account is staff for that brewery. See <a href="/claim-your-brewery">Claim Your Brewery</a>.</li>
            <li><strong>Billing information.</strong> If you turn on billing for the API, you enter your card details on Stripe’s checkout page, not ours, and Stripe stores them. We keep the Stripe customer identifier for your account and a record of your monthly billable usage, charges, and invoices. When you view your Billing page, we ask Stripe for your card’s brand, last four digits, and expiration date to show them to you; we do not store them.</li>
            <li><strong>Contributions.</strong> Brewer, beer, location, and address records you add or edit are published to everyone under a <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a> license, as described in our <a href="/terms">Terms of Use</a>. We do not publish your name or email address with your Contributions. Do not put personal information in a Contribution.</li>
            <li><strong>Messages.</strong> When you use our contact form, we collect your name, email address, and message, and deliver them to our inbox by email.</li>
        </ul>

        <h3>Information we collect automatically</h3>

        <ul>
            <li><strong>API request logs.</strong> For each API request, we log the API key used, the IP address it came from, the time, the method and address requested, the request body, and our response. We use these logs to troubleshoot, to investigate abuse, and to see which account made a change. They are deleted after three months.</li>
            <li><strong>Usage counts.</strong> We count API requests per key per month to apply the free allowance, the spend cap, and billing.</li>
            <li><strong>Server and error logs.</strong> Like most websites, our servers record IP addresses, browser type, the pages requested, and the date and time. When something goes wrong, we also record the page, your IP address, and, if you are logged in, your account, so we can find and fix the problem.</li>
            <li><strong>Analytics.</strong> We use <a href="https://usefathom.com">Fathom Analytics</a> to count page views. Fathom does not use cookies and does not collect personal information.</li>
            <li><strong>Search.</strong> When you search the Site, our server sends your search terms to Algolia to get results. Your IP address is not sent with them.</li>
        </ul>

        <h3>Cookies</h3>

        <p>Catalog.beer sets one cookie of its own: a session cookie, set when you log in or create an account, that keeps you logged in and protects our forms against forgery. It expires after 30 days, or when you log out. We do not use advertising or tracking cookies.</p>

        <p>Some pages include content from Google — <a href="https://www.google.com/recaptcha/about/">reCAPTCHA</a> on the account creation and contact pages, maps on brewer, location, and brewery map pages, and address suggestions on the location forms. Google may set its own cookies and receive your IP address and the address of the page you are visiting when that content loads.</p>

        <h3>Do Not Track</h3>

        <p>Catalog.beer does not track visitors over time and across third-party websites to provide targeted advertising, and therefore does not respond to Do Not Track (DNT) signals.</p>

        <h2>How We Use Your Information</h2>
        <hr>

        <p>We use the information we collect to:</p>

        <ul>
            <li>provide the Site and the API, including your account, your API key, and brewery staff access;</li>
            <li>send you email about your account, such as email verification, password resets, and billing notices;</li>
            <li>send you important notices, such as changes to our Terms of Use or this Privacy Policy. Because this information is important to your use of Catalog.beer, you may not opt out of receiving these communications;</li>
            <li>bill for paid API usage;</li>
            <li>reply to your messages;</li>
            <li>keep the Services secure, review Contributions, and prevent spam, fraud, and abuse; and</li>
            <li>understand how the Services are used and improve them, using statistics that do not identify individuals.</li>
        </ul>

        <p>We do not sell your personal information, and we do not share it for targeted advertising.</p>

        <h2>Service Providers</h2>
        <hr>

        <p>We use the following providers to run Catalog.beer. Each receives only the information it needs to do its job, and handles it under its own privacy policy:</p>

        <ul>
            <li><strong><a href="https://www.linode.com/legal-privacy/">Akamai (Linode)</a></strong> hosts our servers and databases in the United States.</li>
            <li><strong><a href="https://stripe.com/privacy">Stripe</a></strong> processes API payments and stores your payment details.</li>
            <li><strong><a href="https://postmarkapp.com/privacy-policy">Postmark</a></strong> delivers our email, including contact form messages.</li>
            <li><strong><a href="https://www.algolia.com/policies/privacy/">Algolia</a></strong> provides site search.</li>
            <li><strong><a href="https://policies.google.com/privacy">Google</a></strong> provides reCAPTCHA, maps, and address suggestions, and helps validate addresses submitted with location records.</li>
            <li><strong><a href="https://usefathom.com/privacy">Fathom Analytics</a></strong> provides cookie-free page view statistics.</li>
        </ul>

        <h2>Other Disclosures</h2>
        <hr>

        <p>It may be necessary — by law, legal process, litigation, and/or requests from public and governmental authorities within or outside your country of residence — for Catalog.beer to disclose your personal information. We may also disclose information about you if we determine that, for purposes of national security, law enforcement, or other issues of public importance, disclosure is necessary or appropriate.</p>

        <p>We may also disclose information about you if we determine that disclosure is reasonably necessary to enforce our Terms of Use or protect our operations or users. Additionally, in the event of a reorganization, merger, or sale, we may transfer any and all personal information we collect to the relevant third party.</p>

        <h2>Protection of Personal Information</h2>
        <hr>

        <p>Catalog.beer takes the security of your personal information seriously. The Site and the API are served only over encrypted connections (TLS). Our servers are hosted by Akamai (Linode) in facilities with physical security measures, and access to them is limited. Passwords are stored as one-way hashes, and card details are held by Stripe, not by us.</p>

        <h2>Retention</h2>
        <hr>

        <ul>
            <li>Account information is kept until you close your account.</li>
            <li>API request logs are deleted after three months.</li>
            <li>Monthly usage counts and billing records are kept after you close your account, because they are financial records.</li>
            <li>Error logs are kept for as long as we need them to fix the problem they describe.</li>
            <li>Contact form messages are kept in our email for as long as they are useful for answering you.</li>
            <li>Contributions remain published after you close your account; see our <a href="/terms">Terms of Use</a>.</li>
        </ul>

        <h2>Access, Correction, and Deletion</h2>
        <hr>

        <p>You can view and update your name, email address, and password, or close your account, from your <a href="/account">Account</a> page. Closing your account deletes your account information, your API key, and any brewery staff access. Billing records and request logs are kept as described under Retention.</p>

        <p>For other personal information we hold, we will provide you with access, and you may ask us to correct it if it is inaccurate or delete it if Catalog.beer is not required to retain it by law or for legitimate business purposes. We may decline to process requests that are frivolous or vexatious, jeopardize the privacy of others, are extremely impractical, or for which access is not otherwise required by local law. Access, correction, or deletion requests can be made through our <a href="/contact">contact form</a>.</p>

        <h2>Children</h2>
        <hr>

        <p>Catalog.beer is a database about beer and is intended for adults of legal drinking age. It is not directed to children, and you must be of legal drinking age where you live — 21 or older in the United States — to create an account. If we learn that we have collected personal information from someone under that age, we will delete it as soon as possible.</p>

        <h2>Third-Party Sites and Services</h2>
        <hr>

        <p>Catalog.beer’s websites and services contain links to third-party websites, such as brewery websites. Information collected by third parties is governed by their privacy practices. We encourage you to learn about the privacy practices of those third parties.</p>

        <h2>Changes to This Policy</h2>
        <hr>

        <p>Catalog.beer may update this Privacy Policy from time to time. When we change it in a material way, we will post the updated policy here with a new effective date and notify account holders by email.</p>

        <h2>Privacy Questions</h2>
        <hr>

        <p>If you have any questions or concerns about Catalog.beer’s Privacy Policy or data processing, or if you would like to make a complaint about a possible breach of local privacy laws, please <a href="/contact">contact us</a>.</p>

        <p>All such communications are examined and replies issued where appropriate as soon as possible. If you are unsatisfied with the reply received, you may refer your complaint to the relevant regulator in your jurisdiction. If you ask us, we will endeavor to provide you with information about relevant complaint avenues which may be applicable to your circumstances.</p>

        <h2>State-Specific Laws</h2>
        <hr>

        <h3>California</h3>

        <p>If you are a California resident, the California Consumer Privacy Act gives you the right to know what personal information we collect about you and how we use and disclose it, and to ask us to correct or delete it. You can exercise these rights by <a href="/contact">contacting us</a>. If you choose to exercise your privacy rights, you have the right not to receive discriminatory treatment or a lesser degree of service from Catalog.beer. Catalog.beer does not sell or share your personal information.</p>

        <h3>Nevada</h3>

        <p>You have the right to opt out of the sale of your personal information. Catalog.beer does not sell your personal information.</p>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
