// Puntenoverzicht, registraties, meldingen en challenges.
let hoursPhaseFilter = "OF";
let hoursPage = 1;
let selectedRegistrationRecord = null;
let challengeState = null;
let challengeSyncBusy = false;

// Laat de server de challengevoortgang bijwerken en voorkom overlappende verzoeken.
async function syncChallenges() {
  if (challengeSyncBusy || !window.LARAVEL_API?.challengeSync) return;
  challengeSyncBusy = true;
  try {
    challengeState = await apiRequest(window.LARAVEL_API.challengeSync, { method: 'POST', body: '{}' });
    challengeState.completed.forEach((item) => hint('Challenge voltooid: ' + item.title + '! Je hebt ' + item.reward + ' XP verdiend.'));
    renderXp();
    const card = document.querySelector('.xp-challenge');
    const active = challengeState.active;
    card.querySelector('h2').textContent = active?.title || 'Alle challenges voltooid';
    card.querySelector('p').textContent = active ? active.description : 'Binnenkort volgt een nieuwe uitdaging.';
    card.querySelector('button').onclick = openChallengeDetail;
    document.getElementById('xpChallengeProgress').textContent = active ? active.progress + ' / ' + active.target + ' · +' + active.reward + ' XP' : 'Bekijk je historie';
    document.getElementById('xpChallengeBar').style.width = active ? Math.min(100, active.progress / active.target * 100) + '%' : '100%';
    if (!document.getElementById('challengeModal').classList.contains('hidden')) renderChallengeDetail();
  } catch (error) {
    document.querySelector('.xp-challenge p').textContent = 'Challenges konden niet worden geladen. Open Punten opnieuw om het nogmaals te proberen.';
  } finally { challengeSyncBusy = false; }
}

