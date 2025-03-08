<?php
include '../../database/database.php';
$query = "SELECT product_id, product_name, quantity, price, unitofmeasurement, 
                 category_id, supplier_id, createdbyid, createdate, updatedbyid, updatedate 
          FROM Product";
$result = $conn->query($query);
// Query to get all suppliers for the dropdowns
$supplier_query = "SELECT supplier_id, supplier_name FROM Supplier";
$supplier_result = $conn->query($supplier_query);
$suppliers = [];
while ($supplier = $supplier_result->fetch_assoc()) {
    $suppliers[$supplier['supplier_id']] = $supplier['supplier_name'];
}

$category_query = "SELECT category_id, category_name FROM Category";
$category_result = $conn->query($category_query);
$categories = [];
while ($category = $category_result->fetch_assoc()) {
    $categories[$category['category_id']] = $category['category_name'];
}
// Define unit of measurement options
$units = [
    'pcs' => 'Pieces (pcs)',
    'kg' => 'Kilograms (kg)',
    'g' => 'Grams (g)',
    'l' => 'Liters (l)',
    'ml' => 'Milliliters (ml)',
    'm' => 'Meters (m)',
    'can' => 'Canned Goods (can)',
    'box' => 'Box',
    'pack' => 'Pack'
];
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

    /* Table Styling */
    .products-table {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
        /* Enables scrolling for wide tables */
    }

    .table-controls {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .create-btn {
        background: #6b8e5e;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.5s ease-in-out;
    }

    .create-btn:hover {
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
        background-color: #e6c200 !important;
        color: white !important;
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

        .search-container {
            flex-direction: column;
        }

        .table-controls {
            justify-content: center;
        }
    }

    @media (max-width: 500px) {
        .sidebar {
            display: none;
            /* Hide sidebar on very small screens */
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

    .supplier-dropdown {
        width: 100%;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: white;
    }

    .supplier-dropdown:disabled {
        background-color: #f9f9f9;
        opacity: 1;
        color: #333;
    }

    .category-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .category-table th,
    .category-table td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    .category-table th {
        background-color: #e6c200;
        color: white;
    }

    .category-table tr:hover {
        background-color: #f1f1f1;
    }

    .category-table {
        width: 100%;
        /* Adjust width as needed */
        border-collapse: collapse;
        margin-top: 15px;
    }

    .category-table th,
    .category-table td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        text-align: center;
        /* Center-align text in table cells */
    }

    .category-table th {
        background-color: #e6c200;
        color: white;
    }

    .category-table tr:hover {
        background-color: #f1f1f1;
    }

    /* Center-align form elements */
    .modal-body.text-center .form-label {
        display: block;
        text-align: center;
    }

    .modal-body.text-center .form-control {
        display: block;
        margin: 0 auto;
        /* Center the input fields */
    }

    .modal-body.text-center .btn {
        display: block;
        margin: 0 auto;
    }
</style>

<div class="main-content">
    <header>
        <h1>Products</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>
    <hr>
    <div class="search-container">
        <input type="text" id="searchProductID" placeholder="Product ID">
        <input type="text" id="searchCategoryID" placeholder="Category ID">
        <input type="text" id="searchName" placeholder="Name">
        <input type="text" id="searchPrice" placeholder="Price">
        <button class="search-btn" onclick="filterProducts()">SEARCH</button>
        <button class="clear-btn" onclick="clearFilters()">CLEAR</button>
    </div>

    <div class="products-table">
        <div class="table-controls">
            <button class="create-btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                Category <i class="fa-solid fa-gear"></i>
            </button>
            <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                CREATE NEW <i class="fa-solid fa-add"></i>
            </button>
        </div>
        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Unit</th>
                        <th>Category ID</th>
                        <th>Supplier ID</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Updated By</th>
                        <th>Updated Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body">
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['product_id'] ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= number_format($row['price'], 2) ?></td>
                            <td><?= htmlspecialchars($row['unitofmeasurement']) ?></td>
                            <td><?= $row['category_id'] ?? 'N/A' ?></td>
                            <td><?= $row['supplier_id'] ?? 'N/A' ?></td>
                            <td><?= $row['createdbyid'] ?? 'N/A' ?></td>
                            <td><?= $row['createdate'] ?></td>
                            <td><?= $row['updatedbyid'] ?? 'N/A' ?></td>
                            <td><?= $row['updatedate'] ?? 'N/A' ?></td>
                            <td class="d-flex gap-2">
                                <button class="btn btn-sm text-warning" onclick='loadEditModal(<?= json_encode($row) ?>)'
                                    data-bs-toggle="modal" data-bs-target="#editProductModal">
                                    <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                                </button>

                                <button class="btn btn-sm text-danger" onclick="confirmDelete(<?= $row['product_id'] ?>)"><i
                                        class="fa fa-trash" style="color:rgb(255, 0, 25);"></i>
                                    Delete</button>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- ADD PRODUCT MODAL -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/addproduct_handler.php" method="POST">
                <div class="modal-body">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" class="form-control" required>
                    <label>Quantity:</label>
                    <input type="number" name="quantity" class="form-control" required>
                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                    <label>Unit:</label>
                    <select name="unitofmeasurement" class="form-control" required>
                        <option value="">Select Unit</option>
                        <?php foreach ($units as $value => $label) : ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Category:</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $id => $name) : ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Supplier:</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $id => $name) : ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Product</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- EDIT PRODUCT MODAL -->

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/editproduct_handler.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" class="form-control" required>
                    <label>Quantity:</label>
                    <input type="number" name="quantity" class="form-control" required>
                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                    <label>Unit:</label>
                    <select name="unitofmeasurement" class="form-control" required>
                        <option value="">Select Unit</option>
                        <?php foreach ($units as $value => $label) : ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Category:</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $id => $name) : ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Supplier:</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $id => $name) : ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CATEGORY MANAGEMENT MODAL -->

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 600px;">
        <!-- Custom width instead of modal-lg -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add Category Form -->
                <form id="addCategoryForm" action="../../handlers/addcategory_handler.php" method="POST">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category ID:</label>
                        <input type="text" class="form-control" id="category_id" name="category_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_name" class="form-label">New Category Name:</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Category</button>
                </form>

                <!-- Category List -->
                <table class="category-table">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="category-table-body">
                        <?php foreach ($categories as $id => $name) : ?>
                            <tr class="text-center">
                                <td><?= $id ?></td>
                                <td><?= htmlspecialchars($name) ?></td>
                                <td>
                                    <button class="btn btn-sm text-warning"
                                        onclick="loadEditCategory(<?= $id ?>, '<?= htmlspecialchars($name) ?>')"
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                        <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-danger" onclick="confirmDeleteCategory(<?= $id ?>)">
                                        <i class="fa fa-trash" style="color: rgb(255, 0, 25);"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- EDIT CATEGORY MODAL -->
