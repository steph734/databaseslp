<?php
include '../database/database.php';
$inventoryId = $_POST['inventory_id'] ?? '';
$productId = $_POST['product_id'] ?? '';
$price = $_POST['price'] ?? '';
$stock = $_POST['stock'] ?? '';

$query = "SELECT 
    i.inventory_id, 
    i.product_id,
    p.product_name, 
    i.price, 
    i.stock_quantity, 
    i.total_value, 
    i.received_date, 
    i.last_restock_date, 
    i.damage_stock, 
    i.createdbyid, 
    i.createdate, 
    i.updatedbyid, 
    i.updatedate,
    CASE 
        WHEN (i.stock_quantity - i.damage_stock) <= 0 THEN 'Out of Stock'
        WHEN (i.stock_quantity - i.damage_stock) <= 115 THEN 'Low Stock'
        WHEN (i.stock_quantity - i.damage_stock) <= 280 THEN 'Reorder Needed'
        ELSE 'In Stock'
    END AS stock_level
FROM Inventory i
JOIN Product p ON i.product_id = p.product_id
WHERE 1=1";
if ($inventoryId) $query .= " AND i.inventory_id LIKE '%$inventoryId%'";
if ($productId) $query .= " AND i.product_id LIKE '%$productId%'";
if ($price) $query .= " AND i.price LIKE '%$price%'";
if ($stock) {
    $stockRange = explode('-', $stock);
    if (count($stockRange) == 2) {
        $query .= " AND (i.stock_quantity - i.damage_stock) BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $stockRange[0], $stockRange[1]);
    } else {
        $query .= " AND (i.stock_quantity - i.damage_stock) LIKE '%$stock%'";
    }
}
$result = $stmt ?? $conn->query($query);

// Build HTML response
ob_start();
if ($result->num_rows > 0) :
    while ($row = $result->fetch_assoc()) : ?>
        <tr>
            <td><?= $row['inventory_id'] ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= number_format($row['price'], 2) ?></td>
            <td><?= $row['stock_quantity'] ?></td>
            <td><?= $row['total_value'] ? number_format($row['total_value'], 2) : 'N/A' ?></td>
            <td><?= $row['received_date'] ?? '-' ?></td>
            <td><?= $row['last_restock_date'] ?? '-' ?></td>
            <td><?= $row['damage_stock'] ?? '-' ?></td>
            <td
                class="<?= $row['stock_level'] == 'Out of Stock' ? 'text-danger' : ($row['stock_level'] == 'Low Stock' ? 'text-warning' : ($row['stock_level'] == 'Reorder Needed' ? 'text-primary' : 'text-success')) ?>">
                <?= $row['stock_level'] ?>
            </td>
            <td><?= $row['createdbyid'] ?? '-' ?></td>
            <td><?= $row['createdate'] ?></td>
            <td><?= $row['updatedbyid'] ?? '-' ?></td>
            <td><?= $row['updatedate'] ?? '-' ?></td>
            <td>
                <button class="btn btn-sm text-warning action-btn" onclick='loadEditModal(<?= json_encode($row) ?>)'
                    data-bs-toggle="modal" data-bs-target="#editInventoryModal">
                    <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                </button>
                <button class="btn btn-sm text-danger action-btn" onclick="confirmDelete(<?= $row['inventory_id'] ?>)">
                    <i class="fa fa-trash" style="color: rgb(255, 0, 25);"></i> Delete
                </button>
            </td>
        </tr>
    <?php endwhile;
else : ?>
    <tr>
        <td colspan="13" style="text-align: center; padding: 20px; color: #666;">
            No inventory records found
        </td>
    </tr>
<?php endif;
$html = ob_get_clean();
echo json_encode(['html' => $html]);
?>