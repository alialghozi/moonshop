<?php
session_start();

// Restrict access to 'owner' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MoonShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            text-align: justify;
        }
        body h3 {
            color: #024334;
            text-align: center;
            padding-top: 20px;
            padding-bottom: 10px;
        }
        .sidebar {
            background-color: #024334;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            width: 250px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #049674;
            transition: background-color 0.3s;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
        .quick-tips h4 {
            color: #024334;
        }
        .quick-tips {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 4px #024334;
            margin-bottom: 20px;
        }
        .image-showcase {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 200px;
        }
        .image-showcase img {
            width: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
        }
        .image-showcase .quote-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            background: #167052;
            padding: 20px 40px;
            border-radius: 8px;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="approve_shops.php">View and Verify Shop Requests</a>
        <a href="manage_shops.php">Add/Remove Shops</a>
        <a href="shop_statistics.php">View Shop Statistics</a>
        <a href="approve_products.php">Verify New Products</a>
        <a href="income_statistics.php">View Income Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h3>Welcome to MoonShop Admin Dashboard!</h3>
        <p>Here, you can manage shops, verify products, and oversee platform performance. Use the navigation links to get started!</p>

        <div class="quick-tips">
            <h4>Quick Tips</h4>
            <ul>
                <li>Review shop requests daily to ensure a seamless user experience.</li>
                <li>Monitor income reports weekly to keep track of platform growth.</li>
                <li>Verify products to maintain the quality and eco-friendly standards.</li>
            </ul>
        </div>

        <div class="image-showcase">
            <img src="https://i.postimg.cc/RhytBXNn/pexels-alena-koval-233944-886521.jpg" alt="Motivational Background">
            <div class="quote-overlay">
                "Building a Greener Future: One Product at a Time"
            </div>
        </div>
    </div>
</body>
</html>
