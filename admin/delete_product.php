<?php
include '../db.php';

if (!isset($_GET['id'])) {
    die("Product not found.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: manage_orders.php?deleted=1");
} else {
    echo "Error deleting product.";
}
?>
