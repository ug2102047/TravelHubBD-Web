<?php
require_once 'config.php';
require_once 'auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    header("Location: login.php?error=Please login to manage your bookings.");
    exit();
}

// Get booking_id from URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($booking_id <= 0) {
    header("Location: my_bookings.php?error=Invalid booking ID specified for cancellation.");
    exit();
}

try {
    // First, verify the booking belongs to the current user and get its status
    $sql_check = "SELECT booking_id, user_id, booking_status FROM bookings WHERE booking_id = :booking_id AND user_id = :user_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute(['booking_id' => $booking_id, 'user_id' => $user_id]);
    $booking = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        // Booking not found for this user
        header("Location: my_bookings.php?error=Booking not found or you do not have permission to cancel it.");
        exit();
    }

    // Check if the booking can be cancelled (e.g., only 'pending' or 'confirmed' bookings)
    // You might have different rules, e.g., confirmed bookings can only be cancelled X days before check-in
    if ($booking['booking_status'] == 'pending' || $booking['booking_status'] == 'confirmed') {
        
        // Update the booking status to 'cancelled'
        $sql_update = "UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = :booking_id AND user_id = :user_id";
        $stmt_update = $conn->prepare($sql_update);
        
        if ($stmt_update->execute(['booking_id' => $booking_id, 'user_id' => $user_id])) {
            if ($stmt_update->rowCount() > 0) {
                // Successfully cancelled
                // Optional: Add logic here to notify admin, process refunds (if applicable), update room availability, etc.
                header("Location: my_bookings.php?message=Booking ID #" . $booking_id . " has been successfully cancelled.");
                exit();
            } else {
                // No rows affected, something unexpected (shouldn't happen if check passed)
                header("Location: my_bookings.php?error=Could not cancel the booking. It might have already been processed.");
                exit();
            }
        } else {
            // SQL update execution failed
            error_log("Failed to update booking status for booking_id: " . $booking_id . " - User: " . $user_id);
            header("Location: my_bookings.php?error=An error occurred while trying to cancel the booking. Please try again.");
            exit();
        }
    } else {
        // Booking is not in a cancellable state (e.g., already 'failed' or 'cancelled')
        header("Location: my_bookings.php?error=This booking cannot be cancelled as its current status is '" . htmlspecialchars($booking['booking_status']) . "'.");
        exit();
    }

} catch (PDOException $e) {
    error_log("PDOException in process_cancellation.php: " . $e->getMessage());
    header("Location: my_bookings.php?error=A database error occurred. Please contact support. (Error Code: PCDB01)");
    exit();
}
?>
