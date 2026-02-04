<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: minor.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "Mproject");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");

if ($result->num_rows == 0) {
    die("User not found");
}

$user = $result->fetch_assoc();
$success_msg = $error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        $sql = "UPDATE users SET 
                name='$name',
                email='$email',
                phone='$phone',
                username='$username',
                password='$password'
                WHERE id=$user_id";
        
        if ($conn->query($sql)) {
            $success_msg = "Profile updated successfully!";
            $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        } else {
            $error_msg = "Error updating profile: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete'])) {
        $conn->query("DELETE FROM users WHERE id=$user_id");
        session_destroy();
        header("Location: minor.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Settings</title>
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
        
        .settings-container {
            max-width: 800px;
            margin: 40px auto;
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
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
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
        
        <form method="post">
            <div class="section">
                <h3><i class="fas fa-info-circle"></i> Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" 
                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" id="username" 
                               value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" name="email" id="email" 
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" id="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-lock"></i> Password</h3>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" id="password" 
                           value="<?php echo htmlspecialchars($user['password'] ?? ''); ?>" required>
                    <small class="text-muted">Leave unchanged to keep current password</small>
                </div>
            </div>
            
            <div class="btn-group">
                <button type="submit" name="update" class="btn btn-update">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="submit" name="delete" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    <i class="fas fa-trash"></i> Delete Account
                </button>
                <a href="dashboard.php" class="btn back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>