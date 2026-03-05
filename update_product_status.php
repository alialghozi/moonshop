<?php
require 'connection.php';
session_start();

// Ensure the user is logged in as a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id']; // Get the seller's user ID

// Fetch products for the seller's shop
$query = "SELECT * FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = intval($_POST['product_id']);

        if ($action === 'update_status') {
            $new_status = $_POST['status'];
            $query = "UPDATE products SET status = ? WHERE id = ? AND seller_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sii", $new_status, $product_id, $seller_id);

            if ($stmt->execute()) {
                $success_message = "Product status updated successfully!";
            } else {
                $error_message = "Error updating product status: " . $stmt->error;
            }

        } elseif ($action === 'edit_product') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $eco_rating = intval($_POST['eco_rating']);
            $quantity = intval($_POST['quantity']);

            $query = "UPDATE products SET name = ?, description = ?, price = ?, eco_rating = ?, quantity = ? WHERE id = ? AND seller_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssdiiii", $name, $description, $price, $eco_rating, $quantity, $product_id, $seller_id);

            if ($stmt->execute()) {
                $success_message = "Product updated successfully!";
            } else {
                $error_message = "Error updating product: " . $stmt->error;
            }

        } elseif ($action === 'add_stock') {
            $additional_quantity = intval($_POST['additional_quantity']);

            $query = "UPDATE products SET quantity = quantity + ? WHERE id = ? AND seller_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iii", $additional_quantity, $product_id, $seller_id);

            if ($stmt->execute()) {
                $success_message = "Stock added successfully!";
            } else {
                $error_message = "Error adding stock: " . $stmt->error;
            }

        } elseif ($action === 'delete_product') {
            $query = "DELETE FROM products WHERE id = ? AND seller_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $product_id, $seller_id);

            if ($stmt->execute()) {
                $success_message = "Product deleted successfully!";
            } else {
                $error_message = "Error deleting product: " . $stmt->error;
            }

        } elseif ($action === 'update_image') {
            $target_dir = "uploads/";
            $image_file_name = uniqid() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed!";
            } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $error_message = "Error uploading the new image!";
            } else {
                $query = "UPDATE products SET image = ? WHERE id = ? AND seller_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sii", $target_file, $product_id, $seller_id);

                if ($stmt->execute()) {
                    $success_message = "Product image updated successfully!";
                } else {
                    $error_message = "Error updating image: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0px;
        }

        .sidebar {
            width: 250px;
            background-color: #024334;
            color: white;
            position: fixed;
            height: 100vh;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #036852;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            background-color: #fff;
            border-collapse: collapse;
        }

        table th, table td {
            text-align: center;
            padding: 10px;
            border: 1px solid #ccc;
        }

        table th {
            background-color: #024334;
            color: white;
        }

        .btn {
            margin: 5px;
        }

        img {
            width: 80px;
            height: 80px;
        }

        .alert {
            margin: 10px 0;
            padding: 10px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Seller Dashboard</h2>
        <a href="upload_product.php">Upload Product</a>
        <a href="view_order.php">View Orders</a>
        <a href="update_product_status.php">Update Product Status</a>

        <a href="pay_management.php">Pay Management</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h2>Manage Your Products</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Eco Rating</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['eco_rating']); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($product['status']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product"></td>
                        <td>
                           
                            <!-- Edit Product -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="edit_product">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                <input type="text" name="description" value="<?php echo htmlspecialchars($product['description']); ?>">
                                <input type="text" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                <input type="number" name="eco_rating" value="<?php echo htmlspecialchars($product['eco_rating']); ?>" min="1" max="5" required>
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" min="1" required>
                                <button type="submit" class="btn btn-success btn-sm">Save</button>
                            </form>
                            <!-- Add Stock -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add_stock">
                                <input type="number" name="additional_quantity" placeholder="Add Quantity" min="1" required>
                                <button type="submit" class="btn btn-warning btn-sm">Add Stock</button>
                            </form>
                            <!-- Delete Product -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="delete_product">
                                <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            <!-- Update Image -->
                            <form method="POST" enctype="multipart/form-data" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="update_image">
                                <input type="file" name="image" accept="image/*" required>
                                <button type="submit" class="btn btn-info btn-sm">Update Image</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
