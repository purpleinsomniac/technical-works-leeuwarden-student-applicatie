(() => {
  const key = "ontwikkeling-dashboard-instellingen";
  const sectionInputs = [...document.querySelectorAll("[data-role-section-setting]")];
  const sectionKeys = [...new Set(sectionInputs.map((input) => input.dataset.roleSectionSetting))];
  const storageKey = "laravel-" + window.LARAVEL_USER.id + "-" + key;
  const statuses = [...document.querySelectorAll("[data-role-settings-status], .role-dashboard-settings .settings-saved")];
  const saveButtons = [...document.querySelectorAll("[data-role-settings-save]")];
  const resetButtons = [...document.querySelectorAll("[data-role-settings-reset]")];
  let preferences;

  function readPreferences() {
    let saved = window.LARAVEL_STATE?.[key];
    try {
      const local = localStorage.getItem(storageKey);
      if (local) saved = JSON.parse(local);
    } catch (error) {
      console.error("Could not read dashboard settings from local storage.", error);
    }

    const selectedSections = Array.isArray(saved?.sections)
      ? saved.sections.filter((section) => sectionKeys.includes(section))
      : [...sectionKeys];

    return {
      compact: saved?.compact === true,
      darkMode: saved?.darkMode === true,
      sections: selectedSections.length > 0 ? selectedSections : [...sectionKeys],
    };
  }

  function saveLocally() {
    try {
      localStorage.setItem(storageKey, JSON.stringify(preferences));
    } catch (error) {
      console.error("Could not save dashboard settings to local storage.", error);
    }
  }

  function setStatus(message, failed = false) {
    statuses.forEach((status) => {
      status.textContent = message;
      status.classList.toggle("role-settings-error", failed);
    });
  }

  function apply() {
    document.documentElement.classList.toggle("theme-dark", preferences.darkMode);
    document.getElementById("app")?.classList.toggle("dashboard-compact", preferences.compact);

    for (const section of document.querySelectorAll("[data-role-section]")) {
      const visible = preferences.sections.includes(section.dataset.roleSection);
      section.classList.toggle("hidden", !visible);
      document.querySelectorAll("[data-route]").forEach((button) => {
        if (button.dataset.route === section.id) button.classList.toggle("hidden", !visible);
      });
    }

    document.querySelectorAll('[data-role-setting="compact"]').forEach((input) => { input.checked = preferences.compact; });
    document.querySelectorAll('[data-role-setting="darkMode"]').forEach((input) => { input.checked = preferences.darkMode; });
    for (const input of sectionInputs) {
      input.checked = preferences.sections.includes(input.dataset.roleSectionSetting);
    }
  }

  async function save() {
    saveButtons.forEach((button) => { button.disabled = true; });
    setStatus("Wijzigingen opslaan...");

    try {
      const response = await fetch(window.LARAVEL_ROLE_SETTINGS_URL, {
        method: "PUT",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": window.LARAVEL_CSRF,
        },
        body: JSON.stringify(preferences),
      });
      const body = await response.json();
      if (!response.ok) {
        throw new Error(body.message || Object.values(body.errors || {}).flat()[0] || "Instellingen opslaan is mislukt.");
      }

      setStatus("Laatst opgeslagen: zojuist");
    } catch (error) {
      setStatus(error.message || "Instellingen opslaan is mislukt.", true);
    } finally {
      saveButtons.forEach((button) => { button.disabled = false; });
    }
  }

  function update(event) {
    const changedInput = event?.currentTarget;
    if (changedInput?.dataset.roleSetting) {
      document.querySelectorAll(`[data-role-setting="${changedInput.dataset.roleSetting}"]`)
        .forEach((input) => { input.checked = changedInput.checked; });
    }
    if (changedInput?.dataset.roleSectionSetting) {
      document.querySelectorAll(`[data-role-section-setting="${changedInput.dataset.roleSectionSetting}"]`)
        .forEach((input) => { input.checked = changedInput.checked; });
    }

    preferences = {
      compact: document.querySelector('[data-role-setting="compact"]:checked')?.checked === true,
      darkMode: document.querySelector('[data-role-setting="darkMode"]:checked')?.checked === true,
      sections: sectionKeys.filter((key) => sectionInputs.some((input) => input.dataset.roleSectionSetting === key && input.checked)),
    };

    if (preferences.sections.length === 0) {
      setStatus("Kies ten minste één zichtbaar onderdeel.", true);
      apply();
      return;
    }

    apply();
    saveLocally();
    save();
  }

  function reset() {
    preferences = { compact: false, darkMode: false, sections: [...sectionKeys] };
    apply();
    saveLocally();
    save();
  }

  function isSectionVisible(page) {
    if (!preferences) return true;
    const section = [...document.querySelectorAll("[data-role-section]")].find((item) => item.id === page);
    return !section || preferences.sections.includes(section.dataset.roleSection);
  }

  function showDashboard() {
    const firstVisible = [...document.querySelectorAll("[data-role-section]")]
      .find((section) => preferences.sections.includes(section.dataset.roleSection));
    if (firstVisible) show(firstVisible.id);
  }

  window.RoleSettings = { initialize, reset, isSectionVisible, showDashboard };

  function initialize() {
    preferences = readPreferences();
    apply();
    const activePage = document.querySelector(".view:not(.hidden)[data-role-section]");
    if (activePage && !isSectionVisible(activePage.id)) {
      const firstVisible = [...document.querySelectorAll("[data-role-section]")]
        .find((section) => preferences.sections.includes(section.dataset.roleSection));
      if (firstVisible) show(firstVisible.id);
    }
    document.querySelectorAll("[data-role-setting], [data-role-section-setting]").forEach((input) => input.addEventListener("change", update));
    saveButtons.forEach((button) => button.addEventListener("click", save));
    resetButtons.forEach((button) => button.addEventListener("click", reset));
  }
})();
