// Taken worden per week opgeslagen met de lokale datum van maandag als sleutel.
function weekMonday(value = new Date()) {
  const date = typeof value === 'string' ? new Date(value + 'T12:00:00') : new Date(value);
  date.setDate(date.getDate() - (date.getDay() + 6) % 7);
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return date.getFullYear() + '-' + month + '-' + day;
}

const weekPlanKey = 'weekly-plan';

// Lees de taken voor de geselecteerde week.
function weekTasks() {
  try {
    const tasks = JSON.parse(loadLocalState(weekPlanKey) || '[]');
    return Array.isArray(tasks) ? tasks : [];
  } catch {
    return [];
  }
}

// Toon een korte samenvatting van de taken op het dashboard.
function renderWeekPlanSummary() {
  const monday = weekMonday();
  const tasks = weekTasks().filter((task) => task.week === monday);
  const openTasks = tasks.filter((task) => !task.done);
  document.getElementById('attentionText').textContent = tasks.length
    ? openTasks.length + ' van ' + tasks.length + ' taken nog te doen'
    : 'Maak je eigen planning voor deze week';

  // Toon open taken eerst en behoud binnen beide groepen de opgeslagen volgorde.
  const orderedTasks = [...openTasks, ...tasks.filter((task) => task.done)];
  const preview = orderedTasks.slice(0, 3).map((task) => {
    const text = esc(task.text);
    const doneClass = task.done ? 'done' : '';
    const statusIcon = task.done ? '✓' : '○';
    return '<li class="' + doneClass + '"><span aria-hidden="true">' + statusIcon +
      '</span><span title="' + text + '">' + text + '</span></li>';
  });

  if (tasks.length > 3) {
    preview.push('<li class="week-plan-more">En nog ' + (tasks.length - 3) + ' taken · bekijk je weekplanning</li>');
  }
  document.getElementById('weekPlanPreview').innerHTML = preview.join('');
}

// Open de weekplanning met de bijbehorende taken.
function openWeekPlan() {
  document.getElementById('weekPlanDate').value = weekMonday();
  renderWeekPlan();
  document.getElementById('weekPlanModal').classList.remove('hidden');
  document.body.classList.add('account-modal-open');
  document.getElementById('weekTaskText').focus();
}

// Sluit het scherm met de weekplanning.
function closeWeekPlan() {
  document.getElementById('weekPlanModal').classList.add('hidden');
  document.body.classList.remove('account-modal-open');
}

// Toon de taken voor de week waarin de gekozen datum valt.
function renderWeekPlan() {
  const field = document.getElementById('weekPlanDate');
  field.value = weekMonday(field.value || new Date());
  const tasks = weekTasks().filter((task) => task.week === field.value);
  const rows = tasks.map((task) => {
    const taskId = esc(task.id);
    const doneClass = task.done ? 'done' : '';
    const checked = task.done ? 'checked' : '';
    return '<div class="week-task ' + doneClass + '">' +
      '<label><input type="checkbox" ' + checked + ' onchange="toggleWeekTask(\'' + taskId + '\')">' +
      '<span>' + esc(task.text) + '</span></label>' +
      '<button class="button" type="button" aria-label="Taak verwijderen" onclick="deleteWeekTask(\'' +
      taskId + '\')">Verwijderen</button></div>';
  });
  document.getElementById('weekTaskList').innerHTML = rows.join('') ||
    '<p class="dim">Nog geen taken voor deze week. Voeg je eerste taak toe.</p>';
  renderWeekPlanSummary();
}

// Bewaar de taken voor de week en werk de weergave bij.
function persistWeekTasks(tasks) {
  saveLocalState(weekPlanKey, JSON.stringify(tasks));
  renderWeekPlan();
}

// Voeg een geldige taak toe aan de geselecteerde week.
function addWeekTask(event) {
  event.preventDefault();
  const field = document.getElementById('weekTaskText');
  const text = field.value.trim();
  if (!text) return;
  const tasks = weekTasks();
  if (tasks.length >= 250) {
    document.getElementById('weekPlanMessage').textContent = 'Je planning bevat maximaal 250 taken. Verwijder eerst oude taken.';
    return;
  }
  tasks.push({ id: crypto.randomUUID(), week: document.getElementById('weekPlanDate').value, text, done: false });
  persistWeekTasks(tasks);
  field.value = '';
  field.focus();
}

// Wissel de taak tussen open en afgerond.
function toggleWeekTask(id) {
  persistWeekTasks(weekTasks().map((task) => task.id === id ? { ...task, done: !task.done } : task));
}

// Haal de gekozen taak uit de weekplanning.
function deleteWeekTask(id) {
  persistWeekTasks(weekTasks().filter((task) => task.id !== id));
}

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') closeWeekPlan();
});
