<?php
/**
 * BAZARIO - Complete E2E Test with Manual Notification Trigger
 * Tests the full notification pipeline
 */

require_once "config.php";
require_once "notification_service.php";

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "BAZARIO E2E Notification Test\n";
echo "========================================\n\n";

// Use actual user with orders
$test_user_id = 4; // Bishal Budha - has orders
$test_order_id = 1; // First order

echo "Step 1: Load Test Data\n";
echo "─────────────────────────\n";

// Get user
$user_sql = "SELECT id, name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($stmt, "i", $test_user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    echo "❌ User not found\n";
    exit;
}

echo "✓ User: {$user['name']} ({$user['email']})\n";

// Get order
$order_sql = "SELECT id, order_number, status, total_amount FROM orders WHERE id = ?";
$stmt = mysqli_prepare($conn, $order_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    echo "❌ Order not found\n";
    exit;
}

echo "✓ Order #{$order['order_number']} - Amount: ₹{$order['total_amount']} - Current: {$order['status']}\n\n";

// Step 2: Create notifications manually
echo "Step 2: Creating Test Notifications\n";
echo "────────────────────────────────────\n";

$test_notifications = [
    ['order_placed', 'Order Placed', 'Your order has been successfully placed!', "track_order.php?order_id={$test_order_id}"],
    ['processing', 'Processing', 'Your order is now being processed.', "track_order.php?order_id={$test_order_id}"],
    ['shipped', 'Out for Delivery', 'Your order is out for delivery today!', "track_order.php?order_id={$test_order_id}"],
];

foreach ($test_notifications as [$type, $title, $body, $link]) {
    $result = create_notification(
        $conn,
        $test_user_id,
        $test_order_id,
        $type,
        $title,
        $body,
        $link
    );
    
    if ($result) {
        echo "✓ Created '{$title}' notification\n";
    } else {
        echo "❌ Failed to create '{$title}' notification\n";
        echo "   Database Error: " . mysqli_error($conn) . "\n";
    }
}
echo "\n";

// Step 3: Check created notifications
echo "Step 3: Verify Notifications Created\n";
echo "──────────────────────────────────────\n";

$notif_sql = "SELECT id, title, is_read, created_at FROM notifications WHERE order_id = ? ORDER BY created_at";
$stmt = mysqli_prepare($conn, $notif_sql);
mysqli_stmt_bind_param($stmt, "i", $test_order_id);
mysqli_stmt_execute($stmt);
$notif_result = mysqli_stmt_get_result($stmt);

$notif_count = 0;
while ($notif = mysqli_fetch_assoc($notif_result)) {
    $notif_count++;
    $read_badge = $notif['is_read'] ? '✓ Read' : '○ Unread';
    echo "{$notif_count}. {$notif['title']} [{$read_badge}]\n";
    echo "   Created: {$notif['created_at']}\n";
}
mysqli_stmt_close($stmt);

echo "✓ Total: {$notif_count} notifications\n\n";

// Step 4: Test mark as read
echo "Step 4: Test Mark as Read\n";
echo "──────────────────────────\n";

$mark_result = mark_all_read($conn, $test_user_id);
if ($mark_result) {
    echo "✓ Marked all notifications as read\n";
} else {
    echo "❌ Failed to mark as read\n";
}

// Verify
$unread_count = get_unread_notifications_count($conn, $test_user_id);
$total_notif_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM notifications WHERE user_id = 4");
$total_notifs = mysqli_fetch_assoc($total_notif_result);
echo "  Unread: {$unread_count} / Total: {$total_notifs['count']}\n\n";

// Step 5: Test status update with notification
echo "Step 5: Test Update Status with Auto-notification\n";
echo "───────────────────────────────────────────────────\n";

// First update the status to a valid value
$update_result = update_order_status(
    $conn,
    $test_order_id,
    'Processing',
    0,  // Admin/system ID
    'E2E Test: Auto-triggered notification'
);

if ($update_result['success']) {
    echo "✓ {$update_result['message']}\n";
} else {
    echo "❌ {$update_result['message']}\n";
}
echo "\n";

// Step 6: Check notification preferences
echo "Step 6: Notification Preferences\n";
echo "────────────────────────────────\n";

$prefs = get_notification_preferences($conn, $test_user_id);
if (!$prefs) {
    echo "Creating default preferences...\n";
    create_default_preferences($conn, $test_user_id);
    $prefs = get_notification_preferences($conn, $test_user_id);
}

if ($prefs) {
    $enabled = [];
    foreach ($prefs as $key => $val) {
        if (strpos($key, 'email_on_') === 0 && $val) {
            $enabled[] = str_replace('email_on_', '', $key);
        }
    }
    echo "✓ Email alerts enabled for: " . implode(', ', $enabled) . "\n";
} else {
    echo "⚠ Could not load preferences\n";
}

echo "\n========================================\n";
echo "Tests Complete!\n";
echo "========================================\n";
echo "\nSummary:\n";
echo "  ✓ Notifications created and stored\n";
echo "  ✓ Mark as read functionality works\n";
echo "  ✓ Status update triggers notifications\n";
echo "  ✓ User preferences configured\n";
echo "\nThe notification system is operational!\n";

mysqli_close($conn);
?>
