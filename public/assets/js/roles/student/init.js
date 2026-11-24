// Initialisatie en archivering van leeruitkomsten.
function getArchivedOutcomes() {
  try {
    const saved = JSON.parse(loadLocalState(archivedOutcomesKey) || "[]");
    return Array.isArray(saved) ? saved : [];
  } catch {
    return [];
  }
}

function updateOutcomeArchiveControls() {
  const archived = getArchivedOutcomes().includes(currentOutcomeCode);
  const button = document.getElementById("archiveOutcomeButton");
  const notice = document.getElementById("outcomeArchiveNotice");
  if (!button || !notice) return;
  notice.classList.toggle("hidden", !archived);
  button.textContent = archived ? "Terugzetten bij actieve leeruitkomsten" : "Leeruitkomst archiveren";
  button.classList.toggle("restore", archived);
  button.setAttribute("aria-label", archived ? "Leeruitkomst uit archief terugzetten" : "Afgeronde leeruitkomst archiveren");
}

// Bewaar de archiefkeuze en toon daarna het bijbehorende catalogusfilter.
function toggleOutcomeArchive() {
  if (!currentOutcomeCode) return;
  const archived = getArchivedOutcomes();
  const wasArchived = archived.includes(currentOutcomeCode);
  const updated = wasArchived
    ? archived.filter((code) => code !== currentOutcomeCode)
    : [...archived, currentOutcomeCode];
  saveLocalState(archivedOutcomesKey, JSON.stringify(updated));
  const filter = document.getElementById("catalogArchiveFilter");
  filter.value = wasArchived ? "all" : "archived";
  renderCatalog();
  show("learningOutcomes");
  hint(wasArchived ? "De leeruitkomst staat weer bij je actieve leeruitkomsten." : "De leeruitkomst is gearchiveerd en blijft hier makkelijk terug te vinden.");
}

// Dit script wordt als laatste geladen en vult de studentweergaven.
if (window.LARAVEL_USER) {
  renderGoalOptions();
  initializeGoals();
  renderReflectionOptions();
  showReflectionOutcomePreview();
  updateFeedbackOutcomeOptions();
  renderDashboard();
  renderHours();
  renderHourApprovals();
  renderCatalog();
  renderReflections();
  renderFeedback();
  updateHoursProcessingReadState();
  initializeStudentNavigation();
}
