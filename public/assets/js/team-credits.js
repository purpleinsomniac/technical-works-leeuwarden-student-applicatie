(() => {
  const dialog = document.getElementById('teamCreditsDialog');
  if (!dialog) return;

  document.querySelectorAll('button[aria-controls="teamCreditsDialog"]').forEach((button) => {
    button.addEventListener('click', () => dialog.showModal());
  });

  // Een klik op de achtergrond komt ook op het dialoogvenster binnen.
  dialog.addEventListener('click', (event) => {
    if (event.target !== dialog) return;

    const { left, right, top, bottom } = dialog.getBoundingClientRect();
    const { clientX, clientY } = event;
    const isOutside = clientX < left || clientX > right || clientY < top || clientY > bottom;
    if (isOutside) dialog.close();
  });
})();
