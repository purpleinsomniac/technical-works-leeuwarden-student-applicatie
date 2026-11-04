// Leerdoelen, badges en de driestappenwizard.
let goalWizardStep = 1;
let goalStatusFilter = 'actief';
let goalListPage = 1;
let goalSaving = false;
let selectedGoalId = null;
async function completeSelectedGoal() {
  const goal = goalItems.find((item) => Number(item.id) === Number(selectedGoalId));
  if (!goal || goal.status === 'afgerond') return;
  const button = document.getElementById('goalCompleteButton');
  button.disabled = true;
  try {
    await apiRequest(apiUrl(window.LARAVEL_API.goalComplete, goal.id), {method:'POST', body:'{}'});
    goal.status = 'afgerond';
    window.LARAVEL_DATA.goals = [...goalItems];
    renderDashboard();
    renderGoalList();
    closeGoalDetail();
    hint('Je leerdoel is afgerond en staat bij Afgerond.');
  } catch (error) { document.getElementById('goalCompleteMessage').textContent = error.message; }
  finally { button.disabled = false; }
}
function goalInsightData() {
  const activities = window.LARAVEL_DATA?.goalInsights?.activities || [];
  const badges = [
    { title: 'Eerste leerdoel', icon: '◎', earned: goalItems.length > 0, description: 'Maak je eerste persoonlijke leerdoel.' },
    { title: 'Plannen & organiseren', icon: '▦', earned: goalItems.some((goal) => goal.status === 'afgerond' && /plann|voortgang/i.test(goal.title)), description: 'Rond een leerdoel over plannen of voortgang af.' },
    { title: 'Teamsamenwerking', icon: '♧', earned: goalItems.some((goal) => goal.status === 'afgerond' && /samenwerk|team/i.test(goal.title)), description: 'Rond een leerdoel over samenwerken af.' },
  ];
  badges.push(
    { title: 'Eerste mijlpaal', icon: '⚑', earned: goalItems.some((g) => g.status === 'afgerond'), description: 'Rond je eerste leerdoel af.' },
    { title: 'Doorzetter', icon: '★', earned: goalItems.filter((g) => g.status === 'afgerond').length >= 5, description: 'Rond vijf leerdoelen af.' },
    { title: 'Doelgericht', icon: '◎', earned: goalItems.filter((g) => g.status === 'afgerond').length >= 10, description: 'Rond tien leerdoelen af.' },
    { title: 'Projectbouwer', icon: '▣', earned: assignments.some((p) => p.status === 'completed'), description: 'Rond een project succesvol af.' },
    { title: 'Ervaren maker', icon: '◇', earned: assignments.filter((p) => p.status === 'completed').length >= 5, description: 'Rond vijf projecten af.' },
    { title: 'Reflectieve denker', icon: '▤', earned: (window.LARAVEL_DATA?.reflections || []).length >= 3, description: 'Schrijf drie reflecties over je ontwikkeling.' },
    { title: 'Brede ontwikkeling', icon: '✧', earned: ['OF','VF','AF'].every((phase) => goalItems.some((g) => goalPhaseCode(g) === phase && g.status === 'afgerond')), description: 'Rond in iedere fase een leerdoel af.' }
  );
  return { activities, badges };
}
function renderGoalInsights() {
  const { activities, badges } = goalInsightData();
  document.getElementById('goalBadges').innerHTML = badges.slice(0, 3).map((badge) => '<span class="goal-badge ' + (badge.earned ? 'earned' : 'locked') + '" title="' + esc(badge.description) + '"><i>' + badge.icon + '</i><b>' + esc(badge.title) + '</b><small>' + (badge.earned ? '✓' : '○') + '</small></span>').join('');
  document.getElementById('goalRecentActivity').innerHTML = activities.slice(0, 3).map((item) => '<div><span>＋</span><strong>' + esc(item.title) + '</strong><time>' + esc(new Date(item.date).toLocaleDateString('nl-NL', {day:'numeric',month:'short'})) + '</time></div>').join('') || '<p>Nog geen activiteit.</p>';
  const monday = (date) => { const d = new Date(date); d.setHours(0,0,0,0); d.setDate(d.getDate() - (d.getDay() + 6) % 7); return d.getTime(); };
  const weeks = new Set(activities.map((item) => monday(item.date)));
  const current = monday(new Date());
  const weekBefore = (n) => { const date = new Date(current); date.setDate(date.getDate() - n * 7); return date.getTime(); };
  let streak = 0;
  let offset = weeks.has(current) ? 0 : 1;
  while (weeks.has(weekBefore(offset + streak))) streak++;
  document.getElementById('goalStreakCopy').textContent = streak ? 'Je leert ' + streak + ' weken op rij. Goed bezig!' : 'Maak deze week een nieuwe stap.';
  document.getElementById('goalStreak').innerHTML = Array.from({length:4}, (_, index) => '<span class="' + (weeks.has(weekBefore(3-index)) ? 'done' : '') + '" title="' + (3-index) + ' weken geleden">' + (weeks.has(weekBefore(3-index)) ? '✓' : '·') + '</span>').join('') + '<strong>' + streak + '<small>weken</small></strong>';
}
function openGoalInsight(type) {
  const { activities, badges } = goalInsightData();
  document.getElementById('goalInsightTitle').textContent = type === 'badges' ? 'Jouw badges' : 'Recente activiteit';
  document.getElementById('goalInsightContent').innerHTML = type === 'badges'
    ? '<p class="badge-gallery-intro">' + badges.filter((badge) => badge.earned).length + ' van de ' + badges.length + ' badges behaald. Blijf werken aan je ontwikkeling!</p><div class="badge-gallery">' + badges.map((badge) => '<article class="badge-gallery-card ' + (badge.earned ? 'earned' : 'locked') + '"><span class="badge-gallery-icon" aria-hidden="true">' + badge.icon + '</span><div><span class="badge-gallery-status">' + (badge.earned ? '✓ Behaald' : 'Nog te behalen') + '</span><h3>' + esc(badge.title) + '</h3><p>' + esc(badge.description) + '</p></div></article>').join('') + '</div>'
    : activities.map((item) => '<p><b>' + esc(item.title) + '</b><br>' + esc(new Date(item.date).toLocaleString('nl-NL')) + '</p>').join('') || '<p>Nog geen activiteit.</p>';
  document.getElementById('goalInsightModal').classList.remove('hidden');
  document.querySelector('#goalInsightModal .modal-close').focus();
}
function closeGoalInsight() { document.getElementById('goalInsightModal')?.classList.add('hidden'); }
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeGoalInsight(); });
let goalItems = Array.isArray(window.LARAVEL_DATA?.goals) ? [...window.LARAVEL_DATA.goals] : [];

