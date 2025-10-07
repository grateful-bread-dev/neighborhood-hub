<?php
require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "<!doctype html><meta charset='utf-8'><p>Missing or invalid post id.</p><p><a href='feed.php'>← Back to feed</a></p>";
  exit;
}
$isGuest = !logged_in();
$me = (int)($_SESSION['user_id'] ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Post - Neighborhood Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container detail" id="post-root"
     data-post-id="<?= $id ?>"
     data-guest="<?= $isGuest ? '1' : '0' ?>"
     data-me="<?= $me ?>">

    <p><a href="feed.php">Back to feed</a></p>

    <div id="meta" class="meta"></div>
    <h1 id="title">Loading…</h1>
    <p id="body"></p>

    <div class="actions">
      <?php if ($isGuest): ?>
        <a class="btn btn-primary" href="index.php">Sign in to bookmark or message</a>
      <?php else: ?>
        <button id="bookmarkBtn" class="btn btn-primary" hidden>Toggle Bookmark</button>
        <button id="pmBtn" class="btn btn-primary" hidden>Message Poster (private)</button>
      <?php endif; ?>
    </div>

    <hr>

    <section class="panel">
      <h2>Public Replies</h2>

      <?php if ($isGuest): ?>
        <p class="text-muted">Sign in to post a public reply.</p>
      <?php else: ?>
        <form id="commentForm" class="newpost-form" autocomplete="off">
          <label class="sr-only" for="cbody">Write a public reply</label>
          <textarea id="cbody" placeholder="Write a public reply…" required></textarea>
          <button type="submit">Post Public Reply</button>
          <small id="cmsg" class="text-muted"></small>
        </form>
      <?php endif; ?>

      <div id="comments" class="comments" aria-live="polite" aria-busy="false"></div>
    </section>
  </div>

  <script src="js/utils.js"></script>
  <script src="js/post.js"></script>
  
</body>
</html>


