<?php
require 'connection.php';
session_start();

// Check if the user is logged in
$buyer_id = $_SESSION['user_id'] ?? null;

// Handle form submissions for update or remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update' && isset($_POST['cart_id'], $_POST['quantity'])) {
            $cart_id = $_POST['cart_id'];
            $quantity = max(1, intval($_POST['quantity']));

            if ($buyer_id) {
                // Update cart in the database
                $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $quantity, $cart_id);
                $stmt->execute();
            } else {
                // Update cart in session
                if (isset($_SESSION['cart'][$cart_id])) {
                    $_SESSION['cart'][$cart_id]['quantity'] = $quantity;
                }
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['cart_id'])) {
            $cart_id = $_POST['cart_id'];

            if ($buyer_id) {
                // Remove from database cart
                $delete_query = "DELETE FROM cart WHERE id = ?";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param("i", $cart_id);
                $stmt->execute();
            } else {
                // Remove from session cart
                unset($_SESSION['cart'][$cart_id]);
            }
        }
    }
}

// Fetch cart items
if ($buyer_id) {
    // Fetch cart from the database for logged-in users
    $cart_query = "SELECT c.id AS cart_id, c.quantity, p.name, p.price, p.image 
                   FROM cart c
                   JOIN products p ON c.product_id = p.id
                   WHERE c.buyer_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    $cart_items = $cart_result->fetch_all(MYSQLI_ASSOC);
    $cart_count = count($cart_items); // Count distinct products in the database cart
} else {
    // Fetch cart from session for guest users
    $cart_items = [];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cart_id => $item) {
            $product_query = "SELECT id, name, price, image FROM products WHERE id = ?";
            $stmt = $conn->prepare($product_query);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $product_result = $stmt->get_result()->fetch_assoc();

            if ($product_result) {
                $cart_items[] = [
                    'cart_id' => $cart_id,
                    'quantity' => $item['quantity'],
                    'name' => $product_result['name'],
                    'price' => $product_result['price'],
                    'image' => $product_result['image'],
                ];
            }
        }
    }
    $cart_count = count($cart_items); // Count distinct products in the session cart
}

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - MoonShop</title>
    <style>
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin: 0;
            background-color: #f7f7f7;
        }

        .shopname {
            display: flex;
            align-items: center;
        }

        .shopname img {
            height: 80px;
            width: auto;
            margin-right: 15px;
        }

        .nav {
            background-color: #024334;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
        }

        .shopname h2 {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        }

        .nav-right a:hover {
            color: #049674;
            transition: color 0.3s;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
            color: #333;
        }

        table img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .btn-update {
            background-color: #007bff;
            color: #fff;
        }

        .btn-update:hover {
            background-color: #0056b3;
        }

        .btn-remove {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        .btn-checkout {
            background-color: #28a745;
            color: #fff;
        }

        .btn-checkout:hover {
            background-color: #218838;
        }

        .total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .empty-message {
            text-align: center;
            font-size: 1.2em;
            color: #777;
        }

        .footer {
            background-color: #024334;
            color: white;
            text-align: center;
            padding: 15px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav">
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
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Your Cart</h1>
        <?php if (!empty($cart_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $cart_item): ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($cart_item['image']); ?>" alt="Product Image">
                                <p><?php echo htmlspecialchars($cart_item['name']); ?></p>
                            </td>
                            <td>$<?php echo number_format($cart_item['price'], 2); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_item['cart_id']); ?>">
                                    <input type="number" name="quantity" value="<?php echo htmlspecialchars($cart_item['quantity']); ?>" min="1" style="width: 60px;">
                                    <button class="btn btn-update" type="submit">Update</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($cart_item['price'] * $cart_item['quantity'], 2); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_item['cart_id']); ?>">
                                    <button class="btn btn-remove" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">Total: $<?php echo number_format($total_price, 2); ?></div>
            <div class="action-buttons">
                <a href="checkout.php"><button class="btn btn-checkout">Proceed to Checkout</button></a>
            </div>
        <?php else: ?>
            <p class="empty-message">Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 MoonShop</p>
    </footer>
</body>
</html>
