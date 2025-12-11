<?php
require_once 'config.php';
require_once 'auth.php'; // For isLoggedIn() function

if (session_status() == PHP_SESSION_NONE) { // Ensure session is started for messages
    session_start();
}

if (!isset($_GET['spot_id']) || empty($_GET['spot_id'])) {
    header("Location: destination.php?error=Spot ID is missing");
    exit();
}

$spot_id = (int)$_GET['spot_id'];

// Fetch spot details
$sql_spot = "SELECT ts.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_reviews 
             FROM tourist_spots ts
             LEFT JOIN reviews r ON ts.spot_id = r.spot_id
             WHERE ts.spot_id = :spot_id
             GROUP BY ts.spot_id";
$stmt_spot = $conn->prepare($sql_spot);
$stmt_spot->execute(['spot_id' => $spot_id]);
$spot = $stmt_spot->fetch(PDO::FETCH_ASSOC);

if (!$spot) {
    header("Location: destination.php?error=Tourist spot not found");
    exit();
}

// MODIFIED: Fetch spot images from the 'gallery' table for the current spot_id
$sql_spot_images = "SELECT image_id, image_url, caption FROM gallery WHERE spot_id = :spot_id ORDER BY image_id ASC";
$stmt_spot_images = $conn->prepare($sql_spot_images);
$stmt_spot_images->execute(['spot_id' => $spot_id]);
$spot_images_gallery = $stmt_spot_images->fetchAll(PDO::FETCH_ASSOC);

// Fallback to main spot image from 'tourist_spots' table if no images are found in 'gallery' for this spot
if (empty($spot_images_gallery) && !empty($spot['image_url'])) {
    $spot_images_gallery[] = ['image_url' => $spot['image_url'], 'caption' => $spot['name'] . ' Main View'];
}


// Fetch reviews for this spot
$sql_reviews = "SELECT r.*, u.full_name as user_full_name, u.username as user_username 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.spot_id = :spot_id 
                ORDER BY r.review_date DESC LIMIT 10";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->execute(['spot_id' => $spot_id]);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

// Fetch nearby hotels
$nearby_hotels = [];
if (!empty($spot['location'])) {
    $sql_nearby_hotels = "SELECT hotel_id, name, image_url, price_per_night, star_rating 
                          FROM hotels 
                          WHERE location LIKE :location AND country = :country 
                          ORDER BY star_rating DESC, price_per_night ASC 
                          LIMIT 5"; 
    $stmt_nearby_hotels = $conn->prepare($sql_nearby_hotels);
    $stmt_nearby_hotels->execute(['location' => '%' . $spot['location'] . '%', 'country' => 'Bangladesh']);
    $nearby_hotels = $stmt_nearby_hotels->fetchAll(PDO::FETCH_ASSOC);
}

