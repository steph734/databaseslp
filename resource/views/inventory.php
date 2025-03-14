<?php
include '../../database/database.php';

// Initialize query components
$whereConditions = [];
$orderClause = "ORDER BY i.inventory_id ASC"; // Default ordering

// Handle search container filters (from SEARCH button)
if (isset($_GET['search']) && $_GET['search'] === '1') {
    if (isset($_GET['inventory_id']) && !empty($_GET['inventory_id'])) {
        $whereConditions[] = "i.inventory_id = '" . $conn->real_escape_string($_GET['inventory_id']) . "'";
    }
    if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
        $whereConditions[] = "p.product_name LIKE '%" . $conn->real_escape_string($_GET['product_name']) . "%'";
    }
    if (isset($_GET['price']) && !empty($_GET['price'])) {
        $whereConditions[] = "i.price = '" . $conn->real_escape_string($_GET['price']) . "'";
    }
    if (isset($_GET['stock']) && !empty($_GET['stock'])) {
        $whereConditions[] = "i.stock_quantity = '" . $conn->real_escape_string($_GET['stock']) . "'";
    }
}

// Handle table control filters (stock level)
if (isset($_GET['stock_level']) && !empty($_GET['stock_level'])) {
    $stockLevel = $conn->real_escape_string($_GET['stock_level']);
    if ($stockLevel === 'Out of Stock') {
        $whereConditions[] = "(i.stock_quantity - i.damage_stock) <= 0";
    } elseif ($stockLevel === 'Low Stock') {
        $whereConditions[] = "(i.stock_quantity - i.damage_stock) > 0 AND (i.stock_quantity - i.damage_stock) <= 30";
    } elseif ($stockLevel === 'Reorder Needed') {
        $whereConditions[] = "(i.stock_quantity - i.damage_stock) > 30 AND (i.stock_quantity - i.damage_stock) <= 50";
    } elseif ($stockLevel === 'In Stock') {
        $whereConditions[] = "(i.stock_quantity - i.damage_stock) > 50";
    }
}

// Handle ordering (from table controls)
if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
    list($column, $direction) = explode('|', $_GET['order_by']);
    $column = $conn->real_escape_string($column);
    $direction = strtoupper($conn->real_escape_string($direction));
    if (
        in_array($column, ['inventory_id', 'product_name', 'price', 'stock_quantity', 'received_date']) &&
        in_array($direction, ['ASC', 'DESC'])
    ) {
        $orderClause = "ORDER BY " . ($column === 'product_name' ? 'p.' : 'i.') . "$column $direction";
    }
}

// Build WHERE clause
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Count query
$countQuery = "SELECT 
    SUM(CASE WHEN (i.stock_quantity - i.damage_stock) <= 0 THEN 1 ELSE 0 END) AS out_of_stock_count,
    SUM(CASE WHEN (i.stock_quantity - i.damage_stock) > 0 AND (i.stock_quantity - i.damage_stock) <= 115 THEN 1 ELSE 0 END) AS low_stock_count
FROM Inventory i
JOIN Product p ON i.product_id = p.product_id";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$outOfStockCount = $countRow['out_of_stock_count'] ?? 0;
$lowStockCount = $countRow['low_stock_count'] ?? 0;
$totalCriticalCount = $outOfStockCount + $lowStockCount;

// Main query
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
    p.quantity AS product_quantity,
    CASE 
        WHEN (i.stock_quantity - i.damage_stock) <= 0 THEN 'Out of Stock'
        WHEN (i.stock_quantity - i.damage_stock) <= 30 THEN 'Low Stock'
        WHEN (i.stock_quantity - i.damage_stock) <= 50 THEN 'Reorder Needed'
        ELSE 'In Stock'
    END AS stock_level
FROM Inventory i
JOIN Product p ON i.product_id = p.product_id
$whereClause
$orderClause";
$result = $conn->query($query);

if (!$result) {
    echo "Query failed: " . $conn->error;
    exit;
}

