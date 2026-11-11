window.RoleApp.initializeRole({
  label: "Docent",
  routes: [
    ["Overzicht", "coachOverview"],
    ["Groepen", "coachGroups"],
    ["Leeractiviteiten", "coachActivities"],
    ["Leerdoelen", "coachGoals"],
    ["Projecten", "coachProjects"],
    ["Feedback", "coachFeedback"],
  ],
});
RoleSettings.initialize();

// Geef elk hoofdonderwerp een eigen deelbare URL en herstel die pagina na verversen.
const coachPageRoutes = {
  coachOverview: "overview",
  coachGroups: "groups",
  coachActivities: "activities",
  coachGoals: "goals",
  coachProjects: "projects",
  coachFeedback: "feedback",
};
const coachNav = document.getElementById("nav");
coachNav.querySelectorAll("[data-route]").forEach((button) => {
  const routeKey = coachPageRoutes[button.dataset.route];
  const url = window.LARAVEL_COACH_ROUTES?.[routeKey];
  if (!url) return;
  const link = document.createElement("a");
  link.className = button.className;
  link.dataset.route = button.dataset.route;
  link.href = url;
  const counts = {
    coachGroups: window.LARAVEL_DATA?.groups?.length,
    coachActivities: window.LARAVEL_DATA?.activities?.length,
    coachGoals: window.LARAVEL_DATA?.goals?.length,
    coachProjects: window.LARAVEL_DATA?.projects?.length,
    coachFeedback: window.LARAVEL_DATA?.pendingFeedback?.length,
  };
  const title = document.createElement("span");
  title.textContent = button.textContent;
  link.append(title);
  const count = counts[button.dataset.route];
  if (Number.isFinite(count)) {
    const badge = document.createElement("span");
    badge.className = "coach-nav-count";
    badge.textContent = String(count);
    badge.setAttribute("aria-hidden", "true");
    link.append(badge);
    link.setAttribute("aria-label", `${button.textContent}, ${count}`);
  }
  button.replaceWith(link);
});
const initialCoachView = {
  overview: "coachOverview",
  groups: "coachGroups",
  activities: "coachActivities",
  goals: "coachGoals",
  projects: "coachProjects",
  feedback: "coachFeedback",
}[window.LARAVEL_COACH_PAGE] || "coachOverview";
const requestedStudentId = Number(window.LARAVEL_COACH_STUDENT_ID || 0);
const coachViewFromLocation = () => {
  const currentPath = window.location.pathname.replace(/\/$/, "");
  for (const [viewId, routeKey] of Object.entries(coachPageRoutes)) {
    const routePath = new URL(window.LARAVEL_COACH_ROUTES[routeKey], window.location.origin).pathname.replace(/\/$/, "");
    if (routePath === currentPath) return viewId;
  }
  return null;
};
const initialStudentView = window.LARAVEL_COACH_PAGE === "student" && requestedStudentId
  ? `coachStudent-${requestedStudentId}`
  : null;
show(initialStudentView || coachViewFromLocation() || initialCoachView, true);

// Wissel coachpagina's zonder de zijbalk opnieuw op te bouwen; URL en browsergeschiedenis blijven synchroon.
coachNav.addEventListener("click", (event) => {
  const link = event.target.closest("a[data-route]");
  if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
  if (!window.RoleSettings.isSectionVisible(link.dataset.route)) return;
  event.preventDefault();
  window.history.pushState({ coachView: link.dataset.route }, "", link.href);
  show(link.dataset.route);
  if (link.dataset.route === "coachProjects" && coachProjectStatusFilter) {
    coachProjectStatusFilter.value = new URL(link.href).searchParams.get("status") || "";
    coachProjectStatusFilter.dispatchEvent(new Event("change"));
  }
  coachMobileMenuButton?.setAttribute("aria-expanded", "false");
  coachSidebar?.classList.remove("is-open");
});

window.addEventListener("popstate", () => {
  const viewId = coachViewFromLocation();
  if (!viewId) return;
  show(viewId, true);
  if (viewId === "coachProjects" && coachProjectStatusFilter) {
    const status = new URL(window.location.href).searchParams.get("status") || "";
    coachProjectStatusFilter.value = status;
    coachProjectStatusFilter.dispatchEvent(new Event("change"));
  }
});

// Gebruik het zichtbare label als toegankelijke naam voor elke navigatieknop.
document.querySelectorAll("#nav [data-route]").forEach((button) => {
  button.setAttribute("aria-label", button.textContent.trim());
});

