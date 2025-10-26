<?php
include 'includes/db.php';

// Check if ID is passed in URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid product ID.";
    exit;
}

$id = intval($_GET['id']); // Sanitize ID

// Fetch product from database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found.";
    exit;
}

// Fetch product details
$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?> - Secure Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>

    <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" width="250" alt="Product Image"><br><br>

    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>

    <a href="add_to_cart.php?id=<?php echo $product['id']; ?>">Add to Cart</a>
</body>
</html>
