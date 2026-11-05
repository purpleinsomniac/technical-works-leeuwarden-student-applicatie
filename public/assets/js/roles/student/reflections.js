let reflectionTypeFilter = 'all';
function selectReflectionOutcomeFilter(kind) {
  document.getElementById(kind === 'Softskills' ? 'reflectionHardOutcome' : 'reflectionSoftOutcome').value = 'all';
  renderReflections();
}
let reflectionSkillFilter = 'Hardskills';
function selectReflectionSkill(kind) {
  reflectionSkillFilter = kind;
  renderReflectionOutcomePicker();
}
function chooseReflectionOutcome(code) {
  document.getElementById('reflectionOutcome').value = code;
  showReflectionOutcomePreview();
}
function renderReflectionOutcomePicker() {
  const select = document.getElementById('reflectionOutcome');
  const allowed = new Set([...select.options].map((option) => option.value));
  const available = outcomes.filter((item) => allowed.has(item.code));
  const search = document.getElementById('reflectionOutcomeSearch').value.trim().toLowerCase();
  document.querySelectorAll('[data-reflection-skill]').forEach((button) => {
    const kind = button.dataset.reflectionSkill;
    button.textContent = (kind === 'Hardskills' ? 'Hard skills' : 'Soft skills') + ' (' + available.filter((item) => item.kind === kind).length + ')';
    button.classList.toggle('active', kind === reflectionSkillFilter);
    button.setAttribute('aria-pressed', String(kind === reflectionSkillFilter));
  });
  const list = available.filter((item) => item.kind === reflectionSkillFilter && (item.short + ' ' + item.code).toLowerCase().includes(search));
  document.getElementById('reflectionOutcomePicker').innerHTML = list.map((item) => '<button type="button" aria-pressed="' + (select.value === item.code) + '" class="reflection-outcome-option ' + (select.value === item.code ? 'selected' : '') + '" onclick="chooseReflectionOutcome(\'' + item.code + '\')"><span class="nav-icon" aria-hidden="true">' + uiIcon(item.kind === 'Hardskills' ? 'layout' : 'feedback') + '</span><span><strong>' + esc(item.short) + '</strong><small>' + esc(item.code) + '</small></span><i aria-hidden="true">' + (select.value === item.code ? '✓' : '') + '</i></button>').join('') || '<p class="dim">Geen leeruitkomsten gevonden. Kies een andere vaardigheid, fase of project.</p>';
}
function selectReflectionType(type) { reflectionTypeFilter = type; renderReflections(); }
function updateReflectionLinkType() {
  const previousOutcome = document.getElementById('reflectionOutcome').value;
  const isProject = document.getElementById('reflectionLinkType').value === 'project';
  document.getElementById('reflectionProjectLabel').classList.toggle('hidden', !isProject);
  const select = document.getElementById('reflectionProject');
  const previous = select.value;
  const phase = document.getElementById('reflectionPhase').value;
  const available = assignments.filter((item) => item.phase === phase);
  select.innerHTML = '<option value="">' + (available.length ? 'Kies een project' : 'Geen projecten in deze fase') + '</option>' + available.map((item) => '<option value="' + item.id + '">' + esc(item.title) + '</option>').join('');
  select.value = available.some((item) => String(item.id) === previous) ? previous : '';
  if (isProject) selectReflectionProject();
  else updateReflectionOutcomeOptions();
  const outcomeSelect = document.getElementById('reflectionOutcome');
  if ([...outcomeSelect.options].some((option) => option.value === previousOutcome)) outcomeSelect.value = previousOutcome;
  showReflectionOutcomePreview();
}
function selectReflectionProject() {
  const project = assignments.find((item) => Number(item.id) === Number(document.getElementById('reflectionProject').value));
  if (project) document.getElementById('reflectionPhase').value = project.phase;
  updateReflectionOutcomeOptions();
}
// Reflecties tonen, toevoegen en verwijderen.

// -----------------------------------------------------------------------------
// Reflecties
// -----------------------------------------------------------------------------
let reflections = Array.isArray(window.LARAVEL_DATA?.reflections)
  ? [...window.LARAVEL_DATA.reflections]
  : [];

