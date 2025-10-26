<?php
include '../db.php';

if (!isset($_GET['id'])) die("Product not found.");
$id = intval($_GET['id']);
$message = "";

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) die("Product not found.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . basename($image));
    } else {
        $image = $product['image'];
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?");
    $stmt->bind_param("ssdsi", $name, $desc, $price, $image, $id);

    if ($stmt->execute()) $message = "Product updated successfully!";
    else $message = "Error updating product.";
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Product</title></head>
<body>
<h2>Edit Product</h2>
<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br><br>
    Description: <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br><br>
    Price: <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required><br><br>
    Image: <input type="file" name="image"><br><br>
    <button type="submit">Save Changes</button>
</form>
<p><?php echo $message; ?></p>
</body>
</html>
