<?php
include '../../database/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../../resource/views/products.php?error=unauthorized");
    exit();
}

// Initialize query components
$whereConditions = [];
$orderClause = "ORDER BY p.product_id ASC"; // Default ordering

// Handle search container filters (from SEARCH button)
if (isset($_GET['search']) && $_GET['search'] === '1') {
    if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
        $whereConditions[] = "p.product_id = '" . $conn->real_escape_string($_GET['product_id']) . "'";
    }
    if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
        $whereConditions[] = "p.category_id = '" . $conn->real_escape_string($_GET['category_id']) . "'";
    }
    if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
        $whereConditions[] = "p.product_name LIKE '%" . $conn->real_escape_string($_GET['product_name']) . "%'";
    }
    if (isset($_GET['price']) && !empty($_GET['price'])) {
        $whereConditions[] = "p.price = '" . $conn->real_escape_string($_GET['price']) . "'";
    }
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $whereConditions[] = "p.status = '" . $conn->real_escape_string($_GET['status']) . "'";
    }
}

// Handle table control filters (status)
if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
    $statusFilter = $conn->real_escape_string($_GET['status_filter']);
    if (in_array($statusFilter, ['available', 'unavailable'])) {
        $whereConditions[] = "p.status = '$statusFilter'";
    }
}

// Handle ordering (from table controls)
if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
    list($column, $direction) = explode('|', $_GET['order_by']);
    $column = $conn->real_escape_string($column);
    $direction = strtoupper($conn->real_escape_string($direction));
    if (
        in_array($column, ['product_id', 'product_name', 'quantity', 'price', 'createdate']) &&
        in_array($direction, ['ASC', 'DESC'])
    ) {
        $orderClause = "ORDER BY p.$column $direction";
    }
}

// Build WHERE clause
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Main query
$query = "SELECT p.product_id, p.product_name, p.quantity, p.price, p.unitofmeasurement, 
                 p.category_id, p.supplier_id, p.createdbyid, p.createdate, p.updatedbyid, p.updatedate, 
                 p.status, s.supplier_name
          FROM Product p
          LEFT JOIN Supplier s ON p.supplier_id = s.supplier_id
          $whereClause
          $orderClause";
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
/* Your existing CSS remains unchanged, with additions for Select2 */
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

