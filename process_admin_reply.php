<?php
require_once 'config.php';
require_once 'auth.php';

// PHPMailer is NOT needed here anymore if not sending email notifications to client
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;
// require 'vendor/autoload.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isAdmin()) {
    header("Location: login.php?error=Admin_access_only");
    exit();
}

$admin_user_id = $_SESSION['user_id']; // Admin who is replying

// --- Handle different actions ---
// Action can be 'send_reply', 'mark_read', 'close_submission'
$action = $_POST['send_reply_action'] ?? $_GET['action'] ?? '';


if ($action == 'send_reply' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $submission_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
    $reply_message = trim(filter_input(INPUT_POST, 'reply_message', FILTER_SANITIZE_FULL_SPECIAL_CHARS)); 
    // Client email, name, original subject are not needed for sending email anymore, but good to have for context if needed
    // $client_email = filter_input(INPUT_POST, 'client_email', FILTER_VALIDATE_EMAIL);
    // $client_name = filter_input(INPUT_POST, 'client_name', FILTER_SANITIZE_STRING);
    // $original_subject = filter_input(INPUT_POST, 'original_subject', FILTER_SANITIZE_STRING);


    if (!$submission_id || empty($reply_message)) {
        // Redirect back to the reply page with an error if submission_id or message is missing
        $error_msg = "Missing required fields for reply.";
        if ($submission_id) {
             header("Location: admin_reply_message.php?submission_id=" . $submission_id . "&reply_status=error&error_msg=" . urlencode($error_msg));
        } else {
            header("Location: admin_view_messages.php?reply_status=error&error_msg=" . urlencode($error_msg));
        }
        exit();
    }

    try {
        // 1. Save reply to database
        $sql_save_reply = "INSERT INTO message_replies (submission_id, admin_user_id, reply_message, replied_at) 
                           VALUES (:submission_id, :admin_user_id, :reply_message, NOW())";
        $stmt_save_reply = $conn->prepare($sql_save_reply);
        $stmt_save_reply->execute([
            ':submission_id' => $submission_id,
            ':admin_user_id' => $admin_user_id,
            ':reply_message' => $reply_message 
        ]);

        // 2. Update original submission status to 'replied'
        $sql_update_status = "UPDATE contact_submissions SET status = 'replied' WHERE submission_id = :submission_id";
        $stmt_update_status = $conn->prepare($sql_update_status);
        $stmt_update_status->execute([':submission_id' => $submission_id]);

        // Redirect to the view messages page with a success message
        header("Location: admin_view_messages.php?reply_status=success&submission_id_replied=" . $submission_id);
        exit();

    } catch (PDOException $e) {
        error_log("Error saving admin reply or updating status for submission_id {$submission_id}: " . $e->getMessage());
        // Redirect back to the specific reply page with an error
        header("Location: admin_reply_message.php?submission_id=" . $submission_id . "&reply_status=error&error_msg=" . urlencode("Database error while saving reply."));
        exit();
    }

} elseif (($action == 'mark_read' || $action == 'close_submission') && $_SERVER["REQUEST_METHOD"] == "GET") {
    $submission_id_get = filter_input(INPUT_GET, 'submission_id', FILTER_VALIDATE_INT);
    if (!$submission_id_get) {
        header("Location: admin_view_messages.php?update_status=error&error_msg=" . urlencode("Invalid submission ID for status update."));
        exit();
    }

    $new_status_val = '';
    if ($action == 'mark_read') $new_status_val = 'read';
    if ($action == 'close_submission') $new_status_val = 'closed';

    if (!empty($new_status_val)) {
        try {
            // Ensure not to mark 'replied' as 'read' to preserve the 'replied' status
            $current_status_check_sql = "SELECT status FROM contact_submissions WHERE submission_id = :submission_id";
            $stmt_current_status = $conn->prepare($current_status_check_sql);
            $stmt_current_status->execute([':submission_id' => $submission_id_get]);
            $current_status_row = $stmt_current_status->fetch(PDO::FETCH_ASSOC);

            if ($current_status_row && $current_status_row['status'] == 'replied' && $new_status_val == 'read') {
                 header("Location: admin_view_messages.php?update_status=info&info_msg=" . urlencode("Message already replied, status not changed to read."));
                 exit();
            }
            
            $stmt_update = $conn->prepare("UPDATE contact_submissions SET status = :new_status WHERE submission_id = :submission_id");
            $stmt_update->execute([':new_status' => $new_status_val, ':submission_id' => $submission_id_get]);
            
            if ($stmt_update->rowCount() > 0) {
                header("Location: admin_view_messages.php?update_status=success&updated_id=" . $submission_id_get);
            } else {
                header("Location: admin_view_messages.php?update_status=no_change&id=" . $submission_id_get);
            }

        } catch (PDOException $e) {
            error_log("Error updating submission status for ID {$submission_id_get}: " . $e->getMessage());
            header("Location: admin_view_messages.php?update_status=error&error_msg=" . urlencode("Database error during status update."));
        }
        exit();
    } else {
        header("Location: admin_view_messages.php?update_status=error&error_msg=" . urlencode("Invalid status action."));
        exit();
    }
} else {
    header("Location: admin_view_messages.php?error=Invalid_request_to_process_reply");
    exit();
}
?>
