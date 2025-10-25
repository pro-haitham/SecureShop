<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart']) || empty($_SESSION['checkout'])) {
    header("Location: cart.php");
    exit();
}

// Calculate total for display
$total = 0.0;
foreach ($_SESSION['cart'] as $id => $item) {
    $stmt = $conn->prepare("SELECT price, name FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($p = $res->fetch_assoc()) {
        $total += $p['price'] * $item['quantity'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head><title>Payment - Demo</title></head>
<body>
    <h2>Payment (Demo Mode)</h2>
    <p><strong>Order total:</strong> $<?= number_format($total, 2) ?></p>
    <p><strong>Billing to:</strong> <?= htmlspecialchars($_SESSION['checkout']['name']) ?> — <?= htmlspecialchars($_SESSION['checkout']['email']) ?></p>

    <form method="POST" action="process_payment.php">
        <!-- demo card fields only, do not store them server-side in production -->
        <label>Card Number (demo)</label><br>
        <input type="text" name="card_number" maxlength="19" placeholder="4242 4242 4242 4242" required><br><br>

        <label>Expiry (MM/YY)</label><br>
        <input type="text" name="expiry" maxlength="5" placeholder="12/34" required><br><br>

        <label>CVC</label><br>
        <input type="text" name="cvc" maxlength="4" placeholder="123" required><br><br>

        <button type="submit">Pay $<?= number_format($total, 2) ?> (Demo)</button>
    </form>

    <p><a href="checkout.php">← Back to Checkout</a></p>
</body>
</html>
