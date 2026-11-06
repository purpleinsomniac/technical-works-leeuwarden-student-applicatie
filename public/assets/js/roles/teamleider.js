window.RoleApp.initializeRole({
  label: "Teamleider",
  routes: [
    ["Groepsoverzicht", "managerGroups"],
    ["Rapportages", "managerReports"],
    ["Instellingen", "managerPreferencesSettings"],
  ],
});
RoleSettings.initialize();
