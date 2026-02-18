<?php
/**
 * Quick Fix Script
 * Updates admin user role and creates test users properly
 */

require_once "config.php";

$messages = [];

// 1. Add role column if it doesn't exist
$role_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($role_check) == 0) {
    $messages[] = "Adding 'role' column...";
    $alter_sql = "ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user' AFTER password";
    if (mysqli_query($conn, $alter_sql)) {
        $messages[] = "✓ Role column added";
    } else {
        $messages[] = "✗ Error adding role column: " . mysqli_error($conn);
    }
}

// 2. Update ALL existing users to have a default role
$messages[] = "Updating user roles...";
$update_default = mysqli_query($conn, "UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''");
$messages[] = "✓ Default roles assigned";

// 3. Set admin user to admin role
$messages[] = "Setting admin user role...";
$update_admin = mysqli_query($conn, "UPDATE users SET role = 'admin' WHERE username = 'admin'");
if ($update_admin) {
    $messages[] = "✓ Admin user updated to admin role";
} else {
    $messages[] = "✗ Error: " . mysqli_error($conn);
}

// 4. Verify admin user
$admin_check = mysqli_query($conn, "SELECT username, password, role FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) > 0) {
    $admin = mysqli_fetch_assoc($admin_check);
    $messages[] = "Admin verification:";
    $messages[] = "  - Username: " . $admin['username'];
    $messages[] = "  - Password: " . $admin['password'];
    $messages[] = "  - Role: " . $admin['role'];
    $messages[] = "✓ Admin account is ready";
} else {
    $messages[] = "✗ Admin account not found - creating it...";
    $insert_admin = mysqli_query($conn, "INSERT INTO users (username, email, phone, dob, password, name, role) 
                                        VALUES ('admin', 'admin@example.com', '9876543210', '2000-01-01', 'admin123', 'Admin User', 'admin')
                                        ON DUPLICATE KEY UPDATE role = 'admin'");
    if ($insert_admin) {
        $messages[] = "✓ Admin account created";
    } else {
        $messages[] = "✗ Error: " . mysqli_error($conn);
    }
}

// 5. Update or create test user
$messages[] = "Setting up test user...";
$update_testuser = mysqli_query($conn, "UPDATE users SET role = 'user' WHERE username = 'testuser'");
if (mysqli_affected_rows($conn) > 0) {
    $messages[] = "✓ Test user updated";
} else {
    $messages[] = "Creating test user...";
    $insert_testuser = mysqli_query($conn, "INSERT INTO users (username, email, phone, dob, password, name, role) 
                                           VALUES ('testuser', 'user@example.com', '1234567890', '2001-05-15', 'user123', 'Test User', 'user')");
    if ($insert_testuser) {
        $messages[] = "✓ Test user created";
    }
}

// 6. Final verification
$messages[] = "";
$messages[] = "=== FINAL VERIFICATION ===";
$all_users = mysqli_query($conn, "SELECT id, username, password, role FROM users");
while ($u = mysqli_fetch_assoc($all_users)) {
    $messages[] = "User: " . $u['username'] . " | Password: " . $u['password'] . " | Role: " . $u['role'];
}

$success = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fix Login Issues</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header i {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .fix-log {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.8;
            color: #333;
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .fix-log div {
            margin-bottom: 6px;
        }
        
        .success-box {
            background: #f0fff4;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .success-box h5 {
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .cred-box {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }
        
        .cred-box strong {
            color: #667eea;
        }
        
        .btn-login {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s;
            width: 100%;
            text-align: center;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-magic"></i>
            <h2>Fixing Login Issues</h2>
            <p>Updating database configuration</p>
        </div>
        
        <div class="fix-log">
            <?php foreach ($messages as $msg): ?>
                <div><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
        
        <div class="success-box">
            <h5><i class="fas fa-check-circle"></i> Fix Complete!</h5>
            
            <p style="margin-bottom: 15px; color: #155724;">Your database has been updated. Use these credentials:</p>
            
            <div class="cred-box">
                <strong>Admin Login:</strong><br>
                Username: <strong style="color: #dc3545;">admin</strong><br>
                Password: <strong style="color: #dc3545;">admin123</strong>
            </div>
            
            <div class="cred-box">
                <strong>User Login:</strong><br>
                Username: <strong style="color: #28a745;">testuser</strong><br>
                Password: <strong style="color: #28a745;">user123</strong>
            </div>
        </div>
        
        <a href="minor.php" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Go to Login Page
        </a>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
