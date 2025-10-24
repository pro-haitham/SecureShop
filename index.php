<?php include 'includes/db.php'; ?>

<?php
session_start();
include 'includes/db.php';
?>


<!DOCTYPE html>
<html>
<head>
    <title>SecureShop - Home</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        h1 { text-align: center; }
        .products { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
        .product {
            background: white; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 15px; width: 220px; text-align: center;
        }
        .product img { width: 100%; height: 180px; object-fit: cover; border-radius: 6px; }
        .product h3 { margin: 10px 0; }
        .product p { color: #555; }
        .price { color: #00b894; font-weight: bold; font-size: 18px; }
        .add-to-cart {
            background: #00b894; color: white; border: none; padding: 10px;
            cursor: pointer; border-radius: 5px; transition: 0.3s;
        }
        .add-to-cart:hover { background: #019870; }
    </style>
</head>
<body>

<h1>🛍️ Welcome to SecureShop</h1>
<div class="products">

<?php
// Fetch products from database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "
        <div class='product'>
            <img src='assets/images/{$row['image']}' alt='{$row['name']}'>
            <h3>{$row['name']}</h3>
            <p class='price'>$ {$row['price']}</p>
            <p>" . substr($row['description'], 0, 50) . "...</p>
            <button class='add-to-cart'>Add to Cart</button>
        </div>
        ";
    }
} else {
    echo "<p>No products found!</p>";
}
$conn->close();
?>

</div>
</body>
</html>
