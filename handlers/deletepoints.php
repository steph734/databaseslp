<?php
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    $points_id = $_GET['id'] ?? null;

    if (!$points_id) {
        throw new Exception('Points ID is required');
    }

    // Prepare and execute delete query
    $query = "DELETE FROM points WHERE points_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $points_id);
    
    if ($stmt->execute()) {
        // Echo success message before redirect
        echo "Points deleted successfully";
        header("Location: ../resource/layout/web-layout.php?page=points");
        exit; // Important to stop script execution after redirect
    } else {
        throw new Exception('Failed to delete points');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo "Error: " . $e->getMessage();
}
?>