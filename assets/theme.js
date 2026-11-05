(() => {
  const key = 'dts-theme';
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  let saved = null;
  try {
    const value = window.localStorage.getItem(key);
    if (value === 'light' || value === 'dark') saved = value;
  } catch (_) { /* System preference still works when storage is unavailable. */ }

  const apply = () => {
    const dark = saved ? saved === 'dark' : media.matches;
    document.documentElement.classList.toggle('dark', dark);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.hidden = false;
      button.setAttribute('aria-pressed', String(dark));
      button.title = dark ? button.dataset.lightLabel : button.dataset.darkLabel;
    });
  };
  apply(); // Runs before the stylesheet to avoid a flash of the wrong theme.

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        saved = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        try { window.localStorage.setItem(key, saved); } catch (_) {}
        apply();
      });
    });
    apply();
  });
  media.addEventListener('change', () => { if (!saved) apply(); });
  window.addEventListener('storage', (event) => {
    if (event.key === key) {
      saved = event.newValue === 'light' || event.newValue === 'dark' ? event.newValue : null;
      apply();
    }
  });
})();
