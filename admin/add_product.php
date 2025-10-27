<?php
session_start();
include '../includes/db.php'; // Fixed include path

// --- Access Control: Only Admins Allowed ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = "";

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize & validate input
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

    // --- File Upload Settings ---
    $target_dir = "../assets/images/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $image = basename($_FILES['image']['name']);
    $target_file = $target_dir . $image;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = 1;

    // --- Validate uploaded file ---
    if (!empty($_FILES['image']['tmp_name'])) {
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $message = "File is not an image.";
            $uploadOk = 0;
        }
    } else {
        $message = "No image file selected.";
        $uploadOk = 0;
    }

    // File size limit: 5MB
    if ($_FILES['image']['size'] > 5000000) {
        $message = "File too large (max 5MB).";
        $uploadOk = 0;
    }

    // Allow only specific formats
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedTypes)) {
        $message = "Only JPG, JPEG, PNG, or GIF files are allowed.";
        $uploadOk = 0;
    }

    // Prevent overwriting
    if (file_exists($target_file)) {
        $image = time() . "_" . $image; // Rename file if it exists
        $target_file = $target_dir . $image;
    }

    // --- Final Upload & Database Insert ---
    if ($uploadOk === 1 && move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $desc, $price, $category_id, $stock, $image);

        if ($stmt->execute()) {
            $message = "✅ Product added successfully!";
        } else {
            $message = "❌ Database error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        $message .= " Upload failed.";
    }
}

// --- Fetch categories for dropdown ---
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-container">
    <h1>Add New Product</h1>

    <?php if ($message): ?>
        <div class="message-box"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Product Form -->
    <form action="" method="POST" enctype="multipart/form-data" class="product-form">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <label for="price">Price ($)</label>
        <input type="number" id="price" name="price" step="0.01" required>

        <label for="stock">Stock</label>
        <input type="number" id="stock" name="stock" required>

        <label for="category">Category</label>
        <select id="category" name="category_id">
            <option value="">(No Category)</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="image">Product Image</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif" required>

        <button type="submit">Add Product</button>
    </form>
</div>

</body>
</html>
