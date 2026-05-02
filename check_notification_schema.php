<?php
/**
 * BAZARIO - Check Database Schema
 * Verify all required tables exist and have correct structure
 */

require_once "config.php";

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🗄️ Database Schema Check</h2>";
echo "<hr>";

// Check if notifications table exists
echo "<h3>1. Checking 'notifications' Table</h3>";
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if (mysqli_num_rows($tables_check) > 0) {
    echo "✅ notifications table exists<br><br>";
    
    // Get table structure
    $columns = mysqli_query($conn, "DESCRIBE notifications");
    echo "<table style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th style='border: 1px solid #ddd; padding: 8px;'>Field</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Key</th></tr>";
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Field']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Type']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Null']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ notifications table NOT found!<br>";
}

echo "<hr>";

// Check notification_preferences table
echo "<h3>2. Checking 'notification_preferences' Table</h3>";
$tables_check2 = mysqli_query($conn, "SHOW TABLES LIKE 'notification_preferences'");
if (mysqli_num_rows($tables_check2) > 0) {
    echo "✅ notification_preferences table exists<br><br>";
    
    // Get table structure
    $columns2 = mysqli_query($conn, "DESCRIBE notification_preferences");
    echo "<table style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th style='border: 1px solid #ddd; padding: 8px;'>Field</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    while ($col = mysqli_fetch_assoc($columns2)) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Field']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Type']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Null']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ notification_preferences table NOT found!<br>";
}

echo "<hr>";

// Check orders table
echo "<h3>3. Checking 'orders' Table Status Field</h3>";
$orders_check = mysqli_query($conn, "DESCRIBE orders");
$has_status = false;
while ($col = mysqli_fetch_assoc($orders_check)) {
    if ($col['Field'] === 'status') {
        $has_status = true;
        echo "✅ status field exists in orders table<br>";
        echo "Type: {$col['Type']}<br>";
        echo "Default: " . ($col['Default'] ? $col['Default'] : 'None') . "<br>";
    }
}
if (!$has_status) {
    echo "❌ status field NOT found in orders table!<br>";
}

echo "<hr>";
echo "<h3>✅ Schema Check Complete!</h3>";

mysqli_close($conn);
?>
