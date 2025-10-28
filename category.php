<?php
session_start();
include 'includes/db.php';

// Get category ID and validate
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$category_id = intval($_GET['id']);

// Fetch category details
$stmt_cat = $conn->prepare("SELECT name, description FROM categories WHERE id = ?");
$stmt_cat->bind_param("i", $category_id);
$stmt_cat->execute();
$category = $stmt_cat->get_result()->fetch_assoc();
$stmt_cat->close();

if (!$category) {
    header('Location: index.php');
    exit;
}

// Fetch products in this category
$stmt_prod = $conn->prepare("SELECT id, name, description, price, image FROM products WHERE category_id = ? AND stock > 0");
$stmt_prod->bind_param("i", $category_id);
$stmt_prod->execute();
$result = $stmt_prod->get_result();

// IMPROVEMENT: Set page title
$page_title = htmlspecialchars($category['name']) . " - SecureShop";
include 'includes/header.php'; // Use new header
?>

<main class="container">
    <div class="category-header">
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>
        <p><?php echo htmlspecialchars($category['description']); ?></p>
    </div>

    <div class="products-grid">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $product_id = htmlspecialchars($row['id']);
                $product_name = htmlspecialchars($row['name']);
                $product_description = htmlspecialchars($row['description']);
                $product_price = htmlspecialchars($row['price']);
                $product_image = htmlspecialchars($row['image'] ? $row['image'] : 'placeholder.jpg');
                
                echo "
                <div class='product-card'>
                    <a href='product.php?id={$product_id}' class='product-link-wrapper'>
                        <img src='assets/images/{$product_image}' alt='{$product_name}'>
                        <h3>{$product_name}</h3>
                        <p class='price'>$ {$product_price}</p>
                        <p class='description'>" . substr($product_description, 0, 60) . "...</p>
                    </a>
                    
                    <form class='ajax-add-to-cart-form'>
                        <input type='hidden' name='quantity' value='1'>
                        <button type='submit' class='add-to-cart-btn' data-id='{$product_id}'>Add to Cart</button>
                    </form>
                    
                </div>
                ";
            }
        } else {
            echo "<p>No products found in this category.</p>";
        }
        $conn->close();
        ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>