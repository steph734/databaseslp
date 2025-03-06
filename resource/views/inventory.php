<div class="main-content">
    <header>
        <h1>Inventory</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>

    <div class="search-container">
        <input type="text" placeholder="Inventory ID">
        <input type="text" placeholder="Product ID">
        <input type="text" placeholder="Price">
        <input type="text" placeholder="Quantity">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="inventory-table">
        <div class="table-controls">
            <button class="create-btn">CREATE NEW <span>+</span></button>
            <button class="edit-btn">EDIT <span>✏️</span></button>
            <button class="delete-btn">DELETE <span>🗑️</span></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>InventoryID</th>
                    <th>ProductID</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Received</th>
                    <th>Last Restock</th>
                    <th>Damage</th>
                </tr>
            </thead>
            <tbody id="inventory-table-body">
                <!-- Data will be populated dynamically from the database -->
            </tbody>
        </table>
    </div>
</div>