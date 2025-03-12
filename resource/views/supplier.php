<?php
include '../../database/database.php';

$supplier_query = "SELECT supplier_id, supplier_name, contact_info, address, status, createdbyid, createdate, updatedbyid, updatedate FROM Supplier";
$supplier_result = $conn->query($supplier_query);
?>

<style>
    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .supplier-card {
        position: relative;
        background: white;
        border-radius: 10px;
        border: 1px solid #34502b;
        padding: 20px;
        transition: 0.3s;
        min-height: 200px;
    }

    .info-toggle {
        position: absolute;
        top: 10px;
        right: 10px;
        background: transparent;
        border: none;
        font-size: 16px;
        color: #007bff;
        cursor: pointer;
    }

    .supplier-info {
        display: none;
        position: absolute;
        top: 35px;
        right: 10px;
        background: rgba(255, 255, 255, 0.8);
        color: #555;
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        text-align: left;
        width: 180px;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .supplier-info.active {
        display: block;
    }

    .supplier-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .supplier-card h3 {
        margin-bottom: 10px;
        color: #34502b;
    }

    .supplier-card p {
        margin: 5px 0;
        font-size: 14px;
        color: #555;
    }

    .supplier-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        color: white;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-edit {
        background: rgb(255, 255, 255);
        color: #ffc107;
        border: 1px solid #ffc107;
    }

    .btn-edit:hover {
        background: #ffc107;
        color: white;
    }

    .btn-delete-supplier {
        background: rgb(255, 255, 255);
        color: rgba(255, 0, 25, 0.37);
        border: 1px solid rgba(255, 0, 25, 0.37);
    }

    .btn-delete-supplier:hover {
        background: rgb(255, 0, 25);
        color: white;
    }

    .add-form {
        display: none;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .add-form input,
    .add-form textarea {
        width: 100%;
        margin-top: 10px;
        padding: 8px;
    }

    .btn-save {
        border: none;
        margin-top: 20px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        color: white;
        background-color: #34502b;
        border-radius: 5px;
        width: 100px !important;
        transition: all 0.2s ease-in-out;
    }

    .btn-save:hover {
        color: #34502b;
        background-color: rgb(255, 255, 255);
        border: 1px solid #34502b;
    }

    .header-supplier {
        position: inherit;
        height: 80px;
    }

    .btn-add-supplier {
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

    .btn-add-supplier:hover {
        transform: translateY(-3px);
    }

    .btn-add-supplier.active {
        background: white;
        color: #34502b;
        border: 1px solid #34502b;
    }

    .details {
        margin-bottom: 30px;
    }

    .supplier-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
        font-size: 100px;
        color: rgb(191, 195, 190);
    }

    .tabs {
        display: flex;
        gap: 20px;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .tabs a,
    .tabs span {
        cursor: pointer;
        color: #000;
        text-decoration: none;
    }

    .tabs span.active {
        border-bottom: 2px solid #000;
    }

    /* Status Dropdown Styles */
    .status-dropdown-supplier {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 14px;
        font-weight: bold;
        background: white;
        width: 120px;
        cursor: pointer;
    }


    .status-active {
        color: #28a745;
    }

    .status-inactive {
        color: #dc3545;
    }
</style>

<div class="main-content">
    <header>
        <h1>Suppliers</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>
    <!-- Tabs -->
    <div class="tabs">
        <span class="active" data-tab="suppliers">My Suppliers</span>
        <span data-tab="receiving">Receiving</span>
    </div>
    <hr>

    <!-- Suppliers Content -->
    <div id="suppliers-content">
        <div class="header-supplier">
            <button class="btn-add-supplier" onclick="toggleAddSupplierForm()">
                <i class="fa fa-add"></i> Add Supplier
            </button>
        </div>
        <div class="add-form" id="addSupplierForm">
            <h3 style="color: #34502b;">Add New Supplier</h3>
            <form action="../../handlers/addsupplier_handler.php" method="POST">
                <input class="form-control" type="text" name="supplier_name" placeholder="Supplier Name" required>
                <input class="form-control" type="text" name="contact_info" placeholder="Contact Info" required>
                <textarea class="form-control my-3" name="address" placeholder="Address" rows="3" required></textarea>
                <button type="submit" class="btn-save">Save</button>
            </form>
        </div>

        <div class="card-container">
            <?php while ($row = $supplier_result->fetch_assoc()) : ?>
                <div class="supplier-card">
                    <div class="supplier-logo">
                        <i class="fa-solid fa-shop"></i>
                    </div>
                    <hr>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><strong><?= htmlspecialchars($row['supplier_name']) ?></strong></h3>
                        <select class="status-dropdown-supplier status-<?= strtolower($row['status']) ?>"
                            onchange="updateSupplierStatus(<?= $row['supplier_id'] ?>, this.value)">

                            <option value="active" <?= $row['status'] === 'active' ? 'selected' : '' ?>
                                class="status-active">Active</option>
                            <option value="inactive" <?= $row['status'] === 'inactive' ? 'selected' : '' ?>
                                class="status-inactive">Inactive</option>
                        </select>
                    </div>
                    <button class="info-toggle" onclick="toggleInfo(this)">
                        <i class="fa fa-circle-info" style="color:rgba(0, 0, 0, 0.87);"></i>
                    </button>
                    <div class="supplier-info">
                        <p><strong>Created by:</strong> <?= $row['createdbyid'] ?? 'N/A' ?></p>
                        <p><strong>Created at:</strong> <?= $row['createdate'] ?></p>
                        <p><strong>Updated by:</strong> <?= $row['updatedbyid'] ?? 'N/A' ?></p>
                        <p><strong>Updated at:</strong> <?= $row['updatedate'] ?? 'N/A' ?></p>
                    </div>
                    <div class="details">
                        <p><strong class="important-detail">Contact Info:</strong>
                            <?= htmlspecialchars($row['contact_info']) ?></p>
                        <p><strong class="important-detail">Address:</strong> <?= htmlspecialchars($row['address']) ?>
                        </p>
                    </div>
                    <div class="supplier-actions">
                        <button class="btn btn-edit" onclick="loadEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-delete-supplier" onclick="confirmDelete(<?= $row['supplier_id'] ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Receiving Content -->
    <div id="receiving-content" style="display: none;">
        <?php include __DIR__ . '/receiving.php'; ?>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSupplierForm" action="../../handlers/editsupplier_handler.php" method="POST">
                    <input type="hidden" id="editSupplierId" name="supplier_id">
                    <div class="mb-3">
                        <label for="editSupplierName" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="editSupplierName" name="supplier_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editContactInfo" class="form-label">Contact Info</label>
                        <input type="text" class="form-control" id="editContactInfo" name="contact_info" required>
                    </div>
                    <div class="mb-3">
                        <label>Address:</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
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
    function loadEditModal(supplier) {
        document.getElementById("editSupplierId").value = supplier.supplier_id;
        document.getElementById("editSupplierName").value = supplier.supplier_name;
        document.getElementById("editContactInfo").value = supplier.contact_info;
        document.getElementById("editAddress").value = supplier.address;

        var modal = new bootstrap.Modal(document.getElementById("editSupplierModal"));
        modal.show();
    }

    function toggleAddSupplierForm() {
        var form = document.getElementById("addSupplierForm");
        var button = document.querySelector(".btn-add-supplier");

        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            button.innerHTML = '<i class="fa fa-times"></i> Close';
            button.classList.add("active");
        } else {
            form.style.display = "none";
            button.innerHTML = '<i class="fa fa-add"></i> Add Supplier';
            button.classList.remove("active");
        }
    }

    function confirmDelete(supplierId) {
        if (confirm("Are you sure you want to delete this supplier?")) {
            window.location.href = "../../handlers/delete_supplier_handler.php?id=" + supplierId;
        }
    }

    function toggleInfo(button) {
        var infoBox = button.nextElementSibling;
        infoBox.classList.toggle("active");
    }

    function updateSupplierStatus(supplierId, newStatus) {
        // Update the dropdown color dynamically
        const dropdown = document.querySelector(`select[onchange="updateSupplierStatus(${supplierId}, this.value)"]`);
        dropdown.className = `status-dropdown status-${newStatus}`;

        // AJAX request to update the database
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../../handlers/update_supplier_status_handler.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.responseText === "success") {
                    console.log(`Status updated to ${newStatus} for supplier ID ${supplierId}`);
                } else {
                    alert("Failed to update status: " + xhr.responseText);
                    // Revert dropdown on failure
                    dropdown.value = dropdown.dataset.oldValue;
                    dropdown.className = `status-dropdown status-${dropdown.dataset.oldValue}`;
                }
            }
        };
        // Store the old value in case of failure
        dropdown.dataset.oldValue = dropdown.value;
        xhr.send(`supplier_id=${supplierId}&status=${newStatus}`);
    }

    document.querySelectorAll('.tabs span').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tabs span').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('suppliers-content').style.display = this.dataset.tab ===
                'suppliers' ? 'block' : 'none';
            document.getElementById('receiving-content').style.display = this.dataset.tab ===
                'receiving' ? 'block' : 'none';
        });
    });

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>