function getReflections() {
  return reflections.filter((item) => !item.deleted);
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

let reflectionDetailTrigger = null;
let selectedReflectionId = null;
let editingReflectionId = null;
function openOutcomeReflectionDetail(id, fromList = false) {
  const item = reflections.find((reflection) => String(reflection.id) === String(id));
  if (!item) return;
  selectedReflectionId = item.id;
  reflectionDetailTrigger = document.activeElement;
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
  const modal = document.getElementById('outcomeReflectionDetailModal');
  if (!modal.querySelector('.reflection-detail-art')) {
    const art = document.querySelector('.goal-detail-art')?.cloneNode(true);
    if (art) { art.setAttribute('class','reflection-detail-art'); modal.querySelector('.reflection-detail-modal-head').appendChild(art); }
  }
  document.body.appendChild(modal);
  document.getElementById('reflectionEditButton').classList.toggle('hidden', Boolean(item.deleted));
  if (fromList) document.getElementById('reflectionDetailContent').insertAdjacentHTML('beforeend', '<div class="reflection-modal-actions"><button class="button" type="button" onclick="' + (item.deleted ? 'restoreReflection' : 'deleteReflection') + '(\'' + esc(item.id) + '\')">' + (item.deleted ? 'Reflectie herstellen' : 'Reflectie verwijderen') + '</button></div>');
  modal.classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => modal.querySelector('.modal-close').focus({preventScroll:true}));
}

