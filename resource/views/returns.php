<?php
include '../../database/database.php';


// Fetch Customer Returns Data
$query_customer = "SELECT * FROM customerreturn";
$result_customer = mysqli_query($conn, $query_customer);

// Debugging - Check if $query_customer is empty
if (!$query_customer) {
    die("Error: Customer SQL query is empty!");
}

if (!$result_customer) {
    die("Database query failed: " . mysqli_error($conn));
}

// Fetch Supplier Returns Data
$query_supplier = "SELECT * FROM supplierreturn";
$result_supplier = mysqli_query($conn, $query_supplier);

if (!$result_supplier) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<style>

    /* Styling for Customer and Supplier Tabs */
    .tabs {
        display: flex;
        gap: 20px;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .tabs span {
        cursor: pointer;
        color: #000;
        text-decoration: none;
    }

    .tabs span.active {
        border-bottom: 2px solid #000;
    }

    /* Search and Table Controls Styling */
    .search-container,
    .table-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .search-container input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 290px; /* Increased from 150px */
}

    .search-btn,
    .clear-btn,
    .create-btn,
    .edit-btn,
    .delete-btn {
        padding: 8px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        color: white;
    }

    .search-btn {
        background: #6b8e5e;
    }

    .clear-btn {
        background: #a3a3a3;
    }

    .create-btn {
        background-color: rgb(255, 255, 255);
        color: rgb(81, 2, 2);
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid rgb(81, 2, 2);
    }

    .create-btn:hover {
        background-color: rgb(81, 2, 2);
        color: white;
    }

    .edit-btn {
        background: #ffd700;
        color: black;
    }

    .delete-btn {
        background: #34502b;
    }

    .delete-btn:hover {
        background-color:  #34502b;
        color: white;
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
    }



    th {
        background-color: #e6c200;
        color: black;
        text-align: center;
        padding: 10px;
    }

    th,
    td {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    tr:hover {
        background: #f1f1f1;
    }

    /* Modal Styling */
    .modal {
    display: none; /* Ensure it remains hidden by default */
    position: fixed;
    z-index: 10;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}


    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 5px;
        width: 35%; /* Reduced width from 40% */
        max-width: 450px; /* Added max width */
        max-height: 80vh; /* Restricts height */
        overflow-y: auto; /* Enables scrolling if content is too long */
        position: relative;
    }

    .modal-header {
        background: #007200;
        color: white;
        padding: 10px;
        font-size: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header .close {
        font-size: 24px;
        cursor: pointer;
    }

    .modal-body {
        padding: 15px;
    }

    .modal-body label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
    }

    .modal-body input,
    .modal-body select {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

        .modal-footer .submit-btn,
    .modal-footer .cancel-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .submit-btn {
        background-color: #007200;
        color: white;
    }

    .cancel-btn {
        background-color: #ccc;
        color: black;
    }

    .submit-btn:hover {
        background-color: #005a00;
    }

    .cancel-btn:hover {
        background-color: #999;
}
</style>

<script>
    function showTable(type) {
    console.log("Switching to:", type);

    let customerTable = document.getElementById('customer_returns-table-body');
    let supplierTable = document.getElementById('supplier-table');
    let customerSearch = document.getElementById('customer-search');
    let supplierSearch = document.getElementById('supplier-search');

    if (type === 'customer') {
        customerTable.style.display = 'table';
        supplierTable.style.display = 'none';
        customerSearch.style.display = 'block';
        supplierSearch.style.display = 'none';
    } else {
        customerTable.style.display = 'none';
        supplierTable.style.display = 'table';
        customerSearch.style.display = 'none';
        supplierSearch.style.display = 'block';
    }

    // Ensure tabs are highlighted correctly
    document.querySelectorAll('.tabs span').forEach(tab => tab.classList.remove('active'));
    document.querySelector(`.tabs span[data-type="${type}"]`).classList.add('active');

    // Update form actions
    document.getElementById('create-form').action = (type === 'customer') ? 'handlers/addcustomerreturn_handler.php' :
        'handlers/addsupplierreturn_handler.php';
    document.getElementById('edit-form').action = (type === 'customer') ? 'handlers/editcustomerreturn_handler.php' :
        'handlers/editsupplierreturn_handler.php';
    document.getElementById('delete-form').action = (type === 'customer') ?
        'handlers/deletecustomerreturn_handler.php' : 'handlers/deletesupplierreturn_handler.php';
}

    function openModal(modalId) {
        document.getElementById(modalId).style.display = "flex";
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

    function openCreateModal() {
        const activeTab = document.querySelector('.tabs span.active').getAttribute('data-type');
        const modalTitle = document.getElementById('modal-title');
        const modalFields = document.getElementById('modal-fields');
        const createForm = document.getElementById('create-form');

        if (activeTab === 'customer') {
            modalTitle.textContent = 'Add Customer Return';
            modalFields.innerHTML = `
                <label for="customer_id">Customer ID:</label>
                <input type="number" name="customer_id" required>

                <label for="return_reason">Return Reason:</label>
                <input type="text" name="return_reason" required>

                <label for="return_date">Return Date:</label>
                <input type="date" name="return_date" required>

                <label for="refund_status">Refund Status:</label>
                <select name="refund_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Refunded</option>
                    <option value="Rejected">Replaced</option>
                </select>

                <label for="total_amount">Total Amount:</label>
                <input type="number" name="total_amount" step="0.01" required>

                <label for="createdbyid">Created By:</label>
                <input type="text" name="createdbyid" required>

                <label for="createdate">Created Date:</label>
                <input type="datetime-local" name="createdate" required>
            `;
            createForm.action = '../../handlers/addcustomerreturn_handler.php';
        } else {
            modalTitle.textContent = 'Add Supplier Return';
            modalFields.innerHTML = `
                <label for="supplier_id">Supplier ID:</label>
                <input type="number" name="supplier_id" required>

                <label for="return_reason">Return Reason:</label>
                <input type="text" name="return_reason" required>

                <label for="return_date">Return Date:</label>
                <input type="date" name="return_date" required>

                <label for="refund_status">Refund Status:</label>
                <select name="refund_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Refunded</option>
                    <option value="Rejected">Replaced</option>
                </select>

                <label for="createdbyid">Created By:</label>
                <input type="text" name="createdbyid" required>

                <label for="createdate">Created Date:</label>
                <input type="datetime-local" name="createdate" required>
            `;
            createForm.action = '../../handlers/addsupplierreturn_handler.php';
        }

        openModal('createModal'); // Open the modal
    }


    function openEditModal(type, id, relatedId, reason, date, status, amount, updateId, updateDate) {
        const modalTitle = document.getElementById('editModalTitle');
        const modalFields = document.getElementById('edit-modal-fields');
        const editForm = document.getElementById('edit-form');

        if (type === 'customer') {
            modalTitle.textContent = 'Edit Customer Return';
            modalFields.innerHTML = `
                <input type="hidden" id="edit_customerreturn_id" name="customer_return_id" value="${id}">
                <label for="edit_customer_id">Customer ID:</label>
                <input type="number" id="edit_customer_id" name="customer_id" value="${relatedId}" required>
                <label for="edit_return_reason">Return Reason:</label>
                <input type="text" id="edit_return_reason" name="return_reason" value="${reason}" required>
                <label for="edit_return_date">Return Date:</label>
                <input type="date" id="edit_return_date" name="return_date" value="${date}" required>
                <label for="edit_refund_status">Refund Status:</label>
                <select id="edit_refund_status" name="refund_status" required>
                    <option value="Pending" ${status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="Refunded" ${status === 'Refunded' ? 'selected' : ''}>Refunded</option>
                    <option value="Replaced" ${status === 'Replaced' ? 'selected' : ''}>Replaced</option>
                </select>
                <label for="edit_total_amount">Total Amount:</label>
                <input type="number" id="edit_total_amount" name="total_amount" value="${amount}" step="0.01" required>
                <label for="edit_updatedbyid">Updated By:</label>
                <input type="text" id="edit_updatedbyid" name="updatedbyid" value="${updateId}" required>
                <label for="edit_updatedate">Update Date:</label>
                <input type="datetime-local" id="edit_updatedate" name="updatedate" value="${updateDate}" step="1" required>
            `;
            editForm.action = '../../handlers/editcustomerreturn_handler.php';
        } else {
            modalTitle.textContent = 'Edit Supplier Return';
            modalFields.innerHTML = `
                <input type="hidden" id="edit_supplierreturn_id" name="supplier_return_id" value="${id}">
                <label for="edit_supplier_id">Supplier ID:</label>
                <input type="number" id="edit_supplier_id" name="supplier_id" value="${relatedId}" required>
                <label for="edit_return_reason_supplier">Return Reason:</label>
                <input type="text" id="edit_return_reason_supplier" name="return_reason" value="${reason}" required>
                <label for="edit_return_date_supplier">Return Date:</label>
                <input type="date" id="edit_return_date_supplier" name="return_date" value="${date}" required>
                <label for="edit_refund_status_supplier">Refund Status:</label>
                <select id="edit_refund_status_supplier" name="refund_status" required>
                    <option value="Pending" ${status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="Refunded" ${status === 'Refunded' ? 'selected' : ''}>Refunded</option>
                    <option value="Replaced" ${status === 'Replaced' ? 'selected' : ''}>Replaced</option>
                </select>
                <label for="edit_updatedbyid_supplier">Updated By:</label>
                <input type="text" id="edit_updatedbyid_supplier" name="updatedbyid" value="${updateId}" required>
                <label for="edit_updatedate_supplier">Update Date:</label>
                <input type="datetime-local" id="edit_updatedate_supplier" name="updatedate" value="${updateDate}" step="1" required>
            `;
            editForm.action = '../../handlers/editsupplierreturn_handler.php';
        }

        openModal('editModal'); // Open modal after setting values
    }

        // Fix confirmDelete functions to use return_id[]
    function confirmDeleteCustomer(returnId) {
        const form = document.getElementById('delete-form');
        form.action = "../../handlers/deletecustomerreturn_handler.php";
        document.getElementById('delete_return_id').value = returnId;
        openModal('deleteModal');
    }

    function confirmDeleteSupplier(returnId) {
        const form = document.getElementById('delete-form');
        form.action = "../../handlers/deletesupplierreturn_handler.php";
        document.getElementById('delete_return_id').value = returnId;
        openModal('deleteModal');
    }


    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }

function toggleSelectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function deleteSelectedRows() {
    const selectedIds = [];
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    checkboxes.forEach(checkbox => selectedIds.push(checkbox.value));

    if (selectedIds.length === 0) {
        alert("Please select at least one return to delete.");
        return;
    }

    if (!confirm("Are you sure you want to delete the selected returns?")) {
        return;
    }

    const form = document.getElementById('delete-form');
    // Clear previous hidden inputs to avoid duplicates
    form.querySelectorAll('input[name="return_id[]"]').forEach(input => input.remove());

    // Add new hidden inputs
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'return_id[]'; // Consistent array notation
        input.value = id;
        form.appendChild(input);
    });

    // Set the correct action based on the active tab
    const activeTab = document.querySelector('.tabs span.active').getAttribute('data-type');
    form.action = activeTab === 'customer' 
        ? '../../handlers/deletecustomerreturn_handler.php' 
        : '../../handlers/deletesupplierreturn_handler.php';

    form.submit();
}

