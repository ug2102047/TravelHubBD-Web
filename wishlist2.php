<?php
require_once 'config.php';
require_once 'auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php?redirect=wishlist2.php");
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Fetch wishlisted hotels
$sql_hotels = "
    SELECT w.wishlist_id, h.hotel_id, h.name, h.category, h.price_per_night, h.image_url, h.location, h.star_rating
    FROM wishlist w
    JOIN hotels h ON w.hotel_id = h.hotel_id
    WHERE w.user_id = :user_id AND w.hotel_id IS NOT NULL
    ORDER BY w.added_date DESC
";
$stmt_hotels = $conn->prepare($sql_hotels);
$stmt_hotels->execute(['user_id' => $user_id]);
$wishlisted_hotels = $stmt_hotels->fetchAll(PDO::FETCH_ASSOC);

// Fetch wishlisted spots
$sql_spots = "
    SELECT w.wishlist_id, s.spot_id, s.name, s.type, s.image_url, s.location, s.division
    FROM wishlist w
    JOIN tourist_spots s ON w.spot_id = s.spot_id
    WHERE w.user_id = :user_id AND w.spot_id IS NOT NULL
    ORDER BY w.added_date DESC
";
$stmt_spots = $conn->prepare($sql_spots);
$stmt_spots->execute(['user_id' => $user_id]);
$wishlisted_spots = $stmt_spots->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/wishlist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="wishlist-page-container">
        <h2><i class="fas fa-heart"></i> My Wishlist</h2>

        <?php if (isset($_GET['message'])): ?>
            <p class="message-area success"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="message-area error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <?php if (empty($wishlisted_hotels) && empty($wishlisted_spots)): ?>
            <p class="no-items-message">Your wishlist is currently empty. Start exploring and add your favorite hotels and destinations!</p>
        <?php else: ?>
            <?php if (!empty($wishlisted_hotels)): ?>
                <section class="wishlist-section">
                    <h3><i class="fas fa-hotel"></i> Wishlisted Hotels</h3>
                    <div class="wishlist-grid">
                        <?php foreach ($wishlisted_hotels as $hotel): ?>
                            <div class="wishlist-item-card">
                                <div class="wishlist-item-image">
                                    <img src="<?php echo !empty($hotel['image_url']) ? htmlspecialchars($hotel['image_url']) : 'https://placehold.co/400x300/E1E1E1/A0A0A0?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                </div>
                                <div class="wishlist-item-info">
                                    <h4><?php echo htmlspecialchars($hotel['name']); ?></h4>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
                                    <?php if (!empty($hotel['category'])): ?>
                                        <p><i class="fas fa-tag"></i> Category: <?php echo htmlspecialchars($hotel['category']); ?></p>
                                    <?php endif; ?>
                                    <p><i class="fas fa-dollar-sign"></i> Price: $<?php echo number_format($hotel['price_per_night'] ?? 0, 2); ?> /night</p>
                                    <div class="star-rating-display">
                                        <?php for($s_idx = 1; $s_idx <= 5; $s_idx++): ?>
                                            <i class="fas fa-star <?php echo ($s_idx <= $hotel['star_rating']) ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="wishlist-item-actions">
                                    <a href="hotel_details.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="btn-view-item"><i class="fas fa-eye"></i> View</a>
                                    <a href="remove_from_wishlist.php?wishlist_id=<?php echo $hotel['wishlist_id']; ?>" class="btn-remove-wishlist" onclick="return confirm('Are you sure you want to remove this hotel from your wishlist?');"><i class="fas fa-trash-alt"></i> Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($wishlisted_spots)): ?>
                 <section class="wishlist-section">
                    <h3><i class="fas fa-map-signs"></i> Wishlisted Destinations</h3>
                    <div class="wishlist-grid">
                        <?php foreach ($wishlisted_spots as $spot): ?>
                            <div class="wishlist-item-card">
                                 <div class="wishlist-item-image">
                                    <img src="<?php echo !empty($spot['image_url']) ? htmlspecialchars($spot['image_url']) : 'https://placehold.co/400x300/E1E1E1/A0A0A0?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($spot['name']); ?>">
                                </div>
                                <div class="wishlist-item-info">
                                    <h4><?php echo htmlspecialchars($spot['name']); ?></h4>
                                     <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($spot['location']); ?> (<?php echo htmlspecialchars($spot['division']); ?>)</p>
                                    <p><i class="fas fa-tag"></i> Type: <?php echo htmlspecialchars($spot['type']); ?></p>
                                </div>
                                <div class="wishlist-item-actions">
                                    <a href="spot_details.php?spot_id=<?php echo $spot['spot_id']; ?>" class="btn-view-item"><i class="fas fa-eye"></i> View</a>
                                    <a href="remove_from_wishlist.php?wishlist_id=<?php echo $spot['wishlist_id']; ?>" class="btn-remove-wishlist" onclick="return confirm('Are you sure you want to remove this spot from your wishlist?');"><i class="fas fa-trash-alt"></i> Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>