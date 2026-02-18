<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: minor.php");
    exit;
}

// Restrict to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Access Denied</title>
        <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
        <style>
            body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f8f9fa; }
            .error-container { text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .error-container h1 { color: #dc3545; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <h1>❌ Access Denied</h1>
            <p>Only administrators can access this page.</p>
            <a href='user_dashboard.php' class='btn btn-primary mt-3'>Go to Dashboard</a>
        </div>
    </body>
    </html>
    ");
}

require_once "config.php";

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error_msg = $success_msg = "";

// Fetch product details
$sql = "SELECT * FROM product WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result->num_rows == 0) {
    die("Product not found");
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $category = sanitize_input($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $description = sanitize_input($_POST['description']);
    $current_image = $product['image'];
    
    // Validate inputs
    if (empty($name) || empty($category) || $price <= 0 || $quantity < 0) {
        $error_msg = "Please fill all required fields with valid values";
    } else {
        // Handle new image upload if provided
        $new_image = $current_image;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_errors = validate_image($_FILES['image']);
            
            if (empty($image_errors)) {
                $new_filename = generate_unique_filename($_FILES['image']['name']);
                $upload_path = "uploads/" . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists
                    if (!empty($current_image) && file_exists("uploads/" . $current_image)) {
                        unlink("uploads/" . $current_image);
                    }
                    $new_image = $new_filename;
                } else {
                    $error_msg = "Failed to upload new image";
                }
            } else {
                $error_msg = implode("<br>", $image_errors);
            }
        }
        
        // Update product if no errors
        if (empty($error_msg)) {
            $sql = "UPDATE product SET name = ?, category = ?, price = ?, quantity = ?, description = ?, image = ?, updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssdissi", $name, $category, $price, $quantity, $description, $new_image, $product_id);
            
            if (mysqli_stmt_execute($stmt)) {
                log_activity($conn, $_SESSION['user_id'], "Updated Product", "Updated product: " . $name);
                $success_msg = "Product updated successfully!";
                
                // Refresh product data
                $product['name'] = $name;
                $product['category'] = $category;
                $product['price'] = $price;
                $product['quantity'] = $quantity;
                $product['description'] = $description;
                $product['image'] = $new_image;
            } else {
                $error_msg = "Error updating product: " . mysqli_error($conn);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product - Mobile Accessories</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px 20px;
        }
        
        .form-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
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
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .current-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            margin-bottom: 15px;
        }
        
        .preview-img {
            max-width: 100%;
            max-height: 250px;
            object-fit: cover;
            margin-top: 15px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            display: none;
        }
        
        .file-input-wrapper {
            position: relative;
        }
        
        .file-input-label {
            display: block;
            padding: 20px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .file-input-label:hover {
            background: #f0f0ff;
            border-color: #764ba2;
        }
        
        .file-input-label i {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        #image {
            display: none;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-submit {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .back-btn {
            display: block;
            text-align: center;
            background: #27ae60;
            color: white;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 15px;
            transition: all 0.3s;
            font-weight: 600;
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
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>📝 Edit Product</h2>
            <p>Update product details below</p>
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
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="name" id="name" 
                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category <span style="color: red;">*</span></label>
                <select class="form-control" name="category" id="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="Mobile Covers" <?php echo $product['category'] == 'Mobile Covers' ? 'selected' : ''; ?>>Mobile Covers</option>
                    <option value="Chargers" <?php echo $product['category'] == 'Chargers' ? 'selected' : ''; ?>>Chargers</option>
                    <option value="Headphones" <?php echo $product['category'] == 'Headphones' ? 'selected' : ''; ?>>Headphones / Earphones</option>
                    <option value="Power Banks" <?php echo $product['category'] == 'Power Banks' ? 'selected' : ''; ?>>Power Banks</option>
                    <option value="Screen Protectors" <?php echo $product['category'] == 'Screen Protectors' ? 'selected' : ''; ?>>Screen Protectors</option>
                    <option value="Cables" <?php echo $product['category'] == 'Cables' ? 'selected' : ''; ?>>Cables</option>
                    <option value="Memory Cards" <?php echo $product['category'] == 'Memory Cards' ? 'selected' : ''; ?>>Memory Cards</option>
                    <option value="Other Accessories" <?php echo $product['category'] == 'Other Accessories' ? 'selected' : ''; ?>>Other Accessories</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="price">Price (NPR) <span style="color: red;">*</span></label>
                    <input type="number" class="form-control" name="price" id="price" 
                           step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="quantity">Quantity <span style="color: red;">*</span></label>
                    <input type="number" class="form-control" name="quantity" id="quantity" 
                           value="<?php echo $product['quantity']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Current Product Image</label>
                <div>
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="Current product image" class="current-image">
                </div>
            </div>
            
            <div class="form-group">
                <label>Upload New Image (optional)</label>
                <div class="file-input-wrapper">
                    <label class="file-input-label" for="image">
                        <i class="fas fa-cloud-upload-alt"></i><br>
                        Click to upload a new image or leave blank to keep current<br>
                        <small>PNG, JPG, GIF up to 5MB</small>
                    </label>
                    <input type="file" class="form-control" name="image" id="image" 
                           accept="image/*" onchange="previewImage(event)">
                </div>
                <img id="preview" class="preview-img" alt="Image preview">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" name="description" id="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Product
                </button>
            </div>
            
            <a href="user_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </form>
    </div>
    
    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
