<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || 
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: minor.php");
    exit;
}

require_once "config.php";
require_once "notification_service.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize_input($_POST['status']);
    $notes = isset($_POST['notes']) ? sanitize_input($_POST['notes']) : '';
    
    // Validate status - updated to match the new statuses
    $valid_statuses = ['Order Placed', 'Confirmed', 'Processing', 'Packing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    
    if (!in_array($new_status, $valid_statuses)) {
        header("Location: orders.php?error=invalid_status");
        exit;
    }
    
    // Check if order exists
    $check_sql = "SELECT id FROM orders WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $order_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        header("Location: orders.php?error=order_not_found");
        exit;
    }
    
    // Use the notification-aware status update function
    $admin_id = $_SESSION['user_id'];
    $result = update_order_status($conn, $order_id, $new_status, $admin_id, $notes);
    
    if ($result['success']) {
        header("Location: orders.php?success=status_updated");
        exit;
    } else {
        header("Location: orders.php?error=update_failed");
        exit;
    }
} else {
    header("Location: orders.php");
    exit;
}
?>
