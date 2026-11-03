// Archiveren en alle studentonderdelen na het laden initialiseren.

// -----------------------------------------------------------------------------
// Archiveren van leeruitkomsten
// -----------------------------------------------------------------------------
function getArchivedOutcomes() {
  try {
    const saved = JSON.parse(loadLocalState(archivedOutcomesKey) || "[]");
    return Array.isArray(saved) ? saved : [];
  } catch (error) {
    return [];
  }
}

function isOutcomeArchived(code) {
  return getArchivedOutcomes().includes(code);
}

function updateOutcomeArchiveControls() {
  const archived = isOutcomeArchived(currentOutcomeCode),
    button = document.getElementById("archiveOutcomeButton"),
    notice = document.getElementById("outcomeArchiveNotice");
  if (!button || !notice) return;
  notice.classList.toggle("hidden", !archived);
  button.textContent = archived ? "Terugzetten bij actieve leeruitkomsten" : "Leeruitkomst archiveren";
  button.classList.toggle("restore", archived);
  button.setAttribute("aria-label", archived ? "Leeruitkomst uit archief terugzetten" : "Afgeronde leeruitkomst archiveren");
}

function toggleOutcomeArchive() {
  if (!currentOutcomeCode) return;
  const archived = getArchivedOutcomes(),
    wasArchived = archived.includes(currentOutcomeCode),
    updated = wasArchived ? archived.filter((code) => code !== currentOutcomeCode) : [...archived, currentOutcomeCode];
  saveLocalState(archivedOutcomesKey, JSON.stringify(updated));
  const filter = document.getElementById("catalogArchiveFilter");
  filter.value = wasArchived ? "all" : "archived";
  catalogPage = 1;
  renderCatalog();
  show("learningOutcomes");
  hint(wasArchived ? "De leeruitkomst staat weer bij je actieve leeruitkomsten." : "De leeruitkomst is gearchiveerd en blijft hier makkelijk terug te vinden.");
}

const openOutcomeWithArchiveState = openOutcome;
openOutcome = function (code) {
  openOutcomeWithArchiveState(code);
  updateOutcomeArchiveControls();
};

const renderHoursWithUpdatedLabels = renderHours;
renderHours = function () {
  renderHoursWithUpdatedLabels();
  const emptyTitle = document.querySelector("#hoursList .empty-state h3");
  if (emptyTitle) emptyTitle.textContent = "Nog geen uren";
};

if (window.LARAVEL_USER) {
  if (role === "student") {
    renderGoalOptions();
    renderActiveGoal();
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
  }

  login();
}
