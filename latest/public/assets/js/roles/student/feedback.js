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

let feedbackDetailTrigger = null;
function openOutcomeFeedbackDetail(id, fromList = false) {
  const item = getFeedback().find((feedback) => String(feedback.id) === String(id));
  if (!item) return;
  feedbackDetailTrigger = document.activeElement;
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
  const modal = document.getElementById('outcomeFeedbackDetailModal');
  if (!modal.querySelector('.feedback-detail-art')) {
    const art = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    art.setAttribute('class', 'feedback-detail-art');
    art.setAttribute('viewBox', '0 0 200 140');
    art.setAttribute('fill', 'none');
    art.setAttribute('aria-hidden', 'true');
    art.innerHTML = '<path d="M0 139q25-42 62-29 27-61 67-40 46-25 71 11v58Z" fill="#e1f6ef"/><path d="m64 32 98 7q6 1 5 7l-10 64-103-9 8-64q0-5 2-5Z" fill="#c5efdf" stroke="#1ca987" stroke-width="4"/><path d="m68 38 93 7-8 56-93-7Z" fill="#f5fffb"/><path d="m54 101 103 9-15 14-107-10v-5Z" fill="#008b72"/><path d="m54 101 103 9-11 9-108-10Z" fill="#c1eadf"/><path d="M81 66v17m13-27v28m13-21v22" stroke="#159b7b" stroke-width="7"/><path d="m121 58 26 2m-27 7 22 2m-23 8 16 1" stroke="#7acbb6" stroke-width="3"/><path d="m47 29-11-6m18-5-6-11m21 9-1-11" stroke="#008e75" stroke-width="3" stroke-linecap="round"/>';
    modal.querySelector('.reflection-detail-modal-head').appendChild(art);
  }
  document.body.appendChild(modal);
  modal.querySelector('.reflection-modal-actions')?.remove();
  modal.querySelector('.reflection-detail-footer .dark').classList.toggle('hidden', fromList);
  if (fromList) {
    const actions = document.createElement('div');
    actions.className = 'reflection-modal-actions';
    const button = document.createElement('button');
    button.className = 'button';
    button.type = 'button';
    button.textContent = 'Feedback verwijderen';
    button.onclick = () => deleteFeedback(item.id);
    actions.appendChild(button);
    modal.querySelector('.reflection-detail-footer').prepend(actions);
  }
  modal.classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => modal.querySelector('.modal-close').focus({preventScroll:true}));
}

