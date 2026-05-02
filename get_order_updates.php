<?php
/**
 * BAZARIO - Check for Order Updates
 * AJAX endpoint to check if user has new notifications/order status updates
 */

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['notification_count' => 0, 'success' => false]);
    exit;
}

require_once "config.php";
require_once "notification_service.php";

$user_id = $_SESSION['user_id'];

// Get current unread notification count
$unread_count = get_unread_notifications_count($conn, $user_id);

// Get recent notifications
$recent_notifications = get_user_notifications($conn, $user_id, 10, 0);

// Build response
$response = [
    'success' => true,
    'notification_count' => $unread_count,
    'recent_notifications' => $recent_notifications
];

header('Content-Type: application/json');
echo json_encode($response);

mysqli_close($conn);
?>
