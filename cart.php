<?php
require 'connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?status=error&message=" . urlencode("You must be logged in to manage your cart."));
    exit();
}

$buyer_id = $_SESSION['user_id'];

// Get the action parameter
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addToCart($conn, $buyer_id);
        break;
    case 'update':
        updateCart($conn, $buyer_id);
        break;
    case 'delete':
        deleteFromCart($conn, $buyer_id);
        break;
    default:
        header("Location: index.php?status=error&message=" . urlencode("Invalid action specified."));
        exit();
}

// Function to add a product to the cart
function addToCart($conn, $buyer_id) {
    $product_id = $_POST['product_id'] ?? null;

    if (!$product_id || !is_numeric($product_id)) {
        header("Location: index.php?status=error&message=" . urlencode("Invalid product ID."));
        exit();
    }

    try {
        // Check if the product already exists in the cart
        $check_query = "SELECT id, quantity FROM cart WHERE buyer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $buyer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update the quantity if the product already exists
            $cart_item = $result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + 1;
            $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
            $stmt->execute();
            header("Location: index.php?status=success&message=" . urlencode("Product quantity updated in cart."));
        } else {
            // Insert a new product into the cart
            $quantity = 1;
            $insert_query = "INSERT INTO cart (buyer_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iii", $buyer_id, $product_id, $quantity);
            $stmt->execute();
            header("Location: index.php?status=success&message=" . urlencode("Product added to cart."));
        }
    } catch (Exception $e) {
        header("Location: index.php?status=error&message=" . urlencode("Error adding product to cart: " . $e->getMessage()));
    }
}

// Function to update the quantity of a product in the cart
function updateCart($conn, $buyer_id) {
    $cart_id = $_POST['cart_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if (!$cart_id || !is_numeric($cart_id) || !$quantity || !is_numeric($quantity) || $quantity < 1) {
        header("Location: index.php?status=error&message=" . urlencode("Invalid cart ID or quantity."));
        exit();
    }

    try {
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ? AND buyer_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("iii", $quantity, $cart_id, $buyer_id);
        if ($stmt->execute()) {
            header("Location: index.php?status=success&message=" . urlencode("Cart item updated successfully."));
        } else {
            header("Location: index.php?status=error&message=" . urlencode("Failed to update cart item."));
        }
    } catch (Exception $e) {
        header("Location: index.php?status=error&message=" . urlencode("Error updating cart: " . $e->getMessage()));
    }
}

// Function to delete a product from the cart
function deleteFromCart($conn, $buyer_id) {
    $cart_id = $_POST['cart_id'] ?? null;

    if (!$cart_id || !is_numeric($cart_id)) {
        header("Location: index.php?status=error&message=" . urlencode("Invalid cart ID."));
        exit();
    }

    try {
        $delete_query = "DELETE FROM cart WHERE id = ? AND buyer_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $cart_id, $buyer_id);
        if ($stmt->execute()) {
            header("Location: index.php?status=success&message=" . urlencode("Cart item deleted successfully."));
        } else {
            header("Location: index.php?status=error&message=" . urlencode("Failed to delete cart item."));
        }
    } catch (Exception $e) {
        header("Location: index.php?status=error&message=" . urlencode("Error deleting cart item: " . $e->getMessage()));
    }
}
?>
