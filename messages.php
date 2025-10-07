<?php
require_once __DIR__ . '/config.php';
require_login();

$threadId = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
$postId = isset($_GET['post_id'])   ? (int)$_GET['post_id']   : 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Messages - Neighborhood Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container thread-container" id="thread-root"
       data-thread-id="<?= (int)$threadId ?>"
       data-post-id="<?= (int)$postId ?>"
       data-me="<?= (int)($_SESSION['user_id'] ?? 0) ?>">
    <div class="thread-head">
      <h1 class="app-title" style="margin:0;">Messages</h1>
      <a class="pill" href="feed.php">Back to feed</a>
    </div>

    <div id="meta" class="thread-meta"></div>

    <div id="box" class="thread-box" aria-live="polite" aria-busy="true"></div>

    <div class="send-row">
      <textarea id="text" placeholder="Write a message…" aria-label="Message"></textarea>
      <button id="send" class="btn btn-primary" type="button">Send</button>
    </div>
  </div>

  <script src="js/utils.js" defer></script>
  <script src="js/messages.js" defer></script>
</body>
</html>

