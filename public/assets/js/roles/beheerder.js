window.RoleApp.initializeRole({
  label: "Beheerder",
  routes: [
    ["Overzicht", "adminProcessing"],
    ["Gebruikers", "adminUsers"],
    ["Leeruitkomsten", "adminLearningOutcomes"],
    ["Leeractiviteiten", "adminLearningActivities"],
    ["Logboek", "adminLog"],
  ],
});

function toggleLearningOutcomeForm() {
  const form = document.getElementById("createLearningOutcome");
  const button = document.getElementById("toggleLearningOutcomeForm");

  if (!form || !button) return;

  form.hidden = !form.hidden;
  button.setAttribute("aria-expanded", String(!form.hidden));

  if (!form.hidden) {
    form.scrollIntoView({ behavior: "smooth" });
  }
}

if (new URLSearchParams(window.location.search).get("page") === "leeruitkomsten") {
  show("adminLearningOutcomes");
}

if (new URLSearchParams(window.location.search).get("page") === "leeractiviteiten") {
  show("adminLearningActivities");
}

if (new URLSearchParams(window.location.search).get("page") === "gebruikers") {
  show("adminUsers");
}
