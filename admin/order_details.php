<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Set the current page for active nav link
$current_page = 'orders';

// --- Validate ID Parameter ---
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: manage_orders.php?error=invalid_id');
    exit;
}
$order_id = intval($_GET['id']);

// --- Fetch Order Details ---
$stmt_order = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$order = $stmt_order->get_result()->fetch_assoc();
$stmt_order->close();

if (!$order) {
    header('Location: manage_orders.php?error=not_found');
    exit;
}

// --- Fetch Order Items ---
$stmt_items = $conn->prepare("
    SELECT oi.*, p.name AS product_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo $order_id; ?> - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>

<header class="admin-header">
    <h1>🛍️ Admin Dashboard</h1>
    <nav class="admin-nav">
        <a href="dashboard.php" class="<?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="manage_categories.php" class="<?php echo ($current_page === 'categories') ? 'active' : ''; ?>">Categories</a>
        <a href="add_product.php" class="<?php echo ($current_page === 'add_product') ? 'active' : ''; ?>">Add Product</a>
        <a href="manage_orders.php" class="<?php echo ($current_page === 'orders') ? 'active' : ''; ?>">Manage Orders</a>
        <a href="../index.php" target="_blank">View Shop</a>
        <a href="../logout.php" class="logout">Logout</a>
    </nav>
</header>

<main class="admin-container">
    <h2>Order Details: #<?php echo $order['id']; ?></h2>
    <a href="manage_orders.php" style="margin-bottom: 1.5rem; display: inline-block;">&larr; Back to All Orders</a>
    
    <div class="order-details-grid">
        <div class="customer-details-card">
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
        </div>
        
        <div class="order-summary-card">
            <h3>Order Summary</h3>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
            <p><strong>User ID:</strong> <?php echo $order['user_id'] ?? 'N/A (Guest)'; ?></p>
            <p class="total">
                <strong>Total Paid:</strong> $<?php echo number_format($order['total'], 2); ?>
            </p>
        </div>

        <div class="data-table order-items-table">
            <h2>Items in this Order</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price (at time of purchase)</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['product_id']; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="admin-footer">
    <p>© <?php echo date('Y'); ?> SecureShop Admin Panel</p>
</footer>

</body>
</html>