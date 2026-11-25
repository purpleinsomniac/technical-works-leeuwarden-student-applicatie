window.RoleApp.initializeRole({
  label: "Teamleider",
  routes: [
    ["Groepsoverzicht", "managerGroups"],
    ["Docenten", "managerTeachers"],
    ["Rapportages", "managerReports"],
    ["Instellingen", "managerPreferencesSettings"],
  ],
});
RoleSettings.initialize();

const teamleaderNav = document.getElementById("nav");
const groupOverviewRoute = teamleaderNav.querySelector('[data-route="managerGroups"]');
const groupOverviewLink = document.createElement("a");
groupOverviewLink.className = groupOverviewRoute.className;
groupOverviewLink.dataset.route = groupOverviewRoute.dataset.route;
groupOverviewLink.href = window.LARAVEL_TEAMLEADER_ROUTES.overview;
groupOverviewLink.textContent = groupOverviewRoute.textContent;
groupOverviewLink.classList.toggle("active", window.LARAVEL_TEAMLEADER_PAGE === "overview");
if (window.LARAVEL_TEAMLEADER_PAGE === "overview") groupOverviewLink.setAttribute("aria-current", "page");
groupOverviewRoute.replaceWith(groupOverviewLink);

const groupRoute = (groupId) => window.LARAVEL_TEAMLEADER_ROUTES.group.replace("__GROUP_ID__", groupId);
const studentRoute = (groupId, studentId) => window.LARAVEL_TEAMLEADER_ROUTES.student
  .replace("__GROUP_ID__", groupId)
  .replace("__STUDENT_ID__", studentId);

if (window.LARAVEL_TEAMLEADER_GROUP) {
  const groupLink = document.createElement("a");
  groupLink.className = "nav teamleader-subnav";
  groupLink.dataset.route = "managerGroups";
  groupLink.href = groupRoute(window.LARAVEL_TEAMLEADER_GROUP.id);
  groupLink.textContent = `| ${window.LARAVEL_TEAMLEADER_GROUP.name}`;
  groupLink.classList.toggle("active", window.LARAVEL_TEAMLEADER_PAGE === "group");
  if (window.LARAVEL_TEAMLEADER_PAGE === "group") groupLink.setAttribute("aria-current", "page");
  groupOverviewLink.insertAdjacentElement("afterend", groupLink);

  if (window.LARAVEL_TEAMLEADER_STUDENT) {
    const studentLink = document.createElement("a");
    studentLink.className = "nav teamleader-subnav teamleader-student-subnav";
    studentLink.dataset.route = "managerGroups";
    studentLink.href = studentRoute(
      window.LARAVEL_TEAMLEADER_GROUP.id,
      window.LARAVEL_TEAMLEADER_STUDENT.id,
    );
    studentLink.textContent = `| ${window.LARAVEL_TEAMLEADER_STUDENT.name}`;
    studentLink.classList.toggle("active", window.LARAVEL_TEAMLEADER_PAGE === "student");
    if (window.LARAVEL_TEAMLEADER_PAGE === "student") studentLink.setAttribute("aria-current", "page");
    groupLink.insertAdjacentElement("afterend", studentLink);
  }
}

const setTeacherExpanded = (button, expanded) => {
  const groupsRow = document.getElementById(button.getAttribute("aria-controls"));
  if (!groupsRow) return;

  button.setAttribute("aria-expanded", String(expanded));
  groupsRow.classList.toggle("hidden", !expanded);
};

document.querySelectorAll("[data-teamleader-teacher-toggle]").forEach((button) => {
  button.addEventListener("click", () => {
    const expanded = button.getAttribute("aria-expanded") === "true";
    setTeacherExpanded(button, !expanded);
  });
});

document.querySelectorAll("[data-teamleader-open-teacher]").forEach((button) => {
  button.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    const teacherToggle = document.getElementById(
      `teamleader-teacher-toggle-${button.dataset.teamleaderOpenTeacher}`,
    );
    if (!teacherToggle) return;

    show("managerTeachers", true);
    setTeacherExpanded(teacherToggle, true);
    teacherToggle.scrollIntoView({ behavior: "smooth", block: "center" });
  });
});

document.querySelectorAll("[data-teamleader-open-group]").forEach((button) => {
  button.addEventListener("click", () => {
    window.location.assign(groupRoute(button.dataset.teamleaderOpenGroup));
  });
});

document.querySelectorAll("[data-teamleader-group-summary]").forEach((summary) => {
  summary.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    const groupId = summary.dataset.teamleaderGroupSummary;
    const destination = window.LARAVEL_TEAMLEADER_PAGE === "group"
      ? window.LARAVEL_TEAMLEADER_ROUTES.overview
      : groupRoute(groupId);
    window.location.assign(destination);
  });
});

