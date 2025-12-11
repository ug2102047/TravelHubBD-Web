<?php
require_once 'config.php';
require_once 'auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isAdmin()) {
    header("Location: login.php?error=Admin_access_only");
    exit();
}

$submission_id = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : 0;

if ($submission_id <= 0) {
    header("Location: admin_view_messages.php?error=Invalid message ID.");
    exit();
}

// Fetch the original submission
$sql_submission = "SELECT cs.*, u_client.username AS client_username 
                   FROM contact_submissions cs 
                   LEFT JOIN users u_client ON cs.user_id = u_client.user_id
                   WHERE cs.submission_id = :submission_id";
$stmt_submission = $conn->prepare($sql_submission);
$stmt_submission->execute([':submission_id' => $submission_id]);
$submission = $stmt_submission->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    header("Location: admin_view_messages.php?error=Message not found.");
    exit();
}

// Fetch previous replies for this submission
$sql_replies = "SELECT mr.*, u_admin.username AS admin_username 
                FROM message_replies mr
                JOIN users u_admin ON mr.admin_user_id = u_admin.user_id
                WHERE mr.submission_id = :submission_id 
                ORDER BY mr.replied_at ASC";
$stmt_replies = $conn->prepare($sql_replies);
$stmt_replies->execute([':submission_id' => $submission_id]);
$replies = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);

// Mark the message as 'read' if it was 'new' and no action is being processed
if ($submission['status'] == 'new' && !isset($_GET['reply_status'])) {
    try {
        $stmt_mark_read = $conn->prepare("UPDATE contact_submissions SET status = 'read' WHERE submission_id = :submission_id AND status = 'new'");
        $stmt_mark_read->execute([':submission_id' => $submission_id]);
        if ($stmt_mark_read->rowCount() > 0) {
            $submission['status'] = 'read'; // Update status for current page view
        }
    } catch (PDOException $e) {
        error_log("Error marking message as read (ID: {$submission_id}): " . $e->getMessage());
    }
}

$admin_username_session = $_SESSION['username'] ?? 'Admin'; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message #<?php echo htmlspecialchars($submission_id); ?> - Admin</title>
    <link rel="stylesheet" href="CSS/admin_reply_message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="admin-container">
        <a href="admin_view_messages.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to All Messages</a>
        
        <div class="message-thread-container">
            <h2>
                <i class="fas fa-envelope-open-text"></i> Message from <?php echo htmlspecialchars($submission['name']); ?>
                <span class="current-status-display status-<?php echo strtolower(htmlspecialchars($submission['status'])); ?>"><?php echo htmlspecialchars($submission['status']); ?></span>
            </h2>
             <?php if (isset($_GET['reply_status']) && $_GET['reply_status'] == 'error'): ?>
                <p class="message-area error"><?php echo isset($_GET['error_msg']) ? htmlspecialchars(urldecode($_GET['error_msg'])) : 'Failed to send reply.'; ?></p>
            <?php endif; ?>


            <div class="message-header">
                <p><strong>Client Name:</strong> <?php echo htmlspecialchars($submission['name']); ?></p>
                <p><strong>Client Email:</strong> <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>"><?php echo htmlspecialchars($submission['email']); ?></a></p>
                <p><strong>Subject:</strong> <?php echo htmlspecialchars($submission['subject']); ?></p>
                <p><strong>Received:</strong> <?php echo htmlspecialchars(date("l, F j, Y - g:i A", strtotime($submission['submitted_at']))); ?></p>
                <?php if ($submission['user_id']): ?>
                    <p><strong>Submitted by (Registered User):</strong> <?php echo htmlspecialchars($submission['client_username'] ?? 'ID: ' . $submission['user_id']); ?></p>
                <?php endif; ?>
            </div>

            <h3><i class="fas fa-comment-dots"></i> Original Message:</h3>
            <div class="message-body-original">
                <?php echo nl2br(htmlspecialchars($submission['message'])); ?>
            </div>

            <?php if (!empty($replies)): ?>
                <div class="replies-section">
                    <h3><i class="fas fa-history"></i> Reply History:</h3>
                    <?php foreach ($replies as $reply): ?>
                        <div class="reply-item <?php echo ($reply['admin_user_id'] == $_SESSION['user_id']) ? 'admin-reply-item' : ''; // Style admin's own replies differently if needed ?>">
                            <p class="reply-header">
                                <strong>Replied by:</strong> <?php echo htmlspecialchars($reply['admin_username'] ?? 'Admin'); ?> 
                                on <?php echo htmlspecialchars(date("M j, Y, g:i A", strtotime($reply['replied_at']))); ?>
                            </p>
                            <div class="reply-content">
                                <?php echo nl2br(htmlspecialchars($reply['reply_message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($submission['status'] != 'closed'): ?>
            <div class="reply-form-container">
                <h3><i class="fas fa-reply"></i> Your Reply to <?php echo htmlspecialchars($submission['name']); ?>:</h3>
                <form action="process_admin_reply.php" method="POST">
                    <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
                    <div class="form-group">
                        <label for="reply_message">Message:</label>
                        <textarea id="reply_message" name="reply_message" rows="8" required placeholder="Type your reply here... This reply will be visible to the client in their 'My Support Tickets' section."></textarea>
                    </div>
                    <button type="submit" name="send_reply_action" value="send_reply" class="btn-send-reply"><i class="fas fa-paper-plane"></i> Save & Record Reply</button>
                </form>
            </div>
            <?php else: ?>
                <p style="margin-top:20px; padding:10px; background-color:#f0f0f0; border-radius:4px; font-weight:bold; color:#7f8c8d; text-align:center;">
                    <i class="fas fa-lock"></i> This message thread has been closed. No further replies can be added.
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