// Product query
$productQuery = "SELECT p.product_id, p.product_name, p.quantity 
                 FROM Product p
                 LEFT JOIN Inventory i ON p.product_id = i.product_id
                 WHERE i.product_id IS NULL";
$productResult = $conn->query($productQuery);
?>

<style>
    html,
    body {
        overflow-x: hidden;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background: url('../../path/to/your/image.jpg') no-repeat center center fixed;
        background-size: cover;
    }

    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        padding: 20px;
        overflow: hidden;
    }

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
        background: #34502b;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
    }

    .clear-btn {
        background: rgb(255, 255, 255);
        width: 70px;
        color: #34502b;
        border: 1px solid #34502b;
    }

    .products-table {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
    }

    .table-controls {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
        gap: 10px;
        align-items: center;
    }

    .create-btn {
        background: #34502b;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    .create-btn:hover {
        background: white;
        color: #34502b;
        border: 1px solid #34502b;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .table-responsive {
        max-width: 100%;
        overflow-x: auto;
    }

    thead {
        background: rgb(255, 255, 255) !important;
    }

    th {
        color: rgb(22, 21, 21) !important;
        text-align: center !important;
        padding: 10px;
        font-size: 14px !important;
    }

    th,
    td {
        text-align: center;
        padding: 10px;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
    }

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

    .modal-content {
        padding: 20px;
        border-radius: 5px;
    }

    .modal-header {
        color: #34502b;
        padding: 15px;
        border-radius: 5px 5px 0 0;
    }

    .modal-footer {
        display: flex;
    }

    .validation-message {
        color: #dc3545;
        font-size: 0.9em;
        margin-top: 5px;
        display: none;
    }

    /* Custom styling for dropdowns */
    .custom-select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        background-color: white;
        cursor: pointer;
        width: 50px;
        /* Fixed width for the select box */
    }

    .select2-container {
        width: 50px !important;
        /* Fixed width for the container */
    }

    .select2-selection__rendered {
        display: flex;
        align-items: center;
        padding: 0 5px;
        justify-content: center;
        /* Center the icon */
    }

    .select2-selection__rendered i {
        font-size: 16px;
    }


    .custom-select:focus {
        outline: none;
        border-color: #34502b;
        box-shadow: 0 0 5px rgba(52, 80, 43, 0.5);
    }

    /* Style the dropdown menu */
    .select2-dropdown {
        width: 200px !important;
        /* Wider dropdown for full text */
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 1050;
        /* Ensure it floats above other elements */
    }

    .select2-results__option {
        padding: 8px;
        display: flex;
        align-items: center;
    }

    /* Media queries */
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
            flex-wrap: wrap;
            justify-content: center;
        }

        .custom-select,
        .select2-container {
            width: 50px !important;
            /* Maintain size on smaller screens */
        }
    }

    @media (max-width: 500px) {
        .sidebar {
            display: none;
        }

        .main-content {
            margin-left: 0;
            width: 100%;
        }

        header {
            flex-direction: column;
            text-align: center;
        }

        .stock-legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 14px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            display: inline-block;
        }

        .custom-select,
        .select2-container {
            width: 50px !important;
            /* Maintain size on smallest screens */
        }
    }
