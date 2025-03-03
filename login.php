<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/login.css"> 

</head>
<body>
    <div class="login-container">
        <h2>ADMIN LOGIN</h2>
        <form onsubmit="handleLogin(event)">
            <div class="input-group">
                <span class="icon">👤</span>
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <span class="icon">🔒</span>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="options">
                <input type="checkbox" id="remember-me">
                <label for="remember-me">Remember me</label>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.html">Sign up here.</a></p>
    </div>
</body>

<script>
        function handleLogin(event) {
            event.preventDefault(); // Prevent default form submission

            // Get input values
            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;

            // Simulated authentication (Replace this with real authentication logic)
            if (username === "admin" && password === "password123") {
                alert("Login successful! Redirecting...");
                window.location.href = "dashboard.html"; // Redirect to dashboard
            } else {
                alert("Invalid username or password. Please try again.");
            }
        }
    </script>
</html>