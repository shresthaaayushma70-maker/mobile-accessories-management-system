<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: minor.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");

if (mysqli_num_rows($result) == 0) {
    die("User not found");
}

$user = mysqli_fetch_assoc($result);
$success_msg = $error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $username = sanitize_input($_POST['username']);
        $password = sanitize_input($_POST['password']);
        
        $errors = [];
        
        // Validate name
        if (empty($name) || strlen($name) < 3) {
            $errors[] = "Name must be at least 3 characters";
        }
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        } else {
            // Check if email is already used by another user
            $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = mysqli_prepare($conn, $check_email);
            mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = "Email is already in use by another account";
            }
            mysqli_stmt_close($stmt);
        }
        
        // Validate phone
        if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = "Phone number must be 10 digits";
        }
        
        // Validate username
        if (empty($username) || strlen($username) < 4) {
            $errors[] = "Username must be at least 4 characters";
        } else {
            // Check if username is already used by another user
            $check_username = "SELECT id FROM users WHERE username = ? AND id != ?";
            $stmt = mysqli_prepare($conn, $check_username);
            mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = "Username is already taken";
            }
            mysqli_stmt_close($stmt);
        }
        
        // Validate password
        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if (empty($errors)) {
            $sql = "UPDATE users SET name=?, email=?, phone=?, username=?, password=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt === false) {
                $error_msg = "Database error: " . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $username, $password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                log_activity($conn, $user_id, "Profile Update", "User updated profile");
                $success_msg = "Profile updated successfully!";
                $_SESSION['username'] = $username;
                
                // Refresh user data
                $user = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
                $user = mysqli_fetch_assoc($user);
            } else {
                $error_msg = "Error updating profile: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
            }
        } else {
            $error_msg = implode("<br>", $errors);
        }
    }
    
    if (isset($_POST['delete'])) {
        // Log the deletion before deleting the user
        log_activity($conn, $user_id, "Account Deletion", "User deleted their account");
        
        // Delete all user's products first
        $products_result = mysqli_query($conn, "SELECT image FROM product");
        while ($product = mysqli_fetch_assoc($products_result)) {
            $image_path = "uploads/" . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete user (cascade will handle activity_log)
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        session_destroy();
        header("Location: minor.php");
        exit;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Settings - Mobile Accessories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .container-main {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }
        
        .sidebar a, .sidebar button {
            display: block;
            width: 100%;
            color: #ecf0f1;
            padding: 15px 20px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            font-size: 15px;
        }
        
        .sidebar a:hover, .sidebar button:hover {
            background: #34495e;
            border-left-color: #667eea;
            padding-left: 30px;
        }
        
        .sidebar a i, .sidebar button i {
            margin-right: 10px;
            width: 20px;
        }
        
        .sidebar-logout-btn {
            display: block;
            width: 100%;
            color: #ecf0f1;
            padding: 15px 20px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            font-size: 15px;
        }
        
        .sidebar-logout-btn:hover {
            background: #34495e;
            border-left-color: #dc3545;
            padding-left: 30px;
        }
        
        .sidebar-logout-btn i {
            margin-right: 10px;
            width: 20px;
        }
        
        .content {
            margin-left: 250px;
            padding: 30px;
            flex: 1;
        }
        
        .settings-container {
            max-width: 800px;
            margin: 40px;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
        
        .settings-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .settings-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .section {
            margin-bottom: 35px;
        }
        
        .section h3 {
            color: #555;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
        }
        
        .section h3 i {
            margin-right: 10px;
            color: #667eea;
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
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            flex: 1;
            min-width: 150px;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .back-btn {
            background: #27ae60;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #229954;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        
        .user-info-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .user-info-display p {
            margin: 5px 0;
            color: #666;
        }
        
        .user-info-display strong {
            color: #333;
        }
    </style>
</head>
<body>
    <?php if ($is_admin): ?>
        <!-- For admin users, show simplified navbar -->
        <div class="header">
            <i class="fas fa-mobile-alt"></i> Profile Settings
        </div>
    <?php else: ?>
        <!-- Header and sidebar for regular users -->
        <div class="header">
            <i class="fas fa-mobile-alt"></i> Mobile Accessories
        </div>
        <div class="container-main">
            <div class="sidebar">
                <a href="user_dashboard.php">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-bag"></i> My Orders
                </a>
                <a href="profile.php">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <form action="logout.php" method="POST" style="margin: 0; padding: 0;">
                    <button type="submit" class="sidebar-logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            <div class="content">
    <?php endif; ?>
    
    <div class="settings-container">
        <div class="settings-header">
            <h2><i class="fas fa-user-cog"></i> Profile Settings</h2>
            <p style="color: #666; margin: 0;">Manage your account information</p>
        </div>
        
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong><i class="fas fa-check-circle"></i> Success!</strong> <?php echo $success_msg; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong><i class="fas fa-exclamation-circle"></i> Error!</strong> <?php echo $error_msg; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="user-info-display">
            <p><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
            <?php if (!empty($user['last_login'])): ?>
                <p><strong>Last Login:</strong> <?php echo date('F d, Y g:i A', strtotime($user['last_login'])); ?></p>
            <?php endif; ?>
        </div>
        
        <form method="post">
            <div class="section">
                <h3><i class="fas fa-info-circle"></i> Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Full Name <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" 
                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="username">Username <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" name="username" id="username" 
                               value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email">Email Address <span style="color: red;">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" 
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" id="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                               placeholder="10-digit number">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth (Read-only)</label>
                    <input type="date" class="form-control" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" disabled>
                    <small class="text-muted">Date of birth cannot be changed</small>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-lock"></i> Password</h3>
                <div class="form-group">
                    <label for="password">Password <span style="color: red;">*</span></label>
                    <input type="password" class="form-control" name="password" id="password" 
                           value="<?php echo htmlspecialchars($user['password'] ?? ''); ?>" required>
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
            </div>
            
            <div class="btn-group">
                <button type="submit" name="update" class="btn btn-update">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="submit" name="delete" class="btn btn-danger" 
                        onclick="return confirm('⚠️ WARNING: This will permanently delete your account and all your products. This action cannot be undone. Are you absolutely sure?');">
                    <i class="fas fa-trash"></i> Delete Account
                </button>
                <a href="<?php echo $is_admin ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="btn back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
    <!-- End settings-container -->
    
    <?php if (!$is_admin): ?>
        </div>
        <!-- End content -->
        </div>
        <!-- End container-main -->
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
