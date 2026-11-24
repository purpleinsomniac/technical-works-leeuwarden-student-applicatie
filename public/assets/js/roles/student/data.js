// Bereid de door Laravel aangeleverde gegevens voor op gebruik in de studentweergaven.

const studentData = window.LARAVEL_DATA || {};

// Kopieer de lijst zodat een andere sortering de bronlijst niet wijzigt.
function copyStudentList(key) {
  const items = studentData[key];
  return Array.isArray(items) ? [...items] : [];
}

const outcomes = copyStudentList("outcomes");
const phases = copyStudentList("phases");
const assignments = copyStudentList("assignments");
const records = copyStudentList("records");

// Elk project en zijn urenregistratie horen bij precies één fase.
for (const project of assignments) {
  project.phases = [project.phase || project.phases?.[0] || "OF"];
}
for (const record of records) {
  const project = assignments.find((item) => Number(item.id) === Number(record.assignmentId));
  record.phases = [project?.phases[0] || record.phases?.[0] || "OF"];
}
const hourApprovals = copyStudentList("hourApprovals");

// Bewaar de bestaande keuze per fase, ook wanneer een projectstatus later verandert.
const currentWorkByPhase = Object.fromEntries(phases.map(([phaseCode]) => {
  const assignment = assignments.find((item) => item.phase === phaseCode && item.status !== "completed")
    || assignments.find((item) => item.phase === phaseCode);
  return [phaseCode, assignment];
}));

function studentPhaseName(code, fallback = code) {
  return phases.find(([phaseCode]) => phaseCode === code)?.[1] || fallback;
}
