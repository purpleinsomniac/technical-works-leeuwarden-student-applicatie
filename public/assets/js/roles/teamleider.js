window.RoleApp.initializeRole({
  label: "Teamleider",
  routes: [
    ["Groepsoverzicht", "managerGroups"],
    ["Docenten", "managerTeachers"],
    ["Rapportages", "managerReports"],
    ["Instellingen", "managerPreferencesSettings"],
  ],
});
RoleSettings.initialize();
