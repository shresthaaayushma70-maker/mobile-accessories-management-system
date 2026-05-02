# BAZARIO - QUICK START IMPLEMENTATION GUIDE

## 📋 Executive Checklist

### Pre-Implementation
```
☐ Backup existing database: mysqldump -u root -p Mproject > backup_$(date +%Y%m%d).sql
☐ Test on development/local server first
☐ Review all migration scripts
☐ Notify stakeholders about system updates
☐ Schedule maintenance window if needed
```

### Phase 1: Database Setup (15 minutes)
```
☐ Login to phpMyAdmin or MySQL console
☐ Run BAZARIO_DATABASE_MIGRATION.sql
☐ Verify tables created: notifications, order_status_history, notification_preferences
☐ Check data migration completed successfully
☐ Test database connections
```

### Phase 2: Backend Implementation (30 minutes)
```
☐ Copy notification_service.php to root directory
☐ Include notification_service.php in config.php
☐ Update checkout.php - remove payment method field
☐ Update admin_edit_product.php - remove house/apartment field
☐ Create update_order_status_admin.php for admin order updates
☐ Test notification creation functions
```

### Phase 3: Frontend Implementation (45 minutes)
```
☐ Update navbar.php with Bazario branding
☐ Replace/update CSS - use BAZARIO_STYLES.css
☐ Add Bazario logo to all pages
☐ Update page headers with brand colors
☐ Implement notification center UI
☐ Add order tracking page link to user dashboard
```

### Phase 4: Integration & Testing (60 minutes)
```
☐ Test order creation flow (no payment method)
☐ Test admin order status updates
☐ Verify notifications trigger correctly
☐ Test email sending (if configured)
☐ Verify SMS preferences save (optional)
☐ Test order tracking page display
☐ Verify Bazario branding throughout
☐ Test on mobile/responsive design
☐ Security audit - check SQL injection prevention
☐ Performance testing - check query times
```

### Phase 5: Go Live (30 minutes)
```
☐ Deploy to production server
☐ Clear browser cache
☐ Test all user flows
☐ Monitor for errors
☐ Have rollback plan ready
```

---

## 🚀 STEP-BY-STEP IMPLEMENTATION

### STEP 1: Database Migration

```bash
# Connect to MySQL
mysql -u root -p

# Select database
USE Mproject;

# Run the migration script
SOURCE /path/to/BAZARIO_DATABASE_MIGRATION.sql;

# Verify tables exist
SHOW TABLES;
```

**Expected Output Tables:**
- notifications ✓
- order_status_history ✓
- notification_preferences ✓
- system_settings ✓

---

### STEP 2: Update configuration file

**File: config.php**

Add at the end:
```php
// Include notification service
require_once 'notification_service.php';

// Email configuration for notifications
define('NOTIFICATION_EMAIL', 'noreply@bazario.com');
define('NOTIFICATION_FROM', 'Bazario');
define('SMTP_HOST', 'localhost'); // or your email service
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com'); // if needed
define('SMTP_PASS', 'your-password'); // if needed

// SMS Configuration (Optional - set only if using SMS)
// define('SMS_PROVIDER', 'twilio'); // or 'nexmo', 'aws'
// define('SMS_API_KEY', 'your-api-key');
// define('SMS_PHONE_FROM', 'YOUR_PHONE_NUMBER');
```

---

### STEP 3: Update checkout.php

**Remove these lines:**
```php
// DELETE:
$payment_method = sanitize_input($_POST['payment_method']);
if (!in_array($payment_method, ['COD', 'Online'])) {
    $error_msg = "Invalid payment method selected";
}

// DELETE the payment method form field in HTML
```

**Keep only:**
```php
// Payment method automatically set to COD
$payment_method = 'COD';
```

---

### STEP 4: Create Admin Order Status Update Page

**File: admin_update_order_status.php**

