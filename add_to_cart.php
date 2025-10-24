<?php
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

// initialize cart if empty
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// if product already in cart, increase quantity
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['quantity'] += 1;
} else {
    $_SESSION['cart'][$id] = ['quantity' => 1];
}

header("Location: cart.php");
exit();
?>
