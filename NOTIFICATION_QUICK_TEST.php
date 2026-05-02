<?php
/**
 * BAZARIO - Quick Test Checklist
 * One-page test guide for notification system
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notification System - Quick Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #001a33;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        .test-section h2 {
            margin-top: 0;
            color: #001a33;
        }
        .step {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        .step-num {
            display: inline-block;
            background: #3498db;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
            font-weight: bold;
        }
        .check {
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }
        .warning {
            color: #dc3545;
            font-weight: bold;
            margin-right: 10px;
        }
        .link {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .link:hover {
            background: #2980b9;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 BAZARIO Notification System - Quick Test Checklist</h1>
        
        <!-- Pre-requisites -->
        <div class="test-section info">
            <h2>📋 Pre-requisites</h2>
            <div class="step">
                <span class="check">✓</span> You have access to both user and admin accounts
            </div>
            <div class="step">
                <span class="check">✓</span> You can access localhost/mobile-accessories
            </div>
            <div class="step">
                <span class="check">✓</span> XAMPP is running with MySQL
            </div>
        </div>

        <!-- Database Check -->
        <div class="test-section">
            <h2>1️⃣ Database Schema Check</h2>
            <p>Verify all required tables and structures exist.</p>
            <a href="check_notification_schema.php" class="link" target="_blank">
                🔍 Run Database Schema Check
            </a>
            <div class="step">
                <strong>Expected Results:</strong>
                <ul>
                    <li><span class="check">✓</span> notifications table exists</li>
                    <li><span class="check">✓</span> notification_preferences table exists</li>
                    <li><span class="check">✓</span> orders table exists with status field</li>
                    <li><span class="check">✓</span> users table exists</li>
                </ul>
            </div>
        </div>

        <!-- Notification Creation Test -->
        <div class="test-section">
            <h2>2️⃣ Notification Creation Test</h2>
            <p>Test if notifications are being created when orders are placed.</p>
            <a href="test_notification_creation.php" class="link" target="_blank">
                📝 Run Notification Creation Test
            </a>
            <div class="step">
                <strong>Expected Results:</strong>
                <ul>
                    <li><span class="check">✓</span> Test user found</li>
                    <li><span class="check">✓</span> Test order found</li>
                    <li><span class="check">✓</span> Notifications exist for order</li>
                    <li><span class="check">✓</span> Notification preferences loaded</li>
                    <li><span class="check">✓</span> Test notification created</li>
                </ul>
            </div>
        </div>

        <!-- Complete System Test -->
        <div class="test-section">
            <h2>3️⃣ Complete System Debugger</h2>
            <p>Full diagnostic of the entire notification system.</p>
            <a href="debug_notifications.php" class="link" target="_blank">
                🔧 Open System Debugger
            </a>
            <div class="step">
                <strong>Tests Included:</strong>
                <ul>
                    <li><span class="check">✓</span> Database connection</li>
                    <li><span class="check">✓</span> Orders & notifications count</li>
                    <li><span class="check">✓</span> User preferences status</li>
                    <li><span class="check">✓</span> Recent notifications list</li>
                    <li><span class="check">✓</span> Test notification creation</li>
                </ul>
            </div>
        </div>

        <!-- Manual E2E Test -->
        <div class="test-section success">
            <h2>4️⃣ Manual End-to-End Test</h2>
            <p>Test the complete notification flow from order to delivery.</p>
            
            <h3>Phase 1: User Places Order</h3>
            <div class="step">
                <span class="step-num">1</span> Login as regular user
            </div>
            <div class="step">
                <span class="step-num">2</span> Go to Shop and add a product to cart
            </div>
            <div class="step">
                <span class="step-num">3</span> Checkout and place order
            </div>
            <div class="step">
                <span class="step-num">4</span> Go to Notifications page
                <ul style="margin: 5px 0 0 0;">
                    <li><span class="check">✓</span> "Order Placed" notification should appear</li>
                    <li><span class="check">✓</span> Notification should show order timestamp</li>
                </ul>
            </div>

            <h3>Phase 2: Admin Updates Status (in new browser tab)</h3>
            <div class="step">
                <span class="step-num">5</span> Login as admin (in new tab/browser)
            </div>
            <div class="step">
                <span class="step-num">6</span> Go to Order Management
            </div>
            <div class="step">
                <span class="step-num">7</span> Find the order from Step 3
            </div>
            <div class="step">
                <span class="step-num">8</span> Click "Update Status" button
            </div>
            <div class="step">
                <span class="step-num">9</span> Select "Processing" status
            </div>
            <div class="step">
                <span class="step-num">10</span> Click "Update Status"
                <ul style="margin: 5px 0 0 0;">
                    <li><span class="check">✓</span> Status badge should update immediately</li>
                    <li><span class="check">✓</span> Success message should appear</li>
                </ul>
            </div>

            <h3>Phase 3: User Sees Update (go back to user tab)</h3>
            <div class="step">
                <span class="step-num">11</span> Stay on Orders page (auto-refresh is checking every 5 sec)
            </div>
            <div class="step">
                <span class="step-num">12</span> Within 5 seconds, you should see:
                <ul style="margin: 5px 0 0 0;">
                    <li><span class="check">✓</span> Blue notification banner appears at top</li>
                    <li><span class="check">✓</span> Message: "New order status update! Refreshing page..."</li>
                    <li><span class="check">✓</span> Page automatically reloads</li>
                    <li><span class="check">✓</span> Order status shows "Processing"</li>
                </ul>
            </div>

            <h3>Phase 4: Check Notifications</h3>
            <div class="step">
                <span class="step-num">13</span> Click Notifications bell icon
            </div>
            <div class="step">
                <span class="step-num">14</span> Go to Notifications page
                <ul style="margin: 5px 0 0 0;">
                    <li><span class="check">✓</span> "Processing" notification should appear</li>
                    <li><span class="check">✓</span> Should show: "Your order is being processed"</li>
                    <li><span class="check">✓</span> Should show recent timestamp</li>
                </ul>
            </div>

            <h3>Phase 5: Test Preferences Save</h3>
            <div class="step">
                <span class="step-num">15</span> Click on "Preferences" tab
            </div>
            <div class="step">
                <span class="step-num">16</span> Toggle some email notifications ON/OFF
            </div>
            <div class="step">
                <span class="step-num">17</span> Click "Save Preferences" button
                <ul style="margin: 5px 0 0 0;">
                    <li><span class="check">✓</span> Button should show "Saving..."</li>
                    <li><span class="check">✓</span> Success message: "✓ Saved successfully!"</li>
                </ul>
            </div>
            <div class="step">
                <span class="step-num">18</span> Refresh page (F5)
                <ul style="margin: 5px 0 0 0;">
                    <li><span class="check">✓</span> Settings should persist (not reset)</li>
                </ul>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="test-section error">
            <h2>🛠️ Troubleshooting</h2>
            
            <h3>If Status Update Doesn't Show:</h3>
            <ol>
                <li>Check browser DevTools (F12) → Console for JavaScript errors</li>
                <li>Check Network tab - should see requests every 5 seconds</li>
                <li>Verify database was updated: Use debug_notifications.php</li>
                <li>Clear browser cache and reload</li>
            </ol>

            <h3>If Notifications Don't Create:</h3>
            <ol>
                <li>Run test_notification_creation.php</li>
                <li>Check notification_preferences table has entries</li>
                <li>Verify email_on_* fields are set to 1</li>
                <li>Check PHP error logs</li>
            </ol>

            <h3>If Preferences Save Fails:</h3>
            <ol>
                <li>Open browser DevTools (F12) → Network tab</li>
                <li>Click Save and watch for errors</li>
                <li>Check if user has notification_preferences record</li>
                <li>Run debug_notifications.php to test</li>
            </ol>
        </div>

        <!-- Summary -->
        <div class="test-section info">
            <h2>📊 Test Summary Checklist</h2>
            <div class="step">
                <input type="checkbox"> Database schema is correct
            </div>
            <div class="step">
                <input type="checkbox"> Notifications are created when order placed
            </div>
            <div class="step">
                <input type="checkbox"> Admin can update order status
            </div>
            <div class="step">
                <input type="checkbox"> User sees status update automatically (within 5 sec)
            </div>
            <div class="step">
                <input type="checkbox"> User receives notification for status change
            </div>
            <div class="step">
                <input type="checkbox"> User can save preference settings
            </div>
            <div class="step">
                <input type="checkbox"> Preferences persist after page refresh
            </div>
            <div class="step">
                <input type="checkbox"> Notification banner shows on status update
            </div>
            <div class="step">
                <input type="checkbox"> Page auto-reloads when new notification arrives
            </div>
            <div class="step">
                <input type="checkbox"> All timestamps show correctly
            </div>
        </div>

        <!-- Quick Links -->
        <div class="test-section info">
            <h2>🔗 Quick Links</h2>
            <div class="step">
                <a href="check_notification_schema.php" class="link" target="_blank">1. Database Schema Check</a>
            </div>
            <div class="step">
                <a href="test_notification_creation.php" class="link" target="_blank">2. Notification Creation Test</a>
            </div>
            <div class="step">
                <a href="debug_notifications.php" class="link" target="_blank">3. System Debugger</a>
            </div>
            <div class="step">
                <a href="orders.php" class="link">4. User Orders Page</a>
            </div>
            <div class="step">
                <a href="admin_orders_manage.php" class="link">5. Admin Order Management</a>
            </div>
            <div class="step">
                <a href="notifications.php" class="link">6. Notifications Center</a>
            </div>
        </div>
    </div>
</body>
</html>
