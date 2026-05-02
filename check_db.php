<?php
require_once "config.php";

echo "=== Database Check ===\n\n";

// Get users
$users = mysqli_query($conn, "SELECT id, username, name FROM users");
echo "USERS:\n";
while($u = mysqli_fetch_assoc($users)) {
    echo "  ID {$u['id']}: {$u['username']} ({$u['name']})\n";
}

// Get orders
$orders = mysqli_query($conn, "SELECT o.id, o.order_number, o.user_id, o.status, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id");
echo "\nORDERS:\n";
$order_count = 0;
while($o = mysqli_fetch_assoc($orders)) {
    echo "  Order #{$o['order_number']} (ID {$o['id']}): @{$o['username']} - {$o['status']}\n";
    $order_count++;
}

if ($order_count == 0) {
    echo "  [No orders in database]\n";
}

// Check notifications table
$notifs = mysqli_query($conn, "SELECT COUNT(*) as count FROM notifications");
$notif_row = mysqli_fetch_assoc($notifs);
echo "\nNOTIFICATIONS: {$notif_row['count']} total\n";

mysqli_close($conn);
?>