function openChallengeDetail() {
  const modal = document.getElementById('challengeModal');
  challengeListFilter = 'recommended';
  challengeShowAll = false;
  renderChallengeDetail();
  modal.classList.remove('hidden');
  modal.querySelector('.modal-close').focus({preventScroll:true});
}
let challengeListFilter = 'recommended';
let challengeShowAll = false;
function selectChallengeFilter(filter) {
  challengeListFilter = filter;
  challengeShowAll = false;
  renderChallengeDetail();
}
function toggleAllChallenges() {
  challengeShowAll = !challengeShowAll;
  renderChallengeDetail();
}
function renderChallengeDetail() {
  const active = challengeState?.active;
  document.getElementById('challengeDetailTitle').textContent = active?.title || 'Alle challenges voltooid';
  document.getElementById('challengeDetailCopy').textContent = active?.description || 'Je hebt alle beschikbare challenges in deze fase voltooid. Bekijk hieronder wat je hebt bereikt.';
  const percent = active ? Math.min(100, Math.round(active.progress / active.target * 100)) : 100;
  document.getElementById('challengeProgressPercent').textContent = percent + '%';
  document.getElementById('challengeProgressRing').style.setProperty('--challenge-progress', percent * 3.6 + 'deg');
  document.getElementById('challengePhaseName').textContent = active?.phase || challengeState?.phase || 'Jouw fase';
  document.getElementById('challengeProgressCopy').textContent = active ? 'Voortgang: ' + active.progress + ' / ' + active.target + ' · Beloning: ' + active.reward + ' XP' : 'Alle uitdagingen in deze fase afgerond.';
  document.getElementById('challengeSummaryReward').textContent = active ? '☆ +' + active.reward + ' XP' : '✓ Voltooid';
  document.getElementById('challengeActiveCard').classList.toggle('hidden', !active);
  document.getElementById('challengeActiveTitle').textContent = active?.title || '';
  document.getElementById('challengeActiveDescription').textContent = active?.description || '';
  document.getElementById('challengeActiveCount').textContent = active ? active.progress + ' / ' + active.target + ' voltooid' : '';
  document.getElementById('challengeActiveBar').style.width = percent + '%';
  document.getElementById('challengeActiveReward').textContent = active ? '+' + active.reward + ' XP' : '';
  const history = challengeState?.history || [];
  document.getElementById('challengeHistory').innerHTML = history.map((item) => '<li>' + esc(item.title) + ' · ' + esc(item.phase) + ' · +' + item.reward + ' XP · Voltooid</li>').join('') || '<li>Je hebt nog geen challenges voltooid.</li>';
  // Sluit de huidige en afgeronde challenges uit van de lijst met volgende uitdagingen.
  const finished = new Set(history.filter((item) => item.phase === challengeState?.phase).map((item) => Number(item.challenge_id)));
  let upcoming = (challengeState?.catalog || []).filter((item) => !finished.has(Number(item.id)) && Number(item.id) !== Number(active?.challenge_id));
  if (challengeListFilter === 'nearly') upcoming = upcoming.filter((item) => item.target > 0 && item.progress / item.target >= .75).sort((a,b) => b.progress/b.target - a.progress/a.target);
  const visible = challengeShowAll || challengeListFilter === 'phase' ? upcoming : upcoming.slice(0,3);
  const icons = {reflections:'reflection',project_reflections:'reflection',feedback:'feedback',goals_created:'target',goals_completed:'target',projects:'assignment',team_reflections:'feedback',evidence:'assignment',on_time_projects:'calendar',skill_balance:'layout',phase_percentage:'progress',points:'progress'};
  document.getElementById('challengeUpcoming').innerHTML = visible.map((item) => '<article><span class="challenge-list-icon ' + (item.type === 'feedback' ? 'purple' : '') + '">' + uiIcon(icons[item.type] || 'target') + '</span><div><strong>' + esc(item.title) + '</strong><p>' + esc(item.description) + '</p></div><b class="challenge-xp-pill">+' + item.reward + ' XP</b></article>').join('') || '<p class="challenge-empty">' + (challengeListFilter === 'nearly' ? 'Er zijn nog geen volgende challenges voor minstens 75% voltooid.' : 'Geen volgende challenges in deze fase.') + '</p>';
  document.getElementById('challengeVisibleCount').textContent = visible.length + ' van ' + upcoming.length + ' uitdagingen zichtbaar';
  const showAllButton = document.getElementById('challengeShowAll');
  showAllButton.classList.toggle('hidden', upcoming.length <= 3 || challengeListFilter === 'phase');
  showAllButton.textContent = challengeShowAll ? 'Toon minder uitdagingen ↑' : 'Bekijk alle uitdagingen →';
  document.querySelectorAll('[data-challenge-filter]').forEach((button) => {
    const selected = button.dataset.challengeFilter === challengeListFilter;
    button.classList.toggle('active', selected);
    button.setAttribute('aria-pressed', String(selected));
  });
}
function closeChallengeDetail() { document.getElementById('challengeModal').classList.add('hidden'); }
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeChallengeDetail(); });
window.addEventListener('focus', syncChallenges);

// Tel de punten op voor het gekozen jaar en de gekozen fase.
function renderHours(refreshChallenges = true) {
  const selected = document.getElementById("hoursYear").value;
  const isTotal = selected === "total";
  const target = phasePointTarget;
  const phaseProgress = phases.find(([code]) => code === hoursPhaseFilter);
  // De bestaande puntenregistraties vallen onder leerjaar één.
  const phasePoints = isTotal || Number(selected) === 1 ? phaseProgress?.[3] ?? 0 : 0;
  const remaining = Math.max(0, target - phasePoints);
  const percentage = isTotal || Number(selected) === 1 ? phaseProgress?.[2] ?? 0 : 0;
  const periodLabel = isTotal ? "Totaal opleiding" : "Leerjaar " + selected;

  document.getElementById("registrationsYear").textContent = periodLabel;
  document.getElementById("hoursYearTotal").textContent = phasePoints.toLocaleString("nl-NL");
  document.getElementById("hoursTarget").textContent = target.toLocaleString("nl-NL");
  document.getElementById("hoursProgressPercent").textContent = pointPercentLabel(percentage);
  document.getElementById("hoursProgressRing").style.setProperty("--hours-progress", displayPointPercent(percentage) * 3.6 + "deg");
  document.getElementById("hoursYearPercentage").textContent = hoursPhaseName(hoursPhaseFilter) + ' · ' + (remaining === 0 ? 'Puntendoel behaald' : 'Nog ' + remaining.toLocaleString('nl-NL') + ' punten nodig');

  renderHoursRegistrations(selected, periodLabel);
  renderXp();
  if (refreshChallenges) syncChallenges();
}

