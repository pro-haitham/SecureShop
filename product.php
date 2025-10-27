<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Product not found or out of stock
    header('Location: index.php?error=not_found');
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

include 'includes/header.php'; // Use new header
?>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?> - SecureShop</title>
</head>

<main class="container">
    <div class="product-page-container">
        <div class="product-image-column">
            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-details-column">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-price-large">$<?php echo number_format($product['price'], 2); ?></p>
            <p class="product-stock <?php echo $product['stock'] <= 5 ? 'low' : ''; ?>">
                <?php echo $product['stock']; ?> in stock
                <?php if ($product['stock'] <= 5) echo "(Order soon!)"; ?>
            </p>
            
            <div class="product-description-full">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                
                <button type="submit" class="add-to-cart-btn large">Add to Cart</button>
            </form>
            
            <a href="index.php" class="back-link">← Back to products</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>