function closeOutcomeFeedbackDetail() {
  document.getElementById("outcomeFeedbackDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
  feedbackDetailTrigger?.focus({preventScroll:true});
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

function selectFeedbackOutcomeFilter(kind) {
  document.getElementById(kind === 'Softskills' ? 'feedbackHardOutcome' : 'feedbackSoftOutcome').value = 'all';
  renderFeedback();
}
function openLatestFeedback() {
  const item = getFeedback().find((item) => outcomes.find((o) => o.code === item.code)?.phase === feedbackPhaseFilter);
  if (item) openOutcomeFeedbackDetail(item.id, true);
}
function renderFeedback() {
  if (feedbackPhaseFilter === 'all') feedbackPhaseFilter = 'OF';
  const all = getFeedback();
  const current = all.filter((item) => outcomes.find((o) => o.code === item.code)?.phase === feedbackPhaseFilter);
  for (const [kind,id] of [['Softskills','feedbackSoftOutcome'],['Hardskills','feedbackHardOutcome']]) {
    const select = document.getElementById(id), previous = select.value;
    const available = outcomes.filter((o) => o.phase === feedbackPhaseFilter && o.kind === kind);
    select.innerHTML = '<option value="all">Alle ' + (kind === 'Softskills' ? 'soft' : 'hard') + ' skills</option>' + available.map((o) => '<option value="' + esc(o.code) + '">' + esc(o.short) + '</option>').join('');
    select.value = available.some((o) => o.code === previous) ? previous : 'all';
  }
  const search = document.getElementById('feedbackSearch').value.trim().toLowerCase();
  const type = document.getElementById('feedbackTypeFilter').value;
  const skill = ['feedbackSoftOutcome','feedbackHardOutcome'].map((id) => document.getElementById(id).value).find((v) => v !== 'all');
  const list = current.filter((item) => (type === 'all' || item.type === type) && (!skill || skill === item.code) && [item.title,item.person,item.code,outcomes.find((o) => o.code === item.code)?.short,item.positive,item.improve,item.action].join(' ').toLowerCase().includes(search));
  document.getElementById('feedbackCount').textContent = all.length + ' ' + (all.length === 1 ? 'feedbackmoment' : 'feedbackmomenten');
  document.getElementById('feedbackPhaseFilter').innerHTML = phases.map(([code,name]) => '<button type="button" aria-pressed="' + (code === feedbackPhaseFilter) + '" class="reflection-filter-button ' + (code === feedbackPhaseFilter ? 'active' : '') + '" data-feedback-phase="' + code + '"><i class="reflection-phase-icon" aria-hidden="true">' + (code === 'OF' ? '✓' : code === 'VF' ? '◇' : '⚑') + '</i><span class="reflection-phase-copy"><strong>' + esc(name) + '</strong><small>' + feedbackCountForPhase(all,code) + ' feedbackmomenten</small></span><i class="reflection-phase-indicator" aria-hidden="true"></i></button>').join('');
  document.getElementById('feedbackLatestTitle').textContent = current[0]?.title || 'Nog geen feedback';
  document.getElementById('feedbackLatestDate').textContent = current[0] ? current[0].date + ' · ' + current[0].person : 'Voeg je eerste feedbackmoment toe.';
  document.getElementById('feedbackLatestButton').disabled = !current.length;
  document.getElementById('feedbackNextCopy').textContent = 'Vraag of geef feedback in de ' + (phases.find(([code]) => code === feedbackPhaseFilter)?.[1] || 'gekozen fase') + ' en maak een vervolgafspraak.';
  document.getElementById('feedbackList').innerHTML = list.map((item) => {
    const outcome = outcomes.find((o) => o.code === item.code);
    return '<button type="button" class="feedback-table-row" data-feedback-id="' + esc(item.id) + '"><span class="reflection-row-title"><i aria-hidden="true">↗</i><span><strong>' + esc(item.title) + '</strong><small>' + esc(outcome?.short || '') + '</small></span></span><span class="reflection-row-label">' + (item.type === 'given' ? 'Gegeven' : 'Ontvangen') + '</span><span class="reflection-row-code">' + esc(outcome?.short || item.code) + '</span><span class="reflection-row-person">' + esc(item.person) + '</span><span class="reflection-row-date">' + esc(item.date) + '</span><span aria-hidden="true">›</span></button>';
  }).join('') || '<div class="empty-state"><h2>Geen feedback gevonden</h2><p>Pas je filters aan of voeg feedback toe.</p></div>';
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
  if (!returnToOutcome) {
    document.getElementById('feedbackPhase').value = feedbackPhaseFilter === 'all' ? 'OF' : feedbackPhaseFilter;
    updateFeedbackOutcomeOptions();
  }
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
    closeOutcomeFeedbackDetail();
    feedbackItems = feedbackItems.filter((item) => item.id !== id);
    renderFeedback();
    renderOutcomeFeedback();
  } catch (error) {
    hint(error.message);
  }
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("feedbackForm").classList.contains("hidden")) closeFeedbackForm();
  if (event.key === 'Escape' && !document.getElementById('outcomeFeedbackDetailModal').classList.contains('hidden')) closeOutcomeFeedbackDetail();
});
document.addEventListener('click', (event) => {
  const row = event.target.closest('[data-feedback-id]');
  if (row) openOutcomeFeedbackDetail(row.dataset.feedbackId, true);
  const phase = event.target.closest('[data-feedback-phase]');
  if (phase) filterFeedbackByPhase(phase.dataset.feedbackPhase);
});

