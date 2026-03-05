<?php
session_start();
require 'connection.php';

// Ensure the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders related to the seller's products
$query_orders = "
    SELECT o.id AS order_id, o.buyer_id, o.total, o.payment_method, o.status, o.created_at, 
           u.first_name AS buyer_first_name, u.last_name AS buyer_last_name, u.email AS buyer_email
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.buyer_id = u.id
    WHERE p.seller_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC";
$stmt_orders = $conn->prepare($query_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders_result = $stmt_orders->get_result();

$orders = [];
$order_sequence = 1; // Start order sequence from 1 for this seller
while ($order = $orders_result->fetch_assoc()) {
    $order_id = $order['order_id'];

    // Fetch products for each order
    $products_query = "
        SELECT p.name, oi.quantity, oi.price, oi.total_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
    $stmt_products = $conn->prepare($products_query);
    $stmt_products->bind_param("i", $order_id);
    $stmt_products->execute();
    $products_result = $stmt_products->get_result();

    $products = [];
    while ($product = $products_result->fetch_assoc()) {
        $products[] = $product;
    }

    $stmt_products->close(); // Close after usage
    $order['products'] = $products;
    $order['order_sequence'] = $order_sequence++; // Assign a unique sequence for the seller
    $orders[] = $order;
}

$stmt_orders->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .sidebar {
            width: 250px;
            background-color: rgba(2, 67, 52, 0.95);
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

        .card {
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .card-header {
            background-color: #024334;
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .form-select {
            margin-right: 10px;
        }

        .list-group-item {
            background-color: rgba(250, 250, 250, 0.9);
        }

        h2 {
            color: #024334;
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
        <!-- Carousel -->
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="Track Your Orders.jpg" class="d-block w-100" alt="Slider Image 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Track Your Orders</h5>
                        <p>Stay updated with all customer purchases.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="Grow Your Sales.jpg" class="d-block w-100" alt="Slider Image 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Grow Your Sales</h5>
                        <p>Manage your shop effectively to increase your revenue.</p>
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

        <div class="container mt-5">
            <h2>View Orders</h2>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Order #<?php echo $order['order_sequence']; ?></h5>
                            <small>Placed on: <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></small>
                        </div>
                        <div class="card-body">
                            <p><strong>Buyer:</strong> <?php echo htmlspecialchars($order['buyer_first_name'] . ' ' . $order['buyer_last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                            <p><strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            <hr>
                            <h6>Products</h6>
                            <ul class="list-group">
                                <?php foreach ($order['products'] as $product): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br>
                                        Quantity: <?php echo $product['quantity']; ?>
                                        <br>
                                        Price: $<?php echo number_format($product['price'], 2); ?>
                                        <br>
                                        Subtotal: $<?php echo number_format($product['total_price'], 2); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <form action="update_order_status.php" method="POST" class="d-inline">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="status" class="form-select w-auto d-inline">
                                    <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Processing" <?php echo $order['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Completed" <?php echo $order['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No orders available for your products.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