/* Gebruik dezelfde paginering voor studenten, overzichten en leeruitkomsten. */
function createPager(allItems, pageSize, anchor, label) {
  const nav = document.createElement("nav");
  nav.className = "coach-outcome-pagination";
  nav.setAttribute("aria-label", label);
  nav.innerHTML = `
    <span class="coach-outcome-range" aria-live="polite"></span>
    <div class="coach-outcome-page-controls">
      <button class="coach-outcome-page-button" type="button" data-dir="-1" aria-label="Vorige pagina">←</button>
      <span class="coach-outcome-page-label" aria-live="polite"></span>
      <button class="coach-outcome-page-button" type="button" data-dir="1" aria-label="Volgende pagina">→</button>
    </div>`;
  anchor.after(nav);

  const [prev, next] = nav.querySelectorAll("button");
  const range = nav.querySelector(".coach-outcome-range");
  const pageLabel = nav.querySelector(".coach-outcome-page-label");
  let matches = allItems;
  let page = 1;

  function render() {
    const totalPages = Math.max(1, Math.ceil(matches.length / pageSize));
    page = Math.min(Math.max(page, 1), totalPages);
    const start = (page - 1) * pageSize;
    const visible = new Set(matches.slice(start, start + pageSize));

    allItems.forEach((item) => item.classList.toggle("hidden", !visible.has(item)));
    nav.classList.toggle("hidden", matches.length <= pageSize);
    prev.disabled = page === 1;
    next.disabled = page === totalPages;
    pageLabel.textContent = `Pagina ${page} van ${totalPages}`;
    range.textContent = matches.length
      ? `Toont ${start + 1}–${Math.min(start + pageSize, matches.length)} van ${matches.length}`
      : "";
  }

  nav.addEventListener("click", (event) => {
    const button = event.target.closest("button");
    if (!button) return;
    page += Number(button.dataset.dir);
    render();
  });

  return {
    show(items) {
      matches = items;
      items.forEach((item) => anchor.append(item));
      page = 1;
      render();
    },
  };
}

/* Pas de naam-, fase- en groepsfilters toe voordat de gevonden studenten worden gepagineerd. */
const coachStudentSearch = document.getElementById("coachStudentSearch");
const coachPhaseFilter = document.getElementById("coachPhaseFilter");
const coachGroupFilter = document.getElementById("coachGroupFilter");
const coachStudentSort = document.getElementById("coachStudentSort");
const coachStudentCards = [...document.querySelectorAll(".coach-student-card[data-student-name]")];
const coachNoResults = document.getElementById("coachNoResults");
const coachStudentGrid = document.querySelector(".coach-student-grid");

if (coachStudentSearch && coachPhaseFilter && coachNoResults && coachStudentGrid) {
  const studentPager = createPager(coachStudentCards, 8, coachStudentGrid, "Pagina's met studenten");

  function filterCoachStudents() {
    const searchTerm = coachStudentSearch.value.trim().toLocaleLowerCase("nl");
    const selectedPhase = coachPhaseFilter.value;
    const selectedGroup = coachGroupFilter?.value ?? "";

    const matches = coachStudentCards.filter((card) => {
      const matchesName = card.dataset.studentName.includes(searchTerm);
      const matchesPhase = !selectedPhase || card.dataset.studentPhase === selectedPhase;
      const matchesGroup = !selectedGroup || card.dataset.studentGroupIds.includes(`,${selectedGroup},`);
      return matchesName && matchesPhase && matchesGroup;
    });

    const sort = coachStudentSort?.value ?? "name";
    matches.sort((a, b) => {
      if (sort === "progress-asc") {
        return Number(a.dataset.studentProgress) - Number(b.dataset.studentProgress);
      }
      if (sort === "xp-asc") {
        return Number(a.dataset.studentXp) - Number(b.dataset.studentXp);
      }
      return a.dataset.studentName.localeCompare(b.dataset.studentName, "nl");
    });

    studentPager.show(matches);
    const studentCount = document.getElementById("coachStudentCount");
    if (studentCount) {
      studentCount.textContent = `${matches.length} ${matches.length === 1 ? "student" : "studenten"}`;
    }
    coachNoResults.classList.toggle("hidden", matches.length > 0);

    // Laat in de kop zien welke groep is gekozen.
    const rosterTitle = document.getElementById("coachRosterTitle");
    if (rosterTitle) {
      const groupName = selectedGroup
        ? coachGroupFilter.selectedOptions[0].textContent.split(" · ")[0]
        : "";
      rosterTitle.textContent = groupName ? `Studenten in ${groupName}` : "Studenten in mijn groepen";
    }

    // Markeer de gekozen groepskaart.
    document.querySelectorAll("[data-coach-group-card]").forEach((card) => {
      const active = card.dataset.coachGroupCard === selectedGroup;
      card.classList.toggle("is-active", active);
      card.setAttribute("aria-pressed", String(active));
    });
  }

  coachStudentSearch.addEventListener("input", filterCoachStudents);
  coachPhaseFilter.addEventListener("change", filterCoachStudents);
  coachGroupFilter?.addEventListener("change", filterCoachStudents);
  coachStudentSort?.addEventListener("change", filterCoachStudents);
  filterCoachStudents();
}

