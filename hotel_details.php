<?php
require_once 'config.php';
require_once 'auth.php'; // For isLoggedIn() function

if (session_status() == PHP_SESSION_NONE) { // Ensure session is started for messages
    session_start();
}

if (!isset($_GET['hotel_id']) || empty($_GET['hotel_id'])) {
    header("Location: all_hotels.php?error=Hotel ID is missing");
    exit();
}

$hotel_id = (int)$_GET['hotel_id'];

// Fetch hotel details
$sql_hotel = "SELECT h.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_reviews 
              FROM hotels h
              LEFT JOIN reviews r ON h.hotel_id = r.hotel_id
              WHERE h.hotel_id = :hotel_id AND h.country = :country
              GROUP BY h.hotel_id";
$stmt_hotel = $conn->prepare($sql_hotel);
$stmt_hotel->execute(['hotel_id' => $hotel_id, 'country' => 'Bangladesh']);
$hotel = $stmt_hotel->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    header("Location: all_hotels.php?error=Hotel not found");
    exit();
}

// MODIFIED: Fetch hotel images from the 'hotel_images' table as per your provided schema
$sql_images = "SELECT image_id, image_url, caption FROM hotel_images WHERE hotel_id = :hotel_id ORDER BY image_id ASC";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->execute(['hotel_id' => $hotel_id]);
$hotel_images_gallery = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

// Fallback to main hotel image from 'hotels' table if no images are found in 'hotel_images'
if (empty($hotel_images_gallery) && !empty($hotel['image_url'])) {
    $hotel_images_gallery[] = ['image_url' => $hotel['image_url'], 'caption' => $hotel['name'] . ' Main View'];
}


// Fetch room types
$sql_rooms = "SELECT * FROM room_types WHERE hotel_id = :hotel_id ORDER BY price_per_night ASC";
$stmt_rooms = $conn->prepare($sql_rooms);
$stmt_rooms->execute(['hotel_id' => $hotel_id]);
$room_types = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);

// Fetch reviews
$sql_reviews = "SELECT r.*, u.full_name as user_full_name, u.username as user_username 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.hotel_id = :hotel_id 
                ORDER BY r.review_date DESC LIMIT 10";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->execute(['hotel_id' => $hotel_id]);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

$hotel_amenities_list = !empty($hotel['amenities']) ? explode(',', $hotel['amenities']) : [];

// Retrieve review submission status messages
$review_submission_success_hotel = null;
$review_submission_errors_hotel = $_SESSION['review_submission_errors'] ?? [];

