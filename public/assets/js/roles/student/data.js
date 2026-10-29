// Zet de door Laravel opgehaalde databasegegevens om naar studentweergaven.
// In dit bestand staan bewust geen onderwijs- of demogegevens meer hardcoded.

const studentData = window.LARAVEL_DATA || {};

let outcomes = Array.isArray(studentData.outcomes) ? [...studentData.outcomes] : [];
let phases = Array.isArray(studentData.phases) ? [...studentData.phases] : [];
let assignments = Array.isArray(studentData.assignments) ? [...studentData.assignments] : [];
let records = Array.isArray(studentData.records) ? [...studentData.records] : [];
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

// Bepaal aandachtspunten vanuit de laagste voortgang in de database.
const attentionOutcomesByPhase = Object.fromEntries(
  phases.map(([phaseCode]) => {
    const lowestProgress = outcomes
      .filter((outcome) => outcome.phase === phaseCode && !isOutcomeCompleted(outcome))
      .sort((left, right) => (left.pct ?? 0) - (right.pct ?? 0))
      .slice(0, 1)
      .map((outcome) => outcome.code);

    return [phaseCode, lowestProgress];
  }),
);
const attentionOutcomeCodes = Object.values(attentionOutcomesByPhase).flat();

const assignmentStatuses = {
  active: "Actief",
  review: "Beoordeling",
  completed: "Afgerond",
};

// Extra gegevens van urenregistraties worden eveneens door Laravel aangeleverd.
const registrationMeta = records.map((record) => [
  record[5] || "Datum onbekend",
  record[6] || "Beoordelaar onbekend",
]);

function isOutcomeCompleted(outcome) {
  return outcome.status === "behaald" || Number(outcome.pct) >= 80;
}