function closeOutcomeReflectionDetail() {
  document.getElementById("outcomeReflectionDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
  reflectionDetailTrigger?.focus({preventScroll:true});
}

document.addEventListener('click', (event) => {
  const summary = event.target.closest('#reflectionList .reflection-table-row > summary');
  if (!summary) return;
  event.preventDefault();
  openOutcomeReflectionDetail(summary.parentElement.id.replace('reflection-row-', ''), true);
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && !document.getElementById('outcomeReflectionDetailModal').classList.contains('hidden')) closeOutcomeReflectionDetail();
});

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
  if (reflectionPhaseFilter === 'all') reflectionPhaseFilter = 'OF';
  const phaseCode = reflectionPhaseFilter;
  const status = document.getElementById('reflectionListStatus').value;
  const search = document.getElementById('reflectionSearch').value.trim().toLowerCase();
  for (const [id,kind,label] of [['reflectionSoftOutcome','Softskills','soft'],['reflectionHardOutcome','Hardskills','hard']]) {
    const select = document.getElementById(id), previous = select.value;
    const available = outcomes.filter((item) => item.phase === phaseCode && item.kind === kind);
    select.innerHTML = '<option value="all">Alle ' + label + ' skills</option>' + available.map((item) => '<option value="' + esc(item.code) + '">' + esc(item.short) + '</option>').join('');
    select.value = available.some((item) => item.code === previous) ? previous : 'all';
  }
  const selectedOutcome = [document.getElementById('reflectionSoftOutcome').value,document.getElementById('reflectionHardOutcome').value].find((value) => value !== 'all');
  const phaseItems = reflections.filter((item) => outcomes.find((outcome) => outcome.code === item.code)?.phase === phaseCode);
  const current = phaseItems.filter((item) => !item.deleted);
  document.querySelectorAll('[data-reflection-type]').forEach((button) => {
    const type = button.dataset.reflectionType;
    const count = current.filter((item) => type === 'all' || (type === 'project' ? Boolean(item.assignmentId) : !item.assignmentId)).length;
    button.classList.toggle('active', type === reflectionTypeFilter);
    button.setAttribute('aria-pressed', String(type === reflectionTypeFilter));
    button.textContent = (type === 'all' ? 'Alle reflecties' : type === 'project' ? 'Projecten' : 'Leeruitkomsten') + ' (' + count + ')';
  });
  const list = phaseItems.filter((item) => (reflectionTypeFilter === "all" || (reflectionTypeFilter === "project" ? Boolean(item.assignmentId) : !item.assignmentId)) && (status === 'all' || (status === 'deleted' ? item.deleted : !item.deleted)) && (!selectedOutcome || item.code === selectedOutcome) && [item.title,item.code,item.situation,item.good,item.improve,item.next].join(' ').toLowerCase().includes(search));
  const reflectionTotal = getReflections().length;
  document.getElementById('reflectionCount').textContent = reflectionTotal + (reflectionTotal === 1 ? ' reflectie' : ' reflecties');
  document.getElementById('reflectionPhaseFilter').innerHTML = phases.map(([code,name]) => {
    const count = getReflectionCountForPhase(getReflections(),code);
    return '<button type="button" aria-pressed="' + (code === phaseCode) + '" class="reflection-filter-button ' + (code === phaseCode ? 'active' : '') + '" onclick="filterReflectionsByPhase(\'' + code + '\')"><i class="reflection-phase-icon" aria-hidden="true">' + (code === 'OF' ? '✓' : code === 'VF' ? '◇' : '⚑') + '</i><span class="reflection-phase-copy"><strong>' + esc(name) + '</strong><small>' + count + ' ' + (count === 1 ? 'reflectie' : 'reflecties') + '</small></span><i class="reflection-phase-indicator" aria-hidden="true"></i></button>';
  }).join('');
  document.getElementById('reflectionLatestTitle').textContent = current[0]?.title || 'Nog geen reflectie';
  document.getElementById('reflectionLatestDate').textContent = current[0]?.date || 'Schrijf je eerste terugblik.';
  document.getElementById('reflectionLatestButton').disabled = !current.length;
  document.getElementById('reflectionNextCopy').textContent = 'Schrijf een nieuwe reflectie in de ' + (phases.find(([code])=>code === phaseCode)?.[1] || 'gekozen fase') + '.';
  document.getElementById('reflectionList').innerHTML = list.map((item) => {
    const outcome = outcomes.find((entry) => entry.code === item.code);
    const sections = [['Situatie',item.situation],['Wat ging goed?',item.good],['Wat kan beter?',item.improve],['Volgende stap',item.next]];
    return '<details id="reflection-row-' + esc(item.id) + '" class="reflection-table-row"><summary><span class="reflection-row-title"><i aria-hidden="true">▤</i><span><strong>' + esc(item.title) + '</strong><small>' + esc(outcome?.short || '') + '</small></span></span><span class="reflection-row-label">' + (item.assignmentId ? '▣ Project' : '◎ Leeruitkomst') + '</span><span class="reflection-row-code" title="' + esc(item.code) + '">' + esc(outcome?.short || item.code) + '</span><span class="reflection-row-status ' + (item.deleted ? 'deleted' : '') + '">' + (item.deleted ? 'Verwijderd' : '✓ Afgerond') + '</span><span class="reflection-row-date">' + esc(item.date) + '</span><span aria-hidden="true">›</span></summary><div class="reflection-table-body">' + sections.map(([title,text])=>'<section><h3>' + title + '</h3><p>' + esc(text || '') + '</p></section>').join('') + '<button class="button" type="button" onclick="' + (item.deleted ? 'restoreReflection' : 'deleteReflection') + '(\'' + esc(item.id) + '\')">' + (item.deleted ? 'Reflectie herstellen' : 'Reflectie verwijderen') + '</button></div></details>';
  }).join('') || '<div class="empty-state"><h2>Geen reflecties gevonden</h2><p>Pas je filters aan of schrijf een nieuwe reflectie.</p></div>';
}
async function restoreReflection(id) {
  try {
    await apiRequest(apiUrl(window.LARAVEL_API.reflectionRestore,id), {method:'POST',body:'{}'});
    closeOutcomeReflectionDetail();
    reflections = reflections.map((item)=>item.id === id ? {...item,deleted:false} : item);
    renderReflections();
    renderOutcomeReflections();
    hint('De reflectie is hersteld.');
  } catch(error) { hint(error.message); }
}
function newReflectionInPhase() {
  document.getElementById('reflectionPhase').value = reflectionPhaseFilter;
  updateReflectionOutcomeOptions();
  openReflectionForm();
}
function openLatestReflection() {
  const item = getReflections().find((item)=>outcomes.find((outcome)=>outcome.code === item.code)?.phase === reflectionPhaseFilter);
  if (!item) { newReflectionInPhase(); return; }
  reflectionTypeFilter = 'all';
  document.getElementById('reflectionListStatus').value = 'active';
  document.getElementById('reflectionSoftOutcome').value = 'all';
  document.getElementById('reflectionHardOutcome').value = 'all';
  document.getElementById('reflectionSearch').value = '';
  renderReflections();
  openOutcomeReflectionDetail(item.id, true);
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
  if (document.getElementById('reflectionLinkType').value === 'project' && !document.getElementById('reflectionProject').value) {
    message.textContent = 'Kies een project om je reflectie aan te koppelen.';
    return;
  }
  if (!title || !code || [situation, good, improve, next].some((value) => value.length < 10)) {
    message.textContent = "Vul alle stappen in en schrijf bij iedere vraag minimaal 10 tekens.";
    message.className = "form-message error";
    return;
  }
  try {
    const saved = await apiRequest(editingReflectionId ? apiUrl(window.LARAVEL_API.reflectionUpdate, editingReflectionId) : window.LARAVEL_API.reflectionStore, {
      method: editingReflectionId ? 'PUT' : 'POST',
      body: JSON.stringify({ title, code, situation, good, improve, next, assignmentId: document.getElementById('reflectionLinkType').value === 'project' ? Number(document.getElementById('reflectionProject').value) : null }),
    });
    if (editingReflectionId) reflections = reflections.map((item) => String(item.id) === String(editingReflectionId) ? saved : item);
    else reflections.unshift(saved);
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
      .filter((o) => o.phase === phaseCode && (document.getElementById('reflectionLinkType').value !== 'project' || assignments.find((item) => Number(item.id) === Number(document.getElementById('reflectionProject').value))?.outcomeCodes.includes(o.code)))
      .map((o) => '<option value="' + o.code + '">' + esc(o.short) + " — " + o.code + "</option>")
      .join("") +
    "</optgroup>";
  showReflectionOutcomePreview();
}

function showReflectionOutcomePreview() {
  renderReflectionOutcomePicker();
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
  if (!returnToOutcome) document.getElementById('reflectionPhase').value = reflectionPhaseFilter === 'all' ? 'OF' : reflectionPhaseFilter;
  reflectionReturnToOutcome = returnToOutcome;
  const form = document.getElementById("reflectionForm");
  updateReflectionLinkType();
  form.classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.getElementById("reflectionTitle").focus());
}

