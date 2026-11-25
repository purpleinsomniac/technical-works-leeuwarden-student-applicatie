// Studentdashboard en persoonlijke dashboardinstellingen.

// Bewaar de gekozen fase en werk het dashboard bij.
function choose(p) {
  phase = p;
  renderDashboard();
}

// Vul het dashboard met gegevens voor de geselecteerde studiefase.
function renderDashboard() {
  const current = phases.find((item) => item[0] === phase) || phases[0];
  if (!current) return;
  phase = current[0];

  document.getElementById("phaseTabs").innerHTML = phases
    .map((x) => '<button class="tab ' + (phase === x[0] ? "active" : "") + '" onclick="choose(\'' + x[0] + "')\">" + x[1] + "</button>")
    .join("");

  document.getElementById("phasePercent").textContent = pointPercentLabel(current[2]);
  document.getElementById('dashboardPhaseTitle').textContent = 'Voortgang in de ' + current[1].toLowerCase();
  document.getElementById("phaseProgressLabel").textContent = "van de " + current[1].toLowerCase() + " behaald";

  document.getElementById("phaseBar").innerHTML = '<span style="width:' + pointPercentLabel(current[2]) + '"></span>';
  const work = currentWorkByPhase[phase];
  document.getElementById("currentWorkTitle").textContent = work?.title || "Nog geen opdracht";
  document.getElementById("currentWorkMeta").textContent = (work?.type || "Geen actieve opdracht") + " · " + current[1];
  const currentProject = assignments.find((item) => item.phase === phase && item.status !== 'completed');
  document.getElementById('dashboardCurrentProjectButton').onclick = () => currentProject ? openAssignment(currentProject.id) : show('assignments');

  const openAssignments = assignments.filter((assignment) => assignment.status !== 'completed');
  // Sorteer een kopie voor de eerstvolgende deadline, zodat de overzichtslijst haar volgorde houdt.
  const nextDeadline = [...openAssignments]
    .sort((a, b) => String(a.dueDateIso || '9999-12-31').localeCompare(String(b.dueDateIso || '9999-12-31')))[0];
  document.getElementById('nextDeadlineDate').textContent = nextDeadline?.dueDate || 'Nog geen deadline';
  document.getElementById('nextDeadlineTitle').textContent = nextDeadline?.title || 'Je hebt op dit moment geen openstaande projecten.';
  document.getElementById('dashboardNextDeadlineButton').onclick = () => nextDeadline ? openAssignment(nextDeadline.id) : show('assignments');
  renderWeekPlanSummary();

  const phaseOutcomes = outcomes
    .filter((outcome) => outcome.phase === phase && Number.isFinite(outcome.pct))
    .sort((a, b) => b.pct - a.pct)
    .slice(0, 4);
  document.getElementById("dashboardOutcomeProgress").innerHTML = phaseOutcomes
    .map(
      (outcome) =>
        '<button class="dashboard-outcome-row" type="button" onclick="openOutcome(\'' +
        outcome.code +
        "')\"><span>" +
        esc(outcome.short) +
        '</span><span class="bar"><span style="width:' +
        pointPercentLabel(outcome.pct) +
        '"></span></span><b>' +
        pointPercentLabel(outcome.pct) +
        "</b></button>",
    )
    .join("");

  const allActiveGoals = (window.LARAVEL_DATA?.goals || []).filter((goal) => goal.status === 'actief' && goalPhaseCode(goal) === phase);
  const activeGoals = allActiveGoals.slice(0, 2);
  document.getElementById('dashboardActiveGoalCount').textContent = '(' + allActiveGoals.length + ' actief)';
  document.getElementById('dashboardGoalsCaption').textContent = current[1] + ' · ' + allActiveGoals.length + ' actieve leerdoelen';
  document.getElementById("dashboardActionList").innerHTML = activeGoals.length
    ? activeGoals
        .map(
          (goal) =>
            '<button class="dashboard-action-row" type="button" onclick="show(\'goals\');openGoalDetail(' +
            Number(goal.id) +
            ')"><span class="action-status active">◎' +
            '</span><span class="dashboard-goal-copy"><strong>' +
            esc(goal.title) +
            "</strong><small>" +
            'Leeruitkomst: ' + esc(goal.outcome || 'Nog niet gekoppeld') +
            '</small></span><span aria-hidden="true">›</span></button>',
        )
        .join("")
    : '<p class="dashboard-empty">Nog geen actieve leerdoelen in deze fase. Maak een leerdoel aan via Alle leerdoelen.</p>';

  document.getElementById("dashboardDeadlineList").innerHTML = openAssignments
    .slice(0, 3)
    .map(
      (assignment) =>
        '<button type="button" onclick="openAssignment(' +
        assignment.id +
        ')"><span>' +
        esc(assignment.dueDate) +
        "</span><strong>" +
        esc(assignment.title) +
        '</strong><span class="dashboard-deadline-status ' + esc(assignment.status || 'todo') + '">' +
        (assignment.status === 'active' ? 'In voortgang' : assignment.status === 'review' ? 'In review' : 'Nog te starten') +
        '</span><span aria-hidden="true">›</span></button>',
    )
    .join("");

  const recentFeedback = feedbackItems[0];
  document.getElementById("dashboardRecentFeedback").innerHTML = recentFeedback
    ? '<blockquote class="dashboard-feedback-quote">“' +
      esc(recentFeedback.improve || recentFeedback.positive) +
      '”</blockquote><p class="dashboard-feedback-author"><strong>' +
      esc(recentFeedback.person) +
      "</strong><span>" +
      esc(recentFeedback.date) +
      "</span></p>"
    : '<p class="dashboard-empty">Er is nog geen feedback opgeslagen.</p>';

  applyDashboardPreferences();
}

