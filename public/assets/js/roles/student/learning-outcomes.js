function outcomePointTarget(outcome) {
  const target = Number(outcome.target);
  return target > 0 ? target.toLocaleString('nl-NL') + ' punten' : 'Nog niet vastgesteld';
}
// Leeruitkomsten, leerdoelen, filters en paginering.

function practicalExplanation(outcome) {
  const independence = outcome.phase === "OF" ? "onder begeleiding" : outcome.phase === "VF" ? "met beperkte begeleiding" : "zelfstandig";
  return outcome.kind === "Hardskills"
    ? "Je past deze vaardigheid " +
        independence +
        " toe in een concrete opdracht. Laat niet alleen het eindresultaat zien, maar leg ook uit welke aanpak, hulpmiddelen en keuzes je hebt gebruikt."
    : "Je laat deze vaardigheid zien tijdens samenwerking, communicatie en je eigen leerproces. Beschrijf een concrete situatie, jouw handelen, ontvangen feedback en wat je een volgende keer anders doet.";
}

function openOutcome(code, returnPage = "learningOutcomes") {
  const x = outcomes.find((o) => o.code === code),
    hasProgress = x.pct !== null;
  outcomeReturnPage = returnPage;
  const backButton = document.getElementById("outcomeBackButton");
  backButton.textContent = returnPage === "assignmentDetail"
    ? "← Terug naar opdracht"
    : returnPage === "hours"
      ? "← Terug naar urenregistratie"
      : "← Terug naar leeruitkomsten";
  currentOutcomeCode = code;
  document.getElementById("outcomeTitle").textContent = x.short;
  document.getElementById("outcomeMeta").textContent = phases.find((p) => p[0] === x.phase)[1];
  document.getElementById("outcomeCode").textContent = x.code;
  document.getElementById("outcomeText").textContent = x.description;
  document.getElementById("outcomePractical").textContent = practicalExplanation(x);
  document.getElementById("detailPercent").textContent = (Number(x.pct) || 0) + "%";
  document.getElementById("detailBar").style.width = Math.min(100, x.pct || 0) + "%";
  document.getElementById("detailHours").textContent = (Number(x.hours) || 0) + " punten";
  document.getElementById('detailPointsBadge').textContent = (Number(x.hours) || 0) + ' / ' + (Number(x.target) || 0) + ' punten';
  document.getElementById("detailTarget").textContent = outcomePointTarget(x);
  document.getElementById("detailStatus").textContent = !hasProgress
    ? "Nog niet gestart"
    : Number(x.hours) >= Number(x.target)
      ? "Richtwaarde behaald"
      : x.pct >= 40
        ? "In ontwikkeling"
        : "Gestart";
  renderOutcomeReflections();
  renderOutcomeFeedback();
  show("outcomeDetail");
}

function backFromOutcome() {
  show(outcomeReturnPage);
}

const catalogPageSize = 4;

function getOutcomeProgress(outcome) {
  const percentage = Number(outcome.pct);
  return Number.isFinite(percentage) ? Math.max(0, Math.min(100, percentage)) : 0;
}

function getOutcomeStatus(outcome) {
  const percentage = getOutcomeProgress(outcome);
  if (Number(outcome.hours) >= Number(outcome.target) && Number(outcome.target) > 0) return "completed";
  if (percentage > 0) return "active";
  return "todo";
}

function resetCatalogAndRender() {
  catalogPage = 1;
  renderCatalog();
}

function selectCatalogPhase(selectedPhase) {
  document.getElementById("catalogPhase").value = selectedPhase;
  catalogPage = 1;
  renderCatalog();
}

function setCatalogPage(pageNumber) {
  catalogPage = pageNumber;
  renderCatalog();
}

function renderCatalogSummary(selectedPhase) {
  const selectedPhaseData = phases.find((item) => item[0] === selectedPhase);
  const phaseOutcomes = outcomes.filter((outcome) => outcome.phase === selectedPhase);
  const total = phaseOutcomes.length;
  const completed = phaseOutcomes.filter((outcome) => getOutcomeStatus(outcome) === "completed").length;
  const active = phaseOutcomes.filter((outcome) => getOutcomeStatus(outcome) === "active").length;
  const todo = total - completed - active;
  const percentage = total ? Math.round((completed / total) * 100) : 0;

  document.getElementById("catalogTotalPill").textContent = total + " LEERUITKOMSTEN";
  document.getElementById("catalogProgressTitle").textContent = "Jouw voortgang · " + (selectedPhaseData?.[1] || "Fase");
  document.getElementById("catalogProgressPercent").textContent = percentage + "%";
  document.getElementById("catalogProgressText").textContent = completed + " van de " + total + " leeruitkomsten afgerond";
  document.getElementById("catalogProgressBar").style.width = percentage + "%";
  document.getElementById("catalogProgressRing").style.setProperty("--catalog-progress", percentage * 3.6 + "deg");
  document.getElementById("catalogCompletedCount").textContent = completed;
  document.getElementById("catalogActiveCount").textContent = active;
  document.getElementById("catalogTodoCount").textContent = todo;
  document.getElementById('devTotal').textContent = total;
  [['devCompletedPct',completed],['devActivePct',active],['devTodoPct',todo]].forEach(([id,count]) => document.getElementById(id).textContent = (total ? Math.round(count / total * 100) : 0) + '%');
  document.getElementById('developmentSkills').innerHTML = ['Softskills','Hardskills'].map((kind) => {
    const list = phaseOutcomes.filter((item) => item.kind === kind);
    const pct = list.length ? Math.round(list.reduce((sum,item) => sum + getOutcomeProgress(item),0)/list.length) : 0;
    return '<div class="development-skill ' + (kind === 'Softskills' ? 'soft' : '') + '"><strong>' + (kind === 'Softskills' ? 'Soft skills' : 'Hard skills') + '</strong><span class="bar"><span style="width:' + pct + '%"></span></span><b>' + pct + '%</b></div>';
  }).join('');
  document.getElementById('developmentPhasePoints').innerHTML = phases.map(([code,name]) => '<div class="development-phase-points"><span>' + esc(name) + '</span><b>' + outcomes.filter((item) => item.phase === code).reduce((sum,item) => sum + (Number(item.hours)||0),0).toLocaleString('nl-NL') + ' punten</b></div>').join('');
}

