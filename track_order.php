<?php
/**
 * BAZARIO - Order Tracking Page
 * Display order details with interactive timeline and tracking information
 * File: track_order.php
 */

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: minor.php");
    exit;
}

require_once "config.php";
require_once "notification_service.php";

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Get order details
$order_sql = "SELECT o.*, u.name as user_name FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.id = ? AND (o.user_id = ? OR ? = 1)";

$order_stmt = mysqli_prepare($conn, $order_sql);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 1 : 0;
mysqli_stmt_bind_param($order_stmt, "iii", $order_id, $user_id, $is_admin);
mysqli_stmt_execute($order_stmt);
$order_result = mysqli_stmt_get_result($order_stmt);
$order = mysqli_fetch_assoc($order_result);
mysqli_stmt_close($order_stmt);

if (!$order) {
    die("Order not found");
}

// Get order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = mysqli_prepare($conn, $items_sql);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);
$items = [];
while ($row = mysqli_fetch_assoc($items_result)) {
    $items[] = $row;
}
mysqli_stmt_close($items_stmt);

// Get status history
$history = get_order_status_history($conn, $order_id);

// Calculate delivery days if delivered
$delivery_days = null;
if ($order['status'] === 'Delivered' && $order['delivered_at']) {
    $placed_time = strtotime($order['placed_at']);
    $delivered_time = strtotime($order['delivered_at']);
    $delivery_days = floor(($delivered_time - $placed_time) / 86400);
}

// Estimate delivery date
$estimated_delivery = get_estimated_delivery_date($order['status']);

// Status styling
$status_colors = [
    'Order Placed' => ['color' => '#3498db', 'icon' => 'fa-box'],
    'Confirmed' => ['color' => '#3498db', 'icon' => 'fa-check-circle'],
    'Processing' => ['color' => '#3498db', 'icon' => 'fa-cogs'],
    'Packing' => ['color' => '#e67e22', 'icon' => 'fa-boxes'],
    'Out for Delivery' => ['color' => '#e67e22', 'icon' => 'fa-truck'],
    'Delivered' => ['color' => '#27ae60', 'icon' => 'fa-check-circle'],
    'Cancelled' => ['color' => '#e74c3c', 'icon' => 'fa-times-circle']
];

