<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/layout/web-layout.php?page=returns&error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['return_id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../resource/layout/web-layout.php?page=returns");
    exit();
}

// Ensure return_id is an array, even for single deletes
$return_ids = is_array($_POST['return_id']) ? $_POST['return_id'] : [$_POST['return_id']];

if (empty($return_ids)) {
    $_SESSION['error'] = "No return IDs provided!";
    header("Location: ../resource/layout/web-layout.php?page=returns");
    exit();
}

try {
    $conn->begin_transaction();

    // Prepare the delete query
    $placeholders = implode(',', array_fill(0, count($return_ids), '?'));
    $query = "DELETE FROM supplierreturn WHERE supplier_return_id IN ($placeholders)";
    $delete_stmt = $conn->prepare($query);

    if ($delete_stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters dynamically
    $types = str_repeat('i', count($return_ids));
    $delete_stmt->bind_param($types, ...$return_ids);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            $conn->commit();
            $_SESSION['success'] = "Supplier return(s) deleted successfully!";
        } else {
            $conn->rollback();
            $_SESSION['error'] = "No supplier return records found with the provided IDs!";
        }
    } else {
        $conn->rollback();
        $_SESSION['error'] = "Error processing deletion: " . $conn->error;
    }

    $delete_stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error processing deletion: " . $e->getMessage();
    error_log("Exception: " . $e->getMessage());
}

$conn->close();
header("Location: ../resource/layout/web-layout.php?page=returns");
exit();
?>