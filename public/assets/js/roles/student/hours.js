// Urenoverzicht, registratiedetails en urenmeldingen.
let hoursPhaseFilter = "all";
let hoursPage = 1;
let selectedRegistrationRecord = null;

function renderHours() {
  const selected = document.getElementById("hoursYear").value;
  const totalData = Object.values(hoursByYear).reduce(
    (sum, item) => ({
      total: sum.total + item.total,
      orientation: sum.orientation + item.orientation,
      deepening: sum.deepening + item.deepening,
      completion: sum.completion + item.completion,
    }),
    { total: 0, orientation: 0, deepening: 0, completion: 0 },
  );
  const isTotal = selected === "total";
  const data = isTotal ? totalData : hoursByYear[Number(selected)];
  const target = isTotal ? 3200 : 800;
  const remaining = Math.max(0, target - data.total);
  const percentage = target ? Math.round((data.total / target) * 100) : 0;
  const periodLabel = isTotal ? "Totaal opleiding" : "Leerjaar " + selected;

  document.getElementById("registrationsYear").textContent = periodLabel;
  document.getElementById("hoursYearTotal").textContent = data.total.toLocaleString("nl-NL");
  document.getElementById("hoursTarget").textContent = target.toLocaleString("nl-NL");
  document.getElementById("hoursProgressPercent").textContent = percentage + "%";
  document.getElementById("hoursProgressRing").style.setProperty("--hours-progress", Math.min(360, percentage * 3.6) + "deg");
  document.getElementById("hoursYearPercentage").textContent = "van de urennorm behaald";

  const segments = [
    ["orientation", data.orientation, "Oriëntatiefase"],
    ["deepening", data.deepening, "Verdiepingsfase"],
    ["completion", data.completion, "Afrondingsfase"],
    ["remaining", remaining, "Nog te behalen"],
  ];
  document.getElementById("hoursStackedBar").innerHTML = segments.map(([kind, hours, label]) =>
    '<span class="hours-segment ' + kind + '" style="width:' + Math.max(0, (hours / target) * 100) + '%" title="' + esc(label) + ': ' + hours + ' uur"></span>',
  ).join("");
  document.getElementById("hoursLegend").innerHTML = segments.map(([kind, hours, label]) =>
    '<div><span class="legend-color ' + kind + '"></span><span><b>' + hours.toLocaleString("nl-NL") + ' uur</b><small>' + esc(label) + '</small></span></div>',
  ).join("");

  renderHoursRegistrations(selected, periodLabel);
}

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
    : '<div class="hours-empty"><span>⌕</span><h3>Geen registraties gevonden</h3><p>Pas de periode, fase of zoekopdracht aan.</p></div>';
  document.getElementById("hoursPagination").innerHTML = renderHoursPagination(pageCount);
}

function renderHoursRow(record) {
  const index = records.indexOf(record);
  const linked = (record.outcomeCodes || []).map((code) => outcomes.find((outcome) => outcome.code === code)).filter(Boolean);
  const phaseBadges = (record.phases || []).map((code) => '<span class="hours-phase-badge ' + code + '">' + esc(hoursPhaseName(code)) + '</span>').join("");
  const outcomeChips = linked.slice(0, 3).map((outcome) =>
    '<span class="hours-outcome-chip ' + esc(outcome.phase) + '" title="' + esc(hoursPhaseName(outcome.phase)) + '">' + uiIcon("outcomes") + esc(outcome.short) + '</span>',
  ).join("") + (linked.length > 3 ? '<span class="hours-outcome-more">+' + (linked.length - 3) + '</span>' : '');

  return '<button type="button" class="hours-registration-row" onclick="openHourRegistration(' + index + ')">' +
    '<span class="hours-assignment-cell"><span class="hours-assignment-icon">' + assignmentIcon(record) + '</span><span><strong>' + esc(record.title) + '</strong><small>' + esc(record.type) + ' · Leerjaar ' + record.year + '</small></span></span>' +
    '<span class="hours-phase-cell">' + phaseBadges + '</span>' +
    '<span class="hours-outcome-cell">' + outcomeChips + '</span>' +
    '<strong class="hours-value">' + Number(record.hours).toLocaleString("nl-NL") + ' uur</strong>' +
    '<span class="hours-date">' + esc(record.date) + '</span><span class="hours-chevron" aria-hidden="true">›</span></button>';
}

function renderHoursPagination(pageCount) {
  if (pageCount <= 1) return "";
  let html = '<button type="button" ' + (hoursPage === 1 ? 'disabled' : '') + ' onclick="goToHoursPage(' + (hoursPage - 1) + ')">‹</button>';
  for (let page = 1; page <= pageCount; page += 1) {
    html += '<button type="button" class="' + (page === hoursPage ? 'active' : '') + '" onclick="goToHoursPage(' + page + ')">' + page + '</button>';
  }
  return html + '<button type="button" ' + (hoursPage === pageCount ? 'disabled' : '') + ' onclick="goToHoursPage(' + (hoursPage + 1) + ')">›</button>';
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
  const linked = (record.outcomeCodes || []).map((code) => outcomes.find((outcome) => outcome.code === code)).filter(Boolean);
  selectedRegistrationRecord = record;
  selectedRegistrationCode = linked[0]?.code || null;
  document.getElementById("registrationDetailTitle").textContent = record.title;
  document.getElementById("registrationDetailType").textContent = record.type;
  document.getElementById("registrationDetailDescription").textContent = record.description || "";
  document.getElementById("registrationDetailHours").textContent = Number(record.hours).toLocaleString("nl-NL") + " uur";
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
  document.getElementById("hoursApprovalTitle").textContent = totalHours + " nieuwe uren";
  document.getElementById("hoursApprovalDescription").textContent = hourApprovals.length
    ? "Deze uren zijn gecontroleerd en gekoppeld aan " + hourApprovals.length + " onderdelen."
    : "Er zijn nog geen gevalideerde uren beschikbaar.";
  document.getElementById("hoursApprovalTotal").textContent = totalHours + " uur";
  document.getElementById("hoursApprovalCount").textContent = hourApprovals.length;
  document.getElementById("hoursApprovalDate").textContent = latestDate;
  document.getElementById("hoursApprovalEyebrow").textContent = "URENVERWERKING · " + latestDate.toUpperCase();
  document.getElementById("hoursApprovalList").innerHTML = hourApprovals.length
    ? hourApprovals
        .map(
          (approval) =>
            '<article class="approval-item"><div class="approval-hours">' +
            Number(approval.hours || 0) +
            '<span>uur</span></div><div><h3>' +
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
    : '<p class="dashboard-empty">Er zijn nog geen gevalideerde uren.</p>';
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
  notification.setAttribute("aria-label", isRead ? "Laatste urenverwerking bekijken" : totalHours + " nieuwe uren bekijken");
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

