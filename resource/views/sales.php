<?php
include '../../database/database.php';

// Query to fetch sales data with joined tables, grouping by sales_id for display
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
GROUP BY s.sales_id";
$result = $conn->query($query);

// Query to fetch available products and their stock quantities
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
    /* Prevent horizontal scrolling */
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

    /* Table Styling */
    .sales-table {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
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

    /* Modal Styling */
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

    /* Responsive Fix */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 200px;
            width: calc(100% - 200px);
        }

        .search-container-sales {
            flex-direction: column;
        }
    }

    @media (max-width: 500px) {
        .main-content {
            margin-left: 0;
            width: 100%;
        }
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
        <button class="search-btn" onclick="filterSales()">SEARCH</button>
        <button class="clear-btn" onclick="clearFilters()">CLEAR</button>
    </div>
    <div class="header-sales">
        <button class="btn-add active" onclick="toggleAddForm()" style="font-weight: bold;">
            <i class="fa fa-add"></i> Add Sale
        </button>
        <button class="btn-delete active" onclick="removeSelected()" style="font-weight: bold;">
            <i class="fa fa-trash"></i> Remove
        </button>
    </div>

    <div class="add-form" id="addSalesForm" style="display: none;">
        <h3 style="color: #34502b;">Add New Sale</h3>
        <form action="../../handlers/addsales_handler.php" method="POST" id="addSaleForm">
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
                        <th>Update Date</th>
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
                                <td><?= $row['updatedate'] ?? '-' ?></td>
                                <td>
                                    <button class="btn btn-sm" style="color: #2a3f23 !important;"
                                        onclick='loadViewModal(<?= json_encode($row) ?>)' data-bs-toggle="modal"
                                        data-bs-target="#viewSalesModal">
                                        <i class="fa fa-eye" style="color: #2a3f23;"></i> View
                                    </button>
                                    <!-- Uncomment if you want to keep the delete button -->
                                    <!-- <button class="btn btn-sm text-danger" onclick="confirmDelete(<?= $row['sales_id'] ?>)">
                                        <i class="fa fa-trash" style="color: rgb(255, 0, 25);"></i> Delete
                                    </button> -->
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
                <label>Update Date:</label>
                <p id="view_updatedate"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
        style="width: auto !important; padding-right: 2.5rem !important;">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php elseif (isset($_SESSION['error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
        style="width: auto !important; padding-right: 2.5rem !important;">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const salesCheckboxes = document.querySelectorAll('.sales-checkbox');
        const products = <?php echo json_encode($products); ?>;

        // Toggle select all checkboxes
        selectAllCheckbox.addEventListener('change', () => {
            salesCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

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

        // Add another product entry in the add form
        window.addProductEntry = function() {
            const container = document.getElementById('product-entries');
            const entry = document.createElement('div');
            entry.className = 'product-entry';
            entry.innerHTML = `
                <select class="form-control" name="product_id[]" required onchange="updateQuantityLimit(this)">
                    <option value="">Select Product</option>
                    ${products.map(product => 
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

        // Clear the form to its initial state
        window.clearForm = function() {
            const form = document.getElementById('addSaleForm');
            const container = document.getElementById('product-entries');

            // Reset basic form fields
            form.querySelector('input[name="customer_id"]').value = '';
            form.querySelector('input[name="sale_date"]').value = '<?= date('Y-m-d') ?>';
            form.querySelector('select[name="payment_method"]').value = 'Cash';

            // Remove all product entries except the first one
            const entries = container.getElementsByClassName('product-entry');
            while (entries.length > 1) {
                entries[1].remove(); // Remove all but the first entry
            }

            // Reset the first product entry
            const firstEntry = entries[0];
            firstEntry.querySelector('select[name="product_id[]"]').value = '';
            firstEntry.querySelector('input[name="quantity[]"]').value = '';
            firstEntry.querySelector('input[name="quantity[]"]').max = '';
            firstEntry.querySelector('input[name="unit_price[]"]').value = '';

            // Update remove buttons visibility
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

        // Update quantity limit based on selected product
        window.updateQuantityLimit = function(selectElement) {
            const quantityInput = selectElement.nextElementSibling;
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const maxQuantity = selectedOption.getAttribute('data-quantity') || 0;
            quantityInput.max = maxQuantity;
            if (quantityInput.value > maxQuantity) {
                quantityInput.value = maxQuantity;
            }
        }

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
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'ids=' + encodeURIComponent(salesIds.join(','))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            checkedBoxes.forEach(cb => {
                                cb.closest('tr').remove();
                            });
                            selectAllCheckbox.checked = false;
                            alert('Selected sales deleted successfully!');
                        } else {
                            alert('Error deleting sales: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting sales.');
                    });
            }
        }

        // Filter sales
        window.filterSales = function() {
            const salesID = document.getElementById('searchSalesID').value.toLowerCase();
            const customerName = document.getElementById('searchCustomerName').value.toLowerCase();
            const productID = document.getElementById('searchProductID').value.toLowerCase();
            const totalAmount = document.getElementById('searchTotalAmount').value.toLowerCase();
            const paymentMethod = document.getElementById('searchPaymentMethod').value.toLowerCase();

            const rows = document.querySelectorAll('#sales-table-body tr');

            rows.forEach(row => {
                const salesIdText = row.cells[1].textContent.toLowerCase();
                const customerNameText = row.cells[3].textContent.toLowerCase();
                const productIdText = row.cells[4].textContent.toLowerCase();
                const totalAmountText = row.cells[5].textContent.toLowerCase().replace('₱', '').replace(
                    ',', '');
                const paymentMethodText = row.cells[6].textContent.toLowerCase();

                const matchesSalesID = salesID === '' || salesIdText.includes(salesID);
                const matchesCustomerName = customerName === '' || customerNameText.includes(
                    customerName);
                const matchesProductID = productID === '' || productIdText.includes(productID);
                const matchesTotalAmount = totalAmount === '' || totalAmountText.includes(totalAmount);
                const matchesPaymentMethod = paymentMethod === '' || paymentMethodText ===
                    paymentMethod;

                if (matchesSalesID && matchesCustomerName && matchesProductID && matchesTotalAmount &&
                    matchesPaymentMethod) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        };

        // Clear filters
        window.clearFilters = function() {
            document.getElementById('searchSalesID').value = '';
            document.getElementById('searchCustomerName').value = '';
            document.getElementById('searchProductID').value = '';
            document.getElementById('searchTotalAmount').value = '';
            document.getElementById('searchPaymentMethod').value = '';
            filterSales();
        };

        // Delete single sale confirmation
        window.confirmDelete = function(salesId) {
            if (confirm("Are you sure you want to delete this sale?")) {
                window.location.href = "../../handlers/deletesale_handler.php?id=" + salesId;
            }
        };

        // Load view modal with sale details
        window.loadViewModal = function(sale) {
            document.getElementById('view_sales_id').textContent = sale.sales_id;
            document.getElementById('view_sale_date').textContent = sale.sale_date || '-';
            document.getElementById('view_customer_name').textContent = sale.customer_name || 'Anonymous';
            document.getElementById('view_payment_method').textContent = sale.payment_method ? sale
                .payment_method.charAt(0).toUpperCase() + sale.payment_method.slice(1) : '-';
            document.getElementById('view_total_amount').textContent = Number(sale.total_amount).toFixed(2);
            document.getElementById('view_createdate').textContent = sale.createdate || '-';
            document.getElementById('view_updatedate').textContent = sale.updatedate || '-';

            const productIds = sale.product_ids ? sale.product_ids.split(', ') : [];
            const productNames = sale.product_names ? sale.product_names.split(', ') : [];
            const quantities = sale.quantities ? sale.quantities.split(', ') : [];
            const unitPrices = sale.unit_prices ? sale.unit_prices.split(', ') : [];
            const subtotalPrices = sale.subtotal_prices ? sale.subtotal_prices.split(', ') : [];

            const tbody = document.getElementById('view_products');
            tbody.innerHTML = ''; // Clear previous content

            for (let i = 0; i < productIds.length; i++) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${productIds[i] || '-'}</td>
                    <td>${productNames[i] || '-'}</td>
                    <td>${quantities[i] || '-'}</td>
                    <td>${Number(unitPrices[i]).toFixed(2) || '-'}</td>
                    <td>${Number(subtotalPrices[i]).toFixed(2) || '-'}</td>
                `;
                tbody.appendChild(row);
            }
        };

        // Initialize quantity limits and remove buttons visibility for existing entries
        document.querySelectorAll('select[name="product_id[]"]').forEach(select => {
            updateQuantityLimit(select);
        });
        updateRemoveButtonsVisibility();
    });

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>