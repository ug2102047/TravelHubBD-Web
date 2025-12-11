<?php
require_once 'config.php';
require_once 'auth.php'; // For user_id if logged in

// PHPMailer is no longer needed for admin notification in this version.
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;
// require 'vendor/autoload.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Constants for website name (still useful for potential future use or logging)
define("WEBSITE_NAME_FOR_SUBMISSION", "Travel Hub Bangladesh");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id_session = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    // Honeypot check
    if (isset($_POST["hp_check"]) && !empty($_POST["hp_check"])) {
        error_log("Honeypot field filled in contact submission. Possible spam from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
        // Silently redirect as if successful to not alert the bot.
        // You might choose to not even redirect here, or redirect to a blank page.
        header("Location: contact.php?status=success_hp#form-status"); 
        exit;
    }

    $contact_name = isset($_POST["contact_name"]) ? strip_tags(trim($_POST["contact_name"])) : '';
    $contact_email = isset($_POST["contact_email"]) ? filter_var(trim($_POST["contact_email"]), FILTER_SANITIZE_EMAIL) : '';
    $contact_subject = isset($_POST["contact_subject"]) ? strip_tags(trim($_POST["contact_subject"])) : 'No Subject Provided';
    $contact_message = isset($_POST["contact_message"]) ? trim($_POST["contact_message"]) : '';

    $errors = [];
    if (empty($contact_name)) $errors[] = "Name is required.";
    if (empty($contact_message)) $errors[] = "Message is required.";
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email address is required.";
    if (empty($contact_subject)) $errors[] = "Subject is required.";


    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contact_submissions (user_id, name, email, subject, message, status, submitted_at) 
                    VALUES (:user_id, :name, :email, :subject, :message, 'new', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id_session, // Can be NULL
                ':name' => $contact_name,
                ':email' => $contact_email,
                ':subject' => $contact_subject,
                ':message' => $contact_message
            ]);
            $new_submission_id = $conn->lastInsertId();

            // --- Admin Notification Email Section REMOVED ---
            // No email will be sent to the admin upon new submission.
            // Admins will need to check the admin panel (admin_view_messages.php) for new messages.

            // Clear old form data from session on successful submission
            unset($_SESSION['contact_form_errors']);
            unset($_SESSION['contact_form_old_data']);

            header("Location: contact.php?status=success_db#form-status");
            exit;

        } catch (PDOException $e) {
            error_log("Error saving contact submission to DB (Submission ID attempt, if available: " . ($new_submission_id ?? 'N/A') . "): " . $e->getMessage() . " | SQLSTATE: " . $e->getCode());
            $_SESSION['contact_form_errors'] = ["A database error occurred while submitting your message. Please try again later."]; 
            $_SESSION['contact_form_old_data'] = $_POST; // Keep old data for repopulation
            header("Location: contact.php?status=error_db#form-status");
            exit;
        }
    } else {
        // Store errors and old data in session to repopulate the form
        $_SESSION['contact_form_errors'] = $errors; 
        $_SESSION['contact_form_old_data'] = $_POST; 
        header("Location: contact.php?status=error_validation#form-status");
        exit;
    }

} else {
    // If not a POST request, redirect to contact page
    header("Location: contact.php");
    exit;
}
?>
