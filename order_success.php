<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$tracking = sanitize_input($_GET['tracking'] ?? null);
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

if (!$tracking || !$order_id) {
    header("Location: index.php");
    exit();
}

// Fetch order details to display
$stmt = $conn->prepare("SELECT customer_name, email, total FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
    // Order not found
    header("Location: index.php");
    exit();
}

include 'includes/header.php'; // Use new header
?>
<head>
    <title>Order Success! - SecureShop</title>
</head>

<main class="container">
    <div class="success-container">
        <h2>✅ Order Placed Successfully!</h2>
        <p>Thank you, <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>. Your order has been confirmed.</p>
        
        <div class="success-details">
            <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_id); ?></p>
            <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($tracking); ?></p>
            <p><strong>Total Paid:</strong> $<?php echo number_format($order['total'], 2); ?></p>
            <p>A confirmation email (demo) has been sent to: <strong><?php echo htmlspecialchars($order['email']); ?></strong></p>
        </div>

        <div class="success-actions">
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            <a href="profile.php" class="btn-secondary">View My Orders</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>