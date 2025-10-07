<?php
session_start();

// if already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: feed.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Neighborhood Hub - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="app-header">
            <h1 class="app-title">Neighborhood Hub</h1>
            <nav class="actions">
                <a class="pill" href="feed.php">Feed</a>
            </nav>
        </header>        
        <p class="tagline">Reconnect with your neighbors and community</p>

        <!-- login form -->
        <form action="login.php" method="post" class="auth-form">
            <h2>Login</h2>

            <label>Email
                <input type="email" name="email" required autocomplete="email">
            </label>

            <label>Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <!-- register redirect -->
        <p class="register-link">New here? <a href="register.php">Create an account</a></p>
    </div>
</body>
</html>
