<?php
session_start();
include 'includes/db.php';

// IMPROVEMENT: Set page title
$page_title = "Your Shopping Cart - SecureShop";

include 'includes/header.php'; // Use the new header

$total = 0;
$products_in_cart = [];

if (!empty($_SESSION['cart'])) {
    $cart_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
    $types = str_repeat('i', count($cart_ids));
    
    $stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$cart_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products_data = [];
    while ($p = $result->fetch_assoc()) {
        $products_data[$p['id']] = $p;
    }
    $stmt->close();

    foreach ($_SESSION['cart'] as $id => $item) {
        if (isset($products_data[$id])) {
            $product = $products_data[$id];
            $subtotal = $product['price'] * $item['quantity'];
            $total += $subtotal;
            
            $products_in_cart[] = [
                'id' => $id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'stock' => $product['stock']
            ];
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
}
?>

<main class="container">
    <div class="cart-container">
        <h2>Your Shopping Cart</h2>

        <div id="cart-content">
            <?php if (!empty($products_in_cart)): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products_in_cart as $p): ?>
                        <tr data-product-id="<?php echo $p['id']; ?>">
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td>$<?php echo number_format($p['price'], 2); ?></td>
                            <td class="cart-quantity">
                                <button class="btn-quantity" data-action="decrease" data-id="<?php echo $p['id']; ?>">-</button>
                                <span><?php echo $p['quantity']; ?></span>
                                <button class="btn-quantity" data-action="increase" data-id="<?php echo $p['id']; ?>" <?php echo ($p['quantity'] >= $p['stock']) ? 'disabled' : ''; ?>>+</button>
                            </td>
                            <td class="cart-subtotal">$<?php echo number_format($p['subtotal'], 2); ?></td>
                            <td>
                                <button class="btn-remove" data-id="<?php echo $p['id']; ?>">Remove</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-summary">
                    <h3 class="cart-total">Total: $<?php echo number_format($total, 2); ?></h3>
                    <a href='checkout.php' class='btn btn-primary'>Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <p>Your cart is empty!</p>
            <?php endif; ?>
        </div>
        <br><br>
        <a href="index.php">← Continue Shopping</a>
    </div>
</main>

<?php include 'includes/footer.php'; // Use the new footer ?>