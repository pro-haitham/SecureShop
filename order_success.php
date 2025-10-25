<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$tracking = $_GET['tracking'] ?? null;
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

if (!$tracking || !$order_id) {
    header("Location: index.php");
    exit();
}

// Optionally: fetch order summary to show details
$stmt = $conn->prepare("SELECT id, customer_name, email, total, created_at FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html>
<head><title>Order Success</title></head>
<body>
    <h2>Order Placed Successfully ✅</h2>
    <p>Thank you, <?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?>.</p>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
    <p><strong>Tracking Number:</strong> <?= htmlspecialchars($tracking) ?></p>
    <p><strong>Total Paid:</strong> $<?= number_format($order['total'] ?? 0, 2) ?></p>
    <p>A confirmation email (demo) was sent to: <?= htmlspecialchars($order['email'] ?? '') ?></p>

    <p><a href="index.php">Continue Shopping</a> | <a href="profile.php">View Orders</a></p>
</body>
</html>