// Opdrachtenoverzicht, opdrachtdetails en inleveren.

// Bouwt het opdrachtenoverzicht en zoekt de gekoppelde leeruitkomsten op.
function renderAssignments() {
  const search = document.getElementById("assignmentSearch").value.trim().toLowerCase();
  const selectedStatus = document.getElementById("assignmentStatus").value;

  const list = assignments.filter((assignment) => {
    const linkedOutcomes = assignment.outcomeCodes
      .map((code) => outcomes.find((outcome) => outcome.code === code))
      .filter(Boolean);
    const searchableText = [
      assignment.title,
      assignment.type,
      assignment.description,
      ...linkedOutcomes.flatMap((outcome) => [outcome.code, outcome.short]),
    ]
      .join(" ")
      .toLowerCase();

    return assignment.status === selectedStatus && (!search || searchableText.includes(search));
  });

  document.getElementById("assignmentTotal").textContent = assignments.length + " OPDRACHTEN";
  document.getElementById("assignmentActiveCount").textContent = assignments.filter((item) => item.status === "active").length;
  document.getElementById("assignmentReviewCount").textContent = assignments.filter((item) => item.status === "review").length;
  document.getElementById("assignmentCompletedCount").textContent = assignments.filter((item) => item.status === "completed").length;
  document.getElementById("assignmentCount").textContent = list.length + " " + (list.length === 1 ? "opdracht" : "opdrachten") + " gevonden";

  document.getElementById("assignmentList").innerHTML = list.length
    ? list
        .map((assignment) => {
          const phaseName = phases.find((item) => item[0] === assignment.phase)?.[1] || assignment.phase;

          return (
            '<article class="assignment-card card ' +
            assignment.status +
            '"><button class="assignment-card-open" type="button" onclick="openAssignment(' +
            assignment.id +
            ')"><div class="assignment-card-head"><div><span class="assignment-type">' +
            esc(assignment.type) +
            "</span><h2>" +
            esc(assignment.title) +
            '</h2></div><span class="assignment-status ' +
            assignment.status +
            '">' +
            assignmentStatuses[assignment.status] +
            '</span></div><p class="assignment-description">' +
            esc(assignment.description) +
            '</p><div class="assignment-meta compact"><div><span>Fase</span><b>' +
            esc(phaseName) +
            '</b></div><div><span>Inleverdatum</span><b>' +
            esc(assignment.dueDate) +
            '</b></div></div><span class="assignment-open-label">Bekijk opdrachtinformatie <b aria-hidden="true">→</b></span></button></article>'
          );
        })
        .join("")
    : '<div class="empty-state"><span class="empty-icon">⌕</span><h2>Geen opdrachten gevonden</h2><p>Pas de zoekterm of filters aan.</p></div>';
}

// Vult de detailpagina van de gekozen opdracht en toont daar de koppelingen.
function openAssignment(id) {
  const assignment = assignments.find((item) => item.id === id);
  if (!assignment) return;
  selectedAssignmentId = id;

  const phaseName = phases.find((item) => item[0] === assignment.phase)?.[1] || assignment.phase;
  const linkedOutcomes = assignment.outcomeCodes
    .map((code) => outcomes.find((outcome) => outcome.code === code))
    .filter(Boolean);

  document.getElementById("assignmentDetailType").textContent = assignment.type;
  document.getElementById("assignmentDetailTitle").textContent = assignment.title;
  document.getElementById("assignmentDetailIntro").textContent = assignmentStatuses[assignment.status] + " · " + phaseName;
  document.getElementById("assignmentDetailDescription").textContent = assignment.description;
  document.getElementById("assignmentDetailPhase").textContent = phaseName;
  document.getElementById("assignmentDetailDueDate").textContent = assignment.dueDate;
  document.getElementById("assignmentDetailHours").textContent = assignment.hours + " uur";
  document.getElementById("assignmentDetailOutcomeCount").textContent = linkedOutcomes.length;

  const status = document.getElementById("assignmentDetailStatus");
  status.className = "assignment-status " + assignment.status;
  status.textContent = assignmentStatuses[assignment.status];

  const submitButton = document.getElementById("assignmentSubmitButton");
  const submissionNote = document.getElementById("assignmentSubmissionNote");
  submitButton.classList.toggle("hidden", assignment.status !== "active");
  submissionNote.textContent =
    assignment.status === "active"
      ? "Lever je opdracht in wanneer alle onderdelen klaar zijn."
      : assignment.status === "review"
        ? "Deze opdracht is ingeleverd en wacht op beoordeling door de docent."
        : "Deze opdracht is beoordeeld en afgerond.";

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
    hint("Je opdracht is ingeleverd en staat nu bij Beoordeling.");
  } catch (error) {
    hint(error.message);
  }
}

