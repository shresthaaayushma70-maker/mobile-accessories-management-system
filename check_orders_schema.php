<?php
require_once "config.php";

echo "ORDERS TABLE STRUCTURE:\n";
$result = mysqli_query($conn, "DESCRIBE orders");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['Field']}: {$row['Type']} ({$row['Null']})\n";
}

echo "\nORDER_STATUS_HISTORY TABLE STRUCTURE:\n";
$result = mysqli_query($conn, "DESCRIBE order_status_history");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['Field']}: {$row['Type']} ({$row['Null']})\n";
}

mysqli_close($conn);
?>