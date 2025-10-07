// utils.js
(function (window) {
  const Utils = {
    escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[m]));
    }
  };
  window.Utils = Utils;
})(window);

