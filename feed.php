<?php
require_once __DIR__.'/config.php';

// defaults for user regardless of login
$isLogged = logged_in();
$defCity = $_SESSION['user_city'] ?? '';
$defState = $_SESSION['user_state'] ?? '';
$defCountry = $_SESSION['user_country'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Neighborhood Hub - Feed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <header class="app-header">
      <h1 class="app-title">Neighborhood Hub</h1>
      <nav class="actions" aria-label="Primary">
        <?php if (logged_in()): ?>
          <a class="pill" href="newpost.php">+ New Post</a>
          <a class="btn" href="events.php">Events</a>
          <a class="pill" href="inbox.php">Inbox</a>
          <a class="pill" href="bookmarks.php">Bookmarks</a>
          <a class="pill" href="profile.php">Profile</a>
          <a class="btn" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="btn" href="index.php">Sign In</a>
          <a class="pill" href="register.php">Register</a>
        <?php endif; ?>    
      </nav>
    </header>

    <section class="controls" aria-label="Filters">
      <label for="type">Type</label>
      <select id="type" name="type">
        <option value="">Any</option>
        <option value="request">Request</option>
        <option value="offer">Offer</option>
        <option value="lost_found">Lost & Found</option>
        <option value="alert">Alert</option>
        <option value="event">Event</option>
        <option value="other">Other</option>
      </select>

      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="">Any</option>
        <option>Tools</option>
        <option>Pets</option>
        <option>Yard Sale</option>
        <option>Other</option>
      </select>

      <input id="q" name="q" placeholder="Search title or text…" aria-label="Search">

      <!-- location filters -->
      <label for="city">City</label>
      <input id="city" name="city" value="<?= htmlspecialchars($defCity) ?>">

      <label for="state">State</label>
      <input id="state" name="state" value="<?= htmlspecialchars($defState) ?>">

      <label for="country">Country</label>
      <input id="country" name="country" value="<?= htmlspecialchars($defCountry) ?>">

      <button id="apply" type="button" class="btn btn-primary">Apply</button>
    </section>

    <section id="grid" class="grid" aria-live="polite" aria-busy="false"></section>
  </div>

  <script src="js/utils.js" defer></script>
  <script src="js/feed.js" defer></script>

</body>
</html>

