<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Please fill all required fields.";
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
                $_SESSION['role'] = 'user';
                header('Location: index.php');
                exit;
            } else {
                $message = "Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Sign Up</title></head>
<body>
<h2>Sign Up</h2>
<p style="color:red"><?= $message ?></p>
<form method="POST">
  <input type="text" name="username" placeholder="Username" required><br>
  <input type="email" name="email" placeholder="Email"><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button type="submit">Register</button>
</form>
</body>
</html>
