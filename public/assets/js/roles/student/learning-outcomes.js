// Leeruitkomsten, detailweergave en catalogusfilters.

function outcomePointTarget(outcome) {
  const target = Number(outcome.target);
  return target > 0 ? target.toLocaleString('nl-NL') + ' punten' : 'Nog niet vastgesteld';
}

function practicalExplanation(outcome) {
  let independence = "zelfstandig";
  if (outcome.phase === "OF") independence = "onder begeleiding";
  if (outcome.phase === "VF") independence = "met beperkte begeleiding";
  return outcome.kind === "Hardskills"
    ? "Je past deze vaardigheid " +
        independence +
        " toe in een concrete opdracht. Laat niet alleen het eindresultaat zien, maar leg ook uit welke aanpak, hulpmiddelen en keuzes je hebt gebruikt."
    : "Je laat deze vaardigheid zien tijdens samenwerking, communicatie en je eigen leerproces. Beschrijf een concrete situatie, jouw handelen, ontvangen feedback en wat je een volgende keer anders doet.";
}

// Vul het detailscherm en ververs de knop voor archiveren of herstellen.
function openOutcome(code) {
  const outcome = outcomes.find((item) => item.code === code);
  currentOutcomeCode = code;
  document.getElementById("outcomeBackButton").textContent = "← Terug naar leeruitkomsten";
  document.getElementById("outcomeTitle").textContent = outcome.short;
  document.getElementById("outcomeMeta").textContent = phases.find(([code]) => code === outcome.phase)[1];
  document.getElementById("outcomeCode").textContent = outcome.code;
  document.getElementById("outcomeText").textContent = outcome.description;
  document.getElementById("outcomePractical").textContent = practicalExplanation(outcome);
  renderOutcomePoints(outcome);
  renderOutcomeReflections();
  renderOutcomeFeedback();
  show("outcomeDetail");
  updateOutcomeArchiveControls();
}

function renderOutcomePoints(outcome) {
  const points = Number(outcome.hours);
  const target = Number(outcome.target);
  document.getElementById("detailPercent").textContent = pointPercentLabel(outcome.pct);
  document.getElementById("detailBar").style.width = pointPercentLabel(outcome.pct);
  document.getElementById("detailHours").textContent = (points || 0) + " punten";
  document.getElementById('detailPointsBadge').textContent = (points || 0) + ' / ' + (target || 0) + ' punten';
  document.getElementById("detailTarget").textContent = outcomePointTarget(outcome);

  const level = competencyLevel(getOutcomeProgress(outcome));
  const status = document.getElementById("detailStatus");
  status.textContent = level.label;
  status.dataset.level = level.key;
  document.getElementById("detailStatusDescription").textContent = level.detail;
}

function backFromOutcome() {
  show("learningOutcomes");
}

function getOutcomeProgress(outcome) {
  const percentage = Number(outcome.pct);
  return Number.isFinite(percentage) ? Math.max(0, Math.min(100, percentage)) : 0;
}

// Bepaal de status op basis van punten en voortgang.
function getOutcomeStatus(outcome) {
  return competencyLevel(getOutcomeProgress(outcome)).key;
}

function selectCatalogPhase(selectedPhase) {
  document.getElementById("catalogPhase").value = selectedPhase;
  renderCatalog();
}

