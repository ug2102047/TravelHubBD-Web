<?php
require_once 'config.php';
require_once 'auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header("Location: login.php?redirect=" . urlencode('my_profile.php'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt_user = $conn->prepare("SELECT full_name, username, dob, email, phone_number, district, is_traveler FROM users WHERE user_id = :user_id");
$stmt_user->execute([':user_id' => $user_id]);
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    // Should not happen if user is logged in, but as a safeguard
    session_unset();
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit();
}

// Get status messages from session if redirected from process_update_profile.php
$profile_update_success = $_SESSION['profile_update_success'] ?? null;
$profile_update_errors = $_SESSION['profile_update_errors'] ?? [];
$password_change_success = $_SESSION['password_change_success'] ?? null;
$password_change_errors = $_SESSION['password_change_errors'] ?? [];

unset($_SESSION['profile_update_success'], $_SESSION['profile_update_errors'], 
      $_SESSION['password_change_success'], $_SESSION['password_change_errors']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo htmlspecialchars($user_data['username']); ?> - Travel Hub</title>
    <link rel="stylesheet" href="CSS/my_profile.css">
    <link rel="stylesheet" href="CSS/hotel_styles_v2.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <h2><i class="fas fa-user-cog"></i> My Profile Settings</h2>

        <section class="profile-section">
            <h3><i class="fas fa-id-card"></i> Personal Information</h3>
            <?php if ($profile_update_success): ?>
                <p class="message-area success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($profile_update_success); ?></p>
            <?php endif; ?>
            <?php if (!empty($profile_update_errors)): ?>
                <div class="message-area error">
                    <p><i class="fas fa-exclamation-triangle"></i> Could not update profile. Please fix the errors:</p>
                    <ul>
                        <?php foreach ($profile_update_errors as $error): ?>
                            <li>- <?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="process_update_profile.php" method="POST">
                <input type="hidden" name="action" value="update_info">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                    <small style="color:#777;">Username cannot be changed.</small>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="tel" id="phone_number" name="phone_number" pattern="01[3-9][0-9]{8}" title="Enter a valid 11-digit Bangladeshi mobile number" value="<?php echo htmlspecialchars($user_data['phone_number']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user_data['dob']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="district">District:</label>
                    <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($user_data['district']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="is_traveler">Are you a traveler?</label>
                    <select id="is_traveler" name="is_traveler" required>
                        <option value="Yes" <?php echo ($user_data['is_traveler'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="No" <?php echo ($user_data['is_traveler'] == 'No') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <button type="submit" class="btn-update-profile"><i class="fas fa-save"></i> Update Information</button>
            </form>
        </section>

        <section class="profile-section">
            <h3><i class="fas fa-key"></i> Change Password</h3>
            <?php if ($password_change_success): ?>
                <p class="message-area success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($password_change_success); ?></p>
            <?php endif; ?>
            <?php if (!empty($password_change_errors)): ?>
                <div class="message-area error">
                     <p><i class="fas fa-exclamation-triangle"></i> Could not change password. Please fix the errors:</p>
                    <ul>
                        <?php foreach ($password_change_errors as $error): ?>
                            <li>- <?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="process_update_profile.php" method="POST">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" minlength="6" required placeholder="Minimum 6 characters">
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password:</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                </div>
                <button type="submit" class="btn-update-profile" style="background-color:#e67e22;"><i class="fas fa-lock"></i> Change Password</button>
            </form>
        </section>

    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
