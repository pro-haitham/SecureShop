<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($username) || empty($password) || empty($email)) {
        $message = "Please fill all required fields.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $message = "Invalid email format.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username or email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $username, $email, $hash);
            if ($ins->execute()) {
                $_SESSION['user_id'] = $ins->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user'; // Default role
                header('Location: profile.php'); // Redirect to profile
                exit;
            } else {
                $message = "Registration failed.";
            }
            $ins->close();
        }
        $check->close();
    }
}

// IMPROVEMENT: Set the dynamic page title
$page_title = "Sign Up - SecureShop";
include 'includes/header.php'; // Use new header
?>

<main class="container">
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if ($message): ?>
            <p class="message error"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="signup.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password (min 8 characters)</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="btn-submit">Register</button>
        </form>
        <p class="form-switch">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; // Use new footer ?>