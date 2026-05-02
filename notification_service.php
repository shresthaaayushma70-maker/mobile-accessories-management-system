<?php
/**
 * BAZARIO - Notification Service & Order Tracking Functions
 * Handles all notification generation, email/SMS delivery, and order status management
 * Version: 1.0 | Updated: Jan 2026
 */

// ======================================
// 1. Notification Creation Functions
// ======================================

/**
 * Create and store notification in database
 */
function create_notification($conn, $user_id, $order_id, $notification_type, $title, $body, $link = '') {
    $sql = "INSERT INTO notifications (user_id, order_id, notification_type, title, body, link, is_read, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iissss", $user_id, $order_id, $notification_type, $title, $body, $link);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Notification creation failed: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        error_log("Notification prepare failed: " . mysqli_error($conn));
        return false;
    }
}

/**
 * Get unread notifications count for user
 */
function get_unread_notifications_count($conn, $user_id) {
    $sql = "SELECT COUNT(*) as unread_count FROM notifications 
            WHERE user_id = ? AND is_read = FALSE";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['unread_count'];
    }
    return 0;
}

/**
 * Get all notifications for user (paginated)
 */
function get_user_notifications($conn, $user_id, $limit = 10, $offset = 0) {
    $sql = "SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $notifications;
    }
    return [];
}

/**
 * Mark notification as read
 */