function searchTable() {
    const activeTab = document.querySelector('.tabs span.active').getAttribute('data-type');
    const tableBody = activeTab === 'customer' 
        ? document.getElementById('customer_returns-table-body').getElementsByTagName('tbody')[0]
        : document.getElementById('supplier-table').getElementsByTagName('tbody')[0];
    
    const rows = tableBody.getElementsByTagName('tr');
    
    if (activeTab === 'customer') {
        const returnIdInput = document.getElementById('search-customer-return-id').value.toLowerCase();
        const customerIdInput = document.getElementById('search-customer-id').value.toLowerCase();
        const createdDateInput = document.getElementById('search-customer-created-date').value;
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            
            const returnId = cells[1] ? cells[1].textContent.toLowerCase() : '';
            const customerId = cells[2] ? cells[2].textContent.toLowerCase() : '';
            const createdDate = cells[8] ? cells[8].textContent : '';
            
            const matchesReturnId = !returnIdInput || returnId.includes(returnIdInput);
            const matchesCustomerId = !customerIdInput || customerId.includes(customerIdInput);
            const matchesCreatedDate = !createdDateInput || createdDate.startsWith(createdDateInput);
            
            if (matchesReturnId && matchesCustomerId && matchesCreatedDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    } else {
        const returnIdInput = document.getElementById('search-supplier-return-id').value.toLowerCase();
        const supplierIdInput = document.getElementById('search-supplier-id').value.toLowerCase();
        const returnDateInput = document.getElementById('search-supplier-return-date').value;
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            
            const returnId = cells[1] ? cells[1].textContent.toLowerCase() : '';
            const supplierId = cells[2] ? cells[2].textContent.toLowerCase() : '';
            const returnDate = cells[4] ? cells[4].textContent : ''; // Return Date is in column 4 for supplier
            
            const matchesReturnId = !returnIdInput || returnId.includes(returnIdInput);
            const matchesSupplierId = !supplierIdInput || supplierId.includes(supplierIdInput);
            const matchesReturnDate = !returnDateInput || returnDate.startsWith(returnDateInput);
            
            if (matchesReturnId && matchesSupplierId && matchesReturnDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }
}

function clearSearch() {
    const activeTab = document.querySelector('.tabs span.active').getAttribute('data-type');
    const tableBody = activeTab === 'customer' 
        ? document.getElementById('customer_returns-table-body').getElementsByTagName('tbody')[0]
        : document.getElementById('supplier-table').getElementsByTagName('tbody')[0];
    
    if (activeTab === 'customer') {
        document.getElementById('search-customer-return-id').value = '';
        document.getElementById('search-customer-id').value = '';
        document.getElementById('search-customer-created-date').value = '';
    } else {
        document.getElementById('search-supplier-return-id').value = '';
        document.getElementById('search-supplier-id').value = '';
        document.getElementById('search-supplier-return-date').value = '';
    }
    
    const rows = tableBody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        rows[i].style.display = '';
    }
}

// Add event listener for Enter key
document.querySelectorAll('.search-container input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchTable();
        }
    });
});



