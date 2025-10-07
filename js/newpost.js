// newpost.js
(function () {
  const form = document.getElementById('postForm');
  if (!form) return;

  const msg = document.getElementById('msg');

  async function handleSubmit(e) {
    e.preventDefault();
    msg.textContent = 'Submitting…';

    // build payload from the form
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.action = 'create_post';

    // POST JSON to the webservice
    const btn = form.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; }

    try {
      const res = await fetch('webservice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });

      const data = await res.json();

      if (res.ok && data.ok) {
        msg.textContent = 'Post created! Redirecting…';
        setTimeout(() => (location.href = 'feed.php'), 800);
      } else {
        msg.textContent = (data && data.error) ? data.error : 'Error creating post.';
      }
    } catch (err) {
      msg.textContent = 'Network error creating post.';
    } finally {
      if (btn) { btn.disabled = false; }
    }
  }

  form.addEventListener('submit', handleSubmit);
})();
