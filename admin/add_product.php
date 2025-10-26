<?php
include '../db.php';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);

    $image = $_FILES['image']['name'];
    $target = "../uploads/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $name, $desc, $price, $image);

    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $message = "Error adding product.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Add Product</title></head>
<body>
<h2>Add Product</h2>
<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" required><br><br>
    Description: <textarea name="description" required></textarea><br><br>
    Price: <input type="number" step="0.01" name="price" required><br><br>
    Image: <input type="file" name="image" required><br><br>
    <button type="submit">Add Product</button>
</form>
<p><?php echo $message; ?></p>
</body>
</html>
