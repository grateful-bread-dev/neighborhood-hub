// messages.js
(function () {
  const root = document.getElementById('thread-root');
  if (!root) return;

  let threadId = parseInt(root.dataset.threadId || '0', 10);
  const postId = parseInt(root.dataset.postId  || '0', 10);
  const meId = parseInt(root.dataset.me      || '0', 10);

  const box = document.getElementById('box');
  const meta = document.getElementById('meta');
  const text = document.getElementById('text');
  const send = document.getElementById('send');

  const escapeHtml =
    (window.Utils && window.Utils.escapeHtml) ||
    function (s) {
      return String(s).replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[m]));
    };

  async function ensureThread() {
    if (threadId || !postId) return;
    const res = await fetch('webservice.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ action: 'open_thread', post_id: postId })
    });
    const data = await res.json();
    if (res.ok && data.ok) {
      threadId = data.thread_id;
      history.replaceState(null, '', '?thread_id=' + threadId);
    } else {
      box.innerHTML = '<p class="text-muted">' +
        escapeHtml(data.error || 'Unable to open thread.') + '</p>';
      throw new Error(data.error || 'open_thread failed');
    }
  }

  async function loadMessages() {
    if (!threadId) return;
    box.setAttribute('aria-busy', 'true');

    const res = await fetch(
      'webservice.php?action=list_messages&thread_id=' + encodeURIComponent(threadId),
      { credentials: 'same-origin' }
    );
    const data = await res.json();
    box.innerHTML = '';

    if (!res.ok) {
      box.innerHTML = '<p class="text-muted">' +
        escapeHtml(data.error || 'Failed to load messages') + '</p>';
    } else {
      (data.messages || []).forEach(m => {
        const el = document.createElement('div');
        el.className = 'msg' + (parseInt(m.sender_id, 10) === meId ? ' me' : '');
        el.innerHTML = `
          <span class="from">${escapeHtml(m.sender)}:</span>
          <span class="body">${escapeHtml(m.body)}</span>
          <span class="time">${new Date(m.created_at).toLocaleString()}</span>
        `;
        box.appendChild(el);
      });

      if (!data.messages || data.messages.length === 0) {
        box.innerHTML = '<p class="text-muted">No messages yet. Say hello!</p>';
      }
      box.scrollTop = box.scrollHeight;
    }
    box.setAttribute('aria-busy', 'false');
  }

  async function sendMessage() {
    const body = text.value.trim();
    if (!body || !threadId) return;

    const res = await fetch('webservice.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ action: 'send_message', thread_id: threadId, body })
    });
    const data = await res.json();

    if (res.ok && data.ok) {
      text.value = '';
      loadMessages();
    } else {
      alert(data.error || 'Failed to send message');
    }
  }

  function bindEvents() {
    send && send.addEventListener('click', sendMessage);
    text && text.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) sendMessage();
    });
  }

  async function init() {
    meta.textContent = postId
      ? `Thread for post #${postId}`
      : (threadId ? `Thread #${threadId}` : '');

    bindEvents();
    await ensureThread();
    await loadMessages();

    // refresh every 10 seconds
    setInterval(loadMessages, 10000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
