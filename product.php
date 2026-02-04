<?php
$conn = new mysqli("localhost", "root", "", "Mproject");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$category = $_POST['category'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$description = $_POST['description'];

// Handle image upload
if (isset($_FILES['image'])) {
    $image_name = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/";

    // Optional: rename image to avoid duplicates
    $new_image_name = time() . "_" . $image_name;

    if (move_uploaded_file($tmp_name, $upload_dir . $new_image_name)) {
        // Save product in database
        $sql = "INSERT INTO product (name, category, price, quantity, description, image) 
                VALUES ('$name', '$category', '$price', '$quantity', '$description', '$new_image_name')";

        if ($conn->query($sql) === TRUE) {
            header("Location: dashboard.php"); // redirect to dashboard
            exit;
        } else {
            echo "Database error: " . $conn->error;
        }
    } else {
        echo "Failed to upload image.";
    }
} else {
    echo "No image selected.";
}

$conn->close();
?>