```php
<?php
session_start();

// Check admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize_input($_POST['status']);
    $notes = sanitize_input($_POST['notes'] ?? '');
    $admin_id = $_SESSION['user_id'];
    
    // Validate status
    $valid_statuses = ['Order Placed', 'Confirmed', 'Processing', 'Packing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    
    if (!in_array($new_status, $valid_statuses)) {
        die("Invalid status");
    }
    
    // Update order status
    $result = update_order_status($conn, $order_id, $new_status, $admin_id, $notes);
    
    if ($result['success']) {
        header("Location: orders.php?success=status_updated");
    } else {
        header("Location: orders.php?error=" . urlencode($result['message']));
    }
}
?>
```

---

### STEP 5: CSS Implementation

**File: admin_dashboard.php (or any page using styles)**

**Add to <head>:**
```html
<link rel="stylesheet" href="BAZARIO_STYLES.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
```

**Update navbar HTML:**
```html
<nav class="navbar">
    <div class="navbar-container">
        <a href="dashboard.php" class="bazario-logo">
            <i class="fas fa-shopping-bag"></i>
            BAZARIO
        </a>
        <ul class="nav-menu">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="user_dashboard.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="notifications.php" class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
```

---

### STEP 6: Notification Integration

**Update orders.php - Add after order status changes:**

```php
// After successfully creating an order
$notification_created = create_notification(
    $conn,
    $user_id,
    $order_id,
    'order_placed',
    'Order Placed',
    'Your order #' . $order_number . ' has been placed successfully!',
    'fa-box'
);

// Send initial email notification
send_notification_email($conn, $user_id, $order_id, 'Order Placed');
```

---

### STEP 7: Testing Checklist

**User Flow Tests:**
```
☐ User can register and login
☐ User can view products
☐ User can place order (no payment method shown)
☐ Order created with status "Order Placed"
☐ Notification created and visible
☐ User can navigate to track order
☐ Order timeline displays correctly
☐ User can update notification preferences
```

**Admin Flow Tests:**
```
☐ Admin can login
☐ Admin can view all orders
☐ Admin can update order status
☐ Order status history records created
☐ Notifications sent on each status change
☐ Email notifications received (if configured)
☐ Admin can see notification logs
```

**Data Integrity Tests:**
```
☐ No orders with payment_method = 'Online'
☐ No orders with house_number field
☐ Notifications linked to correct orders
☐ Status timestamps recorded correctly
☐ User preferences saved correctly
```

---

## 🔧 CONFIGURATION OPTIONS

### Email Configuration (Gmail Example)

**Update config.php:**
```php
// Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-16-char-app-password'); // Generate in Gmail settings
define('SMTP_ENCRYPTION', 'tls');
```

### SMS Configuration (Twilio Example)

**Add to config.php:**
```php
// Twilio SMS
require_once 'vendor/autoload.php';
define('TWILIO_SID', 'your-account-sid');
define('TWILIO_TOKEN', 'your-auth-token');
define('TWILIO_FROM', '+1234567890');
```

---

## 📊 DATABASE QUERIES REFERENCE

### Get Today's Orders
```sql
SELECT * FROM orders 
WHERE DATE(placed_at) = CURDATE() 
ORDER BY placed_at DESC;
```

### Get Undelivered Orders
```sql
SELECT * FROM orders 
WHERE status NOT IN ('Delivered', 'Cancelled')
ORDER BY placed_at DESC;
```

### Get Average Delivery Time
```sql
SELECT 
  AVG(DATEDIFF(delivered_at, placed_at)) as avg_days
FROM orders 
WHERE status = 'Delivered';
```

### Get Notification Statistics
```sql
SELECT 
  type,
  COUNT(*) as total,
  SUM(is_read) as opened
FROM notifications
GROUP BY type;
```

### Get User's Recent Orders
```sql
SELECT 
  order_number, status, total_amount, placed_at
FROM orders 
WHERE user_id = ? 
ORDER BY placed_at DESC 
LIMIT 10;
```

