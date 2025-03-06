<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../statics/css/bootstrap.min.css">

    <style>
        .button-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .custom-login-btn {
            background-color: #3a5d39;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
        }

        .custom-login-btn i {
            color: white;
            /* Default icon color */
            transition: color 0.3s ease-in-out;
        }

        .custom-login-btn:hover {
            background-color: rgb(253, 255, 253);
            color: #3a5d39;
            border: 2px solid #3a5d39;
        }

        .custom-login-btn:hover i {
            color: #3a5d39;
        }
    </style>
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
            <div class="button-container">
                <button type="submit" class="custom-login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>
            </div>


        </form>
        <div class="mt-3">
            <p>Don't have an account? <a href="signup.php">Sign up here.</a></p>
        </div>
    </div>

</body>


</html>