function mark_notification_read($conn, $notification_id) {
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $notification_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

/**
 * Mark all notifications as read for user
 */
function mark_all_read($conn, $user_id) {
    $sql = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

// ======================================
// 2. Order Status Update Functions
// ======================================

/**
 * Update order status and create status history record
 */
function update_order_status($conn, $order_id, $new_status, $admin_id, $notes = '') {
    // Get current order details
    $order_sql = "SELECT user_id, status FROM orders WHERE id = ?";
    $order_stmt = mysqli_prepare($conn, $order_sql);
    mysqli_stmt_bind_param($order_stmt, "i", $order_id);
    mysqli_stmt_execute($order_stmt);
    $order_result = mysqli_stmt_get_result($order_stmt);
    
    if (mysqli_num_rows($order_result) == 0) {
        mysqli_stmt_close($order_stmt);
        return ['success' => false, 'message' => 'Order not found'];
    }
    
    $order = mysqli_fetch_assoc($order_result);
    $user_id = $order['user_id'];
    $old_status = $order['status'];
    mysqli_stmt_close($order_stmt);
    
    // Update timestamp based on status
    $timestamp_field = get_timestamp_field($new_status);
    
    $update_sql = "UPDATE orders SET status = ?, updated_at = NOW()";
    if ($timestamp_field) {
        $update_sql .= ", $timestamp_field = NOW()";
    }
    $update_sql .= " WHERE id = ?";
    
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if (!$update_stmt) {
        return ['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)];
    }
    
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $order_id);
    $update_executed = mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    if (!$update_executed) {
        return ['success' => false, 'message' => 'Failed to update order status'];
    }
    
    // Create status history record
    $history_sql = "INSERT INTO order_status_history (order_id, status, changed_by, notes) 
                    VALUES (?, ?, ?, ?)";
    $history_stmt = mysqli_prepare($conn, $history_sql);
    if ($history_stmt) {
        mysqli_stmt_bind_param($history_stmt, "isss", $order_id, $new_status, $admin_id, $notes);
        mysqli_stmt_execute($history_stmt);
        mysqli_stmt_close($history_stmt);
    }
    
    // Create notification for user
    $notification_data = get_notification_for_status($new_status);
    $notif_created = create_notification(
        $conn,
        $user_id,
        $order_id,
        $notification_data['type'],
        $notification_data['title'],
        $notification_data['message'],
        "track_order.php?order_id=" . $order_id
    );
    if (!$notif_created) {
        error_log("Failed to create notification for order $order_id user $user_id");
    }
    
    // Check user's notification preferences
    $prefs = get_notification_preferences($conn, $user_id);
    
    // Send email if enabled
    $email_pref_field = get_email_preference_field($new_status);
    if ($prefs && $email_pref_field && $prefs[$email_pref_field]) {
        send_notification_email($conn, $user_id, $order_id, $new_status);
    }
    
    // Log activity
    log_activity($conn, $admin_id, 'UPDATE_ORDER_STATUS', 
                 "Changed order #$order_id from $old_status to $new_status. Notes: $notes");
    
    return ['success' => true, 'message' => 'Order status updated successfully', 'notification_created' => (bool)$notif_created];
}

/**
 * Map status to timestamp field
 */
function get_timestamp_field($status) {
    $map = [
        'Order Placed' => 'placed_at',
        'Confirmed' => 'confirmed_at',
        'Processing' => 'processing_at',
        'Packing' => 'packing_at',
        'Out for Delivery' => 'shipped_at',
        'Delivered' => 'delivered_at',
        'Cancelled' => 'cancelled_at'
    ];
    return isset($map[$status]) ? $map[$status] : null;
}

/**
 * Map status to email preference field name
 */
function get_email_preference_field($status) {
    $map = [
        'Order Placed' => 'email_on_order_placed',
        'Confirmed' => 'email_on_order_confirmed',
        'Processing' => 'email_on_processing',
        'Packing' => 'email_on_packing',
        'Out for Delivery' => 'email_on_shipped',
        'Delivered' => 'email_on_delivered',
        'Cancelled' => 'email_on_cancelled'
    ];
    return isset($map[$status]) ? $map[$status] : null;
}

/**
 * Get notification data based on status
 */
function get_notification_for_status($status) {
    $notifications = [
        'Order Placed' => [
            'type' => 'order_placed',
            'title' => 'Order Placed',
            'message' => 'Your order has been placed successfully! You will receive updates soon.',
            'icon' => 'fa-box'
        ],
        'Confirmed' => [
            'type' => 'order_confirmed',
            'title' => 'Order Confirmed',
            'message' => 'Your order has been confirmed. Payment received (COD). We\'re preparing your items!',
            'icon' => 'fa-check-circle'
        ],
        'Processing' => [
            'type' => 'processing',
            'title' => 'Processing',
            'message' => 'Your order is being processed. Items are being picked and prepared.',
            'icon' => 'fa-cogs'
        ],
        'Packing' => [
            'type' => 'packing',
            'title' => 'Packing',
            'message' => 'Your order is being packed. It will be ready for shipment soon!',
            'icon' => 'fa-boxes'
        ],
        'Out for Delivery' => [
            'type' => 'shipped',
            'title' => 'Out for Delivery',
            'message' => 'Your order is out for delivery today! Check back for delivery updates.',
            'icon' => 'fa-truck'
        ],
        'Delivered' => [
            'type' => 'delivered',
            'title' => 'Delivered',
            'message' => 'Your order has been delivered successfully! Thank you for shopping at Bazario.',
            'icon' => 'fa-check-circle'
        ],
        'Cancelled' => [
            'type' => 'cancelled',
            'title' => 'Order Cancelled',
            'message' => 'Your order has been cancelled. Please contact support for details.',
            'icon' => 'fa-times-circle'
        ]
    ];
    
    return isset($notifications[$status]) ? $notifications[$status] : $notifications['Order Placed'];
}

// ======================================
// 3. Notification Preferences Functions
// ======================================

/**
 * Get user's notification preferences
 */
function get_notification_preferences($conn, $user_id) {
    $sql = "SELECT * FROM notification_preferences WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $prefs = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $prefs;
    }
    return null;
}

/**
 * Create default notification preferences for new user
 */
function create_default_preferences($conn, $user_id) {
    $sql = "INSERT INTO notification_preferences (user_id) VALUES (?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

/**
 * Update notification preferences
 */
function update_notification_preferences($conn, $user_id, $preferences) {
    $allowed_cols = [
        'email_on_order_placed', 'email_on_order_confirmed', 'email_on_processing',
        'email_on_packing', 'email_on_shipped', 'email_on_delivered',
        'sms_on_order_placed', 'sms_on_order_confirmed', 'sms_on_processing',
        'sms_on_packing', 'sms_on_shipped', 'sms_on_delivered', 'phone_number'
    ];
    
    $updates = [];
    $params = [];
    $types = '';
    
    foreach ($preferences as $key => $value) {
        if (in_array($key, $allowed_cols)) {
            $updates[] = "$key = ?";
            $params[] = $value;
            $types .= in_array($key, ['phone_number']) ? 's' : 'i';
        }
    }
    
    if (empty($updates)) {
        error_log("No valid preference columns provided for update");
        return false;
    }
    
    $params[] = $user_id;
    $types .= 'i';
    
    $sql = "UPDATE notification_preferences SET " . implode(', ', $updates) . " WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        // Ensure params are passed correctly
        call_user_func_array(
            'mysqli_stmt_bind_param',
            array_merge([$stmt, $types], array_map(function(&$a) { return $a; }, $params))
        );
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Preference update failed for user $user_id: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        error_log("Preference update prepare failed: " . mysqli_error($conn));
        return false;
    }
}

// ======================================
// 4. Email Notification Functions
// ======================================

/**
 * Send notification email
 */
function send_notification_email($conn, $user_id, $order_id, $status) {
    // Get user email
    $user_sql = "SELECT email, name FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user = mysqli_fetch_assoc($user_result);
    mysqli_stmt_close($user_stmt);
    
    if (!$user || empty($user['email'])) {
        return false;
    }
    
    // Get order details
    $order_sql = "SELECT * FROM orders WHERE id = ?";
    $order_stmt = mysqli_prepare($conn, $order_sql);
    mysqli_stmt_bind_param($order_stmt, "i", $order_id);
    mysqli_stmt_execute($order_stmt);
    $order_result = mysqli_stmt_get_result($order_stmt);
    $order = mysqli_fetch_assoc($order_result);
    mysqli_stmt_close($order_stmt);
    
    if (!$order) {
        return false;
    }
    
    // Generate email content
    $email_data = generate_notification_email($user, $order, $status);
    
    // Prepare email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Bazario <noreply@bazario.com>" . "\r\n";
    $headers .= "Reply-To: support@bazario.com" . "\r\n";
    
    // Send email
    $to = $user['email'];
    $subject = $email_data['subject'];
    $body = $email_data['body'];
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Generate email content based on status
 */
function generate_notification_email($user, $order, $status) {
    $notification = get_notification_for_status($status);
    
    $order_date = date('M d, Y', strtotime($order['placed_at']));
    $order_number = $order['order_number'];
    $amount = number_format($order['total_amount'], 2);
    $customer_name = $user['name'];
    
    $tracking_url = "https://bazario.local/track-order.php?order_id={$order['id']}";
    
    $body = "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .header { background-color: #001a33; color: white; padding: 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 30px; }
            .content h2 { color: #001a33; margin-bottom: 15px; }
            .status-badge { background-color: #3498db; color: white; padding: 8px 12px; border-radius: 4px; display: inline-block; margin: 10px 0; }
            .order-info { background-color: #f9f9f9; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #001a33; }
            .order-info p { margin: 8px 0; }
            .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e0e0e0; }
            .btn { background-color: #001a33; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>BAZARIO</h1>
                <p>Your Mobile Accessories Store</p>
            </div>
            
            <div class='content'>
                <h2>Hi {$customer_name},</h2>
                
                <p>{$notification['message']}</p>
                
                <div class='status-badge'>" . strtoupper($status) . "</div>
                
                <div class='order-info'>
                    <p><strong>Order Number:</strong> {$order_number}</p>
                    <p><strong>Order Date:</strong> {$order_date}</p>
                    <p><strong>Total Amount:</strong> ₹{$amount}</p>
                    <p><strong>Payment Method:</strong> Cash on Delivery (COD)</p>
                </div>
                
                <a href='{$tracking_url}' class='btn'>Track Your Order</a>
                
                <p style='color: #666; font-size: 14px;'>
                    If you have any questions, please contact our support team at <strong>support@bazario.com</strong>
                </p>
            </div>
            
            <div class='footer'>
                <p>© 2026 Bazario. All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return [
        'subject' => "Bazario Order Update: {$notification['title']} - {$order_number}",
        'body' => $body
    ];
}

// ======================================
// 5. Order Details & History Functions
// ======================================

/**
 * Get order with full details
 */
function get_order_with_details($conn, $order_id) {
    $sql = "SELECT o.*, 
            (SELECT GROUP_CONCAT(oi.product_name) FROM order_items oi WHERE oi.order_id = o.id) as items
            FROM orders o 
            WHERE o.id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $order;
    }
    return null;
}

/**
 * Get order status history
 */
function get_order_status_history($conn, $order_id) {
    $sql = "SELECT osh.*, u.name as changed_by_name 
            FROM order_status_history osh
            LEFT JOIN users u ON osh.changed_by = u.id
            WHERE osh.order_id = ?
            ORDER BY osh.timestamp DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $history = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $history;
    }
    return [];
}

/**
 * Calculate estimated delivery date based on current status
 */
function get_estimated_delivery_date($current_status) {
    $delays = [
        'Order Placed' => 4,      // 4 days
        'Confirmed' => 3,         // 3 days
        'Processing' => 3,        // 3 days
        'Packing' => 2,           // 2 days
        'Out for Delivery' => 0,  // Same day or next day
        'Delivered' => 0          // Already delivered
    ];
    
    $days = isset($delays[$current_status]) ? $delays[$current_status] : 4;
    return date('Y-m-d', strtotime("+{$days} days"));
}

// ======================================
// 6. Dashboard Statistics Functions
// ======================================

/**
 * Get order statistics for dashboard
 */
function get_order_statistics($conn, $user_id = null) {
    $where = $user_id ? "WHERE o.user_id = {$user_id}" : "";
    
    $sql = "SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status IN ('Processing', 'Packing', 'Out for Delivery') THEN 1 ELSE 0 END) as in_transit,
            SUM(CASE WHEN status = 'Order Placed' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(total_amount) as total_spent
            FROM orders o
            {$where}";
    
    $result = $conn->query($sql);
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Get recent orders for dashboard
 */
function get_recent_orders($conn, $limit = 5, $user_id = null) {
    $where = $user_id ? "WHERE o.user_id = {$user_id}" : "";
    
    $sql = "SELECT o.id, o.order_number, o.status, o.total_amount, o.placed_at, o.customer_name
            FROM orders o
            {$where}
            ORDER BY o.placed_at DESC
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $orders = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $orders;
    }
    return [];
}

// ======================================
// 7. Validation Functions
// ======================================

/**
 * Validate order status transition
 */
function is_valid_status_transition($current_status, $new_status) {
    $valid_transitions = [
        'Order Placed' => ['Confirmed', 'Cancelled'],
        'Confirmed' => ['Processing', 'Cancelled'],
        'Processing' => ['Packing', 'Cancelled'],
        'Packing' => ['Out for Delivery', 'Cancelled'],
        'Out for Delivery' => ['Delivered', 'Cancelled'],
        'Delivered' => [],
        'Cancelled' => []
    ];
    
    if (!isset($valid_transitions[$current_status])) {
        return false;
    }
    
    return in_array($new_status, $valid_transitions[$current_status]);
}

/**
 * Get status color for UI display
 */
function get_status_color($status) {
    $colors = [
        'Order Placed' => '#3498db',      // Blue
        'Confirmed' => '#3498db',         // Blue
        'Processing' => '#3498db',        // Blue
        'Packing' => '#e67e22',           // Orange
        'Out for Delivery' => '#e67e22',  // Orange
        'Delivered' => '#27ae60',         // Green
        'Cancelled' => '#e74c3c'          // Red
    ];
    
    return isset($colors[$status]) ? $colors[$status] : '#666666';
}

/**
 * Get status icon for UI display
 */
function get_status_icon($status) {
    $icons = [
        'Order Placed' => 'fa-box',
        'Confirmed' => 'fa-check-circle',
        'Processing' => 'fa-cogs',
        'Packing' => 'fa-boxes',
        'Out for Delivery' => 'fa-truck',
        'Delivered' => 'fa-check-circle',
        'Cancelled' => 'fa-times-circle'
    ];
    
    return isset($icons[$status]) ? $icons[$status] : 'fa-info-circle';
}

?>
