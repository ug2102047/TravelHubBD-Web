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

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['booking_id']) && isset($_GET['new_status'])) {
    $booking_id = (int)$_GET['booking_id'];
    $new_status = trim($_GET['new_status']);

    // Validate new_status against allowed values
    $allowed_statuses = ['confirmed', 'failed', 'cancelled']; // Add more if needed, like 'completed'
    if (!in_array($new_status, $allowed_statuses)) {
        header("Location: admin_manage_bookings.php?error=Invalid status provided.");
        exit();
    }

    if ($booking_id <= 0) {
        header("Location: admin_manage_bookings.php?error=Invalid booking ID.");
        exit();
    }

    try {
        // Check current status to prevent unwanted updates (optional but good practice)
        $stmt_check = $conn->prepare("SELECT booking_status FROM bookings WHERE booking_id = :booking_id");
        $stmt_check->execute([':booking_id' => $booking_id]);
        $current_booking = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$current_booking) {
             header("Location: admin_manage_bookings.php?error=Booking not found.");
             exit();
        }

        // Example logic: A 'failed' or 'cancelled' booking should not be 'confirmed' again directly
        // Or a 'confirmed' booking might only be 'cancelled' not 'failed' by admin.
        // This logic can be more complex based on your business rules.
        // For now, we allow admin to change from pending to confirmed/failed, and confirmed to cancelled.
        
        if ($current_booking['booking_status'] == 'pending' && ($new_status == 'confirmed' || $new_status == 'failed')) {
            // Allow change
        } elseif ($current_booking['booking_status'] == 'confirmed' && $new_status == 'cancelled') {
            // Allow change
        } elseif ($current_booking['booking_status'] == $new_status) {
             header("Location: admin_manage_bookings.php?message=Booking is already " . htmlspecialchars($new_status) . ".");
             exit();
        } else {
            header("Location: admin_manage_bookings.php?error=Cannot change booking status from " . htmlspecialchars($current_booking['booking_status']) . " to " . htmlspecialchars($new_status) . " with this action.");
            exit();
        }


        $sql = "UPDATE bookings SET booking_status = :new_status WHERE booking_id = :booking_id";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([':new_status' => $new_status, ':booking_id' => $booking_id])) {
            if ($stmt->rowCount() > 0) {
                // Optional: Send email notification to the user about the status change
                // For example, if $new_status is 'confirmed', send a confirmation email.
                // If $new_status is 'failed', send a notification with reasons (if any).
                header("Location: admin_manage_bookings.php?message=Booking ID #" . $booking_id . " status successfully updated to " . htmlspecialchars($new_status) . ".");
            } else {
                header("Location: admin_manage_bookings.php?error=No changes made. Booking ID #" . $booking_id . " might not exist or status was already " . htmlspecialchars($new_status) . ".");
            }
        } else {
            error_log("Failed to update booking status for booking_id: " . $booking_id);
            header("Location: admin_manage_bookings.php?error=Database error while updating status for booking ID #" . $booking_id . ".");
        }
    } catch (PDOException $e) {
        error_log("PDOException in admin_update_booking_status.php: " . $e->getMessage());
        header("Location: admin_manage_bookings.php?error=A database error occurred. Please try again.");
    }
    exit();

} else {
    // If not a GET request or parameters are missing
    header("Location: admin_manage_bookings.php?error=Invalid request.");
    exit();
}
?>
