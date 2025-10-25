<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart']) || empty($_SESSION['checkout'])) {
    header("Location: cart.php");
    exit();
}

// Minimal validation of demo card inputs
$card = trim($_POST['card_number'] ?? '');
$expiry = trim($_POST['expiry'] ?? '');
$cvc = trim($_POST['cvc'] ?? '');

// Don't store card data. This is a demo only.
if (empty($card) || empty($expiry) || empty($cvc)) {
    header("Location: payment.php");
    exit();
}

// Simulate payment: 90% success, 10% fail
$rand = rand(1, 100);
$success = $rand <= 90;

if (!$success) {
    // Payment failed simulation
    $_SESSION['payment_error'] = "Payment failed (simulated). Please try again.";
    header("Location: payment.php");
    exit();
}

// Payment "succeeded" -> proceed to insert order & items

// Calculate total and ensure product prices are current
$total = 0.0;
$items_details = []; // array of [product_id, qty, price]

foreach ($_SESSION['cart'] as $pid => $item) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($p = $res->fetch_assoc()) {
        $price = (float)$p['price'];
        $qty = (int)$item['quantity'];
        $total += $price * $qty;
        $items_details[] = ['id' => $pid, 'qty' => $qty, 'price' => $price];
    } else {
        // product not found — skip or handle error
    }
    $stmt->close();
}

// Use checkout info from session
$name = $_SESSION['checkout']['name'];
$email = $_SESSION['checkout']['email'];
$address = $_SESSION['checkout']['address'];

// If user logged in, associate user_id
$user_id = $_SESSION['user_id'] ?? null;

// Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, address, total, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("isssd", $user_id, $name, $email, $address, $total);

if (!$stmt->execute()) {
    // handle DB error
    $_SESSION['payment_error'] = "Failed to create order: " . $conn->error;
    header("Location: payment.php");
    exit();
}

$order_id = $stmt->insert_id;
$stmt->close();

// Insert order items
$stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($items_details as $it) {
    $stmt_item->bind_param("iiid", $order_id, $it['id'], $it['qty'], $it['price']);
    $stmt_item->execute();
}
$stmt_item->close();

// (Optional) Decrease product stock
$update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
foreach ($items_details as $it) {
    $u = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
    $u->bind_param("ii", $it['qty'], $it['id']);
    $u->execute();
    $u->close();
}

// Clear cart & checkout session
$_SESSION['cart'] = [];
unset($_SESSION['checkout']);

// Generate fake tracking number
$tracking = 'TRK' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 10));

// Redirect to success page with tracking (do not expose sensitive data in URL)
header("Location: order_success.php?tracking={$tracking}&order_id={$order_id}");
exit();
