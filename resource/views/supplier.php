<?php
include '../../database/database.php';

$query = "SELECT supplier_id, supplier_name, contact_info, address, createdbyid, createdate, updatedbyid, updatedate FROM Supplier";
$result = $conn->query($query);
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
        border: 1px solid rgb(227, 209, 8);
        padding: 20px;
        transition: 0.3s;
        min-height: 200px;
    }

    /* Info Button */
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

    /* Hidden Dropdown */
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
        color: rgb(255, 174, 0);
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

    .btn-delete {
        background: rgb(255, 255, 255);
        color: rgba(255, 0, 25, 0.37);
        border: 1px solid rgba(255, 0, 25, 0.37);
    }

    .btn-delete:hover {
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

    .add-form input {
        /* display: block; */
        width: 100%;
        margin-top: 10px;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #6b8e5e;
    }

    .btn-save {
        border: none;
        margin-top: 20px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        color: white;
        background-color: #ffc107;
        border-radius: 5px;
        width: 100px !important;
        transition: all 0.2s ease-in-out;
    }

    .btn-save:hover {
        color: #ffc107;
        background-color: rgb(255, 255, 255);
        border: 1px solid #ffc107;
    }

    .header-supplier {
        position: inherit;
        height: 80px;
    }

    .btn-add {
        background: #6b8e5e;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        margin-bottom: 20px;
        top: 100px;
        right: 100px;
        transition: all 0.3s ease-in-out;
    }

    .btn-add:hover {
        transform: translateY(-3px);
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
        color: #6b8e5e;
    }
</style>

<div class="main-content">
    <header>
        <h1>Suppliers</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>
    <hr>
    <div class="header-supplier">
        <button class="btn-add active" onclick="toggleAddForm()" style="font-weight: bold;"><i class="fa fa-add"></i>
            Add Supplier</button>
    </div>
    <div class="add-form" id="addSupplierForm">

        <h3 style="color: #6b8e5e;">Add New Supplier</h3>
        <form action="../../handlers/addsupplier_handler.php" method="POST">
            <input type="text" name="supplier_name" placeholder="Supplier Name" required>
            <input type="text" name="contact_info" placeholder="Contact Info" required>
            <input type="text" name="address" placeholder="Address" required>
            <button type="submit" class="btn-save">Save</button>
        </form>
    </div>

    <div class="card-container">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="supplier-card">

                <div class="supplier-logo">
                    <i class="fa-solid fa-shop"></i>
                </div>
                <hr>
                <h3><strong><?= htmlspecialchars($row['supplier_name']) ?></strong></h3>

                <button class="info-toggle" onclick="toggleInfo(this)">
                    <i class="fa fa-circle-info" style="color:rgb(197, 192, 192);"></i>
                </button>

                <div class="supplier-info">
                    <p><strong>Created by:</strong> <?= $row['createdbyid'] ?? 'N/A' ?></p>
                    <p><strong>Created at:</strong> <?= $row['createdate'] ?></p>
                    <p><strong>Updated by:</strong> <?= $row['updatedbyid'] ?? 'N/A' ?></p>
                    <p><strong>Updated at:</strong> <?= $row['updatedate'] ?? 'N/A' ?></p>
                </div>

                <div class="details">
                    <p><strong class="important-detail">Contact Info:</strong> <?= htmlspecialchars($row['contact_info']) ?>
                    </p>
                    <p><strong class="important-detail">Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
                </div>

                <div class="supplier-actions">
                    <button class="btn btn-edit" onclick="loadEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-delete" onclick="confirmDelete(<?= $row['supplier_id'] ?>)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<script>
    function toggleAddForm() {
        var form = document.getElementById("addSupplierForm");
        var button = document.querySelector(".btn-add");

        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            button.innerHTML = '<i class="fa fa-times"></i> Close';
            button.style.backgroundColor = "white";
            button.style.color = "green";
            button.style.border = "1px solid green";
        } else {
            form.style.display = "none";
            button.innerHTML = '<i class="fa fa-add"></i> Add Supplier';
            button.style.backgroundColor = "#6b8e5e";
            button.style.color = "white";
        }
    }


    function confirmDelete(supplierId) {
        if (confirm("Are you sure you want to delete this supplier?")) {
            window.location.href = "delete_supplier.php?id=" + supplierId;
        }
    }

    function loadEditModal(supplier) {
        document.querySelector("#editSupplierModal input[name='supplier_id']").value = supplier.supplier_id;
        document.querySelector("#editSupplierModal input[name='supplier_name']").value = supplier.supplier_name;
        document.querySelector("#editSupplierModal input[name='contact_person']").value = supplier.contact_person;
        document.querySelector("#editSupplierModal input[name='phone']").value = supplier.phone;
        document.querySelector("#editSupplierModal input[name='email']").value = supplier.email;
        document.querySelector("#editSupplierModal input[name='address']").value = supplier.address;
    }

    function toggleInfo(button) {
        var infoBox = button.nextElementSibling; // Get the next element (supplier-info div)
        infoBox.classList.toggle("active");
    }
</script>