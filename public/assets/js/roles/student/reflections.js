// Reflecties tonen, toevoegen en verwijderen.

// -----------------------------------------------------------------------------
// Reflecties
// -----------------------------------------------------------------------------
let reflections = Array.isArray(window.LARAVEL_DATA?.reflections)
  ? [...window.LARAVEL_DATA.reflections]
  : [];

function getReflections() {
  return reflections;
}

function renderOutcomeReflections() {
  if (!currentOutcomeCode) return;
  const list = getReflections().filter((item) => item.code === currentOutcomeCode),
    container = document.getElementById("outcomeReflectionList");
  container.innerHTML = list.length
    ? list
        .map(
          (item) =>
            '<button class="outcome-reflection-row" type="button" onclick="openOutcomeReflectionDetail(\'' +
            esc(item.id) +
            "')\"><div><strong>" +
            esc(item.title) +
            '</strong><span class="reflection-date">' +
            esc(item.date) +
            '</span></div><span class="row-arrow">→</span></button>',
        )
        .join("")
    : '<div class="outcome-reflection-empty compact"><strong>Nog geen reflectie</strong><p>Voeg je eerste reflectie toe.</p></div>';
}

function openOutcomeReflectionDetail(id) {
  const item = getReflections().find((reflection) => reflection.id === id);
  if (!item) return;
  document.getElementById("reflectionDetailDate").textContent = item.date;
  document.getElementById("reflectionDetailTitle").textContent = item.title;
  document.getElementById("reflectionDetailCode").textContent = item.code;
  document.getElementById("reflectionDetailContent").innerHTML = item.situation
    ? "<section><span>1</span><div><h3>Situatie</h3><p>" +
      esc(item.situation) +
      "</p></div></section><section><span>2</span><div><h3>Wat ging goed?</h3><p>" +
      esc(item.good) +
      "</p></div></section><section><span>3</span><div><h3>Wat kan beter?</h3><p>" +
      esc(item.improve) +
      "</p></div></section><section><span>4</span><div><h3>Volgende stap</h3><p>" +
      esc(item.next) +
      "</p></div></section>"
    : "<section><span>1</span><div><h3>Reflectie</h3><p>" + esc(item.text || "") + "</p></div></section>";
  document.getElementById("outcomeReflectionDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#outcomeReflectionDetailModal .modal-close").focus());
}

