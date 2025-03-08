<?php
include '../../database/database.php';

$query = "SELECT inventory_id, product_id, price, stock_quantity, total_value, 
                 received_date, last_restock_date, damage_stock, 
                 createdbyid, createdate, updatedbyid, updatedate 
          FROM Inventory";
$result = $conn->query($query);
?>

<style>
    html,
    body {
        overflow-x: hidden;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    /* Main Content */
    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        padding: 20px;
        overflow: hidden;
    }

    /* Header */
    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: black;
        padding: 15px;
        border-radius: 5px;
    }

    .search-profile {
        display: flex;
        align-items: center;
    }

    .search-container {
        margin: 20px 0;
        display: flex;
        gap: 10px;
    }

    .search-container input {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .search-btn,
    .clear-btn {
        padding: 8px 15px;
        border: none;
        background: #28a745;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
    }

    .clear-btn {
        background: #dc3545;
        width: 70px;
    }

    /* Table Styling */
    .products-table {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
        /* Enables scrolling for wide tables */
    }

    .table-controls {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .create-btn {
        background: #6b8e5e;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.5s ease-in-out;
    }

    .create-btn:hover {
        background: white;
        color: #6b8e5e;
        border: 1px solid #6b8e5e;
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .table-responsive {
        max-width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background-color: #e6c200 !important;
        color: white !important;
        text-align: center !important;
        padding: 10px;
    }

    th,
    td {
        text-align: center;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }


    tr:hover {
        background: #f1f1f1;
    }

    /* Buttons */
    .btn {
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
    }

    .btn-warning {
        background: #ffc107;
    }

    .btn-danger {
        background: #dc3545;
    }

    .btn-primary {
        background: #007bff;
    }

    /* Modal Styling */
    .modal-content {
        padding: 20px;
        border-radius: 5px;
    }

    .modal-header {
        background: rgb(24, 152, 47);
        color: white;
        padding: 15px;
        border-radius: 5px 5px 0 0;
    }

    .modal-footer {
        display: flex;
        justify-content: space-between;
    }

    /* Responsive Fix */
    @media (max-width: 768px) {
        .sidebar {
            width: 200px;
        }

        .main-content {
            margin-left: 200px;
            width: calc(100% - 200px);
        }

        .search-container {
            flex-direction: column;
        }

        .table-controls {
            justify-content: center;
        }
    }

    @media (max-width: 500px) {
        .sidebar {
            display: none;
            /* Hide sidebar on very small screens */
        }

        .main-content {
            margin-left: 0;
            width: 100%;
        }

        header {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="main-content">
    <header>
        <h1>Inventory</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>
    <hr>
    <div class="search-container">
        <input type="text" id="searchInventoryID" placeholder="Inventory ID">
        <input type="text" id="searchProductID" placeholder="Product ID">
        <input type="text" id="searchPrice" placeholder="Price">
        <input type="text" id="searchStock" placeholder="Stock Quantity">
        <button class="search-btn" onclick="filterInventory()">SEARCH</button>
        <button class="clear-btn" onclick="clearFilters()">CLEAR</button>
    </div>

    <div class="products-table">
        <div class="table-controls">
            <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                CREATE NEW <i class="fa-solid fa-add"></i>
            </button>
        </div>
        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Inventory ID</th>
                        <th>Product ID</th>
                        <th>Price</th>
                        <th>Stock Quantity</th>
                        <th>Total Value</th>
                        <th>Received Date</th>
                        <th>Last Restock</th>
                        <th>Damage Stock</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Updated By</th>
                        <th>Updated Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventory-table-body">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= $row['inventory_id'] ?></td>
                                <td><?= $row['product_id'] ?></td>
                                <td><?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['stock_quantity'] ?></td>
                                <td><?= $row['total_value'] ? number_format($row['total_value'], 2) : 'N/A' ?></td>
                                <td><?= $row['received_date'] ?? 'N/A' ?></td>
                                <td><?= $row['last_restock_date'] ?? 'N/A' ?></td>
                                <td><?= $row['damage_stock'] ?? 'N/A' ?></td>
                                <td><?= $row['createdbyid'] ?? 'N/A' ?></td>
                                <td><?= $row['createdate'] ?></td>
                                <td><?= $row['updatedbyid'] ?? 'N/A' ?></td>
                                <td><?= $row['updatedate'] ?? 'N/A' ?></td>
                                <td class="d-flex gap-2">
                                    <button class="btn btn-sm text-warning" onclick='loadEditModal(<?= json_encode($row) ?>)'
                                        data-bs-toggle="modal" data-bs-target="#editInventoryModal">
                                        <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-danger" onclick="confirmDelete(<?= $row['inventory_id'] ?>)">
                                        <i class="fa fa-trash" style="color:rgb(255, 0, 25);"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 20px; color: #666;">
                                No inventory records found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD INVENTORY MODAL -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInventoryModalLabel">Add Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/addinventory_handler.php" method="POST">
                <div class="modal-body">
                    <label>Product ID:</label>
                    <input type="number" name="product_id" class="form-control" required>

                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>

                    <label>Stock Quantity:</label>
                    <input type="number" name="stock_quantity" class="form-control" required>

                    <label>Total Value:</label>
                    <input type="number" step="0.01" name="total_value" class="form-control">

                    <label>Received Date:</label>
                    <input type="date" name="received_date" class="form-control">

                    <label>Last Restock Date:</label>
                    <input type="date" name="last_restock_date" class="form-control">

                    <label>Damage Stock:</label>
                    <input type="number" name="damage_stock" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Inventory</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT INVENTORY MODAL -->
<div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInventoryModalLabel">Edit Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/editinventory_handler.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="inventory_id">

                    <label>Product ID:</label>
                    <input type="number" name="product_id" class="form-control" required>

                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>

                    <label>Stock Quantity:</label>
                    <input type="number" name="stock_quantity" class="form-control" required>

                    <label>Total Value:</label>
                    <input type="number" step="0.01" name="total_value" class="form-control">

                    <label>Received Date:</label>
                    <input type="date" name="received_date" class="form-control">

                    <label>Last Restock Date:</label>
                    <input type="date" name="last_restock_date" class="form-control">

                    <label>Damage Stock:</label>
                    <input type="number" name="damage_stock" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
        style="width: 290px !important;">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<script>
    function confirmDelete(inventoryId) {
        if (confirm("Are you sure you want to delete this inventory record?")) {
            window.location.href = "../../handlers/delete_inventory_handler.php?id=" + inventoryId;
        }
    }

    function loadEditModal(inventory) {
        document.querySelector("#editInventoryModal input[name='inventory_id']").value = inventory.inventory_id;
        document.querySelector("#editInventoryModal input[name='product_id']").value = inventory.product_id;
        document.querySelector("#editInventoryModal input[name='price']").value = inventory.price;
        document.querySelector("#editInventoryModal input[name='stock_quantity']").value = inventory.stock_quantity;
        document.querySelector("#editInventoryModal input[name='total_value']").value = inventory.total_value || '';
        document.querySelector("#editInventoryModal input[name='received_date']").value = inventory.received_date || '';
        document.querySelector("#editInventoryModal input[name='last_restock_date']").value = inventory.last_restock_date ||
            '';
        document.querySelector("#editInventoryModal input[name='damage_stock']").value = inventory.damage_stock || '';
    }

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>