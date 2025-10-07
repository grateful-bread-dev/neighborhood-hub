<?php
require_once __DIR__ . '/config.php';
require_login();

$err = [];
$ok = null;

// load current user
$stmt = $pdo->prepare("SELECT email, display_name, city, state, country FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) {
  header('Location: logout.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $display = trim($_POST['display_name'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $state = trim($_POST['state'] ?? '');
  $country = trim($_POST['country'] ?? '');

  if ($display === '' || mb_strlen($display) < 3) $err[] = 'Display name must be at least 3 characters.';
  if ($city === '') $err[] = 'City is required.';
  if ($state === '') $err[] = 'State is required.';
  if ($country === '') $err[] = 'Country is required.';

  if (!$err) {
    $upd = $pdo->prepare("UPDATE users SET display_name=?, city=?, state=?, country=? WHERE id=?");
    $upd->execute([$display, $city, $state, $country, $_SESSION['user_id']]);

    // refresh session fields
    $_SESSION['display_name'] = $display;
    $_SESSION['user_city'] = $city;
    $_SESSION['user_state'] = $state;
    $_SESSION['user_country'] = $country;

    $ok = 'Profile updated.';
    $user['display_name'] = $display;
    $user['city'] = $city; $user['state'] = $state; $user['country'] = $country;
  }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile - Neighborhood Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <header class="app-header">
      <h1 class="app-title">Your Profile</h1>
      <nav class="actions">
        <a class="pill" href="feed.php">Feed</a>
        <a class="pill" href="events.php">Events</a>
        <a class="pill" href="inbox.php">Inbox</a>
        <a class="pill" href="bookmarks.php">Bookmarks</a>
        <a class="btn" href="logout.php">Logout</a>
      </nav>
    </header>

    <?php if ($ok): ?>
      <div class="notice success"><?= h($ok) ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="notice error">
        <?php foreach ($err as $e): ?><p><?= h($e) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section class="panel">
      <h2>Account</h2>
      <p class="text-muted">Email: <strong><?= h($user['email']) ?></strong></p>
    </section>

    <section class="panel">
      <h2>Display & Location</h2>
      <form method="post" class="auth-form">
        <label>Display Name
          <input name="display_name" value="<?= h($user['display_name']) ?>" required>
        </label>

        <label>City
          <input name="city" value="<?= h($user['city']) ?>" required>
        </label>

        <label>State
          <input name="state" value="<?= h($user['state']) ?>" required>
        </label>

        <label>Country
          <input name="country" value="<?= h($user['country']) ?>" required>
        </label>

        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </section>
  </div>
</body>
</html>
