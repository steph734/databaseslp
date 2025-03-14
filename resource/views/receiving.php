<?php
include '../../database/database.php';

// Pagination settings
$records_per_page = 6; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch total number of receiving records for pagination
$total_query = "SELECT COUNT(DISTINCT r.receiving_id) as total FROM receiving r";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated receiving records, ordered by receiving_date DESC
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
ORDER BY r.receiving_date DESC
LIMIT ? OFFSET ?";
$stmt = $conn->prepare($receiving_query);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$receiving_result = $stmt->get_result();

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

    .status-container {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .status-text {
        font-size: 14px;
        font-weight: bold;
        margin-right: 5px;
        transition: color 0.3s ease, opacity 0.3s ease;
    }

    .status-text.fade {
        opacity: 0;
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

    .status-box-received {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        background-color: #e6ffe6;
        border: 1px solid #28a745;
        color: #28a745;
    }

    .status-dropdown {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background: transparent;
        border: none;
        font-size: 14px;
        cursor: pointer;
        color: #555;
        width: 20px;
        padding: 0;
        position: relative;
    }

    .status-dropdown:focus {
        outline: none;
    }

    .status-container:not(.received)::after {
        content: '\25BC';
        font-size: 10px;
        color: #555;
        position: absolute;
        right: 5px;
        pointer-events: none;
    }

    .status-container.received::after {
        content: none;
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
    .btn-delete,
    .btn-invoice {
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
    .btn-delete:hover,
    .btn-invoice:hover {
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

    .modal-dialog.modal-lg {
        max-width: 800px;
    }

    .modal-content {
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        flex-shrink: 0;
        background: #f9f9f9;
        border-bottom: 1px solid #ddd;
    }

    .modal-title {
        font-weight: 300;
        color: #34502b;
    }

    .modal-body {
        max-height: 60vh;
        overflow-y: auto;
        padding: 20px;
        line-height: 24px;
        color: #555;
    }

    .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #eee;
    }

    .table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }

    .table td {
        padding: 5px;
        vertical-align: top;
    }

    .table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }

    .table tr.item td {
        border-bottom: 1px solid #eee;
    }

    .table tr.item.last td {
        border-bottom: none;
    }

    .table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
        text-align: right;
    }

    .modal-body label {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .modal-body p {
        margin: 0 0 15px 0;
        color: #555;
    }

    #confirmReceivedModal .modal-header {
        background: #f9f9f9;
        border-bottom: 1px solid #ddd;
    }

    #confirmReceivedModal .modal-title {
        font-weight: 300;
        color: #34502b;
    }

    #confirmReceivedModal .modal-body {
        color: #555;
        font-size: 16px;
    }

    #confirmReceivedModal .btn-primary {
        background: #34502b;
        border: none;
    }

    #confirmReceivedModal .btn-primary:hover {
        background: #2a3f23;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px 0;
        gap: 10px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-decoration: none;
        color: #34502b;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: #34502b;
        color: white;
        border-color: #34502b;
    }

    .pagination .current {
        background: #34502b;
        color: white;
        border-color: #34502b;
    }

    .pagination .disabled {
        color: #aaa;
        border-color: #ddd;
        pointer-events: none;
    }
</style>

