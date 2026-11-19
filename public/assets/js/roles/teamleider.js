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

document.querySelectorAll("[data-teamleader-teacher-toggle]").forEach((button) => {
  button.addEventListener("click", () => {
    const groupsRow = document.getElementById(button.getAttribute("aria-controls"));
    const expanded = button.getAttribute("aria-expanded") === "true";
    button.setAttribute("aria-expanded", String(!expanded));
    groupsRow.classList.toggle("hidden", expanded);
  });
});

document.querySelectorAll("[data-teamleader-open-group]").forEach((button) => {
  button.addEventListener("click", () => {
    const group = document.getElementById(`teamleader-group-card-${button.dataset.teamleaderOpenGroup}`);
    if (!group) return;

    show("managerGroups", true);
    group.open = true;
    group.scrollIntoView({ behavior: "smooth", block: "start" });
  });
});

document.querySelectorAll("[data-teamleader-phase-filter]").forEach((filter) => {
  const studentDetails = filter.closest("details");
  const outcomeGrid = studentDetails.querySelector("[data-teamleader-outcome-grid]");
  const outcomes = [...outcomeGrid.querySelectorAll("[data-teamleader-outcome]")];
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

    const sortedOutcomes = [...outcomes];
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
  };

  filter.addEventListener("change", updatePhaseProgress);
  sortFilter.addEventListener("change", updatePhaseProgress);
});
