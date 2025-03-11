<?php
include '../../database/database.php';
$query = "SELECT p.product_id, p.product_name, p.quantity, p.price, p.unitofmeasurement, 
                 p.category_id, p.supplier_id, p.createdbyid, p.createdate, p.updatedbyid, p.updatedate, 
                 s.supplier_name
          FROM Product p
          LEFT JOIN Supplier s ON p.supplier_id = s.supplier_id";
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
        background: #34502b;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
    }

    .clear-btn {
        border: 1px solid #34502b;
        background: white;
        width: 70px;
        color: #34502b;
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
        background: #34502b;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    .create-btn:hover {
        background: white;
        color: #34502b;
        border: 1px solid #34502b;
        transform: translateY(-1px);
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
        color: rgb(22, 21, 21) !important;
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
        background: white !important;
        border: 1px solid #34502b !important;
        color: #34502b;
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
            <?php include __DIR__ . '/profile.php'; ?>
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
                CATEGORY <i class="fa-solid fa-gear"></i>
            </button>
            <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                ADD PRODUCT <i class="fa-solid fa-add"></i>
            </button>
        </div>
        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price(₱)</th>
                        <th>Unit</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th style="width:auto;">Created By</th>
                        <th>Created Date</th>
                        <th>Updated By</th>
                        <th>Updated Date</th>
                        <th class="text-center w-5">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><?= $row['product_id'] ?></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= number_format($row['price'], 2) ?></td>
                                <td><?= htmlspecialchars($row['unitofmeasurement']) ?></td>
                                <td><?= $row['category_id'] ?? '-' ?></td>
                                <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                                <td><?= $row['createdbyid'] ?? '-' ?></td>
                                <td><?= $row['createdate'] ?></td>
                                <td><?= $row['updatedbyid'] ?? '-' ?></td>
                                <td><?= $row['updatedate'] ?? '-' ?></td>
                                <td>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 20px; color: #666;">
                                No products found.</td>
                        </tr>
                    <?php endif; ?>
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
                    <label class="my-2">Product Name:</label>
                    <input type="text" name="product_name" class="form-control" required>
                    <label class="my-2">Quantity:</label>
                    <input type="number" name="quantity" class="form-control" required>
                    <label class="my-2">Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                    <label class="my-2">Unit of measurement:</label>
                    <select name="unitofmeasurement" class="form-control" required>
                        <option value="">Select Unit</option>
                        <?php foreach ($units as $value => $label) : ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="my-2">Category:</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $id => $name) : ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="my-2">Supplier of product:</label>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                                <td><?= htmlspecialchars($id) ?></td>
                                <td><?= htmlspecialchars($name) ?></td>
                                <td>
                                    <button class="btn btn-sm text-warning"
                                        onclick="loadEditCategory('<?= htmlspecialchars($id) ?>', '<?= htmlspecialchars($name) ?>')"
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                        <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-danger"
                                        onclick="confirmDeleteCategory('<?= htmlspecialchars($id) ?>')">
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
                    <label for="edit_category_id" class="py-3">Current Category ID:</label>
                    <input type="text" class="form-control" name="category_id" id="edit_category_id" required>

                    <label for="new_category_id" class="py-3">New Category ID:</label>
                    <input type="text" class="form-control" name="new_category_id" id="new_category_id" required>

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
<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
        style="width: auto !important; padding-right: 2.5rem !important;">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']);
    ?>
<?php elseif (isset($_SESSION['error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
        style="width: auto !important; padding-right: 2.5rem !important;">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']);
    ?>
<?php endif ?>

<script>
    function confirmDelete(productid) {
        if (confirm("Are you sure do you want to delete this supplier?")) {
            window.location.href = "../../handlers/delete_product_handler.php?id=" + productid;
        }
    }

    function confirmDeleteCategory(id) {
        if (confirm("Are you sure you want to delete the category '" + id +
                "'? This will also delete all related products.")) {
            window.location.href = "../../handlers/deletecategory_handler.php?id=" + encodeURIComponent(id);
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