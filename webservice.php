<?php
require_once __DIR__ . '/config.php';

function respond($data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

$rawJson = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)) {
  $rawJson = file_get_contents('php://input');
  $json = json_decode($rawJson, true);
  if (is_array($json)) {
    foreach ($json as $k => $v) {
      if (!isset($_POST[$k])) $_POST[$k] = $v;
    }
  }
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) respond(['error' => 'Missing action'], 400);

try {
  switch ($action) {

    // GET
    case 'list_posts': {
      if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['error'=>'Method Not Allowed'], 405);

      $type = $_GET['type'] ?? null;
      $category = $_GET['category'] ?? null;
      $q = trim($_GET['q'] ?? '');
      $limit = (int)($_GET['limit'] ?? 50);
      $limit = max(1, min($limit, 100));
      $city = trim($_GET['city'] ?? '');
      $state = trim($_GET['state'] ?? '');
      $country = trim($_GET['country'] ?? '');


      $sql = "SELECT p.id, p.type, p.title, p.body, p.category,
                     p.city, p.state, p.country, 
                     p.created_at, u.display_name AS author
              FROM posts p
              JOIN users u ON u.id = p.user_id
              WHERE 1=1";
      $params = [];

      if ($type) { $sql .= " AND p.type = ?"; $params[] = $type; }
      if ($category) { $sql .= " AND p.category = ?"; $params[] = $category; }
      if ($q !== '') { $sql .= " AND (p.title LIKE ? OR p.body LIKE ?)";
                       $like = "%$q%"; $params[] = $like; $params[] = $like; }

      if ($city !== '') { $sql .= " AND p.city = ?"; $params[] = $city; }
      if ($state !== '') { $sql .= " AND p.state = ?"; $params[] = $state; }
      if ($country !== '') { $sql .= " AND p.country = ?"; $params[] = $country; }

      $sql .= " ORDER BY p.created_at DESC LIMIT $limit";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      respond(['posts' => $stmt->fetchAll()]);
    }

    // GET
    case 'get_post': {
      if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['error'=>'Method Not Allowed'], 405);
      $id = (int)($_GET['id'] ?? 0);
      if ($id <= 0) respond(['error'=>'Missing id'], 400);

      $stmt = $pdo->prepare(
        "SELECT p.id, p.type, p.title, p.body, p.category,
                     p.city, p.state, p.country, 
                     p.created_at, u.display_name AS author
         FROM posts p
         JOIN users u ON u.id = p.user_id
         WHERE p.id = ?"
      );
      $stmt->execute([$id]);
      $post = $stmt->fetch();
      if (!$post) respond(['error'=>'Not found'], 404);

      // include bookmark flag if logged in
      if (logged_in()) {
        $chk = $pdo->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND post_id = ?");
        $chk->execute([$_SESSION['user_id'], $id]);
        $post['bookmarked'] = (bool)$chk->fetchColumn();
      }

      respond(['post' => $post]);
    }

    // POST create_post
    case 'create_post': {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      // accept JSON or form data
      $input = $_POST;
      if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
      }

      $type = $input['type'] ?? '';
      $title = trim($input['title'] ?? '');
      $body = trim($input['body'] ?? '');
      $category = trim($input['category'] ?? '');
      $city = trim($input['city'] ?? ($_SESSION['user_city']    ?? ''));
      $state = trim($input['state'] ?? ($_SESSION['user_state']   ?? ''));
      $country = trim($input['country'] ?? ($_SESSION['user_country'] ?? ''));
      if ($city === '' || $state === '' || $country === '') {
        $u = $pdo->prepare("SELECT city, state, country FROM users WHERE id=?");
        $u->execute([$_SESSION['user_id']]);
        if ($row = $u->fetch()) {
            if ($city === '') $city = $row['city'] ?? '';
            if ($state === '') $state = $row['state'] ?? '';
            if ($country === '') $country = $row['country'] ?? '';
        }
      }

      if (!$type || !$title || !$body || !$category || !$city || !$state || !$country) {
        respond(['error'=>'Missing required fields'], 422);
      }

      $stmt = $pdo->prepare(
        "INSERT INTO posts (user_id, type, title, body, category, city, state, country)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$_SESSION['user_id'], $type, $title, $body, $category, $city, $state, $country]);

      respond(['ok'=>true, 'post_id'=>(int)$pdo->lastInsertId()], 201);
    }

    // POST toggle_bookmark
    case 'toggle_bookmark': {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $input = $_POST;
      if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
      }
      $postId = (int)($input['post_id'] ?? 0);
      if ($postId <= 0) respond(['error'=>'Missing post_id'], 400);

      // check if a bookmark exists
      $chk = $pdo->prepare("SELECT 1 FROM bookmarks WHERE user_id=? AND post_id=?");
      $chk->execute([$_SESSION['user_id'], $postId]);

      if ($chk->fetchColumn()) {
        $del = $pdo->prepare("DELETE FROM bookmarks WHERE user_id=? AND post_id=?");
        $del->execute([$_SESSION['user_id'], $postId]);
        respond(['ok'=>true, 'bookmarked'=>false]);
      } else {
        $ins = $pdo->prepare("INSERT INTO bookmarks (user_id, post_id, created_at)
                              VALUES (?, ?, NOW())");
        $ins->execute([$_SESSION['user_id'], $postId]);
        respond(['ok'=>true, 'bookmarked'=>true]);
      }
    }

    // GET my_bookmarks
    case 'my_bookmarks': {
      if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $stmt = $pdo->prepare(
        "SELECT p.id, p.type, p.title, p.body, p.category,
                     p.city, p.state, p.country, 
                     p.created_at, u.display_name
         FROM bookmarks b
         JOIN posts p ON p.id = b.post_id
         JOIN users u ON u.id = p.user_id
         WHERE b.user_id = ?
         ORDER BY b.created_at DESC"
      );
      $stmt->execute([$_SESSION['user_id']]);
      respond(['bookmarks' => $stmt->fetchAll()]);
    }

    // GET my_threads
    case 'my_threads': {
      if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $stmt = $pdo->prepare(
        "SELECT t.id AS thread_id, t.post_id, t.created_at,
                p.title AS post_title,
                u.display_name AS other_user
        FROM threads t
        JOIN posts p ON p.id = t.post_id
        JOIN users u ON u.id = CASE
            WHEN t.buyer_id = ? THEN t.seller_id
            ELSE t.buyer_id
        END
        WHERE t.buyer_id = ? OR t.seller_id = ?
        ORDER BY t.created_at DESC"
      );
      $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
      respond(['threads' => $stmt->fetchAll()]);
    }


    // POST open_thread
    case 'open_thread': {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $input = $_POST;
      if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
      }
      $postId = (int)($input['post_id'] ?? 0);
      if ($postId <= 0) respond(['error'=>'Missing post_id'], 400);

      // look up post and owner
      $p = $pdo->prepare("SELECT id, user_id FROM posts WHERE id=?");
      $p->execute([$postId]);
      $post = $p->fetch();
      if (!$post) respond(['error'=>'Post not found'], 404);

      $me = (int)$_SESSION['user_id'];
      $owner = (int)$post['user_id'];

      // try to find existing thread
      $find = $pdo->prepare("SELECT id FROM threads
                             WHERE post_id=? AND ((buyer_id=? AND seller_id=?) OR (buyer_id=? AND seller_id=?))
                             ORDER BY id LIMIT 1");
      $find->execute([$postId, $me, $owner, $owner, $me]);
      $threadId = (int)($find->fetchColumn() ?: 0);

      if (!$threadId) {
        $ins = $pdo->prepare("INSERT INTO threads (post_id, buyer_id, seller_id, created_at)
                              VALUES (?,?,?,NOW())");
        $ins->execute([$postId, $me, $owner]);
        $threadId = (int)$pdo->lastInsertId();
      }

      respond(['ok'=>true, 'thread_id'=>$threadId]);
    }

    // GET list_comments
    case 'list_comments': {
      if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['error'=>'Method Not Allowed'], 405);
      $postId = (int)($_GET['post_id'] ?? 0);
      if ($postId <= 0) respond(['error'=>'Missing post_id'], 400);

      $stmt = $pdo->prepare(
        "SELECT c.id, c.post_id, c.user_id, u.display_name AS author, c.body, c.created_at
        FROM comments c
        JOIN users u ON u.id = c.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC"
      );
      $stmt->execute([$postId]);
      respond(['comments' => $stmt->fetchAll()]);
    }

    // POST add_comment, post_id
    case 'add_comment': {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $input = $_POST;
      if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
      }

      $postId = (int)($input['post_id'] ?? 0);
      $body = trim($input['body'] ?? '');
      if ($postId <= 0 || $body === '') respond(['error'=>'Missing fields'], 422);

      // verify post exists
      $chk = $pdo->prepare("SELECT 1 FROM posts WHERE id=?");
      $chk->execute([$postId]);
      if (!$chk->fetchColumn()) respond(['error'=>'Post not found'], 404);

      $ins = $pdo->prepare("INSERT INTO comments (post_id, user_id, body) VALUES (?,?,?)");
      $ins->execute([$postId, $_SESSION['user_id'], $body]);

      respond(['ok'=>true, 'comment_id'=>(int)$pdo->lastInsertId()], 201);
    }

    // GET list_messages
    case 'list_messages': {
      if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $threadId = (int)($_GET['thread_id'] ?? 0);
      if ($threadId <= 0) respond(['error'=>'Missing thread_id'], 400);

      // membership check
      $chk = $pdo->prepare("SELECT 1 FROM threads WHERE id=? AND (buyer_id=? OR seller_id=?)");
      $chk->execute([$threadId, $_SESSION['user_id'], $_SESSION['user_id']]);
      if (!$chk->fetchColumn()) respond(['error'=>'Forbidden'], 403);

      $stmt = $pdo->prepare(
        "SELECT m.id, m.sender_id, u.display_name AS sender, m.body, m.created_at
         FROM messages m
         JOIN users u ON u.id = m.sender_id
         WHERE m.thread_id = ?
         ORDER BY m.created_at ASC"
      );
      $stmt->execute([$threadId]);
      respond(['messages'=>$stmt->fetchAll()]);
    }

    // POST send_message
    case 'send_message': {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method Not Allowed'], 405);
      if (!logged_in()) respond(['error'=>'Unauthorized'], 401);

      $input = $_POST;
      if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
      }
      $threadId = (int)($input['thread_id'] ?? 0);
      $body = trim($input['body'] ?? '');
      if ($threadId <= 0 || $body === '') respond(['error'=>'Missing fields'], 422);

      // membership check
      $chk = $pdo->prepare("SELECT 1 FROM threads WHERE id=? AND (buyer_id=? OR seller_id=?)");
      $chk->execute([$threadId, $_SESSION['user_id'], $_SESSION['user_id']]);
      if (!$chk->fetchColumn()) respond(['error'=>'Forbidden'], 403);

      $ins = $pdo->prepare("INSERT INTO messages (thread_id, sender_id, body, created_at)
                            VALUES (?,?,?,NOW())");
      $ins->execute([$threadId, $_SESSION['user_id'], $body]);

      respond(['ok'=>true, 'message_id'=>(int)$pdo->lastInsertId()]);
    }

    default:
      respond(['error' => 'Unknown action'], 400);
  }

} catch (Throwable $e) {
  respond(['error' => 'Server error', 'detail' => $e->getMessage()], 500);
}

