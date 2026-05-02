<?php
require_once "config.php";

$result = mysqli_query($conn, "DESCRIBE notifications");
if (!$result) {
    echo "Error: " . mysqli_error($conn) . "\n";
    exit;
}

echo "NOTIFICATIONS TABLE STRUCTURE:\n";
echo "─────────────────────────────────\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['Field']}: {$row['Type']} ({$row['Null']})\n";
}

mysqli_close($conn);
?>
