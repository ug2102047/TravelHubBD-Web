<?php
require_once 'config.php';
require_once 'auth.php'; // For isLoggedIn() function

// Default values and getting values from GET parameters
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : ''; // This comes from refine_hotel_search.php when searching near a spot
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$star_rating_filter = isset($_GET['star_rating']) ? (int)$_GET['star_rating'] : 0;
$price_min_filter = isset($_GET['price_min']) && is_numeric($_GET['price_min']) ? (float)$_GET['price_min'] : 0;
$price_max_filter = isset($_GET['price_max']) && is_numeric($_GET['price_max']) ? (float)$_GET['price_max'] : 0;
$amenities_filter = isset($_GET['amenities']) && is_array($_GET['amenities']) ? $_GET['amenities'] : [];
$refined_spot_name = isset($_GET['refined_spot_name']) ? trim($_GET['refined_spot_name']) : '';


$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'popularity'; 

$available_categories = ['Luxury Hotel', 'Boutique Hotel', 'Guest House', 'Eco-Resort', 'Family Hotels', 'Standard Hotel', 'Resort', 'Apartment']; 
$available_amenities = ['Wi-Fi', 'Parking', 'Restaurant', 'AC', 'Swimming Pool', 'Gym', 'Spa', 'Pet Friendly', 'Airport Shuttle', 'Family Rooms']; 

$sql = "SELECT h.*, AVG(r.rating) as avg_rating, COUNT(DISTINCT r.review_id) as total_reviews 
        FROM hotels h 
        LEFT JOIN reviews r ON h.hotel_id = r.hotel_id 
        WHERE h.country = :country"; // Assuming all hotels are in Bangladesh
$params = ['country' => 'Bangladesh'];

// Adding filters
if (!empty($search_term)) { // General search term (hotel name or location part)
    $sql .= " AND (h.name LIKE :search_term OR h.location LIKE :search_term_loc)";
    $params['search_term'] = '%' . $search_term . '%';
    $params['search_term_loc'] = '%' . $search_term . '%';
}
if (!empty($location_filter)) { // Specific location from a tourist spot
    $sql .= " AND h.location LIKE :location_filter";
    $params['location_filter'] = '%' . $location_filter . '%';
}
if (!empty($category_filter)) {
    $sql .= " AND h.category = :category";
    $params['category'] = $category_filter;
}
if ($star_rating_filter > 0) {
    $sql .= " AND h.star_rating >= :star_rating";
    $params['star_rating'] = $star_rating_filter;
}
if ($price_min_filter > 0) {
    $sql .= " AND h.price_per_night >= :price_min";
    $params['price_min'] = $price_min_filter;
}
if ($price_max_filter > 0 && $price_max_filter >= $price_min_filter) { // ensure max is not less than min
    $sql .= " AND h.price_per_night <= :price_max";
    $params['price_max'] = $price_max_filter;
}

if (!empty($amenities_filter)) {
    foreach ($amenities_filter as $index => $amenity) {
        // Using FIND_IN_SET assumes amenities are stored as a comma-separated string in the DB
        // e.g., "Wi-Fi,Parking,AC"
        $sql .= " AND FIND_IN_SET(:amenity{$index}, h.amenities)"; 
        $params["amenity{$index}"] = $amenity;
    }
}

$sql .= " GROUP BY h.hotel_id";

