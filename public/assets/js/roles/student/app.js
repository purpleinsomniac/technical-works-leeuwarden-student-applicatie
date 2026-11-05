// Navigatie, iconen en algemene interfacefuncties.

// -----------------------------------------------------------------------------
// Navigatie en algemene interface
// -----------------------------------------------------------------------------
const routes = {
  student: [
    ["Dashboard", "studentDashboard", "dashboard"],
    ["Leeruitkomsten", "learningOutcomes", "outcomes"],
    ["Projecten", "assignments", "assignment"],
    ["Punten", "hours", "clock"],
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
  reflectionPhaseFilter = "all",
  feedbackPhaseFilter = "all",
  currentOutcomeCode = null,
  reflectionReturnToOutcome = false,
  feedbackReturnToOutcome = false,
  catalogPage = 1,
  selectedAssignmentId = null,
  outcomeReturnPage = "learningOutcomes";
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

function setSidebarAccountOpen(open, restoreFocus = true) {
  const button = document.getElementById("sidebarAccountButton");
  const modal = document.getElementById("sidebarAccountDetails");
  const closeButton = document.getElementById("accountModalClose");
  if (!button || !modal) return;

  button.setAttribute("aria-expanded", String(open));
  modal.classList.toggle("hidden", !open);
  modal.setAttribute("aria-hidden", String(!open));
  document.body.classList.toggle("account-modal-open", open);

  if (open) {
    window.setTimeout(() => closeButton?.focus(), 0);
  } else if (restoreFocus) {
    button.focus();
  }
}

function initializeSidebarAccount() {
  const button = document.getElementById("sidebarAccountButton");
  const modal = document.getElementById("sidebarAccountDetails");
  if (!button || !modal || button.dataset.initialized === "true") return;

  button.dataset.initialized = "true";
  button.addEventListener("click", () => {
    setSidebarAccountOpen(button.getAttribute("aria-expanded") !== "true");
  });

  modal.addEventListener("click", (event) => {
    if (event.target.closest("[data-account-close]")) setSidebarAccountOpen(false);
  });

  document.addEventListener("keydown", (event) => {
    const isOpen = button.getAttribute("aria-expanded") === "true";
    if (event.key === "Escape" && isOpen) {
      setSidebarAccountOpen(false);
    }
    if (event.key === "Tab" && isOpen) {
      const focusable = [...modal.querySelectorAll('a[href], button:not([disabled])')];
      const first = focusable[0];
      const last = focusable.at(-1);
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last?.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
      }
    }
  });
}

function login() {
  role = window.LARAVEL_USER?.role || "student";

  document.getElementById("roleLabel").textContent = names[role];

  document.getElementById("actor").textContent = window.LARAVEL_USER?.name || names[role];
  document.getElementById("sidebarRole").textContent = names[role];
  document.getElementById("sidebarAvatar").textContent = (window.LARAVEL_USER?.name || names[role])
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();

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
  initializeSidebarAccount();
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
  if (page === "hours") renderHours();
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

