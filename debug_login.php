<?php
/**
 * Advanced Login Debugger
 * Test login credentials directly
 */

require_once "config.php";

$test_username = "admin";
$test_password = "admin123";
$test_role = "admin";

$debug_log = [];
$debug_log[] = "=== LOGIN DEBUG TEST ===";
$debug_log[] = "Testing login for: " . $test_username;
$debug_log[] = "";

// 1. Check if user exists in database
$debug_log[] = "STEP 1: Checking if admin user exists...";
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $debug_log[] = "✗ Prepare failed: " . mysqli_error($conn);
} else {
    $debug_log[] = "✓ Statement prepared";
    
    mysqli_stmt_bind_param($stmt, "s", $test_username);
    if (!mysqli_stmt_execute($stmt)) {
        $debug_log[] = "✗ Execute failed: " . mysqli_error($conn);
    } else {
        $debug_log[] = "✓ Query executed";
        
        $result = mysqli_stmt_get_result($stmt);
        $num_rows = mysqli_num_rows($result);
        $debug_log[] = "✓ Rows found: " . $num_rows;
        
        if ($num_rows == 1) {
            $user = mysqli_fetch_assoc($result);
            
            $debug_log[] = "";
            $debug_log[] = "STEP 2: User data retrieved:";
            $debug_log[] = "  ID: " . $user['id'];
            $debug_log[] = "  Username: " . $user['username'];
            $debug_log[] = "  Email: " . $user['email'];
            $debug_log[] = "  Password (stored): " . $user['password'];
            $debug_log[] = "  Password (input): " . $test_password;
            $debug_log[] = "  Password match: " . ($user['password'] === $test_password ? "YES" : "NO");
            $debug_log[] = "  Role: " . (isset($user['role']) ? $user['role'] : "NOT SET");
            
            $debug_log[] = "";
            $debug_log[] = "STEP 3: Password verification:";
            if ($user['password'] === $test_password) {
                $debug_log[] = "✓ Password matches!";
                
                $debug_log[] = "";
                $debug_log[] = "STEP 4: Role verification:";
                $debug_log[] = "  Expected role: " . $test_role;
                $debug_log[] = "  Actual role: " . (isset($user['role']) ? $user['role'] : "NULL");
                
                if (!isset($user['role'])) {
                    $debug_log[] = "✗ ISSUE: Role column missing or role value is NULL";
                    $debug_log[] = "  Action: Need to run fix_login.php or setup_db.php";
                } elseif ($user['role'] !== $test_role) {
                    $debug_log[] = "✗ ISSUE: Role is '" . $user['role'] . "' but expected '" . $test_role . "'";
                    $debug_log[] = "  Action: Need to update role in database";
                } else {
                    $debug_log[] = "✓ Role matches! Login should work";
                }
            } else {
                $debug_log[] = "✗ ISSUE: Password does not match";
                $debug_log[] = "  Stored: '" . $user['password'] . "'";
                $debug_log[] = "  Input: '" . $test_password . "'";
                $debug_log[] = "  Length stored: " . strlen($user['password']);
                $debug_log[] = "  Length input: " . strlen($test_password);
            }
        } else {
            $debug_log[] = "✗ ISSUE: Admin user not found in database";
            $debug_log[] = "  Action: Need to create admin user";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// 2. List ALL users for reference
$debug_log[] = "";
$debug_log[] = "=== ALL USERS IN DATABASE ===";
$all_users_result = mysqli_query($conn, "SELECT id, username, password, role FROM users");
if ($all_users_result) {
    $count = 0;
    while ($u = mysqli_fetch_assoc($all_users_result)) {
        $count++;
        $debug_log[] = "User #" . $count . ": " . $u['username'] . " | Password: " . $u['password'] . " | Role: " . (isset($u['role']) ? $u['role'] : "NULL");
    }
    if ($count == 0) {
        $debug_log[] = "✗ No users found in database";
    }
}

// 3. Check table structure
$debug_log[] = "";
$debug_log[] = "=== TABLE STRUCTURE ===";
$columns = mysqli_query($conn, "SHOW COLUMNS FROM users");
while ($col = mysqli_fetch_assoc($columns)) {
    $debug_log[] = $col['Field'] . " (" . $col['Type'] . ")";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Debugger</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .debug-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
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
        
        .debug-output {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .debug-output .success {
            color: #4ec9b0;
        }
        
        .debug-output .error {
            color: #f48771;
        }
        
        .debug-output .warning {
            color: #dcdcaa;
        }
        
        .action-needed {
            background: #fff5f5;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .action-needed h5 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .action-step {
            background: white;
            padding: 12px;
            border-left: 4px solid #dc3545;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        
        .action-step strong {
            color: #333;
        }
        
        .btn-fix {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-right: 10px;
            margin-top: 10px;
            transition: transform 0.3s;
        }
        
        .btn-fix:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="header">
            <i class="fas fa-bug"></i>
            <h2>Login Debugger</h2>
            <p>Analyzing login issue for admin user</p>
        </div>
        
        <div class="debug-output">
<?php 
foreach ($debug_log as $line) {
    if (strpos($line, "✓") !== false) {
        echo '<span class="success">' . htmlspecialchars($line) . '</span>' . "\n";
    } elseif (strpos($line, "✗") !== false) {
        echo '<span class="error">' . htmlspecialchars($line) . '</span>' . "\n";
    } elseif (strpos($line, "===") !== false) {
        echo '<span class="warning">' . htmlspecialchars($line) . '</span>' . "\n";
    } else {
        echo htmlspecialchars($line) . "\n";
    }
}
?>
        </div>
        
        <div class="action-needed">
            <h5><i class="fas fa-tools"></i> Recommended Actions</h5>
            
            <div class="action-step">
                <strong>Step 1: Reset Admin Password</strong><br>
                Run this to reset admin credentials to defaults:
                <a href="reset_admin.php" class="btn-fix">
                    <i class="fas fa-sync"></i> Reset Admin
                </a>
            </div>
            
            <div class="action-step">
                <strong>Step 2: Run Complete Fix</strong><br>
                If reset doesn't work, run the complete database fix:
                <a href="fix_login.php" class="btn-fix">
                    <i class="fas fa-wrench"></i> Complete Fix
                </a>
            </div>
            
            <div class="action-step">
                <strong>Step 3: Try Login Again</strong><br>
                After fixing, try logging in with admin / admin123:
                <a href="minor.php" class="btn-fix">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
