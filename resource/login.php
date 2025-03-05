<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../design/css/login.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <h2>ADMIN LOGIN</h2>
        <form action="../handlers/login_handler.php" method="POST">
            <div class="input-group">
                <span class="icon"><i class="fa-solid fa-user"></i></span>
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <span class="icon"><i class="fa-solid fa-lock"></i></span>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="options">
                <input type="checkbox" id="remember-me">
                <label for="remember-me">Remember me</label>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit"><i class="fa-solid fa-right-to-bracket" style="color: #ffffff; margin-right:5px;"></i>Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up here.</a></p>
    </div>
</body>


</html>