// Persoonlijke dashboardinstellingen
const dashboardSettingsKey = "ontwikkeling-dashboard-instellingen";
const notificationSettingsKey = "notification-settings";
const settingsStateKeys = [dashboardSettingsKey, notificationSettingsKey];
const defaultDashboardPreferences = Object.freeze({
  compact: false,
  darkMode: false,
  theme: 'light',
  accent: window.LARAVEL_APPLICATION_THEME?.globalAccentColor
    || window.LARAVEL_APPLICATION_THEME?.accentColor
    || '#009b87',
  density: 'standard',
  outcomes: true,
  goals: true,
  actions: true,
  feedback: true,
  deadlines: true,
});
document.addEventListener("student-state-saved", ({ detail: { keys } }) => {
  if (!settingsStateKeys.some((key) => keys.includes(key))) return;
  // Een bevestiging voor een eerdere wijziging mag de nieuwste opslagstatus niet overschrijven.
  if (settingsStateKeys.some((key) => pendingStateChanges.has(key))) return;
  setSettingsSaveStatus("saved");
  if (keys.includes(dashboardSettingsKey)) applyDashboardPreferences();
  if (keys.includes(notificationSettingsKey)) {
    const { email_notifications, platform_notifications } = notificationPreferences;
    document.dispatchEvent(new CustomEvent("notification-preferences-saved", {
      detail: { email_notifications, platform_notifications },
    }));
  }
});
document.addEventListener("student-state-pending", ({ detail: { keys } }) => {
  if (settingsStateKeys.some((key) => keys.includes(key))) setSettingsSaveStatus("pending");
});
document.addEventListener("student-state-save-failed", () => {
  if (settingsStateKeys.some((key) => pendingStateChanges.has(key))) setSettingsSaveStatus("error");
});
// Laat zien of de voorkeuren worden opgeslagen of al bewaard zijn.
function setSettingsSaveStatus(status) {
  const title = document.getElementById('settingsSaveTitle');
  if (!title) return;
  const footer = title.closest('.settings-footer');
  footer.dataset.saveStatus = status;
  title.textContent = status === 'saved' ? 'Wijzigingen zijn automatisch opgeslagen.' : status === 'pending' ? 'Wijzigingen worden opgeslagen…' : 'Opslaan is niet gelukt.';
  document.getElementById('dashboardSettingsSaved').textContent = status === 'saved' ? 'Je instellingen zijn zojuist bijgewerkt.' : status === 'pending' ? 'Even geduld, je voorkeuren worden bewaard.' : 'Je wijzigingen zijn nog niet op de server opgeslagen. Probeer het opnieuw.';
  document.getElementById('settingsRetrySave').classList.toggle('hidden', status !== 'error');
}

