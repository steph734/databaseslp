<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/signup.css">
    <script>
        function handleSignUp(event) {
            event.preventDefault(); // Prevent default form submission

            // Show alert message
            alert("Sign up successfully! Try signing in...");

            // Redirect to the admin login page
            window.location.href = "admin-login.html";
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="signup-box">
            <h2 class="signup-title">SIGN UP</h2>

            <div class="input-group">
                <input type="text" placeholder="Full Name">
            </div>
            <div class="input-group">
                <input type="email" placeholder="Email">
            </div>
            <div class="input-group">
                <input type="text" placeholder="Phone Number">
            </div>
            <div class="input-group">
                <input type="text" placeholder="Username">
            </div>
            <div class="input-group">
                <input type="password" placeholder="Create Password">
            </div>
            <div class="input-group">
                <input type="password" placeholder="Confirm Password">
            </div>
            <div class="input-group">
                <select>
                    <option value="">Role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <button>Sign Up</button>
        </div>

        <div class="signup-banner">
            <h1>RIVERVIEW OF BUCANA SLP ASSOCIATION</h1>
            <p>Community Enterprise</p>
        </div>
    </div>
</body>
</html>