function closeReflectionForm() {
  editingReflectionId = null;
  document.getElementById("reflectionForm").classList.add("hidden");
  document.getElementById('reflectionLinkType').value = 'outcome';
  document.getElementById('reflectionProjectLabel').classList.add('hidden');
  document.body.classList.remove("modal-open");
  reflectionReturnToOutcome = false;
}

function editSelectedReflection() {
  const item = getReflections().find((entry) => String(entry.id) === String(selectedReflectionId));
  if (!item) return;
  closeOutcomeReflectionDetail();
  show('reflections');
  openReflectionForm();
  editingReflectionId = item.id;
  document.getElementById('reflectionPhase').value = outcomes.find((o) => o.code === item.code)?.phase || 'OF';
  document.getElementById('reflectionLinkType').value = item.assignmentId ? 'project' : 'outcome';
  updateReflectionLinkType();
  if (item.assignmentId) {
    document.getElementById('reflectionProject').value = item.assignmentId;
    selectReflectionProject();
  }
  document.getElementById('reflectionOutcome').value = item.code;
  for (const [id, value] of Object.entries({reflectionTitle:item.title,reflectionSituation:item.situation,reflectionGood:item.good,reflectionImprove:item.improve,reflectionNext:item.next})) document.getElementById(id).value = value || '';
  showReflectionOutcomePreview();
}

async function deleteReflection(id) {
  try {
    await apiRequest(apiUrl(window.LARAVEL_API.reflectionDelete, id), { method: "DELETE" });
    closeOutcomeReflectionDetail();
    reflections = reflections.map((item) => item.id === id ? {...item, deleted:true} : item);
    renderReflections();
    renderOutcomeReflections();
  } catch (error) {
    hint(error.message);
  }
}

