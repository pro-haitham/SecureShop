<?php
session_start();
include '../includes/db.php'; // Correct path to DB connection

// --- Access Control: Only admins can view this page ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// --- Fetch Dashboard Stats Safely ---

// 1️⃣ Total Revenue
$res_rev = $conn->query("SELECT SUM(total) AS total_revenue FROM orders");
$revenue = floatval($res_rev->fetch_assoc()['total_revenue'] ?? 0);

// 2️⃣ Total Orders
$res_ord = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$orders_count = intval($res_ord->fetch_assoc()['total_orders'] ?? 0);

// 3️⃣ Total Customers
$res_cust = $conn->query("SELECT COUNT(*) AS total_customers FROM users WHERE role = 'user'");
$customers = intval($res_cust->fetch_assoc()['total_customers'] ?? 0);

// 4️⃣ Low Stock Items (≤ 5)
$low_stock = $conn->query("SELECT id, name, stock FROM products WHERE stock <= 5 AND stock > 0 ORDER BY stock ASC");

// Optionally: auto-close DB connection at end of script for cleanliness
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SecureShop</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>

<header class="admin-header">
    <h1>🛍️ Admin Dashboard</h1>
    <nav class="admin-nav">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="manage_categories.php">Categories</a>
        <a href="add_product.php">Add Product</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="../index.php" target="_blank">View Shop</a>
        <a href="../logout.php" class="logout">Logout</a>
    </nav>
</header>

<main class="admin-container">

    <!-- Dashboard Statistics -->
    <section class="stat-cards">
        <div class="stat-card revenue">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($revenue, 2) ?></p>
        </div>

        <div class="stat-card orders">
            <h3>Total Orders</h3>
            <p><?= $orders_count ?></p>
        </div>

        <div class="stat-card customers">
            <h3>Total Customers</h3>
            <p><?= $customers ?></p>
        </div>
    </section>

    <!-- Low Stock Items Table -->
    <section class="data-tables">
        <div class="data-table">
            <h2>⚠️ Low Stock Items (≤ 5)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Stock Left</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($low_stock && $low_stock->num_rows > 0): ?>
                        <?php while ($item = $low_stock->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= intval($item['stock']) ?></td>
                                <td><a href="edit_product.php?id=<?= intval($item['id']) ?>" class="btn-edit">Edit</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="no-data">No low stock items 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer class="admin-footer">
    <p>© <?= date('Y') ?> SecureShop Admin Panel</p>
</footer>

</body>
</html>
