(() => {
  const menu = document.getElementById('demoAccountSwitcher');
  if (!menu) return;

  const summary = menu.querySelector('summary');
  document.addEventListener('click', (event) => {
    if (!menu.contains(event.target)) menu.open = false;
  });

  // Zet de focus terug op de menuknop als de gebruiker met Escape sluit.
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !menu.open) return;
    menu.open = false;
    summary.focus({ preventScroll: true });
  });
})();
