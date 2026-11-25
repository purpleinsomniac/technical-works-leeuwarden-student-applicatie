function show(page, force = false) {
  if (!force && window.RoleSettings && !window.RoleSettings.isSectionVisible(page)) return;
  const app = document.getElementById("app");
  app.classList.remove("hidden");
  app.classList.toggle("coach-groups-active", app.classList.contains("role-coach") && page === "coachGroups");

  let activeView = null;
  document.querySelectorAll(".view").forEach((view) => {
    const active = view.id === page;
    view.classList.toggle("hidden", !active);
    if (active) activeView = view;
  });

  const activeRoute = activeView?.dataset.roleSection || page;
  document.querySelectorAll("[data-route]").forEach((button) => {
    const active = button.dataset.route === activeRoute;
    button.classList.toggle("active", active);
    if (active) button.setAttribute("aria-current", "page");
    else button.removeAttribute("aria-current");
  });

  window.scrollTo(0, 0);
}

function hint(message) {
  const notification = document.getElementById("hint");

  if (!notification) return;

  notification.textContent = message;
  notification.classList.remove("hidden");

  window.setTimeout(() => notification.classList.add("hidden"), 3500);
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

function openSidebarAccount() {
  setSidebarAccountOpen(true, false);
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
      const focusable = [...modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), summary, select:not([disabled]), textarea:not([disabled])')]
        .filter((element) => element.getClientRects().length);
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

const sidebarRouteIcons = {
  studentDashboard: "dashboard",
  learningOutcomes: "outcomes",
  assignments: "assignment",
  hours: "clock",
  goals: "target",
  reflections: "reflection",
  feedback: "feedback",
  dashboardSettings: "settings",
  coachOverview: "dashboard",
  coachGroups: "groups",
  coachActivities: "outcomes",
  coachGoals: "target",
  coachProjects: "assignment",
  coachFeedback: "feedback",
  coachSettings: "settings",
  managerGroups: "groups",
  managerReports: "progress",
  managerPreferencesSettings: "settings",
  adminProcessing: "dashboard",
  adminUsers: "users",
  adminGroups: "groups",
  adminLearningOutcomes: "outcomes",
  adminApplicationSettings: "settings",
  adminPreferencesSettings: "settings",
};

const sidebarIconPaths = {
  dashboard: '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-6h5v6"/>',
  outcomes: '<path d="M12 3 20 6v6c0 4.7-3.2 7.5-8 9-4.8-1.5-8-4.3-8-9V6l8-3Z"/><path d="m12 8 1.1 2.2 2.4.4-1.7 1.7.4 2.4-2.2-1.1-2.2 1.1.4-2.4-1.7-1.7 2.4-.4L12 8Z"/>',
  assignment: '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 12h6M9 16h6"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  target: '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="m8 12 3 3 5-6"/>',
  reflection: '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 12h6M9 16h4"/>',
  feedback: '<path d="M4 5h16v13H9l-5 3V5Z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/>',
  settings: '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
  progress: '<path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/>',
  groups: '<path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20"/><circle cx="10" cy="7.5" r="3.5"/><path d="M16 4.2a3.5 3.5 0 0 1 0 6.6M20 20v-1.5a3.5 3.5 0 0 0-2.7-3.4"/>',
  users: '<path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20"/><circle cx="10" cy="7.5" r="3.5"/><path d="M16 4.2a3.5 3.5 0 0 1 0 6.6M20 20v-1.5a3.5 3.5 0 0 0-2.7-3.4"/>',
  logout: '<path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/>',
};

function sidebarIcon(name) {
  const paths = sidebarIconPaths[name] || sidebarIconPaths.dashboard;
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
}

function initializeRole({ label, routes }) {
  document.getElementById("roleLabel").textContent = label;
  document.getElementById("actor").textContent = window.LARAVEL_USER.name;
  document.getElementById("processingNotification")?.classList.add("hidden");
  initializeSidebarAccount();
  const logoutIcon = document.querySelector(".sidebar-logout .nav-icon");
  if (logoutIcon) logoutIcon.innerHTML = sidebarIcon("logout");

  document.getElementById("nav").innerHTML = routes
    .map(([title, page]) => `<button class="nav" data-route="${page}" onclick="show('${page}')"><span class="nav-icon" aria-hidden="true">${sidebarIcon(sidebarRouteIcons[page])}</span><span class="nav-label">${title}</span></button>`)
    .join("");

  show(routes[0][1]);
}

window.RoleApp = { initializeRole, openSidebarAccount, sidebarIcon };
