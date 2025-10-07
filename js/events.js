// events.js
(function () {

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  async function loadEvents(){
    const params = new URLSearchParams({ action: 'list_posts', type: 'event', limit: '50' });
    const q = document.getElementById('q')?.value.trim() || '';
    const city = document.getElementById('city')?.value.trim() || '';
    const state = document.getElementById('state')?.value.trim() || '';
    const country = document.getElementById('country')?.value.trim() || '';

    if (q) params.set('q', q);
    if (city) params.set('city', city);
    if (state) params.set('state', state);
    if (country) params.set('country', country);

    const grid = document.getElementById('grid');
    if (!grid) return;
    grid.setAttribute('aria-busy','true');
    grid.innerHTML = '';

    const res = await fetch('webservice.php?' + params.toString(), { credentials: 'same-origin' });
    const data = await res.json();

    (data.posts || []).forEach(p => {
      const a = document.createElement('a');
      a.className = 'card card-link';
      a.href = 'post.php?id=' + encodeURIComponent(p.id);
      a.innerHTML = `
        <small>EVENT • ${escapeHtml(p.city || '')}, ${escapeHtml(p.state || '')}, ${escapeHtml(p.country || '')}
               • by ${escapeHtml(p.author || '')}</small>
        <h3>${escapeHtml(p.title)}</h3>
        <p>${escapeHtml(p.body)}</p>
        <small>${new Date(p.created_at).toLocaleString()}</small>
      `;
      grid.appendChild(a);
    });

    if (!data.posts || data.posts.length === 0) {
      grid.innerHTML = '<p class="text-muted">No events found. Try different filters or create one above.</p>';
    }
    grid.setAttribute('aria-busy','false');
  }

  const eventForm = document.getElementById('eventForm');
  if (eventForm) {
    document.getElementById('eventForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = document.getElementById('ev-msg');
      if (msg) msg.textContent = 'Publishing…';

      const form = e.currentTarget;
      const payload = Object.fromEntries(new FormData(form).entries());
      payload.action = 'create_post';
      payload.type = 'event';

      const res = await fetch('webservice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });
      const data = await res.json();

      if (res.ok && data.ok) {
        if (msg) msg.textContent = 'Event posted!';
        form.reset();
        loadEvents();
      } else {
        if (msg) msg.textContent = data.error || 'Error creating event.';
      }
    });
  }

  const applyBtn = document.getElementById('apply');
  if (applyBtn) applyBtn.addEventListener('click', loadEvents);

  loadEvents();

})();
