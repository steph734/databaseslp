
<?php
session_start();
include '../database/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../views/products.php?error=unauthorized");
    exit();
}

// Check if request method is GET and 'id' is set
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $category_id = $_GET['id'];

    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM Category WHERE category_id = ?");
    $stmt->bind_param("s", $category_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Category deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting category: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
}
