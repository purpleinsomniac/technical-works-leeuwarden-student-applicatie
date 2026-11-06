// Accountvoorkeuren en wachtwoord wijzigen zonder het profielvenster te verlaten.
(() => {
  const form = document.getElementById('profileSettingsForm');
  if (!form) return;
  const icons = {
    person:'<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
    lock:'<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    shield:'<path d="m12 3 8 3v6c0 4-3 7-8 9-5-2-8-5-8-9V6Z"/>',
    bell:'<path d="M6 9a6 6 0 0 1 12 0c0 7 3 7 3 9H3c0-2 3-2 3-9M9 21h6"/>',
    phone:'<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
    laptop:'<path d="M5 4h14v13H5ZM2 20h20l-3-3H5Z"/>',
    mail:'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
  };
  const icon = (element, name) => { element.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + icons[name] + '</svg>'; };
  document.querySelectorAll('.profile-card > header > span').forEach((element, index) => icon(element, ['person','lock','shield','bell'][index]));
  document.querySelectorAll('.profile-option-icon').forEach((element, index) => icon(element, ['phone','laptop','mail','bell'][index]));
  let savedValues = new FormData(form);
  document.addEventListener('notification-preferences-saved', (event) => {
    for (const [name,value] of Object.entries(event.detail)) form.elements[name].checked = value;
    savedValues = new FormData(form);
  });
  const passwordEditor = document.getElementById('profilePasswordEditor');
  document.getElementById('profileResetPassword').addEventListener('click', () => {
    passwordEditor.open = true;
    form.elements.current_password.focus();
  });
  document.querySelectorAll('[data-account-close]').forEach((button) => button.addEventListener('click', () => {
    for (const name of ['email_notifications', 'platform_notifications']) form.elements[name].checked = savedValues.has(name);
    for (const name of ['current_password', 'password', 'password_confirmation']) form.elements[name].value = '';
    passwordEditor.open = false;
    document.getElementById('profileSaveMessage').textContent = '';
  }));
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('[type="submit"]');
    const message = document.getElementById('profileSaveMessage');
    const data = Object.fromEntries(new FormData(form));
    for (const name of ['email_notifications', 'platform_notifications']) data[name] = form.elements[name].checked;
    button.disabled = true;
    message.textContent = 'Bezig met opslaan…';
    try {
      const response = await fetch(form.action, {method:'PATCH', credentials:'same-origin', headers:{'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN':data._token}, body:JSON.stringify(data)});
      const result = await response.json();
      if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || result.message || 'Opslaan is mislukt.');
      message.textContent = result.message;
      document.dispatchEvent(new CustomEvent('profile-preferences-saved',{detail:{email_notifications:data.email_notifications,platform_notifications:data.platform_notifications}}));
      if (result.passwordChanged) document.getElementById('profilePasswordChanged').textContent = '◷ Laatste wijziging: zojuist';
      for (const name of ['current_password', 'password', 'password_confirmation']) form.elements[name].value = '';
      savedValues = new FormData(form);
      passwordEditor.open = false;
    } catch (error) { message.textContent = error.message; }
    finally { button.disabled = false; }
  });
})();
