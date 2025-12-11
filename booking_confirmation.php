<?php
require_once 'config.php';
require_once 'auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    header("Location: login.php?redirect=my_bookings.php");
    exit();
}

// Get booking_id from URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$status_message = isset($_GET['status']) ? $_GET['status'] : '';


if ($booking_id <= 0) {
    header("Location: my_bookings.php?error=Invalid booking ID specified.");
    exit();
}

// Fetch booking details along with hotel name and room name (if available)
$sql = "SELECT b.*, h.name AS hotel_name, rt.name AS room_type_name 
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.hotel_id
        LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id
        WHERE b.booking_id = :booking_id AND b.user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['booking_id' => $booking_id, 'user_id' => $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: my_bookings.php?error=Booking not found or you do not have permission to view this booking.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/booking_confirmation.css">
    <link rel="stylesheet" href="CSS/hotel_styles_v2.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="confirmation-page-container">
        <h2><i class="fas fa-check-circle"></i> Booking Request Received!</h2>
        
        <?php if ($status_message === 'success'): ?>
        <div class="confirmation-message">
            Thank you, <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong>! Your booking request for <strong><?php echo htmlspecialchars($booking['hotel_name']); ?></strong> has been successfully submitted.
            It is currently <strong>pending verification</strong>. We will notify you via email once your payment is confirmed.
        </div>
        <?php else: ?>
        <div class="confirmation-message" style="background-color: #fdf3e6; color:#e67e22; border-color:#f5cba7;">
            Your booking (ID: #<?php echo htmlspecialchars($booking['booking_id']); ?>) is currently <strong><?php echo htmlspecialchars($booking['booking_status']); ?></strong>.
        </div>
        <?php endif; ?>


        <div class="booking-details-summary">
            <h3>Booking Summary (ID: #<?php echo htmlspecialchars($booking['booking_id']); ?>)</h3>
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type_name'] ?: 'Standard'); // Display 'Standard' if no specific room name from DB ?></p>
            <p><strong>Booked For:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
            <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($booking['phone_number']); ?></p>
            <p><strong>Contact Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
            <p><strong>NID/Passport:</strong> <?php echo htmlspecialchars($booking['nid_passport']); ?></p>
            <p><strong>Country:</strong> <?php echo htmlspecialchars($booking['country']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars(date("l, F j, Y", strtotime($booking['check_in_date']))); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars(date("l, F j, Y", strtotime($booking['check_out_date']))); ?></p>
            <p><strong>Total Nights:</strong> <?php echo htmlspecialchars($booking['nights']); ?></p>
            <p><strong>Price Per Night (at booking):</strong> $<?php echo number_format($booking['room_price_at_booking'], 2); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
            <p><strong>Payment Via:</strong> <?php echo htmlspecialchars($booking['payment_option']); ?></p>
            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($booking['transaction_id']); ?></p>
            <p><strong>Booking Status:</strong> <span class="booking-status-display status-<?php echo strtolower(htmlspecialchars($booking['booking_status'])); ?>"><?php echo htmlspecialchars(ucfirst($booking['booking_status'])); ?></span></p>
            <p><strong>Booking Placed On:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($booking['created_at']))); ?></p>
        </div>

        <div class="page-actions">
            <a href="my_bookings.php" class="btn-primary"><i class="fas fa-list-ul"></i> View All My Bookings</a>
            <a href="index.php" class="btn-primary" style="background-color: #95a5a6;"><i class="fas fa-home"></i> Back to Home</a>
        </div>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>