<!-- EDIT CATEGORY MODAL -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../handlers/editcategory_handler.php" method="POST">
                <div class="modal-body">
                    <label for="edit_category_id" class="py-3">Category Id:</label>
                    <input type="text" class="form-control" name="category_id" id="edit_category_id" required>
                    <label for="edit_category_name" class="py-3">Category Name:</label>
                    <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if (isset($_docSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
        style="width: 290px !important;">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']);
    ?>
<?php endif; ?>

<script>
    function confirmDelete(productid) {
        if (confirm("Are you sure do you want to delete this supplier?")) {
            window.location.href = "../../handlers/delete_product_handler.php?id=" + productid;
        }
    }

    function loadEditModal(product) {
        console.log(product);


        document.querySelector("#editProductModal input[name='product_id']").value = product.product_id;

        document.querySelector("#editProductModal input[name='product_name']").value = product.product_name;
        document.querySelector("#editProductModal input[name='quantity']").value = product.quantity;
        document.querySelector("#editProductModal input[name='price']").value = product.price;

        document.querySelector("#editProductModal select[name='unitofmeasurement']").value =
            product.unitofmeasurement || '';
        document.querySelector("#editProductModal select[name='category_id']").value =
            (product.category_id === "N/A" || product.category_id === null) ? '' : product.category_id;

        document.querySelector("#editProductModal select[name='supplier_id']").value =
            (product.supplier_id === "N/A" || product.supplier_id === null) ? '' : product.supplier_id;
    }

    function loadEditCategory(categoryId, categoryName) {
        document.querySelector("#editCategoryModal input[name='category_id']").value = categoryId;
        document.querySelector("#editCategoryModal input[name='category_name']").value = categoryName;
    }

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>