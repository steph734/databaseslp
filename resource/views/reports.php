<div class="main-content">
    <header>
        <h1>Reports</h1>
        <div class="search-profile">
            <?php include 'searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>

    <div class="search-container">
        <input type="text" id="searchAdjustmentID" placeholder="Adjustment ID">
        <input type="text" id="searchProductID" placeholder="Product ID">
        <input type="text" id="searchDateAdjusted" placeholder="Date Adjusted" onfocus="(this.type='date')" onblur="(this.type='text')">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="supplier-table">
        <div class="table-controls">
            <button class="create-btn">CREATE NEW <span>+</span></button>
            <button class="edit-btn">EDIT <span>✏️</span></button>
            <button class="delete-btn">DELETE <span>🗑️</span></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>AdjustmentID</th>
                    <th>ProductID</th>
                    <th>Adjusted Quantity</th>
                    <th>Description</th>
                    <th>Date Adjusted</th>
                </tr>
            </thead>
            <tbody id="supplier-table-body">
                <!-- Data will be loaded dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="recordModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modal-title">Create Report</h2>
        <form id="recordForm">
            <input type="hidden" id="adjustmentID">
            <label>Product ID:</label>
            <input type="text" id="productID" required>
            <label>Adjusted Quantity:</label>
            <input type="number" id="adjustedQuantity" required>
            <label>Description:</label>
            <input type="text" id="description">
            <label>Date Adjusted:</label>
            <input type="date" id="dateAdjusted" required>
            <button type="submit">Save</button>
        </form>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>