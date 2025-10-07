// bookmarks.js
(function () {
  const listEl = document.getElementById('list');
  if (!listEl) return;

  const escapeHtml =
    (window.Utils && window.Utils.escapeHtml) ||
    function (s) {
      return String(s).replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[m]));
    };

  async function loadBookmarks() {
    listEl.innerHTML = '<p class="text-muted">Loading…</p>';

    try {
      const res = await fetch('webservice.php?action=my_bookmarks', { credentials: 'same-origin' });
      const data = await res.json();

      listEl.innerHTML = '';

      if (!res.ok) {
        listEl.innerHTML =
          '<div class="error-box">' + escapeHtml(data.error || 'Error loading bookmarks') + '</div>';
        return;
      }

      const items = Array.isArray(data.bookmarks) ? data.bookmarks : [];
      if (!items.length) {
        listEl.innerHTML = '<p class="text-muted">No bookmarks yet.</p>';
        return;
      }

      items.forEach(p => {
        const a = document.createElement('a');
        a.className = 'card card-link';
        a.href = 'post.php?id=' + encodeURIComponent(p.id);
        a.innerHTML = `
          <small>${escapeHtml(String(p.type || '').toUpperCase())} • ${escapeHtml(p.category || '')}
          ${p.city ? ' • ' + escapeHtml(p.city) + ', ' + escapeHtml(p.state || '') + ', ' + escapeHtml(p.country || '') : ''}
          </small>
          <h3>${escapeHtml(p.title || '')}</h3>
          <p>${escapeHtml(p.body || '')}</p>
          <small>${new Date(p.created_at).toLocaleString()}</small>
        `;
        listEl.appendChild(a);
      });
    } catch (e) {
      listEl.innerHTML = '<div class="error-box">Network error loading bookmarks.</div>';
    }
  }

  loadBookmarks();
})();
