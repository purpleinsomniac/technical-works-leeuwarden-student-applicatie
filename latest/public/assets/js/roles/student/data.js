// Zet de door Laravel opgehaalde databasegegevens om naar studentweergaven.
// In dit bestand staan bewust geen onderwijs- of demogegevens meer hardcoded.

const studentData = window.LARAVEL_DATA || {};

let outcomes = Array.isArray(studentData.outcomes) ? [...studentData.outcomes] : [];
let phases = Array.isArray(studentData.phases) ? [...studentData.phases] : [];
let assignments = Array.isArray(studentData.assignments) ? [...studentData.assignments] : [];
let records = Array.isArray(studentData.records) ? [...studentData.records] : [];
// Elk project en zijn urenregistratie horen bij precies één fase.
assignments.forEach((project) => { project.phases = [project.phase || project.phases?.[0] || "OF"]; });
records.forEach((record) => {
  const project = assignments.find((item) => Number(item.id) === Number(record.assignmentId));
  record.phases = [project?.phases[0] || record.phases?.[0] || "OF"];
});
let hoursByYear = studentData.hoursByYear || {};
const hourApprovals = Array.isArray(studentData.hourApprovals) ? [...studentData.hourApprovals] : [];

// De huidige opdracht wordt afgeleid uit de opdrachten en leeruitkomsten uit MySQL.
const currentWorkByPhase = {};
for (const phaseItem of phases) {
  const phaseCode = phaseItem[0];
  const assignment = assignments.find((item) => item.phase === phaseCode && item.status !== "completed")
    || assignments.find((item) => item.phase === phaseCode);
  const firstOutcome = outcomes.find((item) => item.phase === phaseCode);
  const linkedOutcome = assignment
    ? outcomes.find((item) => assignment.outcomeCodes.includes(item.code))
    : firstOutcome;

  currentWorkByPhase[phaseCode] = {
    title: assignment?.title || "Nog geen opdracht",
    type: assignment?.type || "Geen actieve opdracht",
    code: assignment?.outcomeCodes?.[0] || firstOutcome?.code || "",
    pct: linkedOutcome?.pct || 0,
  };
}

const assignmentStatuses = {
  active: "Actief",
  review: "Beoordeling",
  completed: "Afgerond",
};

function isOutcomeCompleted(outcome) {
  return outcome.status === "behaald" || Number(outcome.hours) >= Number(outcome.target) && Number(outcome.target) > 0;
}