<!-- Order Form Section -->
<div class="order-section">
    <button class="btn-add-receiving" onclick="toggleAddReceivingForm()">
        <i class="fa fa-add"></i> Create New Order
    </button>
    <div id="orderForm" style="display: none;">
        <h3>Create New Order</h3>
        <form class="order-form" action="../../handlers/create_order_handler.php" method="POST" id="createOrderForm">
            <select class="supplier-select" name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php while ($supplier = $supplier_result->fetch_assoc()) : ?>
                    <option value="<?= $supplier['supplier_id'] ?>"><?= htmlspecialchars($supplier['supplier_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div class="product-list" id="product-list">
                <div class="product-card">
                    <input type="text" name="products[0][name]" placeholder="Product Name" list="product-suggestions"
                        required>
                    <input type="number" name="products[0][quantity]" placeholder="Quantity" min="1" required>
                    <input type="number" name="products[0][unit_cost]" placeholder="Unit Cost (₱)" step="0.01" min="0"
                        required>
                    <button type="button" class="btn-remove-product" onclick="removeProduct(this)"><i
                            class="fa fa-trash"></i></button>
                </div>
            </div>
            <div style="text-align: center;">
                <button type="button" class="btn-add-product" onclick="addProduct()"><i class="fa fa-plus"></i> Add
                    Another Product</button>
                <button type="button" class="btn-clear-product" onclick="clearProducts()"><i class="fa fa-eraser"></i>
                    Clear</button>
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
<div class="receiving-container" id="receiving-container">
    <?php if ($receiving_result->num_rows > 0) : ?>
        <?php while ($row = $receiving_result->fetch_assoc()) : ?>
            <div class="receiving-card" id="card-<?= $row['receiving_id'] ?>">
                <div class="receiving-header">
                    <span class="supplier-name"><?= htmlspecialchars($row['supplier_name']) ?></span>
                    <div class="status-container <?= strtolower($row['status']) === 'received' ? 'received' : '' ?>"
                        id="status-container-<?= $row['receiving_id'] ?>">
                        <?php if (strtolower($row['status']) === 'received') : ?>
                            <span class="status-box-received" id="status-text-<?= $row['receiving_id'] ?>">Received</span>
                        <?php else : ?>
                            <span class="status-text status-<?= strtolower($row['status']) ?>"
                                id="status-text-<?= $row['receiving_id'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                            <select class="status-dropdown" onchange="updateStatus(<?= $row['receiving_id'] ?>, this.value)">
                                <option value="" disabled selected></option>
                                <option value="pending">Pending</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="receiving-details">
                    <p><strong>Receiving ID:</strong> <?= $row['receiving_id'] ?></p>
                    <p><strong>Date:</strong> <?= $row['receiving_date'] ?? '-' ?></p>
                    <p><strong>Total Quantity:</strong> <?= $row['total_quantity'] ?></p>
                    <p><strong>Total Cost:</strong> ₱<?= number_format($row['total_cost'], 2) ?? '-' ?></p>
                </div>
                <button class="btn-invoice" onclick="loadInvoiceModal(<?= htmlspecialchars(json_encode($row)) ?>)">
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

<!-- Pagination Controls -->
<div class="pagination" id="pagination">
    <?php if ($page > 1) : ?>
        <a href="#" onclick="fetchPage(<?= $page - 1 ?>); return false;">« Previous</a>
    <?php else : ?>
        <span class="disabled">« Previous</span>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
        <?php if ($i == $page) : ?>
            <span class="current"><?= $i ?></span>
        <?php else : ?>
            <a href="#" onclick="fetchPage(<?= $i ?>); return false;"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $total_pages) : ?>
        <a href="#" onclick="fetchPage(<?= $page + 1 ?>); return false;">Next »</a>
    <?php else : ?>
        <span class="disabled">Next »</span>
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

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Receiving Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div>
                    <label>Receive #:</label>
                    <p id="view_invoice_number"></p>
                    <label>Created:</label>
                    <p id="view_created_date"></p>
                    <label>Due:</label>
                    <p id="view_due_date"></p>
                </div>
                <div>
                    <label>To:</label>
                    <p id="view_from">Riverview SLP Association<br />Davao City, Davao Del Sur, 8000</p>
                    <label>From:</label>
                    <p id="view_to"></p>
                </div>
                <label>Payment Method:</label>
                <p id="view_payment_method">Cash</p>
                <label>Items:</label>
                <table class="table table-striped">
                    <thead>
                        <tr class="heading">
                            <td>Product Name</td>
                            <td>Quantity</td>
                            <td>Unit Cost (₱)</td>
                            <td>Subtotal Cost (₱)</td>
                        </tr>
                    </thead>
                    <tbody id="view_invoice_items"></tbody>
                    <tr class="total">
                        <td colspan="3"></td>
                        <td id="view_invoice_total"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmReceivedModal" tabindex="-1" aria-labelledby="confirmReceivedModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmReceivedModalLabel">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Marking this order as 'Received' will update product quantities in inventory and set supplier
                    details. This action cannot be undone via status change. Do you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    id="cancelReceivedBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmReceivedBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    let productCount = 1;
    let currentPage = <?php echo $page; ?>;
    let totalRecords = <?php echo $total_records; ?>;
    const recordsPerPage = <?php echo $records_per_page; ?>;

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
        document.getElementById('view_status').textContent = receiving.status ? receiving.status.charAt(0).toUpperCase() +
            receiving.status.slice(1) : '-';
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

    function loadInvoiceModal(receiving) {
        document.getElementById('view_invoice_number').textContent = receiving.receiving_id;
        document.getElementById('view_created_date').textContent = receiving.createdate || '-';
        document.getElementById('view_due_date').textContent = receiving.receiving_date || '-';
        document.getElementById('view_to').textContent = receiving.supplier_name;

        const productNames = receiving.product_names ? receiving.product_names.split(', ') : [];
        const quantities = receiving.quantities ? receiving.quantities.split(', ') : [];
        const unitCosts = receiving.unit_costs ? receiving.unit_costs.split(', ') : [];
        const subtotalCosts = receiving.subtotal_costs ? receiving.subtotal_costs.split(', ') : [];

        const tbody = document.getElementById('view_invoice_items');
        tbody.innerHTML = '';

        for (let i = 0; i < productNames.length; i++) {
            const row = document.createElement('tr');
            row.className = i === productNames.length - 1 ? 'item last' : 'item';
            row.innerHTML = `
                <td>${productNames[i] || '-'}</td>
                <td>${quantities[i] || '-'}</td>
                <td>${Number(unitCosts[i]).toFixed(2) || '-'}</td>
                <td>${Number(subtotalCosts[i]).toFixed(2) || '-'}</td>
            `;
            tbody.appendChild(row);
        }

        document.getElementById('view_invoice_total').textContent = 'Total: ₱' + Number(receiving.total_cost).toFixed(2);

        var modal = new bootstrap.Modal(document.getElementById("invoiceModal"));
        modal.show();
    }

    function updateStatus(receivingId, newStatus) {
        const statusContainer = document.getElementById(`status-container-${receivingId}`);
        const statusText = document.getElementById(`status-text-${receivingId}`);
        const oldStatus = statusText.textContent.trim().toLowerCase();
        const dropdown = statusContainer.querySelector('.status-dropdown');

        if (oldStatus === "received") {
            alert("Cannot edit status once the order is marked as received.");
            dropdown.value = "";
            return;
        }

        if (newStatus === "received") {
            var confirmModal = new bootstrap.Modal(document.getElementById("confirmReceivedModal"));
            confirmModal.show();

            const confirmBtn = document.getElementById("confirmReceivedBtn");
            const cancelBtn = document.getElementById("cancelReceivedBtn");
            confirmBtn.replaceWith(confirmBtn.cloneNode(true));
            cancelBtn.replaceWith(cancelBtn.cloneNode(true));

            const newConfirmBtn = document.getElementById("confirmReceivedBtn");
            const newCancelBtn = document.getElementById("cancelReceivedBtn");

            newConfirmBtn.onclick = function() {
                sendStatusUpdate(receivingId, newStatus, statusContainer, statusText, dropdown);
                confirmModal.hide();
            };

            newCancelBtn.onclick = function() {
                dropdown.value = "";
                confirmModal.hide();
            };
        } else {
            sendStatusUpdate(receivingId, newStatus, statusContainer, statusText, dropdown);
        }
    }

    function sendStatusUpdate(receivingId, newStatus, statusContainer, statusText, dropdown) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../../handlers/update_status_handler.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        console.log(`Status updated to ${newStatus} for receiving ID ${receivingId}`);
                        statusText.classList.add("fade");
                        setTimeout(() => {
                            if (newStatus === "received") {
                                statusContainer.innerHTML =
                                    `<span class="status-box-received" id="status-text-${receivingId}">Received</span>`;
                                statusContainer.classList.add("received");
                            } else {
                                statusText.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                                statusText.className = `status-text status-${newStatus}`;
                                statusContainer.classList.remove("received");
                            }
                            statusText.classList.remove("fade");
                        }, 300);
                    } else {
                        console.error("Server response: " + xhr.responseText);
                        alert("Failed to update status: " + response.message);
                        dropdown.value = "";
                    }
                } else {
                    console.error("AJAX error: Status " + xhr.status);
                    alert("AJAX request failed with status: " + xhr.status);
                    dropdown.value = "";
                }
            }
        };
        xhr.send(`receiving_id=${receivingId}&status=${newStatus}`);
    }

    function confirmDelete(receivingId) {
        if (confirm("Are you sure you want to delete this receiving record?")) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "../../handlers/deletereceiving_handler.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            console.log(`Receiving ID ${receivingId} deleted successfully`);
                            const card = document.getElementById(`card-${receivingId}`);
                            if (card) {
                                card.remove();
                                totalRecords--;
                                updatePagination();
                            }
                        } else {
                            console.error("Server response: " + xhr.responseText);
                            alert("Failed to delete record: " + response.message);
                        }
                    } else {
                        console.error("AJAX error: Status " + xhr.status);
                        alert("AJAX request failed with status: " + xhr.status);
                    }
                }
            };
            xhr.send(`id=${receivingId}`);
        }
    }

    function fetchPage(page) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", `../../resource/views/receiving.php?page=${page}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(xhr.responseText, 'text/html');
                const newContainer = doc.getElementById('receiving-container');
                const newPagination = doc.getElementById('pagination');

                document.getElementById('receiving-container').innerHTML = newContainer.innerHTML;
                document.getElementById('pagination').innerHTML = newPagination.innerHTML;

                currentPage = page;
                updatePaginationLinks();
            }
        };
        xhr.send();
    }

    function updatePagination() {
        const container = document.getElementById('receiving-container');
        const cards = container.getElementsByClassName('receiving-card').length;
        const pagination = document.getElementById('pagination');
        const totalPages = Math.ceil(totalRecords / recordsPerPage);

        if (cards === 0) {
            if (totalRecords > 0) {
                // Fetch the next page if available, otherwise previous
                const newPage = currentPage < totalPages ? currentPage + 1 : (currentPage > 1 ? currentPage - 1 : 1);
                fetchPage(newPage);
            } else {
                container.innerHTML = '<div class="no-records">No receiving records found.</div>';
                pagination.innerHTML = '';
            }
        } else {
            let paginationHTML = '';

            if (currentPage > 1) {
                paginationHTML += `<a href="#" onclick="fetchPage(${currentPage - 1}); return false;">« Previous</a>`;
            } else {
                paginationHTML += `<span class="disabled">« Previous</span>`;
            }

            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHTML += `<span class="current">${i}</span>`;
                } else {
                    paginationHTML += `<a href="#" onclick="fetchPage(${i}); return false;">${i}</a>`;
                }
            }

            if (currentPage < totalPages) {
                paginationHTML += `<a href="#" onclick="fetchPage(${currentPage + 1}); return false;">Next »</a>`;
            } else {
                paginationHTML += `<span class="disabled">Next »</span>`;
            }

            pagination.innerHTML = paginationHTML;
        }
    }

    function updatePaginationLinks() {
        const paginationLinks = document.querySelectorAll('#pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.textContent) || (this.textContent.includes('Previous') ?
                    currentPage - 1 : currentPage + 1);
                fetchPage(page);
            });
        });
    }

    document.getElementById('createOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../../handlers/create_order_handler.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        console.log("Order created successfully");
                        totalRecords++;
                        fetchPage(1); // Fetch the first page to show the new order
                        toggleAddReceivingForm(); // Close the form
                    } else {
                        console.error("Server response: " + xhr.responseText);
                        alert("Failed to create order: " + response.message);
                    }
                } else {
                    console.error("AJAX error: Status " + xhr.status);
                    alert("AJAX request failed with status: " + xhr.status);
                }
            }
        };
        xhr.send(formData);
    });

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);

    // Initialize pagination links
    updatePaginationLinks();
</script>