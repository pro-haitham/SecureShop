<?php
session_start();
include 'includes/db.php';
// Use the new header. This must come after session_start()
include 'includes/header.php';
?>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to SecureShop</h1>
            <p>Find the best products, securely and hassle-free. Up to 30% off this week!</p>
            <a href="#products" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <section class="offer-banner">
        <div class="container">
            <h3>Deal of the Week!</h3>
            <p>Get our exclusive "Test Product" for 50% off. Ends in:</p>
            <div id="countdown-timer">
                <span id="days">00</span> days
                <span id="hours">00</span> hours
                <span id="minutes">00</span> minutes
                <span id="seconds">00</span> seconds
            </div>
            <script>
                // Pass PHP date to JS. Set for 7 days from now.
                const countdownTargetDate = "<?php echo date('Y-m-d H:i:s', strtotime('+7 days')); ?>";
            </script>
        </div>
  </section>
<main class="container">

    <section class="category-highlights" id="categories">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <?php
            // Fetch categories from the database
            $cat_result = $conn->query("SELECT id, name, image FROM categories ORDER BY name ASC LIMIT 3");
            if ($cat_result && $cat_result->num_rows > 0) {
                while ($cat = $cat_result->fetch_assoc()) {
                    $cat_id = htmlspecialchars($cat['id']);
                    $cat_name = htmlspecialchars($cat['name']);
                    $cat_image = htmlspecialchars($cat['image'] ?? 'placeholder.jpg');

                    echo "
                    <a href='category.php?id={$cat_id}' class='category-card-link'>
                        <div class='category-card'>
                            <img src='assets/images/{$cat_image}' alt='{$cat_name}'>
                            <h3>{$cat_name}</h3>
                        </div>
                    </a>
                    ";
                }
            } else {
                echo "<p>No categories found.</p>";
            }
            ?>
        </div>
    </section>
    <h1 class="page-title" id="products">Featured Products</h1>
        <div class="products-grid">
            <?php
            $sql = "SELECT id, name, description, price, image FROM products WHERE stock > 0";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $product_id = htmlspecialchars($row['id']);
                    $product_name = htmlspecialchars($row['name']);
                    $product_description = htmlspecialchars($row['description']);
                    $product_price = htmlspecialchars($row['price']);
                    $product_image = htmlspecialchars($row['image'] ? $row['image'] : 'placeholder.jpg'); // Placeholder
                    
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
            $conn->close();
            ?>
        </div>
    </main>

    <section class="testimonials">
        </section>

    <div id="toast-notification" 
         class="toast" 
         data-show="<?php echo (isset($_GET['added']) && $_GET['added'] == '1') ? 'true' : 'false'; ?>">
        Product added to cart!
    </div>

<?php
// Use the new footer
include 'includes/footer.php';
?>