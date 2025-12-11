<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $district = $_POST['district'];
    $is_traveler = $_POST['is_traveler'];

    // Check if username or email already exists
    $sql_check = "SELECT user_id FROM users WHERE username = :username OR email = :email";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute(['username' => $username, 'email' => $email]);
    $result_check = $stmt_check->fetch();

    if ($result_check) {
        $error = "Username or email already exists.";
    } else {
        $sql = "INSERT INTO users (full_name, username, dob, email, phone_number, password, district, is_traveler) 
                VALUES (:full_name, :username, :dob, :email, :phone, :password, :district, :is_traveler)";
        $stmt = $conn->prepare($sql);
        $params = [
            'full_name' => $full_name,
            'username' => $username,
            'dob' => $dob,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'district' => $district,
            'is_traveler' => $is_traveler
        ];

        if ($stmt->execute($params)) {
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Error: Unable to register user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Tourism Management System</title>
    <link rel="stylesheet" href="CSS/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navigation Menu -->
    <?php include 'navbar.php'; ?>

    <!-- Signup Form -->
    <section class="signup-form">
        <h2>Sign Up</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="signup.php" method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="district">District:</label>
            <input type="text" id="district" name="district" required>

            <label for="is_traveler">Are you a traveler?</label>
            <select id="is_traveler" name="is_traveler" required>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>