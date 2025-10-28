<?php
// This header is included on all pages.
// session_start() is called on individual pages as needed.

// IMPROVEMENT: Get the current page script name for active link highlighting
$current_page = basename($_SERVER['SCRIPT_NAME']);

// IMPROVEMENT: Set a default page title. 
// Individual pages can override this by defining $page_title *before* including this file.
if (!isset($page_title)) {
    $page_title = "SecureShop - Secure Online Shopping";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">🛍️ SecureShop</a>
            <ul class="nav-links">
                <li><a href="index.php" class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="index.php#categories">Categories</a></li> 
                <li><a href="cart.php" class="<?php echo ($current_page === 'cart.php') ? 'active' : ''; ?>">Cart</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php" class="<?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="<?php echo ($current_page === 'login.php') ? 'active' : ''; ?>">Login</a></li>
                    <li><a href="signup.php" class="<?php echo ($current_page === 'signup.php') ? 'active' : ''; ?>">Register</a></li>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin/dashboard.php" class="<?php echo (strpos($_SERVER['SCRIPT_NAME'], 'admin') !== false) ? 'active' : ''; ?>" style="color: #00b894; font-weight: bold;">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>