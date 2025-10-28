<?php
session_start();
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['product_id'])) {
    $id = (int)$data['product_id'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

// --- IMPROVEMENT: Calculate and return new cart count ---
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

echo json_encode(['success' => true, 'cart_count' => $cart_count]);
exit();
?>