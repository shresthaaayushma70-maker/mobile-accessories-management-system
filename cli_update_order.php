<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/notification_service.php';

$order_id = $argv[1] ?? 1;
$new_status = $argv[2] ?? 'Delivered';
$admin_id = $argv[3] ?? 1;
$note = $argv[4] ?? 'CLI test update';

$result = update_order_status($conn, (int)$order_id, $new_status, (int)$admin_id, $note);
echo "Result:\n";
var_export($result);
echo "\n";
?>