$current_style = $status_colors[$order['status']] ?? ['color' => '#666', 'icon' => 'fa-info-circle'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Order - Bazario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="BAZARIO_STYLES.css">
    <style>
        .order-tracking-header {
            background: linear-gradient(135deg, #001a33 0%, #003366 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .timeline-vertical {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline-vertical::before {
            content: '';
            position: absolute;
            left: 39px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #27ae60 0%, #27ae60 40%, #e0e0e0 40%);
        }
        
        .timeline-item {
            margin-bottom: 30px;
            position: relative;
            padding-left: 100px;
        }
        
        .timeline-marker {
            position: absolute;
            left: 0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            background-color: #e0e0e0;
        }
        
        .timeline-item.completed .timeline-marker {
            background-color: #27ae60;
        }
        
        .timeline-item.active .timeline-marker {
            background-color: #3498db;
            animation: pulse 2s infinite;
        }
        
        .timeline-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .timeline-content h4 {
            color: #001a33;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .timeline-content .status-time {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .status-badge-large {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .delivery-info {
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #001a33;
            margin: 20px 0;
        }
        
        .delivery-stat {
            text-align: center;
            padding: 10px;
        }
        
        .delivery-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #001a33;
        }
        
        .delivery-stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .order-items-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        
        .order-items-table th {
            background-color: #001a33;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-items-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .address-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #001a33;
            margin-top: 15px;
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation is handled elsewhere -->
    <div class="main-container">
        <!-- Header -->
        <div class="order-tracking-header">
            <div class="content-wrapper">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 style="margin-bottom: 5px;">Order Tracking</h1>
                        <p style="opacity: 0.9; margin: 0;">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                    </div>
                    <div class="status-badge-large" style="background-color: <?php echo $current_style['color']; ?>;">
                        <?php echo strtoupper($order['status']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="row">
                <!-- Main Tracking Timeline -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h2 style="margin: 0;">Order Journey</h2>
                        </div>
                        <div class="card-body">
                            <div class="timeline-vertical">
                                <!-- Order Placed -->
                                <div class="timeline-item <?php echo ($order['status'] !== 'Order Placed' ? 'completed' : 'active'); ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Order Placed</h4>
                                        <div class="status-time">
                                            <?php echo (!empty($order['placed_at']) && $order['placed_at'] !== '0000-00-00 00:00:00') ? date('M d, Y \a\t h:i A', strtotime($order['placed_at'])) : 'Processing'; ?>
                                        </div>
                                        <p style="margin: 0; color: #666;">Your order has been successfully placed.</p>
                                    </div>
                                </div>

                                <!-- Order Confirmed -->
                                <div class="timeline-item <?php echo (strpos('Confirmed,Processing,Packing,Out for Delivery,Delivered', $order['status']) !== false ? 'completed' : ''); echo ($order['status'] === 'Confirmed' ? ' active' : ''); ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Order Confirmed</h4>
                                        <div class="status-time">
                                            <?php echo (!empty($order['confirmed_at']) && $order['confirmed_at'] !== '0000-00-00 00:00:00') ? date('M d, Y \a\t h:i A', strtotime($order['confirmed_at'])) : 'Awaiting'; ?>
                                        </div>
                                        <p style="margin: 0; color: #666;">Payment received. Your order is confirmed.</p>
                                    </div>
                                </div>

                                <!-- Processing -->
                                <div class="timeline-item <?php echo (strpos('Processing,Packing,Out for Delivery,Delivered', $order['status']) !== false ? 'completed' : ''); echo ($order['status'] === 'Processing' ? ' active' : ''); ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Processing</h4>
                                        <div class="status-time">
                                            <?php echo (!empty($order['processing_at']) && $order['processing_at'] !== '0000-00-00 00:00:00') ? date('M d, Y \a\t h:i A', strtotime($order['processing_at'])) : 'Awaiting'; ?>
                                        </div>
                                        <p style="margin: 0; color: #666;">We're preparing your order. Items are being picked.</p>
                                    </div>
                                </div>

                                <!-- Packing -->
                                <div class="timeline-item <?php echo (strpos('Packing,Out for Delivery,Delivered', $order['status']) !== false ? 'completed' : ''); echo ($order['status'] === 'Packing' ? ' active' : ''); ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Packing</h4>
                                        <div class="status-time">
                                            <?php echo (!empty($order['packing_at']) && $order['packing_at'] !== '0000-00-00 00:00:00') ? date('M d, Y \a\t h:i A', strtotime($order['packing_at'])) : 'Awaiting'; ?>
                                        </div>
                                        <p style="margin: 0; color: #666;">Your order is being packed and labeled for shipment.</p>
                                    </div>
                                </div>

                                <!-- Out for Delivery -->
                                <div class="timeline-item <?php echo (strpos('Out for Delivery,Delivered', $order['status']) !== false ? 'completed' : ''); echo ($order['status'] === 'Out for Delivery' ? ' active' : ''); ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Out for Delivery</h4>
                                        <div class="status-time">
                                            <?php echo (!empty($order['shipped_at']) && $order['shipped_at'] !== '0000-00-00 00:00:00') ? date('M d, Y \a\t h:i A', strtotime($order['shipped_at'])) : 'Awaiting'; ?>
                                        </div>
                                        <p style="margin: 0; color: #666;">Your order is on its way!<?php echo (!empty($order['tracking_number'])) ? ' Tracking: ' . htmlspecialchars($order['tracking_number']) : ''; ?></p>
                                    </div>
                                </div>

                                <!-- Delivered -->
                                <div class="timeline-item <?php echo ($order['status'] === 'Delivered' ? 'completed active' : ''); ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Delivered</h4>
                                        <div class="status-time">
                                            <?php echo (!empty($order['delivered_at']) && $order['delivered_at'] !== '0000-00-00 00:00:00') ? date('M d, Y \a\t h:i A', strtotime($order['delivered_at'])) : 'Awaiting'; ?>
                                        </div>
                                        <p style="margin: 0; color: #666;">Your order has been successfully delivered!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order Items</h3>
                        </div>
                        <div class="card-body">
                            <table class="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                        <td><strong>₹<?php echo number_format($item['subtotal'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div style="text-align: right; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                                <h4 style="color: #001a33; margin: 0;">
                                    Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <!-- Delivery Info Box -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 style="margin: 0;">Delivery Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="delivery-info">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="delivery-stat">
                                            <div class="delivery-stat-value">
                                                <?php echo $delivery_days ?? '—'; ?>
                                            </div>
                                            <div class="delivery-stat-label">Days Taken</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="delivery-stat">
                                            <div class="delivery-stat-value">
                                                <?php echo date('d M', strtotime($estimated_delivery)); ?>
                                            </div>
                                            <div class="delivery-stat-label">Est. Delivery</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 20px;">
                                <label style="color: #001a33; font-weight: 600; margin-bottom: 10px; display: block;">
                                    Delivery Address
                                </label>
                                <div class="address-box">
                                    <p style="margin: 0; color: #333;">
                                        <?php echo !empty($order['address_line1']) ? htmlspecialchars($order['address_line1']) : 'Address not provided'; ?>
                                        <?php if (!empty($order['address_line2'])): ?>
                                            <br><?php echo htmlspecialchars($order['address_line2']); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($order['street'])): ?><br><?php echo htmlspecialchars($order['street']); ?><?php endif; ?>
                                        <?php if (!empty($order['city']) || !empty($order['state']) || !empty($order['postal_code'])): ?><br><?php echo htmlspecialchars($order['city'] ?? ''); ?><?php echo !empty($order['state']) ? ', ' . htmlspecialchars($order['state']) : ''; ?> <?php echo htmlspecialchars($order['postal_code'] ?? ''); ?><?php endif; ?>
                                        <?php if (!empty($order['country'])): ?><br><?php echo htmlspecialchars($order['country']); ?><?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                <label style="color: #666; margin-bottom: 5px;">Order Number</label>
                                <p style="margin: 0; font-weight: 600; color: #001a33;">
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </p>
                            </div>

                            <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                <label style="color: #666; margin-bottom: 5px;">Order Date</label>
                                <p style="margin: 0; font-weight: 600; color: #001a33;">
                                    <?php echo (!empty($order['placed_at']) && $order['placed_at'] !== '0000-00-00 00:00:00') ? date('d M, Y', strtotime($order['placed_at'])) : 'Date not available'; ?>
                                </p>
                            </div>

                            <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                <label style="color: #666; margin-bottom: 5px;">Payment Method</label>
                                <p style="margin: 0; font-weight: 600; color: #001a33;">
                                    Cash on Delivery (COD)
                                </p>
                            </div>

                            <div style="margin-bottom: 0;">
                                <label style="color: #666; margin-bottom: 5px;">Contact Number</label>
                                <p style="margin: 0; font-weight: 600; color: #001a33;">
                                    <?php echo htmlspecialchars($order['customer_phone']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Need Help Box -->
                    <div class="card">
                        <div class="card-header" style="background-color: #e67e22;">
                            <h3 style="margin: 0; color: white;">Need Help?</h3>
                        </div>
                        <div class="card-body">
                            <p>If you have any questions about your order, please contact our support team.</p>
                            <a href="mailto:support@bazario.com" class="btn btn-primary btn-block" style="margin-top: 10px;">
                                <i class="fas fa-envelope"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
