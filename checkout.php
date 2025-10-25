<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

// If cart empty, redirect to cart
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$message = "";

// Show simple checkout form; on submit redirect to payment.php with basic details stored in session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // sanitize and store in session (so payment page can re-show)
    $_SESSION['checkout'] = [
        'name'    => sanitize_input($_POST['name']),
        'email'   => sanitize_input($_POST['email']),
        'address' => sanitize_input($_POST['address']),
    ];

    header("Location: payment.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Checkout</title></head>
<body>
    <h2>Checkout</h2>
    <form method="POST" action="">
        <label>Full Name</label><br>
        <input type="text" name="name" required value="<?= isset($_SESSION['checkout']['name']) ? htmlspecialchars($_SESSION['checkout']['name']) : '' ?>"><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required value="<?= isset($_SESSION['checkout']['email']) ? htmlspecialchars($_SESSION['checkout']['email']) : '' ?>"><br><br>

        <label>Address</label><br>
        <textarea name="address" required><?= isset($_SESSION['checkout']['address']) ? htmlspecialchars($_SESSION['checkout']['address']) : '' ?></textarea><br><br>

        <button type="submit">Continue to Payment</button>
    </form>
    <p><a href="cart.php">← Back to Cart</a></p>
</body>
</html>
