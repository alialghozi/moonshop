<?php
require 'connection.php';
session_start();

// Ensure the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // Validate status input
    $valid_statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
    if (!in_array($status, $valid_statuses)) {
        header("Location: view_order.php?status=invalid_status");
        exit();
    }

    // Update the order status
    $update_query = "
        UPDATE orders 
        SET status = ? 
        WHERE id = ? 
        AND id IN (
            SELECT DISTINCT o.id 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN products p ON oi.product_id = p.id 
            WHERE p.seller_id = ?
        )";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sii", $status, $order_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: view_order.php?status=success");
    } else {
        // Redirect with error message
        header("Location: view_order.php?status=error");
    }
    $stmt->close();
} else {
    // Redirect to view orders if accessed without POST
    header("Location: view_order.php");
    exit();
}
?>
