// Projectenoverzicht, projectdetails en inleveren.
let assignmentPage = 1;
let assignmentFilterKey = '';

// Bouwt het projectenoverzicht en zoekt de gekoppelde leeruitkomsten op.
function renderAssignments() {
  const selectedStatus = document.getElementById("assignmentStatus").value.trim();

  const search = document.getElementById('projectSearch').value.trim().toLowerCase();
  const list = assignments.filter((assignment) => assignmentCategory(assignment) === selectedStatus && [assignment.title,assignment.description,...assignment.outcomeCodes.map((code) => outcomes.find((item) => item.code === code)?.short || code)].join(' ').toLowerCase().includes(search));

  const sort = document.getElementById("assignmentSort")?.value || "deadline";
  list.sort((left, right) => {
    if (sort === "title") return left.title.localeCompare(right.title, "nl");
    return assignmentDeadlineValue(left) - assignmentDeadlineValue(right);
  });
  const filterKey = JSON.stringify([selectedStatus, search, sort]);
  if (filterKey !== assignmentFilterKey) assignmentPage = 1;
  assignmentFilterKey = filterKey;
  const pageCount = Math.max(1, Math.ceil(list.length / 4));
  assignmentPage = Math.min(assignmentPage, pageCount);
  const visibleProjects = list.slice((assignmentPage - 1) * 4, assignmentPage * 4);

  const activeCount = assignments.filter((item) => assignmentCategory(item) === "active").length;
  document.getElementById("assignmentTotal").textContent = assignments.length + " PROJECTEN";
  document.getElementById("assignmentActiveCount").textContent = activeCount;
  document.getElementById('projectSummaryActive').textContent = activeCount;
  document.getElementById('projectSummaryCompleted').textContent = assignments.filter((item) => item.status === 'completed').length;
  const nearest = assignments.filter((item) => assignmentCategory(item) === 'active' && item.dueDateIso).sort((a,b) => assignmentDeadlineValue(a)-assignmentDeadlineValue(b))[0];
  document.getElementById('projectNearestDeadline').textContent = nearest?.dueDate || 'Geen deadline';
  document.getElementById("assignmentCompletedCount").textContent = assignments.filter((item) => item.status === "completed").length;
  document.getElementById("assignmentCount").textContent = list.length + " " + (list.length === 1 ? "project" : "projecten") + " gevonden";
  renderCurrentAssignment(selectedStatus);

  document.querySelectorAll("[data-assignment-status]").forEach((tab) => {
    const selected = tab.dataset.assignmentStatus === selectedStatus;
    tab.classList.toggle("active", selected);
    tab.setAttribute("aria-selected", String(selected));
  });

  const sectionCopy = {
    active: ["Actieve projecten", "Dit zijn je projecten die nog openstaan. Lever ze op tijd in."],
    completed: ["Afgeronde projecten", "Deze projecten heb je succesvol voltooid."],
  }[selectedStatus];
  document.getElementById("assignmentSectionTitle").textContent = sectionCopy[0];
  document.getElementById("assignmentSectionDescription").textContent = sectionCopy[1];

  document.getElementById("assignmentList").innerHTML = list.length
    ? visibleProjects
        .map((assignment) => {
          const icon = assignmentIcon(assignment);
          const displayStatus = assignmentCategory(assignment);
          const phaseBadges = assignmentPhaseCodes(assignment).map((code) => '<span>' + esc(code) + '</span>').join("");
          return '<article class="assignment-row ' + displayStatus + '">' +
            '<button class="assignment-row-open" type="button" onclick="openAssignment(' + assignment.id + ')" aria-label="Bekijk ' + esc(assignment.title) + '">' +
            '<span class="assignment-row-icon" aria-hidden="true">' + icon + '</span>' +
            '<span class="assignment-row-copy"><strong>' + esc(assignment.title) + '</strong><small>' + esc(assignment.description) + '</small><span class="assignment-phase-badges" aria-label="Fases">' + phaseBadges + '</span></span>' +
            '<span class="assignment-row-meta assignment-outcome-meta">' + assignmentListIcon("book") + '<span><b>' + esc(assignmentPhaseNames(assignment)[0]) + '</b><small>' + (assignment.status === 'completed' ? Number(assignment.hours || 0).toLocaleString('nl-NL') + ' punten toegekend' : 'Nog geen punten toegekend') + '</small></span></span>' +
            '<span class="assignment-row-meta">' + assignmentListIcon("calendar") + '<b>' + esc(assignment.dueDate || "Geen deadline") + '</b></span>' +
            '<span class="assignment-row-status ' + displayStatus + '"><i></i>' + assignmentDisplayStatus(assignment) + '</span>' +
            '<span class="assignment-row-chevron" aria-hidden="true">›</span></button></article>';
        })
        .join("")
    : '<div class="empty-state"><span class="empty-icon">⌕</span><h2>Geen projecten gevonden</h2><p>Pas de zoekterm of filters aan.</p></div>';
  document.getElementById('assignmentPagination').innerHTML = pageButtons(assignmentPage, pageCount, 'goToAssignmentPage');
  keepPaginationPosition('assignmentList', filterKey);
}