function renderXp() {
  const xp = challengeState?.xp || 0;
  const level = Math.floor(xp / 1000) + 1;
  const progress = xp % 1000;
  document.getElementById('xpLevelTitle').textContent = 'Level ' + level;
  const titles = ['Nieuwkomer', 'Ontdekker', 'Aanpakker', 'Probleemoplosser', 'Projectbouwer', 'Zelfstandig specialist', 'Projectprofessional', 'Innovator', 'Expert', 'Meester'];
  document.getElementById('xpLevelName').textContent = titles[level - 1] || 'Meester · rang ' + (level - 9);
  document.getElementById('xpLevelProgress').textContent = xp.toLocaleString('nl-NL') + ' / ' + (level * 1000).toLocaleString('nl-NL') + ' XP';
  document.getElementById('xpHelpProgress').textContent = document.getElementById('xpLevelProgress').textContent;
  document.getElementById('xpLevelBar').style.width = (progress / 10) + '%';
  document.getElementById('xpHelpBar').style.width = Math.min(100, xp / (level * 1000) * 100) + '%';
  document.getElementById('xpRemaining').textContent = 'Nog ' + (1000 - progress) + ' XP tot level ' + (level + 1) + '!';
}

function openXpHelp() {
  document.getElementById('xpHelpPanel').classList.remove('hidden');
  document.getElementById('xpHelpPanel').querySelector('button').focus();
}
function closeXpHelp() { document.getElementById('xpHelpPanel').classList.add('hidden'); }
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeXpHelp(); });

// Toon de gefilterde projectregistraties per pagina.
function renderHoursRegistrations(selected, periodLabel) {
  const search = document.getElementById("hoursSearch").value.trim().toLowerCase();
  const sort = document.getElementById("hoursSort").value;
  const perPage = Number(document.getElementById("hoursPerPage").value);
  let filtered = records.filter((record) => {
    const periodMatches = selected === "total" || Number(record.year) === Number(selected);
    const phaseMatches = hoursPhaseFilter === "all" || (record.phases || []).includes(hoursPhaseFilter);
    const linked = (record.outcomeCodes || []).map((code) => outcomes.find((outcome) => outcome.code === code)).filter(Boolean);
    const searchable = [record.title, record.type, record.description, ...linked.flatMap((outcome) => [outcome.short, outcome.code])].join(" ").toLowerCase();
    return periodMatches && phaseMatches && (!search || searchable.includes(search));
  });

  filtered.sort((left, right) => {
    if (sort === "title") return left.title.localeCompare(right.title, "nl");
    if (sort === "hours-desc") return Number(right.hours) - Number(left.hours);
    const direction = sort === "date-asc" ? 1 : -1;
    return direction * String(left.dateIso || "").localeCompare(String(right.dateIso || ""));
  });

  const pageCount = Math.max(1, Math.ceil(filtered.length / perPage));
  hoursPage = Math.min(hoursPage, pageCount);
  const visibleRecords = filtered.slice((hoursPage - 1) * perPage, hoursPage * perPage);
  const start = filtered.length ? (hoursPage - 1) * perPage + 1 : 0;
  const end = Math.min(hoursPage * perPage, filtered.length);

  document.querySelectorAll("[data-hours-phase]").forEach((button) => {
    const active = button.dataset.hoursPhase === hoursPhaseFilter;
    button.classList.toggle("active", active);
    button.setAttribute("aria-selected", String(active));
  });
  document.getElementById("registrationsYear").textContent = periodLabel;
  document.getElementById("hoursResultCount").textContent = filtered.length ? start + " – " + end + " van " + filtered.length + " registraties" : "0 registraties";
  document.getElementById("hoursList").innerHTML = visibleRecords.length
    ? visibleRecords.map(renderHoursRow).join("")
    : '<div class="hours-empty"><span>⌕</span><h3>Geen registraties gevonden</h3><p>Pas de periode, fase of zoekproject aan.</p></div>';
  document.getElementById("hoursPagination").innerHTML = renderHoursPagination(pageCount);
  keepPaginationPosition('hoursList', [selected, hoursPhaseFilter, search, sort, perPage]);
}

