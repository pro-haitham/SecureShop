<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart']) || empty($_SESSION['checkout'])) {
    header("Location: cart.php");
    exit();
}

// Calculate total for display (safe, as it's re-calculated in process_payment.php)
$total = 0.0;
foreach ($_SESSION['cart'] as $id => $item) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($p = $res->fetch_assoc()) {
        $total += $p['price'] * $item['quantity'];
    }
    $stmt->close();
}

$payment_error = $_SESSION['payment_error'] ?? null;
unset($_SESSION['payment_error']); // Clear error after displaying

include 'includes/header.php'; // Use new header
?>
<head>
    <title>Payment - SecureShop</title>
</head>

<main class="container">
    <div class="form-container">
        <h2>Payment - Step 2 of 2 (Demo)</h2>
        <div class="order-summary">
            <strong>Order Total: $<?php echo number_format($total, 2); ?></strong><br>
            Billing to: <?php echo htmlspecialchars($_SESSION['checkout']['name']); ?>
        </div>
        
        <?php if ($payment_error): ?>
            <p class="message error"><?php echo htmlspecialchars($payment_error); ?></p>
        <?php endif; ?>

        <form method="POST" action="process_payment.php">
            <label for="card_number">Card Number (demo)</label>
            <input type="text" id="card_number" name="card_number" maxlength="19" placeholder="4242 4242 4242 4242" required>

            <div class="form-row">
                <div>
                    <label for="expiry">Expiry (MM/YY)</label>
                    <input type="text" id="expiry" name="expiry" maxlength="5" placeholder="12/34" required>
                </div>
                <div>
                    <label for="cvc">CVC</label>
                    <input type="text" id="cvc" name="cvc" maxlength="4" placeholder="123" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Pay $<?php echo number_format($total, 2); ?> (Demo)</button>
        </form>
        <p class="form-switch"><a href="checkout.php">← Back to Details</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>