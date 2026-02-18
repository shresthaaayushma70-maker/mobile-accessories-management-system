<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || 
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: minor.php");
    exit;
}

require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize_input($_POST['status']);
    
    // Validate status
    $valid_statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    
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
    
    // Update order status
    $update_sql = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $order_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        // Log activity
        $user_id = $_SESSION['user_id'];
        log_activity($conn, $user_id, "Order Status Updated", "Order #" . $order_id . " status updated to " . $new_status);
        
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
