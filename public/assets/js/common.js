function show(page) {
  document.getElementById("app").classList.remove("hidden");

  document.querySelectorAll(".view").forEach((view) => {
    view.classList.toggle("hidden", view.id !== page);
  });

  document.querySelectorAll("[data-route]").forEach((button) => {
    button.classList.toggle("active", button.dataset.route === page);
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

function initializeRole({ label, routes }) {
  document.getElementById("roleLabel").textContent = label;
  document.getElementById("actor").textContent = window.LARAVEL_USER.name;
  document.getElementById("processingNotification").classList.add("hidden");

  document.getElementById("nav").innerHTML = routes
    .map(([title, page]) => `<button class="nav" data-route="${page}" onclick="show('${page}')">${title}</button>`)
    .join("");

  show(routes[0][1]);
}

window.RoleApp = { initializeRole };
