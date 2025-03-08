<?php 
include '../database/database.php';

// Initialize variables
$customerID = $name = $contact = $address = "";
$conditions = [];
$params = [];

// Check if search form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['customerID'])) {
        $conditions[] = "customer_id LIKE ?";
        $params[] = "%" . $_POST['customerID'] . "%";
    }
    if (!empty($_POST['name'])) {
        $conditions[] = "name LIKE ?";
        $params[] = "%" . $_POST['name'] . "%";
    }
    if (!empty($_POST['contact'])) {
        $conditions[] = "contact LIKE ?";
        $params[] = "%" . $_POST['contact'] . "%";
    }
    if (!empty($_POST['address'])) {
        $conditions[] = "address LIKE ?";
        $params[] = "%" . $_POST['address'] . "%";
    }
}

// Construct the SQL query dynamically
$query = "SELECT * FROM Customer";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