// Vul ontbrekende voorkeuren aan en lees ook instellingen uit oudere versies.
function getDashboardPreferences(forDisplay = true) {
  try {
    const saved = JSON.parse(loadLocalState(dashboardSettingsKey) || "{}");
    const preferences = { ...defaultDashboardPreferences, ...(saved && typeof saved === "object" ? saved : {}) };
    if (!saved?.theme) preferences.theme = saved?.darkMode ? 'dark' : 'light';
    if (!saved?.density) preferences.density = saved?.compact ? 'compact' : 'standard';
    if (forDisplay) {
      preferences.accent = normalizeAccentColor(preferences.accent, preferences.accent_updated_at, preferences.accent_changed);
    } else {
      delete preferences.accent_updated_at;
    }
    return preferences;
  } catch {
    return { ...defaultDashboardPreferences };
  }
}

// Pas het thema, de dichtheid en zichtbare widgets direct toe op de weergave.
function applyDashboardPreferences() {
  const dashboard = document.getElementById("studentDashboard");
  if (!dashboard) return;
  const preferences = getDashboardPreferences();
  const root = document.documentElement;
  root.classList.toggle('theme-dark', preferences.theme === 'dark' || (preferences.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));
  root.dataset.density = preferences.density;
  root.dataset.accent = preferences.accent;
  root.style.setProperty('--student-accent', preferences.accent);
  document.getElementById('app')?.style.setProperty('--application-accent', preferences.accent);
  dashboard.classList.toggle("dashboard-compact", preferences.density === 'compact');
  for (const key of ["outcomes", "goals", "actions", "feedback", "deadlines"]) {
    dashboard.querySelector('[data-dashboard-widget="' + key + '"]')?.classList.toggle("hidden", !preferences[key]);
  }
}

// Vul de instellingen met de huidige voorkeuren.
function renderDashboardSettings() {
  const preferences = getDashboardPreferences();
  const fields = {
    compact: "settingCompact",
    darkMode: "settingDarkMode",
    outcomes: "settingOutcomes",
    goals: "settingGoals",
    actions: "settingActions",
    feedback: "settingFeedback",
    deadlines: "settingDeadlines",
  };
  for (const [key, id] of Object.entries(fields)) document.getElementById(id).checked = preferences[key];
  renderAppearanceSettings();
  renderNotificationSettings();
  selectSettingsTab(settingsActiveTab);
  applyUiIcons(document.getElementById("dashboardSettings"));
}