function registrationOutcomes(record) {
  const project = assignments.find((item) => Number(item.id) === Number(record.assignmentId));
  const phaseCode = project?.phase || record.phases?.[0];
  return (record.outcomeCodes || [])
    .map((code) => outcomes.find((outcome) => outcome.code === code))
    .filter((outcome) => outcome && outcome.phase === phaseCode);
}

function renderHoursRow(record) {
  const index = records.indexOf(record);
  const linked = registrationOutcomes(record);
  const phaseBadges = (record.phases || []).map((code) => '<span class="hours-phase-badge ' + code + '">' + esc(hoursPhaseName(code)) + '</span>').join("");
  const outcomeChips = linked.slice(0, 2).map((outcome) =>
    '<span class="hours-outcome-chip ' + esc(outcome.phase) + '" title="' + esc(outcome.short + ' · ' + hoursPhaseName(outcome.phase)) + '">' + uiIcon("outcomes") + esc(outcome.short) + '</span>',
  ).join("") + (linked.length > 2 ? '<span class="hours-outcome-more" title="' + esc(linked.slice(2).map((outcome) => outcome.short).join(', ')) + '">+' + (linked.length - 2) + '</span>' : '');

  return '<button type="button" class="hours-registration-row" onclick="openHourRegistration(' + index + ')">' +
    '<span class="hours-assignment-cell"><span class="hours-assignment-icon">' + assignmentIcon(record) + '</span><span><strong>' + esc(record.title) + '</strong><small>' + esc(record.type) + ' · Leerjaar ' + record.year + '</small></span></span>' +
    '<span class="hours-phase-cell">' + phaseBadges + '</span>' +
    '<span class="hours-outcome-cell">' + outcomeChips + '</span>' +
    '<strong class="hours-value"><span>' + Number(record.hours).toLocaleString("nl-NL") + ' punten</span></strong>' +
    '<span class="hours-date">' + uiIcon('calendar') + esc(record.date) + '</span><span class="hours-chevron" aria-hidden="true">›</span></button>';
}

function renderHoursPagination(pageCount) {
  return pageButtons(hoursPage, pageCount, 'goToHoursPage');
}

function resetHoursAndRender() {
  hoursPage = 1;
  renderHours();
}

function selectHoursPhase(phaseCode) {
  hoursPhaseFilter = phaseCode;
  resetHoursAndRender();
}

function goToHoursPage(page) {
  hoursPage = page;
  renderHours();
}

function hoursPhaseName(code) {
  return studentPhaseName(code);
}

function openHourRegistration(index) {
  const record = records[index];
  const linked = registrationOutcomes(record);
  selectedRegistrationRecord = record;
  document.getElementById("registrationDetailTitle").textContent = record.title;
  document.getElementById("registrationDetailType").textContent = record.type;
  document.getElementById("registrationDetailDescription").textContent = record.description || "";
  document.getElementById("registrationDetailHours").textContent = Number(record.hours).toLocaleString("nl-NL") + " punten";
  document.getElementById("registrationDetailYear").textContent = "Leerjaar " + record.year;
  document.getElementById("registrationDetailPhase").textContent = (record.phases || []).map(hoursPhaseName).join(" · ");
  document.getElementById("registrationDetailDate").textContent = record.date;
  document.getElementById("registrationDetailApprover").textContent = record.approver;
  document.getElementById("registrationDetailOutcomeList").innerHTML = renderRegistrationOutcomeGroups(linked);
  document.getElementById("registrationDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#registrationDetailModal .modal-close").focus());
}

function renderRegistrationOutcomeGroups(linked) {
  return ["OF", "VF", "AF"].map((phaseCode) => {
    const phaseOutcomes = linked.filter((outcome) => outcome.phase === phaseCode);
    if (!phaseOutcomes.length) return "";
    return '<section class="registration-outcome-group"><span class="hours-phase-badge ' + phaseCode + '">' + esc(hoursPhaseName(phaseCode)) + '</span>' +
      phaseOutcomes.map((outcome) => '<button type="button" onclick="openRegistrationOutcome(\'' + esc(outcome.code) + '\')"><span><strong>' + esc(outcome.short) + '</strong><small>' + esc(outcome.code) + '</small></span><span aria-hidden="true">›</span></button>').join("") + '</section>';
  }).join("");
}

