(() => {
  const button = document.querySelector('[data-restart]');
  const scene = document.querySelector('.not-found-scene');
  const label = button?.querySelector('span');
  if (!button || !scene || !label) return;
  button.hidden = false;

  button.addEventListener('click', () => {
    if (button.getAttribute('aria-busy') === 'true') return;
    button.setAttribute('aria-disabled', 'true');
    button.setAttribute('aria-busy', 'true');
    label.textContent = 'Genstarter …';
    scene.classList.add('is-restarting');

    window.setTimeout(() => {
      scene.classList.remove('is-restarting');
      label.textContent = 'Prøv igen?';
      button.removeAttribute('aria-disabled');
      button.removeAttribute('aria-busy');
    }, 850);
  });
})();
