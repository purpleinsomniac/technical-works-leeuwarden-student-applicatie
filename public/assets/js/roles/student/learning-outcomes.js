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
  backButton.textContent = returnPage === "assignmentDetail" ? "← Terug naar opdracht" : "← Terug naar leeruitkomsten";
  currentOutcomeCode = code;
  document.getElementById("outcomeTitle").textContent = x.short;
  document.getElementById("outcomeMeta").textContent = phases.find((p) => p[0] === x.phase)[1];
  document.getElementById("outcomeCode").textContent = x.code;
  document.getElementById("outcomeText").textContent = x.description;
  document.getElementById("outcomePractical").textContent = practicalExplanation(x);
  document.getElementById("detailPercent").textContent = hasProgress ? x.pct + "%" : "—";
  document.getElementById("detailBar").style.width = (x.pct || 0) + "%";
  document.getElementById("detailHours").textContent = x.hours === null ? "Nog geen uren" : x.hours + " uur";
  document.getElementById("detailTarget").textContent = "80%";
  document.getElementById("detailStatus").textContent = !hasProgress
    ? "Nog niet gestart"
    : x.pct >= 80
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

function renderGoalOptions() {
  const select = document.getElementById("goalCodes"),
    phaseSelect = document.getElementById("goalPhase"),
    choices = document.getElementById("goalOutcomeChoices");
  if (!select || !phaseSelect || !choices) return;
  const previous = select.value,
    phaseCode = phaseSelect.value,
    list = outcomes.filter((o) => o.phase === phaseCode);
  select.innerHTML = list.map((o) => '<option value="' + o.code + '">' + esc(o.short) + " — " + o.code + "</option>").join("");
  if (list.some((o) => o.code === previous)) select.value = previous;
  const selected = select.value;
  document.getElementById("goalChoiceCount").textContent = list.length + " " + (list.length === 1 ? "leeruitkomst" : "leeruitkomsten");
  choices.innerHTML = list
    .map(
      (o) =>
        '<button type="button" role="option" aria-selected="' +
        (o.code === selected) +
        '" class="goal-outcome-choice ' +
        (o.code === selected ? "selected" : "") +
        '" onclick="selectGoalOutcome(\'' +
        o.code +
        '\')"><span class="goal-choice-number">' +
        String(o.number).padStart(2, "0") +
        "</span><span><strong>" +
        esc(o.short) +
        "</strong><small>" +
        (o.kind === "Hardskills" ? "Vakvaardigheid" : "Persoonlijke vaardigheid") +
        '</small><span class="code">' +
        esc(o.code) +
        '</span></span><span class="goal-choice-check">✓</span></button>',
    )
    .join("");
  showGoalOutcomePreview();
}

function selectGoalOutcome(code) {
  document.getElementById("goalCodes").value = code;
  renderGoalOptions();
}

function showGoalOutcomePreview() {
  const outcome = outcomes.find((o) => o.code === document.getElementById("goalCodes").value),
    preview = document.getElementById("goalOutcomePreview");
  if (!outcome) {
    preview.innerHTML = "";
    return;
  }
  preview.innerHTML =
    '<span class="small dim">Geselecteerde leeruitkomst</span><strong>' +
    esc(outcome.short) +
    '</strong><span class="code">' +
    esc(outcome.code) +
    "</span><p>" +
    esc(outcome.description) +
    "</p>";
}

function renderActiveGoal(goal = window.LARAVEL_DATA?.goals?.find((item) => item.status === "actief")) {
  if (!goal) return;
  document.getElementById("activeGoalStatus").textContent = "ACTIEF LEERDOEL";
  document.getElementById("activeGoalTitle").textContent = goal.title;
  document.getElementById("activeGoalDescription").textContent = goal.description;
  document.getElementById("activeGoalOutcome").textContent = goal.outcome || "Leeruitkomst";
  document.getElementById("activeGoalCode").textContent = goal.code || "—";
}

async function saveGoal() {
  const description = document.getElementById("goalText").value.trim();
  const code = document.getElementById("goalCodes").value;
  const message = document.getElementById("goalMessage");
  if (description.length < 10 || !code) {
    message.textContent = "Beschrijf je leerdoel in minimaal 10 tekens en kies een leeruitkomst.";
    message.className = "form-message error";
    return;
  }
  try {
    const goal = await apiRequest(window.LARAVEL_API.goalStore, {
      method: "POST",
      body: JSON.stringify({ description, code }),
    });
    renderActiveGoal(goal);
    document.getElementById("goalText").value = "";
    message.textContent = "Je leerdoel is opgeslagen.";
    message.className = "form-message success";
  } catch (error) {
    message.textContent = error.message;
    message.className = "form-message error";
  }
}

const catalogPageSize = 4;