const settingsAccentColors = {teal:'#009b87', blue:'#168bed', purple:'#6840e8', pink:'#df238b', orange:'#fa7900', amber:'#efb014', green:'#78b915'};
// Toon de persoonlijke kleur als die nieuwer is dan de algemene kleur.
function normalizeAccentColor(value, updatedAt, pending) {
  const globalAccentUpdatedAt = Number(window.LARAVEL_APPLICATION_THEME?.globalAccentUpdatedAt || 0);
  const accent = Object.prototype.hasOwnProperty.call(settingsAccentColors, value)
    ? settingsAccentColors[value]
    : typeof value === 'string' && /^#[0-9a-f]{6}$/i.test(value) ? value.toLowerCase() : null;
  // Persoonlijke kleuren gelden alleen zolang ze nieuwer zijn dan de globale kleur.
  return accent && (pending || Number(updatedAt || 0) > globalAccentUpdatedAt)
    ? accent
    : window.LARAVEL_APPLICATION_THEME?.accentColor || defaultDashboardPreferences.accent;
}
let settingsActiveTab = 'appearance';
// Open het gekozen tabblad in de instellingen.
function selectSettingsTab(tab) {
  settingsActiveTab = tab;
  document.querySelectorAll('[data-settings-tab]').forEach((button) => {
    const selected = button.dataset.settingsTab === tab;
    button.classList.toggle('active', selected);
    button.setAttribute('aria-pressed', String(selected));
  });
  document.querySelectorAll('[data-settings-panel]').forEach((panel) => panel.classList.toggle('hidden', panel.dataset.settingsPanel !== tab));
}
// Bewaar de wijziging en pas de weergave meteen aan.
function updateAppearanceSetting(key, value) {
  const preferences = {...getDashboardPreferences(false), [key]:value};
  if (key === 'accent') preferences.accent_changed = true;
  preferences.darkMode = preferences.theme === 'dark';
  preferences.compact = preferences.density === 'compact';
  saveLocalState(dashboardSettingsKey, JSON.stringify(preferences));
  applyDashboardPreferences();
  renderAppearanceSettings();
}
// Controleer de ingevoerde kleur voordat deze wordt toegepast.
function updateAppearanceAccentHex(input) {
  if (!/^#[0-9a-f]{6}$/i.test(input.value)) {
    input.setCustomValidity('Vul een hexkleur in, bijvoorbeeld #009B87.');
    input.reportValidity();
    return;
  }
  input.setCustomValidity('');
  updateAppearanceSetting('accent', input.value.toLowerCase());
}
// Toon de huidige waarden in de vormgevingsinstellingen.
function renderAppearanceSettings() {
  const preferences = getDashboardPreferences();
  const choices = (key, options) => options.map(([value,title,description]) => '<label class="settings-choice ' + (preferences[key] === value ? 'selected' : '') + '"><input type="radio" name="appearance-' + key + '" value="' + value + '" ' + (preferences[key] === value ? 'checked' : '') + ' onchange="updateAppearanceSetting(\'' + key + '\', this.value)">' + (key === 'theme' ? '<span class="theme-preview ' + value + '" aria-hidden="true"><i></i><b><em></em><em></em><em></em></b></span>' : '<span class="density-symbol" aria-hidden="true">☰</span>') + '<span><strong>' + title + '</strong><small>' + description + '</small></span></label>').join('');
  document.getElementById('settingsThemeOptions').innerHTML = choices('theme', [['light','Licht','Heldere en frisse weergave'],['dark','Donker','Rustig en prettig voor je ogen'],['system','Systeem','Volgt je apparaatinstellingen']]);
  document.getElementById('settingsDensityOptions').innerHTML = choices('density', [['compact','Compact','Meer informatie op het scherm'],['standard','Standaard','Gebalanceerde weergave'],['spacious','Ruim','Meer witruimte en grotere kaarten']]);
  document.getElementById('settingsAccentOptions').innerHTML = '<label for="studentAccentColor"><span>Kleur kiezen</span><input id="studentAccentColor" type="color" value="' + preferences.accent + '" aria-label="Accentkleur kiezen" onchange="updateAppearanceSetting(\'accent\', this.value.toLowerCase())"></label><label for="studentAccentHex"><span>Hexcode</span><input id="studentAccentHex" type="text" value="' + preferences.accent.toUpperCase() + '" pattern="#[0-9A-Fa-f]{6}" maxlength="7" spellcheck="false" aria-label="Accentkleur als hexcode" oninput="this.setCustomValidity(\'\')" onchange="updateAppearanceAccentHex(this)"></label>';
}
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => { if (getDashboardPreferences().theme === 'system') applyDashboardPreferences(); });

