<?php
require 'connection.php';
session_start();

// Ensure the user is logged in as a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id']; // Get the seller's user ID from the session

// Fetch the shop details linked to the seller
$query_shop = "SELECT id, shop_name FROM shops WHERE seller_id = ?";
$stmt_shop = $conn->prepare($query_shop);
$stmt_shop->bind_param("i", $seller_id);
$stmt_shop->execute();
$result_shop = $stmt_shop->get_result();
$shop = $result_shop->fetch_assoc();

if (!$shop) {
    die("You must register your shop first!");
}

$shop_id = $shop['id']; // Get the shop ID
$shop_name = $shop['shop_name']; // Get the shop name

// Fetch seller's full name
$query_seller = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt_seller = $conn->prepare($query_seller);
$stmt_seller->bind_param("i", $seller_id);
$stmt_seller->execute();
$result_seller = $stmt_seller->get_result();
$seller = $result_seller->fetch_assoc();

if (!$seller) {
    die("Seller information not found!");
}

$seller_name = $seller['first_name'] . " " . $seller['last_name']; // Get seller name

// Handle the product upload form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $eco_rating = trim($_POST['eco_rating']);
    $quantity = trim($_POST['quantity']);
    $error_message = $success_message = "";

    // Validate inputs
    if (empty($name) || empty($price) || empty($eco_rating) || empty($quantity)) {
        $error_message = "All fields are required!";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error_message = "Price must be a positive number!";
    } elseif (!is_numeric($eco_rating) || $eco_rating < 1 || $eco_rating > 5) {
        $error_message = "Eco Rating must be between 1 and 5!";
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $error_message = "Quantity must be a positive number!";
    } else {
        // Handle image upload
        $target_dir = "uploads/"; // Directory to store uploaded images
        $image_file_name = uniqid() . "_" . basename($_FILES["image"]["name"]); // Unique file name
        $target_file = $target_dir . $image_file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed!";
        } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $error_message = "Error uploading the image!";
        } else {
            // Insert product into the database
            $query = "INSERT INTO products (shop_id, seller_name, shop_name, name, description, price, eco_rating, quantity, image, status, seller_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssdissi", $shop_id, $seller_name, $shop_name, $name, $description, $price, $eco_rating, $quantity, $target_file, $seller_id);

            if ($stmt->execute()) {
                $success_message = "Product uploaded successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #024334;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 1rem;
        }

        .sidebar a:hover {
            background-color: #036852;
        }

        .main-content {
            margin-left: 270px;
            padding: 0px;
            flex: 1;
            background: url('background.jpg') no-repeat center center/cover;
        }

        .carousel-inner img {
            height: 400px;
            object-fit: cover;
        }

        .carousel-caption h5 {
            font-size: 24px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }

        .carousel-caption p {
            font-size: 16px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        form input, form textarea, form button {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        form button:hover {
            background-color: #218838;
        }

        .alert {
            text-align: center;
            margin-bottom: 15px;
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
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="Upload Your Products.jpg" class="d-block w-100" alt="Slider Image 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Upload Your Products</h5>
                        <p>Grow your shop by adding new items regularly!</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="Track Your Sales.jpg" class="d-block w-100" alt="Slider Image 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Track Your Sales</h5>
                        <p>Monitor the performance of your shop efficiently.</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <h2>Upload Product</h2>
        <p class="text-center">Logged in as: <strong><?php echo htmlspecialchars($seller_name); ?></strong> (Shop: <strong><?php echo htmlspecialchars($shop_name); ?></strong>)</p>

        <div class="form-container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Product Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                <textarea name="description" placeholder="Description"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                <input type="text" name="price" placeholder="Price" value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>" required>
                <input type="number" name="eco_rating" placeholder="Eco Rating (1-5)" min="1" max="5" value="<?php echo isset($eco_rating) ? htmlspecialchars($eco_rating) : ''; ?>" required>
                <input type="number" name="quantity" placeholder="Quantity" min="1" value="<?php echo isset($quantity) ? htmlspecialchars($quantity) : ''; ?>" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Upload</button>
            </form>
        </div>
    </div>
</body>
</html>
