<?php
// It's good practice to start a session if you plan to use session messages,
// though for this specific redirect-based status, it's not strictly necessary.
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// --- Configuration ---
// IMPORTANT: Replace this with your actual email address.
define("RECEIVING_EMAIL", "puspitabaidya875@gmail.com"); 
// Define your website name for email subjects.
define("WEBSITE_NAME", "Travel Hub Bangladesh");
// Define a maximum message length.
define("MAX_MESSAGE_LENGTH", 5000); // Allow up to 5000 characters

// --- Form Processing ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Honeypot field check (add a hidden field named 'hp_check' in your contact.php form)
    // If this field is filled, it's likely a bot.
    if (isset($_POST["hp_check"]) && !empty($_POST["hp_check"])) {
        // You can log this attempt or just silently ignore it.
        error_log("Honeypot field filled. Possible spam attempt.");
        // Redirect as if it was successful to not alert the bot.
        header("Location: contact.php?status=success#form-status");
        exit;
    }

    // Sanitize and retrieve form inputs
    $contact_name = isset($_POST["contact_name"]) ? strip_tags(trim($_POST["contact_name"])) : '';
    $contact_email = isset($_POST["contact_email"]) ? filter_var(trim($_POST["contact_email"]), FILTER_SANITIZE_EMAIL) : '';
    $contact_subject = isset($_POST["contact_subject"]) ? strip_tags(trim($_POST["contact_subject"])) : 'No Subject Provided';
    $contact_message = isset($_POST["contact_message"]) ? trim($_POST["contact_message"]) : '';

    // --- Validation ---
    $errors = [];
    if (empty($contact_name)) {
        $errors[] = "Name is required.";
    }
    if (empty($contact_message)) {
        $errors[] = "Message is required.";
    } elseif (strlen($contact_message) > MAX_MESSAGE_LENGTH) {
        $errors[] = "Message is too long. Maximum " . MAX_MESSAGE_LENGTH . " characters allowed.";
    }
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }

    if (!empty($errors)) {
        // If data is invalid or missing, redirect back to contact page with an error status.
        // You might want to pass the specific errors back to the contact page using sessions
        // if you want to display them next to the form fields. For simplicity, a generic error.
        // For example: $_SESSION['contact_form_errors'] = $errors;
        header("Location: contact.php?status=error_incomplete#form-status");
        exit;
    }

    // --- Prepare Email Content for Admin ---
    $email_recipient_admin = RECEIVING_EMAIL;
    $email_subject_to_admin = "New Message via " . WEBSITE_NAME . " Contact Form: \"" . $contact_subject . "\"";

    $email_body_to_admin = "Hello Admin,\n\n";
    $email_body_to_admin .= "You have received a new message through the contact form on " . WEBSITE_NAME . ".\n\n";
    $email_body_to_admin .= "======================================================\n";
    $email_body_to_admin .= "Client Details:\n";
    $email_body_to_admin .= "======================================================\n";
    $email_body_to_admin .= "Name:    " . $contact_name . "\n";
    $email_body_to_admin .= "Email:   " . $contact_email . "\n";
    $email_body_to_admin .= "Subject: " . $contact_subject . "\n\n";
    $email_body_to_admin .= "======================================================\n";
    $email_body_to_admin .= "Client's Message:\n";
    $email_body_to_admin .= "======================================================\n";
    $email_body_to_admin .= $contact_message . "\n\n";
    $email_body_to_admin .= "======================================================\n\n";
    $email_body_to_admin .= "IMPORTANT: To reply to this client, simply click 'Reply' in your email client. \n";
    $email_body_to_admin .= "Your response will be sent directly to: " . $contact_email . "\n";

    // --- Email Headers ---
    // Using a "From" address associated with your domain is generally better for deliverability.
    $server_name = $_SERVER['SERVER_NAME'] ?? 'travelhub-bangladesh.com'; // Fallback domain
    if (strpos($server_name, 'www.') === 0) {
        $server_name = substr($server_name, 4);
    }
    $headers = "From: " . WEBSITE_NAME . " Contact Form <contact-form@". $server_name .">\r\n";
    $headers .= "Reply-To: " . $contact_name . " <" . $contact_email . ">\r\n"; // This is key for replying to the client
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Originating-IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\r\n";


    // --- Attempt to Send the Email ---
    if (mail($email_recipient_admin, $email_subject_to_admin, $email_body_to_admin, $headers)) {
        // Email sent successfully to admin
        header("Location: contact.php?status=success#form-status"); 
    } else {
        // Email sending failed
        error_log("CRITICAL: Contact form email sending failed. To: $email_recipient_admin, From: $contact_email, Subject: $contact_subject");
        header("Location: contact.php?status=error_sending#form-status"); 
    }

} else {
    // If the script is accessed directly (not via POST), redirect to the contact page.
    header("Location: contact.php");
    exit;
}
?>
