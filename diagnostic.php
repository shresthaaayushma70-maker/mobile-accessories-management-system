<?php
/**
 * Login Diagnostic Script
 * Helps identify login issues
 */

require_once "config.php";

$diagnostics = [];
$issues = [];

// 1. Check database connection
if ($conn) {
    $diagnostics[] = ["✓", "Database connection successful"];
} else {
    $diagnostics[] = ["✗", "Database connection failed: " . mysqli_connect_error()];
    $issues[] = "Cannot connect to database";
}

// 2. Check if users table exists
$tables_result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($tables_result) > 0) {
    $diagnostics[] = ["✓", "Users table exists"];
} else {
    $diagnostics[] = ["✗", "Users table does not exist"];
    $issues[] = "Run the database_setup.sql script first";
}

// 3. Check if role column exists
$role_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($role_check) > 0) {
    $diagnostics[] = ["✓", "Role column exists in users table"];
} else {
    $diagnostics[] = ["⚠", "Role column missing - will default users to 'user' role"];
    $issues[] = "Run setup_db.php to add role column";
}

// 4. Check users table structure
$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM users");
$columns = [];
while ($col = mysqli_fetch_assoc($columns_result)) {
    $columns[] = $col['Field'];
}
$diagnostics[] = ["ℹ", "Users table columns: " . implode(", ", $columns)];

// 5. Check if admin user exists
$admin_check = mysqli_query($conn, "SELECT * FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) > 0) {
    $admin = mysqli_fetch_assoc($admin_check);
    $diagnostics[] = ["✓", "Admin user exists"];
    $diagnostics[] = ["ℹ", "Admin username: " . htmlspecialchars($admin['username'])];
    $diagnostics[] = ["ℹ", "Admin password in DB: " . htmlspecialchars($admin['password'])];
    if (isset($admin['role'])) {
        $diagnostics[] = ["ℹ", "Admin role: " . htmlspecialchars($admin['role'])];
    } else {
        $diagnostics[] = ["⚠", "Admin user has no role assigned"];
        $issues[] = "Run setup_db.php to assign role to admin user";
    }
} else {
    $diagnostics[] = ["✗", "Admin user does not exist"];
    $issues[] = "Run setup_db.php to create admin user";
}

// 6. Check if testuser exists
$user_check = mysqli_query($conn, "SELECT * FROM users WHERE username = 'testuser'");
if (mysqli_num_rows($user_check) > 0) {
    $user = mysqli_fetch_assoc($user_check);
    $diagnostics[] = ["✓", "Test user exists"];
    $diagnostics[] = ["ℹ", "Test user role: " . (isset($user['role']) ? htmlspecialchars($user['role']) : "N/A")];
} else {
    $diagnostics[] = ["⚠", "Test user does not exist"];
}

// 7. Count all users
$users_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$count_row = mysqli_fetch_assoc($users_count);
$diagnostics[] = ["ℹ", "Total users in database: " . $count_row['count']];

// 8. List all users
$all_users = mysqli_query($conn, "SELECT id, username, password, role FROM users");
$users_list = [];
while ($u = mysqli_fetch_assoc($all_users)) {
    $users_list[] = $u;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Diagnostics</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
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
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .diagnostic-item {
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            background: #f8f9fa;
            border-left: 4px solid #ddd;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .diagnostic-item.success {
            border-left-color: #28a745;
            background: #f0fff4;
            color: #155724;
        }
        
        .diagnostic-item.error {
            border-left-color: #dc3545;
            background: #fff5f5;
            color: #721c24;
        }
        
        .diagnostic-item.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
            color: #856404;
        }
        
        .diagnostic-item.info {
            border-left-color: #17a2b8;
            background: #f0f7ff;
            color: #0c5460;
        }
        
        .icon {
            font-weight: bold;
            min-width: 20px;
        }
        
        .issues-box {
            background: #fff5f5;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .issues-box h5 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .issue-item {
            padding: 10px;
            margin-bottom: 8px;
            background: white;
            border-radius: 4px;
            border-left: 3px solid #dc3545;
        }
        
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
            margin-top: 15px;
            transition: transform 0.3s;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .users-table {
            margin-top: 25px;
            overflow-x: auto;
        }
        
        .users-table table {
            font-size: 13px;
            margin-bottom: 0;
        }
        
        .users-table th {
            background: #667eea;
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .users-table td {
            padding: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-stethoscope"></i>
            <h2>Login Diagnostics</h2>
            <p>Checking your database configuration</p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <?php foreach ($diagnostics as $diag): 
                $type = 'info';
                if (strpos($diag[0], '✓') !== false) $type = 'success';
                elseif (strpos($diag[0], '✗') !== false) $type = 'error';
                elseif (strpos($diag[0], '⚠') !== false) $type = 'warning';
            ?>
                <div class="diagnostic-item <?php echo $type; ?>">
                    <span class="icon"><?php echo $diag[0]; ?></span>
                    <span><?php echo htmlspecialchars($diag[1]); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($issues)): ?>
            <div class="issues-box">
                <h5><i class="fas fa-exclamation-circle"></i> Issues Found</h5>
                <?php foreach ($issues as $issue): ?>
                    <div class="issue-item">
                        <i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($issue); ?>
                    </div>
                <?php endforeach; ?>
                
                <a href="setup_db.php" class="action-button">
                    <i class="fas fa-wrench"></i> Run Setup Script
                </a>
            </div>
        <?php else: ?>
            <div style="background: #f0fff4; border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin-top: 20px;">
                <h5 style="color: #28a745; margin-bottom: 10px;">
                    <i class="fas fa-check-circle"></i> All Systems OK!
                </h5>
                <p style="color: #155724; margin-bottom: 0;">Your database is properly configured. Try logging in again.</p>
                <a href="minor.php" class="action-button">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($users_list)): ?>
            <div class="users-table">
                <h5 style="margin-top: 25px; margin-bottom: 15px;">
                    <i class="fas fa-users"></i> Users in Database
                </h5>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_list as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['id']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td style="font-family: monospace; font-size: 11px;"><?php echo htmlspecialchars($u['password']); ?></td>
                                <td>
                                    <?php if (isset($u['role'])): ?>
                                        <span class="badge badge-<?php echo ($u['role'] === 'admin') ? 'danger' : 'success'; ?>">
                                            <?php echo htmlspecialchars($u['role']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
