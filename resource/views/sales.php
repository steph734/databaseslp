<div class="main-content">
    <header>
        <h1>Sales</h1>
        <div class="search-profile">
            <input type="text" placeholder="Search...">
            <img src="profile.jpg" alt="Profile">
        </div>
    </header>

    <div class="search-container-sales">
        <input type="text" placeholder="Sales ID">
        <input type="text" placeholder="Customer ID">
        <input type="text" placeholder="Total Amount">
        <input type="text" placeholder="Amount Due">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="sales-table">
        <div class="table-controls">
            <button class="create-btn">CREATE NEW <span>+</span></button>
            <button class="edit-btn">EDIT <span>✏️</span></button>
            <button class="delete-btn">DELETE <span>🗑️</span></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>SalesID</th>
                    <th>CustomerID</th>
                    <th>Sale Date</th>
                    <th>Total Amount</th>
                    <th>Amount Due</th>
                </tr>
            </thead>
            <tbody id="sales-table-body">
                <!-- Data will be populated dynamically from the database -->
            </tbody>
        </table>
    </div>
</div>