// inbox.js
(function () {
  const threadsEl = document.getElementById('threads');
  if (!threadsEl) return;

  const escapeHtml =
    (window.Utils && window.Utils.escapeHtml) ||
    function (s) {
      return String(s).replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[m]));
    };

  async function loadThreads() {
    threadsEl.innerHTML = '<p class="text-muted">Loading…</p>';
    try {
      const res = await fetch('webservice.php?action=my_threads', { credentials: 'same-origin' });
      const data = await res.json();
      threadsEl.innerHTML = '';

      if (!res.ok) {
        threadsEl.innerHTML = `<div class="error-box">${escapeHtml(data.error || 'Error loading conversations')}</div>`;
        return;
      }

      (data.threads || []).forEach(t => {
        const div = document.createElement('div');
        div.className = 'thread card';
        div.innerHTML = `
          <a href="messages.php?thread_id=${t.thread_id}">
            <strong>${escapeHtml(t.other_user)}</strong>
            <span>about <em>${escapeHtml(t.post_title)}</em></span>
          </a>
          <small>Started ${new Date(t.created_at).toLocaleString()}</small>
        `;
        threadsEl.appendChild(div);
      });

      if (!data.threads || data.threads.length === 0) {
        threadsEl.innerHTML = '<p class="text-muted">No conversations yet.</p>';
      }
    } catch (e) {
      threadsEl.innerHTML = '<div class="error-box">Network error loading conversations.</div>';
    }
  }

  window.addEventListener('DOMContentLoaded', loadThreads);
})();
