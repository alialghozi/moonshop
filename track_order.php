<?php
require 'connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

// Fetch cart count dynamically for the logged-in user
$cart_count_query = "SELECT SUM(quantity) AS cart_count FROM cart WHERE buyer_id = ?";
$stmt_cart_count = $conn->prepare($cart_count_query);
$stmt_cart_count->bind_param("i", $buyer_id);
$stmt_cart_count->execute();
$cart_count_result = $stmt_cart_count->get_result()->fetch_assoc();
$cart_count = $cart_count_result['cart_count'] ?? 0; // Default to 0 if no items in cart

// Fetch all orders for the logged-in user
$order_query = "
    SELECT o.id AS order_id, o.total, o.payment_method, o.status, o.created_at
    FROM orders o
    WHERE o.buyer_id = ?
    ORDER BY o.created_at ASC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$orders = [];
$order_sequence = 1; // Start order sequence from 1 for this buyer
while ($order = $orders_result->fetch_assoc()) {
    $order_id = $order['order_id'];
    $order['order_sequence'] = $order_sequence++; // Assign sequential order number for the buyer

    // Fetch products for each order
    $products_query = "
        SELECT p.name, p.image, oi.quantity, oi.price, oi.total_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
    $product_stmt = $conn->prepare($products_query);
    $product_stmt->bind_param("i", $order_id);
    $product_stmt->execute();
    $products_result = $product_stmt->get_result();

    $products = [];
    while ($product = $products_result->fetch_assoc()) {
        $products[] = $product;
    }

    $order['products'] = $products;
    $orders[] = $order;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        .nav {
            background-color: rgba(2, 67, 52, 0.95);
            color: white;
            padding: 10px 0;
        }

        .shopname {
            display: flex;
            align-items: center;
        }

        .shopname img {
            height: 50px;
            margin-right: 10px;
        }

        .shopname h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            font-size: 16px;
            font-weight: 500;
        }

        .nav-right a:hover {
            color: #049674;
            transition: color 0.3s ease-in-out;
        }

        .container {
            margin-top: 50px;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .card-header {
            background-color: #024334;
            color: white;
            padding: 15px;
        }

        .card-header h5 {
            margin: 0;
            font-size: 18px;
        }

        .card-body {
            padding: 15px;
        }

        .badge {
            font-size: 14px;
            padding: 5px 10px;
        }

        .list-group-item {
            background-color: rgba(250, 250, 250, 1);
            border: 1px solid #ddd;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }

        .list-group-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
        }

        footer {
            margin-top: 50px;
            background-color: #024334;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="shopname">
                <img src="logo.png" alt="MoonShop Logo">
                <h2>MoonShop</h2>
            </div>
            <div class="nav-right">
                <a href="home.php">Home</a>
                <a href="index.php">Products</a>
                <a href="track_order.php">Orders</a>
                <a href="contactus.php">Contact Us</a>
                <a href="view_cart.php">Cart (<?php echo $cart_count; ?>)</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <h2 class="mb-4">My Orders</h2>
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            <h5>Order #<?php echo $order['order_sequence']; ?></h5>
                            <small>Placed on: <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></small>
                        </div>
                        <div>
                            <span class="badge 
                                <?php echo ($order['status'] === 'Pending') ? 'bg-warning text-dark' : ''; ?>
                                <?php echo ($order['status'] === 'Processing') ? 'bg-primary' : ''; ?>
                                <?php echo ($order['status'] === 'Completed') ? 'bg-success' : ''; ?>
                                <?php echo ($order['status'] === 'Cancelled') ? 'bg-danger' : ''; ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                        <hr>
                        <h6>Products</h6>
                        <ul class="list-group">
                            <?php foreach ($order['products'] as $product): ?>
                                <li class="list-group-item">
                                    <img src="<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br>Quantity: <?php echo $product['quantity']; ?>
                                        <br>Price: $<?php echo number_format($product['price'], 2); ?>
                                        <br>Subtotal: $<?php echo number_format($product['total_price'], 2); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <!-- Cancel Order Button -->
                        <?php if ($order['status'] === 'Pending'): ?>
                            <form action="cancel_order.php" method="POST" class="mt-3">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" class="btn btn-danger">Cancel Order</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">You have not placed any orders yet.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 MoonShop. All Rights Reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