---

## 🎨 BRANDING COLORS - QUICK REFERENCE

```
Primary Navy Blue:        #001a33
Navy Accent:              #003366
Light Gray (Background):  #f5f5f5
White:                    #ffffff
Text Dark:                #333333
Text Light:               #666666
Success Green:            #27ae60
Processing Blue:          #3498db
Warning Orange:           #e67e22
Alert Red:                #e74c3c
```

---

## 📱 RESPONSIVE BREAKPOINTS

```css
Desktop:   1200px+
Tablet:    768px - 1199px
Mobile:    480px - 767px
Small:     < 480px
```

All styles in BAZARIO_STYLES.css include responsive media queries.

---

## 🚨 TROUBLESHOOTING

### Issue: "Notifications table not found"
**Solution:** Run BAZARIO_DATABASE_MIGRATION.sql again, or manually run:
```sql
SHOW TABLES LIKE 'notifications';
```

### Issue: "Payment method still showing"
**Solution:** Check checkout.php hasn't been cached. Clear browser cache and verify the file was updated correctly.

### Issue: "Emails not sending"
**Solution:** 
- Check mail() function is enabled in php.ini
- For Gmail, generate 16-char app password
- Check firewall isn't blocking SMTP port 587

### Issue: "Order tracking page shows blank"
**Solution:**
- Verify track_order.php is in the correct directory
- Check user_id matches order user_id (security check)
- Look for PHP errors in browser console

### Issue: "Notification preferences not saving"
**Solution:**
- Check user has notification_preferences record created
- Verify form method is POST
- Check for database errors in error logs

---

## 📋 DEPLOY CHECKLIST

```
Pre-Deployment:
☐ Database backup taken
☐ All code reviewed
☐ Tests passed locally
☐ CSS/JS files minified (optional)
☐ Sensitive data removed from code

Deployment:
☐ Upload all files to server
☐ Run migration script
☐ Update config.php credentials
☐ Set file permissions (644 for files, 755 for dirs)
☐ Clear any caching
☐ Test critical flows

Post-Deployment:
☐ Monitor error logs
☐ Check email sending works
☐ Verify notification creation
☐ Test order status updates
☐ User feedback collection
```

---

## 📞 SUPPORT & CUSTOMIZATION

### Basic Customizations:
- **Change colors:** Update CSS variables in BAZARIO_STYLES.css
- **Change logo:** Update navbar HTML in navbar.php
- **Change email templates:** Update generate_notification_email() in notification_service.php
- **Add more statuses:** Update valid_statuses array in functions

### Advanced Customizations:
- Integrate with SMS provider (Twilio, AWS SNS, etc.)
- Add order notifications to admin dashboard
- Create analytics/reporting page
- Implement customer rating after delivery
- Add promotional notifications

---

## ✅ SUCCESS CRITERIA

Your Bazario implementation is successful when:

1. ✓ All orders use COD only (no "Online" payment option)
2. ✓ No "house_number" or apartment fields in order form
3. ✓ Order status flows through: Placed → Confirmed → Processing → Packing → Out for Delivery → Delivered
4. ✓ Each status change creates a notification
5. ✓ Users can view order tracking with timeline
6. ✓ Navy blue (#001a33) and white theme applied throughout
7. ✓ "Bazario" branding visible on all pages
8. ✓ Notification preferences can be customized by users
9. ✓ Mobile responsive design works on all devices
10. ✓ No database errors in logs

---

## 📞 CONTACT & SUPPORT

**Created:** January 2026
**Version:** 1.0
**Last Updated:** Jan 2026

For issues or questions about implementation, refer to:
- BAZARIO_IMPLEMENTATION_PLAN.md (Full documentation)
- Code comments in BAZARIO_STYLES.css
- notification_service.php (API documentation)
- BAZARIO_DATABASE_MIGRATION.sql (Schema reference)

---

**Happy deploying! Welcome to Bazario! 🚀**
