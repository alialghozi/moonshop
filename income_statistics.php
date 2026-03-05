<?php
session_start();
require 'connection.php';

// Ensure the user is logged in as an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location:login.php?form=login");
    exit();
}

// Query to get detailed income statistics, excluding canceled orders
$query = "
    SELECT 
        u.first_name, 
        u.last_name, 
        p.name AS product_name, 
        oi.quantity, 
        oi.total_price, 
        o.created_at AS sale_date, 
        o.status, 
        ROUND(oi.total_price * 0.05, 2) AS commission,
        ROUND(oi.total_price * 0.05, 2) AS income
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON p.seller_id = u.id
    WHERE o.status != 'Cancelled'
    ORDER BY o.created_at DESC
";

$result = $conn->query($query);

if (!$result) {
    die("Error fetching income statistics: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Statistics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            height: 100vh;
            padding: 20px;
            position: sticky;
            top: 0;
        }

        .sidebar h2 {
            margin: 0 0 20px;
            color: #f9f9f9;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            margin-bottom: 10px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 16px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 1rem;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .currency {
            text-align: right;
        }

        .no-data {
            text-align: center;
            font-size: 1.2em;
            font-style: italic;
            color: #555;
        }

        .logout {
            text-align: center;
            margin-top: 20px;
        }

        .logout a {
            font-size: 1.1em;
            color: #007BFF;
            text-decoration: none;
        }

        .logout a:hover {
            text-decoration: underline;
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

    <!-- Content -->
    <div class="content">
        <h1>Income Statistics</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Seller Name</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Commission (5%)</th>
                        <th>Income</th>
                        <th>Date of Sale</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td class="currency">$<?php echo number_format($row['total_price'], 2); ?></td>
                            <td class="currency">$<?php echo number_format($row['commission'], 2); ?></td>
                            <td class="currency">$<?php echo number_format($row['income'], 2); ?></td>
                            <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($row['sale_date']))); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['status'] === 'Completed' ? 'Completed' : 'Pending'); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No income statistics available.</p>
        <?php endif; ?>

        <!-- Logout Link -->
        <div class="logout">
            <a href="owner_dashboard.php">Back</a>
        </div>
    </div>

    <?php
    // Close the connection
    $conn->close();
    ?>

</body>
</html>