</style>
<div class="main-content">
    <header>
        <h1>Inventory</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>

    <hr>
    <div class="search-container">
        <input type="text" id="searchInventoryID" placeholder="Inventory ID">
        <input type="text" id="searchProductName" placeholder="Product Name">
        <input type="text" id="searchPrice" placeholder="Price">
        <input type="text" id="searchStock" placeholder="Stock Quantity">
        <button class="search-btn" onclick="searchInventory()">SEARCH</button>
        <button class="clear-btn" onclick="clearSearch()">CLEAR</button>
    </div>

    <?php if ($totalCriticalCount > 0) : ?>
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert"
            style="width: auto !important; padding-right: 2.5rem !important;">
            <div class="alert-content">
                <?php
                $full_warning_message = "Warning: ";
                if ($outOfStockCount > 0) {
                    $full_warning_message .= "{$outOfStockCount} product(s) are out of stock";
                    if ($lowStockCount > 0) {
                        $full_warning_message .= " and {$lowStockCount} product(s) are low stock.";
                    } else {
                        $full_warning_message .= ".";
                    }
                } elseif ($lowStockCount > 0) {
                    $full_warning_message .= "{$lowStockCount} product(s) are low stock.";
                }
                $short_warning_message = strlen($full_warning_message) > 100 ? substr($full_warning_message, 0, 100) . '...' : $full_warning_message;
                ?>
                <span class="alert-short"><?= htmlspecialchars($short_warning_message) ?></span>
                <span class="alert-full d-none"><?= htmlspecialchars($full_warning_message) ?></span>
                <?php if (strlen($full_warning_message) > 100) : ?>
                    <button type="button" class="btn btn-link btn-sm toggle-message">Show More</button>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="products-table">
        <div class="table-controls">
            <select id="stockLevelFilter" class="custom-select" onchange="applyTableFilters()">
                <option value="" data-icon="fa-solid fa-layer-group">All Stock Levels</option>
                <option value="Out of Stock" data-icon="fa-solid fa-ban">Out of Stock</option>
                <option value="Low Stock" data-icon="fa-solid fa-triangle-exclamation">Low Stock</option>
                <option value="Reorder Needed" data-icon="fa-solid fa-cart-plus">Reorder Needed</option>
                <option value="In Stock" data-icon="fa-solid fa-check-circle">In Stock</option>
            </select>

            <select id="orderBy" class="custom-select" onchange="applyTableFilters()">
                <option value="inventory_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">ID (Ascending)</option>
                <option value="inventory_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">ID (Descending)</option>
                <option value="product_name|ASC" data-icon="fa-solid fa-arrow-up-a-z">Product Name (A-Z)</option>
                <option value="product_name|DESC" data-icon="fa-solid fa-arrow-down-z-a">Product Name (Z-A)</option>
                <option value="price|ASC" data-icon="fa-solid fa-arrow-up-wide-short">Price (Low to High)</option>
                <option value="price|DESC" data-icon="fa-solid fa-arrow-down-short-wide">Price (High to Low)</option>
                <option value="stock_quantity|ASC" data-icon="fa-solid fa-arrow-up-1-9">Stock (Low to High)</option>
                <option value="stock_quantity|DESC" data-icon="fa-solid fa-arrow-down-9-1">Stock (High to Low)</option>
                <option value="received_date|DESC" data-icon="fa-solid fa-arrow-down-long">Received Date (Newest)
                </option>
                <option value="received_date|ASC" data-icon="fa-solid fa-arrow-up-long">Received Date (Oldest)</option>
            </select>

            <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="fa-solid fa-pen"></i> UPDATE INVENTORY
            </button>
        </div>
        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock Quantity</th>
                        <th>Total Value</th>
                        <th>Received Date</th>
                        <th>Last Restock</th>
                        <th>Damage Stock</th>
                        <th>Stock Level</th>
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
                                <td><input type="checkbox"></td>
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
                                    <button class="btn btn-sm text-warning action-btn"
                                        onclick='loadEditModal(<?= json_encode($row) ?>)' data-bs-toggle="modal"
                                        data-bs-target="#editInventoryModal">
                                        <i class="fa fa-edit" style="color: #ffc107;"></i> Update
                                    </button>
                                    <button class="btn btn-sm text-danger action-btn"
                                        onclick="confirmDelete(<?= $row['inventory_id'] ?>)">
                                        <i class="fa fa-trash" style="color: rgb(255, 0, 25);"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="15" style="text-align: center; padding: 20px; color: #666;">
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
            <form action="../../handlers/updateinventory_handler.php" method="POST" id="addInventoryForm">
                <div class="modal-body">
                    <label>Product:</label>
                    <select name="product_id" id="addProductSelect" class="form-control" required
                        onchange="checkProductLimit(this.value)">
                        <option value="" disabled selected>Pick a product</option>
                        <?php
                        $productResult->data_seek(0);
                        while ($product = $productResult->fetch_assoc()) : ?>
                            <option value="<?= htmlspecialchars($product['product_id']) ?>"
                                data-quantity="<?= $product['quantity'] ?>">
                                <?= htmlspecialchars($product['product_id'] . ' - ' . $product['product_name'] . ' (Max: ' . $product['quantity'] . ')') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" id="addPriceInput" class="form-control" required
                        min="0">

                    <label>Quantity to Add:</label>
                    <input type="number" name="quantity_to_add" id="quantityToAddInput" class="form-control" required
                        min="1" oninput="validateQuantity()">
                    <p id="quantityValidation" class="validation-message">Quantity cannot exceed available stock.</p>
                    <p id="currentStockPreview" class="mt-2 text-muted"></p>
                    <p id="newStockPreview" class="mt-2 text-success"></p>

                    <label>Received Date:</label>
                    <input type="date" name="received_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="color: white; background-color: #34502b;">Add</button>
                    <button type="button" class="btn" data-bs-dismiss="modal"
                        style="color: #34502b; background-color: rgb(255, 255, 255); border: 1px solid #34502b;">Cancel</button>
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
            <form action="../../handlers/editinventory_handler.php" method="POST" id="editInventoryForm">
                <div class="modal-body">
                    <input type="hidden" name="inventory_id">
                    <label>Product:</label>
                    <input type="text" id="editProductName" class="form-control" readonly>
                    <input type="hidden" name="product_id" id="editProductId">

                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required min="0">

                    <label>Stock Quantity:</label>
                    <input type="number" name="stock_quantity" id="stockQuantityInput" class="form-control" required
                        min="0" oninput="validateEditQuantity()">
                    <p id="stockValidation" class="validation-message">Stock quantity must be zero or more.</p>
                    <p id="remainingQuantityPreview" class="mt-2 text-muted"></p>

                    <label>Total Value:</label>
                    <input type="number" step="0.01" name="total_value" class="form-control" min="0">

                    <label>Received Date:</label>
                    <input type="date" name="received_date" class="form-control">

                    <label>Last Restock Date:</label>
                    <input type="date" name="last_restock_date" class="form-control">

                    <label>Damage Stock:</label>
                    <input type="number" name="damage_stock" id="damageStockInput" class="form-control" min="0"
                        oninput="validateEditQuantity()">
                    <p id="damageStockValidation" class="validation-message">Damage stock cannot exceed stock quantity.
                    </p>

                    <p id="stockLevelPreview" class="mt-2"></p>
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
    <div class="alert alert-success alert-dismissible fade show floating-alert text-center" role="alert"
        style="width: auto !important; padding-right: 2.5rem !important;">
        <div class="alert-content">
            <?php
            $full_message = $_SESSION['success'];
            $short_message = strlen($full_message) > 100 ? substr($full_message, 0, 100) . '...' : $full_message;
            ?>
            <span class="alert-short"><?= htmlspecialchars($short_message) ?></span>
            <span class="alert-full d-none"><?= htmlspecialchars($full_message) ?></span>
            <?php if (strlen($full_message) > 100) : ?>
                <button type="button" class="btn btn-link btn-sm toggle-message">Show More</button>
            <?php endif; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert"
        style="width: auto !important; padding-right: 2.5rem !important;">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Include jQuery and Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for stockLevelFilter
        $('#stockLevelFilter').select2({
            templateResult: formatOption, // Full text with icon in dropdown
            templateSelection: formatSelection, // Only icon in selected option
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true, // Allow dropdown to size based on content
            width: '50px' // Fixed width for the container
        });

        // Initialize Select2 for orderBy
        $('#orderBy').select2({
            templateResult: formatOption, // Full text with icon in dropdown
            templateSelection: formatSelection, // Only icon in selected option
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true, // Allow dropdown to size based on content
            width: '50px' // Fixed width for the container
        });

        // Preserve onchange functionality
        $('#stockLevelFilter, #orderBy').on('change', function() {
            applyTableFilters();
        });
    });

    // Format the dropdown options (icon + text)
    function formatOption(option) {
        if (!option.element) {
            return option.text;
        }
        var $option = $(
            '<span><i class="' + $(option.element).data('icon') + ' me-2"></i>' + option.text + '</span>'
        );
        return $option;
    }

    // Format the selected option (only icon)
    function formatSelection(option) {
        if (!option.element) {
            return option.text; // Fallback to text if no element
        }
        var $selection = $(
            '<span><i class="' + $(option.element).data('icon') + '"></i></span>'
        );
        return $selection;
    }

    function confirmDelete(inventoryId) {
        if (confirm("Are you sure you want to delete this inventory record?")) {
            window.location.href = "../../handlers/delete_inventory_handler.php?id=" + inventoryId;
        }
    }

    function loadEditModal(inventory) {
        document.querySelector("#editInventoryModal input[name='inventory_id']").value = inventory.inventory_id;
        document.querySelector("#editInventoryModal #editProductId").value = inventory.product_id;
        document.querySelector("#editInventoryModal #editProductName").value = inventory.product_name;
        document.querySelector("#editInventoryModal input[name='price']").value = inventory.price;
        document.querySelector("#editInventoryModal input[name='stock_quantity']").value = inventory.stock_quantity;
        document.querySelector("#editInventoryModal input[name='total_value']").value = inventory.total_value || '';
        document.querySelector("#editInventoryModal input[name='received_date']").value = inventory.received_date || '';
        document.querySelector("#editInventoryModal input[name='last_restock_date']").value = inventory.last_restock_date ||
            '';
        document.querySelector("#editInventoryModal input[name='damage_stock']").value = inventory.damage_stock || '';

        document.querySelector("#stockQuantityInput").dataset.productQuantity = inventory.product_quantity;
        validateEditQuantity();
    }

    function validateEditQuantity() {
        const stockQuantityInput = document.querySelector("#stockQuantityInput");
        const damageStockInput = document.querySelector("#damageStockInput");
        const stockValidation = document.querySelector("#stockValidation");
        const damageStockValidation = document.querySelector("#damageStockValidation");
        const remainingQuantityPreview = document.querySelector("#remainingQuantityPreview");
        const productQuantity = parseInt(stockQuantityInput.dataset.productQuantity) || 0;

        const stockQuantity = stockQuantityInput.value === '' ? null : parseInt(stockQuantityInput.value);
        const damageStock = damageStockInput.value === '' ? null : parseInt(damageStockInput.value);

        if (stockQuantity === null) {
            stockValidation.style.display = 'none';
            remainingQuantityPreview.textContent = `Remaining Quantity to Store: ${productQuantity}`;
        } else if (stockQuantity < 0) {
            stockQuantityInput.value = 0;
            stockValidation.textContent = 'Stock quantity must be zero or more.';
            stockValidation.style.display = 'block';
            remainingQuantityPreview.textContent = `Remaining Quantity to Store: ${productQuantity}`;
        } else if (stockQuantity > productQuantity) {
            stockQuantityInput.value = productQuantity;
            stockValidation.textContent = `Stock quantity cannot exceed product quantity (${productQuantity}).`;
            stockValidation.style.display = 'block';
            remainingQuantityPreview.textContent = `Remaining Quantity to Store: 0`;
        } else {
            stockValidation.style.display = 'none';
            remainingQuantityPreview.textContent = `Remaining Quantity to Store: ${productQuantity - stockQuantity}`;
        }

        if (damageStock === null || stockQuantity === null) {
            damageStockValidation.style.display = 'none';
        } else if (damageStock < 0) {
            damageStockInput.value = 0;
            damageStockValidation.textContent = 'Damage stock must be zero or more.';
            damageStockValidation.style.display = 'block';
        } else if (damageStock > stockQuantity) {
            damageStockInput.value = stockQuantity;
            damageStockValidation.textContent = 'Damage stock cannot exceed stock quantity.';
            damageStockValidation.style.display = 'block';
        } else {
            damageStockValidation.style.display = 'none';
        }

        updateStockLevelPreview();
    }

    function updateStockLevelPreview() {
        const stockQuantityInput = document.querySelector("#stockQuantityInput");
        const damageStockInput = document.querySelector("#damageStockInput");
        const stockQuantity = stockQuantityInput.value === '' ? 0 : parseInt(stockQuantityInput.value);
        const damageStock = damageStockInput.value === '' ? 0 : parseInt(damageStockInput.value);
        const effectiveStock = stockQuantity - damageStock;
        let stockLevel;

        if (effectiveStock <= 0) stockLevel = 'Out of Stock';
        else if (effectiveStock <= 115) stockLevel = 'Low Stock';
        else if (effectiveStock <= 280) stockLevel = 'Reorder Needed';
        else stockLevel = 'In Stock';

        const previewElement = document.querySelector("#stockLevelPreview");
        if (previewElement) {
            previewElement.textContent = `Stock Level Preview: ${stockLevel}`;
            previewElement.className =
                `mt-2 ${stockLevel === 'Out of Stock' ? 'text-danger' : stockLevel === 'Low Stock' ? 'text-warning' : stockLevel === 'Reorder Needed' ? 'text-primary' : 'text-success'}`;
        }
    }

    function checkProductLimit(productId) {
        const quantityInput = document.getElementById('quantityToAddInput');
        const currentStockPreview = document.getElementById('currentStockPreview');
        const newStockPreview = document.getElementById('newStockPreview');
        const validationMessage = document.getElementById('quantityValidation');

        if (!productId) {
            currentStockPreview.textContent = '';
            newStockPreview.textContent = '';
            quantityInput.max = '';
            quantityInput.dataset.currentStock = '';
            quantityInput.dataset.maxQuantity = '';
            quantityInput.value = '';
            validationMessage.style.display = 'none';
            return;
        }

        const select = document.getElementById('addProductSelect');
        const maxQuantity = parseInt(select.options[select.selectedIndex].dataset.quantity) || 0;

        currentStockPreview.textContent = 'Current Stock: 0';
        quantityInput.dataset.currentStock = 0;
        quantityInput.dataset.maxQuantity = maxQuantity;
        quantityInput.max = maxQuantity;

        if (quantityInput.value !== '') {
            validateQuantity();
        } else {
            newStockPreview.textContent = '';
            validationMessage.style.display = 'none';
        }
    }

    function validateQuantity() {
        const quantityInput = document.getElementById('quantityToAddInput');
        const validationMessage = document.getElementById('quantityValidation');
        const currentStock = parseInt(quantityInput.dataset.currentStock) || 0;
        const maxQuantity = parseInt(quantityInput.dataset.maxQuantity) || 0;
        const quantityToAdd = quantityInput.value === '' ? null : parseInt(quantityInput.value);

        if (quantityToAdd === null) {
            document.getElementById('newStockPreview').textContent = '';
            validationMessage.style.display = 'none';
            return;
        }

        if (quantityToAdd <= 0) {
            quantityInput.value = '';
            validationMessage.textContent = 'Quantity must be more than zero!';
            validationMessage.style.display = 'block';
            document.getElementById('newStockPreview').textContent = '';
        } else if (quantityToAdd + currentStock > maxQuantity) {
            quantityInput.value = maxQuantity;
            validationMessage.textContent = 'Quantity cannot exceed available stock.';
            validationMessage.style.display = 'block';
            document.getElementById('newStockPreview').textContent = `New Stock Quantity: ${maxQuantity}`;
        } else {
            validationMessage.style.display = 'none';
            document.getElementById('newStockPreview').textContent = `New Stock Quantity: ${currentStock + quantityToAdd}`;
        }
    }

    function searchInventory() {
        const inventoryId = document.getElementById('searchInventoryID').value.trim();
        const productName = document.getElementById('searchProductName').value.trim();
        const price = document.getElementById('searchPrice').value.trim();
        const stock = document.getElementById('searchStock').value.trim();

        const stockLevel = document.getElementById('stockLevelFilter').value;
        const orderBy = document.getElementById('orderBy').value;

        let url = '../../resource/layout/web-layout.php?page=inventory&search=1';
        const params = [];

        if (inventoryId) params.push(`inventory_id=${encodeURIComponent(inventoryId)}`);
        if (productName) params.push(`product_name=${encodeURIComponent(productName)}`);
        if (price) params.push(`price=${encodeURIComponent(price)}`);
        if (stock) params.push(`stock=${encodeURIComponent(stock)}`);
        if (stockLevel) params.push(`stock_level=${encodeURIComponent(stockLevel)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

        if (params.length > 0) url += '&' + params.join('&');
        console.log('Search URL:', url);
        window.location.href = url;
    }

    function applyTableFilters() {
        const stockLevel = document.getElementById('stockLevelFilter').value;
        const orderBy = document.getElementById('orderBy').value;

        const inventoryId = document.getElementById('searchInventoryID').value.trim();
        const productName = document.getElementById('searchProductName').value.trim();
        const price = document.getElementById('searchPrice').value.trim();
        const stock = document.getElementById('searchStock').value.trim();

        let url = '../../resource/layout/web-layout.php?page=inventory';
        const params = [];

        if (inventoryId || productName || price || stock) params.push('search=1');
        if (inventoryId) params.push(`inventory_id=${encodeURIComponent(inventoryId)}`);
        if (productName) params.push(`product_name=${encodeURIComponent(productName)}`);
        if (price) params.push(`price=${encodeURIComponent(price)}`);
        if (stock) params.push(`stock=${encodeURIComponent(stock)}`);
        if (stockLevel) params.push(`stock_level=${encodeURIComponent(stockLevel)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

        if (params.length > 0) url += '&' + params.join('&');
        console.log('Table Filter URL:', url);
        window.location.href = url;
    }

    function clearSearch() {
        document.getElementById('searchInventoryID').value = '';
        document.getElementById('searchProductName').value = '';
        document.getElementById('searchPrice').value = '';
        document.getElementById('searchStock').value = '';

        const stockLevel = document.getElementById('stockLevelFilter').value;
        const orderBy = document.getElementById('orderBy').value;

        let url = '../../resource/layout/web-layout.php?page=inventory';
        const params = [];

        if (stockLevel) params.push(`stock_level=${encodeURIComponent(stockLevel)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

        if (params.length > 0) url += '&' + params.join('&');
        console.log('Clear Search URL:', url);
        window.location.href = url;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggleButtons = document.querySelectorAll('.toggle-message');
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const alert = button.closest('.alert');
                const shortMessage = alert.querySelector('.alert-short');
                const fullMessage = alert.querySelector('.alert-full');
                if (shortMessage.classList.contains('d-none')) {
                    shortMessage.classList.remove('d-none');
                    fullMessage.classList.add('d-none');
                    button.textContent = 'Show More';
                } else {
                    shortMessage.classList.add('d-none');
                    fullMessage.classList.remove('d-none');
                    button.textContent = 'Show Less';
                }
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        document.getElementById('searchInventoryID').value = urlParams.get('inventory_id') || '';
        document.getElementById('searchProductName').value = urlParams.get('product_name') || '';
        document.getElementById('searchPrice').value = urlParams.get('price') || '';
        document.getElementById('searchStock').value = urlParams.get('stock') || '';
        document.getElementById('stockLevelFilter').value = urlParams.get('stock_level') || '';
        document.getElementById('orderBy').value = urlParams.get('order_by') || 'inventory_id|ASC';

        window.filterCriticalStock = function() {
            document.getElementById('searchStock').value = '0-115';
            searchInventory();
        };

        console.log('Loaded URL Params:', urlParams.toString());
    });

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 20000);
</script>