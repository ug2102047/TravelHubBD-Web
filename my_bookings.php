<?php
require_once 'config.php';
require_once 'auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    header("Location: login.php?redirect=" . urlencode('my_bookings.php'));
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Fetch user's bookings, joining with hotels and room_types tables
$sql = "
    SELECT 
        b.booking_id, 
        b.check_in_date, 
        b.check_out_date, 
        b.nights, 
        b.total_price, 
        b.payment_option, 
        b.transaction_id, 
        b.booking_status, 
        b.created_at, 
        h.name AS hotel_name,
        rt.name AS room_type_name, -- Get room type name
        b.full_name AS booking_full_name 
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.hotel_id
    LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id -- Left join in case room_type_id is null
    WHERE b.user_id = :user_id
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/my_bookings.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="my-bookings-page-container">
        <h2><i class="fas fa-briefcase"></i> My Booking History</h2>

        <?php if (isset($_GET['message'])): ?>
            <p class="message-area success" style="max-width: 100%; margin-bottom:20px;"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="message-area error" style="max-width: 100%; margin-bottom:20px;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <p class="no-bookings-info">You haven't made any bookings yet. <a href="all_hotels.php">Find a hotel</a> to start your adventure!</p>
        <?php else: ?>
            <div class="bookings-list-cards">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-entry-card">
                        <h3>
                            <?php echo htmlspecialchars($booking['hotel_name']); ?>
                            <span class="booking-id">Booking ID: #<?php echo htmlspecialchars($booking['booking_id']); ?></span>
                        </h3>
                        <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type_name'] ?: 'Standard'); ?></p>
                        <p><strong>Booked For:</strong> <?php echo htmlspecialchars($booking['booking_full_name']); ?></p>
                        <p><strong>Check-in:</strong> <?php echo htmlspecialchars(date("D, M j, Y", strtotime($booking['check_in_date']))); ?></p>
                        <p><strong>Check-out:</strong> <?php echo htmlspecialchars(date("D, M j, Y", strtotime($booking['check_out_date']))); ?></p>
                        <p><strong>Nights:</strong> <?php echo htmlspecialchars($booking['nights']); ?></p>
                        <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                        <p><strong>Payment Via:</strong> <?php echo htmlspecialchars($booking['payment_option']); ?></p>
                        <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($booking['transaction_id']); ?></p>
                        <p><strong>Status:</strong> <span class="booking-status-tag status-<?php echo strtolower(htmlspecialchars($booking['booking_status'])); ?>"><?php echo htmlspecialchars(ucfirst($booking['booking_status'])); ?></span>
                            <?php if ($booking['booking_status'] == 'pending'): ?>
                                <span class="pending-verification-note">(Payment verification in progress. Usually takes up to 24 hours.)</span>
                            <?php endif; ?>
                        </p>
                        
                        <div class="actions-container">
                            <?php if ($booking['booking_status'] == 'pending' || $booking['booking_status'] == 'confirmed'): ?>
                                <a href="process_cancellation.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                                   class="cancel-booking-btn" 
                                   onclick="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                                   <i class="fas fa-times-circle"></i> Cancel Booking
                                </a>
                            <?php endif; ?>
                        </div>

                        <p class="booking-date-info">Booked On: <?php echo htmlspecialchars(date("M j, Y, g:i a", strtotime($booking['created_at']))); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>
