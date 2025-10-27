<?php
session_start();
include '../includes/db.php'; // BUG FIX: Corrected path
// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Fetch existing product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    die("Product not found.");
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = $product['image']; // Default to old image

    // --- NEW: Secure File Upload Check ---
    // Check if a new file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        if ($_FILES['image']['size'] > 5000000) { // 5MB limit
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = basename($_FILES['image']['name']); // Set new image name
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $uploadOk = 0;
            }
        }
    }
    // --- End Secure File Upload ---

    // Only update if message is not set (i.e., no upload error)
    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?");
        $stmt->bind_param("ssdisi", $name, $desc, $price, $stock, $image, $id);

        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            // Refresh product data to show new values
            $stmt_refresh = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt_refresh->bind_param("i", $id);
            $stmt_refresh->execute();
            $product = $stmt_refresh->get_result()->fetch_assoc();
            $stmt_refresh->close();
        } else {
            $message = "Error updating product.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="add_product.php">Add Product</a>
            <a href="manage_orders.php">Manage Orders</a>
            <a href="../index.php" target="_blank">View Shop</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container">
        <div class="form-container-admin">
            <h2>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h2>
            <?php if ($message): ?>
                <p class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

                <label for="desc">Description</label>
                <textarea id="desc" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

                <label for="price">Price ($)</label>
                <input type="number" id="price" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>

                <label for="image">Current Image</label>
                <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" style="width: 100px; height: auto; border-radius: 5px;">
                
                <label for="image_new">Upload New Image (optional)</label>
                <input type="file" id="image_new" name="image">

                <button type="submit" class="btn-submit">Save Changes</button>
            </form>
        </div>
    </main>
</body>
</html>