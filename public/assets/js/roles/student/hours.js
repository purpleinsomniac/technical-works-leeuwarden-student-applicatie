// Urenoverzicht, registratiedetails en urenmeldingen.

function renderHours() {
  const selected = document.getElementById("hoursYear").value,
    totalData = Object.values(hoursByYear).reduce(
      (sum, item) => ({
        total: sum.total + item.total,
        orientation: sum.orientation + item.orientation,
        deepening: sum.deepening + item.deepening,
        completion: sum.completion + item.completion,
      }),
      { total: 0, orientation: 0, deepening: 0, completion: 0 },
    ),
    isTotal = selected === "total",
    data = isTotal ? totalData : hoursByYear[Number(selected)],
    target = isTotal ? 3200 : 800,
    remaining = Math.max(0, target - data.total),
    percentage = Math.round((data.total / target) * 100),
    periodLabel = isTotal ? "Totaal opleiding" : "Leerjaar " + selected,
    periodRecords = isTotal ? records : records.filter((record) => record[4] === Number(selected));
  document.getElementById("hoursSelectedYear").textContent = periodLabel;
  document.getElementById("registrationsYear").textContent = periodLabel;
  document.querySelector(".hours-total>div>span").textContent = " / " + target.toLocaleString("nl-NL") + " uur";
  document.querySelector(".hours-scale span:last-child").textContent = "Doel: " + target.toLocaleString("nl-NL") + " uur";
  document.getElementById("hoursYearTotal").textContent = data.total.toLocaleString("nl-NL");
  document.getElementById("hoursYearPercentage").textContent = percentage + "% van de urennorm behaald";
  document.getElementById("hoursScalePercentage").textContent = percentage + "% van je doel behaald";
  document.getElementById("hoursOrientation").textContent = data.orientation + " uur";
  document.getElementById("hoursDeepening").textContent = data.deepening + " uur";
  document.getElementById("hoursCompletion").textContent = data.completion + " uur";
  const segments = [
    ["orientation", data.orientation, "Oriëntatiefase"],
    ["deepening", data.deepening, "Verdiepingsfase"],
    ["completion", data.completion, "Afrondingsfase"],
    ["remaining", remaining, "Nog te behalen"],
  ];
  document.getElementById("hoursStackedBar").innerHTML = segments
    .map(
      ([kind, hours, label]) =>
        '<span class="hours-segment ' +
        kind +
        '" style="width:' +
        (hours / target) * 100 +
        '%" title="' +
        label +
        ": " +
        hours +
        ' uur">' +
        (kind === "remaining" ? "" : "<span>" + hours + " uur</span>") +
        "</span>",
    )
    .join("");
  document.getElementById("hoursLegend").innerHTML = segments
    .map(
      ([kind, hours, label]) =>
        '<div><span class="legend-color ' + kind + '"></span><span><b>' + hours.toLocaleString("nl-NL") + " uur</b> " + label + "</span></div>",
    )
    .join("");
  const periodItems = [...Object.entries(hoursByYear).sort((a, b) => a[0] - b[0]), ["total", totalData]];
  document.getElementById("yearlyHoursOverview").innerHTML = periodItems
    .map(([key, item]) => {
      const itemIsTotal = key === "total",
        itemTarget = itemIsTotal ? 3200 : 800,
        label = itemIsTotal ? "Totaal" : "Leerjaar " + key;
      return (
        '<button type="button" class="year-total ' +
        (key === selected ? "active" : "") +
        " " +
        (itemIsTotal ? "grand-total" : "") +
        '" onclick="selectHoursYear(\'' +
        key +
        "')\"><span>" +
        label +
        "</span><strong>" +
        item.total.toLocaleString("nl-NL") +
        ' uur</strong><div class="bar"><span style="width:' +
        (item.total / itemTarget) * 100 +
        '%"></span></div><small>' +
        Math.round((item.total / itemTarget) * 100) +
        "% van " +
        (itemIsTotal ? "de opleidingsnorm" : "de leerjaarnorm") +
        "</small></button>"
      );
    })
    .join("");
  document.getElementById("hoursList").innerHTML = emptyHours
    ? '<div class="empty-state"><h3>Nog geen gevalideerde uren</h3><p>Er zijn voor deze periode nog geen goedgekeurde registraties zichtbaar.</p><button class="button" onclick="emptyHours=false;renderHours()">Toon voorbeeldgegevens</button></div>'
    : periodRecords
        .map((r) => {
          const index = records.indexOf(r),
            meta = registrationMeta[index];
          return (
            '<button type="button" class="simple click registration-row" onclick="openHourRegistration(' +
            index +
            ')"><span><b>' +
            esc(r[0]) +
            '</b><br><span class="small dim">' +
            esc(r[1]) +
            " · Leerjaar " +
            r[4] +
            '</span></span><span class="code">' +
            esc(r[2]) +
            '</span><span class="registration-row-meta"><b>' +
            r[3] +
            " uur</b><small>" +
            esc(meta[0]) +
            "</small></span></button>"
          );
        })
        .join("");
}

function selectHoursYear(period) {
  document.getElementById("hoursYear").value = period;
  emptyHours = false;
  renderHours();
}

function openHourRegistration(index) {
  const record = records[index],
    meta = registrationMeta[index],
    outcome = outcomes.find((item) => item.code === record[2]),
    phaseName = outcome ? (phases.find((item) => item[0] === outcome.phase) || [])[1] : "Onbekende fase";
  selectedRegistrationCode = record[2];
  document.getElementById("registrationDetailTitle").textContent = record[0];
  document.getElementById("registrationDetailType").textContent = record[1];
  document.getElementById("registrationDetailHours").textContent = record[3] + " uur";
  document.getElementById("registrationDetailYear").textContent = "Leerjaar " + record[4];
  document.getElementById("registrationDetailPhase").textContent = phaseName;
  document.getElementById("registrationDetailDate").textContent = meta[0];
  document.getElementById("registrationDetailApprover").textContent = meta[1];
  document.getElementById("registrationDetailOutcomeName").textContent = outcome ? outcome.short : "Leeruitkomst";
  document.getElementById("registrationDetailCode").textContent = record[2];
  document.getElementById("registrationDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#registrationDetailModal .modal-close").focus());
}

function closeRegistrationDetail() {
  document.getElementById("registrationDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function openRegistrationOutcome() {
  const code = selectedRegistrationCode;
  closeRegistrationDetail();
  if (code) openOutcome(code);
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

