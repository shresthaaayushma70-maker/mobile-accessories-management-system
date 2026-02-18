<?php

session_start();

// Check if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$err = "";
$login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user'; // default to user login

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';

    if (empty($username) || empty($password)) {
        $err = "Please enter username and password";
    } else {
        // Simple query - just get the user
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                
                // Check password
                if ($password === $user['password']) {
                    // Set default role if not set
                    $user_role = isset($user['role']) && !empty($user['role']) ? $user['role'] : 'user';
                    
                    // Set session
                    $_SESSION["username"] = $user['username'];
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["role"] = $user_role;
                    $_SESSION["loggedin"] = true;
                    
                    // Redirect based on role (ignore login_type tab selection)
                    if ($user_role === 'admin') {
                        header("Location: admin_dashboard.php");
                        exit;
                    } else {
                        header("Location: user_dashboard.php");
                        exit;
                    }
                } else {
                    $err = "Incorrect password. Please try again.";
                }
            } else {
                $err = "No account found with that username.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $err = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Mobile Accessories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 25px;
        }
        
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
        }
        
        .nav-tabs .nav-link:hover {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .nav-tabs .nav-link.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: none;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .login-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
            margin-bottom: 20px;
        }
        
        .admin-badge {
            background: #dc3545;
            color: white;
        }
        
        .user-badge {
            background: #28a745;
            color: white;
        }
        
        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .register-link a:hover {
            color: #764ba2;
        }
        

    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-mobile-alt" style="font-size: 50px; color: #667eea; margin-bottom: 15px;"></i>
            <h2>Mobile Accessories</h2>
            <p>Login to your account</p>
        </div>
        
        <?php if (!empty($err)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong> <?php echo $err; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <!-- Login Type Tabs -->
        <ul class="nav-tabs">
            <li>
                <a href="#" class="nav-link <?php echo ($login_type === 'user') ? 'active' : ''; ?>" onclick="switchTab('user'); return false;">
                    <i class="fas fa-user"></i> User Login
                </a>
            </li>
            <li>
                <a href="#" class="nav-link <?php echo ($login_type === 'admin') ? 'active' : ''; ?>" onclick="switchTab('admin'); return false;">
                    <i class="fas fa-user-shield"></i> Admin Login
                </a>
            </li>
        </ul>
        
        <!-- User Login Tab -->
        <div class="tab-content <?php echo ($login_type === 'user') ? 'active' : ''; ?>" id="user-tab">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="login-badge user-badge"><i class="fas fa-user"></i> User Account</span>
            </div>
            <form action="" method="post">
                <input type="hidden" name="login_type" value="user">
                
                <div class="form-group">
                    <label for="username-user">Username</label>
                    <input type="text" class="form-control" name="username" id="username-user" 
                           placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password-user">Password</label>
                    <input type="password" class="form-control" name="password" id="password-user" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login as User
                </button>
                
                
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
        
        <!-- Admin Login Tab -->
        <div class="tab-content <?php echo ($login_type === 'admin') ? 'active' : ''; ?>" id="admin-tab">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="login-badge admin-badge"><i class="fas fa-user-shield"></i> Admin Account</span>
            </div>
            <form action="" method="post">
                <input type="hidden" name="login_type" value="admin">
                
                <div class="form-group">
                    <label for="username-admin">Username</label>
                    <input type="text" class="form-control" name="username" id="username-admin" 
                           placeholder="Enter admin username" required>
                </div>
                
                <div class="form-group">
                    <label for="password-admin">Password</label>
                    <input type="password" class="form-control" name="password" id="password-admin" 
                           placeholder="Enter admin password" required>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login as Admin
                </button>
                
                
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function switchTab(type) {
            // Hide all tabs
            document.getElementById('user-tab').classList.remove('active');
            document.getElementById('admin-tab').classList.remove('active');
            
            // Remove active class from all nav-links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab
            if (type === 'user') {
                document.getElementById('user-tab').classList.add('active');
                document.querySelectorAll('.nav-link')[0].classList.add('active');
            } else {
                document.getElementById('admin-tab').classList.add('active');
                document.querySelectorAll('.nav-link')[1].classList.add('active');
            }
        }
    </script>
</body>
</html>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>