function goalPhaseCode(goal) {
  return goal.phaseCode || outcomes.find((outcome) => outcome.code === goal.code)?.phase || "OF";
}

function goalPhaseName(code) {
  return phases.find((item) => item[0] === code)?.[1] || "Oriëntatiefase";
}

function goalStatusMeta(status) {
  if (status === "actief") return { label: "Actief", className: "active" };
  return { label: "Afgerond", className: "completed" };
}

function renderGoalList() {
  const list = document.getElementById("goalList");
  if (!list) return;
  renderGoalInsights();
  const selectedPhase = document.getElementById("goalListPhase")?.value || "OF";
  document.getElementById('goalStatsActive').textContent = goalItems.filter((item) => item.status === 'actief').length;
  document.getElementById('goalStatsCompleted').textContent = goalItems.filter((item) => item.status === 'afgerond').length;
  const nextGoal = goalItems.find((item) => item.status === 'actief' && goalPhaseCode(item) === selectedPhase);
  document.getElementById('goalNextStepText').textContent = nextGoal ? 'Werk verder aan “' + nextGoal.title + '” en voeg een reflectie toe.' : 'Maak een nieuw leerdoel voor deze fase.';
  document.getElementById('goalNextStepButton').textContent = nextGoal ? 'Ga naar leerdoel →' : 'Nieuw leerdoel →';
  document.getElementById('goalNextStepButton').onclick = () => nextGoal ? openGoalDetail(nextGoal.id) : focusGoalWizard();
  document.querySelectorAll('[data-goal-phase]').forEach((tab) => {
    const selected = tab.dataset.goalPhase === selectedPhase;
    tab.classList.toggle('active', selected);
    tab.setAttribute('aria-selected', String(selected));
    const code = tab.dataset.goalPhase;
    tab.textContent = goalPhaseName(code) + ' (' + goalItems.filter((goal) => goalPhaseCode(goal) === code).length + ')';
  });
  const search = document.getElementById("goalListSearch")?.value.trim().toLowerCase() || "";
  const phaseOrder = ["OF", "VF", "AF"];
  const filtered = goalItems.filter((goal) => {
    const phaseCode = goalPhaseCode(goal);
    const searchable = [goal.title, goal.description, goal.outcome, goal.code].join(" ").toLowerCase();
    return goal.status === goalStatusFilter && (selectedPhase === "all" || phaseCode === selectedPhase) && searchable.includes(search);
  });

  document.getElementById('goalActiveTotal').textContent = '(' + goalItems.filter((goal) => goal.status === 'actief').length + ')';
  document.getElementById('goalCompletedTotal').textContent = '(' + goalItems.filter((goal) => goal.status === 'afgerond').length + ')';
  const pageCount = Math.max(1, Math.ceil(filtered.length / 4));
  goalListPage = Math.min(goalListPage, pageCount);
  const visible = filtered.slice((goalListPage - 1) * 4, goalListPage * 4);
  document.getElementById('goalPagination').innerHTML = pageButtons(goalListPage, pageCount, 'setGoalPage');
  if (!filtered.length) {
    list.innerHTML = '<div class="goal-list-empty"><span>◎</span><h3>Geen leerdoelen gevonden</h3><p>Pas je filters aan of maak een nieuw leerdoel.</p></div>';
    return;
  }

  list.innerHTML = phaseOrder
    .map((phaseCode) => {
      const phaseGoals = visible.filter((goal) => goalPhaseCode(goal) === phaseCode);
      if (!phaseGoals.length) return "";
      const phaseClass = phaseCode === "OF" ? "orientation" : phaseCode === "VF" ? "deepening" : "completion";
      return (
        '<section class="goal-phase-group ' +
        phaseClass +
        '"><header><span>' +
        esc(goalPhaseName(phaseCode)) +
        "</span><small>" +
        phaseGoals.length +
        (phaseGoals.length === 1 ? " leerdoel" : " leerdoelen") +
        "</small></header>" +
        phaseGoals
          .map((goal) => {
            const status = goalStatusMeta(goal.status);
            const icon = phaseCode === "OF" ? "layers" : phaseCode === "VF" ? "screen" : "code";
            return (
              '<button class="goal-list-row" type="button" onclick="openGoalDetail(' +
              Number(goal.id) +
              ')"><span class="goal-list-icon ' +
              phaseClass +
              '" data-goal-icon="' +
              icon +
              '" aria-hidden="true"></span><span class="goal-list-copy"><strong>' +
              esc(goal.title) +
              '</strong><span class="goal-card-description">' + esc(goal.description || goal.outcome || '') +
              '</span><span class="code">' +
              esc(goal.code || "Nog niet gekoppeld") +
              '</span></span><span class="goal-list-status ' +
              status.className +
              '"><i></i>' +
              status.label +
              '</span><span class="goal-list-arrow" aria-hidden="true">›</span></button>'
            );
          })
          .join("") +
        "</section>"
      );
    })
    .join("");
}

