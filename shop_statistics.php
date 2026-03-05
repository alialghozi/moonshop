<?php
session_start();
require 'connection.php';

// Ensure the user is logged in as an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Fetch the list of sellers
$query = "SELECT id, first_name, last_name, email FROM users WHERE role = 'seller'";
$sellers_result = $conn->query($query);

// Initialize $stmt to null to avoid errors
$stmt = null;
$seller_name = ''; // Initialize seller name variable

// Check if a seller_id is passed in the URL
if (isset($_GET['seller_id'])) {
    $seller_id = $_GET['seller_id'];

    // Fetch seller name for the selected seller
    $seller_query = "SELECT first_name, last_name FROM users WHERE id = ?";
    $stmt_seller = $conn->prepare($seller_query);
    $stmt_seller->bind_param("i", $seller_id);
    $stmt_seller->execute();
    $seller_result = $stmt_seller->get_result();
    $seller_data = $seller_result->fetch_assoc();
    if ($seller_data) {
        $seller_name = $seller_data['first_name'] . ' ' . $seller_data['last_name'];
    }
    $stmt_seller->close();

    // SQL query to get statistics (total quantity sold and total sales for the selected seller, excluding canceled orders)
    $query = "
        SELECT 
            p.name AS product_name, 
            SUM(oi.quantity) AS total_quantity, 
            SUM(oi.total_price) AS total_sales
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE p.seller_id = ? AND o.status != 'Cancelled'
        GROUP BY p.id
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Statistics</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            display: flex;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
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

        td {
            font-size: 1rem;
        }

        .currency {
            text-align: right;
        }

        .view-statistics {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }

        .view-statistics:hover {
            text-decoration: underline;
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
        <h1>All Sellers</h1>

        <!-- Table for displaying sellers -->
        <table>
            <thead>
                <tr>
                    <th>Seller Name</th>
                    <th>Email</th>
                    <th>View Statistics</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $sellers_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><a href="shop_statistics.php?seller_id=<?php echo $row['id']; ?>" class="view-statistics">View Statistics</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Seller Statistics Table -->
        <?php if (isset($result) && $result->num_rows > 0): ?>
            <h2>Statistics for Seller: <?php echo htmlspecialchars($seller_name); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Quantity Sold</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stat = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($stat['total_quantity']); ?></td>
                            <td class="currency">$<?php echo number_format($stat['total_sales'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif (isset($result)): ?>
            <p>No statistics available for this seller. They may not have made any sales yet.</p>
        <?php endif; ?>

        <!-- Logout Link -->
        <div class="logout">
            <a href="owner_dashboard.php">Back</a>
        </div>
    </div>

    <?php
    // Close the statement if it was used
    if ($stmt !== null) {
        $stmt->close();
    }

    // Close the connection
    $conn->close();
    ?>

</body>
</html>