</script>

<div class="main-content">
    <header>
        <h1>Returns</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>

    <!-- Tabs -->
    <div class="tabs">
        <span class="active" data-type="customer" onclick="showTable('customer')">Customer</span>
        <span data-type="supplier" onclick="showTable('supplier')">Supplier</span>
    </div>
    <hr>


        <!-- Search Fields -->
<div class="search-container" id="customer-search" style="display: block;">
    <input type="text" id="search-customer-return-id" placeholder="Customer Return ID">
    <input type="text" id="search-customer-id" placeholder="Customer ID">
    <input type="date" id="search-customer-created-date" placeholder="Created Date">
    <button class="search-btn" onclick="searchTable()">SEARCH</button>
    <button class="clear-btn" onclick="clearSearch()">CLEAR</button>
</div>

<div class="search-container" id="supplier-search" style="display: none;">
    <input type="text" id="search-supplier-return-id" placeholder="Supplier Return ID">
    <input type="text" id="search-supplier-id" placeholder="Supplier ID">
    <input type="date" id="search-supplier-return-date" placeholder="Return Date">
    <button class="search-btn" onclick="searchTable()">SEARCH</button>
    <button class="clear-btn" onclick="clearSearch()">CLEAR</button>
</div>

    <div class="search-container" id="supplier-search" style="display: none;">
        <input type="text" id="search-supplier-return-id" placeholder="Supplier Return ID">
        <input type="text" id="search-supplier-id" placeholder="Supplier ID">
        <input type="date" id="search-supplier-return-date" placeholder="Return Date">
        <button class="search-btn" onclick="searchTable()">SEARCH</button>
        <button class="clear-btn" onclick="clearSearch()">CLEAR</button>
    </div>


    <!-- Table Controls -->
    <div class="table-controls">
        <button type="btn-add active" class="create-btn" onclick="openCreateModal()"> CREATE NEW <i class="fa fa-add"></i>
        </button>
        <button type="btn-delete active" class="delete-btn" onclick="deleteSelectedRows()"> DELETE <i class="fa fa-trash"></i>
        </button>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span id="modal-title">Add Return</span>
                <span class="close" onclick="closeModal('createModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="create-form" method="POST" action="">
                    <div id="modal-fields">
                        <!-- Fields will be populated dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="submit-btn">Add Return</button>
                        <button type="button" class="cancel-btn" onclick="closeModal('createModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span id="editModalTitle">Edit Return</span>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="edit-form" method="POST" action="">
                    <div id="edit-modal-fields">
                        <!-- Fields will be populated dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="submit-btn">Save Changes</button>
                        <button type="button" class="cancel-btn" onclick="closeModal('editModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

            <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Confirm Delete</span>
                <span class="close" onclick="closeModal('deleteModal')">×</span>
            </div>
            <div class="modal-body">
                <form id="delete-form" method="POST" action="">
                    <!-- Use return_id[] consistently for both single and multiple deletes -->
                    <input type="hidden" id="delete_return_id" name="return_id[]">
                    <p>Are you sure you want to delete the selected return(s)?</p>
                    <div class="modal-footer">
                        <button type="submit" class="submit-btn">Yes, Delete</button>
                        <button type="button" class="cancel-btn" onclick="closeModal('deleteModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Customer Returns Table -->
    <div class="returns-table">
        <table id="customer_returns-table-body">    
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"></th>
                    <th>Customer ReturnID</th>
                    <th>Customer ID</th>
                    <th>Reason</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Updated By</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_customer->num_rows > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result_customer)) : ?>
                        <tr>
                            <td><input type="checkbox" class="row-checkbox" name="return_id[]" value="<?= $row['customer_return_id'] ?>"></td>
                            <td><?= $row['customer_return_id'] ?></td>
                            <td><?= $row['customer_id'] ?></td>
                            <td><?= htmlspecialchars($row['return_reason']) ?></td>
                            <td><?= $row['return_date'] ?></td>
                            <td><?= $row['refund_status'] ?></td>
                            <td><?= number_format($row['total_amount'], 2) ?></td>
                            <td><?= $row['createdbyid'] ?? '-' ?></td>
                            <td><?= $row['createdate'] ?></td>
                            <td><?= $row['updatedbyid'] ?? '-' ?></td>
                            <td><?= $row['updatedate'] ?? '-' ?></td>
                            <td>
                                <button class="btn btn-sm text-warning action-btn"
                                    onclick="openEditModal(
                                        'customer',
                                        '<?= $row['customer_return_id'] ?>',
                                        '<?= $row['customer_id'] ?>',
                                        '<?= htmlspecialchars($row['return_reason'], ENT_QUOTES) ?>',
                                        '<?= $row['return_date'] ?>',
                                        '<?= $row['refund_status'] ?>',
                                        '<?= $row['total_amount'] ?>',
                                        '<?= $row['updatedbyid'] ?>',
                                        '<?= $row['updatedate'] ?>'
                                    )">
                                    <i class="fa fa-edit"></i> Edit</button>

                                <button class="btn btn-sm text-danger" onclick="confirmDeleteCustomer(<?= $row['customer_return_id'] ?>)"><i
                                    class="fa fa-trash" style="color:rgb(255, 0, 25);"></i>Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 20px; color: #666;">
                            No returns found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Supplier Return Table -->
    <div class="returns-table">
        <table id="supplier-table" style="display: none;">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-supplier" onclick="toggleSelectAll(this)"></th>
                    <th>Supplier Return ID</th>
                    <th>Supplier ID</th>
                    <th>Reason</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Updated By</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_supplier->num_rows > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result_supplier)) : ?>
                        <tr>
                            <td><input type="checkbox" class="row-checkbox" name="supplier_return_id[]" value="<?= $row['supplier_return_id'] ?>"></td>
                            <td><?= $row['supplier_return_id'] ?></td>
                            <td><?= $row['supplier_id'] ?></td>
                            <td><?= htmlspecialchars($row['return_reason'], ENT_QUOTES) ?></td>
                            <td><?= $row['return_date'] ?></td>
                            <td><?= $row['refund_status'] ?></td>
                            <td><?= $row['createdbyid'] ?></td>
                            <td><?= $row['createdate'] ?></td>
                            <td><?= $row['updatedbyid'] ?? '-' ?></td>
                            <td><?= $row['updatedate'] ?? '-' ?></td>
                            <td>
                                <button class="btn btn-sm text-warning action-btn"
                                    onclick="openEditModal(
                                        'supplier',
                                        '<?= $row['supplier_return_id'] ?>',
                                        '<?= $row['supplier_id'] ?>',
                                        '<?= htmlspecialchars($row['return_reason'], ENT_QUOTES) ?>',
                                        '<?= $row['return_date'] ?>',
                                        '<?= $row['refund_status'] ?>',
                                        '<?= $row['updatedbyid'] ?>',
                                        '<?= $row['updatedate'] ?>'
                                    )">
                                    <i class="fa fa-edit"></i> Edit
                                </button>

                                <button class="btn btn-sm text-danger" onclick="confirmDeleteSupplier(<?= $row['supplier_return_id'] ?>)">
                                    <i class="fa fa-trash" style="color:rgb(255, 0, 25);"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px; color: #666;">
                            No supplier returns found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>