window.openCoachGroup = function (groupId) {
  show("coachGroups");
  if (coachStudentSearch) coachStudentSearch.value = "";
  if (coachPhaseFilter) coachPhaseFilter.value = "";
  if (coachGroupFilter) coachGroupFilter.value = String(groupId);
  coachGroupFilter?.dispatchEvent(new Event("change"));
  document.getElementById("coachStudentRoster")?.scrollIntoView({ behavior: "smooth", block: "start" });
};

window.openCoachProjectQueue = function () {
  window.location.href = `${window.LARAVEL_COACH_ROUTES.projects}?status=review`;
};

window.openCoachProjects = function () {
  window.location.href = window.LARAVEL_COACH_ROUTES.projects;
};

/* Filter, sort, and paginate the project list without leaving the dashboard. */
const coachProjectGrid = document.querySelector("#coachProjects .coach-directory-grid");
const coachProjectCards = coachProjectGrid ? [...coachProjectGrid.children] : [];
const coachProjectSearch = document.getElementById("coachProjectSearch");
const coachProjectStatusFilter = document.getElementById("coachProjectStatusFilter");
const coachProjectNoResults = document.getElementById("coachProjectNoResults");

if (coachProjectGrid && coachProjectSearch && coachProjectStatusFilter && coachProjectNoResults) {
  const projectPager = createPager(coachProjectCards, 6, coachProjectGrid, "Pagina's met projecten");

  function filterCoachProjects() {
    const searchTerm = coachProjectSearch.value.trim().toLocaleLowerCase("nl");
    const selectedStatus = coachProjectStatusFilter.value;
    const matches = coachProjectCards.filter((card) =>
      card.dataset.projectSearch.includes(searchTerm) &&
      (!selectedStatus || card.dataset.projectStatus === selectedStatus),
    );

    projectPager.show(matches);
    coachProjectNoResults.classList.toggle("hidden", matches.length > 0);
  }

  coachProjectSearch.addEventListener("input", filterCoachProjects);
  coachProjectStatusFilter.addEventListener("change", () => {
    const url = new URL(window.location.href);
    if (coachProjectStatusFilter.value) url.searchParams.set("status", coachProjectStatusFilter.value);
    else url.searchParams.delete("status");
    window.history.replaceState({}, "", url);
    filterCoachProjects();
  });
  document.querySelectorAll("[data-project-filter-shortcut]").forEach((button) => {
    button.addEventListener("click", () => {
      coachProjectStatusFilter.value = button.dataset.projectFilterShortcut;
      coachProjectStatusFilter.dispatchEvent(new Event("change"));
      document.querySelector("#coachProjects .coach-directory-tools")?.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  });
  if (["review", "active", "concept", "completed"].includes(window.LARAVEL_COACH_PROJECT_FILTER)) {
    coachProjectStatusFilter.value = window.LARAVEL_COACH_PROJECT_FILTER;
  }
  filterCoachProjects();
}

/* Keep the remaining read-only directories compact. */
const coachDirectoryPagers = new WeakMap();
document.querySelectorAll(".coach-directory-grid").forEach((grid) => {
  if (grid === coachProjectGrid) return;
  const cards = [...grid.children];
  const pager = createPager(cards, 6, grid, "Pagina's");
  coachDirectoryPagers.set(grid, pager);
  pager.show(cards);
});

/* Zoek ook snel door activiteiten en doelen, zonder de huidige pagina kwijt te raken. */
[
  ["coachActivitySearch", "coachActivityNoResults", "coachActivityPhaseFilter", "phase"],
  ["coachGoalSearch", "coachGoalNoResults", "coachGoalStatusFilter", "status"],
].forEach(([inputId, emptyId, selectId, dataKey]) => {
  const input = document.getElementById(inputId);
  const empty = document.getElementById(emptyId);
  const select = document.getElementById(selectId);
  const grid = input?.closest(".coach-directory-tools")?.nextElementSibling?.nextElementSibling;
  if (!input || !empty || !grid?.classList.contains("coach-directory-grid")) return;
  const cards = [...grid.querySelectorAll("[data-directory-search]")];
  const pager = coachDirectoryPagers.get(grid);
  if (!pager) return;
  const filter = () => {
    const term = input.value.trim().toLocaleLowerCase("nl");
    const selected = select?.value ?? "";
    const matches = cards.filter((card) =>
      card.dataset.directorySearch.includes(term) &&
      (!selected || (card.dataset[`directory${dataKey[0].toUpperCase()}${dataKey.slice(1)}`] ?? "").toLocaleLowerCase("nl") === selected),
    );
    pager.show(matches);
    empty.classList.toggle("hidden", matches.length > 0);
  };
  input.addEventListener("input", filter);
  select?.addEventListener("change", filter);
  filter();
});

/* Paginate long work queues while keeping all open actions available. */
document.querySelectorAll("#coachPendingProjects, .coach-feedback-confirmation-list").forEach((list) => {
  const items = [...list.children];
  if (items.length > 5) createPager(items, 5, list, "Pagina's met open acties").show(items);
});

/* De filters en paginering van leeruitkomsten werken per student afzonderlijk. */
document.querySelectorAll(".coach-student-detail").forEach((detail) => {
  const search = detail.querySelector(".coachOutcomeSearch");
  const statusFilter = detail.querySelector(".coachOutcomeStatus");
  const outcomes = [...detail.querySelectorAll(".coach-outcome-card")];
  const noResults = detail.querySelector(".coach-outcomes-no-results");
  const pagination = detail.querySelector(".coach-outcome-pagination");
  const previousButton = detail.querySelector(".coachOutcomePrevious");
  const nextButton = detail.querySelector(".coachOutcomeNext");
  const pageLabel = detail.querySelector(".coach-outcome-page-label");
  const rangeLabel = detail.querySelector(".coach-outcome-range");

  if (!search || !statusFilter || !noResults) return;

  const pageSize = 5;
  let currentPage = 1;

  function filterOutcomes() {
    const searchTerm = search.value.trim().toLocaleLowerCase("nl");
    const selectedStatus = statusFilter.value;
    const filteredOutcomes = outcomes.filter((outcome) => {
      const matchesSearch = outcome.dataset.outcomeSearch.includes(searchTerm);
      const matchesStatus = !selectedStatus || outcome.dataset.outcomeStatus === selectedStatus;

      return matchesSearch && matchesStatus;
    });
    const totalPages = Math.max(1, Math.ceil(filteredOutcomes.length / pageSize));
    currentPage = Math.min(currentPage, totalPages);
    const firstVisible = (currentPage - 1) * pageSize;
    const visiblePage = new Set(filteredOutcomes.slice(firstVisible, firstVisible + pageSize));

    outcomes.forEach((outcome) => {
      outcome.classList.toggle("hidden", !visiblePage.has(outcome));
    });

    noResults.classList.toggle("hidden", filteredOutcomes.length > 0);

    if (pagination && previousButton && nextButton && pageLabel && rangeLabel) {
      pagination.classList.toggle("hidden", filteredOutcomes.length <= pageSize);
      previousButton.disabled = currentPage === 1;
      nextButton.disabled = currentPage === totalPages;
      pageLabel.textContent = `Pagina ${currentPage} van ${totalPages}`;
      rangeLabel.textContent = filteredOutcomes.length
        ? `Toont ${firstVisible + 1}–${Math.min(firstVisible + pageSize, filteredOutcomes.length)} van ${filteredOutcomes.length}`
        : "";
    }
  }

  // Sla met fetch op, zodat de coach op de detailpagina van de student blijft.
  detail.querySelectorAll(".coach-outcome-form").forEach((form) => {
    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      const button = form.querySelector(".coach-save-button");
      const feedback = form.querySelector(".coach-save-feedback");
      const card = form.closest(".coach-outcome-card");
      const statusBadge = card.querySelector("[data-outcome-status-label]");
      const originalButtonContent = button.innerHTML;

      button.disabled = true;
      button.textContent = "Opslaan...";
      feedback.textContent = "";
      feedback.classList.remove("is-error");

      try {
        const response = await fetch(form.action, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: new FormData(form),
        });
        const result = await response.json();

        if (!response.ok) {
          const validationMessage = Object.values(result.errors ?? {}).flat()[0];
          throw new Error(validationMessage ?? result.message ?? "Opslaan is niet gelukt.");
        }

        const status = form.elements.status.value;
        const statusLabels = {
          niet_gestart: "Niet gestart",
          gestart: "Gestart",
          in_ontwikkeling: "In ontwikkeling",
          behaald: "Behaald",
        };
        card.dataset.outcomeStatus = status;
        statusBadge.className = `coach-status-badge coach-status-${status}`;
        statusBadge.textContent = statusLabels[status];
        feedback.textContent = result.message ?? "De voortgang is bijgewerkt.";

        // Werk de samenvatting bij op basis van de actuele waarden in de formulieren.
        const totalHours = [...detail.querySelectorAll('input[name="gevalideerde_uren"]')]
          .reduce((total, input) => total + Number(input.value), 0);
        const totalHoursLabel = detail.querySelector("[data-coach-total-hours]");
        totalHoursLabel.firstChild.textContent = `${totalHours.toLocaleString("nl")} `;
        const targetHours = Number(detail.dataset.coachTargetHours);
        const percentage = targetHours > 0 ? Math.min(100, Math.round((totalHours / targetHours) * 100)) : 0;
        const progressValue = detail.querySelector("[data-coach-progress-value]");
        const progressBar = detail.querySelector("[data-coach-progress-bar]");
        progressValue.textContent = `${percentage}%`;
        progressBar.setAttribute("aria-valuenow", percentage);
        progressBar.firstElementChild.style.width = `${percentage}%`;

        const studentCard = document.querySelector(`[data-coach-student-card="${detail.dataset.studentId}"]`);
        if (studentCard) {
          const targetHours = Number(studentCard.dataset.coachTargetHours);
          const percentage = targetHours > 0 ? Math.min(100, Math.round((totalHours / targetHours) * 100)) : 0;
          studentCard.dataset.studentProgress = String(percentage);
          studentCard.querySelector("[data-coach-student-hours]").textContent = totalHours.toLocaleString("nl");
          studentCard.querySelector("[data-coach-student-progress]").setAttribute("aria-valuenow", percentage);
          studentCard.querySelector("[data-coach-student-progress] > span").style.width = `${percentage}%`;
          studentCard.querySelector("[data-coach-student-progress-value]").textContent = `${percentage}%`;
        }

        filterOutcomes();
      } catch (error) {
        feedback.textContent = error.message || "Opslaan is niet gelukt. Probeer het opnieuw.";
        feedback.classList.add("is-error");
      } finally {
        button.disabled = false;
        button.innerHTML = originalButtonContent;
      }
    });
  });

  search.addEventListener("input", () => {
    currentPage = 1;
    filterOutcomes();
  });
  statusFilter.addEventListener("change", () => {
    currentPage = 1;
    filterOutcomes();
  });
  previousButton?.addEventListener("click", () => {
    currentPage -= 1;
    filterOutcomes();
  });
  nextButton?.addEventListener("click", () => {
    currentPage += 1;
    filterOutcomes();
  });

  filterOutcomes();
});

/* Houd de navigatiebalk op kleine schermen inklapbaar. */
const coachMobileMenuButton = document.getElementById("coachMobileMenuButton");
const coachSidebar = coachMobileMenuButton?.closest(".side");

if (coachMobileMenuButton && coachSidebar) {
  coachMobileMenuButton.addEventListener("click", () => {
    const isExpanded = coachMobileMenuButton.getAttribute("aria-expanded") === "true";

    coachMobileMenuButton.setAttribute("aria-expanded", String(!isExpanded));
    coachSidebar.classList.toggle("is-open", !isExpanded);
  });

  document.getElementById("nav").addEventListener("click", (event) => {
    if (!event.target.closest("[data-route]") || window.innerWidth > 760) return;

    coachMobileMenuButton.setAttribute("aria-expanded", "false");
    coachSidebar.classList.remove("is-open");
  });
}
