function weekMonday(value = new Date()) {
  const date = typeof value === 'string' ? new Date(value + 'T12:00:00') : new Date(value);
  date.setDate(date.getDate() - (date.getDay() + 6) % 7);
  return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
}
function weekTasks() {
  try { const items = JSON.parse(loadLocalState('weekly-plan') || '[]'); return Array.isArray(items) ? items : []; } catch { return []; }
}
function renderWeekPlanSummary() {
  const tasks = weekTasks().filter((task) => task.week === weekMonday());
  const remaining = tasks.filter((task) => !task.done).length;
  document.getElementById('attentionCount').textContent = remaining;
  document.getElementById('attentionText').textContent = tasks.length ? remaining + ' van ' + tasks.length + ' taken nog te doen' : 'Maak je eigen planning voor deze week';
  const ordered = [...tasks.filter((task) => !task.done), ...tasks.filter((task) => task.done)];
  document.getElementById('weekPlanPreview').innerHTML = ordered.slice(0, 3).map((task) => '<li class="' + (task.done ? 'done' : '') + '"><span aria-hidden="true">' + (task.done ? '✓' : '○') + '</span><span title="' + esc(task.text) + '">' + esc(task.text) + '</span></li>').join('') + (tasks.length > 3 ? '<li class="week-plan-more">En nog ' + (tasks.length - 3) + ' taken · bekijk je weekplanning</li>' : '');
}
function openWeekPlan() {
  document.getElementById('weekPlanDate').value = weekMonday();
  renderWeekPlan();
  document.getElementById('weekPlanModal').classList.remove('hidden');
  document.body.classList.add('account-modal-open');
  document.getElementById('weekTaskText').focus();
}
function closeWeekPlan() {
  document.getElementById('weekPlanModal').classList.add('hidden');
  document.body.classList.remove('account-modal-open');
}
function renderWeekPlan() {
  const field = document.getElementById('weekPlanDate');
  field.value = weekMonday(field.value || new Date());
  const tasks = weekTasks().filter((task) => task.week === field.value);
  document.getElementById('weekTaskList').innerHTML = tasks.map((task) => '<div class="week-task ' + (task.done ? 'done' : '') + '"><label><input type="checkbox" ' + (task.done ? 'checked' : '') + ' onchange="toggleWeekTask(\'' + esc(task.id) + '\')"><span>' + esc(task.text) + '</span></label><button class="button" type="button" aria-label="Taak verwijderen" onclick="deleteWeekTask(\'' + esc(task.id) + '\')">Verwijderen</button></div>').join('') || '<p class="dim">Nog geen taken voor deze week. Voeg je eerste taak toe.</p>';
  renderWeekPlanSummary();
}
function persistWeekTasks(tasks) {
  saveLocalState('weekly-plan', JSON.stringify(tasks));
  renderWeekPlan();
}
function addWeekTask(event) {
  event.preventDefault();
  const field = document.getElementById('weekTaskText');
  const text = field.value.trim();
  if (!text) return;
  const tasks = weekTasks();
  if (tasks.length >= 250) { document.getElementById('weekPlanMessage').textContent = 'Je planning bevat maximaal 250 taken. Verwijder eerst oude taken.'; return; }
  tasks.push({id:crypto.randomUUID(), week:document.getElementById('weekPlanDate').value, text, done:false});
  persistWeekTasks(tasks);
  field.value = '';
  field.focus();
}
function toggleWeekTask(id) { persistWeekTasks(weekTasks().map((task) => task.id === id ? {...task, done:!task.done} : task)); }
function deleteWeekTask(id) { persistWeekTasks(weekTasks().filter((task) => task.id !== id)); }
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeWeekPlan(); });
