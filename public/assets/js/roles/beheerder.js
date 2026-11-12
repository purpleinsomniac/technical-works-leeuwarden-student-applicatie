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
    fields.querySelectorAll("input[name='group_ids[]']").forEach((input) => {
      input.disabled = selector.value !== "student";
    });
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

function toggleAdminForm(formId, buttonId, queryKey) {
  const form = document.getElementById(formId);
  const button = document.getElementById(buttonId);

  if (!form || !button) return;

  form.hidden = !form.hidden;
  button.setAttribute("aria-expanded", String(!form.hidden));

  const url = new URL(window.location.href);
  url.searchParams.set("page", "gebruikers");
  if (form.hidden) {
    url.searchParams.delete(queryKey);
  } else {
    url.searchParams.set(queryKey, "1");
  }
  window.history.replaceState({}, "", url.toString());

  if (!form.hidden) {
    form.scrollIntoView({ behavior: "smooth" });
  }
}

function toggleAdminUserForm() {
  toggleAdminForm("createAdminUser", "toggleAdminUserForm", "new");
}

function toggleStudentImportForm() {
  toggleAdminForm("studentImportForm", "toggleStudentImportForm", "import");
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
