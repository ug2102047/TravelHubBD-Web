<?php
require_once 'config.php';
require_once 'auth.php';

// Session shuru kora, jodi age na hoye thake
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jodi user login kora na thake, login page e pathano hobe
if (!isLoggedIn()) {
    $redirect_params = $_GET; 
    $query_string = http_build_query($redirect_params);
    header("Location: login.php?redirect=" . urlencode("book_hotel.php" . ($query_string ? "?" . $query_string : "")));
    exit();
}

// URL theke parameter neoa
$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$url_hotel_name = isset($_GET['hotel_name']) ? trim(urldecode($_GET['hotel_name'])) : ''; // hotel_details.php theke aste pare
$url_room_type_id = isset($_GET['room_type_id']) ? (int)$_GET['room_type_id'] : null;
$url_room_name = isset($_GET['room_name']) ? trim(urldecode($_GET['room_name'])) : '';
$url_room_price = isset($_GET['room_price']) ? (float)$_GET['room_price'] : 0;

// Hotel ID validate kora
if ($hotel_id <= 0) {
    header("Location: all_hotels.php?error=Invalid hotel specified.");
    exit();
}

// Database theke hotel er mul tottho neoa
$sql_hotel_main_info = "SELECT name, price_per_night FROM hotels WHERE hotel_id = :hotel_id";
$stmt_hotel_main_info = $conn->prepare($sql_hotel_main_info);
$stmt_hotel_main_info->execute(['hotel_id' => $hotel_id]);
$hotel_main_data = $stmt_hotel_main_info->fetch(PDO::FETCH_ASSOC);

if (!$hotel_main_data) {
    header("Location: all_hotels.php?error=Hotel not found.");
    exit();
}

// Dekhanor jonno hotel er naam, room er naam ebong prottek raat er vara nirdharon kora
$display_hotel_name = !empty($url_hotel_name) ? $url_hotel_name : $hotel_main_data['name'];
$final_room_type_id_for_booking = null;
$display_room_name = 'Standard Room'; // Default
$actual_price_per_night = $hotel_main_data['price_per_night']; // Default hotel price

// Jodi URL theke room_type_id ase, tahole room_types table theke oi room er specific tottho neoa
if ($url_room_type_id !== null && $url_room_type_id > 0) {
    $sql_room_specific_info = "SELECT name, price_per_night FROM room_types WHERE room_type_id = :room_type_id AND hotel_id = :hotel_id";
    $stmt_room_specific_info = $conn->prepare($sql_room_specific_info);
    $stmt_room_specific_info->execute(['room_type_id' => $url_room_type_id, 'hotel_id' => $hotel_id]);
    $room_specific_data = $stmt_room_specific_info->fetch(PDO::FETCH_ASSOC);

    if ($room_specific_data) {
        $display_room_name = !empty($url_room_name) ? $url_room_name : $room_specific_data['name']; // URL er naam pradhanno pabe
        $actual_price_per_night = ($url_room_price > 0) ? $url_room_price : $room_specific_data['price_per_night']; // URL er vara pradhanno pabe
        $final_room_type_id_for_booking = $url_room_type_id;
    }
} elseif ($url_room_price > 0 && !empty($url_room_name)) {
    // Jodi room_type_id na thake kintu room_name o room_price URL e thake (general booking link theke)
    $display_room_name = $url_room_name;
    $actual_price_per_night = $url_room_price;
}


// Session theke error message ebong form er purono data neoa
$errors = $_SESSION['booking_form_errors'] ?? [];
$old_data = $_SESSION['booking_form_old_data'] ?? [];
unset($_SESSION['booking_form_errors'], $_SESSION['booking_form_old_data']);