function renderCatalogPhaseTabs(selectedPhase) {
  document.getElementById("catalogPhaseTabs").innerHTML = phases
    .map(
      (item) =>
        '<button class="learning-phase-tab ' +
        (item[0] === selectedPhase ? "active" : "") +
        '" type="button" onclick="selectCatalogPhase(\'' +
        item[0] +
        "')\">" +
        esc(item[1]) +
        "</button>",
    )
    .join("");
}

function renderCatalogPagination(totalPages) {
  document.getElementById("catalogPagination").innerHTML = pageButtons(catalogPage, totalPages, 'setCatalogPage');
}

function renderCatalog() {
  const search = document.getElementById("outcomeSearch").value.trim().toLowerCase();
  const selectedPhase = document.getElementById("catalogPhase").value;
  const selectedStatus = document.getElementById("catalogArchiveFilter").value;
  const selectedSort = document.getElementById("catalogSort").value;
  const archived = getArchivedOutcomes();

  renderCatalogSummary(selectedPhase);
  renderCatalogPhaseTabs(selectedPhase);

  const phaseOutcomes = outcomes.filter((outcome) => outcome.phase === selectedPhase);

  let filtered = phaseOutcomes.filter((outcome) => {
    const isArchived = archived.includes(outcome.code);
    const matchesStatus =
      selectedStatus === "archived" ? isArchived : !isArchived && (selectedStatus === "all" || getOutcomeStatus(outcome) === selectedStatus);
    const searchable = (outcome.code + " " + outcome.short + " " + outcome.description).toLowerCase();
    return matchesStatus && searchable.includes(search);
  });

  if (selectedSort === "progress-desc") filtered.sort((a, b) => getOutcomeProgress(b) - getOutcomeProgress(a));
  if (selectedSort === "progress-asc") filtered.sort((a, b) => getOutcomeProgress(a) - getOutcomeProgress(b));
  if (selectedSort === "alphabetical") filtered.sort((a, b) => a.short.localeCompare(b.short, "nl"));

  const totalPages = 1;
  catalogPage = Math.min(catalogPage, totalPages);
  const start = (catalogPage - 1) * catalogPageSize;
  const visibleOutcomes = filtered;

  document.getElementById("archiveFilterNotice").classList.toggle("hidden", selectedStatus !== "archived");
  document.getElementById("catalogCount").textContent = filtered.length
    ? filtered.length + " leeruitkomsten"
    : "Geen leeruitkomsten gevonden";

  document.getElementById("catalogList").innerHTML = ['Softskills','Hardskills'].map((kind) => {
    const items = visibleOutcomes.filter((outcome) => outcome.kind === kind);
    const pct = items.length ? Math.round(items.reduce((sum,item) => sum + getOutcomeProgress(item),0) / items.length) : 0;
    return '<details class="development-group ' + (kind === 'Softskills' ? 'soft' : 'hard') + '" open><summary><span class="dev-metric-icon">' + (kind === 'Softskills' ? '♧' : '▱') + '</span><h2>' + (kind === 'Softskills' ? 'Soft skills' : 'Hard skills') + '</h2><small>' + items.length + ' leeruitkomsten</small><span class="development-group-total"><span class="bar"><span style="width:' + pct + '%"></span></span><b>' + pct + '%</b></span></summary><div class="development-table-head"><span>Leeruitkomst</span><span>Voortgang</span><span>Punten</span></div>' +
    (items.map((outcome) => {
      const progress = getOutcomeProgress(outcome), status = getOutcomeStatus(outcome);
      return '<button class="development-outcome development-skill-link" type="button" onclick="openOutcome(\'' + outcome.code + '\')"><span class="development-status ' + status + '">' + (status === 'completed' ? '✓' : status === 'active' ? '◷' : '') + '</span><span class="development-outcome-title"><strong>' + esc(outcome.short) + '</strong><small>' + esc(outcome.code) + '</small></span><span class="development-row-bar"><span class="bar"><span style="width:' + progress + '%"></span></span><b>' + progress + '%</b></span><span class="development-points">' + (Number(outcome.hours)||0) + (Number(outcome.target) > 0 ? ' / ' + Number(outcome.target) : ' punten') + '</span><span class="development-chevron" aria-hidden="true">›</span></button>';
    }).join('') || '<p class="development-empty">Geen leeruitkomsten voor deze filters.</p>') + '</details>';
  }).join('');

  renderCatalogPagination(filtered.length ? totalPages : 0);
}
