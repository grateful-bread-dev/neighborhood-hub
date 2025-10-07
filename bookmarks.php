<?php
require_once __DIR__ . '/config.php';
require_login();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Bookmarks - Neighborhood Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <header class="app-header">
      <h1 class="app-title">Bookmarks</h1>
      <nav class="actions" aria-label="Primary">
        <a class="pill" href="feed.php">Feed</a>
        <a class="pill" href="events.php">Events</a>
        <a class="pill" href="inbox.php">Inbox</a>
        <a class="pill" href="profile.php">Profile</a>
        <a class="btn" href="logout.php">Logout</a>
      </nav>
    </header>

    <section id="list" class="grid" aria-live="polite"></section>
  </div>

  <script src="js/utils.js" defer></script>
  <script src="js/bookmarks.js" defer></script>
  
</body>
</html>
