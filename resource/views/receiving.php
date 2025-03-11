<?php
include '../../database/database.php';

// Fetch existing receiving records
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
GROUP BY r.receiving_id";
$receiving_result = $conn->query($receiving_query);

if (!$receiving_result) {
    die("Query failed: " . $conn->error);
}

// Fetch suppliers for the order form
$supplier_query = "SELECT supplier_id, supplier_name FROM Supplier";
$supplier_result = $conn->query($supplier_query);

// Fetch products for autocomplete
$product_query = "SELECT product_name FROM Product";
$product_result = $conn->query($product_query);
$products = [];
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row['product_name'];
}
?>

<style>
    .receiving-container {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .receiving-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .receiving-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .receiving-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .receiving-header .supplier-name {
        font-size: 16px;
        font-weight: bold;
        color: #34502b;
    }

    .receiving-header .status {
        font-size: 14px;
        font-weight: bold;
    }

    .status-pending {
        color: #ffc107;
    }

    .status-received {
        color: #28a745;
    }

    .status-cancelled {
        color: #dc3545;
    }

    .receiving-details {
        font-size: 14px;
        color: #555;
    }

    .receiving-details p {
        margin: 5px 0;
    }

    .receiving-details strong {
        color: #333;
    }

    .btn-view,
    .btn-delete {
        background: white;
        color: #34502b;
        border: 1px solid #34502b;
        padding: 5px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        align-self: flex-end;
        font-size: 14px;
    }

    .btn-view:hover,
    .btn-delete:hover {
        background: #34502b;
        color: white;
    }

    .no-records {
        grid-column: 1 / -1;
        text-align: center;
        padding: 20px;
        color: #666;
        font-size: 16px;
    }

    .order-section {
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .order-section h3 {
        color: #34502b;
        margin-bottom: 15px;
    }

    .order-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .supplier-select {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 14px;
    }

    .product-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .product-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .product-card input {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 14px;
    }

    .btn-add-product {
        background: rgb(255, 255, 255);
        color: #2a3f23;
        border: 1px solid #2a3f23;
        padding: 8px;
        border-radius: 5px;
        width: 200px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-remove-product {
        background-color: inherit;
        color: rgb(63, 35, 35);
        padding: 8px;
        border: none;
        width: 50px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-submit-order {
        background: #2a3f23;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
        width: 150px;
    }

    .btn-submit-order:hover {
        background: #2a3f23;
        color: white;
    }

    .condition-damaged {
        color: #dc3545;
        font-weight: bold;
    }

    .condition-good {
        color: #28a745;
        font-weight: bold;
    }

    .btn-clear-product {
        background: rgb(255, 255, 255);
        color: rgb(87, 2, 2);
        border: 1px solid rgb(87, 2, 2);
        padding: 8px;
        border-radius: 5px;
        width: 100px;
        cursor: pointer;
        margin-left: 10px;
        font-size: 14px;
    }

    .btn-add-receiving {
        background: #34502b;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: all 0.3s ease-in-out;
        font-weight: bold;
    }

    .btn-add-receiving:hover {
        transform: translateY(-3px);
    }

    .btn-add-receiving.active {
        background: white;
        color: #34502b;
        border: 1px solid #34502b;
    }

    /* New styles for scrollable modal */
    .modal-dialog.modal-lg {
        max-width: 800px;
    }

    .modal-content {
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-body {
        padding: 20px;
    }

    .table {
        width: 100%;
        margin-bottom: 1rem;
    }
</style>
<!-- Order Form Section -->
<div class="order-section">
    <button class="btn-add-receiving" onclick="toggleAddReceivingForm()">
        <i class="fa fa-add"></i> Create New Order
    </button>
    <div id="orderForm" style="display: none;">
        <h3>Create New Order</h3>
        <form class="order-form" action="../../handlers/create_order_handler.php" method="POST">
            <select class="supplier-select" name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php while ($supplier = $supplier_result->fetch_assoc()) : ?>
                    <option value="<?= $supplier['supplier_id'] ?>"><?= htmlspecialchars($supplier['supplier_name']) ?></option>
                <?php endwhile; ?>
            </select>
            <div class="product-list" id="product-list">
                <div class="product-card">
                    <input type="text" name="products[0][name]" placeholder="Product Name" list="product-suggestions" required>
                    <input type="number" name="products[0][quantity]" placeholder="Quantity" min="1" required>
                    <input type="number" name="products[0][unit_cost]" placeholder="Unit Cost (₱)" step="0.01" min="0" required>
                    <button type="button" class="btn-remove-product" onclick="removeProduct(this)"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <div style="text-align: center;">
                <button type="button" class="btn-add-product" onclick="addProduct()"><i class="fa fa-plus"></i> Add Another Product</button>
                <button type="button" class="btn-clear-product" onclick="clearProducts()"><i class="fa fa-eraser"></i> Clear</button>
            </div>
            <button type="submit" class="btn-submit-order">Submit Order</button>
        </form>
    </div>
</div>

<!-- Datalist for product suggestions -->
<datalist id="product-suggestions">
    <?php foreach ($products as $product_name) : ?>
        <option value="<?= htmlspecialchars($product_name) ?>">
    <?php endforeach; ?>
</datalist>

<!-- Receiving Cards -->
<div class="receiving-container">
    <?php if ($receiving_result->num_rows > 0) : ?>
        <?php while ($row = $receiving_result->fetch_assoc()) : ?>
            <div class="receiving-card">
                <div class="receiving-header">
                    <span class="supplier-name"><?= htmlspecialchars($row['supplier_name']) ?></span>
                    <span class="status status-<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </div>
                <div class="receiving-details">
                    <p><strong>Receiving ID:</strong> <?= $row['receiving_id'] ?></p>
                    <p><strong>Date:</strong> <?= $row['receiving_date'] ?? '-' ?></p>
                    <p><strong>Total Quantity:</strong> <?= $row['total_quantity'] ?></p>
                    <p><strong>Total Cost:</strong> ₱<?= number_format($row['total_cost'], 2) ?? '-' ?></p>
                </div>
                <button class="btn-view" onclick="loadReceivingModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                    <i class="fa fa-eye"></i> View Details
                </button>
                <button class="btn-delete" onclick="confirmDelete(<?= $row['receiving_id'] ?>)">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="no-records">No receiving records found.</div>
    <?php endif; ?>
</div>

<!-- Receiving Details Modal -->
<div class="modal fade" id="receivingModal" tabindex="-1" aria-labelledby="receivingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivingModalLabel">Receiving Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label>Receiving ID:</label>
                <p id="view_receiving_id"></p>
                <label>Supplier:</label>
                <p id="view_supplier_name"></p>
                <label>Receiving Date:</label>
                <p id="view_receiving_date"></p>
                <label>Total Quantity:</label>
                <p id="view_total_quantity"></p>
                <label>Total Cost (₱):</label>
                <p id="view_total_cost"></p>
                <label>Status:</label>
                <p id="view_status"></p>
                <label>Products Received:</label>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Unit Cost (₱)</th>
                            <th>Subtotal Cost (₱)</th>
                            <th>Condition</th>
                        </tr>
                    </thead>
                    <tbody id="view_receiving_products"></tbody>
                </table>
                <label>Created By:</label>
                <p id="view_createdbyid"></p>
                <label>Create Date:</label>
                <p id="view_createdate"></p>
                <label>Updated By:</label>
                <p id="view_updatedbyid"></p>
                <label>Update Date:</label>
                <p id="view_updatedate"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    let productCount = 1;

    function toggleAddReceivingForm() {
        var form = document.getElementById("orderForm");
        var button = document.querySelector(".btn-add-receiving");

        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            button.innerHTML = '<i class="fa fa-times"></i> Close';
            button.classList.add("active");
        } else {
            form.style.display = "none";
            button.innerHTML = '<i class="fa fa-add"></i> Create New Order';
            button.classList.remove("active");
        }
    }

    function addProduct() {
        const productList = document.getElementById('product-list');
        const newProduct = document.createElement('div');
        newProduct.className = 'product-card';
        newProduct.innerHTML = `
            <input type="text" name="products[${productCount}][name]" placeholder="Product Name" list="product-suggestions" required>
            <input type="number" name="products[${productCount}][quantity]" placeholder="Quantity" min="1" required>
            <input type="number" name="products[${productCount}][unit_cost]" placeholder="Unit Cost (₱)" step="0.01" min="0" required>
            <button type="button" class="btn-remove-product" onclick="removeProduct(this)"><i class="fa fa-trash"></i></button>
        `;
        productList.appendChild(newProduct);
        productCount++;
    }

    function removeProduct(button) {
        const productCard = button.parentElement;
        productCard.remove();
    }

    function clearProducts() {
        const productList = document.getElementById('product-list');
        productList.innerHTML = '';
        productCount = 0;
        addProduct();
    }

    function loadReceivingModal(receiving) {
        document.getElementById('view_receiving_id').textContent = receiving.receiving_id;
        document.getElementById('view_supplier_name').textContent = receiving.supplier_name;
        document.getElementById('view_receiving_date').textContent = receiving.receiving_date || '-';
        document.getElementById('view_total_quantity').textContent = receiving.total_quantity;
        document.getElementById('view_total_cost').textContent = Number(receiving.total_cost).toFixed(2) || '-';
        document.getElementById('view_status').textContent = receiving.status ? receiving.status.charAt(0).toUpperCase() + receiving.status.slice(1) : '-';
        document.getElementById('view_createdbyid').textContent = receiving.createdbyid || '-';
        document.getElementById('view_createdate').textContent = receiving.createdate || '-';
        document.getElementById('view_updatedbyid').textContent = receiving.updatedbyid || '-';
        document.getElementById('view_updatedate').textContent = receiving.updatedate || '-';

        const productIds = receiving.product_ids ? receiving.product_ids.split(', ') : [];
        const productNames = receiving.product_names ? receiving.product_names.split(', ') : [];
        const quantities = receiving.quantities ? receiving.quantities.split(', ') : [];
        const unitCosts = receiving.unit_costs ? receiving.unit_costs.split(', ') : [];
        const subtotalCosts = receiving.subtotal_costs ? receiving.subtotal_costs.split(', ') : [];
        const conditions = receiving.conditions ? receiving.conditions.split(', ') : [];

        const tbody = document.getElementById('view_receiving_products');
        tbody.innerHTML = '';

        for (let i = 0; i < productIds.length; i++) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${productIds[i] || '-'}</td>
                <td>${productNames[i] || '-'}</td>
                <td>${quantities[i] || '-'}</td>
                <td>${Number(unitCosts[i]).toFixed(2) || '-'}</td>
                <td>${Number(subtotalCosts[i]).toFixed(2) || '-'}</td>
                <td class="condition-${conditions[i]?.toLowerCase() || ''}">${conditions[i] || '-'}</td>
            `;
            tbody.appendChild(row);
        }

        var modal = new bootstrap.Modal(document.getElementById("receivingModal"));
        modal.show();
    }

    function confirmDelete(receivingId) {
        if (confirm("Are you sure you want to delete this receiving record?")) {
            window.location.href = "../../handlers/deletereceiving_handler.php?id=" + receivingId;
        }
    }

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>