.search-container input,
.search-container select {
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

.products-table {
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
    gap: 10px;
    align-items: center;
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

th,
td {
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

th {
    color: rgb(22, 21, 21) !important;
}

tr:hover {
    background: #f1f1f1;
}

tr.unavailable {
    background-color: #f8d7da;
    color: #6c757d;
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

.btn-primary {
    background: white !important;
    border: 1px solid #34502b !important;
    color: #34502b;
}

.btn-add-product {
    background: #34502b;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-cancel {
    background: white;
    color: #34502b;
    padding: 8px 12px;
    border: 1px solid #34502b;
    border-radius: 5px;
    cursor: pointer;
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
}

.supplier-dropdown,
.form-control {
    width: 100%;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: white;
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
    text-align: center;
}

.category-table th {
    background-color: #e6c200;
    color: white;
}

.category-table tr:hover {
    background-color: #f1f1f1;
}

.btn-delete {
    background-color: white;
    color: rgb(81, 2, 2);
    padding: 8px 15px;
    border: 1px solid rgb(81, 2, 2);
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-delete:hover {
    background-color: rgb(81, 2, 2);
    color: white;
}

/* Custom styling for dropdowns */
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
    z-index: 1050;
}

.select2-results__option {
    padding: 8px;
    display: flex;
    align-items: center;
}

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
        flex-wrap: wrap;
        justify-content: center;
    }

    .custom-select,
    .select2-container {
        width: 50px !important;
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

    .custom-select,
    .select2-container {
        width: 50px !important;
    }
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
        <select id="searchStatus">
            <option value="">All Statuses</option>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
        </select>
        <button class="search-btn" onclick="searchProducts()">SEARCH</button>
        <button class="clear-btn" onclick="clearSearch()">CLEAR</button>
    </div>

    <div class="products-table">
        <div class="table-controls">
            <select id="statusFilter" class="custom-select" onchange="applyTableFilters()">
                <option value="" data-icon="fa-solid fa-layer-group">All Statuses</option>
                <option value="available" data-icon="fa-solid fa-check-circle">Available</option>
                <option value="unavailable" data-icon="fa-solid fa-ban">Unavailable</option>
            </select>
            <select id="orderBy" class="custom-select" onchange="applyTableFilters()">
                <option value="product_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">ID (Ascending)</option>
                <option value="product_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">ID (Descending)</option>
                <option value="product_name|ASC" data-icon="fa-solid fa-arrow-up-a-z">Name (A-Z)</option>
                <option value="product_name|DESC" data-icon="fa-solid fa-arrow-down-z-a">Name (Z-A)</option>
                <option value="quantity|ASC" data-icon="fa-solid fa-arrow-up-1-9">Quantity (Low to High)</option>
                <option value="quantity|DESC" data-icon="fa-solid fa-arrow-down-9-1">Quantity (High to Low)</option>
                <option value="price|ASC" data-icon="fa-solid fa-arrow-up-wide-short">Price (Low to High)</option>
                <option value="price|DESC" data-icon="fa-solid fa-arrow-down-short-wide">Price (High to Low)</option>
                <option value="createdate|ASC" data-icon="fa-solid fa-arrow-up-long">Created Date (Oldest)</option>
                <option value="createdate|DESC" data-icon="fa-solid fa-arrow-down-long">Created Date (Newest)</option>
            </select>
            <button class="create-btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fa-solid fa-gear"></i> CATEGORY
            </button>
            <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fa-solid fa-add"></i> ADD PRODUCT
            </button>
            <button class="btn-delete" onclick="markSelectedUnavailable()" style="font-weight: bold;">
                <i class="fa-solid fa-ban"></i> Mark as Unavailable
            </button>
        </div>
        <div class="table-responsive rounded-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price(₱)</th>
                        <th>Unit</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Updated By</th>
                        <th>Updated Date</th>
                        <th class="text-center w-5">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body">
                    <?php if ($result->num_rows > 0) : ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr class="<?= $row['status'] === 'unavailable' ? 'unavailable' : '' ?>">
                        <td><input type="checkbox" class="product-checkbox" data-id="<?= $row['product_id'] ?>"></td>
                        <td><?= $row['product_id'] ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                        <td><?= htmlspecialchars($row['unitofmeasurement']) ?></td>
                        <td><?= $row['category_id'] ?? '-' ?></td>
                        <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= $row['createdbyid'] ?? '-' ?></td>
                        <td><?= $row['createdate'] ?></td>
                        <td><?= $row['updatedbyid'] ?? '-' ?></td>
                        <td><?= $row['updatedate'] ?? '-' ?></td>
                        <td>
                            <button class="btn btn-sm text-warning" onclick='loadEditModal(<?= json_encode($row) ?>)'
                                data-bs-toggle="modal" data-bs-target="#editProductModal">
                                <i class="fa fa-edit" style="color: #ffc107;"></i> Edit
                            </button>
                            <?php if ($row['status'] === 'available') : ?>
                            <button class="btn btn-sm text-danger"
                                onclick="confirmMarkUnavailable(<?= $row['product_id'] ?>)">
                                <i class="fa-solid fa-xmark" style="color: rgb(255, 0, 25);"></i> Mark Unavailable
                            </button>
                            <?php else : ?>
                            <button class="btn btn-sm text-success"
                                onclick="confirmMarkAvailable(<?= $row['product_id'] ?>)">
                                <i class="fa fa-check" style="color: green;"></i> Mark Available
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="14" style="text-align: center; padding: 20px; color: #666;">
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
                    <input type="number" name="quantity" class="form-control" min="0" value="0" disabled>
                    <label class="my-2">Price:</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                    <label class="my-2">Unit of Measurement:</label>
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
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-add-product">Add Product</button>
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
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
                    <input type="number" name="quantity" class="form-control" min="0" disabled>
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
                    <select name="supplier_id" class="form-control">
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
                                <!-- <button class="btn btn-sm text-danger"
                                    onclick="confirmDeleteCategory('<?= htmlspecialchars($id) ?>')">
                                    <i class="fa fa-trash" style="color: rgb(255, 0, 25);"></i> Delete
                                </button> -->
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
<?php unset($_SESSION['success']); ?>
<?php elseif (isset($_SESSION['error'])) : ?>
<div class="alert alert-danger alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
    style="width: auto !important; padding-right: 2.5rem !important;">
    <?= $_SESSION['error']; ?>
    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif ?>

<!-- Include jQuery and Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for statusFilter
    $('#statusFilter').select2({
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
    $('#statusFilter, #orderBy').on('change', function() {
        applyTableFilters();
    });

    // Load URL parameters into inputs
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('searchProductID').value = urlParams.get('product_id') || '';
    document.getElementById('searchCategoryID').value = urlParams.get('category_id') || '';
    document.getElementById('searchName').value = urlParams.get('product_name') || '';
    document.getElementById('searchPrice').value = urlParams.get('price') || '';
    document.getElementById('searchStatus').value = urlParams.get('status') || '';
    document.getElementById('statusFilter').value = urlParams.get('status_filter') || '';
    document.getElementById('orderBy').value = urlParams.get('order_by') || 'product_id|ASC';
});

// Format dropdown options (icon + text)
function formatOption(option) {
    if (!option.element) return option.text;
    return $('<span><i class="' + $(option.element).data('icon') + ' me-2"></i>' + option.text + '</span>');
}

// Format selected option (only icon)
function formatSelection(option) {
    if (!option.element) return option.text;
    return $('<span><i class="' + $(option.element).data('icon') + '"></i></span>');
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
}

function getSelectedProductIds() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    return Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-id'));
}

function markSelectedUnavailable() {
    const selectedIds = getSelectedProductIds();
    if (selectedIds.length === 0) {
        alert("Please select at least one product to mark as unavailable.");
        return;
    }
    fetch('../../handlers/check_status_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            const alreadyUnavailable = data.filter(item => item.status === 'unavailable');
            if (alreadyUnavailable.length > 0) {
                const unavailableIds = alreadyUnavailable.map(item => item.product_id).join(', ');
                alert(`The following product(s) are already unavailable: ${unavailableIds}`);
                if (alreadyUnavailable.length === selectedIds.length) return;
                selectedIds = selectedIds.filter(id => !alreadyUnavailable.some(item => item.product_id == id));
                if (selectedIds.length === 0) return;
            }
            if (confirm(`Are you sure you want to mark ${selectedIds.length} product(s) as unavailable?`)) {
                fetch('../../handlers/mark_unavailable_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            product_ids: selectedIds
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert('Error: ' + data.error);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while marking products as unavailable.');
                    });
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
            alert('An error occurred while checking product statuses.');
        });
}

function confirmMarkUnavailable(productId) {
    fetch('../../handlers/check_status_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_ids: [productId]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data[0] && data[0].status === 'unavailable') {
                alert('This product is already unavailable.');
                return;
            }
            if (confirm("Are you sure you want to mark this product as unavailable?")) {
                window.location.href = "../../handlers/mark_unavailable_handler.php?id=" + productId;
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
            alert('An error occurred while checking the product status.');
        });
}

function confirmMarkAvailable(productId) {
    if (confirm("Are you sure you want to mark this product as available?")) {
        fetch('../../handlers/mark_available_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Error: ' + data.error);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while marking the product as available.');
            });
    }
}

