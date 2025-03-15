<?php
include '../database/database.php';

header('Content-Type: application/json');

$order_by = isset($_POST['order']) && in_array($_POST['order'], ['asc', 'desc']) ? $_POST['order'] : 'desc';

$receiving_query = "SELECT 
    r.receiving_id,
    r.supplier_id,
    s.supplier_name,
    r.receiving_date,
    r.total_quantity,
    r.total_cost,
    r.status,
    GROUP_CONCAT(rd.product_id SEPARATOR ', ') AS product_ids,
    GROUP_CONCAT(p.product_name SEPARATOR ', ') AS product_names,
    GROUP_CONCAT(rd.quantity_furnished SEPARATOR ', ') AS quantities,
    GROUP_CONCAT(rd.unit_cost SEPARATOR ', ') AS unit_costs,
    GROUP_CONCAT(rd.subtotal_cost SEPARATOR ', ') AS subtotal_costs,
    GROUP_CONCAT(rd.condition SEPARATOR ', ') AS conditions,
    rd.createdbyid,
    rd.updatedbyid,
    rd.createdate,
    rd.updatedate
FROM receiving r
JOIN Supplier s ON r.supplier_id = s.supplier_id
JOIN receiving_details rd ON r.receiving_id = rd.receiving_id
JOIN Product p ON rd.product_id = p.product_id
GROUP BY r.receiving_id
ORDER BY r.receiving_id " . ($order_by === 'desc' ? 'DESC' : 'ASC');

$receiving_result = $conn->query($receiving_query);

if (!$receiving_result) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

$receivings = [];
while ($row = $receiving_result->fetch_assoc()) {
    $receivings[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $receivings, 'order' => $order_by]);
$conn->close();