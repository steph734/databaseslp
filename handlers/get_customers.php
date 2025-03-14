<?php
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['term'])) {
        throw new Exception('Invalid request');
    }

    $term = "%" . $_POST['term'] . "%";
    $stmt = $conn->prepare("SELECT customer_id, name FROM Customer 
                           WHERE customer_id LIKE ? OR name LIKE ? 
                           LIMIT 10");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'value' => $row['customer_id'],
            'label' => $row['name'] . " (#" . $row['customer_id'] . ")"
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($customers);

} catch (Exception $e) {
    error_log("Error in get_customers.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
}
?>