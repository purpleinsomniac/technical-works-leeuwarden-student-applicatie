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
const adminUserSearch = document.getElementById("adminUserSearch");
const adminUserSearchClear = document.getElementById("adminUserSearchClear");
if (adminUserRoleFilter && adminUserSearch) {
  const userRows = [...document.querySelectorAll("#adminUsers tbody tr[data-user-role]")];
  const emptyRow = document.querySelector("#adminUsers tbody [data-user-empty]");
  const noMatchesRow = document.getElementById("adminUsersNoMatches");
  const userCount = document.getElementById("adminUsersCount");

  const filterUsers = () => {
    const selectedRole = adminUserRoleFilter.value;
    const term = adminUserSearch.value.trim().toLocaleLowerCase("nl");
    const matchingRows = userRows.filter((row) => {
      const matchesRole = selectedRole === "all" || row.dataset.userRole === selectedRole;
      const matchesName = row.dataset.userSearch.includes(term);
      return matchesRole && matchesName;
    });
    const matchingRowSet = new Set(matchingRows);

    userRows.forEach((row) => {
      row.hidden = !matchingRowSet.has(row);
    });

    const showingEmptyState = userRows.length === 0 && selectedRole === "all" && term === "";
    if (emptyRow) emptyRow.hidden = !showingEmptyState;
    if (noMatchesRow) noMatchesRow.hidden = matchingRows.length > 0 || showingEmptyState;
    if (userCount) userCount.textContent = term || selectedRole !== "all"
      ? `${matchingRows.length} van ${userRows.length} gebruikers`
      : `${userRows.length} gebruikers`;
    if (adminUserSearchClear) adminUserSearchClear.hidden = term === "";
  };

  adminUserRoleFilter.addEventListener("change", filterUsers);
  adminUserSearch.addEventListener("input", filterUsers);
  adminUserSearch.addEventListener("search", filterUsers);
  adminUserSearch.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || !adminUserSearch.value) return;
    adminUserSearch.value = "";
    filterUsers();
  });
  adminUserSearchClear?.addEventListener("click", () => {
    adminUserSearch.value = "";
    filterUsers();
    adminUserSearch.focus();
  });
  filterUsers();
}

document.querySelectorAll("[data-class-student-search]").forEach((search) => {
  const form = search.closest(".admin-class-form");
  const options = [...form.querySelectorAll("[data-class-student-option]")];
  const summary = form.querySelector("[data-class-student-summary]");
  const emptyState = form.querySelector("[data-class-student-empty]");
  const clearButton = form.querySelector("[data-class-student-clear]");

  const filterStudents = () => {
    const term = search.value.trim().toLocaleLowerCase("nl");
    let visibleCount = 0;
    options.forEach((option) => {
      const matches = term === "" || option.dataset.classStudentOption.includes(term);
      option.hidden = !matches;
      if (matches) visibleCount += 1;
    });

    const selectedCount = options.filter((option) => option.querySelector("input[type=checkbox]")?.checked).length;
    if (summary) {
      const visibleText = term
        ? `${visibleCount} van ${options.length} ${options.length === 1 ? "student zichtbaar" : "studenten zichtbaar"}`
        : `${visibleCount} ${visibleCount === 1 ? "student zichtbaar" : "studenten zichtbaar"}`;
      summary.textContent = `${visibleText} · ${selectedCount} geselecteerd`;
    }
    if (emptyState) emptyState.hidden = visibleCount > 0;
    if (clearButton) clearButton.hidden = term === "";
  };

  // Browsers fire `search` when the native clear button is used; `input` covers typing and backspace.
  search.addEventListener("input", filterStudents);
  search.addEventListener("search", filterStudents);
  search.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || !search.value) return;
    search.value = "";
    filterStudents();
  });
  clearButton?.addEventListener("click", () => {
    search.value = "";
    filterStudents();
    search.focus();
  });
  form.addEventListener("change", (event) => {
    if (event.target.matches('[data-class-student-option] input[type="checkbox"]')) filterStudents();
  });
  filterStudents();
});
