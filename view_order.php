<?php
session_start();
include 'includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get Order ID and User ID
$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: profile.php');
    exit;
}
$order_id = intval($_GET['id']);

// --- SECURITY CHECK ---
// Fetch order details AND verify it belongs to the logged-in user
$stmt_order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$order = $stmt_order->get_result()->fetch_assoc();
$stmt_order->close();

// If order not found OR doesn't belong to this user, redirect
if (!$order) {
    header('Location: profile.php');
    exit;
}

// --- Fetch Order Items ---
$stmt_items = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name AS product_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();

// Set the dynamic page title
$page_title = "Order Details #" . $order_id . " - SecureShop";
include 'includes/header.php';
?>

<main class="container">
    <h2>Order Details: #<?php echo $order['id']; ?></h2>
    
    <div class="order-details-container">
        <div class="order-items-card">
            <h3>Items in this Order</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="order-summary-card">
            <h3>Order Summary</h3>
            <p><strong>Order Date:</strong><br><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
            <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
            
            <p class="total">
                <strong>Total Paid:</strong> $<?php echo number_format($order['total'], 2); ?>
            </p>
        </div>
    </div>
    
    <a href="profile.php" class="back-to-profile">&larr; Back to Profile</a>
</main>

<?php include 'includes/footer.php'; ?>