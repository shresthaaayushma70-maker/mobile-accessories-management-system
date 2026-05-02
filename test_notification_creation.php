<?php
/**
 * BAZARIO - Test Notification Creation
 * Verify that notifications are being created for order status updates
 */

require_once "config.php";
require_once "notification_service.php";

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Notification System Test</h2>";
echo "<hr>";

// 1. Get all orders with notifications
echo "<h3>1. Orders and Their Notifications</h3>";
$orders_sql = "SELECT o.id, o.order_number, o.status, o.user_id FROM orders LIMIT 5";
$orders_result = mysqli_query($conn, $orders_sql);

while ($order = mysqli_fetch_assoc($orders_result)) {
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<b>Order #{$order['order_number']}</b> | Status: <span style='background: #cfe2ff; padding: 5px 10px; border-radius: 3px;'>{$order['status']}</span><br>";
    
    // Check notifications for this order
    $notif_sql = "SELECT id, title, body, is_read, created_at FROM notifications WHERE order_id = ? ORDER BY created_at DESC";
    $notif_stmt = mysqli_prepare($conn, $notif_sql);
    mysqli_stmt_bind_param($notif_stmt, "i", $order['id']);
    mysqli_stmt_execute($notif_stmt);
    $notif_result = mysqli_stmt_get_result($notif_stmt);
    $notif_count = mysqli_num_rows($notif_result);
    
    if ($notif_count > 0) {
        echo "✅ <b>$notif_count Notification(s) Found:</b><br>";
        while ($notif = mysqli_fetch_assoc($notif_result)) {
            $read_status = $notif['is_read'] ? '✓ Read' : '● Unread';
            echo "  • {$notif['title']}: {$notif['body']} ({$read_status})<br>";
            echo "    Created: {$notif['created_at']}<br>";
        }
    } else {
        echo "❌ <b>No notifications found for this order</b><br>";
    }
    
    // Check user preferences
    $prefs = get_notification_preferences($conn, $order['user_id']);
    if ($prefs) {
        echo "<br><b>User Notification Preferences:</b><br>";
        echo "  • Email on Processing: " . ($prefs['email_on_processing'] ? "✅ Yes" : "❌ No") . "<br>";
        echo "  • Email on Shipped: " . ($prefs['email_on_shipped'] ? "✅ Yes" : "❌ No") . "<br>";
        echo "  • Email on Delivered: " . ($prefs['email_on_delivered'] ? "✅ Yes" : "❌ No") . "<br>";
    }
    
    echo "</div>";
    mysqli_stmt_close($notif_stmt);
}

echo "<hr>";
echo "<h3>2. Create Test Notification</h3>";

// Get first order and create a test notification
$test_sql = "SELECT id, user_id FROM orders LIMIT 1";
$test_result = mysqli_query($conn, $test_sql);
if ($test_result && mysqli_num_rows($test_result) > 0) {
    $test_order = mysqli_fetch_assoc($test_result);
    
    echo "Creating test notification for Order #{$test_order['id']}...<br>";
    
    $success = create_notification(
        $conn,
        $test_order['user_id'],
        $test_order['id'],
        'test_notification',
        'Test Notification',
        'This is a test notification to verify the system is working correctly.',
        'orders.php'
    );
    
    if ($success) {
        echo "✅ Test notification created successfully!<br>";
        
        // Verify it was created
        $verify_sql = "SELECT COUNT(*) as count FROM notifications WHERE order_id = ?";
        $verify_stmt = mysqli_prepare($conn, $verify_sql);
        mysqli_stmt_bind_param($verify_stmt, "i", $test_order['id']);
        mysqli_stmt_execute($verify_stmt);
        $verify_result = mysqli_stmt_get_result($verify_stmt);
        $verify_row = mysqli_fetch_assoc($verify_result);
        
        echo "Total notifications for this order: {$verify_row['count']}<br>";
        mysqli_stmt_close($verify_stmt);
    } else {
        echo "❌ Failed to create test notification<br>";
    }
}

echo "<hr>";
echo "<h3>✅ Test Complete!</h3>";
echo "<p><a href='orders.php'>Back to Orders</a></p>";

mysqli_close($conn);
?>
