// Urenoverzicht, registratiedetails en urenmeldingen.
let hoursPhaseFilter = "OF";
// Voorlopige puntendoelen; pas deze aan zodra de opdrachtgever de normen vastlegt.
const phasePointTargets = { OF: 800, VF: 800, AF: 800 };
let hoursPage = 1;
let selectedRegistrationRecord = null;
let challengeState = null;
let challengeSyncBusy = false;

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
  } catch (error) {
    document.querySelector('.xp-challenge p').textContent = 'Challenges konden niet worden geladen. Open Punten opnieuw om het nogmaals te proberen.';
  } finally { challengeSyncBusy = false; }
}

function openChallengeDetail() {
  const modal = document.getElementById('challengeModal');
  const active = challengeState?.active;
  document.getElementById('challengeDetailTitle').textContent = active?.title || 'Alle challenges voltooid';
  document.getElementById('challengeDetailCopy').textContent = active ? active.description + ' Fase: ' + active.phase + '. Voortgang: ' + active.progress + ' / ' + active.target + '. Beloning: ' + active.reward + ' XP.' : 'Je hebt alle beschikbare challenges voltooid.';
  document.getElementById('challengeHistory').innerHTML = (challengeState?.history || []).map((item) => '<li>' + esc(item.title) + ' · +' + item.reward + ' XP · Voltooid</li>').join('') || '<li>Je hebt nog geen challenges voltooid.</li>';
  modal.classList.remove('hidden');
  modal.querySelector('.modal-close').focus();
}
function closeChallengeDetail() { document.getElementById('challengeModal').classList.add('hidden'); }
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeChallengeDetail(); });
window.addEventListener('focus', syncChallenges);

function renderHours() {
  const selected = document.getElementById("hoursYear").value;
  const isTotal = selected === "total";
  const target = outcomes.filter((outcome) => outcome.phase === hoursPhaseFilter).reduce((sum, outcome) => sum + Number(outcome.target || 0), 0) || phasePointTargets[hoursPhaseFilter];
  const phasePoints = records
    .filter((record) => (isTotal || Number(record.year) === Number(selected)) && record.phases?.[0] === hoursPhaseFilter)
    .reduce((sum, record) => sum + (Number(record.hours) || 0), 0);
  const remaining = Math.max(0, target - phasePoints);
  const percentage = target ? Math.min(100, Math.round((phasePoints / target) * 100)) : 0;
  const periodLabel = isTotal ? "Totaal opleiding" : "Leerjaar " + selected;

  document.getElementById("registrationsYear").textContent = periodLabel;
  document.getElementById("hoursYearTotal").textContent = phasePoints.toLocaleString("nl-NL");
  document.getElementById("hoursTarget").textContent = target.toLocaleString("nl-NL");
  document.getElementById("hoursProgressPercent").textContent = percentage + "%";
  document.getElementById("hoursProgressRing").style.setProperty("--hours-progress", Math.min(360, percentage * 3.6) + "deg");
  document.getElementById("hoursYearPercentage").textContent = hoursPhaseName(hoursPhaseFilter) + ' · ' + (remaining === 0 ? 'Puntendoel behaald' : 'Nog ' + remaining.toLocaleString('nl-NL') + ' punten nodig');

  renderHoursRegistrations(selected, periodLabel);
  renderXp();
  syncChallenges();
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
  ['xpLevelBar', 'xpHelpBar'].forEach((id) => document.getElementById(id).style.width = (progress / 10) + '%');
  document.getElementById('xpRemaining').textContent = 'Nog ' + (1000 - progress) + ' XP tot level ' + (level + 1) + '!';
}

function openXpHelp() {
  document.getElementById('xpHelpPanel').classList.remove('hidden');
  document.getElementById('xpHelpPanel').querySelector('button').focus();
}
function closeXpHelp() { document.getElementById('xpHelpPanel').classList.add('hidden'); }
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeXpHelp(); });

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
  const outcomeChips = linked.slice(0, 3).map((outcome) =>
    '<span class="hours-outcome-chip ' + esc(outcome.phase) + '" title="' + esc(hoursPhaseName(outcome.phase)) + '">' + uiIcon("outcomes") + esc(outcome.short) + '</span>',
  ).join("") + (linked.length > 3 ? '<span class="hours-outcome-more">+' + (linked.length - 3) + '</span>' : '');

  return '<button type="button" class="hours-registration-row" onclick="openHourRegistration(' + index + ')">' +
    '<span class="hours-assignment-cell"><span class="hours-assignment-icon">' + assignmentIcon(record) + '</span><span><strong>' + esc(record.title) + '</strong><small>' + esc(record.type) + ' · Leerjaar ' + record.year + '</small></span></span>' +
    '<span class="hours-phase-cell">' + phaseBadges + '</span>' +
    '<span class="hours-outcome-cell">' + outcomeChips + '</span>' +
    '<strong class="hours-value">' + Number(record.hours).toLocaleString("nl-NL") + ' punten</strong>' +
    '<span class="hours-date">' + esc(record.date) + '</span><span class="hours-chevron" aria-hidden="true">›</span></button>';
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
  return phases.find((phaseItem) => phaseItem[0] === code)?.[1] || code;
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
  if (code) openOutcome(code, "hours");
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
  if (role === "student") show("studentDashboard");
  document.getElementById("hoursApprovalModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#hoursApprovalModal .modal-close").focus());
}

function closeHoursApprovalModal() {
  document.getElementById("hoursApprovalModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

const hoursProcessingReadKey = "hours-processing-latest-read";

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