function goToAssignmentPage(page) {
  assignmentPage = page;
  renderAssignments();
}

function selectAssignmentStatus(status) {
  document.getElementById("assignmentStatus").value = status;
  renderAssignments();
}

function assignmentDeadlineValue(assignment) {
  if (assignment.dueDateIso) return new Date(assignment.dueDateIso + "T00:00:00").getTime();
  const value = assignment.dueDate;
  if (!value) return Number.MAX_SAFE_INTEGER;
  const months = { januari: 0, februari: 1, maart: 2, april: 3, mei: 4, juni: 5, juli: 6, augustus: 7, september: 8, oktober: 9, november: 10, december: 11 };
  const parts = String(value).toLowerCase().split(/\s+/);
  return parts.length === 3 && months[parts[1]] !== undefined ? new Date(Number(parts[2]), months[parts[1]], Number(parts[0])).getTime() : Number.MAX_SAFE_INTEGER;
}

function isAssignmentLate(assignment) {
  if (assignment.status !== "active" || !assignment.dueDateIso) return false;
  const deadline = new Date(assignment.dueDateIso + "T23:59:59");
  return deadline.getTime() < Date.now();
}

function assignmentCategory(assignment) {
  return assignment.status === "completed" ? "completed" : "active";
}

function assignmentPhaseCodes(assignment) {
  const codes = Array.isArray(assignment.phases) && assignment.phases.length ? assignment.phases : [assignment.phase];
  return codes.filter(Boolean).slice(0, 1);
}

function assignmentPhaseNames(assignment) {
  return assignmentPhaseCodes(assignment).map((code) => phases.find((item) => item[0] === code)?.[1] || code);
}

function assignmentDisplayStatus(assignment) {
  return assignment.status === "completed" ? "Afgerond" : "Actief";
}

function renderCurrentAssignment(selectedStatus) {
  const current = assignments
    .filter((assignment) => assignment.status === "active" && !isAssignmentLate(assignment))
    .sort((left, right) => assignmentDeadlineValue(left) - assignmentDeadlineValue(right))[0]
    || assignments.find((assignment) => assignment.status === "active");
  const card = document.getElementById("currentAssignmentCard");
  card.classList.toggle("hidden", !current || selectedStatus !== "active");
  document.getElementById('projectSpotlight').classList.toggle('hidden', !current || selectedStatus !== 'active');
  if (!current || selectedStatus !== "active") return;

  document.getElementById("currentAssignmentIcon").innerHTML = assignmentIcon(current);
  document.getElementById("currentAssignmentTitle").textContent = current.title;
  document.getElementById("currentAssignmentDescription").textContent = current.description;
  document.getElementById("currentAssignmentPhases").textContent = assignmentPhaseNames(current).join(" · ");
  document.getElementById("currentAssignmentDueDate").textContent = current.dueDate || "Geen deadline";
  document.getElementById("currentAssignmentOutcomes").textContent = 'Max. ' + current.maxPoints + ' punten';
  document.getElementById("currentAssignmentButton").onclick = () => openAssignment(current.id);
}

