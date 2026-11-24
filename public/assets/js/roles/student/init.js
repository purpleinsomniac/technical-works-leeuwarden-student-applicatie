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
  window.addEventListener("focus", refreshPointProgress);
  window.setInterval(() => {
    if (!document.hidden) refreshPointProgress();
  }, 10000);
}

let pointRefreshInProgress = false;
let pointRefreshQueued = false;

// Haal ook wijzigingen van de docent op zonder een open detailpagina te sluiten.
async function refreshPointProgress() {
  const url = studentData.pointProgressUrl;
  if (!url) return;
  if (pointRefreshInProgress) {
    pointRefreshQueued = true;
    return;
  }
  pointRefreshInProgress = true;

  try {
    const snapshot = await apiRequest(url);
    if (!Array.isArray(snapshot.outcomes) || !Array.isArray(snapshot.phases)) return;
    if (JSON.stringify(outcomes) === JSON.stringify(snapshot.outcomes) &&
        JSON.stringify(phases) === JSON.stringify(snapshot.phases)) return;

    outcomes.splice(0, outcomes.length, ...snapshot.outcomes);
    phases.splice(0, phases.length, ...snapshot.phases);
    renderDashboard();
    renderCatalog();
    if (!document.getElementById("hours").classList.contains("hidden")) renderHours(false);
    const currentOutcome = outcomes.find((item) => item.code === currentOutcomeCode);
    if (currentOutcome) renderOutcomePoints(currentOutcome);
  } catch {
    // Behoud de laatste meting als de verbinding tijdelijk wegvalt.
  } finally {
    pointRefreshInProgress = false;
    if (pointRefreshQueued) {
      pointRefreshQueued = false;
      refreshPointProgress();
    }
  }
}
