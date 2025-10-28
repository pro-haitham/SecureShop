<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Set the current page for active nav link
$current_page = 'orders';

$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
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
        <div class="data-table full-width">
            <h2>Manage Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><a href="order_details.php?id=<?php echo $row['id']; ?>" title="View Details"><strong>#<?php echo $row['id']; ?></strong></a></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td>$<?php echo number_format($row['total'], 2); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="no-data">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

<footer class="admin-footer">
    <p>© <?php echo date('Y'); ?> SecureShop Admin Panel</p>
</footer>

</body>
</html>