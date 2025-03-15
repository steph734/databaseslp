<?php
include '../../database/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../../resource/views/products.php?error=unauthorized");
    exit();
}

// Fetch all members with their points
$members_query = "
    SELECT c.customer_id, c.name, m.membership_id, COALESCE(SUM(pd.total_points - pd.redeemed_amount), 0) as total_points
    FROM Customer c
    LEFT JOIN Membership m ON c.customer_id = m.customer_id
    LEFT JOIN Points p ON m.membership_id = p.membership_id
    LEFT JOIN Points_Details pd ON p.points_id = pd.points_id
    WHERE c.is_member = 1
    GROUP BY c.customer_id, c.name, m.membership_id
    ORDER BY c.customer_id ASC
";
$members_result = $conn->query($members_query);
$members = [];
if ($members_result->num_rows > 0) {
    while ($row = $members_result->fetch_assoc()) {
        $members[] = $row;
    }
}

// Handle points awarding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['award_points'])) {
    $sales_id = $conn->real_escape_string($_POST['sales_id']);
    $membership_id = $conn->real_escape_string($_POST['membership_id']);
    $createdbyid = $_SESSION['admin_id'];

    // Validate inputs
    if (empty($sales_id) || empty($membership_id)) {
        $_SESSION['error'] = "Sales ID and Membership ID are required!";
    } else {
        // Call the AwardPoints stored procedure
        $stmt = $conn->prepare("CALL AwardPoints(?, ?, ?)");
        $stmt->bind_param("iii", $sales_id, $membership_id, $createdbyid);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Points awarded successfully!";
        } else {
            $_SESSION['error'] = "Error awarding points: " . $conn->error;
        }
        $stmt->close();
    }
    header("Location: point.php");
    exit();
}
?>
<style>
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

.points-table {
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

.btn-award {
    background: #34502b;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.btn-award:hover {
    background: white;
    color: #34502b;
    border: 1px solid #34502b;
    transform: translateY(-1px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
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
    background-color: #e6c200;
}

tr:hover {
    background: #f1f1f1;
}

.floating-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    transition: opacity 0.5s;
}

@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }

    .main-content {
        margin-left: 200px;
        width: calc(100% - 200px);
    }

    .search-profile {
        flex-direction: column;
        text-align: center;
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
    }
}
</style>
</head>

<body>
    <div class="main-content">
        <header>
            <h1>Member Points Management</h1>
            <div class="search-profile">
                <?php include __DIR__ . '/searchbar.php'; ?>
                <?php include __DIR__ . '/profile.php'; ?>
            </div>
        </header>
        <hr>

        <!-- Points Award Form -->
        <div class="card p-4 mb-4">
            <h4>Award Points</h4>
            <form method="POST" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="sales_id" class="form-label">Sales ID</label>
                    <input type="number" class="form-control" id="sales_id" name="sales_id" required>
                </div>
                <div class="col-md-4">
                    <label for="membership_id" class="form-label">Membership ID</label>
                    <select class="form-control" id="membership_id" name="membership_id" required>
                        <option value="">Select Membership</option>
                        <?php foreach ($members as $member) : ?>
                        <option value="<?= $member['membership_id'] ?>">
                            <?= htmlspecialchars($member['name']) ?> (Membership ID: <?= $member['membership_id'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" name="award_points" class="btn-award">Award Points</button>
                </div>
            </form>
        </div>

        <!-- Points Table -->
        <div class="points-table">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Membership ID</th>
                            <th>Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($members) > 0) : ?>
                        <?php foreach ($members as $member) : ?>
                        <tr>
                            <td><?= htmlspecialchars($member['customer_id']) ?></td>
                            <td><?= htmlspecialchars($member['name']) ?></td>
                            <td><?= htmlspecialchars($member['membership_id'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($member['total_points']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                                No members found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
    </script>

    <?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert d-flex align-items-center" role="alert">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show floating-alert d-flex align-items-center" role="alert">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif ?>