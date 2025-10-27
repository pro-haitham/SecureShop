<?php
// This header is included on all pages.
// session_start() is called on individual pages (like index.php) as needed.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureShop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">🛍️ SecureShop</a>
                <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#categories">Categories</a></li> 
    <li><a href="cart.php">Cart</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Register</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin/dashboard.php" style="color: #00b894; font-weight: bold;">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>