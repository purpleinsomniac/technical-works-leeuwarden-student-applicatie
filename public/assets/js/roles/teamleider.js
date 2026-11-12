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
  };

  filter.addEventListener("change", updatePhaseProgress);
});
