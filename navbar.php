<?php
// Ensure session is started if not already, for $_SESSION['username'] and auth functions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth.php'; // For isLoggedIn() and isAdmin() functions
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <title>Navigation Bar - Travel Hub Bangladesh</title> 
    <link rel="stylesheet" href="CSS/navbar.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <div class="logo">
            <a href="index.php">
                <img src="earth-globe.png" alt="Travel Hub Bangladesh Logo"> 
            </a>
        </div>
        <div class="search-bar">
            <form action="search_results.php" method="GET">
                <input type="text" id="searchInput" name="q" placeholder="Search destinations..." required> 
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>

        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): // ### NEW, SIMPLIFIED ADMIN VIEW ### ?>
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="dropdown user-dropdown"> 
                    <a href="javascript:void(0)" class="user-account-link">
                        <i class="fas fa-user-shield"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?> <i class="fas fa-caret-down"></i>
                    </a>
                    <div class="dropdown-content">
                         <a href="my_profile.php"><i class="fas fa-user-edit"></i> Profile</a> 
                         <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            <?php else: // ### ORIGINAL USER VIEW (UNCHANGED) ### ?>
                <li class="dropdown">
                    <a href="destination.php">Destinations <i class="fas fa-caret-down"></i></a> 
                    <div class="dropdown-content">
                        <div class="nested-dropdown">
                            <a href="category.php">By Category</a> 
                            <div class="nested-dropdown-content">
                                <a href="sea.php">Sea Beach</a>
                                <a href="mountain.php">Mountain</a>
                                <a href="river.php">River/Lake</a>
                                <a href="forest.php">Forest</a>
                                <a href="historical_places.php">Historical Places</a>
                                <a href="worship.php">Place of Worship</a>
                            </div>
                        </div>
                        <div class="nested-dropdown">
                            <a href="district.php">By Division</a>
                            <div class="nested-dropdown-content">
                                <a href="dhaka.php">Dhaka</a>
                                <a href="ctg.php">Chattogram</a>
                                <a href="raj.php">Rajshahi</a>
                                <a href="khulna.php">Khulna</a>
                                <a href="barishal.php">Barishal</a>
                                <a href="sylhet.php">Sylhet</a>
                                <a href="rangpur.php">Rangpur</a>
                                <a href="mymensingh.php">Mymensingh</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li><a href="all_hotels.php">Hotels</a></li> 
                <li><a href="wishlist2.php">Wishlist</a></li> 
                <li><a href="my_bookings.php">My Bookings</a></li> 
                <li><a href="my_support_tickets.php">Conversation</a></li>
                <li><a href="review.php">Review</a></li>
                <li class="dropdown user-dropdown"> 
                    <a href="javascript:void(0)" class="user-account-link">
                        <i class="fas fa-user-circle"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'My Account'; ?> <i class="fas fa-caret-down"></i>
                    </a>
                    <div class="dropdown-content">
                         <a href="my_profile.php">Profile</a> 
                         <a href="aboutus.php">About Us</a> 
                         <a href="contact.php">Contact Us</a> 
                         <a href="logout.php">Logout</a>
                    </div>
                </li>
            <?php endif; ?>
        <?php else: // ### GUEST VIEW (UNCHANGED) ### ?>
            <li class="dropdown">
                <a href="destination.php">Destinations <i class="fas fa-caret-down"></i></a> 
                <div class="dropdown-content">
                     <div class="nested-dropdown">
                        <a href="category.php">By Category</a> 
                        <div class="nested-dropdown-content">
                            <a href="sea.php">Sea Beach</a>
                            <a href="mountain.php">Mountain</a>
                            <a href="river.php">River/Lake</a>
                            <a href="forest.php">Forest</a>
                            <a href="historical_places.php">Historical</a>
                            <a href="worship.php">Worship</a>
                        </div>
                    </div>
                    <div class="nested-dropdown">
                        <a href="district.php">By Division</a>
                        <div class="nested-dropdown-content">
                             <a href="dhaka.php">Dhaka</a>
                             <a href="ctg.php">Chattogram</a>
                             <a href="raj.php">Rajshahi</a>
                             <a href="khulna.php">Khulna</a>
                             <a href="barishal.php">Barishal</a>
                             <a href="sylhet.php">Sylhet</a>
                             <a href="rangpur.php">Rangpur</a>
                             <a href="mymensingh.php">Mymensingh</a>
                        </div>
                    </div>
                </div>
            </li>
            <li><a href="all_hotels.php">Hotels</a></li> 
            <li><a href="aboutus.php">About Us</a></li> 
            <li><a href="contact.php">Contact</a></li> 
            <li class="nav-button"><a href="login.php" class="btn-login">Login</a></li>
            <li class="nav-button"><a href="signup.php" class="btn-signup">Signup</a></li>
        <?php endif; ?>
    </ul>
</nav>
</body>
</html>