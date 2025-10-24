<?php
session_start();
include 'includes/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <style>
        body { font-family: Arial; background: #fafafa; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
        th { background: #00b894; color: white; }
        a { text-decoration: none; color: #00b894; }
        .btn { background: #00b894; color: white; padding: 8px 12px; border-radius: 5px; }
        .btn:hover { background: #019870; }
    </style>
</head>
<body>
<h2>Your Shopping Cart</h2>

<?php
$total = 0;

if (!empty($_SESSION['cart'])) {
    echo "<table>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>";

    foreach ($_SESSION['cart'] as $id => $item) {
        $sql = "SELECT name, price FROM products WHERE id = $id";
        $result = $conn->query($sql);
        $product = $result->fetch_assoc();

        $subtotal = $product['price'] * $item['quantity'];
        $total += $subtotal;

        echo "<tr>
                <td>{$product['name']}</td>
                <td>{$item['quantity']}</td>
                <td>$ {$product['price']}</td>
                <td>$ {$subtotal}</td>
                <td><a href='remove_from_cart.php?id=$id'>Remove</a></td>
              </tr>";
    }

    echo "</table><h3>Total: $ {$total}</h3>";
    echo "<a href='checkout.php' class='btn'>Proceed to Checkout</a>";
} else {
    echo "<p>Your cart is empty!</p>";
}
?>
<br><br>
<a href="index.php">← Continue Shopping</a>
</body>
</html>