// Retrieve review submission status messages
$review_submission_success = null;
$review_submission_errors = $_SESSION['review_submission_errors'] ?? [];
if (isset($_GET['review_message'])) {
    $review_submission_success = urldecode($_GET['review_message']);
} elseif (isset($_GET['review_error']) && empty($review_submission_errors)) { 
    $review_submission_errors[] = urldecode($_GET['review_error']);
}
unset($_SESSION['review_submission_errors']); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($spot['name']); ?> - Details</title>
    <link rel="stylesheet" href="CSS/hotel_details.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="hotel-details-page"> 
        <header class="spot-main-header"> 
            <h1><?php echo htmlspecialchars($spot['name']); ?></h1>
            <p class="location-info">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($spot['location']); ?>
                <?php if(!empty($spot['division'])): ?>
                    , <?php echo htmlspecialchars($spot['division']); ?> Division
                <?php endif; ?>
            </p>
            <span class="spot-type-badge"><?php echo htmlspecialchars($spot['type']); ?></span>
            <?php if($spot['avg_rating']): ?>
            <div class="guest-rating large" style="margin-top:10px;">
                Average Rating: <strong><?php echo number_format($spot['avg_rating'], 1); ?>/5</strong> (<?php echo $spot['total_reviews']; ?> Reviews)
            </div>
            <?php endif; ?>
        </header>

        <section class="image-gallery-section card-style">
            <h2><i class="fas fa-images"></i> Image Gallery</h2>
            <?php if (!empty($spot_images_gallery)): ?>
                <div class="image-gallery-grid <?php echo (count($spot_images_gallery) == 1 && empty($spot_images_gallery[0]['caption'])) ? 'single-image' : ''; ?>">
                    <?php foreach ($spot_images_gallery as $index => $img): ?>
                        <a href="<?php echo htmlspecialchars($img['image_url']); ?>" data-lightbox="spot-gallery-lightbox-<?php echo $spot_id; ?>" data-title="<?php echo htmlspecialchars($img['caption'] ?: $spot['name']); ?>">
                            <img src="<?php echo htmlspecialchars($img['image_url']); ?>" alt="<?php echo htmlspecialchars($img['caption'] ?: $spot['name'] . ' image ' . ($index + 1)); ?>">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No additional images available for this tourist spot.</p>
                 <img src="https://placehold.co/800x500/E1E1E1/A0A0A0?text=No+Image+Available" alt="No Images Available" style="width:100%; max-width: 800px; height:auto; border-radius: 8px;">
            <?php endif; ?>
        </section>

        <div class="details-content-wrapper">
            <main class="hotel-main-content"> 
                <section class="hotel-description-section card-style"> 
                    <h2><i class="fas fa-info-circle"></i> Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($spot['description'] ?: 'No detailed description available for this spot.')); ?></p>
                </section>

                <?php if (!empty($spot['historical_significance'])): ?>
                <section class="hotel-description-section card-style"> 
                    <h2><i class="fas fa-landmark"></i> Historical Significance</h2>
                    <p><?php echo nl2br(htmlspecialchars($spot['historical_significance'])); ?></p>
                </section>
                <?php endif; ?>
                
                <section class="card-style" id="plan-visit-section">
                    <h2><i class="fas fa-clipboard-list"></i> Plan Your Visit</h2>
                    <ul class="plan-visit-list">
                        <?php if(!empty($spot['best_time_to_visit'])): ?>
                            <li><i class="fas fa-calendar-alt icon"></i><strong>Best Time to Visit:</strong> <?php echo htmlspecialchars($spot['best_time_to_visit']); ?></li>
                        <?php endif; ?>
                        <?php if(!empty($spot['entry_fee'])): ?>
                            <li><i class="fas fa-ticket-alt icon"></i><strong>Entry Fee:</strong> <?php echo htmlspecialchars($spot['entry_fee']); ?></li>
                        <?php endif; ?>
                        <?php if(!empty($spot['opening_hours'])): ?>
                            <li><i class="fas fa-clock icon"></i><strong>Opening Hours:</strong> <?php echo htmlspecialchars($spot['opening_hours']); ?></li>
                        <?php endif; ?>
                        <?php 
                        if (!empty($spot['things_to_do'])):
                            $things_to_do_list = array_map('trim', explode(',', $spot['things_to_do']));
                        ?>
                            <li>
                                <i class="fas fa-tasks icon"></i><strong>Things to do:</strong>
                                <ul style="list-style-type: disc; padding-left: 40px; margin-top: 5px;">
                                    <?php foreach($things_to_do_list as $thing): ?>
                                        <li><?php echo htmlspecialchars($thing); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <?php if(!empty($spot['how_to_go'])): ?>
                             <li><i class="fas fa-directions icon"></i><strong>How to Go:</strong><br><?php echo nl2br(htmlspecialchars($spot['how_to_go'])); ?></li>
                        <?php endif; ?>
                        <?php if(!empty($spot['special_tips'])): ?>
                            <li><i class="fas fa-star-of-life icon"></i><strong>Special Tips:</strong><br><?php echo nl2br(htmlspecialchars($spot['special_tips'])); ?></li>
                        <?php endif; ?>
                         <?php if(!empty($spot['contact_info'])): ?>
                            <li><i class="fas fa-address-book icon"></i><strong>Contact Info:</strong> <?php echo htmlspecialchars($spot['contact_info']); ?></li>
                        <?php endif; ?>
                    </ul>
                </section>

                <section class="hotel-reviews-section card-style" id="reviews-section"> 
                    <h2><i class="fas fa-comments"></i> Visitor Reviews</h2>
                    <?php if ($review_submission_success): ?>
                        <p class="message-area success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($review_submission_success); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($review_submission_errors)): ?>
                        <div class="message-area error">
                            <p><i class="fas fa-exclamation-triangle"></i> Could not submit review. Please fix the errors:</p>
                            <ul>
                                <?php foreach ($review_submission_errors as $error): ?>
                                    <li>- <?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-author">
                                        <strong><?php echo htmlspecialchars($review['user_full_name'] ?: $review['user_username']); ?></strong>
                                        <span class="review-date">- <?php echo date("M d, Y", strtotime($review['review_date'])); ?></span>
                                    </div>
                                    <div class="review-rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo ($i <= $review['rating']) ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No reviews yet for this tourist spot. Be the first to share your experience!</p>
                    <?php endif; ?>
                    
                     <?php if(isLoggedIn()): ?>
                        <div class="add-review-form card-style" style="margin-top: 20px; background-color:#f9f9f9;">
                            <h4><i class="fas fa-pencil-alt"></i> Leave Your Review:</h4>
                            <form action="submit_review.php" method="POST"> 
                                <input type="hidden" name="spot_id" value="<?php echo $spot_id; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars("spot_details.php?spot_id=".$spot_id."#reviews-section"); ?>">
                                <div class="form-group">
                                    <label for="rating_spot_page">Rating:</label>
                                    <select name="rating" id="rating_spot_page" required class="form-control">
                                        <option value="">-- Select Rating --</option>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Very Good</option>
                                        <option value="3">3 Stars - Good</option>
                                        <option value="2">2 Stars - Fair</option>
                                        <option value="1">1 Star - Poor</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="comment_spot_page">Comment:</label>
                                    <textarea name="comment" id="comment_spot_page" rows="4" required class="form-control" placeholder="Share your experience..."></textarea>
                                </div>
                                <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Submit Review</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p style="margin-top: 20px;"><a href="login.php?redirect=<?php echo urlencode("spot_details.php?spot_id=".$spot_id."#reviews-section"); ?>">Login</a> to leave a review.</p>
                    <?php endif; ?>
                </section>
            </main>

            <aside class="hotel-sidebar"> 
                <div class="booking-widget card-style"> 
                    <h3><i class="fas fa-lightbulb"></i> Quick Actions</h3>
                    <a href="refine_hotel_search.php?spot_location=<?php echo urlencode($spot['location']); ?>&spot_name=<?php echo urlencode($spot['name']); ?>" class="btn-primary btn-block">
                        <i class="fas fa-hotel"></i> Find Nearby Hotels
                    </a>
                     <?php if (isLoggedIn()): ?>
                        <a href="add_to_wishlist.php?spot_id=<?php echo $spot['spot_id']; ?>&page=spot_details&spot_name=<?php echo urlencode($spot['name']);?>" class="btn-wishlist-sidebar" title="Add to Wishlist">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </a>
                    <?php else: ?>
                        <a href="login.php?redirect=<?php echo urlencode("spot_details.php?spot_id=".$spot_id); ?>" class="btn-wishlist-sidebar" title="Login to Add to Wishlist">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </a>
                    <?php endif; ?>
                </div>

                <?php if(!empty($nearby_hotels)): ?>
                <div class="card-style">
                    <h3><i class="fas fa-concierge-bell"></i> Nearby Hotels</h3>
                    <div class="nearby-hotels-grid">
                        <?php foreach($nearby_hotels as $nearby_hotel): ?>
                            <div class="nearby-hotel-card">
                                <a href="hotel_details.php?hotel_id=<?php echo $nearby_hotel['hotel_id']; ?>">
                                    <img src="<?php echo !empty($nearby_hotel['image_url']) ? htmlspecialchars($nearby_hotel['image_url']) : 'https://placehold.co/300x200/E1E1E1/A0A0A0?text=Hotel'; ?>" alt="<?php echo htmlspecialchars($nearby_hotel['name']); ?>">
                                    <div class="nearby-hotel-card-info">
                                        <h5><?php echo htmlspecialchars($nearby_hotel['name']); ?></h5>
                                        <?php if($nearby_hotel['star_rating']): ?>
                                        <div class="star-rating-display small">
                                            <?php for($sidx = 1; $sidx <=5; $sidx++): ?>
                                            <i class="fas fa-star <?php echo ($sidx <= $nearby_hotel['star_rating']) ? 'filled' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <?php endif; ?>
                                        <p class="price">$<?php echo number_format($nearby_hotel['price_per_night'], 2); ?>/night</p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="all_hotels.php?location=<?php echo urlencode($spot['location']); ?>" class="btn-primary btn-block small" style="margin-top:15px;">View All Hotels in <?php echo htmlspecialchars($spot['location']); ?></a>
                </div>
                <?php endif; ?>
                
                <div class="map-widget card-style"> 
                    <h3><i class="fas fa-map-marked-alt"></i> View on Map</h3>
                    <?php 
                        $map_query_spot = htmlspecialchars($spot['name'] . ", " . $spot['location'] . ', ' . $spot['division'] . ', Bangladesh');
                    ?>
                    <iframe 
                        width="100%" 
                        height="250" 
                        frameborder="0" 
                        style="border:0; border-radius: 8px;"
                        src="https://maps.google.com/maps?q=<?php echo urlencode($map_query_spot); ?>&hl=en&z=14&output=embed" 
                        allowfullscreen>
                    </iframe>
                </div>
            </aside>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        lightbox.option({
          'resizeDuration': 200,
          'wrapAround': true,
          'disableScrolling': true
        })
    </script>
</body>
</html>
