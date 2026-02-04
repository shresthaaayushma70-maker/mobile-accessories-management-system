<?php

require_once "config.php";

$username = $email = $phone = $dob = $password = $confirm_password = "";
$username_err = $email_err = $phone_err = $dob_err = $password_err = $confirm_password_err = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate Username
    if (empty($username)) {
        $username_err = "Username cannot be blank";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                $username_err = "This username is already taken";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate Email
    if (empty($email)) {
        $email_err = "Email cannot be blank";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                $email_err = "This email is already registered";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate Phone
    if (empty($phone)) {
        $phone_err = "Phone number cannot be blank";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $phone_err = "Phone number must be 10 digits";
    }

    // Validate DOB
    if (empty($dob)) {
        $dob_err = "Date of Birth cannot be blank";
    } else {
        $age = date_diff(date_create($dob), date_create('today'))->y;
        if ($age < 18) {
            $dob_err = "You must be 18 years old or above";
        }
    }

    // Validate Password
    if (empty($password)) {
        $password_err = "Password cannot be blank";
    } elseif (strlen($password) < 6) {
        $password_err = "Password must be at least 6 characters";
    }

    // Confirm Password
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm your password";
    } elseif ($password !== $confirm_password) {
        $confirm_password_err = "Passwords do not match";
    }

    // If no errors, insert user
    if (empty($username_err) && empty($email_err) && empty($phone_err) && empty($dob_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (username, email, phone, dob, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $phone, $dob, $password);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Registration successful! Redirecting to login...";
                header("refresh:2;url=minor.php");
            } else {
                echo "Error: " . $conn->error;
            }
            mysqli_stmt_close($stmt);
        }
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Mobile Accessories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            margin: 40px auto;
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
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .form-group label {
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .text-danger {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
            margin-top: 20px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>📱 Mobile Accessories</h2>
            <p>Create your account</p>
        </div>
        
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <span class="text-danger"><?php echo $username_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="text-danger"><?php echo $email_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" class="form-control" name="phone" placeholder="10-digit number" value="<?php echo htmlspecialchars($phone); ?>" required>
                <span class="text-danger"><?php echo $phone_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" class="form-control" name="dob" value="<?php echo htmlspecialchars($dob); ?>" required>
                <span class="text-danger"><?php echo $dob_err; ?></span>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" required>
                    <span class="text-danger"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group col-md-6">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                    <span class="text-danger"><?php echo $confirm_password_err; ?></span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-register">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="minor.php">Login here</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>