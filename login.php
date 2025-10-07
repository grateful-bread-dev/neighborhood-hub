<?php
require_once __DIR__ . '/config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // fetch user by email
    $stmt = $pdo->prepare('SELECT id, display_name, password_hash, city, state, country FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {
        // set session and go to the app
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['user_city'] = $user['city'];
        $_SESSION['user_state'] = $user['state'];
        $_SESSION['user_country'] = $user['country'];

        header('Location: feed.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
} elseif (logged_in()) {
    // user already logged in
    header('Location: feed.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - Neighborhood Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Neighborhood Hub</h1>
    <p class="tagline">Reconnect with your neighbors and community</p>

    <?php if ($error): ?>
      <div class="error-box"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <h2>Login</h2>
      <label>Email
        <input name="email" type="email" required>
      </label>
      <label>Password
        <input name="password" type="password" required>
      </label>
      <button type="submit">Sign in</button>
    </form>

    <p class="register-link">
      New here? <a href="register.php">Create an account</a>
    </p>
    <p style="margin-top:.5rem;"><a href="index.php">Back to start</a></p>
  </div>
</body>
</html>
