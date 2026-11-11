function show(page, force = false) {
  if (!force && window.RoleSettings && !window.RoleSettings.isSectionVisible(page)) return;
  const app = document.getElementById("app");
  app.classList.remove("hidden");
  app.classList.toggle("coach-groups-active", app.classList.contains("role-coach") && page === "coachGroups");

  let activeView = null;
  document.querySelectorAll(".view").forEach((view) => {
    const active = view.id === page;
    view.classList.toggle("hidden", !active);
    view.classList.remove("is-accenting");
    if (active) activeView = view;
  });

  document.querySelectorAll("[data-route]").forEach((button) => {
    const active = button.dataset.route === page;
    button.classList.toggle("active", active);
    if (active) button.setAttribute("aria-current", "page");
    else button.removeAttribute("aria-current");
  });

  if (activeView && app.classList.contains("role-coach")) {
    window.requestAnimationFrame(() => {
      if (activeView.classList.contains("hidden")) return;
      if (activeView.coachAccentTimer) window.clearTimeout(activeView.coachAccentTimer);
      activeView.classList.add("is-accenting");
      activeView.coachAccentTimer = window.setTimeout(() => {
        activeView.classList.remove("is-accenting");
        activeView.coachAccentTimer = null;
      }, 760);
    });
  }

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

function initializeRole({ label, routes }) {
  document.getElementById("roleLabel").textContent = label;
  document.getElementById("actor").textContent = window.LARAVEL_USER.name;
  document.getElementById("processingNotification").classList.add("hidden");
  initializeSidebarAccount();

  document.getElementById("nav").innerHTML = routes
    .map(([title, page]) => `<button class="nav" data-route="${page}" onclick="show('${page}')">${title}</button>`)
    .join("");

  show(routes[0][1]);
}

window.RoleApp = { initializeRole, openSidebarAccount };
