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

$filter_status_message = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql_messages = "SELECT submission_id, name, email, subject, LEFT(message, 100) AS message_preview, status, submitted_at 
                 FROM contact_submissions";

$params_messages = [];
if ($filter_status_message != 'all' && in_array($filter_status_message, ['new', 'read', 'replied', 'closed'])) {
    $sql_messages .= " WHERE status = :status";
    $params_messages[':status'] = $filter_status_message;
}
$sql_messages .= " ORDER BY submitted_at DESC";

$stmt_messages = $conn->prepare($sql_messages);
$stmt_messages->execute($params_messages);
$messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Messages - Admin</title>
    <link rel="stylesheet" href="CSS/admin_view_messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="admin-container">
        <h2><i class="fas fa-envelope-open-text"></i> View Contact Messages</h2>

        <?php if (isset($_GET['reply_status']) || isset($_GET['update_status']) || isset($_GET['info_msg'])): ?>
            <div id="form-status" style="margin-bottom:15px;">
                <?php if (isset($_GET['reply_status']) && $_GET['reply_status'] == 'success'): ?>
                    <p class="message-area success">Reply sent and status updated for message ID #<?php echo htmlspecialchars($_GET['submission_id_replied'] ?? ''); ?>!</p>
                <?php elseif (isset($_GET['reply_status']) && $_GET['reply_status'] == 'error'): ?>
                    <p class="message-area error">Failed to send reply. <?php echo isset($_GET['error_msg']) ? htmlspecialchars(urldecode($_GET['error_msg'])) : ''; ?></p>
                <?php endif; ?>
                <?php if (isset($_GET['update_status']) && $_GET['update_status'] == 'success'): ?>
                    <p class="message-area success">Message ID #<?php echo htmlspecialchars($_GET['updated_id'] ?? ''); ?> status updated successfully!</p>
                <?php elseif (isset($_GET['update_status']) && $_GET['update_status'] == 'error'): ?>
                    <p class="message-area error">Failed to update message status. <?php echo isset($_GET['error_msg']) ? htmlspecialchars(urldecode($_GET['error_msg'])) : ''; ?></p>
                <?php elseif (isset($_GET['update_status']) && $_GET['update_status'] == 'no_change'): ?>
                    <p class="message-area info">No status change for message ID #<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>.</p>
                <?php endif; ?>
                 <?php if (isset($_GET['info_msg'])): ?>
                    <p class="message-area info"><?php echo htmlspecialchars(urldecode($_GET['info_msg'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="filter-area">
            <form method="GET" action="admin_view_messages.php">
                <label for="status_filter_msg">Filter by Status:</label>
                <select name="status" id="status_filter_msg" onchange="this.form.submit()">
                    <option value="all" <?php if ($filter_status_message == 'all') echo 'selected'; ?>>All Messages</option>
                    <option value="new" <?php if ($filter_status_message == 'new') echo 'selected'; ?>>New</option>
                    <option value="read" <?php if ($filter_status_message == 'read') echo 'selected'; ?>>Read</option>
                    <option value="replied" <?php if ($filter_status_message == 'replied') echo 'selected'; ?>>Replied</option>
                    <option value="closed" <?php if ($filter_status_message == 'closed') echo 'selected'; ?>>Closed</option>
                </select>
                </form>
        </div>

        <?php if (empty($messages)): ?>
            <p class="no-messages">No messages found matching the criteria.</p>
        <?php else: ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Submitted</th>
                        <th>From</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Preview</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr style="<?php echo $msg['status'] == 'new' ? 'font-weight:bold;' : ''; ?>">
                            <td><?php echo htmlspecialchars($msg['submission_id']); ?></td>
                            <td><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($msg['submitted_at']))); ?></td>
                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                            <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                            <td class="message-preview" title="<?php echo htmlspecialchars($msg['message_preview']); ?>"><?php echo htmlspecialchars($msg['message_preview']); ?>...</td>
                            <td><span class="status-tag-message status-<?php echo strtolower(htmlspecialchars($msg['status'])); ?>"><?php echo htmlspecialchars($msg['status']); ?></span></td>
                            <td class="action-links">
                                <a href="admin_reply_message.php?submission_id=<?php echo $msg['submission_id']; ?>" class="link-reply"><i class="fas fa-reply"></i> View & Reply</a>
                                <?php if ($msg['status'] == 'new'): ?>
                                    <a href="process_admin_reply.php?action=mark_read&submission_id=<?php echo $msg['submission_id']; ?>" class="link-mark-read" title="Mark as Read"><i class="fas fa-eye"></i> Mark Read</a>
                                <?php endif; ?>
                                <?php if ($msg['status'] != 'closed'): ?>
                                    <a href="process_admin_reply.php?action=close_submission&submission_id=<?php echo $msg['submission_id']; ?>" class="link-close" title="Close this submission" onclick="return confirm('Are you sure you want to close this message thread?');"><i class="fas fa-times-circle"></i> Close</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
