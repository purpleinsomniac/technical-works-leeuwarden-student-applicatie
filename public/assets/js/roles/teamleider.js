window.RoleApp.initializeRole({
  label: "Teamleider",
  routes: [
    ["Groepsoverzicht", "managerGroups"],
    ["Rapportages", "managerReports"],
    ["Instellingen", "managerDashboardSettings"],
  ],
});
RoleSettings.initialize({
  settingsPageId: "managerDashboardSettings",
  dashboardPageId: "managerGroups",
});
