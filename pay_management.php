<?php
require 'connection.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to calculate total sales for checked-out products (excluding canceled orders)
function getTotalSales($conn, $user_id, $filter) {
    try {
        $date_condition = $filter === 'weekly' 
            ? "DATE(o.created_at) >= CURDATE() - INTERVAL 7 DAY" 
            : "DATE(o.created_at) >= CURDATE() - INTERVAL 30 DAY";

        $query = "SELECT SUM(oi.total_price * 0.95) AS total_sales 
                  FROM order_items oi
                  JOIN orders o ON oi.order_id = o.id
                  WHERE o.buyer_id = ? AND o.status != 'Cancelled' AND $date_condition";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_sales'] ?? 0; 
    } catch (mysqli_sql_exception $e) {
        error_log("Database error: " . $e->getMessage());
        return 0; 
    }
}

// Function to fetch transactions for checked-out products (excluding canceled orders)
function getTransactions($conn, $user_id, $filter) {
    try {
        $date_condition = $filter === 'weekly' 
            ? "DATE(o.created_at) >= CURDATE() - INTERVAL 7 DAY" 
            : "DATE(o.created_at) >= CURDATE() - INTERVAL 30 DAY";

        $query = "SELECT p.name AS product_name, oi.quantity, oi.price AS price_per_unit, 
                         (oi.total_price * 0.95) AS net_price, o.payment_method, 
                         DATE_FORMAT(o.created_at, '%Y-%m-%d %H:%i:%s') AS date, u.first_name AS buyer_name 
                  FROM order_items oi
                  JOIN orders o ON oi.order_id = o.id
                  JOIN products p ON oi.product_id = p.id
                  JOIN users u ON o.buyer_id = u.id
                  WHERE o.buyer_id = ? AND o.status != 'Cancelled' AND $date_condition
                  ORDER BY o.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        return $transactions;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error: " . $e->getMessage());
        return []; 
    }
}

// Fetch data based on the selected filter
$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'weekly'; 
$total_sales = getTotalSales($conn, $user_id, $filter);
$transactions = getTransactions($conn, $user_id, $filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #f9f9f9;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #024334;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
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
            padding-right: 0px;
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #024334;
            color: white;
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
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form label {
            font-weight: bold;
        }
        .filter-form select, .filter-form button {
            padding: 5px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
        }
        .filter-form button {
            background-color: #024334;
            color: white;
        }
        .filter-form button:hover {
            background-color: #04664a;
            cursor: pointer;
        }
        .total-sales {
            font-size: 18px;
            font-weight: bold;
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
                    <img src="Track Your Income.jpg" class="d-block w-100" alt="Slider Image 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Track Your Income</h5>
                        <p>Monitor your earnings effectively.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="Analyze Your Sales.jpg" class="d-block w-100" alt="Slider Image 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Analyze Your Sales</h5>
                        <p>Stay updated with transaction history.</p>
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

        <!-- Filter Form -->
        <form method="GET" action="" class="filter-form">
            <label for="filter">Filter Transactions:</label>
            <select name="filter" id="filter">
                <option value="weekly" <?= $filter === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                <option value="monthly" <?= $filter === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            </select>
            <button type="submit">Apply</button>
        </form>

        <!-- Total Sales Section -->
        <h2>Total Sales</h2>
        <p class="total-sales">Total Sales After Tax (Owner's Income): $<?= htmlspecialchars(number_format($total_sales, 2)) ?></p>

        <!-- Transactions Table -->
        <h2>Transaction History (<?= ucfirst($filter) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price Per Unit</th>
                    <th>Net Price (After 5% Tax)</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                    <th>Buyer Name</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No transactions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                            <td>$<?= htmlspecialchars(number_format($transaction['price_per_unit'], 2)) ?></td>
                            <td>$<?= htmlspecialchars(number_format($transaction['net_price'], 2)) ?></td>
                            <td><?= htmlspecialchars($transaction['payment_method']) ?></td>
                            <td><?= htmlspecialchars($transaction['date']) ?></td>
                            <td><?= htmlspecialchars($transaction['buyer_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
