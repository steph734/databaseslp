<div class="main-content">
    <header>
        <h1>Reports</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>


    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .main-content {
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }

        h1 {
            font-size: 32px;
        }

        /* Search Bar */
        .search-container {
            display: flex;
            align-items: center;
            background: #f1f1f1;
            border-radius: 20px;
            padding: 8px 12px;
            width: 250px;
        }

        .search-container input {
            border: none;
            background: transparent;
            outline: none;
            flex: 1;
            padding: 5px;
        }

        .search-container i {
            color: #777;
            font-size: 16px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 30px;
        }

        .tabs span {
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .tabs span.active {
            border-bottom: 3px solid #007bff;
            color: #007bff;
        }

        .tabs span i {
            font-size: 22px;
        }

        hr {
            margin-top: -5px;
            border: none;
            height: 2px;
            background: #ddd;
            width: 100%;
        }

        /* Tab Content */
        .tab-content {
            margin-top: 20px;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }
    </style>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <span class="active" data-type="invoice" onclick="showTab('invoice')">
            <i class="fas fa-file-invoice"></i> Invoice
        </span>
        <span data-type="stock_adjustment" onclick="showTab('stock_adjustment')">
            <i class="fas fa-boxes"></i> Stock Adjustment
        </span>
        <span data-type="damage" onclick="showTab('damage')">
            <i class="fas fa-exclamation-triangle"></i> Damage
        </span>
    </div>
    <hr>

    <!-- Tab Content -->
    <div class="tab-content">
        <div id="invoice" class="tab-panel active">
            <h2>Invoice Report</h2>
          
        </div>
        <div id="stock_adjustment" class="tab-panel">
            <h2>Stock Adjustment Report</h2>
          
        </div>
        <div id="damage" class="tab-panel">
            <h2>Damage Report</h2>
           
        </div>
    </div>
</div>

<script>
    function showTab(tabId) {
        // Remove active class from all tabs
        document.querySelectorAll('.tabs span').forEach(tab => tab.classList.remove('active'));

        // Hide all tab panels
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

        // Activate selected tab
        document.querySelector(`.tabs span[data-type="${tabId}"]`).classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
</script>