<?php
require 'connection.php'; // Ensure this file exists and provides a valid $conn object

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session data for a new login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy session data on the server
    session_start(); // Start a fresh session
}

// Redirect if the user is already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'owner':
            header("Location: owner_dashboard.php");
            exit();
        case 'seller':
            header("Location: seller_dashboard.php");
            exit();
        case 'buyer':
            header("Location: ecommerce_db/index.php"); // Explicit path for buyers
            exit();
        default:
            echo "Unrecognized role: " . htmlspecialchars($_SESSION['role']);
            exit();
    }
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate user input
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    if (!$email || empty($password)) {
        $error = "Both fields are required, and email must be valid!";
    } else {
        // Prepare SQL query to fetch user credentials
        $query = "SELECT id, email, password, role, status FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Check if the seller's account is approved
                    if (strtolower($user['role']) === 'seller' && $user['status'] !== 'approved') {
                        $error = "Your account is not yet approved. Please contact support.";
                    } else {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = strtolower($user['role']); // Normalize role to lowercase
                        $_SESSION['email'] = $user['email'];

                        // Redirect based on user role
                        switch ($_SESSION['role']) {
                            case 'owner':
                                header("Location: owner_dashboard.php");
                                exit();
                            case 'seller':
                                header("Location: seller_dashboard.php");
                                exit();
                            case 'buyer':
                                header("Location: index.php"); // Explicit path for buyer
                                exit();
                            default:
                                echo "Unrecognized role in session: " . htmlspecialchars($_SESSION['role']);
                                exit();
                        }
                    }
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "No account found with this email!";
            }

            $stmt->close();
        } else {
            $error = "Database query failed! Please contact support.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background-image: url('background.jpg');
            font-family: Arial, sans-serif;
            background-size: cover;
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
            margin: 20px auto;
            animation: fadeIn 0.5s ease-in-out;
        }

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

        input, button {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
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

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
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
            <a href="register.php">Register</a>
            <a href="contactus.php">Contact Us</a>
        </div>
    </nav>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
    <footer class="footer">
        <p>&copy; 2024 MoonShop</p>
    </footer>
</body>
</html>
