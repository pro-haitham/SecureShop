<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/functions.php';

// Check for POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // 1. Basic validation
    if ($quantity <= 0) {
        // Redirect with error
        header('Location: index.php?error=invalid_quantity');
        exit();
    }

    // 2. Fetch product details and check stock
    // BUG FIX: Changed $link to $conn
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        header('Location: index.php?error=db_error');
        exit();
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        header('Location: index.php?error=not_found');
        exit();
    }

    // 3. Check stock
    if ($product['stock'] < $quantity) {
        header('Location: product.php?id=' . $product_id . '&error=stock_exceeded');
        exit();
    }

    // 4. Update or add item to session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        // Check if adding more exceeds stock
        $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
             header('Location: product.php?id=' . $product_id . '&error=stock_exceeded');
             exit();
        }
        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
    } else {
        // Store minimal info. We fetch details in the cart.
        $_SESSION['cart'][$product_id] = [
            'quantity' => $quantity
        ];
    }

    // NEW FEATURE: Redirect to index with a success flag for the toast
    header('Location: index.php?added=1');
    exit();

} else {
    // No POST data, just go home
    header('Location: index.php');
    exit();
}
?>