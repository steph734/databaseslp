<div class="main-content">
    <header>
        <h1>Membership</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>

    <div class="search-container-membership">
        <input type="text" id="membership-id" placeholder="Membership ID">
        <input type="text" id="customer-id" placeholder="Customer ID">
        <input type="text" id="status" placeholder="Status">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="membership-table">
        <div class="table-controls">
            <button class="create-btn">CREATE NEW <span>+</span></button>
            <button class="edit-btn">EDIT <span>✏️</span></button>
            <button class="delete-btn">DELETE <span>🗑️</span></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>Membership ID</th>
                    <th>Customer ID</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>Date Renewal</th>
                    <th>Created By</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody id="membership-table-body">
                <!-- Data will be populated dynamically from the database -->
            </tbody>
        </table>
    </div>
</div>