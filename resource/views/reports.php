<div class="main-content">
    <header>
        <h1>Reports</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>

    <div class="reports-container">
        <button class="report-btn" onclick="location.href='invoice.php'">
            <i class="fas fa-file-invoice"></i>
            <span>Invoice</span>
        </button>
        <button class="report-btn" onclick="location.href='stock_adjustment.php'">
            <i class="fas fa-boxes"></i>
            <span>Stock Adjustment</span>
        </button>
        <button class="report-btn" onclick="location.href='damage.php'">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Damage</span>
        </button>
    </div>
</div>

<!-- FontAwesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
    .main-content {
        text-align: center;
        padding: 20px;
    }

    h1 {
        font-size: 40px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .reports-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 50px;
        margin-top: 80px;
        flex-wrap: wrap;
    }

    .report-btn {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 50px;
        font-size: 26px;
        font-weight: bold;
        border-radius: 20px;
        cursor: pointer;
        transition: 0.3s;
        box-shadow: 6px 8px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 250px;
        height: 220px;
        text-align: center;
    }

    .report-btn i {
        font-size: 60px;
        margin-bottom: 15px;
    }

    .report-btn:hover {
        background-color: #0056b3;
        transform: translateY(-8px);
    }
</style>

<script src="script.js"></script>
</body>

</html>
