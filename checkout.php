<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['checkout'] = [
        'name'    => sanitize_input($_POST['name']),
        'email'   => sanitize_input($_POST['email']),
        'address' => sanitize_input($_POST['address']),
    ];

    header("Location: payment.php");
    exit();
}

include 'includes/header.php'; // Use new header
?>
<head>
    <title>Checkout - SecureShop</title>
</head>

<main class="container">
    <div class="form-container">
        <h2>Checkout - Step 1 of 2</h2>
        <p>Please enter your shipping and contact details.</p>
        
        <form method="POST" action="checkout.php">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_SESSION['checkout']['name'] ?? ''); ?>">
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['checkout']['email'] ?? ''); ?>">

            <label for="address">Full Address</label>
            <textarea id="address" name="address" required><?php echo htmlspecialchars($_SESSION['checkout']['address'] ?? ''); ?></textarea>

            <button type="submit" class="btn-submit">Continue to Payment</button>
        </form>
        <p class="form-switch"><a href="cart.php">← Back to Cart</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>