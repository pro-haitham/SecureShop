<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    
    $product_id = (int)sanitize_input($_POST['product_id']);
    $quantity = (int)sanitize_input($_POST['quantity']);
    
    // 1. Basic validation
    if ($quantity <= 0) {
        redirect('/index.php?error=Quantity must be positive');
    }

    // 2. Fetch product details and check stock
    $stmt = $link->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $link->error);
        redirect('/index.php?error=System error on product fetch');
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        redirect('/index.php?error=Product not found');
    }

    // 3. Check stock availability (simple check)
    if ($product['stock'] < $quantity) {
        redirect('/product.php?id=' . $product_id . '&error=Insufficient stock. Only ' . $product['stock'] . ' left.');
    }

    // 4. Update or add item to session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        // Check if adding more exceeds stock
        $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
             redirect('/product.php?id=' . $product_id . '&error=Cannot add. Total exceeds stock.');
        }
        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'quantity' => $quantity,
            'image' => '/assets/images/' . ($product['image'] ?? 'placeholder.jpg') // Placeholder image logic
        ];
    }

    redirect('/cart.php?success=Product added to cart!');

} else {
    redirect('/index.php');
}
?>
