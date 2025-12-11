<?php
require_once 'auth.php';

// Destroy the session
session_unset();
session_destroy();

// Redirect to signup page
header("Location: signup.php");
exit();
?>