document.querySelectorAll("[data-teamleader-student-summary]").forEach((summary) => {
  summary.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (window.LARAVEL_TEAMLEADER_PAGE === "student") {
      window.location.assign(groupRoute(window.LARAVEL_TEAMLEADER_GROUP.id));
      return;
    }

    window.location.assign(studentRoute(
      window.LARAVEL_TEAMLEADER_GROUP.id,
      summary.dataset.teamleaderStudentSummary,
    ));
  });
});

document.querySelectorAll("[data-teamleader-phase-filter]").forEach((filter) => {
  const studentDetails = filter.closest("details") || filter.closest(".teamleader-group-details");
  const outcomeGrids = [...studentDetails.querySelectorAll("[data-teamleader-outcome-grid]")];
  const outcomes = outcomeGrids.flatMap((grid) => [...grid.querySelectorAll("[data-teamleader-outcome]")]);
  const originalOrder = new Map(outcomes.map((outcome, index) => [outcome, index]));
  const sortFilter = studentDetails.querySelector("[data-teamleader-outcome-sort]");
  const phaseAverage = studentDetails.querySelector("[data-teamleader-phase-average]");

  const updatePhaseProgress = () => {
    let percentages = [];
    outcomes.forEach((outcome) => {
      const visible = filter.value === "__all" || outcome.dataset.teamleaderProgressPhase === filter.value;
      outcome.classList.toggle("hidden", !visible);

      const percentage = outcome.dataset.teamleaderProgressPercentage;
      if (visible && percentage !== "") percentages.push(Number(percentage));
    });
    const average = percentages.length
      ? Math.round(percentages.reduce((sum, percentage) => sum + percentage, 0) / percentages.length)
      : 0;
    const selectedPhase = filter.options[filter.selectedIndex].textContent;
    const scope = filter.value === "__all" ? "over alle fases" : `in ${selectedPhase}`;
    phaseAverage.textContent = `Gemiddelde voortgang ${scope}: ${average}%`;
    phaseAverage.classList.remove(
      "teamleader-progress-high",
      "teamleader-progress-medium",
      "teamleader-progress-low",
      "teamleader-progress-NVT",
      "teamleader-progress-purple",
      "teamleader-progress-blue",
    );
    const groupPhase = phaseAverage.dataset.teamleaderGroupPhase || "";
    const groupGoalValue = Number(phaseAverage.dataset.teamleaderGroupGoal);
    const phaseRanks = {
      "Oriëntatiefase": 1,
      "Verdiepingsfase": 2,
      "Afrondingsfase": 3,
    };
    const groupPhaseRank = phaseRanks[groupPhase] || 0;
    const selectedPhaseRank = phaseRanks[selectedPhase] || 0;
    const previousPhase = Boolean(groupPhaseRank && selectedPhaseRank && selectedPhaseRank < groupPhaseRank);
    const nextPhase = Boolean(groupPhaseRank && selectedPhaseRank && selectedPhaseRank > groupPhaseRank);
    const outsideGroupPhase = Boolean(groupPhase && filter.value !== "__all" && filter.value !== groupPhase);
    let progressClass = "teamleader-progress-NVT";
    if (filter.value !== "__all" && previousPhase && average < 100) {
      progressClass = "teamleader-progress-purple";
    } else if (filter.value !== "__all" && nextPhase && average > 10) {
      progressClass = "teamleader-progress-blue";
    } else if (!outsideGroupPhase && phaseAverage.dataset.teamleaderGroupGoal !== "") {
      progressClass = average >= groupGoalValue
        ? "teamleader-progress-high"
        : average >= groupGoalValue - 15
          ? "teamleader-progress-medium"
          : "teamleader-progress-low";
    }
    phaseAverage.classList.add(progressClass);

    outcomeGrids.forEach((outcomeGrid) => {
      const sortedOutcomes = [...outcomeGrid.querySelectorAll("[data-teamleader-outcome]")];
      if (sortFilter.value !== "default") {
        const direction = sortFilter.value === "ascending" ? 1 : -1;
        sortedOutcomes.sort((first, second) => {
          const firstValue = first.dataset.teamleaderProgressPercentage;
          const secondValue = second.dataset.teamleaderProgressPercentage;
          if (firstValue === "" || secondValue === "") {
            if (firstValue === secondValue) return originalOrder.get(first) - originalOrder.get(second);
            return firstValue === "" ? 1 : -1;
          }

          return direction * (Number(firstValue) - Number(secondValue))
            || originalOrder.get(first) - originalOrder.get(second);
        });
      }
      sortedOutcomes.forEach((outcome) => outcomeGrid.append(outcome));
    });
  };

  filter.addEventListener("change", updatePhaseProgress);
  sortFilter.addEventListener("change", updatePhaseProgress);
  updatePhaseProgress();
});