function renderGoalPhaseChoices() {
  const container = document.getElementById("goalPhaseChoices");
  const select = document.getElementById("goalPhase");
  if (!container || !select) return;
  container.innerHTML = phases
    .map(
      ([code, label]) =>
        '<button type="button" class="goal-phase-choice ' +
        (select.value === code ? "selected" : "") +
        '" onclick="selectGoalPhase(\'' +
        code +
        '\')"><span class="goal-phase-choice-check">✓</span><strong>' +
        esc(label) +
        "</strong><small>" +
        outcomes.filter((outcome) => outcome.phase === code).length +
        " leeruitkomsten</small></button>",
    )
    .join("");
}

function selectGoalPhase(code) {
  document.getElementById("goalPhase").value = code;
  renderGoalPhaseChoices();
  renderGoalOptions();
}

function renderGoalOptions() {
  const select = document.getElementById("goalCodes");
  const phaseSelect = document.getElementById("goalPhase");
  const choices = document.getElementById("goalOutcomeChoices");
  if (!select || !phaseSelect || !choices) return;
  const previous = select.value;
  const list = outcomes.filter((outcome) => outcome.phase === phaseSelect.value);
  select.innerHTML = '<option value="">Kies een leeruitkomst</option>' + list.map((outcome) => '<option value="' + outcome.code + '">' + esc(outcome.short) + "</option>").join("");
  if (list.some((outcome) => outcome.code === previous)) select.value = previous;
  document.getElementById("goalChoiceCount").textContent = list.length + " " + (list.length === 1 ? "leeruitkomst" : "leeruitkomsten");
  choices.innerHTML = list
    .map(
      (outcome) =>
        '<button type="button" role="option" aria-selected="' +
        (outcome.code === select.value) +
        '" class="goal-outcome-choice ' +
        (outcome.code === select.value ? "selected" : "") +
        '" onclick="selectGoalOutcome(\'' +
        outcome.code +
        '\')"><span class="goal-choice-number">' +
        String(outcome.number).padStart(2, "0") +
        "</span><span><strong>" +
        esc(outcome.short) +
        "</strong><small>" +
        (outcome.kind === "Hardskills" ? "Vakvaardigheid" : "Persoonlijke vaardigheid") +
        '</small><span class="code">' +
        esc(outcome.code) +
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
  const preview = document.getElementById("goalOutcomePreview");
  const outcome = outcomes.find((item) => item.code === document.getElementById("goalCodes")?.value);
  if (!preview) return;
  preview.classList.toggle("hidden", !outcome);
  preview.innerHTML = outcome
    ? '<span>Geselecteerde leeruitkomst</span><strong>' + esc(outcome.short) + '</strong><span class="code">' + esc(outcome.code) + "</span>"
    : "";
}

function setGoalWizardStep(step) {
  goalWizardStep = Math.max(1, Math.min(3, step));
  document.querySelectorAll("[data-goal-step]").forEach((panel) => panel.classList.toggle("hidden", Number(panel.dataset.goalStep) !== goalWizardStep));
  document.querySelectorAll("[data-goal-step-indicator]").forEach((indicator) => {
    const indicatorStep = Number(indicator.dataset.goalStepIndicator);
    indicator.classList.toggle("active", indicatorStep === goalWizardStep);
    indicator.classList.toggle("completed", indicatorStep < goalWizardStep);
  });
  document.getElementById("goalPreviousButton").disabled = goalWizardStep === 1;
  document.getElementById("goalNextButton").classList.toggle("hidden", goalWizardStep === 3);
  document.getElementById("goalSaveButton").classList.toggle("hidden", goalWizardStep !== 3);
  document.getElementById("goalMessage").textContent = "";
  if (goalWizardStep === 2) renderGoalPhaseChoices();
  if (goalWizardStep === 3) renderGoalOptions();
}

function nextGoalStep() {
  const message = document.getElementById("goalMessage");
  if (goalWizardStep === 1 && document.getElementById("goalText").value.trim().length < 10) {
    message.textContent = "Beschrijf je leerdoel in minimaal 10 tekens.";
    message.className = "form-message error";
    return;
  }
  setGoalWizardStep(goalWizardStep + 1);
}

function previousGoalStep() {
  setGoalWizardStep(goalWizardStep - 1);
}

function updateGoalTextCount() {
  const field = document.getElementById("goalText");
  document.getElementById("goalTextCount").textContent = field?.value.length || 0;
}

function focusGoalWizard() {
  document.getElementById('goalWizard').classList.remove('hidden');
  document.body.classList.add('goal-wizard-open');
  setGoalWizardStep(1);
  document.getElementById('goalText').focus();
}

function showGoalOverview() {
  selectGoalStatus('actief');
}

function selectGoalStatus(status) {
  goalStatusFilter = status;
  ['goalOverviewTab', 'goalCompletedTab'].forEach((id, index) => {
    const selected = status === (index ? 'afgerond' : 'actief');
    document.getElementById(id).classList.toggle('active', selected);
    document.getElementById(id).setAttribute('aria-selected', String(selected));
  });
  resetGoalPage();
}
function resetGoalPage() { goalListPage = 1; renderGoalList(); }
function setGoalPage(page) { goalListPage = Math.max(1, page); renderGoalList(); }
function selectGoalListPhase(phase) { document.getElementById('goalListPhase').value = phase; resetGoalPage(); }
function closeGoalWizard(force = false) {
  if (goalSaving) return;
  if (!force && document.getElementById('goalText').value.trim() && !window.confirm('Je hebt nog niet opgeslagen. Wil je het leerdoel sluiten en je invoer verwijderen?')) return;
  document.getElementById('goalWizard').classList.add('hidden');
  document.body.classList.remove('goal-wizard-open');
  document.getElementById('goalText').value = '';
  document.getElementById('goalCodes').value = '';
  updateGoalTextCount();
  document.getElementById('goalNewTab').focus();
}
document.addEventListener('keydown', (event) => {
  const wizard = document.getElementById('goalWizard');
  if (!wizard || wizard.classList.contains('hidden')) return;
  if (event.key === 'Escape') { event.preventDefault(); closeGoalWizard(); }
  if (event.key === 'Tab') {
    const fields = [...wizard.querySelectorAll('button:not([disabled]), textarea, select:not(.hidden)')].filter((field) => field.getClientRects().length);
    const first = fields[0], last = fields[fields.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  }
});

function initializeGoals() {
  renderGoalList();
  renderGoalPhaseChoices();
  setGoalWizardStep(goalWizardStep);
}

function openGoalDetail(id) {
  selectedGoalId = id;
  const goal = goalItems.find((item) => Number(item.id) === Number(id));
  if (!goal) return;
  const status = goalStatusMeta(goal.status);
  const modal = document.getElementById("goalDetailModal");
  const statusElement = document.getElementById("goalDetailStatus");
  statusElement.textContent = status.label;
  statusElement.className = "goal-status " + status.className;
  document.getElementById("goalDetailTitle").textContent = goal.title;
  document.getElementById("goalDetailDescription").textContent = goal.description;
  document.getElementById("goalDetailOutcome").textContent = goal.outcome || "Leeruitkomst";
  document.getElementById("goalDetailCode").textContent = goal.code || "—";
  document.getElementById('goalCompleteButton').classList.toggle('hidden', goal.status === 'afgerond');
  document.getElementById('goalCompleteMessage').textContent = '';
  modal.classList.remove("hidden");
  document.body.classList.add("account-modal-open");
  modal.querySelector(".modal-close")?.focus();
}

function closeGoalDetail() {
  document.getElementById("goalDetailModal")?.classList.add("hidden");
  document.body.classList.remove("account-modal-open");
}

async function saveGoal() {
  if (goalSaving) return;
  const description = document.getElementById("goalText").value.trim();
  const code = document.getElementById("goalCodes").value;
  const message = document.getElementById("goalMessage");
  if (description.length < 10 || !code) {
    message.textContent = "Beschrijf je leerdoel en kies een leeruitkomst.";
    message.className = "form-message error";
    return;
  }
  goalSaving = true;
  document.getElementById('goalSaveButton').disabled = true;
  try {
    const goal = await apiRequest(window.LARAVEL_API.goalStore, {
      method: "POST",
      body: JSON.stringify({ description, code }),
    });
    goal.phaseCode = goal.phaseCode || document.getElementById("goalPhase").value;
    goalItems.unshift(goal);
    window.LARAVEL_DATA.goals = [...goalItems];
    renderDashboard();
    renderGoalList();
    document.getElementById("goalText").value = "";
    document.getElementById("goalCodes").value = "";
    updateGoalTextCount();
    setGoalWizardStep(1);
    message.textContent = "Je leerdoel is opgeslagen en staat in je overzicht.";
    message.className = "form-message success";
    showGoalOverview();
    selectGoalListPhase(goal.phaseCode);
    goalSaving = false;
    closeGoalWizard(true);
    hint('Je leerdoel is opgeslagen.');
  } catch (error) {
    message.textContent = error.message;
    message.className = "form-message error";
  } finally {
    goalSaving = false;
    document.getElementById('goalSaveButton').disabled = false;
  }
}
