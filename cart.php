<?php
session_start();
include 'includes/db.php';
include 'includes/header.php'; // Use the new header

$total = 0;
$products_in_cart = [];

if (!empty($_SESSION['cart'])) {
    // PERFORMANCE FIX (N+1 Query):
    // 1. Get all product IDs from the cart
    $cart_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
    $types = str_repeat('i', count($cart_ids));
    
    // 2. Run ONE query to fetch all products
    $stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$cart_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // 3. Map results to an associative array for easy lookup
    $products_data = [];
    while ($p = $result->fetch_assoc()) {
        $products_data[$p['id']] = $p;
    }
    $stmt->close();

    // 4. Now loop through the session and use the fetched data
    foreach ($_SESSION['cart'] as $id => $item) {
        // Check if product still exists (was fetched from DB)
        if (isset($products_data[$id])) {
            $product = $products_data[$id];
            $subtotal = $product['price'] * $item['quantity'];
            $total += $subtotal;
            
            // Store for display
            $products_in_cart[] = [
                'id' => $id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal
            ];
        } else {
            // Product might have been deleted, remove from cart
            unset($_SESSION['cart'][$id]);
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/style.css"> 

<main class="container">
    <h2>Your Shopping Cart</h2>

    <?php if (!empty($products_in_cart)): ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php foreach ($products_in_cart as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?_e($p['quantity'])?></td> <td>$<?php echo number_format($p['price'], 2); ?></td>
                <td>$<?php echo number_format($p['subtotal'], 2); ?></td>
                <td><a href='remove_from_cart.php?id=<?_e($p['id'])?>'>Remove</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h3>Total: $<?php echo number_format($total, 2); ?></h3>
        <a href='checkout.php' class='btn btn-primary'>Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty!</p>
    <?php endif; ?>
    <br><br>
    <a href="index.php">← Continue Shopping</a>
</main>

<?php include 'includes/footer.php'; // Use the new footer ?>