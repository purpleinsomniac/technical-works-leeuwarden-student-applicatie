// Studentdashboard en persoonlijke dashboardinstellingen.

function choose(p) {
  phase = p;
  renderDashboard();
}

function renderDashboard() {
  const current = phases.find((item) => item[0] === phase) || phases[0];
  if (!current) return;
  phase = current[0];

  document.getElementById("phaseTabs").innerHTML = phases
    .map((x) => '<button class="tab ' + (phase === x[0] ? "active" : "") + '" onclick="choose(\'' + x[0] + "')\">" + x[1] + "</button>")
    .join("");

  document.getElementById("phasePercent").textContent = current[2] + "%";
  document.getElementById('dashboardPhaseTitle').textContent = 'Voortgang in de ' + current[1].toLowerCase();
  document.getElementById("phaseProgressLabel").textContent = "van de " + current[1].toLowerCase() + " behaald";

  document.getElementById("phaseBar").innerHTML = '<span style="width:' + current[2] + '%"></span>';
  const work = currentWorkByPhase[phase] || {
    title: "Nog geen opdracht",
    type: "Geen actieve opdracht",
    code: "",
  };
  document.getElementById("currentWorkTitle").textContent = work.title;
  document.getElementById("currentWorkMeta").textContent = work.type + " · " + current[1];
  const currentProject = assignments.find((item) => item.phase === phase && item.status !== 'completed');
  document.getElementById('dashboardCurrentProjectButton').onclick = () => currentProject ? openAssignment(currentProject.id) : show('assignments');
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
        outcome.pct +
        '%"></span></span><b>' +
        outcome.pct +
        "%</b></button>",
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

  document.getElementById("dashboardDeadlineList").innerHTML = assignments
    .filter((assignment) => assignment.status !== "completed")
    .slice(0, 3)
    .map(
      (assignment) =>
        '<button type="button" onclick="openAssignment(' +
        assignment.id +
        ')"><span>' +
        esc(assignment.dueDate) +
        "</span><strong>" +
        esc(assignment.title) +
        '</strong><span aria-hidden="true">›</span></button>',
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

// -----------------------------------------------------------------------------
// Persoonlijke dashboardinstellingen
// -----------------------------------------------------------------------------
const dashboardSettingsKey = "ontwikkeling-dashboard-instellingen";
const defaultDashboardPreferences = Object.freeze({
  compact: false,
  darkMode: false,
  outcomes: true,
  goals: true,
  actions: true,
  feedback: true,
  deadlines: true,
});
let dashboardSettingsMessageTimer;

document.addEventListener("student-state-saved", (event) => {
  if (!event.detail.keys.includes(dashboardSettingsKey)) return;

  const message = document.getElementById("dashboardSettingsSaved");
  if (!message) return;

  message.classList.add("visible");
  clearTimeout(dashboardSettingsMessageTimer);
  dashboardSettingsMessageTimer = setTimeout(() => message.classList.remove("visible"), 2200);
});

function getDashboardPreferences() {
  try {
    const saved = JSON.parse(loadLocalState(dashboardSettingsKey) || "{}");
    return { ...defaultDashboardPreferences, ...(saved && typeof saved === "object" ? saved : {}) };
  } catch {
    return { ...defaultDashboardPreferences };
  }
}

function applyDashboardPreferences() {
  const dashboard = document.getElementById("studentDashboard");
  if (!dashboard) return;
  const preferences = getDashboardPreferences();
  document.documentElement.classList.toggle("theme-dark", preferences.darkMode);
  dashboard.classList.toggle("dashboard-compact", preferences.compact);
  for (const key of ["outcomes", "goals", "actions", "feedback", "deadlines"]) {
    dashboard.querySelector('[data-dashboard-widget="' + key + '"]')?.classList.toggle("hidden", !preferences[key]);
  }
}

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
  applyUiIcons(document.getElementById("dashboardSettings"));
}

function updateDashboardPreference(key, enabled) {
  if (!(key in defaultDashboardPreferences)) return;
  const preferences = { ...getDashboardPreferences(), [key]: Boolean(enabled) };
  saveLocalState(dashboardSettingsKey, JSON.stringify(preferences));
  applyDashboardPreferences();
}

function resetDashboardPreferences() {
  saveLocalState(dashboardSettingsKey, JSON.stringify(defaultDashboardPreferences));
  renderDashboardSettings();
  applyDashboardPreferences();
  hint("De standaardweergave is hersteld.");
}

async function saveDashboardSettings() {
  const button = document.getElementById('saveDashboardSettingsButton');
  const message = document.getElementById('dashboardSettingsSaved');
  button.disabled = true;
  try {
    await apiRequest(window.LARAVEL_SAVE_URL, {method:'PUT',body:JSON.stringify({data:{[dashboardSettingsKey]:getDashboardPreferences()}})});
    message.textContent = 'Laatst opgeslagen: zojuist';
    hint('Je dashboardinstellingen zijn opgeslagen.');
  } catch (error) { message.textContent = error.message; }
  finally { button.disabled = false; }
}