function getOutcomeProgress(outcome) {
  const percentage = Number(outcome.pct);
  return Number.isFinite(percentage) ? Math.max(0, Math.min(100, percentage)) : 0;
}

function getOutcomeStatus(outcome) {
  const percentage = getOutcomeProgress(outcome);
  if (percentage >= 80) return "completed";
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
  document.getElementById("catalogList")?.scrollIntoView({ behavior: "smooth", block: "start" });
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
  const pagination = document.getElementById("catalogPagination");
  if (totalPages <= 1) {
    pagination.innerHTML = "";
    return;
  }

  let buttons =
    '<button type="button" aria-label="Vorige pagina" ' +
    (catalogPage === 1 ? "disabled" : 'onclick="setCatalogPage(' + (catalogPage - 1) + ')"') +
    ">‹</button>";

  for (let page = 1; page <= totalPages; page++) {
    buttons +=
      '<button type="button" class="' +
      (page === catalogPage ? "active" : "") +
      '" ' +
      (page === catalogPage ? 'aria-current="page"' : "") +
      ' onclick="setCatalogPage(' +
      page +
      ')">' +
      page +
      "</button>";
  }

  buttons +=
    '<button type="button" aria-label="Volgende pagina" ' +
    (catalogPage === totalPages ? "disabled" : 'onclick="setCatalogPage(' + (catalogPage + 1) + ')"') +
    ">›</button>";
  pagination.innerHTML = buttons;
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
    return matchesStatus && (!catalogAttentionOnly || attentionOutcomeCodes.includes(outcome.code)) && searchable.includes(search);
  });

  if (selectedSort === "progress-desc") filtered.sort((a, b) => getOutcomeProgress(b) - getOutcomeProgress(a));
  if (selectedSort === "progress-asc") filtered.sort((a, b) => getOutcomeProgress(a) - getOutcomeProgress(b));
  if (selectedSort === "alphabetical") filtered.sort((a, b) => a.short.localeCompare(b.short, "nl"));

  const totalPages = Math.max(1, Math.ceil(filtered.length / catalogPageSize));
  catalogPage = Math.min(catalogPage, totalPages);
  const start = (catalogPage - 1) * catalogPageSize;
  const visibleOutcomes = filtered.slice(start, start + catalogPageSize);

  document.getElementById("catalogAttentionNotice").classList.toggle("hidden", !catalogAttentionOnly);
  document.getElementById("archiveFilterNotice").classList.toggle("hidden", selectedStatus !== "archived");
  document.getElementById("catalogCount").textContent = filtered.length
    ? "Toont " + (start + 1) + " t/m " + Math.min(start + catalogPageSize, filtered.length) + " van de " + filtered.length + " leeruitkomsten"
    : "Geen leeruitkomsten gevonden";

  document.getElementById("catalogList").innerHTML = visibleOutcomes.length
    ? visibleOutcomes
        .map((outcome) => {
          const percentage = getOutcomeProgress(outcome);
          const status = getOutcomeStatus(outcome);
          const statusIcon = status === "completed" ? "✓" : status === "active" ? "◷" : "";
          const hours = outcome.hours === null ? 0 : outcome.hours;
          return (
            '<button class="catalog-outcome-row ' +
            status +
            (archived.includes(outcome.code) ? " archived" : "") +
            '" type="button" onclick="openOutcome(\'' +
            outcome.code +
            '\')"><span class="catalog-status-icon" aria-hidden="true">' +
            statusIcon +
            '</span><span class="catalog-outcome-name"><strong>' +
            esc(outcome.short) +
            '</strong><span class="code catalog-code">' +
            esc(outcome.code) +
            '</span></span><span class="catalog-outcome-description">' +
            esc(outcome.description) +
            '</span><span class="catalog-row-progress"><span class="catalog-progress-line"><span class="bar"><span style="width:' +
            percentage +
            '%"></span></span><b>' +
            percentage +
            '%</b></span><small>' +
            hours +
            ' uur · Richtwaarde 80%</small></span><span class="catalog-row-arrow" aria-hidden="true">›</span></button>'
          );
        })
        .join("")
    : '<div class="catalog-empty-archive"><h2>Geen leeruitkomsten gevonden</h2><p>Pas je zoekopdracht of filters aan.</p></div>';

  renderCatalogPagination(filtered.length ? totalPages : 0);
}

function showAttentionOutcomes() {
  catalogAttentionOnly = true;
  document.getElementById("catalogPhase").value = phase;
  document.getElementById("outcomeSearch").value = "";
  document.getElementById("catalogArchiveFilter").value = "all";
  catalogPage = 1;
  renderCatalog();
  show("learningOutcomes");
}

function clearAttentionOutcomes() {
  catalogAttentionOnly = false;
  catalogPage = 1;
  renderCatalog();
}
