// js/post.js
(function () {
  const root = document.getElementById('post-root');
  if (!root) return;

  const POST_ID = parseInt(root.dataset.postId || '0', 10);
  const IS_GUEST = (root.dataset.guest || '0') === '1';
  const ME_ID = parseInt(root.dataset.me || '0', 10) || 0;

  if (!POST_ID) return;

  const titleEl = document.getElementById('title');
  const bodyEl = document.getElementById('body');
  const metaEl = document.getElementById('meta');
  const bmBtn = document.getElementById('bookmarkBtn');
  const pmBtn = document.getElementById('pmBtn');
  const commentsEl = document.getElementById('comments');
  const cForm = document.getElementById('commentForm');
  const cBody = document.getElementById('cbody');
  const cMsg = document.getElementById('cmsg');

  const { escapeHtml = (s => String(s).replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]))) } = (window.Utils || {});

  function loginUrl() {
    const ret = encodeURIComponent(`post.php?id=${POST_ID}`);
    return `index.php?next=${ret}`;
  }
  function requireLoginRedirect() {
    window.location.href = loginUrl();
  }

  async function loadPost() {
    try {
      const res = await fetch(`webservice.php?action=get_post&id=${encodeURIComponent(POST_ID)}`, { credentials: 'same-origin' });
      const data = await res.json();

      if (!res.ok) {
        titleEl.textContent = (data && data.error) || 'Post not found';
        return;
      }

      const p = data.post || {};
      titleEl.textContent = p.title || '';
      bodyEl.textContent = p.body  || '';

      const place = [p.city, p.state, p.country].filter(Boolean).join(', ');
      const metaStr = [
        String(p.type || '').toUpperCase(),
        p.category || null,
        place || null,
        p.author ? `by ${p.author}` : null,
        p.created_at ? new Date(p.created_at).toLocaleString() : null
      ].filter(Boolean).join(' • ');
      metaEl.textContent = metaStr;

      // only reveal action buttons for logged-in users
      if (!IS_GUEST) {
        if (bmBtn) bmBtn.hidden = false;
        if (pmBtn) pmBtn.hidden = false;
        if (typeof p.bookmarked !== 'undefined') setBookmarkUI(!!p.bookmarked);
      }

      loadComments();
    } catch (e) {
      titleEl.textContent = 'Network error loading post.';
    }
  }

  function setBookmarkUI(on) {
    if (!bmBtn) return;
    bmBtn.textContent = on ? 'Remove Bookmark' : 'Add Bookmark';
    bmBtn.dataset.on = on ? '1' : '0';
  }

  async function toggleBookmark() {
    if (IS_GUEST) return requireLoginRedirect();

    try {
      const res = await fetch('webservice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'toggle_bookmark', post_id: POST_ID })
      });
      const data = await res.json();

      if (res.status === 401) return requireLoginRedirect();

      if (res.ok && data.ok) {
        setBookmarkUI(!!data.bookmarked);
      } else {
        alert((data && data.error) || 'Failed to toggle bookmark.');
      }
    } catch {
      alert('Network error while toggling bookmark.');
    }
  }

  async function startPrivateMessage() {
    if (IS_GUEST) return requireLoginRedirect();

    try {
      const res = await fetch('webservice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'open_thread', post_id: POST_ID })
      });
      const data = await res.json();

      if (res.status === 401) return requireLoginRedirect();

      if (res.ok && data.ok && data.thread_id) {
        window.location.href = 'messages.php?thread_id=' + encodeURIComponent(data.thread_id);
      } else {
        alert((data && data.error) || 'Could not start conversation.');
      }
    } catch {
      alert('Network error starting conversation.');
    }
  }

  async function loadComments() {
    commentsEl.setAttribute('aria-busy', 'true');
    commentsEl.innerHTML = '<p class="text-muted">Loading replies…</p>';

    try {
      const res  = await fetch('webservice.php?action=list_comments&post_id=' + encodeURIComponent(POST_ID), { credentials: 'same-origin' });
      const data = await res.json();

      commentsEl.innerHTML = '';
      const list = Array.isArray(data.comments) ? data.comments : [];

      list.forEach(c => {
        const div = document.createElement('div');
        div.className = 'comment';
        div.innerHTML = `
          <div class="comment-head">
            <strong>${escapeHtml(c.author || '')}</strong>
            <small>${c.created_at ? new Date(c.created_at).toLocaleString() : ''}</small>
          </div>
          <div class="comment-body">${escapeHtml(c.body || '')}</div>
        `;
        commentsEl.appendChild(div);
      });

      if (!list.length) {
        commentsEl.innerHTML = '<p class="text-muted">No public replies yet.</p>';
      }
    } catch {
      commentsEl.innerHTML = '<p class="text-muted">Network error loading replies.</p>';
    } finally {
      commentsEl.setAttribute('aria-busy', 'false');
    }
  }

  async function submitComment(e) {
    e.preventDefault();
    if (IS_GUEST) return requireLoginRedirect();

    const body = (cBody?.value || '').trim();
    if (!body) return;
    if (cMsg) cMsg.textContent = 'Posting…';

    try {
      const res = await fetch('webservice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'add_comment', post_id: POST_ID, body })
      });
      const data = await res.json();

      if (res.status === 401) return requireLoginRedirect();

      if (res.ok && data.ok) {
        if (cBody) cBody.value = '';
        if (cMsg)  cMsg.textContent = 'Posted!';
        loadComments();
      } else {
        if (cMsg) cMsg.textContent = (data && data.error) || 'Error posting reply.';
      }
    } catch {
      if (cMsg) cMsg.textContent = 'Network error posting reply.';
    }
  }

  if (bmBtn && !IS_GUEST) bmBtn.addEventListener('click', toggleBookmark);
  if (pmBtn && !IS_GUEST) pmBtn.addEventListener('click', startPrivateMessage);
  if (cForm && !IS_GUEST) cForm.addEventListener('submit', submitComment);
  
  loadPost();
})();

