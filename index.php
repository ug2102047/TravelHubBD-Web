<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourism Management System</title>
    <link rel="stylesheet" href="CSS/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navigation Menu -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section with Carousel -->
    <section class="hero">
        <div class="carousel">
            <div class="carousel-item active">
                <img src="sea.jpeg" alt="Tourist Spot 1">
            </div>
            <div class="carousel-item">
                <img src="mountain1.webp" alt="Tourist Spot 2">
            </div>
            <div class="carousel-item">
                <img src="spot3.jpeg" alt="Tourist Spot 3">
            </div>
            <button class="carousel-control prev" onclick="changeSlide(-1)">❮</button>
            <button class="carousel-control next" onclick="changeSlide(1)">❯</button>
        </div>
    </section>

    <!-- Navigation Links Section -->
    <section class="nav-links-section">
        <h2>Explore More</h2>
        <div class="links-container">
            <a href="index.php">Home</a>
            <a href="destination.php">Destination</a>
            <a href="hotel_search.php">Hotel_Search</a>
            <a href="wishlist2.php">Wishlist</a>
          
            <a href="aboutus.php">About Us</a>
            <a href="review.php">Review</a>
            <a href="contact.php">Contact</a>
        </div>
    </section>
    <?php include 'footer.php'; ?>
    <script src="js/index.js"></script>
</body>
</html>