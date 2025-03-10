<?php
include '../../database/database.php';

// Query to fetch sales data with joined SalesLine and Product tables
$query = "SELECT 
    s.sales_id,
    s.sale_date,
    p.product_id,
    p.product_name,
    sl.quantity,
    sl.unit_price,
    sl.subtotal_price,
    s.total_amount,
    s.createdate,
    s.updatedate
FROM Sales s
JOIN SalesLine sl ON s.sales_id = sl.sales_id
JOIN Product p ON sl.product_id = p.product_id";
$result = $conn->query($query);
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

    .search-container-sales {
        margin: 20px 0;
        display: flex;
        gap: 10px;
    }

    .search-container-sales input {
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

    /* Table Styling (Copied from products.php) */
    .sales-table {
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
    }

    .create-btn,
    .edit-btn,
    .delete-btn {
        background: #6b8e5e;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.5s ease-in-out;
        margin-left: 10px;
    }

    .create-btn:hover,
    .edit-btn:hover,
    .delete-btn:hover {
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
        color:rgb(41, 40, 40)!important;
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

        .search-container-sales {
            flex-direction: column;
        }

        .table-controls {
            justify-content: center;
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
        <input type="text" id="searchCustomerID" placeholder="Customer ID">
        <input type="text" id="searchProductID" placeholder="Product ID">
        <input type="text" id="searchTotalAmount" placeholder="Total Amount">
        <input type="text" id="searchQuantity" placeholder="Quantity">
        <button class="search-btn" onclick="filterSales()">SEARCH</button>
        <button class="clear-btn" onclick="clearFilters()">CLEAR</button>
    </div>

    <div class="sales-table">
        <div class="table-controls">
            <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addSalesModal">
                CREATE NEW <i class="fa-solid fa-add"></i>
            </button>
            <!-- Removed Edit/Delete buttons here as they are now per-row -->
        </div>
        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllCheckbox"></th>
                        <th>Sales ID</th>
                        <th>Sale Date</th>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price (₱)</th>
                        <th>Subtotal Price (₱)</th>
                        <th>Total Amount (₱)</th>
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
                                <td><?= $row['product_id'] ?? '-' ?></td>
                                <td><?= htmlspecialchars($row['product_name'] ?? '-') ?></td>
                                <td><?= $row['quantity'] ?? '-' ?></td>
                                <td><?= number_format($row['unit_price'], 2) ?></td>
                                <td><?= number_format($row['subtotal_price'], 2) ?></td>
                                <td><?= number_format($row['total_amount'], 2) ?></td>
                                <td><?= $row['createdate'] ?? '-' ?></td>
                                <td><?= $row['updatedate'] ?? '-' ?></td>
                                <td>
                                    <button class="btn btn-sm text-warning" onclick='loadEditModal(<?= json_encode($row) ?>)'
                                        data-bs-toggle="modal" data-bs-target="#editSalesModal">
                                        <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-danger" onclick="confirmDelete(<?= $row['sales_id'] ?>)">
                                        <i class="fa fa-trash" style="color: rgb(255, 0, 25);"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 20px; color: #666;">
                                No sales records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD SALES MODAL -->
<div class="modal fade" id="addSalesModal" tabindex="-1" aria-labelledby="addSalesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSalesModalLabel">Add Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/addsale_handler.php" method="POST">
                <div class="modal-body">
                    <label class="my-2">Customer ID:</label>
                    <input type="text" name="customer_id" class="form-control" required>
                    <label class="my-2">Sale Date:</label>
                    <input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    <label class="my-2">Product ID:</label>
                    <input type="text" name="product_id" class="form-control" required>
                    <label class="my-2">Quantity:</label>
                    <input type="number" name="quantity" class="form-control" required min="1">
                    <label class="my-2">Unit Price:</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" required min="0">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Sale</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT SALES MODAL -->
<div class="modal fade" id="editSalesModal" tabindex="-1" aria-labelledby="editSalesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSalesModalLabel">Edit Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/editsale_handler.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="sales_id">
                    <label class="my-2">Sale Date:</label>
                    <input type="date" name="sale_date" class="form-control" required>
                    <label class="my-2">Product ID:</label>
                    <input type="text" name="product_id" class="form-control" required>
                    <label class="my-2">Quantity:</label>
                    <input type="number" name="quantity" class="form-control" required min="1">
                    <label class="my-2">Unit Price:</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" required min="0">
                    <label class="my-2">Subtotal Price:</label>
                    <input type="number" step="0.01" name="subtotal_price" class="form-control" required min="0">
                    <label class="my-2">Total Amount:</label>
                    <input type="number" step="0.01" name="total_amount" class="form-control" required min="0">
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

        // Toggle select all checkboxes
        selectAllCheckbox.addEventListener('change', () => {
            salesCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Placeholder for filterSales and clearFilters
        window.filterSales = function() {
            const salesID = document.getElementById('searchSalesID').value;
            const customerID = document.getElementById('searchCustomerID').value;
            const productID = document.getElementById('searchProductID').value;
            const totalAmount = document.getElementById('searchTotalAmount').value;
            const quantity = document.getElementById('searchQuantity').value;

            // Implement AJAX or server-side filtering logic here
            console.log('Filter Sales:', {
                salesID,
                customerID,
                productID,
                totalAmount,
                quantity
            });
        };

        window.clearFilters = function() {
            document.getElementById('searchSalesID').value = '';
            document.getElementById('searchCustomerID').value = '';
            document.getElementById('searchProductID').value = '';
            document.getElementById('searchTotalAmount').value = '';
            document.getElementById('searchQuantity').value = '';
            filterSales(); // Refresh the table
        };

        // Delete confirmation
        window.confirmDelete = function(salesId) {
            if (confirm("Are you sure you want to delete this sale?")) {
                window.location.href = "../../handlers/deletesale_handler.php?id=" + salesId;
            }
        };

        // Load edit modal with sales data
        window.loadEditModal = function(sale) {
            console.log(sale);
            document.querySelector("#editSalesModal input[name='sales_id']").value = sale.sales_id;
            document.querySelector("#editSalesModal input[name='sale_date']").value = sale.sale_date || '';
            document.querySelector("#editSalesModal input[name='product_id']").value = sale.product_id || '';
            document.querySelector("#editSalesModal input[name='quantity']").value = sale.quantity || '';
            document.querySelector("#editSalesModal input[name='unit_price']").value = sale.unit_price || '';
            document.querySelector("#editSalesModal input[name='subtotal_price']").value = sale
                .subtotal_price || '';
            document.querySelector("#editSalesModal input[name='total_amount']").value = sale.total_amount ||
                '';
        };
    });

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>