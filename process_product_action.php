<?php
session_start();
require 'connection.php';

// Ensure the user is logged in as an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Handle product action
if (isset($_POST['product_id'], $_POST['action'])) {
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Approve the product
        $query = "UPDATE products SET status = 'approved' WHERE id = ?";
    } elseif ($action === 'reject') {
        // Reject the product
        $query = "UPDATE products SET status = 'rejected' WHERE id = ?";
    } else {
        header("Location: approve_products.php?message=Invalid+action&type=error");
        exit();
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $message = $action === 'approve' ? "Product approved successfully!" : "Product rejected successfully!";
        header("Location: approve_products.php?message=" . urlencode($message) . "&type=success");
    } else {
        header("Location: approve_products.php?message=Failed+to+process+product&type=error");
    }
    $stmt->close();
    $conn->close();
    exit();
}
