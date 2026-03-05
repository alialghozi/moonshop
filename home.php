<?php
require 'connection.php'; // Connect to the database
session_start();

// Fetch the number of distinct items in the cart for the logged-in user or guest
$cart_count = 0;
$logged_in = isset($_SESSION['user_id']);

if ($logged_in) {
    $buyer_id = $_SESSION['user_id'];

    // Query to count distinct products in the cart for logged-in users
    $cart_count_query = "SELECT COUNT(DISTINCT product_id) AS count FROM cart WHERE buyer_id = ?";
    $stmt = $conn->prepare($cart_count_query);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $cart_count_result = $stmt->get_result()->fetch_assoc();
    $cart_count = $cart_count_result['count'] ?? 0;
} else {
    // For guest users, count distinct products in the session cart
    if (isset($_SESSION['cart'])) {
        $cart_count = count($_SESSION['cart']);
    }
}

// Handle logout if the logout button is clicked
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoonShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin: 0;
            background-color: #f7f7f7;
        }

        .shopname {
            display: flex;
            align-items: center;
        }

        .shopname img {
            height: 50px;
            width: auto;
            margin-right: 15px;
        }

        /* Navbar */
        .nav {
            background-color: #024334;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
        }

        .nav h2 {
            font-size: 26px;
            margin: 0;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 16px;
        }

        .nav-right a:hover {
            color: #049674;
            transition: color 0.3s;
        }

        /* Carousel */
        .carousel-inner img {
            height: 700px;
            object-fit: cover;
        }

        .carousel-caption h5 {
            font-size: 32px;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.6);
        }

        .carousel-caption p {
            font-size: 18px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }

        /* About Us Section */
        .aboutus {
            text-align: justify;
            padding: 40px 20px;
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px;
            border-radius: 8px;
        }

        .aboutus h1 {
            color: #024334;
            text-align: center;
            margin-bottom: 20px;
            font-size: 32px;
        }

        .aboutus p {
            color: #555;
            line-height: 1.8;
            font-size: 16px;
        }

        /* Objectives Section */
        .obj-container {
            display: flex;
            justify-content: center;
            gap: 150px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .obj-card {
            background-color: #024334;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 400px;
            height: 400px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .obj-card h3 {
            color: #ffffff;
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .obj-card p {
            color: #ffffff;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        /* Mission and Vision Cards */
        .card-container {
            display: flex;
            justify-content: center;
            gap: 150px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .card {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
            max-width: 480px;
            margin-bottom: 30px;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .card h2 {
            color: #024334;
            padding: 15px;
            font-size: 22px;
            font-weight: 600;
        }

        .card p {
            padding: 0 15px 15px;
            color: #555;
            line-height: 1.6;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background-color: #024334;
            color: white;
            padding: 15px 0;
            text-align: center;
            font-size: 14px;
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
            <a href="index.php">Products</a>
            <a href="track_order.php">Orders</a>
            <a href="register-seller.php">Become a seller</a>
            <a href="view_cart.php">Cart (<?php echo $cart_count; ?>)</a>
            <?php if ($logged_in): ?>
                <a href="?action=logout" class="btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Carousel Section -->
    <section class="hero">
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active" data-bs-interval="7000">
                    <img src="slid.jpg" class="d-block w-100" alt="Slide 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Explore Our Collection</h5>
                        <p>Find the best deals on top products</p>
                    </div>
                </div>
                <div class="carousel-item" data-bs-interval="7000">
                    <img src="slid2.jpg" class="d-block w-100" alt="Slide 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Shop with Confidence</h5>
                        <p>Experience seamless online shopping.</p>
                    </div>
                </div>
                <div class="carousel-item" data-bs-interval="7000">
                    <img src="slid3.jpg" class="d-block w-100" alt="Slide 3">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>Discover New Arrivals</h5>
                        <p>Upgrade your wardrobe and home.</p>
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
    </section>

    <section class="aboutus">
        <h1>ABOUT MOONSHOP</h1>
        <p>The Eco Marketplace is a digital platform committed to fostering sustainable consumption and production. Designed with simplicity and accessibility in mind, it bridges the gap between environmentally conscious sellers and buyers who prioritize eco-friendly lifestyles. Aligned with SDG 12 (Responsible Consumption and Production), this initiative addresses pressing environmental challenges such as resource depletion, pollution, and waste accumulation.</p>
        <div class="obj-container">
            <div class="obj-card">
                <h3>Objective 1</h3>
                <p>To provide a platform that connects eco-friendly product sellers who value sustainable living.</p>
            </div>
            <div class="obj-card">
                <h3>Objective 2</h3>
                <p>To educate customers about sustainable consumption and encourage environmentally responsible behaviors.</p>
            </div>
            <div class="obj-card">
                <h3>Objective 3</h3>
                <p>To reduce environmental impact by promoting the use of green products and supporting recycling initiatives.</p>
            </div>
        </div>
        <div class="card-container">
            <div class="card">
                <img src="https://i.postimg.cc/2ygVLydq/pexels-singkham-178541-1108572.jpg" alt="Eco Mission">
                <h2>MISSION STATEMENT</h2>
                <p>To promote sustainable living by connecting eco-conscious buyers and sellers.</p>
            </div>
            <div class="card">
                <img src="https://i.postimg.cc/VkWchgRv/pexels-cottonbro-3737623.jpg" alt="Eco Vision">
                <h2>VISION STATEMENT</h2>
                <p>To create a global marketplace fostering a culture of sustainability.</p>
            </div>
        </div>
    </section>

    <section class="footer">
        <p>&copy; 2024 MoonShop | All Rights Reserved</p>
    </section>
</body>
</html>
