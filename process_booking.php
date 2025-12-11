<?php
require_once 'config.php';
require_once 'auth.php';

// Session shuru kora, jodi age na hoye thake
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jodi user login kora na thake, login page e pathano hobe
if (!isLoggedIn()) {
    $hotel_id_redirect = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : 0;
    $redirect_query_parts = [];
    if ($hotel_id_redirect > 0) $redirect_query_parts['hotel_id'] = $hotel_id_redirect;
    if (isset($_POST['hotel_name'])) $redirect_query_parts['hotel_name'] = $_POST['hotel_name'];
    if (isset($_POST['room_type_id'])) $redirect_query_parts['room_type_id'] = $_POST['room_type_id'];
    if (isset($_POST['room_name'])) $redirect_query_parts['room_name'] = $_POST['room_name'];
    if (isset($_POST['price_per_night_for_booking'])) $redirect_query_parts['room_price'] = $_POST['price_per_night_for_booking'];
    
    $query_string_for_redirect = http_build_query($redirect_query_parts);
    header("Location: login.php?redirect=" . urlencode("book_hotel.php" . ($query_string_for_redirect ? "?" . $query_string_for_redirect : "")));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POST data neoa ebong sanitize kora
    $user_id = $_SESSION['user_id'];

    // User ID database e ache kina check kora
    $stmt_check_user = $conn->prepare("SELECT user_id FROM users WHERE user_id = :uid");
    $stmt_check_user->execute([':uid' => $user_id]);
    if (!$stmt_check_user->fetch()) {
        error_log("CRITICAL: Invalid user_id in session: " . $user_id . " during booking attempt. Forcing logout.");
        session_unset();
        session_destroy();
        header("Location: login.php?error=invalid_session_user_critical"); 
        exit();
    }

    $hotel_id = filter_input(INPUT_POST, 'hotel_id', FILTER_VALIDATE_INT);
    $room_type_id = filter_input(INPUT_POST, 'room_type_id', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'min_range' => 1]]); 
    
    $price_per_night_for_booking = filter_input(INPUT_POST, 'price_per_night_for_booking', FILTER_VALIDATE_FLOAT);

    $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
    $phone_number = trim(filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $nid_passport = trim(filter_input(INPUT_POST, 'nid_passport', FILTER_SANITIZE_STRING));
    $country = trim(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING));
    
    $check_in_date_str = trim(filter_input(INPUT_POST, 'check_in_date', FILTER_SANITIZE_STRING));
    $check_out_date_str = trim(filter_input(INPUT_POST, 'check_out_date', FILTER_SANITIZE_STRING));

    $payment_option = trim(filter_input(INPUT_POST, 'payment_option', FILTER_SANITIZE_STRING));
    $transaction_id = trim(filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_STRING));

    $errors = [];
    $_SESSION['booking_form_old_data'] = $_POST; 

    // --- Server-side Validation ---
    if ($hotel_id === false || $hotel_id <= 0) $errors[] = "Invalid hotel selection. Please choose a hotel.";
    if ($price_per_night_for_booking === false || $price_per_night_for_booking <= 0) $errors[] = "Invalid room price information. Please try booking again.";
    
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (!preg_match("/^01[3-9][0-9]{8}$/", $phone_number)) $errors[] = "Phone number must be a valid 11-digit Bangladeshi mobile number (e.g., 017xxxxxxxx).";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email address is required.";
    if (empty($nid_passport)) $errors[] = "NID or Passport number is required.";
    if (empty($country)) $errors[] = "Country of residence is required.";
    
    if (empty($check_in_date_str)) $errors[] = "Check-in date is required.";
    if (empty($check_out_date_str)) $errors[] = "Check-out date is required.";
    
    $nights = 0;
    $check_in_dt = null;
    $check_out_dt = null;

    if (!empty($check_in_date_str) && !empty($check_out_date_str)) {
        try {
            $check_in_dt = new DateTime($check_in_date_str);
            $check_out_dt = new DateTime($check_out_date_str);
            $today = new DateTime('today'); 

            if ($check_in_dt < $today) { 
                $errors[] = "Check-in date cannot be in the past.";
            }
            if ($check_out_dt <= $check_in_dt) { 
                $errors[] = "Check-out date must be after the check-in date.";
            } else {
                $interval = $check_in_dt->diff($check_out_dt);
                $nights = $interval->days;
                if ($nights <= 0) {
                    $errors[] = "Booking must be for at least 1 night.";
                } elseif ($nights > 30) { 
                    $errors[] = "Booking cannot exceed 30 nights.";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Invalid date format. Please use YYYY-MM-DD.";
            error_log("Date parsing error: " . $e->getMessage());
        }
    }

    $allowed_payment_options = ['Bkash', 'Nagad', 'Rocket'];
    if (empty($payment_option) || !in_array($payment_option, $allowed_payment_options)) {
        $errors[] = "Please select a valid payment option.";
    }
    if (empty($transaction_id) || !preg_match('/^[A-Za-z0-9]{8,10}$/', $transaction_id)) {
        $errors[] = "Transaction ID must be 8-10 alphanumeric characters.";
    }

    // Database e hotel ebong room type (jodi thake) exist kore kina check kora
    $hotel_exists = false;
    if ($hotel_id > 0) {
        $stmt_check_h = $conn->prepare("SELECT hotel_id FROM hotels WHERE hotel_id = :hid");
        $stmt_check_h->execute(['hid' => $hotel_id]);
        if ($stmt_check_h->fetch()) {
            $hotel_exists = true;
        } else {
            $errors[] = "The selected hotel does not appear to be valid.";
        }
    }

    if ($hotel_exists && $room_type_id !== null && $room_type_id > 0) { 
        $stmt_check_r = $conn->prepare("SELECT room_type_id FROM room_types WHERE room_type_id = :rtid AND hotel_id = :hid");
        $stmt_check_r->execute(['rtid' => $room_type_id, 'hid' => $hotel_id]);
        if (!$stmt_check_r->fetch()) {
            $errors[] = "The selected room type is not valid for this hotel.";
        }
    } elseif ($room_type_id === false && filter_input(INPUT_POST, 'room_type_id') !== null) {
        // This means room_type_id was provided but was not a valid integer >= 1
        $errors[] = "Invalid room type selection.";
    }


    // Critical data check before attempting insert
    if (empty($errors)) { // Shudhu jodi age kono error na thake
        if ($nights <= 0) $errors[] = "Number of nights calculation failed or is invalid.";
        if ($price_per_night_for_booking <= 0) $errors[] = "Room price is invalid.";
        // Ensure dates are valid DateTime objects before formatting for DB
        if (!$check_in_dt || !$check_out_dt) $errors[] = "Date objects could not be created from input.";
    }


    // Jodi kono error na thake, tahole booking insert kora hobe
    if (empty($errors)) {
        $total_price = $nights * $price_per_night_for_booking;

        $sql_insert = "INSERT INTO bookings (user_id, hotel_id, room_type_id, full_name, phone_number, email, nid_passport, country, check_in_date, check_out_date, nights, room_price_at_booking, total_price, payment_option, transaction_id, booking_status, created_at)
                       VALUES (:user_id, :hotel_id, :room_type_id, :full_name, :phone_number, :email, :nid_passport, :country, :check_in_date, :check_out_date, :nights, :room_price_at_booking, :total_price, :payment_option, :transaction_id, 'pending', NOW())";
        
        $stmt_insert = $conn->prepare($sql_insert);
        $params_to_insert = [
            ':user_id' => $user_id,
            ':hotel_id' => $hotel_id,
            ':room_type_id' => $room_type_id, 
            ':full_name' => $full_name,
            ':phone_number' => $phone_number,
            ':email' => $email,
            ':nid_passport' => $nid_passport,
            ':country' => $country,
            ':check_in_date' => $check_in_dt->format('Y-m-d'), // DateTime object theke format kora
            ':check_out_date' => $check_out_dt->format('Y-m-d'), // DateTime object theke format kora
            ':nights' => $nights,
            ':room_price_at_booking' => $price_per_night_for_booking,
            ':total_price' => $total_price,
            ':payment_option' => $payment_option,
            ':transaction_id' => $transaction_id
        ];

        try {
            if ($stmt_insert->execute($params_to_insert)) {
                $booking_id = $conn->lastInsertId();
                unset($_SESSION['booking_form_old_data']); 
                header("Location: booking_confirmation.php?booking_id=$booking_id&status=success");
                exit();
            } else {
                $errors[] = "Booking failed due to a server issue. Please try again later.";
                error_log("Booking Insert Failed (Non-PDO Exception): " . print_r($stmt_insert->errorInfo(), true) . " | Params: " . print_r($params_to_insert, true));
            }
        } catch (PDOException $e) {
            error_log("PDOException during booking: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | File: " . $e->getFile() . " | Line: " . $e->getLine() . " | Params: " . print_r($params_to_insert, true));
            $errors[] = "An unexpected database error occurred. Please contact support. (Error Code: PB01S)"; 
        }
    }

    // Jodi error thake, form page e redirect kora
    if (!empty($errors)) {
        $_SESSION['booking_form_errors'] = $errors;
        
        $redirect_get_params_on_error = [
            'hotel_id' => $_POST['hotel_id'] ?? $hotel_id,
        ];
        if (isset($_POST['hotel_name'])) $redirect_get_params_on_error['hotel_name'] = $_POST['hotel_name'];
        if (isset($_POST['room_type_id'])) $redirect_get_params_on_error['room_type_id'] = $_POST['room_type_id'];
        if (isset($_POST['room_name'])) $redirect_get_params_on_error['room_name'] = $_POST['room_name'];
        if (isset($_POST['price_per_night_for_booking'])) $redirect_get_params_on_error['room_price'] = $_POST['price_per_night_for_booking'];

        header("Location: book_hotel.php?" . http_build_query($redirect_get_params_on_error));
        exit();
    }

} else {
    // Jodi POST request na hoy
    header("Location: all_hotels.php");
    exit();
}
?>
