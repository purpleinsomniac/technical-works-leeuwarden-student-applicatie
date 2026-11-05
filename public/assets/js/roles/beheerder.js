window.RoleApp.initializeRole({
  label: "Beheerder",
  routes: [
    ["Overzicht", "adminProcessing"],
    ["Gebruikers", "adminUsers"],
    ["Leeruitkomsten", "adminLearningOutcomes"],
    ["Applicatie-instellingen", "adminApplicationSettings"],
  ],
});

function toggleAdminUserForm() {
  const form = document.getElementById("createAdminUser");
  const button = document.getElementById("toggleAdminUserForm");

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

if (new URLSearchParams(window.location.search).get("page") === "gebruikers") {
  show("adminUsers");
}

if (new URLSearchParams(window.location.search).get("page") === "applicatie-instellingen") {
  show("adminApplicationSettings");
}

const adminUserRoleFilter = document.getElementById("adminUserRoleFilter");
if (adminUserRoleFilter) {
  const userRows = [...document.querySelectorAll("#adminUsers tbody tr[data-user-role]")];
  const emptyRow = document.querySelector("#adminUsers tbody [data-user-empty]");
  const noMatchesRow = document.getElementById("adminUsersNoMatches");
  const userCount = document.getElementById("adminUsersCount");

  adminUserRoleFilter.addEventListener("change", () => {
    const selectedRole = adminUserRoleFilter.value;
    const matchingRows = userRows.filter((row) => selectedRole === "all" || row.dataset.userRole === selectedRole);

    userRows.forEach((row) => {
      row.hidden = selectedRole !== "all" && row.dataset.userRole !== selectedRole;
    });

    if (emptyRow) emptyRow.hidden = selectedRole !== "all";
    if (noMatchesRow) noMatchesRow.hidden = matchingRows.length > 0 || selectedRole === "all";
    if (userCount) userCount.textContent = `${matchingRows.length} van ${userRows.length} gebruikers`;
  });
}
