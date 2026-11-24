// Navigatie, iconen en algemene interfacefuncties.

const studentNavigation = [
  ["Dashboard", "studentDashboard", "dashboard"],
  ["Leeruitkomsten", "learningOutcomes", "outcomes"],
  ["Projecten", "assignments", "assignment"],
  ["Punten", "hours", "clock"],
  ["Leerdoelen", "goals", "target"],
  ["Reflecties", "reflections", "reflection"],
  ["Feedback", "feedback", "feedback"],
  ["Instellingen", "dashboardSettings", "settings"],
];

let phase = "OF";
let reflectionPhaseFilter = "all";
let feedbackPhaseFilter = "all";
let currentOutcomeCode = null;
let reflectionReturnToOutcome = false;
let feedbackReturnToOutcome = false;
let selectedAssignmentId = null;
const archivedOutcomesKey = "archived-learning-outcomes";

const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);

// Eén herbruikbare iconenverzameling voor de navigatie en dashboardkaarten.
const iconPaths = Object.freeze({
  dashboard: '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-6h5v6"/>',
  outcomes: '<path d="M12 3 20 6v6c0 4.7-3.2 7.5-8 9-4.8-1.5-8-4.3-8-9V6l8-3Z"/><path d="m12 8 1.1 2.2 2.4.4-1.7 1.7.4 2.4-2.2-1.1-2.2 1.1.4-2.4-1.7-1.7 2.4-.4L12 8Z"/>',
  assignment: '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 12h6M9 16h6"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  target: '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="m8 12 3 3 5-6"/>',
  reflection: '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 12h6M9 16h4"/>',
  feedback: '<path d="M4 5h16v13H9l-5 3V5Z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/>',
  settings: '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
  progress: '<path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/>',
  idea: '<path d="M9 18h6M10 22h4"/><path d="M8.5 15.5A7 7 0 1 1 15.5 15.5c-.9.7-1.4 1.5-1.5 2.5h-4c-.1-1-.6-1.8-1.5-2.5Z"/>',
  list: '<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
  calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
  layout: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M10 9v12"/>',
  eye: '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
  logout: '<path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/>',
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

// Vul de zijbalk met de gegevens van de ingelogde student.
function initializeStudentNavigation() {
  const studentName = window.LARAVEL_USER?.name || "Student";
  document.getElementById("roleLabel").textContent = "Student";
  document.getElementById("actor").textContent = studentName;
  document.getElementById("sidebarRole").textContent = "Student";
  document.getElementById("sidebarAvatar").textContent = studentName
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();

  document
    .getElementById("processingNotification")
    .classList.toggle("hidden", hourApprovals.length === 0);

  document.getElementById("nav").innerHTML = studentNavigation
    .map(([label, page, icon]) => {
      const settingsClass = page === "dashboardSettings" ? "settings-nav" : "";
      return '<button class="nav ' + settingsClass + '" data-route="' + page +
        '" onclick="show(\'' + page + '\')">' +
        '<span class="nav-icon">' + uiIcon(icon) + '</span>' +
        '<span class="nav-label">' + label + '</span></button>';
    })
    .join("");
  applyUiIcons();
  initializeSidebarAccount();
  show("studentDashboard");
}

// Wissel van weergave en houd het bijbehorende hoofdmenu-item actief.
function show(page) {
  document.querySelectorAll(".view").forEach((view) => view.classList.toggle("hidden", view.id !== page));

  let navigationPage = page;
  if (page === "outcomeDetail") navigationPage = "learningOutcomes";
  if (page === "assignmentDetail") navigationPage = "assignments";
  document.querySelectorAll("[data-route]").forEach((button) => {
    button.classList.toggle("active", button.dataset.route === navigationPage);
  });
  if (page === "assignments") renderAssignments();
  if (page === "hours") renderHours();
  if (page === "dashboardSettings") renderDashboardSettings();
  if (page === "studentDashboard") applyDashboardPreferences();
  window.scrollTo(0, 0);
}

let hintTimer = null;
// Toon een tijdelijke bevestiging of foutmelding onder in beeld.
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
