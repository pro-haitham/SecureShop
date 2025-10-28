<?php
session_start();
include '../includes/db.php';

// // --- Access Control: Only Admins Allowed ---
// if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }

// Set the current page for active nav link
$current_page = 'add_product';

$message = "";
$error = "";

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

    $image = "";
    $uploadOk = 0; // Set to 0 initially

    // --- Validate uploaded file ---
    if (!empty($_FILES['image']['tmp_name'])) {
        $image = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1; // Assume OK for now
        
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $error = "File is not an image.";
            $uploadOk = 0;
        }

        // File size limit: 5MB
        if ($_FILES['image']['size'] > 5000000) {
            $error = "File too large (max 5MB).";
            $uploadOk = 0;
        }

        // Allow only specific formats
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedTypes)) {
            $error = "Only JPG, JPEG, PNG, or GIF files are allowed.";
            $uploadOk = 0;
        }

        // Prevent overwriting
        if (file_exists($target_file)) {
            $image = time() . "_" . $image; // Rename file if it exists
            $target_file = $target_dir . $image;
        }
        
    } else {
        $error = "No image file selected.";
        $uploadOk = 0;
    }

    // --- Final Upload & Database Insert ---
    if (empty($error) && $uploadOk === 1) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiis", $name, $desc, $price, $category_id, $stock, $image);

            if ($stmt->execute()) {
                $message = "Product added successfully!";
            } else {
                $error = "Database error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $error = "File upload failed. Check folder permissions.";
        }
    }
}

// --- Fetch categories for dropdown ---
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>

<header class="admin-header">
    <h1>🛍️ Admin Dashboard</h1>
    <nav class="admin-nav">
        <a href="dashboard.php" class="<?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="manage_categories.php" class="<?php echo ($current_page === 'categories') ? 'active' : ''; ?>">Categories</a>
        <a href="add_product.php" class="<?php echo ($current_page === 'add_product') ? 'active' : ''; ?>">Add Product</a>
        <a href="manage_orders.php" class="<?php echo ($current_page === 'orders') ? 'active' : ''; ?>">Manage Orders</a>
        <a href="../index.php" target="_blank">View Shop</a>
        <a href="../logout.php" class="logout">Logout</a>
    </nav>
</header>

<main class="admin-container">
    <div class="form-container-admin">
        <h2>Add New Product</h2>

        <?php if ($message): ?><p class="message success"><?php echo $message; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="message error"><?php echo $error; ?></p><?php endif; ?>

        <form action="add_product.php" method="POST" enctype="multipart/form-data">
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
                    <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif" required>

            <button type="submit" class="btn-submit">Add Product</button>
        </form>
    </div>
</main>

<footer class="admin-footer">
    <p>© <?php echo date('Y'); ?> SecureShop Admin Panel</p>
</footer>

</body>
</html>