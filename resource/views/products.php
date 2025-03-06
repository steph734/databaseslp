<div class="main-content">
    <header>
        <h1>Products</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>

    <div class="search-container">
        <input type="text" placeholder="Product ID">
        <input type="text" placeholder="Category ID">
        <input type="text" placeholder="Name">
        <input type="text" placeholder="Price">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="products-table">
        <div class="table-controls">
            <button class="create-btn">CREATE NEW <span>+</span></button>
            <button class="edit-btn">EDIT <span>✏️</span></button>
            <button class="delete-btn">DELETE <span>🗑️</span></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>ProductID</th>
                    <th>CategoryID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>SupplierID</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <!-- Data will be populated dynamically from the database -->
            </tbody>
        </table>
    </div>
</div>