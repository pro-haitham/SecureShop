<?php
session_start();
include '../includes/db.php';
// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Set the current page for active nav link
$current_page = ''; // Not a main nav item

// --- Fetch categories for the dropdown ---
$categories_stmt = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = intval($_GET['id']);
$message = "";
$error = "";

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
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null; // <-- BUG FIX
    $image = $product['image']; // Default to old image

    // --- Secure File Upload Check ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $error = "File is not an image.";
            $uploadOk = 0;
        }

        if ($_FILES['image']['size'] > 5000000) { // 5MB limit
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = basename($_FILES['image']['name']); // Set new image name
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Only update if no error
    if (empty($error)) {
        // --- BUG FIX: Added category_id to query ---
        $stmt_update = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, stock=?, image=? WHERE id=?");
        $stmt_update->bind_param("ssdiisi", $name, $desc, $price, $category_id, $stock, $image, $id);

        if ($stmt_update->execute()) {
            $message = "Product updated successfully!";
            // Refresh product data to show new values
            $stmt_refresh = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt_refresh->bind_param("i", $id);
            $stmt_refresh->execute();
            $product = $stmt_refresh->get_result()->fetch_assoc();
            $stmt_refresh->close();
        } else {
            $error = "Error updating product.";
        }
        $stmt_update->close();
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
            <h2>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h2>
            
            <?php if ($message): ?><p class="message success"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="message error"><?php echo $error; ?></p><?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" action="edit_product.php?id=<?php echo $id; ?>">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

                <label for="desc">Description</label>
                <textarea id="desc" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

                <label for="price">Price ($)</label>
                <input type="number" id="price" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>

                <label for="category">Category</label>
                <select id="category" name="category_id">
                    <option value="">(No Category)</option>
                    <?php 
                    // Reset pointer and loop through categories for dropdown
                    $categories_stmt->data_seek(0); 
                    while ($cat = $categories_stmt->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="image">Current Image</label>
                <img src="../assets/images/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" alt="Product Image" style="width: 100px; height: auto; border-radius: 5px;">
                
                <label for="image_new">Upload New Image (optional)</label>
                <input type="file" id="image_new" name="image">

                <button type="submit" class="btn-submit">Save Changes</button>
            </form>
        </div>
    </main>

<footer class="admin-footer">
    <p>© <?php echo date('Y'); ?> SecureShop Admin Panel</p>
</footer>

</body>
</html>