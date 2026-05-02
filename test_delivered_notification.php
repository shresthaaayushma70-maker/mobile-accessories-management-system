<?php
/**
 * Test: Delivered Status Notification
 * Simulates admin marking order as delivered and verifies user gets notification
 */

require_once "config.php";
require_once "notification_service.php";

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "Testing: Delivered Status Notification\n";
echo "========================================\n\n";

// Test data
$test_order_id = 1;
$test_user_id = 4;
$test_admin_id = 1;

// Step 1: Get order details
$order_sql = "SELECT id, order_number, status, user_id FROM orders WHERE id = ?";
$stmt = mysqli_prepare($conn, $order_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

echo "Order Status Before: " . $order['status'] . "\n";

// Step 2: Count notifications before update
$before_notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE order_id = ? AND notification_type = 'delivered'";
$stmt = mysqli_prepare($conn, $before_notif_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$before_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

echo "Delivered Notifications Before: " . $before_count['count'] . "\n\n";

// Step 3: Update order to Delivered
echo "Updating order status to 'Delivered'...\n";
$update_result = update_order_status(
    $conn,
    $test_order_id,
    'Delivered',
    $test_admin_id,
    'Order delivered to customer'
);

if ($update_result['success']) {
    echo "✓ Status updated: " . $update_result['message'] . "\n\n";
} else {
    echo "❌ Update failed: " . $update_result['message'] . "\n\n";
}

// Step 4: Check if delivered notification was created
$after_notif_sql = "SELECT id, title, body, created_at FROM notifications WHERE order_id = ? AND notification_type = 'delivered'";
$stmt = mysqli_prepare($conn, $after_notif_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$notif_result = mysqli_stmt_get_result($stmt);

echo "Delivered Notifications After Update:\n";
$found_delivered = false;
while ($notif = mysqli_fetch_assoc($notif_result)) {
    echo "✓ ID {$notif['id']}: {$notif['title']}\n";
    echo "  Body: {$notif['body']}\n";
    echo "  Created: {$notif['created_at']}\n";
    $found_delivered = true;
}
mysqli_stmt_close($stmt);

if (!$found_delivered) {
    echo "❌ NO delivered notification found!\n";
}

echo "\n";

// Step 5: Get all notifications for this order
echo "All Notifications for Order #{$order['order_number']}:\n";
$all_notif_sql = "SELECT notification_type, COUNT(*) as count FROM notifications WHERE order_id = ? GROUP BY notification_type";
$stmt = mysqli_prepare($conn, $all_notif_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$all_notif = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($all_notif)) {
    echo "  - {$row['notification_type']}: {$row['count']} notification(s)\n";
}
mysqli_stmt_close($stmt);

// Step 6: Check order in database
$order_check_sql = "SELECT status FROM orders WHERE id = ?";
$stmt = mysqli_prepare($conn, $order_check_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$order_check = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

echo "\n";
echo "Order Status After: " . $order_check['status'] . "\n";

// Step 7: Check status history
echo "\nOrder Status History:\n";
$history_sql = "SELECT status, notes, created_at FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $history_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$history = mysqli_stmt_get_result($stmt);

while ($h = mysqli_fetch_assoc($history)) {
    echo "  - {$h['status']}: {$h['notes']} ({$h['created_at']})\n";
}
mysqli_stmt_close($stmt);

echo "\n========================================\n";

if ($found_delivered) {
    echo "✓ SUCCESS: User received delivered notification\n";
} else {
    echo "❌ ISSUE: User DID NOT receive delivered notification\n";
    echo "Action needed: Implement notification trigger for Delivered status\n";
}

echo "========================================\n";

mysqli_close($conn);
?>
