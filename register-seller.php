<?php
include 'connection.php'; // Include the database connection

// Initialize variables to avoid undefined variable warnings
$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['fName']);
    $last_name = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash password

    $shop_name = trim($_POST['shop_name']);
    $shop_description = trim($_POST['shop_description']);
    $shop_location = trim($_POST['shop_location']);

    try {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "This email is already registered.";
            $stmt->close();
        } else {
            $stmt->close();

            // Insert into `users` table
            $role = 'seller';
            $status = 'pending';
            $query = "INSERT INTO users (first_name, last_name, email, phone_number, address, password, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone, $address, $password, $status, $role);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert seller-specific data into `shops` table
                $shop_query = "INSERT INTO shops (seller_id, shop_name, shop_description, shop_location, status) VALUES (?, ?, ?, ?, 'pending')";
                $shop_stmt = $conn->prepare($shop_query);
                $shop_stmt->bind_param("isss", $user_id, $shop_name, $shop_description, $shop_location);

                if (!$shop_stmt->execute()) {
                    $error = "Error registering shop: " . $shop_stmt->error;
                    $shop_stmt->close();
                } else {
                    $success = "Registration successful! Your seller account is pending approval.";
                    $shop_stmt->close();
                }
                $stmt->close();
            } else {
                $error = "Error registering user: " . $stmt->error;
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        $error = "Unexpected error: " . $e->getMessage();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration</title>
    <style>
        /* General Styles */
        body {
      background-image: url('background.jpg');
      font-family: Arial, sans-serif;
      background-size:cover;
      background-size: 100vw 100vh;
      background-repeat: no-repeat;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

        .container {
    width: 400px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin: 20px 0; /* Removes vertical centering */
    margin-left: 150px; /* Moves the container to the left */
    animation: fadeIn 0.5s ease-in-out;}

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .shopname {
            display: flex;
            align-items: center;
        }

        .shopname img {
            height: 50px;
            width: auto;
            margin-right: 10px;
        }

        .nav {
            background-color: #024334;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .shopname h2 {
            font-size: 20px;
            margin: 0;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            font-size: 14px;
        }

        .nav-right a:hover {
            color: #049674;
            transition: color 0.3s;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input, textarea, button {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
        }

        input:focus, textarea:focus {
            border-color: #4CAF50;
        }

        button {
            background: #4CAF50;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background: #45a049;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        .error, .success {
            text-align: center;
            margin: 10px 0;
            font-size: 14px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        textarea {
            resize: none;
            height: 40px;
        }

        p {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }

        p a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        p a:hover {
            text-decoration: underline;
        }

        footer.footer {
            background: #024334;
            color: white;
            text-align: center;
            padding: 0px;
            margin-top: auto;
            width: 100%;
        }
    </style>
</head>
<body>
<nav class="nav">
    <div class="shopname">
        <img src="logo.png" alt="MoonShop Logo">
        <h2>MoonShop</h2>
    </div>
    <div class="nav-right">
        <a href="home.php">Home</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="contactus.php">Contact Us</a>
    </div>
</nav>
<div class="container">
    <h2>Register as a Seller</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="fName" placeholder="First Name" required>
        <input type="text" name="lName" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required pattern="[0-9]+" title="Enter a valid phone number">
        <input type="text" name="address" placeholder="Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="shop_name" placeholder="Shop Name" required>
        <textarea name="shop_description" placeholder="Shop Description" required></textarea>
        <input type="text" name="shop_location" placeholder="Shop Location" required>
        <button type="submit">Register</button>
    </form>
    <p>Registering as a buyer? <a href="register.php">Click here</a></p>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
<footer class="footer">
    <p>&copy; 2024 MoonShop</p>
</footer>
</body>
</html>
