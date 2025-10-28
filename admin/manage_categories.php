<?php
session_start();
include '../includes/db.php';
// if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }

$message = '';
$error = '';
$edit_category = null;

// Handle Form Submissions (Add/Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;

    // --- Secure File Upload Logic ---
    $image = $_POST['current_image'] ?? null; // Keep old image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        if (getimagesize($_FILES['image']['tmp_name']) === false) {
            $error = "File is not an image.";
            $uploadOk = 0;
        } elseif ($_FILES['image']['size'] > 5000000) { // 5MB limit
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
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

    if (empty($error)) {
        if ($id) {
            // Update existing category
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $description, $image, $id);
            if ($stmt->execute()) {
                $message = "Category updated successfully!";
            } else {
                $error = "Error updating category: " . $conn->error;
            }
        } else {
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $description, $image);
            if ($stmt->execute()) {
                $message = "Category added successfully!";
            } else {
                $error = "Error adding category: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // We set product category_id to NULL on delete (defined in SQL), so this is safe.
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Category deleted successfully!";
    } else {
        $error = "Error deleting category.";
    }
    $stmt->close();
}

// Handle Edit (Fetch data for form)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_category = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch all categories for display
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_categories.php">Categories</a> <a href="add_product.php">Add Product</a>
            <a href="manage_orders.php">Manage Orders</a>
            <a href="../index.php" target="_blank">View Shop</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container admin-grid">
        <div class="form-container-admin">
            <h2><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h2>
            
            <?php if ($message): ?><p class="message success"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="message error"><?php echo $error; ?></p><?php endif; ?>

            <form method="POST" action="manage_categories.php" enctype="multipart/form-data">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_category['name'] ?? ''); ?>" required>

                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($edit_category['description'] ?? ''); ?></textarea>

                <?php if ($edit_category && $edit_category['image']): ?>
                    <label>Current Image</label>
                    <img src="../assets/images/<?php echo htmlspecialchars($edit_category['image']); ?>" alt="Category Image" style="width: 100px; height: auto; border-radius: 5px;">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_category['image']); ?>">
                <?php endif; ?>

                <label for="image">Image</label>
                <input type="file" id="image" name="image">

                <button type="submit" class="btn-submit"><?php echo $edit_category ? 'Update Category' : 'Add Category'; ?></button>
                <?php if ($edit_category): ?>
                    <a href="manage_categories.php" class="btn-cancel">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="data-table">
            <h2>Existing Categories</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><img src="../assets/images/<?php echo htmlspecialchars($cat['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($cat['description'], 0, 50)); ?>...</td>
                            <td class="actions">
                                <a href="manage_categories.php?edit=<?php echo $cat['id']; ?>" class="btn-edit">Edit</a>
                                <a href="manage_categories.php?delete=<?php echo $cat['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure? This will not delete products, but will un-categorize them.');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No categories found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>