<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: minor.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "Mproject");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = htmlspecialchars($_SESSION['username']);
$sql = "SELECT * FROM product ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Mobile Accessories</title>
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
        
        .sidebar a {
            display: block;
            color: #ecf0f1;
            padding: 15px 20px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar a:hover {
            background: #34495e;
            border-left-color: #667eea;
            padding-left: 30px;
        }
        
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .content {
            margin-left: 250px;
            padding: 30px;
            flex: 1;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #667eea;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }
        
        .product-details {
            padding: 20px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-category {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .product-quantity {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-delete {
            flex: 1;
            background: #e74c3c;
            color: white;
            padding: 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-delete:hover {
            background: #c0392b;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .btn-add-product {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-add-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .content {
                margin-left: 200px;
                padding: 20px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 600px) {
            .sidebar {
                position: absolute;
                width: 200px;
                z-index: 1000;
            }
            
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        📱 Mobile Accessories Dashboard
    </div>
    
    <div class="container-main">
        <div class="sidebar">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="product.html"><i class="fas fa-plus-circle"></i> Add Product</a>
            <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="content">
            <div class="section-title">
                <i class="fas fa-box"></i> Product Inventory
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <div class="product-details">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-quantity">
                                    <i class="fas fa-cubes"></i> Stock: <?php echo $product['quantity']; ?>
                                </div>
                                <div class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                                </div>
                                <div class="product-actions">
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-delete" onclick="return confirm('Delete this product?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No products added yet</p>
                    <a href="product.html" class="btn-add-product">Add Your First Product</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>