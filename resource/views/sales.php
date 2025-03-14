<?php
include '../../database/database.php';

// Initialize query components
$whereConditions = [];
$orderClause = "ORDER BY s.sales_id DESC"; // Default ordering

// Handle search filters
if (isset($_GET['search']) && $_GET['search'] === '1') {
    if (isset($_GET['sales_id']) && !empty($_GET['sales_id'])) {
        $whereConditions[] = "s.sales_id = '" . $conn->real_escape_string($_GET['sales_id']) . "'";
    }
    if (isset($_GET['customer_name']) && !empty($_GET['customer_name'])) {
        $whereConditions[] = "c.name LIKE '%" . $conn->real_escape_string($_GET['customer_name']) . "%'";
    }
    if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
        $whereConditions[] = "p.product_id = '" . $conn->real_escape_string($_GET['product_id']) . "'";
    }
    if (isset($_GET['total_amount']) && !empty($_GET['total_amount'])) {
        $whereConditions[] = "s.total_amount = '" . $conn->real_escape_string($_GET['total_amount']) . "'";
    }
    if (isset($_GET['payment_method']) && !empty($_GET['payment_method'])) {
        $whereConditions[] = "s.payment_method = '" . $conn->real_escape_string($_GET['payment_method']) . "'";
    }
}

// Handle table control filters (e.g., payment method)
if (isset($_GET['filter_payment']) && !empty($_GET['filter_payment'])) {
    $whereConditions[] = "s.payment_method = '" . $conn->real_escape_string($_GET['filter_payment']) . "'";
}

// Handle ordering
if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
    list($column, $direction) = explode('|', $_GET['order_by']);
    $column = $conn->real_escape_string($column);
    $direction = strtoupper($conn->real_escape_string($direction));
    if (
        in_array($column, ['sales_id', 'sale_date', 'customer_name', 'total_amount', 'payment_method']) &&
        in_array($direction, ['ASC', 'DESC'])
    ) {
        $orderClause = "ORDER BY " . ($column === 'customer_name' ? 'c.name' : "s.$column") . " $direction";
    }
}

// Build WHERE clause
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Main sales query
$query = "SELECT 
    s.sales_id,
    s.sale_date,
    c.name AS customer_name,
    GROUP_CONCAT(p.product_id SEPARATOR ', ') AS product_ids,
    GROUP_CONCAT(p.product_name SEPARATOR ', ') AS product_names,
    SUM(sl.quantity) AS total_quantity,
    GROUP_CONCAT(sl.quantity SEPARATOR ', ') AS quantities,
    GROUP_CONCAT(sl.unit_price SEPARATOR ', ') AS unit_prices,
    GROUP_CONCAT(sl.subtotal_price SEPARATOR ', ') AS subtotal_prices,
    s.total_amount,
    s.payment_method,
    s.createdate,
    s.updatedate
FROM Sales s
JOIN SalesLine sl ON s.sales_id = sl.sales_id
JOIN Product p ON sl.product_id = p.product_id
LEFT JOIN Customer c ON s.customer_id = c.customer_id
$whereClause
GROUP BY s.sales_id
$orderClause";
$result = $conn->query($query);

if (!$result) {
    echo "Query failed: " . $conn->error;
    exit;
}

// Product query for add form
$product_query = "SELECT p.product_id, p.product_name, i.stock_quantity 
                 FROM Product p 
                 JOIN Inventory i ON p.product_id = i.product_id 
                 WHERE i.stock_quantity > 0";
$product_result = $conn->query($product_query);
$products = [];
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
}
?>

