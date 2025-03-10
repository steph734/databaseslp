<?php
include '../../database/database.php';
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
    .search-container, .table-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .search-container input {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 150px;
    }

    .search-btn, .clear-btn, .create-btn, .edit-btn, .delete-btn {
        padding: 8px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        color: white;
    }

    .search-btn { background: #6b8e5e; }
    .clear-btn { background: #a3a3a3; }
    .create-btn { background: #6b8e5e; }
    .edit-btn { background: #ffd700; color: black; }
    .delete-btn { background: #ff6347; }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background-color: #e6c200;
        color: white;
        text-align: center;
        padding: 10px;
    }

    th, td {
        text-align: center;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    tr:hover {
        background: #f1f1f1;
    }

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 10;
        left: 0;
        top: 0;
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
        width: 40%;
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

    .modal-body input, .modal-body select {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        padding-top: 10px;
    }

    .submit-btn {
        background: #007200;
        color: white;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        margin-right: 10px;
    }

    .cancel-btn {
        background: #a3a3a3;
        color: white;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

</style>

<script>
    function showTable(type) {
        document.getElementById('customer-table').style.display = (type === 'customer') ? 'table' : 'none';
        document.getElementById('supplier-table').style.display = (type === 'supplier') ? 'table' : 'none';

        document.querySelectorAll('.tabs span').forEach(tab => tab.classList.remove('active'));
        document.querySelector(`.tabs span[data-type="${type}"]`).classList.add('active');

        // Update form actions
        document.getElementById('create-form').action = (type === 'customer') ? 'handlers/addcustomerreturn_handler.php' : 'handlers/addsupplierreturn_handler.php';
        document.getElementById('edit-form').action = (type === 'customer') ? 'handlers/editcustomerreturn_handler.php' : 'handlers/editsupplierreturn_handler.php';
        document.getElementById('delete-form').action = (type === 'customer') ? 'handlers/deletecustomerreturn_handler.php' : 'handlers/deletesupplierreturn_handler.php';
    }

    function openModal(modalId) {
        document.getElementById(modalId).style.display = "flex";
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    function openEditModal(returnId, customerId, reason, returnDate, refundStatus, totalAmount, createdBy, createDate) {
        document.getElementById("edit_customerreturn_id").value = returnId;
        document.getElementById("edit_customer_id").value = customerId;
        document.getElementById("edit_return_reason").value = reason;
        document.getElementById("edit_return_date").value = returnDate;
        document.getElementById("edit_refund_status").value = refundStatus;
        document.getElementById("edit_total_amount").value = totalAmount;
        document.getElementById("edit_createdbyid").value = createdBy;
        document.getElementById("edit_createdate").value = createDate;
        openModal("editModal");
    }

    function openDeleteModal(returnId) {
        document.getElementById("delete_return_id").value = returnId;
        openModal("deleteModal");

    }

    window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
}
</script>

<div class="main-content">
    <header>
        <h1>Returns</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>

    <!-- Tabs -->
    <div class="tabs">
        <span class="active" data-type="customer" onclick="showTable('customer')">Customer</span>
        <span data-type="supplier" onclick="showTable('supplier')">Supplier</span>
    </div>

    <!-- Search Fields -->
    <div class="search-container">
        <input type="text" placeholder="Customer Return ID">
        <input type="text" placeholder="Sales ID">
        <input type="text" placeholder="Product ID">
        <input type="text" placeholder="Reason">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

   <!-- Table Controls -->
<div class="table-controls">
    <button type="button" class="create-btn" onclick="openModal('createModal')">CREATE NEW <span>+</span></button>
    <button type="button" class="edit-btn" onclick="openEditModal('editModal')">EDIT <span>✏️</span></button>
    <button type="button" class="delete-btn" onclick="openDeleteModal('deleteModal')">DELETE <span>🗑️</span></button>
</div>

<!-- Create Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span>Add Return</span>
            <span class="close" onclick="closeModal('createModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="../../handlers/addcustomerreturn_handler.php">
                <label for="customer_id">Customer Return ID:</label>
                <input type="number" name="customer_id" required>

                <label for="return_reason">Return Reason:</label>
                <input type="text" name="return_reason" required>

                <label for="return_date">Return Date:</label>
                <input type="date" name="return_date" required>

                <label for="refund_status">Refund Status:</label>
                <select name="refund_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>

                <label for="total_amount">Total Amount:</label>
                <input type="number" name="total_amount" step="0.01" required>

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
            <span>Edit Return</span>
            <span class="close" onclick="closeModal('editModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="../../handlers/editcustomerreturn_handler.php">
                <input type="hidden" id="edit_return_id" name="return_id">

                <label for="edit_return_reason">Return Reason:</label>
                <input type="text" id="edit_return_reason" name="return_reason" required>

                <label for="edit_return_date">Return Date:</label>
                <input type="date" id="edit_return_date" name="return_date" required>

                <label for="edit_refund_status">Refund Status:</label>
                <select id="edit_refund_status" name="refund_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>

                <label for="edit_total_amount">Total Amount:</label>
                <input type="number" id="edit_total_amount" name="total_amount" step="0.01" required>

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
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="../../handlers/deletecustomerreturn_handler.php">
                <input type="hidden" id="delete_return_id" name="return_id">
                <p>Are you sure you want to delete this return?</p>
                <div class="modal-footer">
                    <button type="submit" class="submit-btn">Yes, Delete</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('deleteModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Returns Table -->
    <div class="returns-table">
        <table id="customer-table">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>CustomerReturnID</th>
                    <th>SalesID</th>
                    <th>ProductID</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $query = "SELECT * FROM customerreturn";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td><input type='checkbox'></td>
            <td>{$row['customer_return_id']}</td>
            <td>{$row['customer_id']}</td>
            <td>{$row['return_reason']}</td>
            <td>{$row['return_date']}</td>
            <td>{$row['refund_status']}</td>
            <td>{$row['total_amount']}</td>
            <td>{$row['createdbyid']}</td>
            <td>{$row['createdate']}</td>
            <td>{$row['updatedbyid']}</td>
            <td>{$row['updatedate']}</td>
            <td>
                <button class='edit-btn' onclick=\"openEditModal(
                    '{$row['customer_return_id']}', 
                    '{$row['customer_id']}', 
                    '{$row['return_reason']}', 
                    '{$row['return_date']}', 
                    '{$row['refund_status']}', 
                    '{$row['total_amount']}', 
                    '{$row['createdbyid']}', 
                    '{$row['createdate']}'
                )\">Edit</button>

                <button class='delete-btn' onclick=\"openDeleteModal('{$row['customer_return_id']}')\">Delete</button>
            </td>
        </tr>";
    }
    ?>
</tbody>
        </table>

        <table id="supplier-table" style="display: none;">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>SupplierReturnID</th>
                    <th>ProductID</th>
                    <th>SupplierID</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM supplierreturn";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td><input type='checkbox'></td>
                        <td>{$row['supplier_return_id']}</td>
                        <td>{$row['supplier_id']}</td>
                        <td>{$row['return_reason']}</td>
                        <td>{$row['return_date']}</td>
                        <td>{$row['refund_status']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>