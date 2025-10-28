<?php
session_start();
include_once 'includes/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get data from JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['product_id'], $data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$product_id = (int)$data['product_id'];
$quantity = (int)$data['quantity'];

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity.']);
    exit;
}

// 2. Fetch product details and check stock
$stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

// 4. Update or add item to session cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$new_quantity = $quantity;
if (isset($_SESSION['cart'][$product_id])) {
    $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
}

// 3. Check stock
if ($product['stock'] < $new_quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock!']);
    exit;
}

$_SESSION['cart'][$product_id] = ['quantity' => $new_quantity];

// Calculate new cart count
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

echo json_encode([
    'success' => true, 
    'message' => 'Product added to cart!',
    'cart_count' => $cart_count
]);
exit;
?>