window.RoleApp.initializeRole({
  label: "Docent",
  routes: [
    ["Overzicht", "coachOverview"],
    ["Studenten", "coachStudents"],
    ["Leeractiviteiten", "coachActivities"],
    ["Leerdoelen", "coachGoals"],
    ["Projecten", "coachProjects"],
  ],
});
RoleSettings.initialize();

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

/* Pas de naam- en fasefilters toe voordat de gevonden studenten worden gepagineerd. */
const coachStudentSearch = document.getElementById("coachStudentSearch");
const coachPhaseFilter = document.getElementById("coachPhaseFilter");
const coachClassFilter = document.getElementById("coachClassFilter");
const coachStudentSort = document.getElementById("coachStudentSort");
const coachStudentCards = [...document.querySelectorAll(".coach-student-card[data-student-name]")];
const coachNoResults = document.getElementById("coachNoResults");
const coachStudentGrid = document.querySelector(".coach-student-grid");

if (coachStudentSearch && coachPhaseFilter && coachNoResults && coachStudentGrid) {
  const studentPager = createPager(coachStudentCards, 8, coachStudentGrid, "Pagina's met studenten");

  function filterCoachStudents() {
    const searchTerm = coachStudentSearch.value.trim().toLocaleLowerCase("nl");
    const selectedPhase = coachPhaseFilter.value;
    const selectedClass = coachClassFilter?.value ?? "";

    const matches = coachStudentCards.filter((card) => {
      const matchesName = card.dataset.studentName.includes(searchTerm);
      const matchesPhase = !selectedPhase || card.dataset.studentPhase === selectedPhase;
      const matchesClass = !selectedClass || card.dataset.studentClassIds.includes(`,${selectedClass},`);
      return matchesName && matchesPhase && matchesClass;
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
    coachNoResults.classList.toggle("hidden", matches.length > 0);
  }

  coachStudentSearch.addEventListener("input", filterCoachStudents);
  coachPhaseFilter.addEventListener("change", filterCoachStudents);
  coachClassFilter?.addEventListener("change", filterCoachStudents);
  coachStudentSort?.addEventListener("change", filterCoachStudents);
  filterCoachStudents();
}

window.openCoachPhase = function (phase) {
  show("coachStudents");
  if (coachClassFilter) coachClassFilter.value = "";
  if (!coachPhaseFilter) return;
  coachPhaseFilter.value = [...coachPhaseFilter.options].some((option) => option.value === phase) ? phase : "";
  coachPhaseFilter.dispatchEvent(new Event("change"));
};

window.openCoachClass = function (classId) {
  show("coachStudents");
  if (coachPhaseFilter) coachPhaseFilter.value = "";
  if (coachClassFilter) coachClassFilter.value = String(classId);
  coachClassFilter?.dispatchEvent(new Event("change"));
};

window.openCoachProjectQueue = function () {
  show("coachProjects");
  const statusFilter = document.getElementById("coachProjectStatusFilter");
  const search = document.getElementById("coachProjectSearch");
  if (statusFilter) statusFilter.value = "review";
  if (search) search.value = "";
  statusFilter?.dispatchEvent(new Event("change"));
};

window.openCoachProjects = function () {
  show("coachProjects");
  const statusFilter = document.getElementById("coachProjectStatusFilter");
  const search = document.getElementById("coachProjectSearch");
  if (statusFilter) statusFilter.value = "";
  if (search) search.value = "";
  statusFilter?.dispatchEvent(new Event("change"));
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
  coachProjectStatusFilter.addEventListener("change", filterCoachProjects);
  filterCoachProjects();
}

/* Keep the remaining read-only directories compact. */
document.querySelectorAll(".coach-directory-grid").forEach((grid) => {
  if (grid === coachProjectGrid) return;
  const cards = [...grid.children];
  createPager(cards, 6, grid, "Pagina's").show(cards);
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