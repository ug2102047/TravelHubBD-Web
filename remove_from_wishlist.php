<?php
require_once 'config.php';
require_once 'auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    // Try to redirect back to the wishlist page or a default
    header("Location: login.php?redirect=wishlist2.php");
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Get wishlist_id from URL
$wishlist_id = isset($_GET['wishlist_id']) ? (int)$_GET['wishlist_id'] : 0;

// Validate wishlist_id
if ($wishlist_id <= 0) {
    // Redirect back to wishlist2.php with an error message
    header("Location: wishlist2.php?error=" . urlencode("Invalid wishlist item specified."));
    exit();
}

// Attempt to remove the item from the wishlist
$sql = "DELETE FROM wishlist WHERE wishlist_id = :wishlist_id AND user_id = :user_id";
$stmt = $conn->prepare($sql);

if ($stmt->execute(['wishlist_id' => $wishlist_id, 'user_id' => $user_id])) {
    if ($stmt->rowCount() > 0) {
        // Item successfully removed
        header("Location: wishlist2.php?message=" . urlencode("Item successfully removed from wishlist."));
    } else {
        // Item not found for this user, or already removed
        header("Location: wishlist2.php?error=" . urlencode("Could not remove item. It might have been already removed or does not belong to you."));
    }
} else {
    // SQL execution failed
    header("Location: wishlist2.php?error=" . urlencode("An error occurred while trying to remove the item."));
}
exit();
?>
