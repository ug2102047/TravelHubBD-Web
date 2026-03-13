<?php
require_once 'config.php';
require_once 'auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    // Try to build a redirect URL back to the current page or a sensible default
    $current_page_url = 'index.php'; // Default fallback
    if(isset($_SERVER['REQUEST_URI'])) {
        $current_page_url = basename($_SERVER['REQUEST_URI']);
    }
    header("Location: login.php?redirect=" . urlencode($current_page_url));
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Get hotel_id, spot_id, search query, and page from URL
$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$spot_id = isset($_GET['spot_id']) ? (int)$_GET['spot_id'] : 0;
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? trim($_GET['page']) : ''; // e.g., 'all_hotels', 'hotel_details', 'sea', 'destination'
$spot_location_for_redirect = isset($_GET['spot_location']) ? trim($_GET['spot_location']) : ''; // For refine_hotel_search redirect

// Validate input
if ($hotel_id <= 0 && $spot_id <= 0) {
    $error_message = "Invalid item specified.";
    // Attempt to redirect back intelligently or to a default page
    if (!empty($page)) {
        if ($page === 'hotel_details' && $hotel_id > 0) {
             header("Location: hotel_details.php?hotel_id={$hotel_id}&error=" . urlencode($error_message));
        } elseif (file_exists($page . ".php")) {
            header("Location: {$page}.php?error=" . urlencode($error_message));
        } else {
            header("Location: index.php?error=" . urlencode($error_message));
        }
    } elseif (!empty($search_query)) {
        header("Location: search_results.php?q=" . urlencode($search_query) . "&error=" . urlencode($error_message));
    } else {
        header("Location: index.php?error=" . urlencode($error_message));
    }
    exit();
}

$message = '';
$error = '';

// Handle hotel wishlist
if ($hotel_id > 0) {
    // Check if hotel exists
    $sql_check = "SELECT hotel_id FROM hotels WHERE hotel_id = :hotel_id AND country = :country";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute(['hotel_id' => $hotel_id, 'country' => 'Bangladesh']);
    if (!$stmt_check->fetch()) {
        $error = "Hotel not found.";
    } else {
        // Check if already in wishlist
        $sql_exists = "SELECT wishlist_id FROM wishlist WHERE user_id = :user_id AND hotel_id = :hotel_id";
        $stmt_exists = $conn->prepare($sql_exists);
        $stmt_exists->execute(['user_id' => $user_id, 'hotel_id' => $hotel_id]);
        if ($stmt_exists->fetch()) {
            $message = "Hotel already in wishlist.";
        } else {
            // Add to wishlist
            $sql_add = "INSERT INTO wishlist (user_id, hotel_id, added_date) VALUES (:user_id, :hotel_id, NOW())";
            $stmt_add = $conn->prepare($sql_add);
            if ($stmt_add->execute(['user_id' => $user_id, 'hotel_id' => $hotel_id])) {
                $message = "Hotel added to wishlist!";
            } else {
                $error = "Failed to add hotel to wishlist.";
            }
        }
    }
    // Redirect for hotel
    $redirect_params = $error ? "error=" . urlencode($error) : "message=" . urlencode($message);
    if ($page === 'hotel_details') {
        header("Location: hotel_details.php?hotel_id={$hotel_id}&{$redirect_params}");
    } elseif ($page === 'all_hotels') {
        header("Location: all_hotels.php?{$redirect_params}");
    } else { // Fallback for older pages or if 'page' is not specific to new hotel pages
        $fallback_page = !empty($page) && file_exists($page . ".php") ? $page . ".php" : "all_hotels.php";
        header("Location: {$fallback_page}?{$redirect_params}");
    }
    exit();
}

// Handle spot wishlist
if ($spot_id > 0) {
    // Check if spot exists
    $sql_check = "SELECT spot_id FROM tourist_spots WHERE spot_id = :spot_id AND country = :country";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute(['spot_id' => $spot_id, 'country' => 'Bangladesh']);
    if (!$stmt_check->fetch()) {
        $error = "Spot not found.";
    } else {
        // Check if already in wishlist
        $sql_exists = "SELECT wishlist_id FROM wishlist WHERE user_id = :user_id AND spot_id = :spot_id";
        $stmt_exists = $conn->prepare($sql_exists);
        $stmt_exists->execute(['user_id' => $user_id, 'spot_id' => $spot_id]);
        if ($stmt_exists->fetch()) {
            $message = "Spot already in wishlist.";
        } else {
            // Add to wishlist
            $sql_add = "INSERT INTO wishlist (user_id, spot_id, added_date) VALUES (:user_id, :spot_id, NOW())";
            $stmt_add = $conn->prepare($sql_add);
            if ($stmt_add->execute(['user_id' => $user_id, 'spot_id' => $spot_id])) {
                $message = "Spot added to wishlist!";
            } else {
                $error = "Failed to add spot to wishlist.";
            }
        }
    }
    // Redirect for spot
    $redirect_params = $error ? "error=" . urlencode($error) : "message=" . urlencode($message);
    if (!empty($search_query)) {
        header("Location: search_results.php?q=" . urlencode($search_query) . "&{$redirect_params}");
    } elseif (!empty($page) && file_exists($page . ".php")) {
        // For pages like sea.php, ctg.php etc.
        header("Location: {$page}.php?{$redirect_params}");
    } elseif (!empty($spot_location_for_redirect)) {
        // If coming from refine_hotel_search or similar context for a spot
         header("Location: refine_hotel_search.php?spot_location=" . urlencode($spot_location_for_redirect) . "&{$redirect_params}");
    }
     else {
        header("Location: destination.php?{$redirect_params}"); // Default spot listing page
    }
    exit();
}
?>

