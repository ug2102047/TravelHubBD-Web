<?php
require_once 'config.php';

// Get category and type from URL
$category = isset($_GET['category']) ? urldecode($_GET['category']) : '';
$type = isset($_GET['type']) ? urldecode($_GET['type']) : '';

// Validate inputs
if (empty($category) || empty($type)) {
    header("Location: hotel_search.php?error=Please select both hotel type and destination type");
    exit();
}

// Validate category and type against allowed values
$valid_categories = ['Luxury Hotel', 'Boutique Hotel', 'Guest House', 'Eco-Resort', 'Family Hotels'];
$valid_types = ['Sea', 'Mountain', 'River', 'Forest', 'Historical', 'Worship'];
if (!in_array($category, $valid_categories) || !in_array($type, $valid_types)) {
    header("Location: hotel_search.php?error=Invalid hotel type or destination type");
    exit();
}

// Fetch hotels for the given category and spot type
$sql_hotels = "
    SELECT DISTINCT h.*
    FROM hotels h
    JOIN tourist_spots s ON h.location = s.location
    WHERE h.category = :category AND s.type = :type AND h.country = :country
";
$stmt_hotels = $conn->prepare($sql_hotels);
$stmt_hotels->execute([
    'category' => $category,
    'type' => $type,
    'country' => 'Bangladesh'
]);
$hotels = $stmt_hotels->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category); ?> near <?php echo htmlspecialchars($type); ?> Destinations - Tourism Management System</title>
    <link rel="stylesheet" href="CSS/hotel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Hotels Section -->
    <section class="hero-spots">
        <h2><?php echo htmlspecialchars($category); ?> near <?php echo htmlspecialchars($type); ?> Destinations</h2>
        <?php if (isset($_GET['message'])): ?>
            <p class="success"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <div id="hotels-list">
            <?php if (empty($hotels)): ?>
                <p>No <?php echo htmlspecialchars($category); ?> available near <?php echo htmlspecialchars($type); ?> destinations.</p>
            <?php else: ?>
                <?php foreach ($hotels as $hotel): ?>
                    <div class="hotel-row">
                        <img src="<?php echo htmlspecialchars($hotel['image_url'] ?? 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                        <div class="hotel-info">
                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($hotel['category'] ?? 'Hotel'); ?></p>
                            <p><strong>Price per Night:</strong> $<?php echo number_format($hotel['price_per_night'] ?? 0, 2); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($hotel['location'] ?? 'Unknown'); ?></p>
                            <?php if (!empty($hotel['description'])): ?>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($hotel['description']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($hotel['amenities'])): ?>
                                <p><strong>Amenities:</strong> <?php echo htmlspecialchars($hotel['amenities']); ?></p>
                            <?php endif; ?>
                            <div class="hotel-links">
                                <a href="add_to_wishlist.php?hotel_id=<?php echo $hotel['hotel_id']; ?>&category=<?php echo urlencode($category); ?>&type=<?php echo urlencode($type); ?>" class="wishlist-btn">
                                    <i class="far fa-heart"></i> Add to Wishlist
                                </a>
                                <a href="book_hotel.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="book-now-btn">
                                    <i class="fas fa-book"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>