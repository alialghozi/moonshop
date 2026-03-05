<?php
require 'connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    // Start transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // Check if the order belongs to the user and its status is Pending
        $order_check_query = "SELECT id, status FROM orders WHERE id = ? AND buyer_id = ? AND status = 'Pending'";
        $stmt = $conn->prepare($order_check_query);
        $stmt->bind_param("ii", $order_id, $buyer_id);
        $stmt->execute();
        $order_result = $stmt->get_result();

        if ($order_result->num_rows === 0) {
            throw new Exception("Invalid order or the order cannot be canceled.");
        }

        // Fetch all items in the order
        $order_items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($order_items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items_result = $stmt->get_result();

        // Update product quantity
        while ($item = $order_items_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $update_stock_query = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_stock_query);
            $update_stmt->bind_param("ii", $quantity, $product_id);
            $update_stmt->execute();

            if ($update_stmt->affected_rows === 0) {
                throw new Exception("Failed to update stock for product ID: " . $product_id);
            }
        }

        // Update the order status to "Cancelled"
        $update_order_query = "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND buyer_id = ?";
        $stmt = $conn->prepare($update_order_query);
        $stmt->bind_param("ii", $order_id, $buyer_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Failed to cancel the order.");
        }

        // Commit the transaction
        $conn->commit();

        // Redirect to track_order.php with success message
        header("Location: track_order.php?status=canceled");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error canceling order: " . $e->getMessage());
    }
} else {
    // Redirect to track_order.php if accessed incorrectly
    header("Location: track_order.php");
    exit();
}
?>
