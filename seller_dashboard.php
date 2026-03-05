<?php
session_start();
require 'connection.php';

// Ensure the user is logged in and has the role 'seller'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch seller's shop details
$query = "SELECT id, shop_name, shop_description, status FROM shops WHERE seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$shop_result = $stmt->get_result();
$shop = $shop_result->fetch_assoc();

// Fetch the number of pending orders
$query_orders = "
    SELECT COUNT(*) AS pending_orders 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id 
    WHERE p.seller_id = ? AND o.status = 'Pending'";
$stmt_orders = $conn->prepare($query_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$order_result = $stmt_orders->get_result();
$order_data = $order_result->fetch_assoc();

// Fetch the number of products uploaded
$query_products = "SELECT COUNT(*) AS total_products FROM products WHERE seller_id = ?";
$stmt_products = $conn->prepare($query_products);
$stmt_products->bind_param("i", $user_id);
$stmt_products->execute();
$product_result = $stmt_products->get_result();
$product_data = $product_result->fetch_assoc();

$stmt->close();
$stmt_orders->close();
$stmt_products->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #024334;
            padding-top: 20px;
            color: white;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #036852;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .dashboard-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card h3 {
            font-size: 1.4rem;
            color: #007BFF;
        }

        .button {
            margin-top: 15px;
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #0056b3;
        }

        /* Carousel Styling */
        .carousel-inner img {
            height: 400px;
            object-fit: cover;
        }

        .carousel-caption h5 {
            font-size: 32px;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.6);
        }

        .carousel-caption p {
            font-size: 18px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Seller Dashboard</h2>
        <a href="upload_product.php">Upload Product</a>
        <a href="view_order.php">View Orders</a>
        <a href="update_product_status.php">Update Product Status</a>
        <a href="pay_management.php">Pay Management</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Welcome to Your Seller Dashboard</h1>

        <!-- Carousel -->
        <div id="dashboardCarousel" class="carousel slide mt-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="slide1.jpg" class="d-block w-100" alt="Slide 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Manage Your Shop</h5>
                        <p>Upload products and track your performance in real-time.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="slide2.jpg" class="d-block w-100" alt="Slide 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Analyze Your Sales</h5>
                        <p>Get detailed insights about your shop's revenue.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="slide3.jpg" class="d-block w-100" alt="Slide 3">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Track Orders</h5>
                        <p>Stay updated with the status of your orders.</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <div class="dashboard-container">
            <!-- Shop Information -->
            <?php if ($shop): ?>
                <div class="dashboard-card">
                    <h3>Shop Information</h3>
                    <p><strong>Shop Name:</strong> <?php echo htmlspecialchars($shop['shop_name']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($shop['shop_description']); ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($shop['status'])); ?></p>
                </div>
            <?php else: ?>
                <div class="dashboard-card">
                    <h3>Register Your Shop</h3>
                    <p>Your shop is not registered yet. Please register your shop.</p>
                    <a href="register_shop.php" class="button">Register Shop</a>
                </div>
            <?php endif; ?>

            <!-- Dashboard Statistics -->
            <div class="dashboard-card">
                <h3>Dashboard Overview</h3>
                <p><strong>Pending Orders:</strong> <?php echo $order_data['pending_orders'] ?? 0; ?></p>
                <p><strong>Products Uploaded:</strong> <?php echo $product_data['total_products'] ?? 0; ?></p>
            </div>
        </div>
    </div>

</body>
</html>
