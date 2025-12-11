<?php
require_once 'auth.php'; // For navbar

// Data for category cards
$categories = [
    [
        'name' => 'Sea Beach',
        'image_url' => 'images/sea.webp',
        'page_url' => 'sea.php',
        'description' => 'Explore the longest and most scenic beaches of Bangladesh. The golden sands, refreshing air, and enchanting sunsets will soothe your soul.',
        'activities' => ['Swimming', 'Surfing', 'Beach Football', 'Watching Sunsets', 'Enjoying Local Cuisine']
    ],
    [
        'name' => 'Mountain',
        'image_url' => 'images/maun.jpg',
        'page_url' => 'mountain.php',
        'description' => 'Lose yourself in the kingdom of clouds from the mountain peaks. The green valleys, indigenous cultures, and thrilling treks offer a unique experience.',
        'activities' => ['Trekking', 'Camping', 'Visiting Tribal Villages', 'Exploring Waterfalls', 'Enjoying Panoramic Views']
    ],
    [
        'name' => 'River / Lake',
        'image_url' => 'images/kaptai.jpg',
        'page_url' => 'river.php',
        'description' => 'Enjoy the beauty of serene rivers and lakes in riverine Bangladesh. Boat trips and the idyllic rural life will bring you closer to nature.',
        'activities' => ['Boat Journeys', 'Kayaking', 'Fishing', 'Exploring Haors', 'Observing Riparian Life']
    ],
    [
        'name' => 'Forest',
        'image_url' => 'images/forest.jpeg',
        'page_url' => 'forest.php',
        'description' => 'Discover the mystery and biodiversity of deep forests. From the Sundarbans mangrove to the rainforests of Sylhet, each offers a unique adventure.',
        'activities' => ['Jungle Safari', 'Wildlife Spotting', 'Tree Top Walking', 'Bird Watching', 'Nature Hiking']
    ],
    [
        'name' => 'Historical Places',
        'image_url' => 'images/his.jpg',
        'page_url' => 'historical_places.php',
        'description' => 'Journey through a thousand years of history and heritage by visiting Bangladesh\'s historical sites. Ancient ruins will take you back in time.',
        'activities' => ['Exploring Ruins', 'Visiting Museums', 'Admiring Architecture', 'Learning History', 'Photography']
    ],
    [
        'name' => 'Place of Worship',
        'image_url' => 'images/worship.jpg',
        'page_url' => 'worship.php',
        'description' => 'Visit sacred places of worship that represent a confluence of different religions and cultures. The spiritual atmosphere and architecture will mesmerize you.',
        'activities' => ['Visiting Temples & Mosques', 'Finding Spiritual Peace', 'Observing Festivals', 'Admiring Architecture', 'Learning Religious History']
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/category.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="hero-spots">
        <h2>Explore by Category</h2>
        <div class="card-container">
            <?php foreach ($categories as $category): ?>
                <div class="info-card">
                    <div class="info-card-image" style="background-image: url('<?php echo htmlspecialchars($category['image_url']); ?>');"></div>
                    <div class="info-card-content">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <div class="activities-section">
                            <strong><i class="fas fa-tasks"></i> Key Activities:</strong>
                            <ul class="activities-list">
                                <?php foreach ($category['activities'] as $activity): ?>
                                    <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($activity); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="<?php echo htmlspecialchars($category['page_url']); ?>" class="btn-explore">Explore Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>