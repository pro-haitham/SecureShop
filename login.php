<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=? OR email=?");
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $hash, $role);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username; // Store the actual username
            $_SESSION['role'] = $role;
            
            // Redirect admin to dashboard
            if ($role === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: profile.php'); // Redirect user to profile
            }
            exit;
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Invalid username or password.";
    }
    $stmt->close();
}

// IMPROVEMENT: Set the dynamic page title
$page_title = "Login - SecureShop";
include 'includes/header.php'; // Use new header
?>

<main class="container">
    <div class="form-container">
        <h2>Login</h2>
        <?php if ($message): ?>
            <p class="message error"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="btn-submit">Login</button>
        </form>
        <p class="form-switch">Don't have an account? <a href="signup.php">Sign up here</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>