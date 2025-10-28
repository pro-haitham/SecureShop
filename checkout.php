<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// --- NEW: Pre-fill form for logged-in users ---
$user_name = $_SESSION['checkout']['name'] ?? '';
$user_email = $_SESSION['checkout']['email'] ?? '';

if (isset($_SESSION['user_id']) && empty($_SESSION['checkout'])) {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $user_name = $user['username'];
        $user_email = $user['email'];
    }
    $stmt->close();
}
// --- End pre-fill ---

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['checkout'] = [
        'name'    => sanitize_input($_POST['name']),
        'email'   => sanitize_input($_POST['email']),
        'address' => sanitize_input($_POST['address']),
    ];
    header("Location: payment.php");
    exit();
}

// IMPROVEMENT: Set page title
$page_title = "Checkout - SecureShop";
include 'includes/header.php'; // Use new header
?>

<main class="container">
    <div class="form-container">
        <h2>Checkout - Step 1 of 2</h2>
        <p>Please enter your shipping and contact details.</p>
        
        <form method="POST" action="checkout.php">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($user_name); ?>">
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user_email); ?>">

            <label for="address">Full Address</label>
            <textarea id="address" name="address" required><?php echo htmlspecialchars($_SESSION['checkout']['address'] ?? ''); ?></textarea>

            <button type="submit" class="btn-submit">Continue to Payment</button>
        </form>
        <p class="form-switch"><a href="cart.php">← Back to Cart</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>