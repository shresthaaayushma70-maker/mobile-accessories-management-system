<?php
/**
 * BAZARIO - Complete Notification System Debugger
 * Tests all components of the notification system
 */

session_start();

// Allow both logged-in and admin access
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

require_once "config.php";
require_once "notification_service.php";

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Notification System Debugger</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .test-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .test-btn:hover { background: #0056b3; }
        .log { background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0; font-family: monospace; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>🔧 Notification System Debugger</h1>";

if (!$is_logged_in) {
    echo "<div class='section error'>
        <h3>⚠️ Not Logged In</h3>
        <p>Please log in first to fully test the notification system.</p>
        <a href='minor.php'>Login</a>
    </div>";
} else {
    $user_id = $_SESSION['user_id'];
    echo "<div class='section info'>
        <h3>✓ Logged In as User ID: {$user_id}</h3>
    </div>";
}

// Test 1: Database Connection
echo "<div class='section'>
    <h3>1️⃣ Database Connection Test</h3>";

if ($conn) {
    echo "<p class='success'>✓ Database connected successfully</p>";
    
    // Check if tables exist
    $tables_needed = ['notifications', 'notification_preferences', 'orders', 'users'];
    echo "<table>
        <tr><th>Table</th><th>Status</th></tr>";
    
    foreach ($tables_needed as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        $exists = mysqli_num_rows($result) > 0;
        $status = $exists ? '<span class=\'success\'>✓ Exists</span>' : '<span class=\'error\'>✗ Missing</span>';
        echo "<tr><td>$table</td><td>$status</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ Database connection failed</p>";
}

echo "</div>";

// Test 2: Orders and Notifications
echo "<div class='section'>
    <h3>2️⃣ Orders & Notifications Test</h3>";

$orders = mysqli_query($conn, "SELECT o.id, o.order_number, o.status, o.user_id, COUNT(n.id) as notification_count 
                                FROM orders o 
                                LEFT JOIN notifications n ON o.id = n.order_id 
                                GROUP BY o.id 
                                ORDER BY o.created_at DESC 
                                LIMIT 5");

if (mysqli_num_rows($orders) > 0) {
    echo "<table>
        <tr>
            <th>Order #</th>
            <th>Status</th>
            <th>User ID</th>
            <th>Notifications</th>
            <th>Action</th>
        </tr>";
    
    while ($order = mysqli_fetch_assoc($orders)) {
        echo "<tr>
            <td>#{$order['order_number']}</td>
            <td>{$order['status']}</td>
            <td>{$order['user_id']}</td>
            <td>{$order['notification_count']}</td>
            <td>
                <button class='test-btn' onclick=\"testOrderNotification({$order['id']}, '{$order['order_number']}')\">
                    Test Notification
                </button>
            </td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No orders found. Please place an order first.</p>";
}

echo "</div>";

// Test 3: Notification Preferences
if ($is_logged_in) {
    echo "<div class='section'>
        <h3>3️⃣ Notification Preferences Test</h3>";
    
    $prefs = get_notification_preferences($conn, $user_id);
    
    if (!$prefs) {
        echo "<p class='warning'>No preferences found. Creating defaults...</p>";
        create_default_preferences($conn, $user_id);
        $prefs = get_notification_preferences($conn, $user_id);
    }
    
    if ($prefs) {
        echo "<table>
            <tr><th>Setting</th><th>Status</th></tr>";
        
        $preference_fields = [
            'email_on_order_placed' => 'Order Placed',
            'email_on_order_confirmed' => 'Order Confirmed',
            'email_on_processing' => 'Processing',
            'email_on_packing' => 'Packing',
            'email_on_shipped' => 'Shipped',
            'email_on_delivered' => 'Delivered'
        ];
        
        foreach ($preference_fields as $field => $label) {
            $value = isset($prefs[$field]) && $prefs[$field] ? '✓ Enabled' : '✗ Disabled';
            $class = strpos($value, '✓') !== false ? 'success' : 'warning';
            echo "<tr><td>$label</td><td class='$class'>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ Failed to load preferences</p>";
    }
    
    echo "</div>";
}

// Test 4: Recent Notifications
if ($is_logged_in) {
    echo "<div class='section'>
        <h3>4️⃣ Recent Notifications (Last 10)</h3>";
    
    $notifications = get_user_notifications($conn, $user_id, 10, 0);
    
    if (count($notifications) > 0) {
        echo "<table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Order ID</th>
                <th>Created</th>
            </tr>";
        
        foreach ($notifications as $notif) {
            $status = $notif['is_read'] ? '✓ Read' : '● Unread';
            $created = date('M d, Y H:i', strtotime($notif['created_at']));
            echo "<tr>
                <td>{$notif['id']}</td>
                <td>{$notif['title']}</td>
                <td>$status</td>
                <td>{$notif['order_id']}</td>
                <td>$created</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No notifications found</p>";
    }
    
    echo "</div>";
}

// Test 5: Create Test Notification
if ($is_logged_in) {
    echo "<div class='section'>
        <h3>5️⃣ Create Test Notification</h3>";
    
    // Get first order for test
    $test_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, user_id FROM orders LIMIT 1"));
    
    if ($test_order) {
        echo "<p>Testing notification creation for Order ID: {$test_order['id']}</p>";
        echo "<button class='test-btn' onclick=\"createTestNotification({$test_order['id']}, {$test_order['user_id']})\">
            Create Test Notification
        </button>";
    } else {
        echo "<p class='warning'>No orders available for testing</p>";
    }
    
    echo "</div>";
}

echo "</div>

<script>
    function testOrderNotification(orderId, orderNumber) {
        const status = prompt('Enter new status (e.g., Processing, Delivered, etc.):', 'Processing');
        if (!status) return;
        
        const data = new FormData();
        data.append('action', 'test_notification');
        data.append('order_id', orderId);
        data.append('status', status);
        
        fetch(window.location.href, {
            method: 'POST',
            body: data
        })
        .then(response => response.text())
        .then(() => {
            alert('Notification test initiated. Check your notifications page.');
            location.reload();
        })
        .catch(error => alert('Error: ' + error));
    }
    
    function createTestNotification(orderId, userId) {
        const data = new FormData();
        data.append('action', 'create_test');
        data.append('order_id', orderId);
        data.append('user_id', userId);
        
        fetch(window.location.href, {
            method: 'POST',
            body: data
        })
        .then(response => response.text())
        .then(() => {
            alert('Test notification created!');
            location.reload();
        })
        .catch(error => alert('Error: ' + error));
    }
</script>

</body>
</html>";

// Handle AJAX test requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'test_notification') {
            $order_id = intval($_POST['order_id']);
            $status = sanitize_input($_POST['status']);
            $admin_id = $is_logged_in ? $user_id : 1;
            
            $result = update_order_status($conn, $order_id, $status, $admin_id, 'Test notification');
            echo "Test result: " . json_encode($result);
            exit;
        } elseif ($_POST['action'] === 'create_test') {
            $order_id = intval($_POST['order_id']);
            $user_id_test = intval($_POST['user_id']);
            
            $success = create_notification(
                $conn,
                $user_id_test,
                $order_id,
                'test',
                'Test Notification',
                'This is a test notification to verify the system is working.',
                'orders.php'
            );
            
            echo "Notification creation: " . ($success ? 'Success' : 'Failed');
            exit;
        }
    }
}

mysqli_close($conn);
?>
