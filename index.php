<?php
require 'connection.php';
session_start();

// Fetch all approved products with non-zero quantity
$product_query = "SELECT p.id, p.name, p.description, p.price, p.image, p.quantity 
                  FROM products p
                  WHERE p.status = 'approved' AND p.quantity > 0";
$product_result = $conn->query($product_query);

if (!$product_result) {
    die("Error fetching products: " . $conn->error);
}

// Check if the user is logged in
$buyer_id = $_SESSION['user_id'] ?? null;

// Fetch cart count for logged-in or guest users
if ($buyer_id) {
    // If logged in, fetch cart count from the database
    $cart_count_query = "SELECT COUNT(*) AS count FROM cart WHERE buyer_id = ?";
    $stmt = $conn->prepare($cart_count_query);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $cart_count_result = $stmt->get_result()->fetch_assoc();
    $cart_count = $cart_count_result['count'] ?? 0;
} else {
    // If not logged in, fetch cart count from session
    $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; // Count distinct products in the session cart
}

// Handle "Add to Cart" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $product_id = intval($_POST['product_id']);
    $quantity = 1; // Default to 1 for simplicity

    if ($buyer_id) {
        // Add product to cart in the database for logged-in users
        $check_cart_query = "SELECT id FROM cart WHERE buyer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($check_cart_query);
        $stmt->bind_param("ii", $buyer_id, $product_id);
        $stmt->execute();
        $cart_item = $stmt->get_result()->fetch_assoc();

        if ($cart_item) {
            // Update the quantity if the product is already in the cart
            $update_cart_query = "UPDATE cart SET quantity = quantity + ? WHERE id = ?";
            $stmt = $conn->prepare($update_cart_query);
            $stmt->bind_param("ii", $quantity, $cart_item['id']);
        } else {
            // Insert the product into the cart
            $insert_cart_query = "INSERT INTO cart (buyer_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_cart_query);
            $stmt->bind_param("iii", $buyer_id, $product_id, $quantity);
        }
        $stmt->execute();
    } else {
        // Add product to cart in session for guest users
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if the product is already in the cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            // Add new product to the cart
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'quantity' => $quantity,
            ];
        }
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoonShop - Products</title>
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
            height: 80px;
            width: auto;
            margin-right: 15px;
        }

        .nav {
            background-color: #024334;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
        }

        .shopname h2 {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        }

        .nav-right a:hover {
            color: #049674;
            transition: color 0.3s;
        }

        .products-section {
            padding: 40px;
            background-color: #fff;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .products-section h1 {
            color: #024334;
            text-align: center;
            margin-bottom: 20px;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-card img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .product-card h5 {
            font-size: 18px;
            margin: 10px 0;
            color: #024334;
        }

        .product-card p {
            margin: 5px 0;
        }

        .product-card .price {
            color: #049674;
            font-weight: bold;
        }

        .footer {
            background-color: #024334;
            color: white;
            text-align: center;
            padding: 15px 0;
        }

        button {
            background-color: #049674;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #036852;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav">
        <div class="shopname">
            <img src="logo.png" alt="MoonShop Logo">
            <h2>MoonShop</h2>
        </div>
        <div class="nav-right">
            <a href="home.php">Home</a>
            <a href="index.php">Products</a>
            <a href="track_order.php">Orders</a>
            <a href="contactus.php">Contact Us</a>
            <a href="view_cart.php">Cart (<?php echo $cart_count; ?>)</a>
        </div>
    </nav>

    <!-- Products Section -->
    <section class="products-section">
        <h1>Our Products</h1>
        <div class="products">
            <?php if ($product_result->num_rows === 0): ?>
                <p>No products available at the moment.</p>
            <?php else: ?>
                <?php while ($product = $product_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='placeholder.jpg';">
                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
                        <p><strong>Quantity Available: <?php echo htmlspecialchars($product['quantity']); ?></strong></p>
                        <?php if ($product['quantity'] > 0): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <button type="submit">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <p class="out-of-stock">Out of Stock</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 MoonShop</p>
    </footer>
</body>
</html>
