<?php
require_once 'config.php'; // May be needed if any DB data is shown directly on this form
require_once 'auth.php'; // For navbar.php

// If form is submitted, redirect to all_hotels.php with parameters
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['category']) || isset($_GET['type']) || isset($_GET['location_search']))) {
    $redirect_url = "all_hotels.php?";
    $params = [];

    if (!empty($_GET['location_search'])) {
        // Use 'search_term' for all_hotels.php to search by name or location
        $params[] = "search_term=" . urlencode(trim($_GET['location_search']));
    }
    if (!empty($_GET['category'])) {
        $params[] = "category=" . urlencode($_GET['category']);
    }
    // 'type' (destination type) from this form is not a direct filter in all_hotels.php
    // It could be mapped to location or a new filter if tourist_spots are linked for this search
    // For now, this specific 'type' field might not be used by all_hotels.php unless it's adapted.
    // If 'type' was meant to be a location like "Sea Beach Area", it should go into 'location_search'.
    
    // Add other potential filters from this form if they map to all_hotels.php filters

    if (!empty($params)) {
        $redirect_url .= implode("&", $params);
    }

    header("Location: " . $redirect_url);
    exit();
}

// Available categories and destination types (these could also come from a database)
$available_hotel_categories = ['Luxury Hotel', 'Boutique Hotel', 'Guest House', 'Eco-Resort', 'Family Hotels', 'Standard Hotel', 'Resort', 'Apartment'];
// $available_destination_types = ['Sea', 'Mountain', 'River', 'Forest', 'Historical', 'Worship', 'City Center', 'Rural']; // This is less used now

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/hotel_search.css">
    <link rel="stylesheet" href="CSS/hotel_styles_v2.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="hotel-search-form-container">
        <h2><i class="fas fa-search-location"></i> Find Your Perfect Hotel</h2>
        <p style="text-align:center; margin-bottom:20px; color:#777;">Fill out the form below to find the best hotel for your trip.</p>

        <?php if (isset($_GET['error_message'])): // Example error display ?>
            <p class="error-message-placeholder"><?php echo htmlspecialchars($_GET['error_message']); ?></p>
        <?php endif; ?>

        <form action="all_hotels.php" method="GET" id="hotel-search-form-main">
            <div class="form-group-search">
                <label for="location_search_input">Location or Hotel Name:</label>
                <input type="text" id="location_search_input" name="search_term" placeholder="e.g., Cox's Bazar or Hotel Seagull">
            </div>

            <div class="form-group-search">
                <label for="category_search_select">Hotel Type:</label>
                <select id="category_search_select" name="category">
                    <option value="" disabled selected>Select a type</option>
                    <?php foreach ($available_hotel_categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                     <option value="">All Types</option> </select>
            </div>
            <button type="submit" class="search-submit-btn"><i class="fas fa-search"></i> Search Hotels</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
