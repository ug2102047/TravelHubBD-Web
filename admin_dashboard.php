<?php
require_once 'config.php';
require_once 'auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isAdmin()) {
    header("Location: login.php?error=Admin_access_only");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Travel Hub Bangladesh</title>
    <link rel="stylesheet" href="CSS/admin_dashboard.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container { max-width: 1100px; margin: 30px auto; padding: 20px; }
        .admin-container h2 { text-align: center; color: #2c3e50; margin-bottom: 30px; font-size: 2.2em; }
        .dashboard-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .menu-item { background-color: #fff; padding: 30px 25px; text-align: center; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; border-left: 5px solid transparent; }
        .menu-item:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 8px 20px rgba(0,0,0,0.12); border-left-color: #3498db; }
        .menu-item .fas, .menu-item .far { font-size: 2.8em; margin-bottom: 18px; color: #3498db; }
        .menu-item h3 { font-size: 1.5em; margin-bottom: 12px; color: #34495e; }
        .menu-item a { text-decoration: none; color: inherit; display:block; height:100%;}
        .menu-item p { font-size: 0.95em; color: #7f8c8d; line-height:1.5; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
        <div class="dashboard-menu">
            <a href="admin_manage_bookings.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <h3>Manage Bookings</h3>
                <p>View, confirm, or update booking statuses for hotel reservations.</p>
            </a>
            <a href="admin_view_messages.php" class="menu-item">
                <i class="far fa-envelope-open"></i>
                <h3>View Contact Messages</h3>
                <p>Read and reply to messages submitted via the contact form.</p>
            </a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>
