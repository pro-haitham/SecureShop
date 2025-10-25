<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { header('Location: ../login.php'); }

$res = $conn->query("SELECT COUNT(*) as total FROM orders");
$t = $res->fetch_assoc();
$recent = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$low = $conn->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC");
?>
<!-- HTML: show $t['total'], loop $recent and $low -->
