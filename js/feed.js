// feed.js
const { escapeHtml } = window.Utils;

document.addEventListener('DOMContentLoaded', () => {

  async function loadPosts() {
    const params = new URLSearchParams({ action: 'list_posts', limit: '50' });

    const t = document.getElementById('type').value;
    const c = document.getElementById('category').value;
    const q = document.getElementById('q').value.trim();
    const city = document.getElementById('city').value.trim();
    const state = document.getElementById('state').value.trim();
    const country = document.getElementById('country').value.trim();

    if (t) params.set('type', t);
    if (c) params.set('category', c);
    if (q) params.set('q', q);
    if (city) params.set('city', city);
    if (state) params.set('state', state);
    if (country) params.set('country', country);

    const grid = document.getElementById('grid');
    grid.setAttribute('aria-busy', 'true');
    grid.innerHTML = '';

    const res = await fetch('webservice.php?' + params.toString(), { credentials: 'same-origin' });
    const data = await res.json();

    (data.posts || []).forEach(p => {
      const a = document.createElement('a');
      a.className = 'card card-link';
      a.href = 'post.php?id=' + encodeURIComponent(p.id);
      a.innerHTML = `
        <small>${escapeHtml(String(p.type).toUpperCase())} • ${escapeHtml(p.category)}
              ${p.city ? ' • ' + escapeHtml(p.city) + ', ' + escapeHtml(p.state) + ', ' + escapeHtml(p.country) : ''}
              • by ${escapeHtml(p.author)}</small>
        <h3>${escapeHtml(p.title)}</h3>
        <p>${escapeHtml(p.body)}</p>
        <small>${new Date(p.created_at).toLocaleString()}</small>
      `;
      grid.appendChild(a);
    });

    if (!data.posts || data.posts.length === 0) {
      grid.innerHTML = '<p class="text-muted">No posts found. Try adjusting your filters.</p>';
    }

    grid.setAttribute('aria-busy', 'false');
  }

  // triggers reload
  document.getElementById('apply').addEventListener('click', loadPosts);

  // automatically load posts
  setTimeout(loadPosts, 100);
});

