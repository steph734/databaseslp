<div class="main-content">
    <header>
        <h1>Customers</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>

    <div class="search-container">
        <input type="text" placeholder="Customer ID">
        <input type="text" placeholder="Name">
        <input type="text" placeholder="Contact">
        <input type="text" placeholder="Address">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="customer-table">
        <div class="table-controls">
            <button class="create-btn">CREATE NEW <span>+</span></button>
            <button class="edit-btn">EDIT <span>✏️</span></button>
            <button class="delete-btn">DELETE <span>🗑️</span></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>CustomerID</th>
                    <th>TypeID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Member</th>
                </tr>
            </thead>
            <tbody id="customer-table-body">
                <!-- Data will be populated dynamically from the database -->
            </tbody>
        </table>
    </div>
</div>