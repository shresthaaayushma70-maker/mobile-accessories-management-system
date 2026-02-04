<?php
$conn = new mysqli("localhost", "root", "", "Mproject");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Get image filename
    $result = $conn->query("SELECT image FROM product WHERE id=$id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = "uploads/" . $row['image'];
        if (file_exists($image_path)) unlink($image_path);
    }

    // Delete product from database
    $sql = "DELETE FROM product WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    echo "No product ID provided.";
}

$conn->close();
?>
