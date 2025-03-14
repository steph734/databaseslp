<?php
include '../../database/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../resource/login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin details
$query = "SELECT admin_id, first_name, middle_name, last_name, username, email, phonenumber, role 
          FROM admin 
          WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header("Location: ../resource/layout/web-layout?page=profileadmin");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            padding: 15px;
        }

        .profile-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-label {
            color: #34502b;
            font-weight: bold;
        }

        .form-control {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 8px;
        }

        .btn-primary {
            background-color: #34502b;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #4a6f3e;
        }

        .btn-danger {
            background-color: rgb(255, 255, 255);
            border: 1px solid #c82333;
            color: #c82333;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .floating-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            width: auto !important;
            padding-right: 2.5rem !important;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-link.toggle-message {
            padding: 0;
            font-size: 0.9em;
            color: #fff;
            text-decoration: underline;
        }

        .btn-link.toggle-message:hover {
            color: #ddd;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 500px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .profile-container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="main-content">
        <header>
            <h1>Admin Profile</h1>
        </header>
        <hr>
        <div class="profile-container">
            <h3 style="color: #34502b;">Manage Your Profile</h3>
            <form action="../../handlers/update_profile_handler.php" method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="mb-3">
                    <label class="form-label">First Name:</label>
                    <input type="text" name="first_name" class="form-control"
                        value="<?= htmlspecialchars($admin['first_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Middle Name (Optional):</label>
                    <input type="text" name="middle_name" class="form-control"
                        value="<?= htmlspecialchars($admin['middle_name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name:</label>
                    <input type="text" name="last_name" class="form-control"
                        value="<?= htmlspecialchars($admin['last_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username:</label>
                    <input type="text" name="username" class="form-control"
                        value="<?= htmlspecialchars($admin['username']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number:</label>
                    <input type="text" name="phonenumber" class="form-control"
                        value="<?= htmlspecialchars($admin['phonenumber']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($admin['role']) ?>" readonly>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                        data-bs-target="#deactivateModal">Deactivate Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Deactivate Account Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivateModalLabel" style="color: #34502b;">Confirm Deactivation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure do you want to deactivate your account? This action will make your account inactive.
                </div>
                <div class="modal-footer">
                    <form action="../../handlers/deactivate_account_handler.php" method="POST">
                        <input type="hidden" name="deactivate" value="1">
                        <button type="submit" class="btn btn-danger">Yes, Deactivate</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success alert-dismissible fade show floating-alert text-center" role="alert">
            <div class="alert-content">
                <?php
                $full_message = $_SESSION['success'];
                $short_message = strlen($full_message) > 100 ? substr($full_message, 0, 100) . '...' : $full_message;
                ?>
                <span class="alert-short"><?= htmlspecialchars($short_message) ?></span>
                <span class="alert-full d-none"><?= htmlspecialchars($full_message) ?></span>
                <?php if (strlen($full_message) > 100) : ?>
                    <button type="button" class="btn btn-link btn-sm toggle-message">Show More</button>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Notification Handling
        setTimeout(() => {
            const alerts = document.querySelectorAll(".floating-alert");
            alerts.forEach(alert => {
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            });
        }, 20000);

        document.querySelectorAll('.toggle-message').forEach(button => {
            button.addEventListener('click', () => {
                const alert = button.closest('.alert');
                const short = alert.querySelector('.alert-short');
                const full = alert.querySelector('.alert-full');
                if (short.classList.contains('d-none')) {
                    short.classList.remove('d-none');
                    full.classList.add('d-none');
                    button.textContent = 'Show More';
                } else {
                    short.classList.add('d-none');
                    full.classList.remove('d-none');
                    button.textContent = 'Show Less';
                }
            });
        });
    </script>
</body>

</html>