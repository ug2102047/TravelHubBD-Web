<?php
require_once 'config.php';
require_once 'auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header("Location: login.php?redirect=" . urlencode('my_support_tickets.php'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all submissions by this user
$sql_submissions = "SELECT submission_id, subject, message, status, submitted_at 
                    FROM contact_submissions 
                    WHERE user_id = :user_id 
                    ORDER BY submitted_at DESC";
$stmt_submissions = $conn->prepare($sql_submissions);
$stmt_submissions->execute([':user_id' => $user_id]);
$submissions = $stmt_submissions->fetchAll(PDO::FETCH_ASSOC);

// For each submission, fetch its replies
$threads = [];
foreach ($submissions as $submission) {
    $sql_replies = "SELECT mr.reply_message, mr.replied_at, u_admin.username AS admin_username
                    FROM message_replies mr
                    JOIN users u_admin ON mr.admin_user_id = u_admin.user_id
                    WHERE mr.submission_id = :submission_id
                    ORDER BY mr.replied_at ASC";
    $stmt_replies = $conn->prepare($sql_replies);
    $stmt_replies->execute([':submission_id' => $submission['submission_id']]);
    $replies = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);
    
    $threads[] = [
        'submission' => $submission,
        'replies' => $replies
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Support Tickets - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/my_support_tickets.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="support-ticket-container">
        <h2><i class="fas fa-life-ring"></i> My Support Tickets</h2>
        <p style="text-align:center; margin-bottom:20px;">View your submitted queries and our responses. For new queries, please use the <a href="contact.php">Contact Us</a> page.</p>

        <?php if (empty($threads)): ?>
            <p class="no-tickets-message">You have not submitted any support tickets yet. If you have a question, please <a href="contact.php">contact us</a>.</p>
        <?php else: ?>
            <?php foreach ($threads as $thread): ?>
                <div class="ticket-thread">
                    <div class="ticket-submission-header">
                        <h3><?php echo htmlspecialchars($thread['submission']['subject']); ?></h3>
                        <p class="meta-info">
                            Submitted: <strong><?php echo htmlspecialchars(date("D, M j, Y - g:i A", strtotime($thread['submission']['submitted_at']))); ?></strong> | 
                            Status: <span class="status-tag-ticket status-<?php echo strtolower(htmlspecialchars($thread['submission']['status'])); ?>"><?php echo htmlspecialchars($thread['submission']['status']); ?></span>
                        </p>
                    </div>

                    <div class="message-content client-message">
                        <span class="author">You wrote:</span>
                        <span class="timestamp"><?php echo htmlspecialchars(date("M j, Y, g:i a", strtotime($thread['submission']['submitted_at']))); ?></span>
                        <p><?php echo nl2br(htmlspecialchars($thread['submission']['message'])); ?></p>
                    </div>

                    <?php if (!empty($thread['replies'])): ?>
                        <?php foreach ($thread['replies'] as $reply): ?>
                            <div class="message-content admin-reply">
                                <span class="author"><?php echo htmlspecialchars($reply['admin_username'] ?? 'Support Team'); ?> replied:</span>
                                <span class="timestamp"><?php echo htmlspecialchars(date("M j, Y, g:i a", strtotime($reply['replied_at']))); ?></span>
                                <p><?php echo nl2br(htmlspecialchars($reply['reply_message'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($thread['submission']['status'] != 'closed' && $thread['submission']['status'] != 'new'): ?>
                         <div class="message-content admin-reply" style="background-color: #fef9e7;">
                            <p style="font-style:italic; color:#7d6608;">Our team has read your message. We will get back to you as soon as possible.</p>
                         </div>
                    <?php endif; ?>
                    
                    <div class="action-bar">
                        <?php if ($thread['submission']['status'] != 'closed'): ?>
                            <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?> 
</body>
</html>
