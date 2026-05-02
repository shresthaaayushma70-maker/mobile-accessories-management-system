<?php
/**
 * BAZARIO - End-to-End Notification Test
 * Tests: Order placement → Notifications → Status updates
 */

require_once "config.php";
require_once "notification_service.php";

// Output as plain text
header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "BAZARIO E2E Notification Test\n";
echo "========================================\n\n";

// 1. Get a test user
$test_user_sql = "SELECT id, name, email, username FROM users LIMIT 1";
$result = mysqli_query($conn, $test_user_sql);

if (mysqli_num_rows($result) == 0) {
    echo "❌ ERROR: No users found in database\n";
    echo "Please create a user first by registering.\n";
    exit;
}

$test_user = mysqli_fetch_assoc($result);
$test_user_id = $test_user['id'];

echo "✓ Test User Found\n";
echo "  - ID: {$test_user_id}\n";
echo "  - Name: {$test_user['name']}\n";
echo "  - Email: {$test_user['email']}\n";
echo "  - Username: {$test_user['username']}\n\n";

// 2. Check if test order exists or create one
echo "Checking for test orders...\n";
$order_sql = "SELECT id, order_number, status FROM orders WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $order_sql);
mysqli_stmt_bind_param($stmt, "i", $test_user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($order_result) == 0) {
    echo "❌ No orders found for this user\n";
    echo "Please place an order first to test notifications.\n";
    mysqli_stmt_close($stmt);
    exit;
}

$test_order = mysqli_fetch_assoc($order_result);
$test_order_id = $test_order['id'];
mysqli_stmt_close($stmt);

echo "✓ Test Order Found\n";
echo "  - Order ID: {$test_order_id}\n";
echo "  - Order #: {$test_order['order_number']}\n";
echo "  - Current Status: {$test_order['status']}\n\n";

// 3. Check existing notifications for this order
echo "Checking Notifications for Order #{$test_order['order_number']}...\n";
$notif_sql = "SELECT id, title, is_read, created_at FROM notifications WHERE order_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $notif_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$notif_result = mysqli_stmt_get_result($stmt);
$notification_count = mysqli_num_rows($notif_result);

if ($notification_count == 0) {
    echo "❌ No notifications found for this order\n";
} else {
    echo "✓ Found {$notification_count} notification(s):\n";
    while ($notif = mysqli_fetch_assoc($notif_result)) {
        $read_status = $notif['is_read'] ? '(Read)' : '(Unread)';
        echo "  - {$notif['id']}: {$notif['title']} {$read_status}\n";
        echo "    Created: {$notif['created_at']}\n";
    }
}
mysqli_stmt_close($stmt);
echo "\n";

// 4. Test status transitions and notification creation
echo "Testing Status Transitions...\n";
$statuses = ['Order Placed', 'Processing', 'Packing', 'Out for Delivery', 'Delivered'];

echo "Current Status: {$test_order['status']}\n";

// Try to transition to next status
$current_idx = array_search($test_order['status'], $statuses);
if ($current_idx !== false && $current_idx < count($statuses) - 1) {
    $next_status = $statuses[$current_idx + 1];
    echo "Attempting to update to: {$next_status}\n";
    
    // For testing, use a fake admin ID
    $test_admin_id = 1;
    
    $result = update_order_status($conn, $test_order_id, $next_status, $test_admin_id, "Test update");
    
    if ($result['success']) {
        echo "✓ Status updated successfully\n";
        
        // Check if new notification was created
        $new_notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $new_notif_sql);
        mysqli_stmt_bind_param($stmt, "i", $test_order_id);
        mysqli_stmt_execute($stmt);
        $notif_count_result = mysqli_stmt_get_result($stmt);
        $notif_count_row = mysqli_fetch_assoc($notif_count_result);
        mysqli_stmt_close($stmt);
        
        echo "✓ Total Notifications now: {$notif_count_row['count']}\n";
    } else {
        echo "❌ Status update failed: {$result['message']}\n";
    }
} else {
    echo "⚠ Order is at final status ({$test_order['status']}), cannot transition further\n";
}
echo "\n";

// 5. Check order status history
echo "Order Status History:\n";
$history_sql = "SELECT status, changed_by, notes, created_at FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $history_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$history_result = mysqli_stmt_get_result($stmt);
$history_count = mysqli_num_rows($history_result);

if ($history_count == 0) {
    echo "  No history records yet\n";
} else {
    while ($history = mysqli_fetch_assoc($history_result)) {
        echo "  - Status: {$history['status']}\n";
        echo "    Changed by: {$history['changed_by']}\n";
        if (!empty($history['notes'])) {
            echo "    Notes: {$history['notes']}\n";
        }
        echo "    Date: {$history['created_at']}\n";
    }
}
mysqli_stmt_close($stmt);
echo "\n";

// 6. Check user notification preferences
echo "User Notification Preferences:\n";
$prefs = get_notification_preferences($conn, $test_user_id);

if (!$prefs) {
    echo "❌ No preferences found, creating defaults...\n";
    create_default_preferences($conn, $test_user_id);
    $prefs = get_notification_preferences($conn, $test_user_id);
}

if ($prefs) {
    $enabled_count = 0;
    foreach ($prefs as $key => $value) {
        if (strpos($key, 'email_on_') === 0 && $value) {
            $enabled_count++;
        }
    }
    echo "✓ Email notifications enabled: {$enabled_count}/6 stages\n";
} else {
    echo "❌ Failed to load preferences\n";
}

echo "\n========================================\n";
echo "Test Complete!\n";
echo "========================================\n";

mysqli_close($conn);
?>
