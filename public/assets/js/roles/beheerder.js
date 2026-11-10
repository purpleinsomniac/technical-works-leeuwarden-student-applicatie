window.RoleApp.initializeRole({
  label: "Beheerder",
  routes: [
    ["Overzicht", "adminProcessing"],
    ["Gebruikers", "adminUsers"],
    ["Groepen", "adminGroups"],
    ["Leeruitkomsten", "adminLearningOutcomes"],
    ["Applicatie-instellingen", "adminApplicationSettings"],
    ["Instellingen", "adminPreferencesSettings"],
  ],
});
RoleSettings.initialize();

document.querySelectorAll("[data-admin-user-role-selector]").forEach((selector) => {
  const updateStudentFields = () => {
    const fields = selector.closest("form")?.querySelector("[data-admin-student-fields]");
    const year = fields?.querySelector("select[name='leerjaar']");
    if (!fields || !year) return;
    fields.hidden = selector.value !== "student";
    year.disabled = selector.value !== "student";
  };
  selector.addEventListener("change", updateStudentFields);
  updateStudentFields();
});

const applicationAccentPicker = document.getElementById("application-accent-color");
const applicationAccentValue = document.getElementById("applicationAccentColorValue");
applicationAccentPicker?.addEventListener("input", () => {
  document.getElementById("app")?.style.setProperty("--application-accent", applicationAccentPicker.value);
  if (applicationAccentValue) applicationAccentValue.textContent = applicationAccentPicker.value.toUpperCase();
});
document.getElementById("application-style")?.addEventListener("change", (event) => {
  document.getElementById("app")?.setAttribute("data-application-style", event.target.value);
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

if (new URLSearchParams(window.location.search).get("page") === "groepen") {
  show("adminGroups");
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

document.querySelectorAll("[data-class-student-search]").forEach((search) => {
  const form = search.closest(".admin-class-form");
  const options = [...form.querySelectorAll("[data-class-student-option]")];

  search.addEventListener("input", () => {
    const term = search.value.trim().toLocaleLowerCase("nl");
    options.forEach((option) => {
      option.hidden = !option.dataset.classStudentOption.includes(term);
    });
  });
});