function assignmentIcon(assignment) {
  const text = (assignment.title + " " + assignment.type + " " + assignment.description).toLowerCase();
  let paths = '<path d="M7 3h8l4 4v14H7z"/><path d="M15 3v5h5M10 13h6M10 17h4"/>';
  if (/software|code|program|app|web|test/.test(text)) paths = '<path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"/>';
  else if (/onderzoek|analyse|probleem|signal|evalu/.test(text)) paths = '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4M9 9h4M11 7v4"/>';
  else if (/present|pitch|advies/.test(text)) paths = '<path d="M4 4h16v12H4zM8 20l4-4 4 4M8 9h2v4H8zM12 7h2v6h-2zM16 10h2v3h-2z"/>';
  else if (/samen|team|groep|rol|bijdrage/.test(text)) paths = '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="2.5"/><path d="M3 20v-2a5 5 0 0 1 10 0v2M13 16a4 4 0 0 1 8 2v2"/>';
  else if (/reflect|verslag|document|plan/.test(text)) paths = '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 12h7M9 16h7"/>';
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + paths + '</svg>';
}

function assignmentListIcon(type) {
  const paths = type === "book" ? '<path d="M4 5a4 4 0 0 1 4-2h4v17H8a4 4 0 0 0-4 2zM20 5a4 4 0 0 0-4-2h-4v17h4a4 4 0 0 1 4 2z"/>' : '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>';
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + paths + '</svg>';
}

// Vult de detailpagina van de gekozen project en toont daar de koppelingen.
function openAssignment(id) {
  const assignment = assignments.find((item) => item.id === id);
  if (!assignment) return;
  selectedAssignmentId = id;

  const phaseName = assignmentPhaseNames(assignment).join(" · ");
  const linkedOutcomes = assignment.outcomeCodes
    .map((code) => outcomes.find((outcome) => outcome.code === code))
    .filter(Boolean);

  document.getElementById("assignmentDetailType").textContent = assignment.type;
  document.getElementById("assignmentDetailTitle").textContent = assignment.title;
  document.getElementById("assignmentDetailIntro").textContent = assignmentDisplayStatus(assignment) + " · " + phaseName;
  document.getElementById("assignmentDetailDescription").textContent = assignment.description;
  document.getElementById("assignmentDetailPhase").textContent = phaseName;
  document.getElementById("assignmentDetailDueDate").textContent = assignment.dueDate;
  document.getElementById("assignmentDetailHours").textContent = assignment.hours + ' / ' + assignment.maxPoints + " punten";
  document.getElementById("assignmentDetailOutcomeCount").textContent = linkedOutcomes.length;
  renderAssignmentFiles(assignment);

  const status = document.getElementById("assignmentDetailStatus");
  status.className = "assignment-status " + (isAssignmentLate(assignment) ? "late" : assignment.status);
  status.textContent = assignmentDisplayStatus(assignment);

  const submitButton = document.getElementById("assignmentSubmitButton");
  const reopenButton = document.getElementById("assignmentReopenButton");
  const submissionNote = document.getElementById("assignmentSubmissionNote");
  submitButton.classList.toggle("hidden", assignment.status !== "active");
  reopenButton.classList.toggle("hidden", assignment.status !== "review");
  submissionNote.textContent =
    assignment.status === "active"
      ? "Lever je project in wanneer alle onderdelen klaar zijn."
      : assignment.status === "review"
        ? "Deze project is ingeleverd en wacht op beoordeling door de docent."
        : "Deze project is beoordeeld en afgerond.";

  document.getElementById("assignmentDetailOutcomeList").innerHTML = linkedOutcomes
    .map(
      (outcome) =>
        '<button class="assignment-outcome" type="button" onclick="openOutcome(\'' +
        esc(outcome.code) +
        "', 'assignmentDetail')\"><span><b>" +
        esc(outcome.short) +
        '</b><small class="code">' +
        esc(outcome.code) +
        '</small></span><span aria-hidden="true">→</span></button>',
    )
    .join("");

  show("assignmentDetail");
}

