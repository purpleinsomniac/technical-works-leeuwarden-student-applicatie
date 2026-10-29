// Studentdashboard en persoonlijke dashboardinstellingen.

function choose(p) {
  phase = p;
  renderDashboard();
}

function openCurrentWorkOutcome() {
  const code = currentWorkByPhase[phase]?.code;
  if (code) openOutcome(code);
  else hint("Er is voor deze fase nog geen leeruitkomst gekoppeld.");
}

function renderDashboard() {
  const current = phases.find((item) => item[0] === phase) || phases[0];
  if (!current) return;
  phase = current[0];

  document.getElementById("phaseTabs").innerHTML = phases
    .map((x) => '<button class="tab ' + (phase === x[0] ? "active" : "") + '" onclick="choose(\'' + x[0] + "')\">" + x[1] + "</button>")
    .join("");

  document.getElementById("phasePercent").textContent = current[2] + "%";
  document.getElementById("phaseProgressLabel").textContent = "van de " + current[1].toLowerCase() + " behaald";

  document.getElementById("phaseBar").innerHTML = '<span style="width:' + current[2] + '%"></span>';
  const work = currentWorkByPhase[phase] || {
    title: "Nog geen opdracht",
    type: "Geen actieve opdracht",
    code: "",
  };
  document.getElementById("currentWorkTitle").textContent = work.title;
  document.getElementById("currentWorkMeta").textContent = work.type + " · " + current[1];
  const attentionCount = (attentionOutcomesByPhase[phase] || []).length;
  document.getElementById("attentionCount").textContent = attentionCount;
  document.getElementById("attentionText").textContent =
    attentionCount === 1 ? "leeruitkomst om verder aan te werken" : "leeruitkomsten om verder aan te werken";

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

  const phaseAssignments = assignments.filter((assignment) => assignment.phase === phase).slice(0, 3);
  document.getElementById("dashboardActionList").innerHTML = phaseAssignments.length
    ? phaseAssignments
        .map(
          (assignment) =>
            '<button class="dashboard-action-row" type="button" onclick="openAssignment(' +
            assignment.id +
            ')"><span class="action-status ' +
            assignment.status +
            '">' +
            (assignment.status === "completed" ? "✓" : "") +
            '</span><span><strong>' +
            esc(assignment.title) +
            "</strong><small>" +
            esc(assignment.type) +
            "</small></span><em>" +
            assignmentStatuses[assignment.status] +
            '</em><span aria-hidden="true">›</span></button>',
        )
        .join("")
    : '<p class="dashboard-empty">Voor deze fase zijn geen openstaande acties.</p>';

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
  for (const key of ["outcomes", "actions", "feedback", "deadlines"]) {
    dashboard.querySelector('[data-dashboard-widget="' + key + '"]')?.classList.toggle("hidden", !preferences[key]);
  }
}

function renderDashboardSettings() {
  const preferences = getDashboardPreferences();
  const fields = {
    compact: "settingCompact",
    darkMode: "settingDarkMode",
    outcomes: "settingOutcomes",
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

