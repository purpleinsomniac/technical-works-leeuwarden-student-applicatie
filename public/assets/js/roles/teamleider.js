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
  const progressRows = studentDetails.querySelectorAll("[data-teamleader-progress-phase]");
  const phaseAverage = studentDetails.querySelector("[data-teamleader-phase-average]");

  const updatePhaseProgress = () => {
    let percentages = [];
    progressRows.forEach((row) => {
      const visible = filter.value === "__all" || row.dataset.teamleaderProgressPhase === filter.value;
      row.classList.toggle("hidden", !visible);

      const percentage = row.dataset.teamleaderProgressPercentage;
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
    );
    phaseAverage.classList.add(
      average >= 75
        ? "teamleader-progress-high"
        : average >= 60
          ? "teamleader-progress-medium"
          : "teamleader-progress-low",
    );
  };

  filter.addEventListener("change", updatePhaseProgress);
});