function confirmDeleteCategory(id) {
    if (confirm("Are you sure you want to delete the category '" + id +
            "'? This will also delete all related products.")) {
        window.location.href = "../../handlers/deletecategory_handler.php?id=" + encodeURIComponent(id);
    }
}

function loadEditModal(product) {
    document.querySelector("#editProductModal input[name='product_id']").value = product.product_id;
    document.querySelector("#editProductModal input[name='product_name']").value = product.product_name;
    document.querySelector("#editProductModal input[name='quantity']").value = product.quantity;
    document.querySelector("#editProductModal input[name='price']").value = product.price;
    document.querySelector("#editProductModal select[name='unitofmeasurement']").value = product.unitofmeasurement ||
        '';
    document.querySelector("#editProductModal select[name='category_id']").value = product.category_id || '';
    document.querySelector("#editProductModal select[name='supplier_id']").value = product.supplier_id || '';
}

function loadEditCategory(categoryId, categoryName) {
    document.querySelector("#editCategoryModal input[name='category_id']").value = categoryId;
    document.querySelector("#editCategoryModal input[name='new_category_id']").value = categoryId;
    document.querySelector("#editCategoryModal input[name='category_name']").value = categoryName;
}

function searchProducts() {
    const productId = document.getElementById('searchProductID').value.trim();
    const categoryId = document.getElementById('searchCategoryID').value.trim();
    const name = document.getElementById('searchName').value.trim();
    const price = document.getElementById('searchPrice').value.trim();
    const status = document.getElementById('searchStatus').value;

    const statusFilter = document.getElementById('statusFilter').value;
    const orderBy = document.getElementById('orderBy').value;

    let url = '../../resource/layout/web-layout.php?page=products&search=1';
    const params = [];

    if (productId) params.push(`product_id=${encodeURIComponent(productId)}`);
    if (categoryId) params.push(`category_id=${encodeURIComponent(categoryId)}`);
    if (name) params.push(`product_name=${encodeURIComponent(name)}`);
    if (price) params.push(`price=${encodeURIComponent(price)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (statusFilter) params.push(`status_filter=${encodeURIComponent(statusFilter)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

    if (params.length > 0) url += '&' + params.join('&');
    window.location.href = url;
}

function applyTableFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const orderBy = document.getElementById('orderBy').value;

    const productId = document.getElementById('searchProductID').value.trim();
    const categoryId = document.getElementById('searchCategoryID').value.trim();
    const name = document.getElementById('searchName').value.trim();
    const price = document.getElementById('searchPrice').value.trim();
    const status = document.getElementById('searchStatus').value;

    let url = '../../resource/layout/web-layout.php?page=products';
    const params = [];

    if (productId || categoryId || name || price || status) params.push('search=1');
    if (productId) params.push(`product_id=${encodeURIComponent(productId)}`);
    if (categoryId) params.push(`category_id=${encodeURIComponent(categoryId)}`);
    if (name) params.push(`product_name=${encodeURIComponent(name)}`);
    if (price) params.push(`price=${encodeURIComponent(price)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (statusFilter) params.push(`status_filter=${encodeURIComponent(statusFilter)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

    if (params.length > 0) url += '&' + params.join('&');
    window.location.href = url;
}

function clearSearch() {
    document.getElementById('searchProductID').value = '';
    document.getElementById('searchCategoryID').value = '';
    document.getElementById('searchName').value = '';
    document.getElementById('searchPrice').value = '';
    document.getElementById('searchStatus').value = '';

    const statusFilter = document.getElementById('statusFilter').value;
    const orderBy = document.getElementById('orderBy').value;

    let url = '../../resource/layout/web-layout.php?page=products';
    const params = [];

    if (statusFilter) params.push(`status_filter=${encodeURIComponent(statusFilter)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);

    if (params.length > 0) url += '&' + params.join('&');
    window.location.href = url;
}

setTimeout(function() {
    let alert = document.querySelector(".floating-alert");
    if (alert) {
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    }
}, 4000);
</script>