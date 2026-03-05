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
    <link rel="stylesheet" href="register_login.css">
    <style>
        .seller-fields {
            display: none;
            margin-top: 10px;
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
                <input type="text" name="fName" placeholder="First Name" required><br>
                <input type="text" name="lName" placeholder="Last Name" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="text" name="phone" placeholder="Phone Number" required pattern="[0-9]+" title="Please enter a valid phone number"><br>
                <input type="text" name="address" placeholder="Address" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <label>
                    <input type="checkbox" name="is_seller" id="is_seller_checkbox"> Register as a Seller
                </label><br>
                <div class="seller-fields" id="seller-fields">
                    <input type="text" name="shop_name" placeholder="Shop Name"><br>
                    <textarea name="shop_description" placeholder="Shop Description"></textarea><br>
                    <input type="text" name="shop_location" placeholder="Shop Location"><br>
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
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="?form=register">Register here</a></p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // JavaScript to toggle visibility of seller fields
        document.addEventListener("DOMContentLoaded", function() {
            const sellerCheckbox = document.getElementById("is_seller_checkbox");
            const sellerFields = document.getElementById("seller-fields");

            sellerCheckbox.addEventListener("change", function() {
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
