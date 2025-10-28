<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart']) || empty($_SESSION['checkout'])) {
    header("Location: cart.php");
    exit();
}

// --- PERFORMANCE FIX: Calculate total in one query ---
$total = 0.0;
if (!empty($_SESSION['cart'])) {
    $cart_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
    $types = str_repeat('i', count($cart_ids));
    
    $stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$cart_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products_data = [];
    while ($p = $result->fetch_assoc()) {
        $products_data[$p['id']] = $p['price'];
    }
    $stmt->close();

    foreach ($_SESSION['cart'] as $id => $item) {
        if (isset($products_data[$id])) {
            $total += $products_data[$id] * $item['quantity'];
        }
    }
}
// --- End Performance Fix ---

$payment_error = $_SESSION['payment_error'] ?? null;
unset($_SESSION['payment_error']); // Clear error after displaying

// IMPROVEMENT: Set the dynamic page title
$page_title = "Payment - SecureShop";
include 'includes/header.php'; // Use new header
?>
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