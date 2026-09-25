<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('API Pricing');
$htmlHead->addDescription('Catalog.beer API pricing: 1,000 free requests every month, then $1 per 1,000 requests. No subscription, no minimum commitment.');
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
        <h1>API Pricing</h1>

        <p class="lead">Simple, usage-based pricing: every account includes 1,000 free API requests per month, and if you need more you pay $1 per 1,000 requests. No subscription, no minimum commitment.</p>

        <h2>Free Tier</h2>

        <p>Every Catalog.beer account includes <strong>1,000 API requests per month</strong> at no cost. Your count resets on the first of each month. For most hobbyists, students, and personal projects, this is more than enough to build something cool. All you need is an <a href="/signup">account</a> and a verified email address.</p>

        <h2>Pay As You Go</h2>

        <p>Need more than 1,000 requests? Add a payment method on your <a href="/billing">Billing page</a> and your API key keeps working past the free tier at <strong>$1 per 1,000 requests</strong>, rounded up to the nearest 1,000. There is nothing to pre-purchase and no plan to pick&#8212;you&#8217;re only billed for what you actually use.</p>

        <p>A few details worth knowing:</p>

        <ul>
            <li><strong>Monthly invoicing.</strong> Usage past the free tier is invoiced on the first of the following month and charged automatically to your saved card. Payments are handled by <a href="https://stripe.com">Stripe</a>; your card details never touch Catalog.beer&#8217;s servers.</li>
            <li><strong>Small balances roll forward.</strong> Balances under $5 aren&#8217;t charged right away&#8212;they carry over until they reach $5 (payment processing fees would eat most of a $1 invoice). Light overage may take a few months to appear on a bill.</li>
            <li><strong>Spend cap.</strong> Every key has a monthly spend cap&#8212;$50 by default, adjustable from $1 to $1,000&#8212;so a runaway script can&#8217;t give you a surprise bill. Once the month&#8217;s usage would cost more than your cap, further requests are declined until the month resets.</li>
            <li><strong>Turn it off anytime.</strong> You can disable billing from your <a href="/billing">Billing page</a> and go back to the free tier whenever you like.</li>
        </ul>

        <h3>Examples</h3>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Requests in a month</th>
                        <th scope="col">Billed requests</th>
                        <th scope="col">Charge</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>800</td>
                        <td>0</td>
                        <td>$0</td>
                    </tr>
                    <tr>
                        <td>1,500</td>
                        <td>500</td>
                        <td>$1</td>
                    </tr>
                    <tr>
                        <td>4,215</td>
                        <td>3,215</td>
                        <td>$4</td>
                    </tr>
                    <tr>
                        <td>26,000</td>
                        <td>25,000</td>
                        <td>$25</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2>Something Bigger?</h2>

        <p>If you have questions about pricing, or you&#8217;re planning something the spend cap can&#8217;t contain, reach out via our <a href="/contact">contact form</a> or email <a href="mailto:michael@catalog.beer">michael@catalog.beer</a>. Our goal is to cover our operating costs while keeping the API accessible to everyone.</p>

        <p>For usage rules and content licensing, see <a href="/api-usage">API Usage</a>. For technical details, see the <a href="/api-docs#billing">API documentation</a>.</p>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
