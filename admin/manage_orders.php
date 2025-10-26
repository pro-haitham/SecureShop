<?php
include '../db.php';
$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Orders</title></head>
<body>
<h2>Manage Orders</h2>
<table border="1" cellpadding="8">
<tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['customer_name']); ?></td>
<td>$<?php echo number_format($row['total'], 2); ?></td>
<td><?php echo htmlspecialchars($row['status']); ?></td>
<td><?php echo htmlspecialchars($row['created_at']); ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
