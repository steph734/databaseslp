<?php
include '../database/database.php';

header('Content-Type: application/json');

$membershipCheck = $conn->query("SELECT COUNT(*) FROM membership WHERE customer_id IS NOT NULL LIMIT 1");
$hasMembership = $membershipCheck->fetch_row()[0] > 0;

echo json_encode(['hasMembership' => $hasMembership]);

$conn->close();
?>