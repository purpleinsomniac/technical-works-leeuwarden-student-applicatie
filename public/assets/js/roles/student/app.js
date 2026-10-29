// Navigatie, iconen en algemene interfacefuncties.

// -----------------------------------------------------------------------------
// Navigatie en algemene interface
// -----------------------------------------------------------------------------
const routes = {
  student: [
    ["Dashboard", "studentDashboard", "dashboard"],
    ["Leeruitkomsten", "learningOutcomes", "outcomes"],
    ["Opdrachten", "assignments", "assignment"],
    ["Uren", "hours", "clock"],
    ["Leerdoelen", "goals", "target"],
    ["Reflecties", "reflections", "reflection"],
    ["Feedback", "feedback", "feedback"],
    ["Instellingen", "dashboardSettings", "settings"],
  ],
  coach: [
    ["Studenten", "coachStudents"],
    ["Studentdetail", "coachStudent"],
  ],
  manager: [
    ["Groepsoverzicht", "managerGroups"],
    ["Rapportages", "managerReports"],
  ],
  admin: [
    ["Verwerking", "adminProcessing"],
    ["Logboek", "adminLog"],
  ],
};

const names = {
  student: "Student",
  coach: "SLB'er / coach",
  manager: "Teamleider",
  admin: "Beheerder",
};

let role = window.LARAVEL_USER?.role || "student",
  phase = "OF",
  emptyHours = false,
  reflectionPhaseFilter = "all",
  feedbackPhaseFilter = "all",
  currentOutcomeCode = null,
  reflectionReturnToOutcome = false,
  feedbackReturnToOutcome = false,
  catalogAttentionOnly = false,
  catalogPage = 1,
  selectedRegistrationCode = null,
  selectedAssignmentId = null,
  outcomeReturnPage = "learningOutcomes";
const archivedOutcomesKey = "archived-learning-outcomes";

const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);

// Eén herbruikbare iconenverzameling voor de navigatie en dashboardkaarten.
const iconPaths = Object.freeze({
  dashboard: '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-6h5v6"/>',
  outcomes: '<path d="M5 3h11a3 3 0 0 1 3 3v15H7a2 2 0 0 1-2-2V3Z"/><path d="M8 7h7M8 11h7M8 15h4"/>',
  assignment: '<path d="M9 5h6M9 3h6v4H9z"/><path d="M7 5H5v16h14V5h-2"/><path d="m9 14 2 2 4-5"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  target: '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="m14 10 6-6M16 4h4v4"/>',
  reflection: '<path d="M4 5h16v11H9l-5 4V5Z"/><path d="M8 9h8M8 12h5"/>',
  feedback: '<path d="M5 20v-6M10 20V9M15 20V4M20 20V12"/>',
  settings: '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
  progress: '<path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/>',
  idea: '<path d="M9 18h6M10 22h4"/><path d="M8.5 15.5A7 7 0 1 1 15.5 15.5c-.9.7-1.4 1.5-1.5 2.5h-4c-.1-1-.6-1.8-1.5-2.5Z"/>',
  list: '<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
  calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
  layout: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M10 9v12"/>',
  eye: '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
});

function uiIcon(name) {
  const paths = iconPaths[name] || iconPaths.dashboard;
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + paths + "</svg>";
}

function applyUiIcons(scope = document) {
  scope.querySelectorAll("[data-icon]").forEach((element) => {
    if (!element.firstElementChild) element.innerHTML = uiIcon(element.dataset.icon);
  });
}

function login() {
  role = window.LARAVEL_USER?.role || "student";

  document.getElementById("roleLabel").textContent = names[role];

  document.getElementById("actor").textContent = window.LARAVEL_USER?.name || names[role];

  document
    .getElementById("processingNotification")
    .classList.toggle("hidden", role !== "student" || hourApprovals.length === 0);

  document.getElementById("nav").innerHTML = routes[role]
    .map(
      ([label, page, icon]) =>
        '<button class="nav ' +
        (page === "dashboardSettings" ? "settings-nav" : "") +
        '" data-route="' +
        page +
        '" onclick="show(\'' +
        page +
        "')\"><span class=\"nav-icon\">" +
        uiIcon(icon) +
        '</span><span class="nav-label">' +
        label +
        "</span></button>",
    )
    .join("");
  applyUiIcons();
  show(routes[role][0][1]);
}

function show(page) {
  // Het inlogscherm heeft in de MVC-structuur een eigen Blade-view.
  // Op het dashboard bestaat daarom alleen de applicatiecontainer.
  document.getElementById("app")?.classList.toggle("hidden", page === "login");

  document.querySelectorAll(".view").forEach((x) => x.classList.toggle("hidden", x.id !== page));

  const navigationPage = page === "outcomeDetail" ? "learningOutcomes" : page === "assignmentDetail" ? "assignments" : page;
  document.querySelectorAll("[data-route]").forEach((x) => x.classList.toggle("active", x.dataset.route === navigationPage));
  if (page === "assignments") renderAssignments();
  if (page === "dashboardSettings") renderDashboardSettings();
  if (page === "studentDashboard") applyDashboardPreferences();
  window.scrollTo(0, 0);
}

let hintTimer = null;
function hint(s) {
  const x = document.getElementById("hint");
  clearTimeout(hintTimer);
  x.textContent = s;
  x.classList.remove("hidden");
  hintTimer = setTimeout(() => {
    x.classList.add("hint-leaving");
    setTimeout(() => {
      x.classList.add("hidden");
      x.classList.remove("hint-leaving");
      x.textContent = "";
    }, 180);
  }, 3500);
}