if (isset($_GET['review_message'])) {
    $review_submission_success_hotel = urldecode($_GET['review_message']);
} elseif (isset($_GET['review_error']) && empty($review_submission_errors_hotel) ) {
    $review_submission_errors_hotel[] = urldecode($_GET['review_error']);
}
unset($_SESSION['review_submission_errors']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['name']); ?> - Details</title>
    <link rel="stylesheet" href="CSS/hotel_details.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="hotel-details-page">
        <header class="hotel-main-header">
            <h1><?php echo htmlspecialchars($hotel['name']); ?></h1>
            <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
            <div class="header-details">
                <div class="star-rating-display large">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo ($i <= $hotel['star_rating']) ? 'filled' : ''; ?>"></i>
                    <?php endfor; ?>
                    (<?php echo $hotel['star_rating'] ? htmlspecialchars($hotel['star_rating']) . ' Star' : 'N/A'; ?>)
                </div>
                <?php if($hotel['avg_rating']): ?>
                <div class="guest-rating large">
                    Avg. Rating: <strong><?php echo number_format($hotel['avg_rating'], 1); ?>/5</strong> (<?php echo htmlspecialchars($hotel['total_reviews']); ?> Reviews)
                </div>
                <?php endif; ?>
            </div>
        </header>

        <section class="image-gallery-section card-style">
            <h2><i class="fas fa-images"></i> Image Gallery</h2>
            <?php if (!empty($hotel_images_gallery)): ?>
                <div class="image-gallery-grid <?php echo (count($hotel_images_gallery) == 1 && empty($hotel_images_gallery[0]['caption'])) ? 'single-image' : ''; ?>">
                    <?php foreach ($hotel_images_gallery as $index => $img): ?>
                        <a href="<?php echo htmlspecialchars($img['image_url']); ?>" data-lightbox="hotel-gallery-lightbox-<?php echo $hotel_id; ?>" data-title="<?php echo htmlspecialchars($img['caption'] ?: $hotel['name']); ?>">
                            <img src="<?php echo htmlspecialchars($img['image_url']); ?>" alt="<?php echo htmlspecialchars($img['caption'] ?: $hotel['name'] . ' image ' . ($index + 1)); ?>">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No additional images available for this hotel.</p>
                <?php if(!empty($hotel['image_url'])): // Fallback to main image from hotels table if gallery is empty AND main image exists ?>
                     <div class="image-gallery-grid single-image">
                        <a href="<?php echo htmlspecialchars($hotel['image_url']); ?>" data-lightbox="hotel-gallery-lightbox-<?php echo $hotel_id; ?>" data-title="<?php echo htmlspecialchars($hotel['name']); ?>">
                            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="width:100%; max-width: 800px; height:auto; border-radius: 8px;">
                        </a>
                    </div>
                <?php else: // If no images in hotel_images and no main image in hotels table ?>
                    <img src="https://placehold.co/800x500/E1E1E1/A0A0A0?text=No+Image+Available" alt="No Images Available" style="width:100%; max-width: 800px; height:auto; border-radius: 8px;">
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <div class="details-content-wrapper">
            <main class="hotel-main-content">
                <section class="hotel-description-section card-style">
                    <h2><i class="fas fa-info-circle"></i> Hotel Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($hotel['description'] ?: 'No detailed description available for this hotel.')); ?></p>
                </section>

                <section class="hotel-amenities-section card-style">
                    <h2><i class="fas fa-concierge-bell"></i> Hotel Amenities</h2>
                    <?php if (!empty($hotel_amenities_list)): ?>
                        <ul class="amenities-list">
                            <?php foreach ($hotel_amenities_list as $amenity): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(trim($amenity)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No information on hotel-wide amenities available.</p>
                    <?php endif; ?>
                </section>

                <section class="room-types-section card-style">
                    <h2><i class="fas fa-bed"></i> Room Types & Prices</h2>
                    <?php if (!empty($room_types)): ?>
                        <div class="room-types-grid">
                            <?php foreach ($room_types as $room): ?>
                                <div class="room-card">
                                    <div class="room-image-container">
                                        <img src="<?php echo !empty($room['image_url']) ? htmlspecialchars($room['image_url']) : 'https://placehold.co/300x200/EFEFEF/A0A0A0?text=Room'; ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="room-image">
                                    </div>
                                    <div class="room-details">
                                        <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                                        <p class="room-price">$<?php echo number_format($room['price_per_night'], 2); ?> /night</p>
                                        <?php if(!empty($room['capacity'])): ?>
                                            <p class="room-capacity"><i class="fas fa-users"></i> Capacity: <?php echo htmlspecialchars($room['capacity']); ?> guests</p>
                                        <?php endif; ?>
                                        <?php if(!empty($room['beds'])): ?>
                                            <p class="room-beds"><i class="fas fa-bed"></i> Beds: <?php echo htmlspecialchars($room['beds']); ?></p>
                                        <?php endif; ?>
                                        <a href="book_hotel.php?hotel_id=<?php echo $hotel_id; ?>&hotel_name=<?php echo urlencode($hotel['name']); ?>&room_type_id=<?php echo $room['room_type_id']; ?>&room_name=<?php echo urlencode($room['name']); ?>&room_price=<?php echo $room['price_per_night']; ?>" class="btn-book-room"><i class="fas fa-calendar-plus"></i> Book This Room</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No specific room types listed. Book a standard room based on general pricing.</p>
                        <a href="book_hotel.php?hotel_id=<?php echo $hotel_id; ?>&hotel_name=<?php echo urlencode($hotel['name']); ?>&room_price=<?php echo $hotel['price_per_night']; ?>" class="btn-primary small" style="margin-top:10px;">Book Standard Room</a>
                    <?php endif; ?>
                </section>

                <section class="hotel-reviews-section card-style" id="reviews-section">
                    <h2><i class="fas fa-comments"></i> Guest Reviews</h2>
                     <?php if ($review_submission_success_hotel): ?>
                        <p class="message-area success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($review_submission_success_hotel); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($review_submission_errors_hotel)): ?>
                        <div class="message-area error">
                            <p><i class="fas fa-exclamation-triangle"></i> Could not submit review. Please fix the errors:</p>
                            <ul>
                                <?php foreach ($review_submission_errors_hotel as $error): ?>
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
                        <p>No reviews yet for this hotel. Be the first to share your experience!</p>
                    <?php endif; ?>
                    
                     <?php if(isLoggedIn()): ?>
                        <div class="add-review-form card-style" style="margin-top: 20px; background-color:#f9f9f9;">
                            <h4><i class="fas fa-pencil-alt"></i> Leave Your Review:</h4>
                            <form action="submit_review.php" method="POST"> 
                                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars("hotel_details.php?hotel_id=".$hotel_id."#reviews-section"); ?>">
                                <div class="form-group">
                                    <label for="rating_hotel_page">Rating:</label>
                                    <select name="rating" id="rating_hotel_page" required class="form-control">
                                        <option value="">-- Select Rating --</option>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Very Good</option>
                                        <option value="3">3 Stars - Good</option>
                                        <option value="2">2 Stars - Fair</option>
                                        <option value="1">1 Star - Poor</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="comment_hotel_page">Comment:</label>
                                    <textarea name="comment" id="comment_hotel_page" rows="4" required class="form-control" placeholder="Share your experience..."></textarea>
                                </div>
                                <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Submit Review</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p style="margin-top: 20px;"><a href="login.php?redirect=<?php echo urlencode("hotel_details.php?hotel_id=".$hotel_id."#reviews-section"); ?>">Login</a> to leave a review.</p>
                    <?php endif; ?>
                </section>
            </main>

            <aside class="hotel-sidebar">
                <div class="booking-widget card-style">
                    <h3><i class="fas fa-calendar-check"></i> Book Your Stay</h3>
                    <p class="sidebar-price">Starts from $<strong><?php echo number_format($hotel['price_per_night'] ?: 0, 2); ?></strong> /night</p>
                    <a href="book_hotel.php?hotel_id=<?php echo $hotel_id; ?>&hotel_name=<?php echo urlencode($hotel['name']); ?>&room_price=<?php echo $hotel['price_per_night']; ?>" class="btn-primary btn-block"><i class="fas fa-door-open"></i> View Rooms & Book</a>
                     <?php if (isLoggedIn()): ?>
                        <a href="add_to_wishlist.php?hotel_id=<?php echo $hotel['hotel_id']; ?>&page=hotel_details&hotel_name=<?php echo urlencode($hotel['name']);?>" class="btn-wishlist-sidebar" title="Add to Wishlist"><i class="far fa-heart"></i> Add to Wishlist</a>
                    <?php else: ?>
                        <a href="login.php?redirect=<?php echo urlencode("hotel_details.php?hotel_id=".$hotel_id); ?>" class="btn-wishlist-sidebar" title="Login to Add to Wishlist"><i class="far fa-heart"></i> Add to Wishlist</a>
                    <?php endif; ?>
                </div>

                <div class="contact-info card-style">
                     <h3><i class="fas fa-phone-alt"></i> Contact Information</h3>
                     <?php if(!empty($hotel['contact_phone'])): ?>
                        <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($hotel['contact_phone']); ?>"><?php echo htmlspecialchars($hotel['contact_phone']); ?></a></p>
                     <?php endif; ?>
                     <?php if(!empty($hotel['contact_email'])): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($hotel['contact_email']); ?>"><?php echo htmlspecialchars($hotel['contact_email']); ?></a></p>
                     <?php endif; ?>
                     <p><strong>Address:</strong> <?php echo htmlspecialchars($hotel['location']); ?></p>
                </div>
                
                <div class="map-widget card-style">
                    <h3><i class="fas fa-map-marked-alt"></i> View on Map</h3>
                     <?php 
                        $map_query_hotel = htmlspecialchars($hotel['name'] . ", " . $hotel['location'] . ', Bangladesh');
                    ?>
                    <iframe 
                        width="100%" 
                        height="250" 
                        frameborder="0" 
                        style="border:0; border-radius: 8px;"
                        src="https://maps.google.com/maps?q=<?php echo urlencode($map_query_hotel); ?>&hl=en&z=15&output=embed" 
                        allowfullscreen>
                    </iframe>
                </div>
            </aside>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        lightbox.option({
          'resizeDuration': 200,
          'wrapAround': true,
          'disableScrolling': true
        })
    </script>
</body>
</html>
