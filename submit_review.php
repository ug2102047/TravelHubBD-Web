<?php
require_once 'config.php';
require_once 'auth.php'; // For isLoggedIn() and getting user_id

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    // If user is not logged in, try to redirect them back to where they came from, or login page
    $redirect_url = $_POST['redirect_url'] ?? 'login.php';
    if ($redirect_url == 'login.php' || empty($redirect_url)) { // Prevent redirect loop to login
         $redirect_url = 'index.php'; // Fallback to a safe page
    }
    header("Location: login.php?redirect=" . urlencode($redirect_url) . "&error=Please login to submit a review.");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING));
    
    $spot_id = filter_input(INPUT_POST, 'spot_id', FILTER_VALIDATE_INT);
    $hotel_id = filter_input(INPUT_POST, 'hotel_id', FILTER_VALIDATE_INT);
    
    // Determine the redirect URL (back to the details page or a general review page)
    $redirect_url = $_POST['redirect_url'] ?? 'review.php'; // Default to general review page
    if (empty($redirect_url) || $redirect_url == 'submit_review.php') { // Prevent redirecting to self
        $redirect_url = $spot_id ? "spot_details.php?spot_id=$spot_id" : ($hotel_id ? "hotel_details.php?hotel_id=$hotel_id" : "review.php");
    }


    $errors = [];
    if (!$rating) {
        $errors[] = "Invalid rating. Please select a rating between 1 and 5 stars.";
    }
    if (empty($comment)) {
        $errors[] = "Comment cannot be empty.";
    }
    if (strlen($comment) > 1000) { // Example max length
        $errors[] = "Comment is too long (maximum 1000 characters).";
    }

    if (!$spot_id && !$hotel_id) {
        $errors[] = "Invalid item to review. Missing spot or hotel ID.";
    }
    if ($spot_id && $hotel_id) {
        $errors[] = "Cannot review a spot and a hotel simultaneously in one submission.";
    }

    // Check if user has already reviewed this specific item (optional but good practice)
    if (empty($errors)) {
        $check_sql = "SELECT review_id FROM reviews WHERE user_id = :user_id AND ";
        $check_params = [':user_id' => $user_id];
        if ($spot_id) {
            $check_sql .= "spot_id = :item_id";
            $check_params[':item_id'] = $spot_id;
        } else {
            $check_sql .= "hotel_id = :item_id";
            $check_params[':item_id'] = $hotel_id;
        }
        
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->execute($check_params);
        if ($stmt_check->fetch()) {
            // Allow multiple reviews or update existing? For now, let's allow multiple.
            // If you want to prevent multiple reviews, add an error:
            // $errors[] = "You have already submitted a review for this item.";
        }
    }


    if (empty($errors)) {
        try {
            $sql_insert = "INSERT INTO reviews (user_id, spot_id, hotel_id, rating, comment, review_date) 
                           VALUES (:user_id, :spot_id, :hotel_id, :rating, :comment, NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            
            $insert_params = [
                ':user_id' => $user_id,
                ':spot_id' => $spot_id ?: null, // Store NULL if not a spot review
                ':hotel_id' => $hotel_id ?: null, // Store NULL if not a hotel review
                ':rating' => $rating,
                ':comment' => $comment
            ];

            if ($stmt_insert->execute($insert_params)) {
                // Success: Redirect back with a success message
                // Add '#reviews-section' or similar if your details pages have an anchor for reviews
                $query_separator = (strpos($redirect_url, '?') === false) ? '?' : '&';
                header("Location: " . $redirect_url . $query_separator . "review_message=" . urlencode("Review submitted successfully! Thank you."));
                exit();
            } else {
                $errors[] = "Failed to submit your review due to a database error. Please try again.";
                error_log("Review Insert Failed: " . print_r($stmt_insert->errorInfo(), true));
            }
        } catch (PDOException $e) {
            error_log("PDOException during review submission: " . $e->getMessage());
            $errors[] = "A critical database error occurred. Please try again later.";
        }
    }

    // If there were errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['review_submission_errors'] = $errors;
        // If you want to pass back old input for comment field:
        // $_SESSION['review_old_comment'] = $comment; 
        
        $query_separator = (strpos($redirect_url, '?') === false) ? '?' : '&';
        // Construct error query string
        $error_query_string = "review_error=" . urlencode(implode("<br>", $errors));
        header("Location: " . $redirect_url . $query_separator . $error_query_string);
        exit();
    }

} else {
    // If not a POST request, redirect to a default page
    header("Location: index.php");
    exit();
}
?>
