<?php
require_once __DIR__ . '/config.php';
require_login();

// prefill location from session
$defCity = $_SESSION['user_city'] ?? '';
$defState = $_SESSION['user_state'] ?? '';
$defCountry = $_SESSION['user_country'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Neighborhood Hub — Events</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <header class="app-header">
    <h1 class="app-title">Community Events</h1>
    <nav class="actions">
      <a class="pill" href="feed.php">Feed</a>
      <a class="pill" href="inbox.php">Inbox</a>
      <a class="pill" href="bookmarks.php">Bookmarks</a>
      <a class="pill" href="profile.php">Profile</a>
      <a class="btn" href="logout.php">Logout</a>
    </nav>
  </header>

  <!-- create event -->
  <section class="panel">
    <h2>Create an Event</h2>
    <form id="eventForm" class="newpost-form">
      <input type="hidden" name="type" value="event">
      <input type="hidden" name="category" value="Events">

      <label>Title
        <input name="title" maxlength="140" required placeholder="Tell us what's happening">
      </label>

      <label>Description
        <textarea name="body" required placeholder="Add the date/time, place, and details."></textarea>
      </label>

      <button type="submit" class="btn btn-primary">Publish Event</button>
      <small id="ev-msg" class="text-muted"></small>
    </form>
  </section>

  <!-- filters for viewing events -->
  <section class="controls" aria-label="Filters">
    <input id="q" name="q" placeholder="Search events…" aria-label="Search">
    <label for="city">City</label>
    <input id="city" value="<?= htmlspecialchars($defCity) ?>">
    <label for="state">State</label>
    <input id="state" value="<?= htmlspecialchars($defState) ?>">
    <label for="country">Country</label>
    <input id="country" value="<?= htmlspecialchars($defCountry) ?>">
    <button id="apply" type="button">Apply</button>
  </section>

  <!-- events list -->
  <section id="grid" class="grid" aria-live="polite" aria-busy="false"></section>
</div>

<script src="js/utils.js" defer></script>  
<script src="js/events.js" defer></script>

</body>
</html>
