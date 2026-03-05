<?php
require 'connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

// Fetch cart items
$cart_query = "SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.quantity AS stock 
               FROM cart c
               JOIN products p ON c.product_id = p.id
               WHERE c.buyer_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$subtotal = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    // Check if stock matches or exceeds the cart quantity
    if ($item['stock'] < $item['quantity']) {
        $error_message = "Insufficient stock for product: " . htmlspecialchars($item['name']);
        $insufficient_stock = true;
        break;
    }
    $subtotal += $item['price'] * $item['quantity'];
    $cart_items[] = $item;
}

$shipping = 0; // Free shipping
$total = $subtotal + $shipping;

// Place order logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postcode = $_POST['postcode'];
    $payment_method = $_POST['payment_method'];

    $conn->begin_transaction();

    try {
        // Insert order into `orders` table
        $order_query = "INSERT INTO orders (buyer_id, first_name, last_name, email, phone, address, city, postcode, payment_method, total, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("issssssssi", $buyer_id, $first_name, $last_name, $email, $phone, $address, $city, $postcode, $payment_method, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        $tax = 0.05; // 5% tax
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $total_price = $price * $quantity;
            $tax_amount = $total_price * $tax;
            $income = $total_price - $tax_amount;

            $order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, total_price, tax_amount) 
                                 VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($order_item_query);
            $stmt->bind_param("iiiddd", $order_id, $product_id, $quantity, $price, $total_price, $tax_amount);
            $stmt->execute();

            $income_query = "INSERT INTO income (order_id, product_id, total_price, tax_amount, income) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($income_query);
            $stmt->bind_param("iiddi", $order_id, $product_id, $total_price, $tax_amount, $income);
            $stmt->execute();

            $stock_update_query = "UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?";
            $stock_stmt = $conn->prepare($stock_update_query);
            $stock_stmt->bind_param("iii", $quantity, $product_id, $quantity);
            $stock_stmt->execute();

            if ($stock_stmt->affected_rows == 0) {
                throw new Exception("Insufficient stock for product: " . htmlspecialchars($item['name']));
            }
        }

        $clear_cart_query = "DELETE FROM cart WHERE buyer_id = ?";
        $stmt = $conn->prepare($clear_cart_query);
        $stmt->bind_param("i", $buyer_id);
        $stmt->execute();

        $conn->commit();

        header("Location: track_order.php?status=success");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error processing order: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoonShop - Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Body Styling */
        body {
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        /* Navigation Bar Styling */
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

        /* Container and Form Styling */
        .container {
            margin-top: 30px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background-color: #049674;
            border: none;
        }

        .btn-success:hover {
            background-color: #036852;
        }

        /* Table Styling */
        .table {
            background-color: white;
            margin-top: 15px;
        }

        /* Footer Styling */
        footer {
            background-color: rgba(2, 67, 52, 0.95);
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
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
                <a href="view_cart.php">Cart (<?php echo count($cart_items); ?>)</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <div class="row">
            <!-- Billing Details -->
            <div class="col-md-6">
                <h4>Billing Details</h4>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name*</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name*</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone*</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address*</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City*</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="postcode" class="form-label">Postcode*</label>
                        <input type="text" id="postcode" name="postcode" class="form-control" required>
                    </div>
                    <h5>Payment Methods</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                        <label class="form-check-label" for="bank_transfer">Bank Transfer</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cash_on_delivery">
                        <label class="form-check-label" for="cash_on_delivery">Cash on Delivery</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card">
                        <label class="form-check-label" for="credit_card">Credit Card</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-4">Place Order</button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-md-6">
                <h4>Order Summary</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td>Subtotal</td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Shipping</td>
                            <td>Free</td>
                        </tr>
                        <tr class="table-success">
                            <td>Total</td>
                            <td>$<?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 MoonShop. All Rights Reserved.</p>
    </footer>
</body>
</html>
