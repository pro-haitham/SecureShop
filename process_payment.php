<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart']) || empty($_SESSION['checkout'])) {
    header("Location: cart.php");
    exit();
}

// Minimal validation of demo card inputs
if (empty($_POST['card_number']) || empty($_POST['expiry']) || empty($_POST['cvc'])) {
    $_SESSION['payment_error'] = "Please fill in all payment fields.";
    header("Location: payment.php");
    exit();
}

// Simulate payment: 90% success, 10% fail
if (rand(1, 100) > 90) {
    $_SESSION['payment_error'] = "Payment failed (simulated). Please try again.";
    header("Location: payment.php");
    exit();
}

// --- IMPROVEMENT: Transaction and efficient data processing ---
$total = 0.0;
$items_details = [];
$cart_ids = array_keys($_SESSION['cart']);

// 1. Get all product data in ONE query
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
$types = str_repeat('i', count($cart_ids));
$stmt_products = $conn->prepare("SELECT id, price, stock FROM products WHERE id IN ($placeholders)");
$stmt_products->bind_param($types, ...$cart_ids);
$stmt_products->execute();
$products_data = $stmt_products->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_products->close();

// Map for easy lookup
$products_map = [];
foreach ($products_data as $p) {
    $products_map[$p['id']] = $p;
}

// 2. Final validation and total calculation
foreach ($_SESSION['cart'] as $pid => $item) {
    $qty = (int)$item['quantity'];
    
    // Check if product still exists
    if (!isset($products_map[$pid])) {
        $_SESSION['payment_error'] = "An item in your cart is no longer available.";
        header("Location: payment.php");
        exit();
    }
    
    $product = $products_map[$pid];
    
    // --- CRITICAL: Final stock check ---
    if ($product['stock'] < $qty) {
        $_SESSION['payment_error'] = "Stock level for " . htmlspecialchars($product['name']) . " changed. Only " . $product['stock'] . " left.";
        header("Location: payment.php");
        exit();
    }
    
    $price = (float)$product['price'];
    $total += $price * $qty;
    $items_details[] = ['id' => $pid, 'qty' => $qty, 'price' => $price];
}

// 3. Start database transaction
$conn->begin_transaction();

try {
    // 4. Insert into orders table
    $name = $_SESSION['checkout']['name'];
    $email = $_SESSION['checkout']['email'];
    $address = $_SESSION['checkout']['address'];
    $user_id = $_SESSION['user_id'] ?? null;

    $stmt_order = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, address, total, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt_order->bind_param("isssd", $user_id, $name, $email, $address, $total);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;
    $stmt_order->close();

    // 5. Insert order items
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($items_details as $it) {
        // Insert item
        $stmt_item->bind_param("iiid", $order_id, $it['id'], $it['qty'], $it['price']);
        $stmt_item->execute();
        
        // Update stock
        $stmt_stock->bind_param("ii", $it['qty'], $it['id']);
        $stmt_stock->execute();
    }
    $stmt_item->close();
    $stmt_stock->close();

    // 6. If all queries succeeded, commit the transaction
    $conn->commit();

} catch (mysqli_sql_exception $exception) {
    // 7. If any query failed, roll back all changes
    $conn->rollback();
    
    error_log("Order processing failed: " . $exception->getMessage());
    $_SESSION['payment_error'] = "A database error occurred. Your order was not placed. Please try again.";
    header("Location: payment.php");
    exit();
}
// --- End Transaction ---

// 8. Clear cart & checkout session
$_SESSION['cart'] = [];
unset($_SESSION['checkout']);

// 9. Redirect to success
$tracking = 'TRK' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 10));
header("Location: order_success.php?tracking={$tracking}&order_id={$order_id}");
exit();
?>