function closeRegistrationDetail() {
  document.getElementById("registrationDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function openRegistrationOutcome(code) {
  closeRegistrationDetail();
  if (code) openOutcome(code);
}

function openRegistrationAssignment() {
  const assignmentId = selectedRegistrationRecord?.assignmentId;
  closeRegistrationDetail();
  if (assignmentId) openAssignment(assignmentId);
}

function renderHourApprovals() {
  const totalHours = hourApprovals.reduce((total, approval) => total + Number(approval.hours || 0), 0);
  const latestDate = hourApprovals[0]?.date || "—";
  const badge = document.querySelector("#processingNotification .processing-badge");

  if (badge) badge.textContent = totalHours;
  document.getElementById("hoursApprovalTitle").textContent = totalHours + " nieuwe punten";
  document.getElementById("hoursApprovalDescription").textContent = hourApprovals.length
    ? "Deze punten zijn gecontroleerd en gekoppeld aan " + hourApprovals.length + " onderdelen."
    : "Er zijn nog geen gevalideerde punten beschikbaar.";
  document.getElementById("hoursApprovalTotal").textContent = totalHours + " punten";
  document.getElementById("hoursApprovalCount").textContent = hourApprovals.length;
  document.getElementById("hoursApprovalDate").textContent = latestDate;
  document.getElementById("hoursApprovalEyebrow").textContent = "URENVERWERKING · " + latestDate.toUpperCase();
  document.getElementById("hoursApprovalList").innerHTML = hourApprovals.length
    ? hourApprovals
        .map(
          (approval) =>
            '<article class="approval-item"><div class="approval-hours">' +
            Number(approval.hours || 0) +
            '<span>punten</span></div><div><h3>' +
            esc(approval.title) +
            "</h3><strong>" +
            esc(approval.outcome) +
            '</strong><span class="code">' +
            esc(approval.code) +
            '</span></div><div class="approval-person"><span>Goedgekeurd door</span><b>' +
            esc(approval.approver) +
            "</b><small>" +
            esc(approval.approverRole) +
            " · " +
            esc(approval.date) +
            "</small></div></article>",
        )
        .join("")
    : '<p class="dashboard-empty">Er zijn nog geen gevalideerde punten.</p>';
}

function openHoursApprovalModal() {
  show("studentDashboard");
  document.getElementById("hoursApprovalModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#hoursApprovalModal .modal-close").focus());
}

function closeHoursApprovalModal() {
  document.getElementById("hoursApprovalModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

const hoursProcessingReadKey = "hours-processing-latest-read";

// Vergelijk de huidige melding met de laatst gelezen melding.
function updateHoursProcessingReadState() {
  const isRead = loadLocalState(hoursProcessingReadKey) === "true",
    notification = document.getElementById("processingNotification"),
    badge = notification.querySelector(".processing-badge"),
    status = document.getElementById("processingReadStatus"),
    button = document.getElementById("markProcessingReadButton");
  notification.classList.toggle("read", isRead);
  badge.classList.toggle("hidden", isRead);
  const totalHours = hourApprovals.reduce((total, approval) => total + Number(approval.hours || 0), 0);
  notification.setAttribute("aria-label", isRead ? "Laatste urenverwerking bekijken" : totalHours + " nieuwe punten bekijken");
  notification.title = isRead ? "Urenverwerking gelezen" : "Nieuwe urenverwerking";
  status.textContent = isRead ? "Gelezen" : "Ongelezen";
  status.className = "processing-read-status " + (isRead ? "read" : "unread");
  button.classList.toggle("hidden", isRead);
}

function markHoursProcessingRead() {
  saveLocalState(hoursProcessingReadKey, "true");
  updateHoursProcessingReadState();
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("reflectionForm").classList.contains("hidden")) closeReflectionForm();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("hoursApprovalModal").classList.contains("hidden")) closeHoursApprovalModal();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("outcomeReflectionDetailModal").classList.contains("hidden")) closeOutcomeReflectionDetail();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("outcomeFeedbackDetailModal").classList.contains("hidden")) closeOutcomeFeedbackDetail();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("registrationDetailModal").classList.contains("hidden")) closeRegistrationDetail();
});

