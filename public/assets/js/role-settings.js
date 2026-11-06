(() => {
  const key = "ontwikkeling-dashboard-instellingen";
  const sectionInputs = [...document.querySelectorAll("[data-role-section-setting]")];
  const sectionKeys = sectionInputs.map((input) => input.dataset.roleSectionSetting);
  const settingsPageId = document.querySelector(".role-dashboard-settings")?.id;
  const storageKey = "laravel-" + window.LARAVEL_USER.id + "-" + key;
  const status = document.getElementById((settingsPageId || "") + "Saved");
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
    if (!status) return;
    status.textContent = message;
    status.classList.toggle("role-settings-error", failed);
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

    document.querySelector('[data-role-setting="compact"]').checked = preferences.compact;
    document.querySelector('[data-role-setting="darkMode"]').checked = preferences.darkMode;
    for (const input of sectionInputs) {
      input.checked = preferences.sections.includes(input.dataset.roleSectionSetting);
    }
  }

  async function save() {
    const button = document.querySelector("[data-role-settings-save]");
    button.disabled = true;
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
      button.disabled = false;
    }
  }

  function update() {
    preferences = {
      compact: document.querySelector('[data-role-setting="compact"]').checked,
      darkMode: document.querySelector('[data-role-setting="darkMode"]').checked,
      sections: sectionInputs.filter((input) => input.checked).map((input) => input.dataset.roleSectionSetting),
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
    document.querySelectorAll("[data-role-setting], [data-role-section-setting]")
      .forEach((input) => input.addEventListener("change", update));
    document.querySelector("[data-role-settings-save]").addEventListener("click", save);
  }
})();
