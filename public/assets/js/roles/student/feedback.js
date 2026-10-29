// Feedback tonen, toevoegen en verwijderen.

// -----------------------------------------------------------------------------
// Feedback
// -----------------------------------------------------------------------------
let feedbackItems = Array.isArray(window.LARAVEL_DATA?.feedback)
  ? [...window.LARAVEL_DATA.feedback]
  : [];

function getFeedback() {
  return feedbackItems;
}

function renderOutcomeFeedback() {
  if (!currentOutcomeCode) return;
  const list = getFeedback().filter((item) => item.code === currentOutcomeCode),
    container = document.getElementById("outcomeFeedbackList");
  container.innerHTML = list.length
    ? list
        .map(
          (item) =>
            '<button class="outcome-feedback-row" type="button" onclick="openOutcomeFeedbackDetail(\'' +
            esc(item.id) +
            '\')"><div><span class="feedback-type ' +
            item.type +
            '">' +
            (item.type === "given" ? "Gegeven" : "Ontvangen") +
            "</span><strong>" +
            esc(item.title) +
            "</strong><small>" +
            esc(item.date) +
            " · " +
            esc(item.person) +
            '</small></div><span class="row-arrow">→</span></button>',
        )
        .join("")
    : '<div class="outcome-reflection-empty compact"><strong>Nog geen feedback</strong><p>Voeg feedback bij deze leeruitkomst toe.</p></div>';
}

function openOutcomeFeedbackDetail(id) {
  const item = getFeedback().find((feedback) => feedback.id === id);
  if (!item) return;
  const type = document.getElementById("feedbackDetailType");
  type.textContent = item.type === "given" ? "Gegeven" : "Ontvangen";
  type.className = "feedback-type " + item.type;
  document.getElementById("feedbackDetailDate").textContent = item.date;
  document.getElementById("feedbackDetailTitle").textContent = item.title;
  document.getElementById("feedbackDetailPerson").textContent = (item.type === "given" ? "Aan: " : "Van: ") + item.person;
  document.getElementById("feedbackDetailCode").textContent = item.code;
  document.getElementById("feedbackDetailContent").innerHTML =
    "<section><span>✓</span><div><h3>Wat gaat goed?</h3><p>" +
    esc(item.positive) +
    "</p></div></section><section><span>↗</span><div><h3>Wat kan beter?</h3><p>" +
    esc(item.improve) +
    "</p></div></section><section><span>→</span><div><h3>Vervolgafspraak</h3><p>" +
    esc(item.action) +
    "</p></div></section>";
  document.getElementById("outcomeFeedbackDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#outcomeFeedbackDetailModal .modal-close").focus());
}

