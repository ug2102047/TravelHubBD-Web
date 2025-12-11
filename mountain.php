<?php
require_once 'config.php';
require_once 'auth.php'; // For navbar

// Fetch Mountain spots from Bangladesh
$sql = "SELECT spot_id, name, type, division, location, image_url FROM tourist_spots WHERE country = :country AND type = :type ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute(['country' => 'Bangladesh', 'type' => 'Mountain']);
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Mountain Destinations";
$current_page_identifier = "mountain";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/destination.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="hero-spots" style="padding: 20px;">
        <h2 style="text-align:center; margin-bottom: 10px;"><?php echo $page_title; ?> in Bangladesh</h2>

        <?php if (isset($_GET['message'])): ?>
            <p class="message-area success"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="message-area error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <div id="spots-list" class="hotel-grid">
            <?php if (empty($spots)): ?>
                <p style="text-align:center;">No mountain destinations available in Bangladesh at the moment.</p>
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
                            <a href="add_to_wishlist.php?spot_id=<?php echo $spot['spot_id']; ?>&page=<?php echo $current_page_identifier; ?>" class="wishlist-btn custom-wishlist">
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
