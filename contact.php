<?php
require_once 'config.php'; // If navbar or footer needs it
require_once 'auth.php';   // For navbar user status

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$status_message_text = '';
$message_alert_type = ''; 
$form_errors = $_SESSION['contact_form_errors'] ?? [];
$old_form_data = $_SESSION['contact_form_old_data'] ?? [];
unset($_SESSION['contact_form_errors'], $_SESSION['contact_form_old_data']);


if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success_db':
            $status_message_text = "Thank you! Your message has been submitted successfully. You can track our reply in 'My Support Tickets' if you are logged in.";
            $message_alert_type = 'success';
            break;
        case 'success_hp': // Honeypot "success" for bots
             $status_message_text = "Message processed."; // Generic message
             $message_alert_type = 'success';
            break;
        case 'error_db':
            $status_message_text = "Apologies! There was a database error while submitting your message. Please try again later.";
            $message_alert_type = 'error';
            break;
        case 'error_validation':
            $status_message_text = "Submission failed. Please check the errors below and try again.";
            // $form_errors array will be displayed below
            $message_alert_type = 'error';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/contact.css" >
    <link rel="stylesheet" href="CSS/hotel_styles_v2.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="contact-page-wrapper">
        <div class="contact-header">
            <h2><i class="fas fa-headset"></i> Contact Our Support Team</h2>
            <p>Have a question or need assistance? Fill out the form below or use our contact details. If you are a registered user, you can also track your queries and our responses in "My Support Tickets" after logging in.</p>
        </div>

        <div id="form-status"> 
            <?php if (!empty($status_message_text) && $message_alert_type !== 'error' || ($message_alert_type === 'error' && empty($form_errors)) ): ?>
                <div class="status-message-area <?php echo htmlspecialchars($message_alert_type); ?>">
                    <i class="fas <?php echo ($message_alert_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?>"></i>
                    <?php echo htmlspecialchars($status_message_text); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($form_errors)): ?>
                 <div class="status-message-area error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($status_message_text ?: "Please correct the errors below:"); ?>
                    <ul class="error-list-validation" style="text-align:left; margin-top:10px;">
                        <?php foreach ($form_errors as $fe): ?>
                            <li><?php echo htmlspecialchars($fe); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="contact-content-grid">
            <div class="contact-info-section">
                <h3><i class="fas fa-address-book"></i> Direct Contact</h3>
                <div class="contact-info-item">
                    <i class="fas fa-envelope"></i>
                    <div><strong>Email:</strong><p><a href="mailto:support@travelhubbangladesh.com">support@travelhubbangladesh.com</a></p></div>
                </div>
                <div class="contact-info-item">
                    <i class="fas fa-phone-alt"></i>
                    <div><strong>Phone:</strong><p><a href="tel:+8801700000000">+880 17XX-XXXXXX</a></p><p><small>(Sun-Thu, 9AM-6PM)</small></p></div>
                </div>
                <div class="contact-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div><strong>Office:</strong><p>123 Adventure Rd, Gulshan<br>Dhaka 1212, Bangladesh</p><p><small>(By appointment)</small></p></div>
                </div>
            </div>

            <div class="contact-form-container">
                <h3><i class="fas fa-paper-plane"></i> Send Us Your Query</h3>
                <form action="process_contact_submission.php" method="POST">
                    <div style="position: absolute; left: -5000px;" aria-hidden="true">
                        <label for="hp_check">Do not fill if human:</label>
                        <input type="text" name="hp_check" id="hp_check" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="contact_name">Full Name:</label>
                        <input type="text" id="contact_name" name="contact_name" value="<?php echo htmlspecialchars($old_form_data['contact_name'] ?? ''); ?>" required placeholder="Your Full Name">
                    </div>
                    <div class="form-group">
                        <label for="contact_email">Email Address:</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($old_form_data['contact_email'] ?? ''); ?>" required placeholder="Your Email Address">
                    </div>
                    <div class="form-group">
                        <label for="contact_subject">Subject:</label>
                        <input type="text" id="contact_subject" name="contact_subject" value="<?php echo htmlspecialchars($old_form_data['contact_subject'] ?? ''); ?>" required placeholder="Brief subject of your message">
                    </div>
                    <div class="form-group">
                        <label for="contact_message">Your Message:</label>
                        <textarea id="contact_message" name="contact_message" required placeholder="Write your detailed message here..."><?php echo htmlspecialchars($old_form_data['contact_message'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn-send-message"><i class="fas fa-envelope-open-text"></i> Submit Message</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
