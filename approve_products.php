<?php
session_start();
require 'connection.php';

// Ensure the user is logged in as an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Fetch products pending approval along with shop name, seller details, image, and quantity
$query = "
    SELECT 
        p.id AS product_id, 
        p.name AS product_name, 
        p.description, 
        p.price, 
        p.quantity, 
        p.image, 
        p.status, 
        u.first_name AS seller_first_name, 
        u.last_name AS seller_last_name, 
        s.shop_name 
    FROM 
        products p
    LEFT JOIN 
        users u ON p.seller_id = u.id
    LEFT JOIN 
        shops s ON p.shop_id = s.id
    WHERE 
        p.status = 'pending'
";
$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Handle messages from process_product_action.php
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : null;
$message_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Products Approval</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #f9f9f9;
            display: flex;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .message {
            margin: 20px auto;
            padding: 15px;
            max-width: 600px;
            text-align: center;
            border-radius: 5px;
            font-size: 1rem;
        }

        .message.success {
            background-color: #e7f9e7;
            color: #28a745;
        }

        .message.error {
            background-color: #f9e7e7;
            color: #dc3545;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 20px;
            text-align: center;
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-card h2 {
            font-size: 1.2rem;
            color: #333;
            margin: 10px 0;
        }

        .product-card p {
            font-size: 0.9rem;
            color: #555;
            margin: 5px 0;
        }

        .product-card .price {
            font-size: 1.1rem;
            font-weight: bold;
            color: #28a745;
        }

        .product-card .quantity {
            font-size: 0.9rem;
            margin: 5px 0;
        }

        .product-card .actions {
            margin-top: 15px;
        }

        .approve-btn, .reject-btn {
            padding: 10px 15px;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin: 5px;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            padding: 20px;
            color: white;
            height: 100vh;
            position: sticky;
            top: 0;
        }

        .sidebar h2 {
            margin-top: 0;
            color: #f9f9f9;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            margin: 5px 0;
        }

        .sidebar a:hover {
            background-color: #495057;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="approve_shops.php">View and Verify Shop Requests</a>
        <a href="manage_shops.php">Add/Remove Shops</a>
        <a href="shop_statistics.php">View Shop Statistics</a>
        <a href="approve_products.php">Verify New Products</a>
        <a href="income_statistics.php">View Income Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Shop Products Approval</h1>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="product-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['image'] ?? 'default-image.jpg'); ?>" alt="Product Image">
                        <h2><?php echo htmlspecialchars($row['product_name']); ?></h2>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="price">$<?php echo number_format($row['price'], 2); ?></p>
                        <p class="quantity">Quantity: <?php echo htmlspecialchars($row['quantity']); ?></p>
                        <p>Seller: <?php echo htmlspecialchars($row['seller_first_name'] ?? 'N/A') . ' ' . htmlspecialchars($row['seller_last_name'] ?? ''); ?></p>
                        <p>Shop: <?php echo htmlspecialchars($row['shop_name'] ?? 'N/A'); ?></p>
                        <div class="actions">
                            <form method="POST" action="process_product_action.php" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                            </form>
                            <form method="POST" action="process_product_action.php" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products pending approval.</p>
            <?php endif; ?>
        </div>

        <?php
        // Close the connection
        $conn->close();
        ?>
    </div>

</body>
</html>
