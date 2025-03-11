<?php
session_start();
include '../database/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = explode(',', $_POST['ids']);
    $ids = array_map('intval', $ids); // Sanitize IDs

    try {
        // Prepare the deletion query
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM Sales WHERE sales_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

        if ($stmt->execute()) {
            // Also delete related SalesLine entries
            $sql_line = "DELETE FROM SalesLine WHERE sales_id IN ($placeholders)";
            $stmt_line = $conn->prepare($sql_line);
            $stmt_line->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt_line->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete sales']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
} elseif (isset($_GET['id'])) {
    // Existing single delete logic
    $id = intval($_GET['id']);
    try {
        $sql = "DELETE FROM Sales WHERE sales_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $sql_line = "DELETE FROM SalesLine WHERE sales_id = ?";
            $stmt_line = $conn->prepare($sql_line);
            $stmt_line->bind_param("i", $id);
            $stmt_line->execute();

            $_SESSION['success'] = "Sale deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete sale.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
