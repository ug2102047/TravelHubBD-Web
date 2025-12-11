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

// Fetch all bookings with user and hotel information
// You might want to add pagination for many bookings
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all'; // 'all', 'pending', 'confirmed', etc.

$sql = "SELECT b.booking_id, b.created_at, b.check_in_date, b.nights, b.total_price, 
               b.booking_status, b.transaction_id, b.payment_option,
               u.full_name AS user_name, u.email AS user_email,
               h.name AS hotel_name
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN hotels h ON b.hotel_id = h.hotel_id";

if ($filter_status != 'all' && in_array($filter_status, ['pending', 'confirmed', 'failed', 'cancelled'])) {
    $sql .= " WHERE b.booking_status = :status";
}
$sql .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if ($filter_status != 'all') {
    $stmt->bindParam(':status', $filter_status);
}
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="CSS/manage_booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="admin-container">
        <h2><i class="fas fa-tasks"></i> Manage All Bookings</h2>

        <?php if (isset($_GET['message'])): ?>
            <p class="message-area success" style="text-align:left; max-width:100%; margin-bottom:15px;"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="message-area error" style="text-align:left; max-width:100%; margin-bottom:15px;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <div class="filter-area">
            <form method="GET" action="admin_manage_bookings.php">
                <label for="status_filter">Filter by Status:</label>
                <select name="status" id="status_filter">
                    <option value="all" <?php if ($filter_status == 'all') echo 'selected'; ?>>All</option>
                    <option value="pending" <?php if ($filter_status == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="confirmed" <?php if ($filter_status == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                    <option value="failed" <?php if ($filter_status == 'failed') echo 'selected'; ?>>Failed</option>
                    <option value="cancelled" <?php if ($filter_status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn-primary small"><i class="fas fa-filter"></i> Filter</button>
            </form>
        </div>


        <?php if (empty($bookings)): ?>
            <p class="no-bookings">No bookings found matching the criteria.</p>
        <?php else: ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Hotel</th>
                        <th>Booked On</th>
                        <th>Check-in</th>
                        <th>Nights</th>
                        <th>Total Price</th>
                        <th>TrxID / Pay Option</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['user_name']); ?><br><small><?php echo htmlspecialchars($booking['user_email']); ?></small></td>
                            <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                            <td><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($booking['created_at']))); ?></td>
                            <td><?php echo htmlspecialchars(date("d M Y", strtotime($booking['check_in_date']))); ?></td>
                            <td><?php echo htmlspecialchars($booking['nights']); ?></td>
                            <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($booking['transaction_id']); ?><br><small>(<?php echo htmlspecialchars($booking['payment_option']); ?>)</small></td>
                            <td><span class="status-tag status-<?php echo strtolower(htmlspecialchars($booking['booking_status'])); ?>"><?php echo htmlspecialchars($booking['booking_status']); ?></span></td>
                            <td class="action-links">
                                <?php if ($booking['booking_status'] == 'pending'): ?>
                                    <a href="admin_update_booking_status.php?booking_id=<?php echo $booking['booking_id']; ?>&new_status=confirmed" class="link-confirm" onclick="return confirm('Are you sure you want to confirm this booking?');"><i class="fas fa-check"></i> Confirm</a>
                                    <a href="admin_update_booking_status.php?booking_id=<?php echo $booking['booking_id']; ?>&new_status=failed" class="link-fail" onclick="return confirm('Are you sure you want to mark this booking as failed?');"><i class="fas fa-times"></i> Fail</a>
                                <?php elseif ($booking['booking_status'] == 'confirmed'): ?>
                                     <a href="admin_update_booking_status.php?booking_id=<?php echo $booking['booking_id']; ?>&new_status=cancelled" class="link-fail" onclick="return confirm('Are you sure you want to mark this booking as CANCELLED by admin?');" title="Cancel this confirmed booking"><i class="fas fa-ban"></i> Cancel</a>
                                <?php else: ?>
                                    <span>No actions</span>
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