// Bereken de voortgang voor de hele fase, los van de zoek- en statusfilters.
function renderCatalogSummary(selectedPhase, phaseOutcomes) {
  const total = phaseOutcomes.length;
  let completed = 0;
  let active = 0;
  for (const outcome of phaseOutcomes) {
    const status = getOutcomeStatus(outcome);
    if (status === "completed") completed++;
    if (status === "active") active++;
  }
  const todo = total - completed - active;
  const selected = phases.find(([code]) => code === selectedPhase);
  const percentage = selected?.[2] ?? 0;
  const earned = selected?.[3] ?? 0;

  document.getElementById("catalogTotalPill").textContent = total + " LEERUITKOMSTEN";
  document.getElementById("catalogProgressTitle").textContent = "Totale voortgang · " + studentPhaseName(selectedPhase, "Fase");
  document.getElementById("catalogSkillCount").textContent = completed + " van de " + total + " leeruitkomsten bekwaam";
  document.getElementById("catalogProgressPercent").textContent = pointPercentLabel(percentage);
  document.getElementById("catalogProgressText").textContent = earned + " / " + phasePointTarget.toLocaleString("nl-NL") + " punten";
  document.getElementById("catalogProgressBar").style.width = pointPercentLabel(percentage);
  document.getElementById("catalogProgressRing").style.setProperty("--catalog-progress", displayPointPercent(percentage) * 3.6 + "deg");
  document.getElementById("catalogCompletedCount").textContent = completed;
  document.getElementById("catalogActiveCount").textContent = active;
  document.getElementById("catalogTodoCount").textContent = todo;
  document.getElementById('devTotal').textContent = total;
  [['devCompletedPct',completed],['devActivePct',active],['devTodoPct',todo]].forEach(([id,count]) => document.getElementById(id).textContent = pointPercentLabel(total ? count / total * 100 : 0));
  document.getElementById('developmentSkills').innerHTML = ['Softskills','Hardskills'].map((kind) => {
    const list = phaseOutcomes.filter((item) => item.kind === kind);
    const budget = list.reduce((sum, item) => sum + Number(item.target), 0);
    const points = list.reduce((sum, item) => sum + Number(item.hours), 0);
    const pct = budget ? Math.min(100, 100 * points / budget) : 0;
    return '<div class="development-skill" data-level="' + competencyLevel(pct).key +
      '"><strong>' + (kind === 'Softskills' ? 'Soft skills' : 'Hard skills') +
      '</strong><span class="bar"><span style="width:' + pointPercentLabel(pct) + '"></span></span><b>' + pointPercentLabel(pct) + '</b></div>';
  }).join('');
  document.getElementById('developmentPhasePoints').innerHTML = phases.map(([code, name, percent, points]) => {
    const current = code === selectedPhase;
    const icon = {OF: 'target', VF: 'layout', AF: 'progress'}[code];
    return '<button type="button" class="development-phase-points' + (current ? ' selected' : '') +
      '" aria-pressed="' + current + '" onclick="selectCatalogPhase(\'' + code + '\')">' +
      '<span class="catalog-phase-icon" aria-hidden="true">' + uiIcon(icon) + '</span><span>' + esc(name) +
      '</span><b>' + Number(points).toLocaleString('nl-NL') + ' / ' + phasePointTarget.toLocaleString('nl-NL') +
      '</b><span class="catalog-score" data-level="' + competencyLevel(percent).key + '">' +
      pointPercentLabel(percent) + '</span></button>';
  }).join('');
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

// Pas de filters en sortering toe en groepeer de resultaten per vaardigheid.
function renderCatalog() {
  const search = document.getElementById("outcomeSearch").value.trim().toLowerCase();
  const selectedPhase = document.getElementById("catalogPhase").value;
  const selectedStatus = document.getElementById("catalogArchiveFilter").value;
  const selectedSort = document.getElementById("catalogSort").value;
  const archived = getArchivedOutcomes();

  const phaseOutcomes = outcomes.filter((outcome) => outcome.phase === selectedPhase);
  renderCatalogSummary(selectedPhase, phaseOutcomes);
  renderCatalogPhaseTabs(selectedPhase);

  const filtered = phaseOutcomes.filter((outcome) => {
    const isArchived = archived.includes(outcome.code);
    if (isArchived !== (selectedStatus === "archived")) return false;
    if (!isArchived && selectedStatus !== "all" && getOutcomeStatus(outcome) !== selectedStatus) return false;
    const searchable = (outcome.code + " " + outcome.short + " " + outcome.description).toLowerCase();
    return searchable.includes(search);
  });

  if (selectedSort === "progress-desc") filtered.sort((a, b) => getOutcomeProgress(b) - getOutcomeProgress(a));
  if (selectedSort === "progress-asc") filtered.sort((a, b) => getOutcomeProgress(a) - getOutcomeProgress(b));
  if (selectedSort === "alphabetical") filtered.sort((a, b) => a.short.localeCompare(b.short, "nl"));

  document.getElementById("archiveFilterNotice").classList.toggle("hidden", selectedStatus !== "archived");
  document.getElementById("catalogCount").textContent = filtered.length
    ? filtered.length + " leeruitkomsten"
    : "Geen leeruitkomsten gevonden";

  document.getElementById("catalogList").innerHTML = ['Softskills', 'Hardskills'].map((kind) => {
    const items = filtered.filter((outcome) => outcome.kind === kind);
    const earned = items.reduce((sum, item) => sum + Number(item.hours), 0);
    const target = items.reduce((sum, item) => sum + Number(item.target), 0);
    const progress = target ? Math.floor(10000 * earned / target) / 100 : 0;
    const isSoftSkill = kind === 'Softskills';
    const rows = items.map(renderCatalogOutcome).join('');
    const groupLevel = competencyLevel(progress);

    return '<details class="development-group ' + (isSoftSkill ? 'soft' : 'hard') + '" open>' +
      '<summary><span class="catalog-skill-icon" aria-hidden="true">' + uiIcon(isSoftSkill ? 'target' : 'layout') + '</span>' +
      '<h2>' + (isSoftSkill ? 'Soft skills' : 'Hard skills') + '</h2>' +
      '<small>' + items.length + ' leeruitkomsten</small>' +
      '<span class="catalog-score catalog-group-score" data-level="' + groupLevel.key + '"' +
      ' title="' + groupLevel.label + '">Gemiddeld: <b>' + pointPercentLabel(progress) + '</b></span></summary>' +
      (rows || '<p class="development-empty">Geen leeruitkomsten voor deze filters.</p>') +
      '</details>';
  }).join('');
}

function renderCatalogOutcome(outcome) {
  const progress = getOutcomeProgress(outcome);
  const level = competencyLevel(progress);
  const points = Number(outcome.hours) || 0;
  const target = Number(outcome.target);
  const pointsSuffix = target > 0 ? ' / ' + target : ' punten';
  return '<button class="development-outcome development-skill-link" type="button" onclick="openOutcome(\'' +
    outcome.code + '\')" data-level="' + level.key + '">' +
    '<span class="catalog-outcome-icon" aria-hidden="true">' + uiIcon('outcomes') + '</span>' +
    '<span class="development-outcome-title"><strong>' + esc(outcome.short) +
    '</strong><small>' + esc(outcome.code) + '</small></span>' +
    '<span class="development-row-bar"><span class="bar"><span style="width:' + pointPercentLabel(progress) +
    '"></span></span><b class="catalog-score">' + pointPercentLabel(progress) +
    '<span class="sr-only"> · ' + level.label + '</span></b></span>' +
    '<span class="development-points">' + points + pointsSuffix + '</span>' +
    '<span class="development-chevron" aria-hidden="true">›</span></button>';
}
