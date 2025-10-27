<?php
session_start();
include 'includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, total, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $uid);
$stmt->execute();
$orders = $stmt->get_result();

include 'includes/header.php'; // Use new header
?>
<head>
    <title>Your Profile - SecureShop</title>
</head>

<main class="container">
    <div class="profile-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>This is your account dashboard where you can view your recent orders.</p>
        
        <h3>Your Order History</h3>
        <div class="order-history-table">
            <?php if ($orders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($o = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $o['id']; ?></td>
                        <td><?php echo date('F j, Y', strtotime($o['created_at'])); ?></td>
                        <td>$<?php echo number_format($o['total'], 2); ?></td>
                        <td>Completed</td> </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>You have not placed any orders yet.</p>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>