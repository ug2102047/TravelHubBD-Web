<?php
require_once 'config.php';
require_once 'auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'update_info') {
        // --- Process Profile Information Update ---
        $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $phone_number = trim(filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING));
        $dob_str = trim(filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING));
        $district = trim(filter_input(INPUT_POST, 'district', FILTER_SANITIZE_STRING));
        $is_traveler = in_array($_POST['is_traveler'], ['Yes', 'No']) ? $_POST['is_traveler'] : 'No';

        $errors_info = [];

        if (empty($full_name)) $errors_info[] = "Full name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors_info[] = "A valid email address is required.";
        if (empty($phone_number) || !preg_match("/^01[3-9][0-9]{8}$/", $phone_number)) $errors_info[] = "A valid 11-digit Bangladeshi phone number is required.";
        if (empty($dob_str)) {
            $errors_info[] = "Date of birth is required.";
        } else {
            try {
                $dob_obj = new DateTime($dob_str);
                // Optional: Add age validation if needed
            } catch (Exception $e) {
                $errors_info[] = "Invalid date of birth format.";
            }
        }
        if (empty($district)) $errors_info[] = "District is required.";

        // Check if email already exists for another user
        if (empty($errors_info)) {
            $stmt_email_check = $conn->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :user_id");
            $stmt_email_check->execute([':email' => $email, ':user_id' => $user_id]);
            if ($stmt_email_check->fetch()) {
                $errors_info[] = "This email address is already registered by another user.";
            }
        }

        if (empty($errors_info)) {
            try {
                $sql_update_info = "UPDATE users SET 
                                    full_name = :full_name, 
                                    email = :email, 
                                    phone_number = :phone_number, 
                                    dob = :dob, 
                                    district = :district, 
                                    is_traveler = :is_traveler 
                                  WHERE user_id = :user_id";
                $stmt_update = $conn->prepare($sql_update_info);
                $stmt_update->execute([
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':phone_number' => $phone_number,
                    ':dob' => $dob_str, // Store as YYYY-MM-DD string
                    ':district' => $district,
                    ':is_traveler' => $is_traveler,
                    ':user_id' => $user_id
                ]);

                $_SESSION['profile_update_success'] = "Your profile information has been updated successfully!";
                // Update session variables if they exist (e.g., email, full_name if you use them elsewhere)
                $_SESSION['user_full_name'] = $full_name; // Example
                $_SESSION['user_email'] = $email;       // Example
                
            } catch (PDOException $e) {
                error_log("Error updating profile info for user_id {$user_id}: " . $e->getMessage());
                $_SESSION['profile_update_errors'] = ["A database error occurred. Please try again."];
            }
        } else {
            $_SESSION['profile_update_errors'] = $errors_info;
        }
        header("Location: my_profile.php#personal-info-section"); // Redirect back to profile page
        exit();

    } elseif ($_POST['action'] == 'change_password') {
        // --- Process Password Change ---
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        $errors_pass = [];

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $errors_pass[] = "All password fields are required.";
        } else {
            if (strlen($new_password) < 6) {
                $errors_pass[] = "New password must be at least 6 characters long.";
            }
            if ($new_password !== $confirm_new_password) {
                $errors_pass[] = "New password and confirmation password do not match.";
            }
        }
        
        if (empty($errors_pass)) {
            // Fetch current hashed password from DB
            $stmt_curr_pass = $conn->prepare("SELECT password FROM users WHERE user_id = :user_id");
            $stmt_curr_pass->execute([':user_id' => $user_id]);
            $user_db_data = $stmt_curr_pass->fetch(PDO::FETCH_ASSOC);

            if ($user_db_data && password_verify($current_password, $user_db_data['password'])) {
                // Current password matches, proceed to update
                try {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql_update_pass = "UPDATE users SET password = :new_password WHERE user_id = :user_id";
                    $stmt_update_p = $conn->prepare($sql_update_pass);
                    $stmt_update_p->execute([
                        ':new_password' => $new_hashed_password,
                        ':user_id' => $user_id
                    ]);
                    $_SESSION['password_change_success'] = "Your password has been changed successfully!";
                } catch (PDOException $e) {
                    error_log("Error changing password for user_id {$user_id}: " . $e->getMessage());
                    $_SESSION['password_change_errors'] = ["A database error occurred while changing password."];
                }
            } else {
                $errors_pass[] = "Incorrect current password.";
            }
        }
        
        if (!empty($errors_pass)) {
            $_SESSION['password_change_errors'] = $errors_pass;
        }
        header("Location: my_profile.php#password-section"); // Redirect back to profile page
        exit();
    } else {
        // Invalid action
        header("Location: my_profile.php?error=invalid_action");
        exit();
    }

} else {
    // Not a POST request or action not set
    header("Location: index.php");
    exit();
}
?>