// Sorting options
switch ($sort_by) {
    case 'price_asc': $sql .= " ORDER BY h.price_per_night ASC, total_reviews DESC"; break;
    case 'price_desc': $sql .= " ORDER BY h.price_per_night DESC, total_reviews DESC"; break;
    case 'rating_desc': $sql .= " ORDER BY avg_rating DESC, total_reviews DESC"; break;
    case 'name_asc': $sql .= " ORDER BY h.name ASC"; break;
    default: $sql .= " ORDER BY total_reviews DESC, avg_rating DESC, h.star_rating DESC"; break; // Popularity
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the overall maximum hotel price for the price range filter default
$max_hotel_price_overall = 50000; // Default
try {
    $max_price_stmt_overall = $conn->query("SELECT MAX(price_per_night) as max_price FROM hotels WHERE country = 'Bangladesh'");
    $db_max_price_overall = $max_price_stmt_overall->fetchColumn();
    if ($db_max_price_overall) {
        $max_hotel_price_overall = (float)$db_max_price_overall;
    }
} catch (PDOException $e) {
    error_log("Error fetching overall max price: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if (!empty($refined_spot_name)) echo 'Hotels near ' . htmlspecialchars($refined_spot_name);
        elseif (!empty($search_term)) echo 'Search Results for "' . htmlspecialchars($search_term) . '"';
        elseif (!empty($location_filter)) echo 'Hotels in ' . htmlspecialchars($location_filter);
        else echo 'All Hotels';
        ?> - Travel Hub
    </title>
    <link rel="stylesheet" href="CSS/all_hotels.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="hotel-listing-page">
        <aside class="filters-sidebar">
            <h3><i class="fas fa-filter"></i> Filter Hotels</h3>
            <form method="GET" action="all_hotels.php" id="filter-form">
                <!-- Preserve existing filters when sorting or applying new ones -->
                <?php if(!empty($refined_spot_name)): ?>
                    <input type="hidden" name="refined_spot_name" value="<?php echo htmlspecialchars($refined_spot_name); ?>">
                <?php endif; ?>
                 <?php if(!empty($location_filter)): ?>
                    <input type="hidden" name="location" value="<?php echo htmlspecialchars($location_filter); ?>">
                <?php endif; ?>


                <div class="filter-group">
                    <label for="search_term_aside">Hotel Name / General Location:</label>
                    <input type="text" id="search_term_aside" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="e.g., Hotel Seagull or Dhaka">
                </div>
                
                <div class="filter-group">
                    <label for="category_aside">Category:</label>
                    <select id="category_aside" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($available_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category_filter == $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="star_rating_aside">Min. Star Rating:</label>
                    <select id="star_rating_aside" name="star_rating">
                        <option value="0">All Ratings</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($star_rating_filter == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Star<?php echo ($i > 1) ? 's' : ''; ?> & Up
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Price Range (Per Night):</label>
                    <div class="price-range-inputs">
                        <input type="number" name="price_min" value="<?php echo htmlspecialchars($price_min_filter > 0 ? $price_min_filter : ''); ?>" placeholder="$ Min" min="0" style="width: calc(50% - 5px);">
                        <input type="number" name="price_max" id="price_max_input_all_hotels" value="<?php echo htmlspecialchars($price_max_filter > 0 ? $price_max_filter : ''); ?>" placeholder="$ Max" min="0" style="width: calc(50% - 5px);">
                    </div>
                     <input type="range" id="price-slider-max-all-hotels" min="0" max="<?php echo $max_hotel_price_overall; ?>" value="<?php echo $price_max_filter ?: $max_hotel_price_overall ; ?>" style="width:100%; margin-top: 5px;">
                     <small>Max Price: $<span id="price-slider-value-all-hotels"><?php echo $price_max_filter ?: $max_hotel_price_overall; ?></span></small>
                </div>
                
                <div class="filter-group">
                    <label>Amenities:</label>
                    <div class="amenities-checkboxes" style="columns: 2; -webkit-columns: 2; -moz-columns: 2; column-gap: 10px;">
                        <?php foreach ($available_amenities as $amenity): ?>
                            <div>
                                <input type="checkbox" id="amenity_<?php echo str_replace([' ', '/'], '_', $amenity); ?>" name="amenities[]" value="<?php echo htmlspecialchars($amenity); ?>" <?php echo in_array($amenity, $amenities_filter) ? 'checked' : ''; ?>>
                                <label for="amenity_<?php echo str_replace([' ', '/'], '_', $amenity); ?>"><?php echo htmlspecialchars($amenity); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="filter-submit-btn btn-primary" style="width:100%; margin-top:10px; padding:10px; font-size:1.05em;"><i class="fas fa-search"></i> Apply Filters</button>
                <a href="all_hotels.php" class="filter-reset-btn btn-primary" style="width:100%; margin-top:10px; padding:10px; font-size:1.05em; background-color:#6c757d;"><i class="fas fa-undo"></i> Reset Filters</a>
            </form>
        </aside>

        <main class="hotel-results-container">
            <div class="results-header">
                <h2>
                    <?php
                    if (!empty($refined_spot_name)) echo 'Hotels near <span style="color:#e67e22;">' . htmlspecialchars($refined_spot_name) . '</span>';
                    elseif (!empty($search_term)) echo 'Search Results for "<span style="color:#e67e22;">' . htmlspecialchars($search_term) . '</span>"';
                    elseif (!empty($location_filter)) echo 'Hotels in <span style="color:#e67e22;">' . htmlspecialchars($location_filter) . '</span>';
                    else echo 'All Hotels in Bangladesh';
                    ?>
                </h2>
                <div class="sort-options">
                    <form method="GET" id="sort-form-all-hotels" style="display:inline;">
                        <!-- Hidden fields to retain current filters when sorting -->
                        <?php foreach ($_GET as $key => $value): if ($key != 'sort_by' && $key != 'page'): ?>
                            <?php if (is_array($value)): foreach ($value as $sub_value):?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($sub_value); ?>">
                            <?php endforeach; else: ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endif; endforeach; ?>

                        <label for="sort_by_all_hotels">Sort By:</label>
                        <select id="sort_by_all_hotels" name="sort_by" onchange="document.getElementById('sort-form-all-hotels').submit()">
                            <option value="popularity" <?php echo ($sort_by == 'popularity') ? 'selected' : ''; ?>>Popularity</option>
                            <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating_desc" <?php echo ($sort_by == 'rating_desc') ? 'selected' : ''; ?>>Rating: High to Low</option>
                            <option value="name_asc" <?php echo ($sort_by == 'name_asc') ? 'selected' : ''; ?>>Name: A-Z</option>
                        </select>
                    </form>
                </div>
            </div>

            <?php if (empty($hotels)): ?>
                <p class="no-results"><i class="fas fa-hotel" style="margin-right:8px; color:#ccc; font-size:1.5em;"></i>Sorry, no hotels found matching your criteria. Please try adjusting your filters or broadening your search.</p>
            <?php else: ?>
                <div class="hotel-grid">
                    <?php foreach ($hotels as $hotel): ?>
                        <div class="hotel-card">
                            <a href="hotel_details.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="hotel-card-link">
                                <div class="hotel-image">
                                    <img src="<?php echo !empty($hotel['image_url']) ? htmlspecialchars($hotel['image_url']) : 'https://placehold.co/400x300/E1E1E1/A0A0A0?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                    <?php if($hotel['avg_rating'] && $hotel['total_reviews'] > 0): ?>
                                    <span class="rating-badge">
                                        <i class="fas fa-star"></i> <?php echo number_format($hotel['avg_rating'], 1); ?> (<?php echo $hotel['total_reviews']; ?>)
                                    </span>
                                    <?php elseif ($hotel['star_rating']): ?>
                                    <span class="rating-badge" style="background-color: #555;">
                                        <?php echo $hotel['star_rating']; ?> <i class="fas fa-star"></i> Hotel
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="hotel-info">
                                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
                                    <?php if(!empty($hotel['category'])): ?>
                                    <p class="category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($hotel['category']); ?></p>
                                    <?php endif; ?>
                                    <p class="price"><i class="fas fa-dollar-sign"></i> Starts from $<?php echo number_format($hotel['price_per_night'], 2); ?> /night</p>
                                     <div class="star-rating-display">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo ($i <= $hotel['star_rating']) ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </a>
                            <div class="hotel-card-actions">
                                <a href="hotel_details.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="btn-details"><i class="fas fa-eye"></i> View Details</a>
                                <?php if (isLoggedIn()): ?>
                                    <a href="add_to_wishlist.php?hotel_id=<?php echo $hotel['hotel_id']; ?>&page=all_hotels&<?php echo http_build_query($_GET); // Pass current filters to wishlist redirect ?>" class="btn-wishlist" title="Add to Wishlist"><i class="far fa-heart"></i></a>
                                <?php else: ?>
                                    <a href="login.php?redirect=all_hotels.php?<?php echo http_build_query($_GET); ?>" class="btn-wishlist" title="Login to Add to Wishlist"><i class="far fa-heart"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </main>
    </div>

    <?php include 'footer.php'; ?>
     <script src="js/all_hotels.js"></script> 
</body>
</html>
