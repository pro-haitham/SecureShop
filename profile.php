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
?>
<!DOCTYPE html>
<html>
<head><title>Your Profile</title></head>
<body>
<h2>Welcome <?= htmlspecialchars($_SESSION['username']); ?></h2>
<a href="logout.php">Logout</a>
<h3>Your Orders</h3>
<?php while ($o = $orders->fetch_assoc()): ?>
  <p>Order #<?= $o['id'] ?> | $<?= $o['total'] ?> | <?= $o['created_at'] ?></p>
<?php endwhile; ?>
</body>
</html>