function closeOutcomeFeedbackDetail() {
  document.getElementById("outcomeFeedbackDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function openFeedbackForCurrentOutcome() {
  const outcome = outcomes.find((o) => o.code === currentOutcomeCode);
  show("feedback");
  if (outcome) {
    document.getElementById("feedbackPhase").value = outcome.phase;
    updateFeedbackOutcomeOptions();
    document.getElementById("feedbackOutcome").value = outcome.code;
    showFeedbackOutcomePreview();
  }
  openFeedbackForm(true);
}

function feedbackCountForPhase(list, phaseCode) {
  if (phaseCode === "all") return list.length;
  return list.filter((item) => {
    const outcome = outcomes.find((o) => o.code === item.code);
    return outcome && outcome.phase === phaseCode;
  }).length;
}

function filterFeedbackByPhase(phaseCode) {
  feedbackPhaseFilter = phaseCode;
  renderFeedback();
}

function renderFeedback() {
  const all = getFeedback(),
    typeFilter = document.getElementById("feedbackTypeFilter").value,
    list = all.filter((item) => {
      const outcome = outcomes.find((o) => o.code === item.code),
        phaseMatch = feedbackPhaseFilter === "all" || (outcome && outcome.phase === feedbackPhaseFilter),
        typeMatch = typeFilter === "all" || item.type === typeFilter;
      return phaseMatch && typeMatch;
    });
  document.getElementById("feedbackCount").textContent = all.length + " " + (all.length === 1 ? "FEEDBACKMOMENT" : "FEEDBACKMOMENTEN");
  document.getElementById("feedbackPhaseFilter").innerHTML = [["all", "Alle fases"], ...phases.map(([code, name]) => [code, name])]
    .map(
      ([code, name]) =>
        '<button type="button" class="reflection-filter-button ' +
        (feedbackPhaseFilter === code ? "active" : "") +
        '" onclick="filterFeedbackByPhase(\'' +
        code +
        "')\">" +
        name +
        "<span>" +
        feedbackCountForPhase(all, code) +
        "</span></button>",
    )
    .join("");
  document.getElementById("feedbackList").innerHTML = list.length
    ? list
        .map((item) => {
          const outcome = outcomes.find((o) => o.code === item.code),
            phaseName = outcome ? (phases.find((p) => p[0] === outcome.phase) || [])[1] : "Onbekende fase",
            typeLabel = item.type === "given" ? "Gegeven" : "Ontvangen";
          return (
            '<details class="feedback-accordion"><summary><div class="feedback-summary"><div class="feedback-summary-top"><span class="feedback-type ' +
            item.type +
            '">' +
            typeLabel +
            '</span><span class="reflection-phase">' +
            esc(phaseName) +
            '</span><span class="reflection-date">' +
            esc(item.date) +
            "</span></div><h2>" +
            esc(item.title) +
            "</h2><p>" +
            esc(item.type === "given" ? "Aan: " : "Van: ") +
            esc(item.person) +
            "</p><strong>" +
            esc(outcome ? outcome.short : "Leeruitkomst") +
            '</strong><span class="code">' +
            esc(item.code) +
            '</span></div><span class="accordion-toggle" aria-hidden="true">⌄</span></summary><div class="feedback-body"><div class="feedback-points"><div class="feedback-point positive"><span>✓</span><div><b>Wat gaat goed?</b><p>' +
            esc(item.positive) +
            '</p></div></div><div class="feedback-point improve"><span>↗</span><div><b>Wat kan beter?</b><p>' +
            esc(item.improve) +
            '</p></div></div><div class="feedback-point action"><span>→</span><div><b>Vervolgafspraak</b><p>' +
            esc(item.action) +
            '</p></div></div></div><div class="feedback-actions"><button class="icon-button" type="button" onclick="deleteFeedback(\'' +
            esc(item.id) +
            "')\">Feedback verwijderen</button></div></div></details>"
          );
        })
        .join("")
    : '<div class="empty-state"><span class="empty-icon">↗</span><h2>Geen feedback gevonden</h2><p>Pas je filters aan of voeg een nieuw feedbackmoment toe.</p></div>';
}

function updateFeedbackOutcomeOptions() {
  const phaseCode = document.getElementById("feedbackPhase").value,
    select = document.getElementById("feedbackOutcome"),
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
  showFeedbackOutcomePreview();
}

function showFeedbackOutcomePreview() {
  const outcome = outcomes.find((o) => o.code === document.getElementById("feedbackOutcome").value),
    preview = document.getElementById("feedbackOutcomePreview");
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

function openFeedbackForm(returnToOutcome = false) {
  feedbackReturnToOutcome = returnToOutcome;
  document.getElementById("feedbackForm").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.getElementById("feedbackPerson").focus());
}

function closeFeedbackForm() {
  document.getElementById("feedbackForm").classList.add("hidden");
  document.body.classList.remove("modal-open");
  feedbackReturnToOutcome = false;
}

async function saveFeedback(event) {
  event.preventDefault();
  const type = document.getElementById("feedbackType").value,
    person = document.getElementById("feedbackPerson").value.trim(),
    title = document.getElementById("feedbackTitle").value.trim(),
    code = document.getElementById("feedbackOutcome").value,
    positive = document.getElementById("feedbackPositive").value.trim(),
    improve = document.getElementById("feedbackImprove").value.trim(),
    action = document.getElementById("feedbackAction").value.trim(),
    message = document.getElementById("feedbackMessage");
  if (!person || !title || !code || [positive, improve, action].some((value) => value.length < 10)) {
    message.textContent = "Vul alle velden in en schrijf bij ieder feedbackpunt minimaal 10 tekens.";
    message.className = "form-message error";
    return;
  }
  try {
    const saved = await apiRequest(window.LARAVEL_API.feedbackStore, {
      method: "POST",
      body: JSON.stringify({ type, person, title, code, positive, improve, action }),
    });
    feedbackItems.unshift(saved);
    event.target.reset();
    message.textContent = "De feedback is bewaard.";
    message.className = "form-message success";
    updateFeedbackOutcomeOptions();
    renderFeedback();
    renderOutcomeFeedback();
    const returnToOutcome = feedbackReturnToOutcome;
    closeFeedbackForm();
    if (returnToOutcome) show("outcomeDetail");
  } catch (error) {
    message.textContent = error.message;
    message.className = "form-message error";
  }
}

async function deleteFeedback(id) {
  try {
    await apiRequest(apiUrl(window.LARAVEL_API.feedbackDelete, id), { method: "DELETE" });
    feedbackItems = feedbackItems.filter((item) => item.id !== id);
    renderFeedback();
    renderOutcomeFeedback();
  } catch (error) {
    hint(error.message);
  }
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("feedbackForm").classList.contains("hidden")) closeFeedbackForm();
});

