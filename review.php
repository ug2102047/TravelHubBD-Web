<?php
require_once 'config.php';
require_once 'auth.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_is_logged_in = isLoggedIn();
$current_user_id = $user_is_logged_in ? $_SESSION['user_id'] : null;

$submission_message = '';
$message_type = ''; // 'success' or 'error'

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_is_logged_in) {
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING));
    $review_type = $_POST['review_type'] ?? ''; // 'spot' or 'hotel'

    $item_id = null;
    if ($review_type === 'spot' && isset($_POST['spot_id'])) {
        $item_id = filter_input(INPUT_POST, 'spot_id', FILTER_VALIDATE_INT);
    } elseif ($review_type === 'hotel' && isset($_POST['hotel_id'])) {
        $item_id = filter_input(INPUT_POST, 'hotel_id', FILTER_VALIDATE_INT);
    }

    if (!$rating) {
        $submission_message = "Invalid rating selected.";
        $message_type = 'error';
    } elseif (empty($comment)) {
        $submission_message = "Comment cannot be empty.";
        $message_type = 'error';
    } elseif (!$item_id) {
        $submission_message = "Please select a valid destination or hotel.";
        $message_type = 'error';
    } else {
        try {
            $sql_insert_review = "";
            $params = [
                ':user_id' => $current_user_id,
                ':item_id' => $item_id,
                ':rating' => $rating,
                ':comment' => $comment
            ];

            if ($review_type === 'spot') {
                $sql_insert_review = "INSERT INTO reviews (user_id, spot_id, rating, comment, review_date) 
                                      VALUES (:user_id, :item_id, :rating, :comment, NOW())";
            } elseif ($review_type === 'hotel') {
                $sql_insert_review = "INSERT INTO reviews (user_id, hotel_id, rating, comment, review_date) 
                                      VALUES (:user_id, :item_id, :rating, :comment, NOW())";
            }

            if (!empty($sql_insert_review)) {
                $stmt_insert = $conn->prepare($sql_insert_review);
                if ($stmt_insert->execute($params)) {
                    $submission_message = "Your review has been submitted successfully! Thank you.";
                    $message_type = 'success';
                } else {
                    $submission_message = "Failed to submit your review. Please try again.";
                    $message_type = 'error';
                    error_log("Review submission failed: " . print_r($stmt_insert->errorInfo(), true));
                }
            } else {
                 $submission_message = "Invalid review type specified.";
                 $message_type = 'error';
            }
        } catch (PDOException $e) {
            $submission_message = "Database error: Could not submit review.";
            $message_type = 'error';
            error_log("PDOException during review submission: " . $e->getMessage());
        }
    }
}


// Fetch data for dropdowns
$all_spots = [];
$all_hotels = [];

if ($user_is_logged_in) { // Only fetch if user is logged in, for the forms
    try {
        $stmt_all_spots = $conn->query("SELECT spot_id, name FROM tourist_spots ORDER BY name ASC");
        $all_spots = $stmt_all_spots->fetchAll(PDO::FETCH_ASSOC);

        $stmt_all_hotels = $conn->query("SELECT hotel_id, name FROM hotels ORDER BY name ASC");
        $all_hotels = $stmt_all_hotels->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching spots/hotels for review form: " . $e->getMessage());
        // You might want to set an error message to display to the user
    }
}


// Fetch existing reviews for Tourist Spots
$sql_spot_reviews = "SELECT r.rating, r.comment, r.review_date, 
                            ts.name AS spot_name, ts.spot_id,
                            u.full_name AS user_full_name, u.username AS user_username
                     FROM reviews r
                     JOIN tourist_spots ts ON r.spot_id = ts.spot_id
                     JOIN users u ON r.user_id = u.user_id
                     WHERE r.spot_id IS NOT NULL
                     ORDER BY r.review_date DESC
                     LIMIT 20"; 
$stmt_spot_reviews = $conn->prepare($sql_spot_reviews);
$stmt_spot_reviews->execute();
$spot_reviews = $stmt_spot_reviews->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing reviews for Hotels
$sql_hotel_reviews = "SELECT r.rating, r.comment, r.review_date,
                             h.name AS hotel_name, h.hotel_id,
                             u.full_name AS user_full_name, u.username AS user_username
                      FROM reviews r
                      JOIN hotels h ON r.hotel_id = h.hotel_id
                      JOIN users u ON r.user_id = u.user_id
                      WHERE r.hotel_id IS NOT NULL
                      ORDER BY r.review_date DESC
                      LIMIT 20"; 
