<?php
require_once __DIR__.'/config.php';
require_login();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>New Post - Neighborhood Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Create a Post</h1>

    <form id="postForm" class="newpost-form">
      <label>Type
        <select name="type" required>
          <option value="">Select…</option>
          <option value="request">Request</option>
          <option value="offer">Offer</option>
          <option value="lost_found">Lost & Found</option>
          <option value="alert">Alert</option>
          <option value="other">Other</option>
        </select>
      </label>

      <label>Category
        <select name="category" required>
          <option value="">Select…</option>
          <option>Tools</option>
          <option>Pets</option>
          <option>Yard Sale</option>
          <option>Other</option>
        </select>
      </label>

      <label>Title
        <input name="title" maxlength="140" required>
      </label>

      <label>Description
        <textarea name="body" required></textarea>
      </label>

      <button type="submit" class="btn btn-primary">Submit Post</button>
      <p id="msg"></p>
    </form>

    <p><a href="feed.php">Back to feed</a></p>
  </div>

  <script src="js/utils.js" defer></script>
  <script src="js/newpost.js" defer></script>
  
</body>
</html>