<style>
    html,
    body {
        overflow-x: hidden;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        padding: 20px;
        overflow: hidden;
    }

    .header-sales {
        padding: 15px;
        display: flex;
        gap: 10px;
    }

    .btn-add {
        background-color: #34502b;
        color: white;
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-add:hover {
        background-color: #2a3f23;
    }

    .add-form {
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
    }

    .add-form .form-control {
        margin-bottom: 10px;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
    }

    .btn-save {
        background-color: #34502b;
        color: white;
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        background-color: #2a3f23;
    }

    .search-container-sales {
        margin: 20px 0;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-container-sales input,
    .search-container-sales select {
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

    .sales-table {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
    }

    .table-controls {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 10px;
        gap: 10px;
        align-items: center;
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
        color: rgb(41, 40, 40) !important;
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
        justify-content: flex-end;
    }

    .modal-body label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
    }

    .modal-body p {
        margin: 5px 0;
    }

    .btn-delete {
        background-color: rgb(255, 255, 255);
        color: rgb(81, 2, 2);
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid rgb(81, 2, 2);
    }

    .btn-delete:hover {
        background-color: rgb(81, 2, 2);
        color: white;
    }

    .product-entry {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }

    .btn-add-product {
        background-color: #34502b;
        color: white;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-remove-product {
        background-color: #dc3545;
        color: white;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-remove-product:hover {
        background-color: #c82333;
    }

    .btn-clear {
        background-color: rgb(255, 255, 255);
        color: rgb(81, 2, 2);
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid rgb(81, 2, 2);
        margin-left: 10px;
    }

    .btn-clear:hover {
        background-color: rgb(81, 2, 2);
        color: white;
    }

    .custom-select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        background-color: white;
        cursor: pointer;
        width: 50px;
    }

    .select2-container {
        width: 50px !important;
    }

    .select2-selection__rendered {
        display: flex;
        align-items: center;
        padding: 0 5px;
        justify-content: center;
    }

    .select2-selection__rendered i {
        font-size: 16px;
    }

    .custom-select:focus {
        outline: none;
        border-color: #34502b;
        box-shadow: 0 0 5px rgba(52, 80, 43, 0.5);
    }

    .select2-dropdown {
        width: 200px !important;
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .select2-results__option {
        padding: 8px;
        display: flex;
        align-items: center;
    }

    .floating-alert {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        width: auto !important;
        padding-right: 2.5rem !important;
    }

    .alert-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-link.toggle-message {
        padding: 0;
        font-size: 0.9em;
        color: #fff;
        text-decoration: underline;
    }

    .btn-link.toggle-message:hover {
        color: #ddd;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 200px;
            width: calc(100% - 200px);
        }

        .search-container-sales {
            flex-direction: column;
        }

        .table-controls {
            flex-wrap: wrap;
            justify-content: center;
        }
    }

    @media (max-width: 500px) {
        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }
</style>

<div class="main-content">
    <header>
        <h1>Sales</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>

    <hr>
    <div class="search-container-sales">
        <input type="text" id="searchSalesID" placeholder="Sales ID">
        <input type="text" id="searchCustomerName" placeholder="Customer Name">
        <input type="text" id="searchProductID" placeholder="Product ID">
        <input type="text" id="searchTotalAmount" placeholder="Total Amount">
        <select id="searchPaymentMethod">
            <option value="">All Payment Methods</option>
            <option value="Cash">Cash</option>
            <option value="GCash">GCash</option>
        </select>
        <button class="search-btn" onclick="searchSales()">SEARCH</button>
        <button class="clear-btn" onclick="clearSearch()">CLEAR</button>
    </div>

    <div class="header-sales">
        <button class="btn-add active" onclick="toggleAddForm()" style="font-weight: bold;">
            <i class="fa fa-add"></i> Add Sale
        </button>
        <button class="btn-delete active" onclick="removeSelected()" style="font-weight: bold;">
            <i class="fa fa-trash"></i> Dump
        </button>
    </div>

    <div class="add-form" id="addSalesForm" style="display: none;">
        <h3 style="color: #34502b;">Add New Sale</h3>
        <form id="addSaleForm" method="POST">
            <input class="form-control" type="text" name="customer_id" placeholder="Customer ID (Optional)">
            <label for="sale_date">Date Purchased:</label>
            <input class="form-control" type="date" name="sale_date" value="<?= date('Y-m-d') ?>" required>
            <label for="product_purchase">Product/s:</label>
            <div id="product-entries">
                <div class="product-entry">
                    <select class="form-control" name="product_id[]" required onchange="updateQuantityLimit(this)">
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['product_id'] ?>" data-quantity="<?= $product['stock_quantity'] ?>">
                                <?= htmlspecialchars($product['product_name']) ?> (ID: <?= $product['product_id'] ?>, Stock:
                                <?= $product['stock_quantity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control" type="number" name="quantity[]" placeholder="Quantity" required min="1"
                        max="">
                    <input class="form-control" type="number" step="0.01" name="unit_price[]" placeholder="Unit Price"
                        required min="0">
                    <button type="button" class="btn-remove-product" onclick="removeProductEntry(this)"
                        style="display: none;"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <div style="padding: 10px; text-align: center;">
                <button type="button" class="btn-add-product" onclick="addProductEntry()"><i class="fa fa-plus"></i> Add
                    more </button>
            </div>
            <label for="payment_method">Payment Method:</label>
            <select class="form-control" name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
            </select>
            <button type="submit" class="btn-save">Save</button>
            <button type="button" class="btn-clear" onclick="clearForm()"><i class="fa fa-eraser"></i> Clear </button>
        </form>
    </div>

    <div class="sales-table">
        <div class="table-controls">
            <select id="filterPayment" class="custom-select" onchange="applyTableFilters()">
                <option value="" data-icon="fa-solid fa-filter">All Payments</option>
                <option value="Cash" data-icon="fa-solid fa-money-bill">Cash</option>
                <option value="GCash" data-icon="fa-solid fa-mobile-alt">GCash</option>
            </select>

            <select id="orderBy" class="custom-select" onchange="applyTableFilters()">
                <option value="sales_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">ID (Descending)</option>
                <option value="sales_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">ID (Ascending)</option>
                <option value="sale_date|DESC" data-icon="fa-solid fa-arrow-down-long">Date (Newest)</option>
                <option value="sale_date|ASC" data-icon="fa-solid fa-arrow-up-long">Date (Oldest)</option>
                <option value="customer_name|ASC" data-icon="fa-solid fa-arrow-up-a-z">Customer (A-Z)</option>
                <option value="customer_name|DESC" data-icon="fa-solid fa-arrow-down-z-a">Customer (Z-A)</option>
                <option value="total_amount|ASC" data-icon="fa-solid fa-arrow-up-wide-short">Amount (Low to High)
                </option>
                <option value="total_amount|DESC" data-icon="fa-solid fa-arrow-down-short-wide">Amount (High to Low)
                </option>
            </select>
        </div>

        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllCheckbox"></th>
                        <th>Sales ID</th>
                        <th>Sale Date</th>
                        <th>Customer</th>
                        <th>Total Products</th>
                        <th>Total Amount (₱)</th>
                        <th>Payment Method</th>
                        <th>Create Date</th>
                        <th class="text-center w-5">Actions</th>
                    </tr>
                </thead>
                <tbody id="sales-table-body">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><input type="checkbox" class="sales-checkbox" data-sales-id="<?= $row['sales_id'] ?>"></td>
                                <td><?= $row['sales_id'] ?></td>
                                <td><?= $row['sale_date'] ?? '-' ?></td>
                                <td><?= htmlspecialchars($row['customer_name'] ?? 'Anonymous') ?></td>
                                <td><?= $row['total_quantity'] ?? '-' ?></td>
                                <td><?= number_format($row['total_amount'], 2) ?></td>
                                <td><?= ucfirst($row['payment_method']) ?></td>
                                <td><?= $row['createdate'] ?? '-' ?></td>
                                <td>
                                    <button class="btn btn-sm" style="color: #2a3f23 !important;"
                                        onclick='loadViewModal(<?= json_encode($row) ?>)' data-bs-toggle="modal"
                                        data-bs-target="#viewSalesModal">
                                        <i class="fa fa-eye" style="color: #2a3f23;"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 20px; color: #666;">
                                No sales records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- VIEW SALES MODAL -->
<div class="modal fade" id="viewSalesModal" tabindex="-1" aria-labelledby="viewSalesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSalesModalLabel">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label>Sales ID:</label>
                <p id="view_sales_id"></p>
                <label>Sale Date:</label>
                <p id="view_sale_date"></p>
                <label>Customer Name:</label>
                <p id="view_customer_name"></p>
                <label>Payment Method:</label>
                <p id="view_payment_method"></p>
                <label>Products Purchased:</label>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Unit Price (₱)</th>
                            <th>Subtotal (₱)</th>
                        </tr>
                    </thead>
                    <tbody id="view_products"></tbody>
                </table>
                <label>Total Amount (₱):</label>
                <p id="view_total_amount"></p>
                <label>Create Date:</label>
                <p id="view_createdate"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert text-center" role="alert">
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
<?php elseif (isset($_SESSION['error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for filterPayment
        $('#filterPayment').select2({
            templateResult: formatOption,
            templateSelection: formatSelection,
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true,
            width: '50px'
        });

        // Initialize Select2 for orderBy
        $('#orderBy').select2({
            templateResult: formatOption,
            templateSelection: formatSelection,
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true,
            width: '50px'
        });

        // Preserve onchange functionality
        $('#filterPayment, #orderBy').on('change', applyTableFilters);

        // Load URL params
        const urlParams = new URLSearchParams(window.location.search);
        $('#searchSalesID').val(urlParams.get('sales_id') || '');
        $('#searchCustomerName').val(urlParams.get('customer_name') || '');
        $('#searchProductID').val(urlParams.get('product_id') || '');
        $('#searchTotalAmount').val(urlParams.get('total_amount') || '');
        $('#searchPaymentMethod').val(urlParams.get('payment_method') || '');
        $('#filterPayment').val(urlParams.get('filter_payment') || '');
        $('#orderBy').val(urlParams.get('order_by') || 'sales_id|DESC');
    });

    // Format dropdown options
    function formatOption(option) {
        if (!option.element) return option.text;
        return $('<span><i class="' + $(option.element).data('icon') + ' me-2"></i>' + option.text + '</span>');
    }

    // Format selected option (icon only)
    function formatSelection(option) {
        if (!option.element) return option.text;
        return $('<span><i class="' + $(option.element).data('icon') + '"></i></span>');
    }

    // Search function (server-side)
    function searchSales() {
        const salesID = $('#searchSalesID').val().trim();
        const customerName = $('#searchCustomerName').val().trim();
        const productID = $('#searchProductID').val().trim();
        const totalAmount = $('#searchTotalAmount').val().trim();
        const paymentMethod = $('#searchPaymentMethod').val();
        const filterPayment = $('#filterPayment').val();
        const orderBy = $('#orderBy').val();

        let url = '../../resource/layout/web-layout.php?page=sales&search=1';
        const params = [];
        if (salesID) params.push(`sales_id=${encodeURIComponent(salesID)}`);
        if (customerName) params.push(`customer_name=${encodeURIComponent(customerName)}`);
        if (productID) params.push(`product_id=${encodeURIComponent(productID)}`);
        if (totalAmount) params.push(`total_amount=${encodeURIComponent(totalAmount)}`);
        if (paymentMethod) params.push(`payment_method=${encodeURIComponent(paymentMethod)}`);
        if (filterPayment) params.push(`filter_payment=${encodeURIComponent(filterPayment)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

        if (params.length > 0) url += '&' + params.join('&');
        console.log('Search URL:', url);
        window.location.href = url;
    }

    // Apply table filters (server-side)
    function applyTableFilters() {
        const salesID = $('#searchSalesID').val().trim();
        const customerName = $('#searchCustomerName').val().trim();
        const productID = $('#searchProductID').val().trim();
        const totalAmount = $('#searchTotalAmount').val().trim();
        const paymentMethod = $('#searchPaymentMethod').val();
        const filterPayment = $('#filterPayment').val();
        const orderBy = $('#orderBy').val();

        let url = '../../resource/layout/web-layout.php?page=sales';
        const params = [];
        if (salesID || customerName || productID || totalAmount || paymentMethod) params.push('search=1');
        if (salesID) params.push(`sales_id=${encodeURIComponent(salesID)}`);
        if (customerName) params.push(`customer_name=${encodeURIComponent(customerName)}`);
        if (productID) params.push(`product_id=${encodeURIComponent(productID)}`);
        if (totalAmount) params.push(`total_amount=${encodeURIComponent(totalAmount)}`);
        if (paymentMethod) params.push(`payment_method=${encodeURIComponent(paymentMethod)}`);
        if (filterPayment) params.push(`filter_payment=${encodeURIComponent(filterPayment)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

        if (params.length > 0) url += '&' + params.join('&');
        console.log('Table Filter URL:', url);
        window.location.href = url;
    }

    // Clear search
    function clearSearch() {
        $('#searchSalesID').val('');
        $('#searchCustomerName').val('');
        $('#searchProductID').val('');
        $('#searchTotalAmount').val('');
        $('#searchPaymentMethod').val('');
        const filterPayment = $('#filterPayment').val();
        const orderBy = $('#orderBy').val();

        let url = '../../resource/layout/web-layout.php?page=sales';
        const params = [];
        if (filterPayment) params.push(`filter_payment=${encodeURIComponent(filterPayment)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

        if (params.length > 0) url += '&' + params.join('&');
        console.log('Clear Search URL:', url);
        window.location.href = url;
    }

    // Toggle add form
    window.toggleAddForm = function() {
        var form = document.getElementById("addSalesForm");
        var button = document.querySelector(".btn-add");
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            button.innerHTML = '<i class="fa fa-times"></i> Close';
            button.style.backgroundColor = "white";
            button.style.color = "#34502b";
            button.style.border = "1px solid #34502b";
        } else {
            form.style.display = "none";
            button.innerHTML = '<i class="fa fa-add"></i> Add Sale';
            button.style.backgroundColor = "#34502b";
            button.style.color = "white";
            button.style.border = "none";
        }
    }

    // Add another product entry
    window.addProductEntry = function() {
        const container = document.getElementById('product-entries');
        const entry = document.createElement('div');
        entry.className = 'product-entry';
        entry.innerHTML = `
                <select class="form-control" name="product_id[]" required onchange="updateQuantityLimit(this)">
                    <option value="">Select Product</option>
                    ${<?php echo json_encode($products); ?>.map(product => 
                        `<option value="${product.product_id}" data-quantity="${product.stock_quantity}">
                            ${product.product_name} (ID: ${product.product_id}, Stock: ${product.stock_quantity})
                        </option>`).join('')}
                </select>
                <input class="form-control" type="number" name="quantity[]" placeholder="Quantity" required min="1" max="">
                <input class="form-control" type="number" step="0.01" name="unit_price[]" placeholder="Unit Price" required min="0">
                <button type="button" class="btn-remove-product" onclick="removeProductEntry(this)"><i class="fa fa-trash"></i></button>
            `;
        container.appendChild(entry);
        updateRemoveButtonsVisibility();
    }

    // Clear the form
    window.clearForm = function() {
        const form = document.getElementById('addSaleForm');
        const container = document.getElementById('product-entries');

        form.querySelector('input[name="customer_id"]').value = '';
        form.querySelector('input[name="sale_date"]').value = '<?= date('Y-m-d') ?>';
        form.querySelector('select[name="payment_method"]').value = 'Cash';

        const entries = container.getElementsByClassName('product-entry');
        while (entries.length > 1) {
            entries[1].remove();
        }

        const firstEntry = entries[0];
        firstEntry.querySelector('select[name="product_id[]"]').value = '';
        firstEntry.querySelector('input[name="quantity[]"]').value = '';
        firstEntry.querySelector('input[name="quantity[]"]').max = '';
        firstEntry.querySelector('input[name="unit_price[]"]').value = '';

        updateRemoveButtonsVisibility();
    }

    // Remove a product entry
    window.removeProductEntry = function(button) {
        const container = document.getElementById('product-entries');
        const entries = container.getElementsByClassName('product-entry');
        if (entries.length > 1) {
            button.parentElement.remove();
            updateRemoveButtonsVisibility();
        } else {
            alert("You must have at least one product entry.");
        }
    }

    // Update visibility of remove buttons
    function updateRemoveButtonsVisibility() {
        const container = document.getElementById('product-entries');
        const entries = container.getElementsByClassName('product-entry');
        const removeButtons = container.getElementsByClassName('btn-remove-product');
        for (let i = 0; i < removeButtons.length; i++) {
            removeButtons[i].style.display = entries.length > 1 ? 'inline-block' : 'none';
        }
    }

    // Update quantity limit
    window.updateQuantityLimit = function(selectElement) {
        const quantityInput = selectElement.nextElementSibling;
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const maxQuantity = selectedOption.getAttribute('data-quantity') || 0;
        quantityInput.max = maxQuantity;
        if (quantityInput.value > maxQuantity) {
            quantityInput.value = maxQuantity;
        }
    }

    // Handle form submission with AJAX
    document.getElementById('addSaleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../handlers/addsales_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    let deductionMessage = `${data.message} Inventory deducted: `;
                    data.deductions.forEach(d => {
                        deductionMessage +=
                            `Product ID ${d.product_id}: ${d.quantity_deducted} unit(s), `;
                    });
                    deductionMessage = deductionMessage.slice(0, -2);

                    const fullMessage = deductionMessage;
                    const shortMessage = fullMessage.length > 100 ? fullMessage.substring(0, 100) + '...' :
                        fullMessage;
                    const notification = document.createElement('div');
                    notification.className =
                        'alert alert-success alert-dismissible fade show floating-alert text-center';
                    notification.role = 'alert';
                    notification.innerHTML = `
                        <div class="alert-content">
                            <span class="alert-short">${shortMessage}</span>
                            <span class="alert-full d-none">${fullMessage}</span>
                            ${fullMessage.length > 100 ? '<button type="button" class="btn btn-link btn-sm toggle-message">Show More</button>' : ''}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(notification);

                    const toggleButton = notification.querySelector('.toggle-message');
                    if (toggleButton) {
                        toggleButton.addEventListener('click', () => {
                            const short = notification.querySelector('.alert-short');
                            const full = notification.querySelector('.alert-full');
                            if (short.classList.contains('d-none')) {
                                short.classList.remove('d-none');
                                full.classList.add('d-none');
                                toggleButton.textContent = 'Show More';
                            } else {
                                short.classList.add('d-none');
                                full.classList.remove('d-none');
                                toggleButton.textContent = 'Show Less';
                            }
                        });
                    }

                    setTimeout(() => {
                        notification.style.opacity = '0';
                        setTimeout(() => notification.remove(), 500);
                    }, 20000);

                    clearForm();
                    toggleAddForm();
                    location.reload();
                } else {
                    const errorNotification = document.createElement('div');
                    errorNotification.className =
                        'alert alert-danger alert-dismissible fade show floating-alert';
                    errorNotification.role = 'alert';
                    errorNotification.innerHTML = `
                        ${data.error || 'Unknown error occurred'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(errorNotification);

                    setTimeout(() => {
                        errorNotification.style.opacity = '0';
                        setTimeout(() => errorNotification.remove(), 500);
                    }, 20000);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                const errorNotification = document.createElement('div');
                errorNotification.className = 'alert alert-danger alert-dismissible fade show floating-alert';
                errorNotification.role = 'alert';
                errorNotification.innerHTML = `
                    An error occurred while saving the sale: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(errorNotification);

                setTimeout(() => {
                    errorNotification.style.opacity = '0';
                    setTimeout(() => errorNotification.remove(), 500);
                }, 20000);
            });
    });

    // Remove selected sales
    window.removeSelected = function() {
        const checkedBoxes = document.querySelectorAll('.sales-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert("Please select at least one sale to remove.");
            return;
        }
        if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected sale(s)?`)) {
            const salesIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-sales-id'));
            fetch('../../handlers/deletesaleselection_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ids=' + encodeURIComponent(salesIds.join(','))
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        checkedBoxes.forEach(cb => cb.closest('tr').remove());
                        document.getElementById('selectAllCheckbox').checked = false;
                        alert('Selected sales deleted successfully!');
                    } else {
                        alert('Error deleting sales: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('An error occurred while deleting sales: ' + error.message);
                });
        }
    }

    // Load view modal
    window.loadViewModal = function(sale) {
        console.log('Sale Data:', sale);
        document.getElementById('view_sales_id').textContent = sale.sales_id || '-';
        document.getElementById('view_sale_date').textContent = sale.sale_date || '-';
        document.getElementById('view_customer_name').textContent = sale.customer_name || 'Anonymous';
        document.getElementById('view_payment_method').textContent = sale.payment_method ?
            sale.payment_method.charAt(0).toUpperCase() + sale.payment_method.slice(1) : '-';
        document.getElementById('view_total_amount').textContent = sale.total_amount !== null && sale.total_amount !==
            undefined ?
            Number(sale.total_amount).toFixed(2) : '-';
        document.getElementById('view_createdate').textContent = sale.createdate || '-';

        const productIds = sale.product_ids ? sale.product_ids.split(', ') : [];
        const productNames = sale.product_names ? sale.product_names.split(', ') : [];
        const quantities = sale.quantities ? sale.quantities.split(', ') : [];
        const unitPrices = sale.unit_prices ? sale.unit_prices.split(', ') : [];
        const subtotalPrices = sale.subtotal_prices ? sale.subtotal_prices.split(', ') : [];

        const tbody = document.getElementById('view_products');
        tbody.innerHTML = '';

        const maxLength = Math.max(productIds.length, productNames.length, quantities.length, unitPrices.length,
            subtotalPrices.length);
        for (let i = 0; i < maxLength; i++) {
            const row = document.createElement('tr');
            row.innerHTML = `
                    <td>${productIds[i] || '-'}</td>
                    <td>${productNames[i] || '-'}</td>
                    <td>${quantities[i] || '-'}</td>
                    <td>${unitPrices[i] !== undefined && unitPrices[i] !== null ? Number(unitPrices[i]).toFixed(2) : '-'}</td>
                    <td>${subtotalPrices[i] !== undefined && subtotalPrices[i] !== null ? Number(subtotalPrices[i]).toFixed(2) : '-'}</td>
                `;
            tbody.appendChild(row);
        }
    };

    // Select all checkboxes
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        document.querySelectorAll('.sales-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Initialize
    document.querySelectorAll('select[name="product_id[]"]').forEach(select => updateQuantityLimit(select));
    updateRemoveButtonsVisibility();

    // Notification toggle
    document.querySelectorAll('.toggle-message').forEach(button => {
        button.addEventListener('click', () => {
            const alert = button.closest('.alert');
            const short = alert.querySelector('.alert-short');
            const full = alert.querySelector('.alert-full');
            if (short.classList.contains('d-none')) {
                short.classList.remove('d-none');
                full.classList.add('d-none');
                button.textContent = 'Show More';
            } else {
                short.classList.add('d-none');
                full.classList.remove('d-none');
                button.textContent = 'Show Less';
            }
        });
    });
</script>