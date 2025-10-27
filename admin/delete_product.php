<?php
session_start();
include '../includes/db.php'; // Correct path to DB connection

// --- Access Control: Admin Only ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// --- Validate ID Parameter ---
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

$id = intval($_GET['id']);
if ($id <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// --- Optional: Delete associated image file ---
$stmt_get = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$res = $stmt_get->get_result();

if ($res && $prod = $res->fetch_assoc()) {
    $image_path = "../assets/images/" . basename($prod['image']); // Security: basename() prevents path traversal
    if (is_file($image_path)) {
        @unlink($image_path); // @ suppresses warning if file missing
    }
}
$stmt_get->close();

// --- Delete product record from database ---
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // ✅ Success: Redirect with confirmation
    header("Location: dashboard.php?deleted=1");
    exit;
} else {
    // ❌ Database deletion failed
    header("Location: dashboard.php?error=delete_failed");
    exit;
}

$stmt->close();
$conn->close();
?>