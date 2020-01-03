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
	<div class="container">
		<div class="row">
			<div class="col-md-2">
			<!-- Empty Column -->
			</div>
			<div class="col-md-8">
    			<h1>Privacy Policy</h1>

				<p>Updated on January 2, 2020</p>

				<p>Your privacy is important to us here at Catalog.beer. So we’ve developed a Privacy Policy that covers how we collect, use, disclose, and store your information. Please <a href="/contact">let us know</a> if you have any questions.</p>

				<h2>Collection and Use of Personal Information</h2>
				<hr>

				<p>Personal information is data that can be used to identify or contact a single person.</p>

				<p>You may be asked to provide your personal information anytime you are in contact with Catalog.beer. We may combine it with other information to provide and improve our services and content. You are not required to provide the personal information that we have requested, but, if you chose not to do so, in many cases we will not be able to provide you with our services or respond to any queries you may have.</p>

				<p>Here are some examples of the types of personal information Catalog.beer may collect and how we may use it:</p>

				<h3>What personal information we collect</h3>

				<p>When you create a Catalog.beer account, pay API fees, or contact us, we may collect a variety of information, including your name, credit card information, and email address.</p>

				<h3>How we use your personal information</h3>

				<ul>
				<li>The personal information we collect allows us to keep you posted on Catalog.beer’s latest announcements.</li>
				<li>We also use personal information to help us create, develop, deliver, and improve our services and content.</li>
				<li>From time to time, we may use your personal information to send important notices, such as communications about purchases and changes to our terms, conditions, and policies. Because this information is important to your interaction with Catalog.beer, you may not opt out of receiving these communications.</li>
				<li>We may also use personal information for internal purposes such as auditing, data analysis, and research to improve Catalog.beer’s services and customer communications.</li>
				</ul>

				<h2>Collection and Use of Non-Personal Information</h2>
				<hr>

				<p>We also collect data in a form that does not, on its own, permit direct association with any specific individual. We may collect, use, transfer, and disclose non-personal information for any purpose. The following are some examples of non-personal information that we collect and how we may use it:</p>

				<h3>Website User Tracking</h3>

				<p>We may collect information regarding customer activities on our website. This information is aggregated and used to help us provide more useful information to our customers and to understand which parts of our website and services are of most interest.</p>
				
				<h3>Do Not Track</h3>

				<p>Catalog.beer does not track its customers over time and across third party websites to provide targeted advertising and therefore does not respond to Do Not Track (DNT) signals. However, some third party sites do keep track of your browsing activities when they serve you content, which enables them to tailor what they present to you.</p>

				<p>Third parties that have content embedded on Catalog.beer’s websites, such as Google&#8217;s reCAPTCHA, set cookies on a user’s browser and/or obtain information about the fact that a web browser visited a specific Catalog.beer webpage from a certain IP address. Third parties cannot collect any other personally identifiable information from Catalog.beer’s websites unless you provide it to them directly.</p>

				<h2>Cookies and Other Technologies</h2>
				<hr>

				<p>Catalog.beer&#8217;s website, online services, interactive applications, and email messages may use &#8220;cookies&#8221;. We treat information collected by cookies and other technologies as non‑personal information. However, to the extent that Internet Protocol (IP) addresses or similar identifiers are considered personal information by local law, we also treat these identifiers as personal information. Similarly, to the extent that non-personal information is combined with personal information, we treat the combined information as personal information for the purposes of this Privacy Policy.</p>

				<p>As is true of most internet services, we gather some information automatically and store it in log files. This information includes Internet Protocol (IP) addresses, browser type, and date/time stamp. We use this information to understand and analyze trends, to administer the site, to learn about user behavior on the site, and to improve our services.</p>

				<h2>Service Providers</h2>
				<hr>

				<p>Catalog.beer integrates with services from the following providers:</p>

				<h3>Google</h3>

				<p>Catalog.beer uses Google&#8217;s <a href="https://www.google.com/recaptcha/intro/invisible.html">invisible reCAPTCHA</a> on our account creation and contact pages to help prevent robots from creating accounts and sending us spam email respectively. You can find Google&#8217;s <a href="https://www.google.com/intl/en/policies/privacy/">Privacy Policy</a> and <a href="https://www.google.com/intl/en/policies/terms/">Terms of Service</a> at the aforementioned links.</p>

				<h2>Others</h2>
				<hr>

				<p>It may be necessary − by law, legal process, litigation, and/or requests from public and governmental authorities within or outside your country of residence − for Catalog.beer to disclose your personal information. We may also disclose information about you if we determine that for purposes of national security, law enforcement, or other issues of public importance, disclosure is necessary or appropriate.</p>

				<p>We may also disclose information about you if we determine that disclosure is reasonably necessary to enforce our terms and conditions or protect our operations or users. Additionally, in the event of a reorganization, merger, or sale we may transfer any and all personal information we collect to the relevant third party.</p>

				<h2>Protection of Personal Information</h2>
				<hr>

				<p>Catalog.beer takes the security of your personal information very seriously. Catalog.beer&#8217;s online services protect your personal information during transit using encryption such as Transport Layer Security (TLS). When your personal data is stored by Catalog.beer, we use computer systems with limited access housed in facilities using physical security measures, operated by our service provider <a href="https://www.linode.com/security">Linode</a>. Your password is encrypted both when you enter and send it to access our services, and when it is stored in our database.</p>

				<h2>Integrity and Retention of Personal Information</h2>
				<hr>

				<p>Catalog.beer makes it easy for you to keep your personal information accurate, complete, and up to date. We will retain your personal information for the period necessary to fulfill the purposes outlined in this Privacy Policy unless a longer retention period is required or permitted by law.</p>

				<h2>Access to Personal Information</h2>
				<hr>

				<p>You can help ensure that your contact information and preferences are accurate, complete, and up to date by logging in to your account at <a href="/account">https://catalog.beer/account</a>. For other personal information we hold, we will provide you with access for any purpose including to request that we correct the data if it is inaccurate or delete the data if Catalog.beer is not required to retain it by law or for legitimate business purposes. We may decline to process requests that are frivolous/vexatious, jeopardize the privacy of others, are extremely impractical, or for which access is not otherwise required by local law. Access, correction, or deletion requests can be made through our <a href="/contact">contact form</a>.</p>

				<h2>Children</h2>
				<hr>

				<p>We understand the importance of taking extra precautions to protect the privacy and safety of children. Children under the age of 13, or equivalent minimum age in the relevant jurisdiction, are not permitted to create their own Catalog.beer account. </p>

				<p>If we learn that we have collected the personal information of a child under 13, or equivalent minimum age depending on jurisdiction, we will take steps to delete the information as soon as possible.</p>

				<h2>Third‑Party Sites and Services</h2>
				<hr>

				<p>Catalog.beer&#8217;s websites, applications, and services may contain links to third-party websites, products, and services.</p>

				<p>Information collected by third parties, which may include such things as location data or contact details, is governed by their privacy practices. We encourage you to learn about the privacy practices of those third parties.</p>

				<h2>Our Commitment to Your Privacy</h2>
				<hr>

				<p>To make sure your personal information is secure, we communicate our privacy and security guidelines to our team and strictly enforce privacy safeguards within the organization.</p>

				<h2>Privacy Questions</h2>
				<hr>

				<p>If you have any questions or concerns about Catalog.beer’s Privacy Policy or data processing or if you would like to make a complaint about a possible breach of local privacy laws, please <a href="/contact">contact us</a>.</p>

				<p>All such communications are examined and replies issued where appropriate as soon as possible. If you are unsatisfied with the reply received, you may refer your complaint to the relevant regulator in your jurisdiction. If you ask us, we will endeavor to provide you with information about relevant complaint avenues which may be applicable to your circumstances.</p>

				<p>Catalog.beer may update its Privacy Policy from time to time. When we change the policy in a material way, a notice will be posted on our website along with the updated Privacy Policy.</p>

				<h2>State Specific Laws</h2>
				<hr>
				<h3>California</h3>
				<p></p>The California Consumer Privacy Act provides California consumers with the right to obtain from Catalog.beer information about the personal information about you that we collect, use, and disclose. You can exercise your rights by <a href="/contact">contacting us</a>.</p>

				<p>If you choose to exercise your privacy rights, you have the right to not to receive discriminatory treatment or a lesser degree of service from Catalog.beer.</p>

				<h3>Nevada</h3>
				<p>You have the right to opt-out of the sale of your personal information. Catalog.beer does not sell your personal information.</p>

			</div>
			<div class="col-md-2">
			<!-- Empty Column -->
			</div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>