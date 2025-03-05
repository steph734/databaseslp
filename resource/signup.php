<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../statics/css/bootstrap.min.css">
    <link rel="stylesheet" href="../design/css/signup.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->

</head>

<body>
    <div class="container-custom">
        <div class="row align-items-center">
            <div class="col-md-6 signup-box">
                <h2 class="signup-title">SIGN UP</h2>
                <form action="../handlers/signin_handler.php" method="POST" onsubmit="return validateForm()">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="first_name" id="first_name"
                                placeholder="First Name" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="middle_name" id="middle_name"
                                placeholder="Middle Name (Optional)">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="last_name" id="last_name"
                                placeholder="Last Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email"
                                required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone Number"
                                required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="username" id="username" placeholder="Username"
                                required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" id="password"
                                placeholder="Create Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="confirm_password"
                                placeholder="Confirm Password" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <select class="form-select" name="role" id="role" required>
                                <option value="" hidden>Role</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <!-- Empty div to maintain two-column layout for the last row -->
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <div class="mt-1">
                            <button type="submit" class="btn btn-signup btn-success w-100 custom-signup-btn">
                                Sign Up
                            </button>

                            <style>

                            </style>

                        </div>
                        <div class="mt-1 text-center">
                            <button type="button" class="btn btn-outline-success custom-btn"
                                onclick="window.location.href='login.php'">
                                <i class="fa-solid fa-right-from-bracket"></i> Back
                            </button>

                        </div>
                    </div>
                </form>
            </div>

            <!-- Signup Banner (Right Column) -->
            <div class="col-md-6 signup-banner">
                <h1>RIVERVIEW OF BUCANA SLP ASSOCIATION</h1>
                <p>Community Enterprise</p>
            </div>
        </div>
    </div>

</body>

</html>