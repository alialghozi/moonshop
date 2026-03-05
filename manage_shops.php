<?php
session_start();
require 'connection.php';

// Restrict access to the owner
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$message = "";
$message_class = "";
$edit_user = null;

// Handle actions: Remove or Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'remove') {
            // Remove shop and user
            $query = "DELETE FROM shops WHERE seller_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $message = "Shop and seller removed successfully!";
                $message_class = "success";
            } else {
                $message = "Error removing shop: " . $stmt->error;
                $message_class = "error";
            }
        } elseif ($_POST['action'] === 'edit') {
            // Fetch user and shop details for edit
            $query = "
                SELECT u.id, u.first_name, u.last_name, u.email, u.address, u.phone_number, 
                       s.shop_name, s.shop_description, s.shop_location 
                FROM users u 
                LEFT JOIN shops s ON u.id = s.seller_id 
                WHERE u.id = ?
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $edit_user = $stmt->get_result()->fetch_assoc();
        }
    } elseif (isset($_POST['update_user'])) {
        // Update shop and user details
        $user_id = intval($_POST['user_id']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $phone_number = trim($_POST['phone_number']);
        $shop_name = trim($_POST['shop_name']);
        $shop_description = trim($_POST['shop_description']);
        $shop_location = trim($_POST['shop_location']);

        // Update user details
        $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, phone_number = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $address, $phone_number, $user_id);
        if ($stmt->execute()) {
            // Update shop details
            $query = "UPDATE shops SET shop_name = ?, shop_description = ?, shop_location = ? WHERE seller_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $shop_name, $shop_description, $shop_location, $user_id);
            $stmt->execute();

            $message = "Shop and seller updated successfully!";
            $message_class = "success";
        } else {
            $message = "Error updating shop: " . $stmt->error;
            $message_class = "error";
        }
    }
    $stmt->close();
}

// Fetch sellers and their shops if not in edit mode
if (!$edit_user) {
    $query = "
        SELECT u.id, u.first_name, u.last_name, u.email, u.address, u.phone_number, 
               s.shop_name, s.shop_description, s.shop_location 
        FROM users u 
        LEFT JOIN shops s ON u.id = s.seller_id 
        WHERE u.role = 'seller'
    ";
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shops</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
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
            text-align: center;
            padding: 10px;
            margin: 20px auto;
            border-radius: 5px;
            max-width: 600px;
        }

        .message.success {
            background-color: #e7f9e7;
            color: #28a745;
        }

        .message.error {
            background-color: #f9e7e7;
            color: #dc3545;
        }

        .shop, .edit-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 10px auto;
            max-width: 600px;
        }

        .shop h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .shop p {
            margin: 5px 0;
            color: #555;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
        }

        input, textarea, button {
            display: block;
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .remove-btn {
            background-color: #dc3545;
            color: white;
        }

        .remove-btn:hover {
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
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="approve_shops.php">View and Verify Shop Requests</a>
        <a href="manage_shops.php">Add/Remove Shops</a>
        <a href="shop_statistics.php">View Shop Statistics</a>
        <a href="approve_products.php">Verify New Products</a>
        <a href="income_statistics.php">View Income Reports</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <h1>Manage Shops</h1>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($edit_user): ?>
            <!-- Edit Form -->
            <form method="POST" action="" class="edit-form">
                <h3>Edit Shop</h3>
                <input type="hidden" name="user_id" value="<?php echo intval($edit_user['id']); ?>">
                <label>First Name:</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($edit_user['first_name']); ?>" required>
                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($edit_user['last_name']); ?>" required>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                <label>Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($edit_user['address']); ?>" required>
                <label>Phone Number:</label>
                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($edit_user['phone_number']); ?>" required>
                <label>Shop Name:</label>
                <input type="text" name="shop_name" value="<?php echo htmlspecialchars($edit_user['shop_name']); ?>" required>
                <label>Shop Description:</label>
                <textarea name="shop_description" required><?php echo htmlspecialchars($edit_user['shop_description']); ?></textarea>
                <label>Shop Location:</label>
                <input type="text" name="shop_location" value="<?php echo htmlspecialchars($edit_user['shop_location']); ?>" required>
                <button type="submit" name="update_user">Update Shop</button>
            </form>
        <?php else: ?>
            <!-- List of Shops -->
            <?php if ($result->num_rows > 0): ?>
                <?php while ($shop = $result->fetch_assoc()): ?>
                    <div class="shop">
                        <h3><?php echo htmlspecialchars("{$shop['first_name']} {$shop['last_name']}"); ?></h3>
                        <p>Email: <?php echo htmlspecialchars($shop['email']); ?></p>
                        <p>Address: <?php echo htmlspecialchars($shop['address']); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($shop['phone_number']); ?></p>
                        <p>Shop Name: <?php echo htmlspecialchars($shop['shop_name'] ?? 'N/A'); ?></p>
                        <p>Shop Description: <?php echo htmlspecialchars($shop['shop_description'] ?? 'N/A'); ?></p>
                        <p>Shop Location: <?php echo htmlspecialchars($shop['shop_location'] ?? 'N/A'); ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="user_id" value="<?php echo intval($shop['id']); ?>">
                            <button name="action" value="edit">Edit</button>
                            <button name="action" value="remove" class="remove-btn">Remove</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No shops found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
