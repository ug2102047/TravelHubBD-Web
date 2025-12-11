<?php
require_once 'config.php';
require_once 'auth.php'; // For navbar and other functions

// Get search query from URL
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch matching tourist spots from Bangladesh
$spots = [];
if (!empty($search_query)) {
    // Select specific columns to match the structure in destination.php
    $sql = "SELECT spot_id, name, type, division, location, image_url 
            FROM tourist_spots 
            WHERE country = :country AND (name LIKE :search_query OR location LIKE :search_query OR type LIKE :search_query OR division LIKE :search_query)
            ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'country' => 'Bangladesh',
        'search_query' => '%' . $search_query . '%'
    ]);
    $spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</title>
    <link rel="stylesheet" href="CSS/destination.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="hero-spots" style="padding: 20px;">
        <h2 style="text-align:center; margin-bottom: 25px;">
            Search Results for "<?php echo htmlspecialchars($search_query); ?>"
        </h2>
        
        <?php if (isset($_GET['message'])): ?>
            <p class="message-area success"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="message-area error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <div id="spots-list" class="hotel-grid">
            <?php if (empty($search_query)): ?>
                <p class="no-results" style="text-align:center; background: #fff; padding: 20px; border-radius: 12px;">Please enter a search term in the navigation bar.</p>
            <?php elseif (empty($spots)): ?>
                <p class="no-results" style="text-align:center; background: #fff; padding: 20px; border-radius: 12px;">No destinations found matching your search.</p>
            <?php else: ?>
                <?php foreach ($spots as $spot): ?>
                    <div class="hotel-card">
                        <a href="spot_details.php?spot_id=<?php echo $spot['spot_id']; ?>" class="hotel-card-link">
                            <div class="hotel-image">
                                <img src="<?php echo !empty($spot['image_url']) ? htmlspecialchars($spot['image_url']) : 'https://placehold.co/400x300/E1E1E1/A0A0A0?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($spot['name']); ?>">
                            </div>
                            <div class="hotel-info">
                                <h3><?php echo htmlspecialchars($spot['name']); ?></h3>
                                <p><i class="fas fa-tag"></i> <strong>Category:</strong> <?php echo htmlspecialchars($spot['type']); ?></p>
                                <p><i class="fas fa-map-pin"></i> <strong>Division:</strong> <?php echo htmlspecialchars($spot['division']); ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($spot['location']); ?></p>
                            </div>
                        </a>
                        <div class="hotel-card-actions spot-links">
                            <a href="spot_details.php?spot_id=<?php echo $spot['spot_id']; ?>" class="btn-details" style="background-color:#3498db;">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="refine_hotel_search.php?spot_location=<?php echo urlencode($spot['location']); ?>&spot_name=<?php echo urlencode($spot['name']); ?>" class="hotels-link">
                                <i class="fas fa-hotel"></i> View Hotels
                            </a>
                            <a href="add_to_wishlist.php?spot_id=<?php echo $spot['spot_id']; ?>&page=search_results&q=<?php echo urlencode($search_query); ?>" class="wishlist-btn custom-wishlist" style="background-color:#e74c3c;">
                                <i class="far fa-heart"></i> Add to Wishlist
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>