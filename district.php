<?php
require_once 'config.php';
require_once 'auth.php'; // For navbar

// Data for division cards
$divisions = [
    [
        'name' => 'Dhaka',
        'image_url' => 'images/dhaka.jpg',
        'page_url' => 'dhaka.php',
        'description' => 'The capital and historic heart of Bangladesh. Dhaka Division blends urban hustle with ancient heritage like Lalbagh Fort and Ahsan Manzil.',
        'activities' => ['Visiting Historical Sites', 'Museum Tours', 'River Cruising', 'Shopping', 'Rickshaw Rides']
    ],
    [
        'name' => 'Chattogram',
        'image_url' => 'images/chattogram.jpg',
        'page_url' => 'ctg.php',
        'description' => 'A stunning combination of hills, sea, and valleys. It hosts Patenga beach, Foy\'s Lake, and verdant hill tracts.',
        'activities' => ['Enjoying the Beach', 'Hill Trekking', 'Lake Visits', 'Ship Watching', 'Trying Mejbani Cuisine']
    ],
    [
        'name' => 'Rajshahi',
        'image_url' => 'images/raj.jpg',
        'page_url' => 'raj.php',
        'description' => 'Known as the "Silk City", Rajshahi is famous for its clean environment, mango orchards, and archaeological sites like Puthia Temple Complex.',
        'activities' => ['Mango Orchard Tours', 'Riverbank Walks', 'Exploring Ruins', 'Shopping for Silk', 'Tasting Traditional Sweets']
    ],
    [
        'name' => 'Khulna',
        'image_url' => 'images/khulna.jpg',
        'page_url' => 'khulna.php',
        'description' => 'The gateway to the world\'s largest mangrove forest, the Sundarbans. This division also features historic sites like the Sixty Dome Mosque.',
        'activities' => ['Sundarbans Safari', 'Wildlife Spotting', 'Visiting Historic Mosques', 'River Cruises', 'Observing Shrimp Farming']
    ],
    [
        'name' => 'Barishal',
        'image_url' => 'images/barishal.jpg',
        'page_url' => 'barishal.php',
        'description' => 'Known as the "Venice of Bengal" for its rivers, canals, and famous floating guava markets. Its natural beauty and rural life attract tourists.',
        'activities' => ['Visiting Floating Markets', 'Boat Tours', 'Guthia Mosque Visit', 'Enjoying Local Dishes', 'Experiencing Rural Life']
    ],
    [
        'name' => 'Sylhet',
        'image_url' => 'images/sylhet.webp',
        'page_url' => 'sylhet.php',
        'description' => 'Famous for its lush tea gardens, hills, and crystal-clear rivers and haors. Jaflong, Bisnakandi, and Ratargul are major attractions.',
        'activities' => ['Tea Garden Tours', 'River Boating', 'Hill & Waterfall Treks', 'Haor Exploration', 'Visiting Holy Shrines']
    ],
    [
        'name' => 'Rangpur',
        'image_url' => 'images/rangpur.jpg',
        'page_url' => 'rangpur.php',
        'description' => 'Rich in ancient history and archaeological sites. This division is home to historical landmarks like Kantajew Temple and Tajhat Palace.',
        'activities' => ['Visiting Palaces', 'Temple Exploration', 'Enjoying Rural Fairs', 'Teesta Barrage Tour', 'Learning Local Culture']
    ],
    [
        'name' => 'Mymensingh',
        'image_url' => 'images/mymensingh.jpg',
        'page_url' => 'mymensingh.php',
        'description' => 'Situated on the banks of the Brahmaputra river, Mymensingh is known for its cultural heritage and natural beauty, including the foothills of the Garo Hills.',
        'activities' => ['Riverbank Strolls', 'Visiting Garo Hills', 'Exploring Palaces', 'Folk Art Museum Tour', 'Tasting River Fish']
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Divisions - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/category.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="hero-spots">
        <h2>Explore by Division</h2>
        <div class="card-container">
            <?php foreach ($divisions as $division): ?>
                <div class="info-card">
                    <div class="info-card-image" style="background-image: url('<?php echo htmlspecialchars($division['image_url']); ?>');"></div>
                    <div class="info-card-content">
                        <h3><?php echo htmlspecialchars($division['name']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars($division['description']); ?></p>
                        <div class="activities-section">
                            <strong><i class="fas fa-tasks"></i> Key Activities:</strong>
                            <ul class="activities-list">
                                <?php foreach ($division['activities'] as $activity): ?>
                                    <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($activity); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="<?php echo htmlspecialchars($division['page_url']); ?>" class="btn-explore">Explore Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>