<?php
/**
 * Reset Admin Credentials
 * Simple script to reset admin user
 */

require_once "config.php";

$messages = [];

// 1. Delete existing admin user
$messages[] = "Deleting existing admin user...";
$delete = mysqli_query($conn, "DELETE FROM users WHERE username = 'admin'");
if ($delete) {
    $messages[] = "✓ Old admin user deleted";
} else {
    $messages[] = "✗ Error deleting: " . mysqli_error($conn);
}

// 2. Insert fresh admin user
$messages[] = "Creating new admin user...";
$insert = mysqli_query($conn, "INSERT INTO users (username, email, phone, dob, password, name, role) 
                               VALUES ('admin', 'admin@example.com', '9876543210', '2000-01-01', 'admin123', 'Admin User', 'admin')");
if ($insert) {
    $messages[] = "✓ New admin user created with password: admin123";
} else {
    $messages[] = "✗ Error inserting: " . mysqli_error($conn);
}

// 3. Verify
$messages[] = "";
$messages[] = "Verifying admin user...";
$verify = mysqli_query($conn, "SELECT username, password, role FROM users WHERE username = 'admin'");
if (mysqli_num_rows($verify) > 0) {
    $admin = mysqli_fetch_assoc($verify);
    $messages[] = "✓ Admin user found:";
    $messages[] = "  Username: " . $admin['username'];
    $messages[] = "  Password: " . $admin['password'];
    $messages[] = "  Role: " . $admin['role'];
} else {
    $messages[] = "✗ Admin user not found after creation";
}

// 4. Create test user
$messages[] = "";
$messages[] = "Setting up test user...";
$delete_test = mysqli_query($conn, "DELETE FROM users WHERE username = 'testuser'");
$insert_test = mysqli_query($conn, "INSERT INTO users (username, email, phone, dob, password, name, role) 
                                   VALUES ('testuser', 'user@example.com', '1234567890', '2001-05-15', 'user123', 'Test User', 'user')");
if ($insert_test) {
    $messages[] = "✓ Test user created with password: user123";
} else {
    $messages[] = "✗ Error: " . mysqli_error($conn);
}

$messages[] = "";
$messages[] = "=== SETUP COMPLETE ===";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Admin</title>
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
        
        .reset-container {
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
        
        .log-output {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.8;
            color: #333;
            max-height: 350px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .log-output div {
            margin-bottom: 6px;
        }
        
        .log-output .success {
            color: #28a745;
            font-weight: bold;
        }
        
        .log-output .error {
            color: #dc3545;
            font-weight: bold;
        }
        
        .credentials {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .credentials h5 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .cred-item {
            background: white;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }
        
        .cred-item strong {
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
            width: 100%;
            text-align: center;
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
    <div class="reset-container">
        <div class="header">
            <i class="fas fa-redo"></i>
            <h2>Admin Reset Complete</h2>
            <p>Admin credentials have been reset</p>
        </div>
        
        <div class="log-output">
            <?php foreach ($messages as $msg): ?>
                <div class="<?php echo (strpos($msg, '✓') !== false) ? 'success' : (strpos($msg, '✗') !== false ? 'error' : ''); ?>">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="credentials">
            <h5><i class="fas fa-key"></i> New Login Credentials</h5>
            
            <div class="cred-item">
                <strong>Admin Account:</strong><br>
                Username: <strong>admin</strong><br>
                Password: <strong>admin123</strong>
            </div>
            
            <div class="cred-item">
                <strong>User Account:</strong><br>
                Username: <strong>testuser</strong><br>
                Password: <strong>user123</strong>
            </div>
        </div>
        
        <a href="minor.php" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Try Login Now
        </a>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
