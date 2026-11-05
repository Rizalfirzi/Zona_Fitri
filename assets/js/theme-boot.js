// Runs before first paint so the saved theme doesn't flash.
(function () {
  var t = null;
  try { t = JSON.parse(localStorage.getItem('jo_theme')); } catch (e) {}
  if (!t) t = 'light';
  document.documentElement.dataset.theme = t;
})();
