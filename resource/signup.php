<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../statics/css/bootstrap.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <style>
            body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(to bottom, #3e5e3e, #d4d8c0);
            display: flex; /* Use flexbox to center content */
            align-items: center; /* Vertically center */
            justify-content: center; /* Horizontally center */
            overflow: hidden; /* Remove scrollbar and prevent scrolling */
            position: fixed; /* Fix the body in place */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .container-custom {
            background: linear-gradient(to bottom, #153b13, #a5b88e);
            border-radius: 10px;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            position: fixed; /* Fix the container in place */
            overflow: hidden; /* Remove scrollbar from container */
            width: 90%;
            max-width: 1200px;
            height: 90vh; /* Limit height to 90% of viewport */
            top: 5%; /* Center vertically with margin */
            left: 5%; /* Center horizontally with margin */
            right: 5%;
            z-index: 1000; /* Ensure it stays on top */
        }

        .signup-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden; /* Remove scrollbar from signup-box */
            max-height: 80vh; /* Limit height to prevent overflow */
        }

        .signup-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .signup-banner {
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%; /* Ensure banner takes full height */
        }

        .signup-banner h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .signup-banner p {
            font-size: 1.25rem;
            margin-top: 0.5rem;
        }

        /* Two-column layout for form fields */
        .form-row {
            display: flex;
            gap: 1rem; /* Space between columns */
            margin-bottom: 1rem; /* Space between rows */
        }

        .form-group {
            flex: 1; /* Equal width for each column */
            min-width: 0; /* Prevent overflow */
        }

        .form-group input,
        .form-group select {
            width: 100%; /* Full width within the column */
        }

        /* Ensure buttons and other elements fit within the layout */
        .btn-signup,
        .btn-outline-success {
            width: 100%; /* Full width for buttons */
        }

        /* Adjust for smaller screens */
        @media (max-width: 767.98px) {
            .signup-banner {
                margin-top: 2rem;
            }
            .signup-banner h1 {
                font-size: 1.75rem;
            }
            .signup-banner p {
                font-size: 1rem;
            }
            .container-custom {
                width: 85%;
                left: 7.5%;
                right: 7.5%;
            }
            .form-row {
                flex-direction: column; /* Stack fields vertically on small screens */
                gap: 0.5rem; /* Reduce gap on mobile */
            }
            .form-group {
                width: 100%; /* Full width on mobile */
            }
        }

        .container-custom::before,
        .container-custom::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .container-custom::before {
            top: -30px;
            left: -30px;
        }

        .container-custom::after {
            bottom: -30px;
            right: -30px;
        }
    </style>
</head>
<body>
    <div class="container-custom">
        <div class="row align-items-center">
            <!-- Signup Form (Left Column) -->
            <div class="col-md-6 signup-box">
                <h2 class="signup-title">SIGN UP</h2>
                <form action="../handlers/signin_handler.php" method="POST" onsubmit="return validateForm()">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name (Optional)">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone Number" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Create Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="confirm_password" placeholder="Confirm Password" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <select class="form-select" name="role" id="role" required>
                                <option value="">Role</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <!-- Empty div to maintain two-column layout for the last row -->
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-signup btn-success w-100">Sign Up</button>
                    </div>
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-outline-success" onclick="window.location.href='login.php'">
                            <i class="fa-solid fa-right-from-bracket" style=" margin-right: 5px; "></i> Back
                        </button>
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
    <!-- Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Form Validation Script -->
    <script>
        function validateForm() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match. Please try again.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>