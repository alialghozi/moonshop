<?php
// Determine which form to show based on the query parameter
$showLogin = isset($_GET['form']) && $_GET['form'] === 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register & Login</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4CAF50, #81C784);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 400px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
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

        .form-container p {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }

        .form-container p a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .form-container p a:hover {
            text-decoration: underline;
        }

        .seller-fields {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$showLogin): ?>
        <!-- Registration Form -->
        <div class="form-container" id="register-container">
            <h2>Register</h2>
            <form method="POST" action="register.php">
                <input type="text" name="fName" placeholder="First Name" required>
                <input type="text" name="lName" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone Number" required pattern="[0-9]+" title="Please enter a valid phone number">
                <input type="text" name="address" placeholder="Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <label>
                    <input type="checkbox" name="is_seller" id="is_seller_checkbox"> Register as a Seller
                </label>

                <!-- Seller-specific fields -->
                <div class="seller-fields" id="seller-fields">
                    <input type="text" name="shop_name" placeholder="Shop Name">
                    <textarea name="shop_description" placeholder="Shop Description"></textarea>
                    <input type="text" name="shop_location" placeholder="Shop Location">
                </div>

                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="?form=login">Login here</a></p>
        </div>
        <?php else: ?>
        <!-- Login Form -->
        <div class="form-container" id="login-container">
            <h2>Login</h2>
            <form method="POST" action="login.php">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="?form=register">Register here</a></p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // JavaScript to toggle seller fields visibility
        document.addEventListener("DOMContentLoaded", function() {
            const sellerCheckbox = document.getElementById("is_seller_checkbox");
            const sellerFields = document.getElementById("seller-fields");

            sellerCheckbox?.addEventListener("change", function() {
                if (sellerCheckbox.checked) {
                    sellerFields.style.display = "block";
                } else {
                    sellerFields.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>
