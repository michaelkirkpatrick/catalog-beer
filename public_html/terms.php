<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Terms of Use');
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
        <h1>Terms of Use</h1>

        <?php
        /*---
        ACTION REQUIRED before this ships:
        1. Fill in the effective date below (and on /privacy, same date).
        2. Rent a PO box, then register a DMCA designated agent with the US
           Copyright Office (https://dmca.copyright.gov). Section 9's safe
           harbor only applies once the agent is registered. Fill in the
           Section 18 placeholders with the same name/address/email.
        3. "Doing business as Catalog.beer": a sole proprietor using a name
           other than their own files a Fictitious Business Name statement
           with San Diego County. Confirm that's done (or file it).
        4. Section 8 (API Fees and Billing) is new draft language — review.
        ---*/
        ?>
        <p>Effective [EFFECTIVE DATE]</p>

        <h2>1. Who We Are; Agreement to Terms of Use</h2>
        <hr>

        <p>These Terms and Conditions of Use (the “Terms of Use”) apply to the Catalog.beer website located at https://catalog.beer, all associated sites and services on that domain, and the Catalog.beer API. Catalog.beer is operated by Michael Eason Kirkpatrick, an individual doing business as Catalog.beer, in San Diego, California. In these Terms of Use, “Catalog.beer,” “we,” “us,” and “our” refer to him in that capacity. By using the Services, you agree to these Terms of Use; if you do not agree, do not use the Services.</p>

        <p>Catalog.beer reserves the right, at its sole discretion, to change, modify, add, or remove portions of these Terms of Use at any time. Material changes will be posted on this page with a new effective date. It is your responsibility to check these Terms of Use periodically for changes. Your continued use of the Services following the posting of changes will mean that you accept and agree to the changes. As long as you comply with these Terms of Use, Catalog.beer grants you a personal, non-exclusive, non-transferable, limited privilege to enter and use the Site.</p>

        <p>Catalog.beer’s <a href="/privacy">Privacy Policy</a>, <a href="/api-usage">API Usage</a>, and <a href="/api-pricing">API Pricing</a> pages apply to your use of the Services, and their terms are made a part of these Terms of Use by this reference.</p>

        <h2>2. Definitions</h2>
        <hr>

        <p>In these Terms of Use:</p>

        <ul>
            <li><strong>“Site”</strong> means the Catalog.beer website at https://catalog.beer and all associated sites on that domain.</li>
            <li><strong>“API”</strong> means the Catalog.beer application programming interface, including the service at api.catalog.beer.</li>
            <li><strong>“Services”</strong> means the Site, the API, and any other service Catalog.beer makes available.</li>
            <li><strong>“Content”</strong> means the database records, text, and other material made available on or through the Services, including Contributions. Content does not include the Catalog.beer Marks or the Site Software.</li>
            <li><strong>“Contribution”</strong> has the meaning given in Section 4.1.</li>
            <li><strong>“Catalog.beer Marks”</strong> means the Catalog.beer name, logo, trade dress, and other trademarks and service marks of Catalog.beer.</li>
            <li><strong>“Site Software”</strong> means the software, source code, page templates, stylesheets, scripts, and visual design of the Site.</li>
        </ul>

        <h2>3. Content and Licensing</h2>
        <hr>

        <h3>3.1 Content is licensed under CC BY 4.0</h3>

        <p>The Content is licensed under a <a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International license (CC BY 4.0)</a>. This license allows you to:</p>

        <ul>
            <li><strong>Share</strong> — copy and redistribute the material in any medium or format.</li>
            <li><strong>Adapt</strong> — remix, transform, and build upon the material for any purpose, even commercially.</li>
        </ul>

        <p>Under the following terms:</p>

        <ul>
            <li><strong>Attribution</strong> — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.</li>
            <li><strong>No additional restrictions</strong> — You may not apply legal terms or technological measures that legally restrict others from doing anything the license permits.</li>
        </ul>

        <p><strong>Notices:</strong></p>

        <p>You do not have to comply with the license for elements of the material in the public domain or where your use is permitted by an applicable exception or limitation.</p>

        <p>No warranties are given. The license may not give you all of the permissions necessary for your intended use. For example, other rights such as publicity, privacy, or moral rights may limit how you use the material.</p>

        <p>You can read the <a href="https://creativecommons.org/licenses/by/4.0/legalcode">full license text</a> on the Creative Commons website.</p>

        <h3>3.2 What the license does not cover</h3>

        <p>The CC BY 4.0 license applies to the Content. It does not apply to the following:</p>

        <ul>
            <li><strong>The Catalog.beer Marks.</strong> Trademark and patent rights are not licensed under CC BY 4.0 (see Section 2(b) of the license). You may not use the Catalog.beer Marks in a way that suggests Catalog.beer endorses you or your use.</li>
            <li><strong>The trademarks of breweries and other third parties</strong>, including brewery names, beer names, brand names, logos, and trade dress appearing in or referenced by the Content. These remain the property of their respective owners, and neither the Content nor these Terms of Use grants you any right to use them. Catalog.beer records these names as a matter of fact, to identify the brewer, beer, or location a record describes. Their presence in the Content does not convey, and must not be represented as conveying, any license, sponsorship, affiliation, approval, or endorsement by the owner of the mark.</li>
            <li><strong>The Site Software.</strong></li>
            <li><strong>Third-party material</strong> displayed on or through the Services that is identified as being subject to different terms. Where a record, field, or page carries such an identification, the terms so identified govern that material in place of CC BY 4.0, and it is your responsibility to comply with them.</li>
        </ul>

        <p>Content that carries no such identification is licensed under CC BY 4.0 as described in Section 3.1.</p>

        <h3>3.3 Attribution</h3>

        <p>Any reasonable manner of attribution that satisfies CC BY 4.0 is acceptable. Catalog.beer suggests the following form:</p>

        <blockquote><p>Data from <a href="https://catalog.beer">Catalog.beer</a>, licensed under <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a>.</p></blockquote>

        <p>If you changed the data, say so — for example, “Adapted from data from Catalog.beer, licensed under CC BY 4.0.” Linking to the specific Catalog.beer page you used is appreciated but not required.</p>

        <h3>3.4 The license and your access to the Services are separate</h3>

        <p>The CC BY 4.0 license grants rights in the Content itself. These Terms of Use govern your access to and use of the Services. Catalog.beer may limit, suspend, or terminate your access to the Site or the API under these Terms of Use. Doing so does not revoke or restrict any rights you have already received in Content under CC BY 4.0; those rights are irrevocable under the terms of that license.</p>

        <h2>4. Your Contributions</h2>
        <hr>

        <h3>4.1 What is a Contribution</h3>

        <p><strong>“Contribution”</strong> means any information, data, or text you submit, post, enter, or otherwise provide to the Site or through the API, including brewer, beer, location, and address records and any descriptions or other text you supply. Feedback is not a Contribution and is governed by Section 20.</p>

        <h3>4.2 Contributions are published publicly under CC BY 4.0</h3>

        <p>Catalog.beer is a public, openly licensed database. You understand and agree that your Contributions will be published on the Site and made available through the API to the general public under a Creative Commons Attribution 4.0 International license (CC BY 4.0), and that once published they may be copied, redistributed, adapted, and used by anyone for any purpose, including commercially.</p>

        <p><strong>Do not submit anything you are not willing to make public on these terms.</strong> Publication cannot be undone: Catalog.beer may remove a Contribution from the Site, but copies already distributed under CC BY 4.0 remain licensed to the people who received them.</p>

        <h3>4.3 License you grant to Catalog.beer</h3>

        <p>You grant Catalog.beer a perpetual, irrevocable, worldwide, non-exclusive, royalty-free, fully paid-up, transferable license, with the right to sublicense through multiple tiers, to use, host, store, reproduce, modify, adapt, translate, create derivative works from, publish, publicly display, publicly perform, and distribute your Contributions, in whole or in part, in any medium and by any means now known or later developed, in connection with the operation, improvement, promotion, and distribution of Catalog.beer and its data. This license survives the closure of your account and any termination of these Terms of Use.</p>

        <h3>4.4 Catalog.beer’s commitment to open licensing</h3>

        <p>Catalog.beer commits that, for as long as Catalog.beer publishes them, Contributions published on the Site will be made available to the public under CC BY 4.0, or under a license at least as permissive. Nothing in this Section prevents Catalog.beer from also making Contributions available under additional or different terms in parallel, provided the public availability described in this Section is maintained, and nothing in this Section limits Catalog.beer’s right to edit or remove Contributions under Section 4.7.</p>

        <h3>4.5 Attribution for Contributions</h3>

        <p>Attribution required under CC BY 4.0 for Contributions runs to Catalog.beer as licensor. To the fullest extent permitted by applicable law, you waive any right to be identified individually as the author of a Contribution, and you agree that neither Catalog.beer nor any recipient of Catalog.beer’s data is required to credit you individually on the Site, through the API, or in any export or distribution. Catalog.beer may credit contributors at its discretion but is under no obligation to do so.</p>

        <h3>4.6 Your representations</h3>

        <p>By submitting a Contribution, you represent and warrant that:</p>

        <ol>
            <li>you have all rights necessary to submit it and to grant the license in Section 4.3;</li>
            <li>the Contribution does not infringe or misappropriate any copyright, trademark, trade secret, right of privacy or publicity, or other right of any third party;</li>
            <li>the Contribution is accurate to the best of your knowledge;</li>
            <li>the Contribution does not contain confidential information you are not authorized to disclose; and</li>
            <li>the Contribution does not violate Section 6 (Your Use of the Services) or any applicable law.</li>
        </ol>

        <h3>4.7 No obligation to publish; moderation</h3>

        <p>Catalog.beer is under no obligation to publish, retain, or continue to display any Contribution, and may review, edit, correct, reject, or remove any Contribution at any time, with or without notice. Catalog.beer does not endorse and is not responsible for the accuracy of any Contribution.</p>

        <h3>4.8 No compensation</h3>

        <p>You are not entitled to any payment, credit, or other consideration for a Contribution or for Catalog.beer’s use of it.</p>

        <h3>4.9 Factual data</h3>

        <p>Much of the information in the Catalog.beer database consists of factual data — for example, brewer names, addresses, alcohol content, and bitterness measurements — which may not be subject to copyright in some jurisdictions. Nothing in this Section is intended to assert copyright in facts. This Section applies to the extent a Contribution is protected by copyright, database rights, or similar rights.</p>

        <h2>5. Brewery Staff Access</h2>
        <hr>

        <p>Catalog.beer treats an account as staff of a brewery when the account’s verified email address is on the same domain as the brewery’s website on file, and may also grant staff access to an account manually. Staff can add and edit the brewery’s records, mark them as verified by the brewer, and edit or delete records that carry that mark. See <a href="/claim-your-brewery">Claim Your Brewery</a> for how this works.</p>

        <p>By using staff access, you represent and warrant that you are authorized by the brewery to maintain its information on Catalog.beer. Staff access is a permission to maintain records; it does not transfer ownership of any record, and records maintained by staff are Contributions licensed under CC BY 4.0 like any other. Changing your account’s email address to a different domain ends any staff access that came from the old domain. Catalog.beer may grant, limit, or revoke staff access, and may correct or remove brewer-verified information, at any time.</p>

        <h2>6. Your Use of the Services</h2>
        <hr>

        <p>You may not attempt to gain unauthorized access to any portion or feature of the Services, or any other systems or networks connected to the Services or to any Catalog.beer server, by hacking, password “mining,” or any other illegitimate means.</p>

        <p>You may not probe, scan, or test the vulnerability of the Services or any network connected to the Services, nor breach the security or authentication measures on the Services or any network connected to them. You may not reverse look-up, trace, or seek to trace any information on any other user of or visitor to the Site, or any other customer of Catalog.beer, including any Catalog.beer account not owned by you, to its source, or exploit the Services or any information made available through them in any way where the purpose is to reveal any information, including but not limited to personal identification or information, other than your own information, as provided for by the Services.</p>

        <p>You agree not to use any device, software, or routine to interfere or attempt to interfere with the proper working of the Services or any transaction being conducted on the Services, or with any other person’s use of the Services.</p>

        <p>You may not forge headers or otherwise manipulate identifiers in order to disguise the origin of any message or transmittal you send to Catalog.beer on or through the Services. You may not pretend that you are, or that you represent, someone else, or impersonate any other individual or entity.</p>

        <h3>Automated access</h3>

        <p>Search engine crawlers, and tools or AI agents acting at the direction of a person — for example, an assistant answering a user’s question or helping a user add the beer they are drinking — are welcome on the Site. You must follow the directives in the Site’s <a href="/robots.txt">robots.txt</a> file, and you may not crawl, scrape, or download the Site in bulk, including to build datasets for training artificial intelligence or machine learning models. Programmatic and bulk access to the data must go through the API, subject to its usage limits and pricing. Catalog.beer may block or rate-limit automated traffic that does not follow these rules.</p>

        <p>You may not use the Services for any purpose that is unlawful or prohibited by these Terms of Use, or to solicit the performance of any illegal activity or other activity which infringes the rights of Catalog.beer or others. For clarity, this Section restricts your use of the Services; it does not restrict rights you hold in Content under CC BY 4.0 (see Section 3.4).</p>

        <p>Please also see information about <a href="/api-usage">API Usage</a>.</p>

        <h2>7. Accounts, API Keys, and Security</h2>
        <hr>

        <p>Certain features of the Services require you to open a Catalog.beer account. The Services are intended for adults. <strong>You must be of legal drinking age where you live — 21 or older in the United States — to create an account.</strong></p>

        <p>You are entirely responsible for maintaining the confidentiality of the information you hold for your account, including your password and API key, and for any and all activity that occurs under your account as a result of your failing to keep this information secure and confidential. You agree to notify Catalog.beer immediately of any unauthorized use of your account, password, or API key, or any other breach of security. You may be held liable for losses incurred by Catalog.beer or any other user of or visitor to the Services due to someone else using your Catalog.beer password, API key, or account as a result of your failing to keep your account information secure and confidential.</p>

        <p>You may not use anyone else’s Catalog.beer password, API key, or account at any time without the express permission and consent of its holder. Catalog.beer cannot and will not be liable for any loss or damage arising from your failure to comply with these obligations.</p>

        <h3>API keys</h3>

        <p>Your API key is issued to you. Do not share, publish, sell, or transfer it, or place it where others can extract it, such as in code that runs in a web browser. You are responsible for all usage of your API key, including any fees under Section 8.</p>

        <h3>Agents and tools</h3>

        <p>If you use an AI agent, script, or other tool to access the Services or to submit Contributions with your account or API key, you are responsible for what it does as if you had done it yourself. Its Contributions are your Contributions, the representations in Section 4.6 apply to them, and its usage counts toward your allowance and any fees.</p>

        <h3>Closing your account</h3>

        <p>You may close your account at any time from your <a href="/account">Account</a> page. Closing your account does not withdraw or terminate the license you granted in Section 4.3, does not remove Contributions already published or already distributed under CC BY 4.0, and does not cancel fees for usage you have already incurred.</p>

        <?php // DRAFT for review: Section 8 is new (taxes, payment failures, refunds, price changes). ?>
        <h2>8. API Fees and Billing</h2>
        <hr>

        <p>Some use of the API is paid. The current free allowance, prices, spend cap, and invoicing schedule are described on the <a href="/api-pricing">API Pricing</a> page, which is part of these Terms of Use. By enabling billing, you authorize Catalog.beer to charge your saved payment method for your usage as described there.</p>

        <ul>
            <li><strong>Payment processing.</strong> Payments are processed by Stripe under Stripe’s own terms. Catalog.beer does not receive or store your full card number.</li>
            <li><strong>Taxes.</strong> Prices do not include taxes. You are responsible for any sales, use, value-added, or similar taxes that apply to your purchases, and Catalog.beer may add them to your invoice where the law requires.</li>
            <li><strong>Spend cap.</strong> Requests that would take your monthly charges past your spend cap are declined until the next month. A declined request is not a failure of the Services.</li>
            <li><strong>Failed payments.</strong> If a charge fails, Catalog.beer may retry it, and may return your API key to the free allowance or suspend paid usage until the balance is paid.</li>
            <li><strong>Refunds.</strong> Charges are for usage already provided and are non-refundable, except where the law requires a refund or where Catalog.beer billed you in error. If you believe a charge is wrong, contact us within 60 days of the invoice date.</li>
            <li><strong>Ending billing.</strong> Turning off billing or closing your account stops future charges. Usage you have already incurred remains payable and may be invoiced, including balances below the usual invoicing minimum.</li>
            <li><strong>Price changes.</strong> Catalog.beer may change its prices or the free allowance. Changes will be posted on the API Pricing page, and accounts with billing enabled will receive at least 30 days’ notice by email before a price increase takes effect. A change does not apply to usage before its effective date. If you do not agree to a change, turn off billing before it takes effect; your continued paid usage afterward means you accept it.</li>
        </ul>

        <h2>9. Copyright Complaints</h2>
        <hr>

        <p>Catalog.beer respects the intellectual property rights of others and asks users of the Services to do the same.</p>

        <p>If you believe that material available on or through the Services infringes your copyright, you may send a written notice under the Digital Millennium Copyright Act (17 U.S.C. § 512) to Catalog.beer’s designated agent at the address in Section 18. Your notice must include:</p>

        <ol>
            <li>a physical or electronic signature of a person authorized to act on behalf of the owner of an exclusive right that is allegedly infringed;</li>
            <li>identification of the copyrighted work claimed to have been infringed;</li>
            <li>identification of the material that is claimed to be infringing, and information reasonably sufficient to permit Catalog.beer to locate the material;</li>
            <li>your name, address, telephone number, and email address;</li>
            <li>a statement that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law; and</li>
            <li>a statement that the information in the notice is accurate, and, under penalty of perjury, that you are authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
        </ol>

        <p>Catalog.beer will respond to properly submitted notices and may remove or disable access to material claimed to be infringing. If you believe your material was removed or disabled in error, you may submit a counter-notification containing the elements required by 17 U.S.C. § 512(g)(3).</p>

        <p><strong>Repeat infringers.</strong> Catalog.beer will, in appropriate circumstances, terminate the accounts of users who are repeat infringers.</p>

        <h2>10. Privacy</h2>
        <hr>

        <p>Catalog.beer’s <a href="/privacy">Privacy Policy</a> describes how we collect and use your information. By using the Services, you acknowledge and agree that Internet transmissions are never completely private or secure. You understand that any message or information you send to the Services may be read or intercepted by others, even if there is a special notice that a particular transmission is encrypted.</p>

        <h2>11. Links to Other Sites</h2>
        <hr>

        <p>The Site may contain links to independent third-party websites (“Linked Sites”). These Linked Sites are provided solely as a convenience to our visitors. Such Linked Sites are not under Catalog.beer’s control, and Catalog.beer is not responsible for and does not endorse the content of such Linked Sites, including any information or materials contained on them. You will need to make your own independent judgment regarding your interaction with these Linked Sites.</p>

        <h2>12. Disclaimers</h2>
        <hr>

        <p>Catalog.beer does not promise that the Services or any content, service, or feature of the Services will be error-free or uninterrupted, that any defects will be corrected, or that your use of the Services will provide specific results. The Services and the Content are delivered on an “as-is” and “as-available” basis. All information provided on the Services is subject to change without notice. Catalog.beer cannot ensure that any files or other data you download from the Services will be free of viruses or contamination or destructive features. Catalog.beer disclaims all warranties, express or implied, including any warranties of accuracy, non-infringement, merchantability, and fitness for a particular purpose. Catalog.beer disclaims any and all liability for the acts, omissions, and conduct of any third parties in connection with or related to your use of the Services. You assume total responsibility for your use of the Services and any Linked Sites. Your sole remedy against Catalog.beer for dissatisfaction with the Services or any Content is to stop using the Services or any such Content. This limitation of relief is a part of the bargain between the parties.</p>

        <p>The above disclaimer applies to any damages, liability, or injuries caused by any failure of performance, error, omission, interruption, deletion, defect, delay in operation or transmission, computer virus, communication line failure, theft or destruction of or unauthorized access to, alteration of, or use of records, whether for breach of contract, tort, negligence, or any other cause of action.</p>

        <p>Catalog.beer reserves the right to do any of the following, at any time, without notice: (1) to modify, suspend, or terminate operation of or access to the Services, or any portion of the Services, for any reason; (2) to modify or change the Services, or any portion of the Services, and any applicable policies or terms; and (3) to interrupt the operation of the Services, or any portion of the Services, as necessary to perform routine or non-routine maintenance, error correction, or other changes.</p>

        <h2>13. Limitation of Liability</h2>
        <hr>

        <p>Except where prohibited by law, in no event will Catalog.beer be liable to you for any indirect, consequential, exemplary, incidental, or punitive damages, including lost profits, even if Catalog.beer has been advised of the possibility of such damages.</p>

        <p>If, notwithstanding the other provisions of these Terms of Use, Catalog.beer is found to be liable to you for any damage or loss which arises out of or is in any way connected with your use of the Services or any Content, Catalog.beer’s liability shall in no event exceed the greater of US$100.00 or the fees you paid to Catalog.beer in the twelve (12) months before the claim arose. Some jurisdictions do not allow limitations of liability, so the foregoing limitation may not apply to you.</p>

        <h2>14. Indemnity</h2>
        <hr>

        <p>You agree to indemnify, defend, and hold Catalog.beer harmless from any demands, loss, liability, claims, or expenses (including reasonable attorneys’ fees) made against Catalog.beer by any third party due to, arising out of, or in connection with:</p>

        <ol>
            <li>your use of the Services;</li>
            <li>your Contributions, including any claim that a Contribution infringes or misappropriates the rights of any third party;</li>
            <li>your breach of these Terms of Use, including the representations you make in Sections 4.6 and 5; or</li>
            <li>your violation of any applicable law or the rights of any third party.</li>
        </ol>

        <h2>15. Violation of These Terms of Use</h2>
        <hr>

        <p>Catalog.beer may disclose any information we have about you (including your identity) if we determine that such disclosure is necessary in connection with any investigation or complaint regarding your use of the Services, or to identify, contact, or bring legal action against someone who may be causing injury to or interference with (either intentionally or unintentionally) Catalog.beer’s rights or property, or the rights or property of visitors to or users of the Services, including Catalog.beer’s customers. Catalog.beer reserves the right at all times to disclose any information that Catalog.beer deems necessary to comply with any applicable law, regulation, legal process, or governmental request. Catalog.beer also may disclose your information when Catalog.beer determines that applicable law requires or permits such disclosure, including exchanging information with other companies and organizations for fraud protection purposes.</p>

        <p>You acknowledge and agree that Catalog.beer may preserve any transmittal or communication by you with Catalog.beer through the Services, and may also disclose such data if required to do so by law or if Catalog.beer determines that such preservation or disclosure is reasonably necessary to (1) comply with legal process, (2) enforce these Terms of Use, (3) respond to claims that any such data violates the rights of others, or (4) protect the rights, property, or personal safety of Catalog.beer, its users, visitors to the Site, and the public.</p>

        <p>You agree that Catalog.beer may, in its sole discretion and without prior notice, terminate your access to the Services and/or block your future access to the Services if we determine that you have violated these Terms of Use or other agreements or guidelines which may be associated with your use of the Services. These remedies are in addition to any other remedies Catalog.beer may have at law or in equity.</p>

        <p>Catalog.beer may also end or suspend your access to the Services without prior notice for other reasons, including (1) requests by law enforcement or other government agencies, (2) your own request, such as when you close your account, (3) discontinuance or material modification of the Services, (4) unexpected technical issues or problems, or (5) inactivity: accounts that have not been used in more than a year may be deleted without notice. Fees you have already incurred remain payable.</p>

        <p>If either party takes legal action arising out of these Terms of Use, the prevailing party will be entitled to recover its reasonable attorneys’ fees and costs, in addition to any other relief granted. You agree that Catalog.beer will not be liable to you or to any third party for termination of your access to the Services as a result of any violation of these Terms of Use.</p>

        <h2>16. Governing Law; Dispute Resolution</h2>
        <hr>

        <p>You agree that all matters relating to your access to or use of the Services, including all disputes, will be governed by the laws of the United States and by the laws of the State of California without regard to its conflicts of laws provisions. You agree to the personal jurisdiction by and venue in the state and federal courts in San Diego, California, and waive any objection to such jurisdiction or venue. Any claim under these Terms of Use must be brought within one (1) year after the cause of action arises, or such claim or cause of action is barred. No recovery may be sought or received for damages other than out-of-pocket expenses, except that the prevailing party will be entitled to costs and attorneys’ fees. In the event of any controversy or dispute between Catalog.beer and you arising out of or in connection with your use of the Services, the parties shall attempt, promptly and in good faith, to resolve any such dispute. If we are unable to resolve any such dispute within a reasonable time (not to exceed thirty (30) days), then either party may submit such controversy or dispute to mediation. If the dispute cannot be resolved through mediation, then the parties shall be free to pursue any right or remedy available to them under applicable law.</p>

        <h2>17. Void Where Prohibited</h2>
        <hr>

        <p>Catalog.beer administers and operates the Services from its location in San Diego, California, USA. Although the Services are accessible worldwide, not all features, products, or services discussed, referenced, provided, or offered through or on the Services are available to all persons or in all geographic locations, or appropriate or available for use outside the United States. Catalog.beer reserves the right to limit, in its sole discretion, the provision and quantity of any feature, product, or service to any person or geographic area. Any offer for any feature, product, or service made on the Services is void where prohibited. If you choose to access the Services from outside the United States, you do so on your own initiative and you are solely responsible for complying with applicable local laws.</p>

        <h2>18. Notices and Contact</h2>
        <hr>

        <?php // ACTION REQUIRED: fill in once the PO box is rented and the DMCA agent is registered (see the note at the top of this file). ?>
        <p>Legal notices to Catalog.beer, including copyright notices under Section 9, should be sent to:</p>

        <blockquote>
            <p>Catalog.beer<br>
            Attn: [DESIGNATED AGENT NAME]<br>
            [PO BOX]<br>
            [CITY, STATE ZIP]<br>
            Email: [LEGAL/DMCA EMAIL ADDRESS]</p>
        </blockquote>

        <p>For anything else, please use our <a href="/contact">contact form</a>.</p>

        <h2>19. Miscellaneous</h2>
        <hr>

        <p>If any of the provisions of these Terms of Use are held by a court or other tribunal of competent jurisdiction to be void or unenforceable, such provisions shall be limited or eliminated to the minimum extent necessary and replaced with a valid provision that best embodies the intent of these Terms of Use, so that these Terms of Use shall remain in full force and effect. These Terms of Use, together with the Privacy Policy, API Usage, and API Pricing pages incorporated by reference in Section 1, constitute the entire agreement between you and Catalog.beer with regard to your use of the Services, and any and all other written or oral agreements or understandings previously existing between you and Catalog.beer with respect to such use are hereby superseded and cancelled. Catalog.beer will not accept any counter-offers to these Terms of Use, and all such offers are hereby categorically rejected. Catalog.beer’s failure to insist on or enforce strict performance of these Terms of Use shall not be construed as a waiver by Catalog.beer of any provision or any right it has to enforce these Terms of Use, nor shall any course of conduct between Catalog.beer and you or any other party be deemed to modify any provision of these Terms of Use. Catalog.beer may assign these Terms of Use, in whole or in part, in connection with a merger, acquisition, reorganization, or sale of all or substantially all of its assets. These Terms of Use shall not be interpreted or construed to confer any rights or remedies on any third parties.</p>

        <h2>20. Feedback and Information</h2>
        <hr>

        <p>Any feedback you provide to Catalog.beer, through the contact form or otherwise, shall be deemed to be non-confidential. Catalog.beer shall be free to use such information on an unrestricted basis. Feedback is not a Contribution and is not published under CC BY 4.0.</p>

        <p>The information contained on this website is subject to change without notice.</p>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
