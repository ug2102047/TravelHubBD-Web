    <?php
    // Check if a session is already active before starting a new one
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    function isAdmin() {
        if (!isLoggedIn()) {
            return false;
        }
        // Ensure config.php is included. If it's already included by another file that includes auth.php,
        // require_once will prevent it from being included again.
        require_once 'config.php'; 

        // It's good practice to ensure $conn is available.
        // config.php should establish the $conn variable.
        // If $conn might not be global, you might need to pass it or access it differently.
        global $conn; // Assuming $conn is a global variable from config.php

        if (!$conn) {
            // Handle database connection error if necessary, though config.php might die on error.
            error_log("Database connection not available in isAdmin function.");
            return false; 
        }

        try {
            $sql = "SELECT role FROM users WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user && $user['role'] === 'admin';
        } catch (PDOException $e) {
            // Log error or handle it appropriately
            error_log("Error in isAdmin function: " . $e->getMessage());
            return false;
        }
    }
    ?>
    