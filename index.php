<?php
// Start the session to manage user login state and cart
session_start();

// Include the database connection file
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureShop - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">🛍️ SecureShop</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="product.php">Products</a></li>
                <li><a href="cart.php">Cart</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1 class="page-title">Featured Products</h1>

        <div class="products-grid">
            <?php
            // Fetch all products from the database
            $sql = "SELECT id, name, description, price, image FROM products";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                // Loop through each product and display it
                while($row = $result->fetch_assoc()) {
                    // Use htmlspecialchars to prevent XSS attacks
                    $product_id = htmlspecialchars($row['id']);
                    $product_name = htmlspecialchars($row['name']);
                    $product_description = htmlspecialchars($row['description']);
                    $product_price = htmlspecialchars($row['price']);
                    $product_image = htmlspecialchars($row['image']);
                    
                    echo "
                    <div class='product-card'>
                        <img src='assets/images/{$product_image}' alt='{$product_name}'>
                        <h3>{$product_name}</h3>
                        <p class='price'>$ {$product_price}</p>
                        <p class='description'>" . substr($product_description, 0, 60) . "...</p>
                        
                        <form action='add_to_cart.php' method='post'>
                            <input type='hidden' name='product_id' value='{$product_id}'>
                            <input type='hidden' name='quantity' value='1'>
                            <button type='submit' class='add-to-cart-btn'>Add to Cart</button>
                        </form>
                    </div>
                    ";
                }
            } else {
                echo "<p>No products found!</p>";
            }
            // Close the database connection
            $conn->close();
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> SecureShop. All Rights Reserved.</p>
    </footer>

</body>
</html>