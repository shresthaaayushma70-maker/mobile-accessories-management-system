<?php
/**
 * Simple Database Initializer
 * Creates or resets users for login
 */

require_once "config.php";

$result_message = "";
$result_type = "success";

// Check if users table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) == 0) {
    $result_message = "✗ Error: users table does not exist. Please run database_setup.sql first.";
    $result_type = "error";
} else {
    // Check if role column exists, if not add it
    $role_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
    if (mysqli_num_rows($role_check) == 0) {
        $alter = mysqli_query($conn, "ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user'");
    }
    
    // Delete and recreate admin user
    mysqli_query($conn, "DELETE FROM users WHERE username = 'admin'");
    $admin_insert = mysqli_query($conn, "INSERT INTO users (username, email, password, role) 
                                        VALUES ('admin', 'admin@example.com', 'admin123', 'admin')");
    
    // Delete and recreate test user
    mysqli_query($conn, "DELETE FROM users WHERE username = 'testuser'");
    $user_insert = mysqli_query($conn, "INSERT INTO users (username, email, password, role) 
                                       VALUES ('testuser', 'user@example.com', 'user123', 'user')");
    
    if ($admin_insert && $user_insert) {
        $result_message = "✓ Database initialized successfully!";
    } else {
        $result_message = "✗ Error creating users: " . mysqli_error($conn);
        $result_type = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Initialize</title>
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
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .icon-large {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .icon-success { color: #28a745; }
        .icon-error { color: #dc3545; }
        
        h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .message {
            font-size: 18px;
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .credentials {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: left;
        }
        
        .credentials h5 {
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .cred-row {
            background: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }
        
        .btn-login {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s;
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
        <div class="icon-large icon-<?php echo $result_type; ?>">
            <?php echo ($result_type === 'success') ? '✓' : '✗'; ?>
        </div>
        
        <h2><?php echo ($result_type === 'success') ? 'Ready to Login!' : 'Setup Error'; ?></h2>
        
        <div class="message <?php echo $result_type; ?>">
            <?php echo htmlspecialchars($result_message); ?>
        </div>
        
        <?php if ($result_type === 'success'): ?>
            <div class="credentials">
                <h5>Login Credentials</h5>
                
                <div class="cred-row">
                    <strong>Admin Account:</strong><br>
                    Username: <strong>admin</strong><br>
                    Password: <strong>admin123</strong>
                </div>
                
                <div class="cred-row">
                    <strong>User Account:</strong><br>
                    Username: <strong>testuser</strong><br>
                    Password: <strong>user123</strong>
                </div>
            </div>
            
            <a href="minor.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        <?php else: ?>
            <a href="init_db.php" class="btn-login">
                <i class="fas fa-redo"></i> Try Again
            </a>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