// User er default kichu data session theke neoa (jodi thake)
$user_full_name_session = $_SESSION['user_full_name'] ?? ''; // Assuming you store these in session upon login
$user_phone_session = $_SESSION['user_phone'] ?? '';
$user_email_session = $_SESSION['user_email'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($display_room_name); ?> at <?php echo htmlspecialchars($display_hotel_name); ?> - Travel Hub</title>
    <link rel="stylesheet" href="CSS/book_hotel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="booking-form-container">
        <h2><i class="fas fa-calendar-check"></i> Complete Your Booking</h2>
        <p class="hotel-room-info">
            You are booking for: <strong><?php echo htmlspecialchars($display_hotel_name); ?></strong><br>
            Room Type: <strong><?php echo htmlspecialchars($display_room_name); ?></strong>
        </p>
        <div class="price-summary">
            <p>Price per Night: $<?php echo number_format($actual_price_per_night, 2); ?></p>
            <p>Total Nights: <span id="total-nights-display">0</span></p>
            <p>Estimated Total: $<strong><span id="total-price-display"><?php echo number_format(0, 2); ?></span></strong></p>
        </div>

        <?php if (!empty($errors)): ?>
            <ul class="error-messages-box">
                <?php foreach ($errors as $error): ?>
                    <li><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="payment-instructions">
            <p><strong><i class="fas fa-info-circle"></i> Payment Instructions:</strong></p>
            <p>Please send the total booking amount to one of the following merchant numbers. Use your <strong>11-digit Phone Number</strong> as the payment reference for easy tracking.</p>
            <ul>
                <li><strong>bKash (Merchant):</strong> 017XX-XXXXXX (Example)</li>
                <li><strong>Nagad (Merchant):</strong> 018XX-XXXXXX (Example)</li>
                <li><strong>Rocket (Merchant):</strong> 019XX-XXXXXX (Example)</li>
            </ul>
            <p><small>After payment, enter the Transaction ID (TrxID) below. It's usually 8-10 alphanumeric characters (e.g., 8X3JFK9S7B).</small></p>
        </div>

        <form action="process_booking.php" method="POST" id="booking-form-final-noedit">
            <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
            <input type="hidden" name="hotel_name" value="<?php echo htmlspecialchars($display_hotel_name); ?>">
            <?php if ($final_room_type_id_for_booking): ?>
                <input type="hidden" name="room_type_id" value="<?php echo $final_room_type_id_for_booking; ?>">
            <?php endif; ?>
            <input type="hidden" name="room_name" value="<?php echo htmlspecialchars($display_room_name); ?>">
            <input type="hidden" name="price_per_night_for_booking" id="price_per_night_for_booking_hidden" value="<?php echo $actual_price_per_night; ?>">
            
            <div class="form-group">
                <label for="full_name">Full Name (as on NID/Passport):</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($old_data['full_name'] ?? $user_full_name_session); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Contact Phone Number (11 digits):</label>
                <input type="tel" id="phone_number" name="phone_number" pattern="01[3-9][0-9]{8}" title="Enter a valid 11-digit Bangladeshi mobile number (e.g., 01712345678)" value="<?php echo htmlspecialchars($old_data['phone_number'] ?? $user_phone_session); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old_data['email'] ?? $user_email_session); ?>" required>
            </div>

            <div class="form-group">
                <label for="nid_passport">NID / Passport Number:</label>
                <input type="text" id="nid_passport" name="nid_passport" value="<?php echo htmlspecialchars($old_data['nid_passport'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="country">Country of Residence:</label>
                <select id="country" name="country" required>
                    <option value="" disabled <?php echo empty($old_data['country']) && empty($_SESSION['user_country'] ?? '') ? 'selected' : ''; ?>>Select your country</option>
                    <option value="Bangladesh" <?php echo (($old_data['country'] ?? ($_SESSION['user_country'] ?? 'Bangladesh')) == 'Bangladesh') ? 'selected' : ''; ?>>Bangladesh</option>
                    <option value="India" <?php echo (($old_data['country'] ?? ($_SESSION['user_country'] ?? '')) == 'India') ? 'selected' : ''; ?>>India</option>
                    <option value="USA" <?php echo (($old_data['country'] ?? ($_SESSION['user_country'] ?? '')) == 'USA') ? 'selected' : ''; ?>>USA</option>
                    <option value="UK" <?php echo (($old_data['country'] ?? ($_SESSION['user_country'] ?? '')) == 'UK') ? 'selected' : ''; ?>>UK</option>
                    <option value="Other" <?php echo (($old_data['country'] ?? ($_SESSION['user_country'] ?? '')) == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="date-inputs">
                <div class="form-group">
                    <label for="check_in_date_input">Check-in Date:</label>
                    <input type="date" id="check_in_date_input" name="check_in_date" value="<?php echo htmlspecialchars($old_data['check_in_date'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="check_out_date_input">Check-out Date:</label>
                    <input type="date" id="check_out_date_input" name="check_out_date" value="<?php echo htmlspecialchars($old_data['check_out_date'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Payment Method Used:</label>
                <div class="payment-options-group">
                    <label><input type="radio" name="payment_option" value="Bkash" <?php echo (($old_data['payment_option'] ?? 'Bkash') == 'Bkash') ? 'checked' : ''; ?> required> bKash</label>
                    <label><input type="radio" name="payment_option" value="Nagad" <?php echo (($old_data['payment_option'] ?? '') == 'Nagad') ? 'checked' : ''; ?>> Nagad</label>
                    <label><input type="radio" name="payment_option" value="Rocket" <?php echo (($old_data['payment_option'] ?? '') == 'Rocket') ? 'checked' : ''; ?>> Rocket</label>
                </div>
            </div>

            <div class="form-group">
                <label for="transaction_id">Transaction ID (TrxID):</label>
                <input type="text" id="transaction_id" name="transaction_id" pattern="[A-Za-z0-9]{8,10}" title="Enter 8-10 alphanumeric characters (e.g., 8X3JFK9S7B)" value="<?php echo htmlspecialchars($old_data['transaction_id'] ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn-primary btn-block" style="font-size:1.1em; padding:15px;"><i class="fas fa-lock"></i> Submit Booking Request</button>
        </form>
    </section>
     
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="js/book_hotel.js"></script> 
</body>
</html>