$stmt_hotel_reviews = $conn->prepare($sql_hotel_reviews);
$stmt_hotel_reviews->execute();
$hotel_reviews = $stmt_hotel_reviews->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reviews - Travel Hub Bangladesh</title> 
    <link rel="stylesheet" href="CSS/review.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="reviews-page-container">
        <h2 class="main-title"><i class="fas fa-comments"></i> Traveller Reviews</h2>
        <p class="page-subtitle">Read what fellow travellers are saying and share your own experiences!</p>

        <?php if (!empty($submission_message)): ?>
            <div class="status-message-display <?php echo $message_type; ?>">
                <i class="fas <?php echo ($message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?>"></i>
                <?php echo htmlspecialchars($submission_message); ?>
            </div>
        <?php endif; ?>

        <div class="reviews-layout">
            <div class="review-column" id="place-reviews">
                <h3><i class="fas fa-map-marked-alt"></i> Destination Reviews</h3>
                
                <?php if ($user_is_logged_in): ?>
                <div class="review-submission-form-container">
                    <h4><i class="fas fa-pencil-alt"></i> Review a Destination</h4>
                    <form action="review.php#place-reviews" method="POST">
                        <input type="hidden" name="review_type" value="spot">
                        <div class="form-group">
                            <label for="spot_id">Select Destination:</label>
                            <select name="spot_id" id="spot_id" required>
                                <option value="">-- Choose a Destination --</option>
                                <?php foreach ($all_spots as $spot): ?>
                                    <option value="<?php echo $spot['spot_id']; ?>"><?php echo htmlspecialchars($spot['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="spot_rating">Your Rating:</label>
                            <select name="rating" id="spot_rating" required>
                                <option value="">-- Rate --</option>
                                <option value="5">5 Stars (Excellent)</option>
                                <option value="4">4 Stars (Very Good)</option>
                                <option value="3">3 Stars (Good)</option>
                                <option value="2">2 Stars (Fair)</option>
                                <option value="1">1 Star (Poor)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="spot_comment">Your Comment:</label>
                            <textarea name="comment" id="spot_comment" rows="4" required placeholder="Share your experience..."></textarea>
                        </div>
                        <button type="submit" class="btn-submit-review"><i class="fas fa-paper-plane"></i> Submit Destination Review</button>
                    </form>
                </div>
                <?php else: ?>
                    <p class="login-prompt-review"><a href="login.php?redirect=<?php echo urlencode('review.php#place-reviews'); ?>">Login</a> to submit a review for a destination.</p>
                <?php endif; ?>

                <?php if (!empty($spot_reviews)): ?>
                    <?php foreach ($spot_reviews as $review): ?>
                        <div class="review-card">
                            <h4 class="review-item-name">
                                <a href="spot_details.php?spot_id=<?php echo $review['spot_id']; ?>">
                                    <?php echo htmlspecialchars($review['spot_name']); ?>
                                </a>
                            </h4>
                            <p class="review-author">By: <strong><?php echo htmlspecialchars($review['user_full_name'] ?: $review['user_username']); ?></strong></p>
                            <p class="review-date"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($review['review_date']))); ?></p>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo ($i <= $review['rating']) ? '' : 'empty'; // Assuming 'empty' class styles empty stars ?>"></i>
                                <?php endfor; ?>
                                (<?php echo $review['rating']; ?>/5)
                            </div>
                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-reviews-message">No reviews found for destinations yet. Be the first to share!</p>
                <?php endif; ?>
            </div>

            <div class="review-column" id="hotel-reviews">
                <h3><i class="fas fa-hotel"></i> Hotel Reviews</h3>

                <?php if ($user_is_logged_in): ?>
                <div class="review-submission-form-container">
                    <h4><i class="fas fa-pencil-alt"></i> Review a Hotel</h4>
                    <form action="review.php#hotel-reviews" method="POST">
                        <input type="hidden" name="review_type" value="hotel">
                        <div class="form-group">
                            <label for="hotel_id">Select Hotel:</label>
                            <select name="hotel_id" id="hotel_id" required>
                                <option value="">-- Choose a Hotel --</option>
                                <?php foreach ($all_hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['hotel_id']; ?>"><?php echo htmlspecialchars($hotel['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hotel_rating">Your Rating:</label>
                             <select name="rating" id="hotel_rating" required>
                                <option value="">-- Rate --</option>
                                <option value="5">5 Stars (Excellent)</option>
                                <option value="4">4 Stars (Very Good)</option>
                                <option value="3">3 Stars (Good)</option>
                                <option value="2">2 Stars (Fair)</option>
                                <option value="1">1 Star (Poor)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hotel_comment">Your Comment:</label>
                            <textarea name="comment" id="hotel_comment" rows="4" required placeholder="Share your experience..."></textarea>
                        </div>
                        <button type="submit" class="btn-submit-review"><i class="fas fa-paper-plane"></i> Submit Hotel Review</button>
                    </form>
                </div>
                <?php else: ?>
                     <p class="login-prompt-review"><a href="login.php?redirect=<?php echo urlencode('review.php#hotel-reviews'); ?>">Login</a> to submit a review for a hotel.</p>
                <?php endif; ?>

                <?php if (!empty($hotel_reviews)): ?>
                    <?php foreach ($hotel_reviews as $review): ?>
                        <div class="review-card">
                            <h4 class="review-item-name">
                                <a href="hotel_details.php?hotel_id=<?php echo $review['hotel_id']; ?>">
                                    <?php echo htmlspecialchars($review['hotel_name']); ?>
                                </a>
                            </h4>
                            <p class="review-author">By: <strong><?php echo htmlspecialchars($review['user_full_name'] ?: $review['user_username']); ?></strong></p>
                            <p class="review-date"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($review['review_date']))); ?></p>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo ($i <= $review['rating']) ? '' : 'empty'; ?>"></i>
                                <?php endfor; ?>
                                (<?php echo $review['rating']; ?>/5)
                            </div>
                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-reviews-message">No reviews found for hotels yet. Be the first to share!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