// Bewaar de keuze voor een dashboardonderdeel en werk de weergave bij.
function updateDashboardPreference(key, enabled) {
  if (!(key in defaultDashboardPreferences)) return;
  const preferences = { ...getDashboardPreferences(false), [key]: Boolean(enabled) };
  if (key === 'compact') preferences.density = enabled ? 'compact' : 'standard';
  if (key === 'darkMode') preferences.theme = enabled ? 'dark' : 'light';
  saveLocalState(dashboardSettingsKey, JSON.stringify(preferences));
  applyDashboardPreferences();
}

// Zet de dashboardvoorkeuren terug op de standaardwaarden.
function resetDashboardPreferences() {
  notificationPreferences = {...defaultNotificationPreferences};
  saveLocalState(dashboardSettingsKey, JSON.stringify(defaultDashboardPreferences));
  saveLocalState('notification-settings', JSON.stringify(notificationPreferences));
  renderDashboardSettings();
  applyDashboardPreferences();
  hint("De standaardweergave is hersteld.");
}

const defaultNotificationPreferences = {feedback:true,grades:true,deadlines:true,projects:false,goals:false,platform_notifications:true,email_notifications:true,weekly_summary:false,do_not_disturb:false,deadline_days:2};
let notificationPreferences = {...defaultNotificationPreferences,...window.LARAVEL_NOTIFICATION_SETTINGS};
// Bewaar de keuze voor het opgegeven meldingskanaal.
function updateNotificationPreference(key, value) {
  notificationPreferences[key] = value;
  saveLocalState('notification-settings', JSON.stringify(notificationPreferences));
  applyNotificationPreferences();
}
// Pas de bewaarde meldingsvoorkeuren toe op de pagina.
function applyNotificationPreferences() {
  const enabled = notificationPreferences.platform_notifications && notificationPreferences.grades && !notificationPreferences.do_not_disturb;
  document.getElementById('processingNotification')?.classList.toggle('hidden', !enabled);
}
// Toon de bewaarde keuzes voor meldingen in de instellingen.
function renderNotificationSettings() {
  const rows = (items) => items.map(([key,title,description,icon]) => '<label class="notification-setting-row"><span class="notification-setting-icon">' + uiIcon(icon) + '</span><strong>' + title + '</strong><small>' + description + '</small><input type="checkbox" class="profile-switch" ' + (notificationPreferences[key] ? 'checked' : '') + ' onchange="updateNotificationPreference(\'' + key + '\',this.checked)"></label>').join('');
  document.getElementById('notificationTopicOptions').innerHTML = rows([
    ['feedback','Feedback van docenten','Ontvang een melding bij nieuwe feedback.','feedback'],
    ['grades','Nieuwe punten','Blijf op de hoogte van je behaalde punten.','progress'],
    ['deadlines','Aankomende deadlines','Ontvang herinneringen voor inlevermomenten.','calendar'],
    ['projects','Updates over projecten','Krijg bericht wanneer een project verandert.','layout'],
    ['goals','Herinneringen voor leerdoelen','Ontvang updates over je voortgang.','target'],
  ]);
  document.getElementById('notificationChannelOptions').innerHTML = rows([
    ['platform_notifications','Platformmeldingen','Ontvang meldingen in het dashboard.','dashboard'],
    ['email_notifications','E-mailmeldingen','Ontvang belangrijke updates via e-mail.','feedback'],
    ['weekly_summary','Wekelijks overzicht','Elke maandag een samenvatting van je voortgang.','calendar'],
  ]);
  document.getElementById('notificationQuietOption').innerHTML = rows([['do_not_disturb','Niet storen','Tijdelijk geen platformmeldingen ontvangen.','clock']]);
  document.getElementById('notificationDeadlineDays').value = notificationPreferences.deadline_days;
  applyNotificationPreferences();
}
document.addEventListener('profile-preferences-saved', (event) => {
  Object.assign(notificationPreferences, event.detail);
  renderNotificationSettings();
});
applyNotificationPreferences();
