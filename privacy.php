<?php
// config.php might not be strictly necessary if this page is static
// and navbar/footer don't rely on $conn directly for this page.
// require_once 'config.php'; 
require_once 'auth.php'; // For navbar.php to check login status

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/privacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="privacy-policy-container">
        <h2><i class="fas fa-shield-alt"></i> Privacy Policy</h2>
        <p class="last-updated">Last Updated: [Insert Date - e.g., June 03, 2025]</p>

        <section id="introduction">
            <h3>1. Introduction</h3>
            <p>
                Welcome to Travel Hub Bangladesh ("we," "us," or "our"). We are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy notice, or our practices with regards to your personal information, please contact us at [Insert Your Support Email Address from contact.php].
            </p>
            <p>
                This privacy notice describes how we might use your information if you visit our website at [Your Website URL], use our mobile application (if applicable), or otherwise engage with us. Reading this privacy notice will help you understand your privacy rights and choices.
            </p>
        </section>

        <section id="information-we-collect">
            <h3>2. What Information Do We Collect?</h3>
            <p>
                <strong>Personal information you disclose to us:</strong> We collect personal information that you voluntarily provide to us when you register on the website, express an interest in obtaining information about us or our products and services, when you participate in activities on the website (such as posting reviews, creating tour plans, or making bookings) or otherwise when you contact us.
            </p>
            <p>The personal information that we collect depends on the context of your interactions with us and the website, the choices you make, and the products and features you use. The personal information we collect may include the following:</p>
            <ul>
                <li><strong>Personal Identifiers:</strong> Name, phone number, email address, postal address, username, password, date of birth.</li>
                <li><strong>Payment Data:</strong> If you make bookings, we may collect data necessary to process your payment, such as your payment instrument number (e.g., transaction ID from mobile banking), and the security code associated with your payment instrument. All payment data is stored by our payment processor [Specify if you use one, or clarify how transaction IDs are used for verification].</li>
                <li><strong>Travel Preferences:</strong> Information related to your travel plans, wishlists, and preferences you provide.</li>
            </ul>
            <p>
                <strong>Information automatically collected:</strong> We automatically collect certain information when you visit, use, or navigate the website. This information does not reveal your specific identity (like your name or contact information) but may include device and usage information, such as your IP address, browser and device characteristics, operating system, language preferences, referring URLs, device name, country, location, information about how and when you use our website, and other technical information.
            </p>
        </section>

        <section id="how-we-use-information">
            <h3>3. How Do We Use Your Information?</h3>
            <p>We use personal information collected via our website for a variety of business purposes described below. We process your personal information for these purposes in reliance on our legitimate business interests, in order to enter into or perform a contract with you, with your consent, and/or for compliance with our legal obligations. We indicate the specific processing grounds we rely on next to each purpose listed below.</p>
            <ul>
                <li><strong>To facilitate account creation and logon process.</strong></li>
                <li><strong>To post testimonials/reviews.</strong> We post testimonials and reviews on our website that may contain personal information.</li>
                <li><strong>To manage user accounts.</strong> We may use your information for the purposes of managing your account and keeping it in working order.</li>
                <li><strong>To send administrative information to you.</strong> For example, information about your bookings, changes to our terms, conditions, and policies.</li>
                <li><strong>To fulfill and manage your orders/bookings.</strong> We may use your information to fulfill and manage your bookings, payments, and process cancellations made through the website.</li>
                <li><strong>To respond to user inquiries/offer support to users.</strong> We may use your information to respond to your inquiries and solve any potential issues you might have with the use of our Services.</li>
                <li><strong>For other Business Purposes,</strong> such as data analysis, identifying usage trends, determining the effectiveness of our promotional campaigns, and to evaluate and improve our website, products, marketing, and your experience.</li>
            </ul>
        </section>

        <section id="sharing-information">
            <h3>4. Will Your Information Be Shared With Anyone?</h3>
            <p>
                We only share information with your consent, to comply with laws, to provide you with services, to protect your rights, or to fulfill business obligations.
            </p>
            <p>
                Specifically, we may need to process your data or share your personal information in the following situations:
            </p>
            <ul>
                <li><strong>Vendors, Consultants, and Other Third-Party Service Providers:</strong> We may share your data with third-party vendors, service providers, contractors, or agents who perform services for us or on our behalf and require access to such information to do that work (e.g., payment processing, data analysis, email delivery, hosting services, customer service, and marketing efforts).</li>
                <li><strong>Business Transfers:</strong> We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business to another company.</li>
                <li><strong>With Hotels/Service Providers:</strong> When you make a booking, we will share necessary information (e.g., your name, contact details, booking dates) with the respective hotel or service provider to facilitate your reservation.</li>
            </ul>
        </section>

        <section id="data-security">
            <h3>5. How Do We Keep Your Information Safe?</h3>
            <p>
                We have implemented appropriate technical and organizational security measures designed to protect the security of any personal information we process. However, despite our safeguards and efforts to secure your information, no electronic transmission over the Internet or information storage technology can be guaranteed to be 100% secure, so we cannot promise or guarantee that hackers, cybercriminals, or other unauthorized third parties will not be able to defeat our security and improperly collect, access, steal, or modify your information.
            </p>
        </section>

        <section id="cookies">
            <h3>6. Do We Use Cookies and Other Tracking Technologies?</h3>
            <p>
                We may use cookies and similar tracking technologies (like web beacons and pixels) to access or store information. Specific information about how we use such technologies and how you can refuse certain cookies is set out in our Cookie Policy [If you have one, link it here, otherwise remove this sentence or create a basic cookie statement].
            </p>
            <p>
                [Placeholder for basic cookie statement if no separate policy: For example, we use cookies to help remember and process items in your wishlist, understand and save your preferences for future visits, and compile aggregate data about site traffic and site interaction so that we can offer better site experiences and tools in the future.]
            </p>
        </section>
        
        <section id="data-retention">
            <h3>7. How Long Do We Keep Your Information?</h3>
            <p>
                We will only keep your personal information for as long as it is necessary for the purposes set out in this privacy notice, unless a longer retention period is required or permitted by law (such as tax, accounting, or other legal requirements). When we have no ongoing legitimate business need to process your personal information, we will either delete or anonymize such information, or, if this is not possible (for example, because your personal information has been stored in backup archives), then we will securely store your personal information and isolate it from any further processing until deletion is possible.
            </p>
        </section>

        <section id="your-rights">
            <h3>8. What Are Your Privacy Rights?</h3>
            <p>
                In some regions, you have certain rights under applicable data protection laws. These may include the right (i) to request access and obtain a copy of your personal information, (ii) to request rectification or erasure; (iii) to restrict the processing of your personal information; and (iv) if applicable, to data portability. In certain circumstances, you may also have the right to object to the processing of your personal information. To make such a request, please use the contact details provided below.
            </p>
            <p>
                If you have an account with us, you can review and change your account information by logging into your profile settings page.
            </p>
        </section>

        <section id="policy-updates">
            <h3>9. Do We Make Updates to This Notice?</h3>
            <p>
                Yes, we will update this notice as necessary to stay compliant with relevant laws. The updated version will be indicated by an updated "Last Updated" date and the updated version will be effective as soon as it is accessible. We encourage you to review this privacy notice frequently to be informed of how we are protecting your information.
            </p>
        </section>

        <section id="contact-us-privacy">
            <h3>10. How Can You Contact Us About This Notice?</h3>
            <div class="contact-info-privacy">
                <p>
                    If you have questions or comments about this notice, you may email us at [Insert Your Privacy-Specific Email Address or Support Email] or by post to:
                </p>
                <p>
                    Travel Hub Bangladesh<br>
                    [Your Company's Legal Name, if different]<br>
                    [Your Physical Address, e.g., 123 Adventure Road, Gulshan]<br>
                    [Dhaka 1212, Bangladesh]
                </p>
            </div>
        </section>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