function closeOutcomeReflectionDetail() {
  document.getElementById("outcomeReflectionDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function keepOnlyOneOutcomeItemOpen(item) {
  if (!item.open) return;
  document.querySelectorAll(".outcome-reflection-detail[open],.outcome-feedback-detail[open]").forEach((other) => {
    if (other !== item) other.open = false;
  });
}

function viewReflectionsForPhase(phaseCode) {
  show("reflections");
  filterReflectionsByPhase(phaseCode);
}

function openReflectionForCurrentOutcome() {
  const outcome = outcomes.find((o) => o.code === currentOutcomeCode);
  show("reflections");
  if (outcome) {
    document.getElementById("reflectionPhase").value = outcome.phase;
    updateReflectionOutcomeOptions();
    document.getElementById("reflectionOutcome").value = outcome.code;
    showReflectionOutcomePreview();
  }
  openReflectionForm(true);
}

function renderReflections() {
  const all = getReflections(),
    list =
      reflectionPhaseFilter === "all"
        ? all
        : all.filter((item) => {
            const outcome = outcomes.find((o) => o.code === item.code);
            return outcome && outcome.phase === reflectionPhaseFilter;
          });
  document.getElementById("reflectionCount").textContent = all.length + " " + (all.length === 1 ? "REFLECTIE" : "REFLECTIES");
  document.getElementById("reflectionPhaseFilter").innerHTML = [["all", "Alle fases"], ...phases.map(([code, name]) => [code, name])]
    .map(
      ([code, name]) =>
        '<button type="button" class="reflection-filter-button ' +
        (reflectionPhaseFilter === code ? "active" : "") +
        '" onclick="filterReflectionsByPhase(\'' +
        code +
        "')\">" +
        name +
        "<span>" +
        getReflectionCountForPhase(all, code) +
        "</span></button>",
    )
    .join("");
  document.getElementById("reflectionList").innerHTML = list.length
    ? list
        .map((item) => {
          const outcome = outcomes.find((o) => o.code === item.code),
            phaseName = outcome ? (phases.find((p) => p[0] === outcome.phase) || [])[1] : "Onbekende fase",
            sections = item.situation
              ? '<div class="reflection-sections"><div><b>1. Situatie</b><p>' +
                esc(item.situation) +
                "</p></div><div><b>2. Wat ging goed?</b><p>" +
                esc(item.good) +
                "</p></div><div><b>3. Wat kan beter?</b><p>" +
                esc(item.improve) +
                "</p></div><div><b>4. Volgende stap</b><p>" +
                esc(item.next) +
                "</p></div></div>"
              : '<div class="legacy-reflection"><b>Terugblik</b><p>' + esc(item.text || "") + "</p></div>";
          return (
            '<details class="reflection-accordion"><summary><div class="reflection-summary"><div class="reflection-summary-top"><span class="reflection-date">' +
            esc(item.date) +
            '</span><span class="reflection-phase">' +
            esc(phaseName) +
            '</span><span class="skill-tag ' +
            (outcome && outcome.kind === "Softskills" ? "soft" : "") +
            '">' +
            esc(outcome ? outcome.kind : "Leeruitkomst") +
            "</span></div><h2>" +
            esc(item.title) +
            "</h2><strong>" +
            esc(outcome ? outcome.short : "Leeruitkomst") +
            '</strong><span class="code">' +
            esc(item.code) +
            '</span></div><span class="accordion-toggle" aria-hidden="true">⌄</span></summary><div class="reflection-accordion-body">' +
            sections +
            '<div class="reflection-actions"><button class="icon-button" type="button" onclick="deleteReflection(\'' +
            esc(item.id) +
            "')\">Reflectie verwijderen</button></div></div></details>"
          );
        })
        .join("")
    : '<div class="empty-state"><span class="empty-icon">✦</span><h2>Geen reflecties in deze fase</h2><p>Kies een andere fase of schrijf een nieuwe reflectie.</p></div>';
}

function getReflectionCountForPhase(list, phaseCode) {
  if (phaseCode === "all") return list.length;
  return list.filter((item) => {
    const outcome = outcomes.find((o) => o.code === item.code);
    return outcome && outcome.phase === phaseCode;
  }).length;
}

function filterReflectionsByPhase(phaseCode) {
  reflectionPhaseFilter = phaseCode;
  renderReflections();
}

async function saveReflection(event) {
  event.preventDefault();
  const title = document.getElementById("reflectionTitle").value.trim();
  const code = document.getElementById("reflectionOutcome").value;
  const situation = document.getElementById("reflectionSituation").value.trim(),
    good = document.getElementById("reflectionGood").value.trim(),
    improve = document.getElementById("reflectionImprove").value.trim(),
    next = document.getElementById("reflectionNext").value.trim();
  const message = document.getElementById("reflectionMessage");
  if (!title || !code || [situation, good, improve, next].some((value) => value.length < 10)) {
    message.textContent = "Vul alle stappen in en schrijf bij iedere vraag minimaal 10 tekens.";
    message.className = "form-message error";
    return;
  }
  try {
    const saved = await apiRequest(window.LARAVEL_API.reflectionStore, {
      method: "POST",
      body: JSON.stringify({ title, code, situation, good, improve, next }),
    });
    reflections.unshift(saved);
    event.target.reset();
    message.textContent = "Je reflectie is bewaard.";
    message.className = "form-message success";
    updateReflectionOutcomeOptions();
    renderReflections();
    renderOutcomeReflections();
    const returnToOutcome = reflectionReturnToOutcome;
    closeReflectionForm();
    if (returnToOutcome) show("outcomeDetail");
  } catch (error) {
    message.textContent = error.message;
    message.className = "form-message error";
  }
}

function renderReflectionOptions() {
  updateReflectionOutcomeOptions();
}

function updateReflectionOutcomeOptions() {
  const phaseCode = document.getElementById("reflectionPhase").value,
    select = document.getElementById("reflectionOutcome"),
    phaseName = (phases.find((p) => p[0] === phaseCode) || [])[1] || "";
  select.innerHTML =
    '<option value="">Kies een leeruitkomst</option><optgroup label="' +
    phaseName +
    '">' +
    outcomes
      .filter((o) => o.phase === phaseCode)
      .map((o) => '<option value="' + o.code + '">' + esc(o.short) + " — " + o.code + "</option>")
      .join("") +
    "</optgroup>";
  showReflectionOutcomePreview();
}

function showReflectionOutcomePreview() {
  const outcome = outcomes.find((o) => o.code === document.getElementById("reflectionOutcome").value),
    preview = document.getElementById("reflectionOutcomePreview");
  if (!outcome) {
    preview.innerHTML = '<span class="small dim">Na je keuze verschijnt hier de naam, code en omschrijving.</span>';
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

function openReflectionForm(returnToOutcome = false) {
  reflectionReturnToOutcome = returnToOutcome;
  const form = document.getElementById("reflectionForm");
  form.classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.getElementById("reflectionTitle").focus());
}

function closeReflectionForm() {
  document.getElementById("reflectionForm").classList.add("hidden");
  document.body.classList.remove("modal-open");
  reflectionReturnToOutcome = false;
}

async function deleteReflection(id) {
  try {
    await apiRequest(apiUrl(window.LARAVEL_API.reflectionDelete, id), { method: "DELETE" });
    reflections = reflections.filter((item) => item.id !== id);
    renderReflections();
    renderOutcomeReflections();
  } catch (error) {
    hint(error.message);
  }
}

