<?php
require_once 'config.php';
require_once 'auth.php'; // For navbar and potentially user-specific defaults

// Get the spot location and name from the URL
$spot_location = isset($_GET['spot_location']) ? trim(urldecode($_GET['spot_location'])) : '';
$spot_name = isset($_GET['spot_name']) ? trim(urldecode($_GET['spot_name'])) : '';

if (empty($spot_location)) {
    // Redirect to a general page or show an error if no location is specified
    header("Location: destination.php?error=No destination specified for hotel search.");
    exit();
}

// Available categories and amenities (can be fetched from DB or defined as in all_hotels.php)
$available_categories = ['Luxury Hotel', 'Boutique Hotel', 'Guest House', 'Eco-Resort', 'Family Hotels', 'Standard Hotel', 'Resort', 'Apartment'];
$available_amenities_sample = ['Wi-Fi', 'AC', 'Swimming Pool', 'Parking', 'Restaurant', 'Gym', 'Spa']; // A sample list

// Get the maximum hotel price for the specific location, for the price slider
$max_hotel_price_location = 50000; // Default max
try {
    $max_price_stmt = $conn->prepare("SELECT MAX(price_per_night) as max_price FROM hotels WHERE country = 'Bangladesh' AND location LIKE :location");
    $max_price_stmt->execute(['location' => '%' . $spot_location . '%']);
    $db_max_price = $max_price_stmt->fetchColumn();
    if ($db_max_price) {
        $max_hotel_price_location = (float)$db_max_price;
    }
} catch (PDOException $e) {
    error_log("Error fetching max price for location {$spot_location}: " . $e->getMessage());
    // Use default max price if query fails
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refine Hotel Search near <?php echo htmlspecialchars($spot_name ?: $spot_location); ?></title>
    <link rel = "stylesheet" href = "CSS/refine_hotel_search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<>
    <?php include 'navbar.php'; ?>

    <div class="refine-search-container">
        <h2><i class="fas fa-hotel"></i> Refine Hotel Search</h2>
        <p class="sub-heading">For Hotels Near: <strong><?php echo htmlspecialchars($spot_name ?: $spot_location); ?></strong></p>

        <form method="GET" action="all_hotels.php">
            <!-- This hidden input carries the spot's location to all_hotels.php -->
            <input type="hidden" name="location" value="<?php echo htmlspecialchars($spot_location); ?>">
            <!-- This hidden input helps display "Hotels near [Spot Name]" on all_hotels.php -->
            <input type="hidden" name="refined_spot_name" value="<?php echo htmlspecialchars($spot_name ?: $spot_location); ?>">


            <div class="filter-group">
                <label for="category">Hotel Category:</label>
                <select id="category" name="category" class="form-control">
                    <option value="">Any Category</option>
                    <?php foreach ($available_categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="star_rating">Minimum Star Rating:</label>
                <select id="star_rating" name="star_rating" class="form-control">
                    <option value="0">Any Rating</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo ($i > 1) ? 's' : ''; ?> & Up</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Price Range (Per Night):</label>
                <div class="price-range-inputs">
                    <input type="number" name="price_min" placeholder="$ Min" min="0" class="form-control" style="width: calc(50% - 5px);">
                    <input type="number" name="price_max" placeholder="$ Max" min="0" class="form-control" id="price_max_input_refine" style="width: calc(50% - 5px);">
                </div>
                <input type="range" id="price-slider-max-refine" min="0" max="<?php echo $max_hotel_price_location; ?>" value="<?php echo $max_hotel_price_location; ?>" style="width:100%; margin-top: 5px;">
                <small>Max Price: $<span id="price-slider-value-refine"><?php echo $max_hotel_price_location; ?></span></small>
            </div>

            <div class="filter-group">
                <label>Key Amenities:</label>
                <div class="amenities-checkboxes" style="columns: 2; -webkit-columns: 2; -moz-columns: 2;">
                    <?php foreach ($available_amenities_sample as $amenity): ?>
                        <div>
                            <input type="checkbox" id="amenity_refine_<?php echo str_replace([' ', '/'], '_', $amenity); ?>" name="amenities[]" value="<?php echo htmlspecialchars($amenity); ?>">
                            <label for="amenity_refine_<?php echo str_replace([' ', '/'], '_', $amenity); ?>"><?php echo htmlspecialchars($amenity); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="filter-submit-btn btn-primary btn-block" style="margin-top: 20px; font-size:1.1em; padding:12px;">
                <i class="fas fa-search"></i> Show Matching Hotels
            </button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script src="js/refine_hotel_search.js"></script>

</body>
</html>