function renderAssignmentFiles(assignment) {
  const files = Array.isArray(assignment.files) ? assignment.files : [];
  const editable = assignment.status === "active" || assignment.status === "concept";
  document.getElementById("assignmentFileUploadLabel").classList.toggle("hidden", !editable);
  document.getElementById("assignmentFileHelp").textContent = editable
    ? "Maximaal 10 MB per bestand. PDF, Office, afbeeldingen en ZIP zijn toegestaan."
    : assignment.status === "review"
      ? "Zet de project terug naar Projecten om bestanden toe te voegen of te verwijderen."
      : "Bij een afgeronde project kunnen bestanden alleen worden bekeken.";

  document.getElementById("assignmentFileList").innerHTML = files.length
    ? files.map((file) =>
        '<div class="assignment-file-row"><span class="assignment-file-icon" aria-hidden="true">' + assignmentFileIcon(file.name) + '</span>' +
        '<span class="assignment-file-copy"><a href="' + esc(file.downloadUrl) + '">' + esc(file.name) + '</a><small>' + formatFileSize(file.size) + ' · ' + esc(file.uploadedAt) + '</small></span>' +
        (editable ? '<button class="assignment-file-delete" type="button" onclick="deleteAssignmentFile(' + file.id + ')" aria-label="Verwijder ' + esc(file.name) + '">×</button>' : '') +
        '</div>',
      ).join("")
    : '<div class="assignment-files-empty"><span aria-hidden="true">⇧</span><p>Nog geen bestanden toegevoegd.</p></div>';
}

function assignmentFileIcon(name) {
  const extension = String(name).split(".").pop().toUpperCase();
  return '<span>' + esc(extension.length <= 4 ? extension : "FILE") + '</span>';
}

function formatFileSize(bytes) {
  const size = Number(bytes) || 0;
  if (size < 1024) return size + " B";
  if (size < 1024 * 1024) return Math.round(size / 1024) + " KB";
  return (size / (1024 * 1024)).toFixed(1).replace(".0", "") + " MB";
}

async function uploadAssignmentFile(input) {
  const assignment = assignments.find((item) => item.id === selectedAssignmentId);
  const file = input.files?.[0];
  if (!assignment || !file) return;

  const label = document.getElementById("assignmentFileUploadLabel");
  label.classList.add("uploading");
  label.querySelector("span").textContent = "Uploaden…";
  const formData = new FormData();
  formData.append("file", file);

  try {
    const savedFile = await apiRequest(apiUrl(window.LARAVEL_API.assignmentFileStore, assignment.id), {
      method: "POST",
      body: formData,
    });
    assignment.files = [savedFile, ...(assignment.files || [])];
    renderAssignmentFiles(assignment);
    hint("Het bestand is aan je project toegevoegd.");
  } catch (error) {
    hint(error.message);
  } finally {
    input.value = "";
    label.classList.remove("uploading");
    label.querySelector("span").textContent = "+ Bestand toevoegen";
  }
}

async function deleteAssignmentFile(fileId) {
  const assignment = assignments.find((item) => item.id === selectedAssignmentId);
  if (!assignment) return;
  if (!window.confirm("Weet je zeker dat je dit bestand wilt verwijderen?")) return;

  try {
    await apiRequest(apiUrl(window.LARAVEL_API.assignmentFileDelete, fileId), { method: "DELETE" });
    assignment.files = (assignment.files || []).filter((file) => file.id !== fileId);
    renderAssignmentFiles(assignment);
    hint("Het bestand is verwijderd.");
  } catch (error) {
    hint(error.message);
  }
}

async function submitAssignment() {
  const assignment = assignments.find((item) => item.id === selectedAssignmentId);
  if (!assignment || assignment.status !== "active") return;

  try {
    const result = await apiRequest(apiUrl(window.LARAVEL_API.assignmentSubmit, assignment.id), {
      method: "POST",
      body: JSON.stringify({}),
    });
    assignment.status = result.status;
    document.getElementById("assignmentStatus").value = "review";
    renderAssignments();
    openAssignment(assignment.id);
    hint("Je project is ingeleverd en staat nu bij Beoordeling.");
  } catch (error) {
    hint(error.message);
  }
}

async function reopenAssignment() {
  const assignment = assignments.find((item) => item.id === selectedAssignmentId);
  if (!assignment || assignment.status !== "review") return;

  try {
    const result = await apiRequest(apiUrl(window.LARAVEL_API.assignmentReopen, assignment.id), {
      method: "POST",
      body: JSON.stringify({}),
    });
    assignment.status = result.status;
    document.getElementById("assignmentStatus").value = isAssignmentLate(assignment) ? "late" : "active";
    renderAssignments();
    openAssignment(assignment.id);
    hint("De project staat weer bij Projecten. Je kunt de bestanden en inhoud verder aanpassen.");
  } catch (error) {
    hint(error.message);
  }
}

