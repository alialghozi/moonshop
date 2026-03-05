<?php
session_start();
require 'connection.php';

// Restrict access to the owner
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$message = ""; // Store feedback messages
$message_class = ""; // Store the CSS class for the message

// Handle shop approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'], $_POST['action'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];

        try {
            if ($action === 'approve') {
                // Approve seller and shop
                $conn->begin_transaction(); // Begin transaction

                // Approve seller in `users` table
                $user_query = "UPDATE users SET status = 'approved' WHERE id = ?";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_stmt->close();

                // Approve shop in `shops` table
                $shop_query = "UPDATE shops SET status = 'verified' WHERE seller_id = ?";
                $shop_stmt = $conn->prepare($shop_query);
                $shop_stmt->bind_param("i", $user_id);
                $shop_stmt->execute();
                $shop_stmt->close();

                $conn->commit(); // Commit transaction
                $message = "Seller and shop approved successfully!";
                $message_class = "success";
            } elseif ($action === 'reject') {
                // Reject seller and shop
                $conn->begin_transaction(); // Begin transaction

                // Delete shop from `shops` table
                $shop_query = "DELETE FROM shops WHERE seller_id = ?";
                $shop_stmt = $conn->prepare($shop_query);
                $shop_stmt->bind_param("i", $user_id);
                $shop_stmt->execute();
                $shop_stmt->close();

                // Delete seller from `users` table
                $user_query = "DELETE FROM users WHERE id = ?";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_stmt->close();

                $conn->commit(); // Commit transaction
                $message = "Seller and shop rejected successfully!";
                $message_class = "success";
            } else {
                $message = "Invalid action.";
                $message_class = "error";
            }
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on failure
            $message = "Error processing request: " . $e->getMessage();
            $message_class = "error";
        }
    } else {
        $message = "Invalid form submission.";
        $message_class = "error";
    }
}

// Fetch pending sellers and their shop details
$query = "
    SELECT 
        u.id AS user_id,
        u.first_name, 
        u.last_name, 
        u.email, 
        u.address, 
        u.phone_number, 
        s.shop_name, 
        s.shop_description, 
        s.shop_location
    FROM 
        users u
    LEFT JOIN 
        shops s ON u.id = s.seller_id
    WHERE 
        u.role = 'seller' AND u.status = 'pending'
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Shops</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: rgba(2, 67, 52, 0.9);
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
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
        }
        .carousel-inner img {
            height: 400px;
            object-fit: cover;
        }
        .carousel-caption h5 {
            font-size: 24px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
            color:#4CAF50;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .message.success {
            color: green;
            background-color: #e7f9e7;
        }
        .message.error {
            color: red;
            background-color: #f9e7e7;
        }
        .seller {
            background: #fff;
            margin: 10px auto;
            padding: 15px;
            border-radius: 5px;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .seller h3 {
            margin: 0 0 10px;
        }
        .seller p {
            margin: 5px 0;
            color: #555;
        }
        button {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
        }
        button[name="action"][value="approve"] {
            background-color: #4CAF50;
        }
        button[name="action"][value="reject"] {
            background-color: #f44336;
        }
        button:hover {
            opacity: 0.9;
        }
        .no-requests {
            text-align: center;
            color: #777;
            font-size: 16px;
            margin-top: 30px;
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
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="shop_requests.jpg" class="d-block w-100" alt="Slider Image 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Approve Shop Requests</h5>
                        <p>Manage and approve pending shop requests efficiently.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="admin_dashboard.jpg" class="d-block w-100" alt="Slider Image 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Admin Dashboard</h5>
                        <p>Track and manage your platform effectively.</p>
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

        <h1>Pending Shop Requests</h1>
        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($seller = $result->fetch_assoc()): ?>
                <div class="seller">
                    <h3><?php echo htmlspecialchars("{$seller['first_name']} {$seller['last_name']}"); ?></h3>
                    <p>Email: <?php echo htmlspecialchars($seller['email']); ?></p>
                    <p>Address: <?php echo htmlspecialchars($seller['address'] ?: 'N/A'); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($seller['phone_number'] ?: 'N/A'); ?></p>
                    <p>Shop Name: <?php echo htmlspecialchars($seller['shop_name'] ?: 'N/A'); ?></p>
                    <p>Shop Description: <?php echo htmlspecialchars($seller['shop_description'] ?: 'N/A'); ?></p>
                    <p>Shop Location: <?php echo htmlspecialchars($seller['shop_location'] ?: 'N/A'); ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?php echo intval($seller['user_id']); ?>">
                        <button name="action" value="approve">Approve</button>
                        <button name="action" value="reject">Reject</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-requests">No pending shop requests.</div>
        <?php endif; ?>
    </div>
</body>
</html>
