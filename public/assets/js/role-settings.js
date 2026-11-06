(() => {
  const key = "ontwikkeling-dashboard-instellingen";
  const sectionInputs = [...document.querySelectorAll("[data-role-section-setting]")];
  const sectionKeys = [...new Set(sectionInputs.map((input) => input.dataset.roleSectionSetting))];
  const storageKey = "laravel-" + window.LARAVEL_USER.id + "-" + key;
  const statuses = [...document.querySelectorAll("[data-role-settings-status], .role-dashboard-settings .settings-saved")];
  const retryButtons = [...document.querySelectorAll("[data-role-settings-retry]")];
  const defaultNotificationPreferences = {
    feedback: true,
    grades: true,
    deadlines: true,
    projects: false,
    goals: false,
    platform_notifications: true,
    email_notifications: true,
    weekly_summary: false,
    do_not_disturb: false,
    deadline_days: 2,
  };
  const accentColors = {
    teal: "#009b87",
    blue: "#168bed",
    purple: "#6840e8",
    pink: "#df238b",
    orange: "#fa7900",
    amber: "#efb014",
    green: "#78b915",
  };
  const accentLabels = {
    teal: "Turkoois",
    blue: "Blauw",
    purple: "Paars",
    pink: "Roze",
    orange: "Oranje",
    amber: "Geel",
    green: "Groen",
  };
  let preferences;
  let notificationPreferences;
  let settingsSaveTimer;
  let notificationSaveTimer;
  let settingsSaveBusy = false;
  let notificationSaveBusy = false;
  let settingsSaveError = null;
  let notificationSaveError = null;
  let settingsRevision = 0;
  let notificationRevision = 0;

  function readPreferences() {
    let saved = window.LARAVEL_STATE?.[key];
    try {
      const local = localStorage.getItem(storageKey);
      if (local) saved = JSON.parse(local);
    } catch (error) {
      console.error("Could not read dashboard settings from local storage.", error);
    }

    const savedSections = Array.isArray(saved?.sections)
      ? saved.sections.filter((section) => sectionKeys.includes(section))
      : [];

    return {
      compact: saved?.compact === true,
      darkMode: saved?.darkMode === true,
      theme: ["light", "dark", "system"].includes(saved?.theme)
        ? saved.theme
        : saved?.darkMode === true ? "dark" : "light",
      density: ["compact", "standard", "spacious"].includes(saved?.density)
        ? saved.density
        : saved?.compact === true ? "compact" : "standard",
      accent: Object.prototype.hasOwnProperty.call(accentColors, saved?.accent) ? saved.accent : "teal",
      sections: sectionKeys.length
        ? (savedSections.length ? savedSections : [...sectionKeys])
        : [],
    };
  }

  function readNotificationPreferences() {
    let saved = window.LARAVEL_NOTIFICATION_SETTINGS || {};
    try {
      const local = localStorage.getItem("laravel-" + window.LARAVEL_USER.id + "-notification-settings");
      if (local) saved = { ...saved, ...JSON.parse(local) };
    } catch (error) {
      console.error("Could not read notification settings from local storage.", error);
    }
    return { ...defaultNotificationPreferences, ...saved };
  }

  function saveLocally() {
    try {
      localStorage.setItem(storageKey, JSON.stringify(preferences));
    } catch (error) {
      console.error("Could not save dashboard settings to local storage.", error);
    }
  }

  function setStatus(message, status = "saved") {
    retryButtons.forEach((button) => { button.classList.toggle("hidden", status !== "error"); });
    statuses.forEach((element) => {
      element.textContent = message;
      element.classList.toggle("role-settings-error", status === "error");
      element.closest(".settings-footer")?.setAttribute("data-save-status", status);
    });
  }

  function apply() {
    const root = document.documentElement;
    const systemDark = preferences.theme === "system" && window.matchMedia("(prefers-color-scheme: dark)").matches;
    root.classList.toggle("theme-dark", preferences.theme === "dark" || systemDark);
    root.dataset.density = preferences.density;
    root.dataset.accent = preferences.accent;
    root.style.setProperty("--student-accent", accentColors[preferences.accent]);
    document.getElementById("app")?.classList.toggle("dashboard-compact", preferences.density === "compact");

    document.querySelectorAll('[data-role-setting="compact"]').forEach((input) => { input.checked = preferences.density === "compact"; });
    document.querySelectorAll('[data-role-setting="darkMode"]').forEach((input) => { input.checked = preferences.theme === "dark"; });
    for (const section of document.querySelectorAll("[data-role-section]")) {
      if (!sectionKeys.length) break;
      const visible = preferences.sections.includes(section.dataset.roleSection);
      section.classList.toggle("hidden", !visible);
      document.querySelectorAll("[data-route]").forEach((button) => {
        if (button.dataset.route === section.id) button.classList.toggle("hidden", !visible);
      });
    }
    for (const input of sectionInputs) {
      input.checked = preferences.sections.includes(input.dataset.roleSectionSetting);
    }
    renderAppearance();
  }

  function renderAppearance() {
    const choices = (name, selected, options, preview) => options.map(([value, title, description]) =>
      `<label class="settings-choice ${selected === value ? "selected" : ""}"><input type="radio" name="role-appearance-${name}" value="${value}" ${selected === value ? "checked" : ""} data-role-appearance="${name}">${preview(value)}<span><strong>${title}</strong><small>${description}</small></span></label>`
    ).join("");

    const themeOptions = [
      ["light", "Licht", "Heldere en frisse weergave"],
      ["dark", "Donker", "Rustig en prettig voor je ogen"],
      ["system", "Systeem", "Volgt je apparaatinstellingen"],
    ];
    const densityOptions = [
      ["compact", "Compact", "Meer informatie op het scherm"],
      ["standard", "Standaard", "Gebalanceerde weergave"],
      ["spacious", "Ruim", "Meer witruimte en grotere kaarten"],
    ];
    const themeContainer = document.querySelector("[data-role-theme-options]");
    const densityContainer = document.querySelector("[data-role-density-options]");
    const accentContainer = document.querySelector("[data-role-accent-options]");
    if (!themeContainer || !densityContainer || !accentContainer) return;

    themeContainer.innerHTML = choices("theme", preferences.theme, themeOptions, (value) => `<span class="theme-preview ${value}" aria-hidden="true"><i></i><b><em></em><em></em><em></em></b></span>`);
    densityContainer.innerHTML = choices("density", preferences.density, densityOptions, () => '<span class="density-symbol" aria-hidden="true">☰</span>');
    accentContainer.innerHTML = Object.entries(accentColors).map(([value, color]) =>
      `<label style="--choice-color:${color}" title="${accentLabels[value]}"><input type="radio" name="role-appearance-accent" aria-label="${accentLabels[value]}" value="${value}" data-role-appearance="accent" ${preferences.accent === value ? "checked" : ""}><span aria-hidden="true">${preferences.accent === value ? "✓" : ""}</span></label>`
    ).join("");
  }

  function renderNotifications() {
    const topics = document.querySelector("[data-role-notification-topics]");
    const channels = document.querySelector("[data-role-notification-channels]");
    const quiet = document.querySelector("[data-role-notification-quiet]");
    const deadline = document.querySelector("[data-role-deadline-days]");
    if (!topics || !channels || !quiet || !deadline) return;

    const rows = (items) => items.map(([name, title, description, icon]) =>
      `<label class="notification-setting-row"><span class="notification-setting-icon" aria-hidden="true">${icon}</span><strong>${title}</strong><small>${description}</small><input type="checkbox" class="profile-switch" data-role-notification="${name}" ${notificationPreferences[name] ? "checked" : ""}></label>`
    ).join("");

    topics.innerHTML = rows([
      ["feedback", "Nieuwe feedback", "Ontvang een melding wanneer feedback beschikbaar is.", "◉"],
      ["grades", "Nieuwe punten", "Blijf op de hoogte van nieuwe punten en resultaten.", "✓"],
      ["deadlines", "Aankomende deadlines", "Ontvang herinneringen voor inlevermomenten.", "▦"],
      ["projects", "Updates over projecten", "Krijg bericht wanneer projectinformatie verandert.", "▤"],
      ["goals", "Herinneringen voor leerdoelen", "Ontvang updates over doelen en voortgang.", "◎"],
    ]);
    channels.innerHTML = rows([
      ["platform_notifications", "Platformmeldingen", "Ontvang meldingen in het dashboard.", "▣"],
      ["email_notifications", "E-mailmeldingen", "Ontvang belangrijke updates via e-mail.", "✉"],
      ["weekly_summary", "Wekelijks overzicht", "Ontvang een wekelijks overzicht per e-mail.", "▦"],
    ]);
    quiet.innerHTML = rows([["do_not_disturb", "Niet storen", "Tijdelijk geen platformmeldingen ontvangen.", "◷"]]);
    deadline.value = String(notificationPreferences.deadline_days);
  }

  async function responseBody(response) {
    try {
      return await response.json();
    } catch {
      return {};
    }
  }

  function errorMessage(body, fallback) {
    return Object.values(body.errors || {}).flat()[0] || body.message || fallback;
  }

  async function saveSettings() {
    if (settingsSaveBusy) return;
    settingsSaveBusy = true;
    const revision = settingsRevision;
    const savedPreferences = { ...preferences, compact: preferences.density === "compact", darkMode: preferences.theme === "dark" };
    const requestPreferences = { ...savedPreferences, role: window.LARAVEL_USER.role };
    if (sectionKeys.length) {
      requestPreferences.sections = preferences.sections;
    } else {
      delete requestPreferences.sections;
    }
    setStatus("Wijzigingen opslaan...", "pending");
    try {
      const response = await fetch(window.LARAVEL_ROLE_SETTINGS_URL, {
        method: "PUT",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": window.LARAVEL_CSRF,
        },
        body: JSON.stringify(requestPreferences),
      });
      const body = await responseBody(response);
      if (!response.ok) throw new Error(errorMessage(body, "Instellingen opslaan is mislukt."));
      settingsSaveError = null;
      if (revision === settingsRevision) {
        setStatus(notificationSaveError || "Laatst opgeslagen: zojuist", notificationSaveError ? "error" : "saved");
      }
    } catch (error) {
      settingsSaveError = error.message || "Instellingen opslaan is mislukt.";
      setStatus(settingsSaveError, "error");
      console.error("Could not save dashboard settings.", error);
    } finally {
      settingsSaveBusy = false;
      if (revision !== settingsRevision) scheduleSettingsSave();
    }
  }

  function scheduleSettingsSave() {
    clearTimeout(settingsSaveTimer);
    settingsSaveTimer = setTimeout(saveSettings, 300);
  }

  async function saveNotifications() {
    if (notificationSaveBusy) return;
    notificationSaveBusy = true;
    const revision = notificationRevision;
    const savedNotifications = { ...notificationPreferences };
    setStatus("Meldingsvoorkeuren opslaan...", "pending");
    try {
      const response = await fetch(window.LARAVEL_API.notificationSettings, {
        method: "PATCH",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": window.LARAVEL_CSRF,
        },
        body: JSON.stringify(savedNotifications),
      });
      const body = await responseBody(response);
      if (!response.ok) throw new Error(errorMessage(body, "Meldingsvoorkeuren opslaan is mislukt."));
      notificationSaveError = null;
      if (revision === notificationRevision) {
        setStatus(settingsSaveError || "Laatst opgeslagen: zojuist", settingsSaveError ? "error" : "saved");
      }
    } catch (error) {
      notificationSaveError = error.message || "Meldingsvoorkeuren opslaan is mislukt.";
      setStatus(notificationSaveError, "error");
      console.error("Could not save notification settings.", error);
    } finally {
      notificationSaveBusy = false;
      if (revision !== notificationRevision) scheduleNotificationSave();
    }
  }

  function scheduleNotificationSave() {
    clearTimeout(notificationSaveTimer);
    notificationSaveTimer = setTimeout(saveNotifications, 300);
  }

  function update(event) {
    const changedInput = event.target;
    if (!(changedInput instanceof HTMLInputElement)) return;
    if (changedInput.dataset.roleSetting === "compact") {
      document.querySelectorAll('[data-role-setting="compact"]').forEach((input) => { input.checked = changedInput.checked; });
      preferences.density = changedInput.checked ? "compact" : "standard";
      preferences.compact = changedInput.checked;
    } else if (changedInput.dataset.roleSetting === "darkMode") {
      document.querySelectorAll('[data-role-setting="darkMode"]').forEach((input) => { input.checked = changedInput.checked; });
      preferences.theme = changedInput.checked ? "dark" : "light";
      preferences.darkMode = changedInput.checked;
    } else if (changedInput.dataset.roleAppearance) {
      preferences[changedInput.dataset.roleAppearance] = changedInput.value;
      if (changedInput.dataset.roleAppearance === "theme") preferences.darkMode = changedInput.value === "dark";
      if (changedInput.dataset.roleAppearance === "density") preferences.compact = changedInput.value === "compact";
    } else if (changedInput.dataset.roleSectionSetting) {
      const sections = sectionInputs
        .filter((input) => input.checked)
        .map((input) => input.dataset.roleSectionSetting);
      if (sections.length === 0) {
        changedInput.checked = true;
        setStatus("Kies ten minste één zichtbaar onderdeel.", "error");
        return;
      }
      preferences.sections = sections;
    } else if (changedInput.dataset.roleNotification) {
      notificationPreferences[changedInput.dataset.roleNotification] = changedInput.checked;
      notificationRevision++;
      if (["email_notifications", "platform_notifications"].includes(changedInput.dataset.roleNotification)) {
        const channel = changedInput.dataset.roleNotification;
        const profileInput = document.querySelector(`#profileSettingsForm [name="${channel}"]`);
        if (profileInput) profileInput.checked = changedInput.checked;
        document.dispatchEvent(new CustomEvent("notification-preferences-saved", {
          detail: {
            email_notifications: notificationPreferences.email_notifications,
            platform_notifications: notificationPreferences.platform_notifications,
          },
        }));
      }
      try {
        localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-notification-settings", JSON.stringify(notificationPreferences));
      } catch (error) {
        console.error("Could not save notification settings to local storage.", error);
      }
      setStatus("Wijzigingen worden automatisch opgeslagen.", "pending");
      scheduleNotificationSave();
      return;
    } else {
      return;
    }

    settingsRevision++;
    apply();
    saveLocally();
    setStatus("Wijzigingen worden automatisch opgeslagen.", "pending");
    scheduleSettingsSave();
  }

  function updateDeadline(event) {
    const value = Number(event.target.value);
    if (![0, 1, 2, 7].includes(value)) return;
    notificationPreferences.deadline_days = value;
    notificationRevision++;
    try {
      localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-notification-settings", JSON.stringify(notificationPreferences));
    } catch (error) {
      console.error("Could not save notification settings to local storage.", error);
    }
    setStatus("Wijzigingen worden automatisch opgeslagen.", "pending");
    scheduleNotificationSave();
  }

  function reset() {
    preferences = {
      compact: false,
      darkMode: false,
      theme: "light",
      density: "standard",
      accent: "teal",
      sections: [...sectionKeys],
    };
    notificationPreferences = { ...defaultNotificationPreferences };
    settingsRevision++;
    notificationRevision++;
    apply();
    renderNotifications();
    saveLocally();
    try {
      localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-notification-settings", JSON.stringify(notificationPreferences));
    } catch (error) {
      console.error("Could not save notification settings to local storage.", error);
    }
    setStatus("Standaardinstellingen opslaan...", "pending");
    scheduleSettingsSave();
    scheduleNotificationSave();
  }

  function selectTab(tab) {
    document.querySelectorAll("[data-settings-tab]").forEach((button) => {
      const selected = button.dataset.settingsTab === tab;
      button.classList.toggle("active", selected);
      button.setAttribute("aria-pressed", String(selected));
    });
    document.querySelectorAll("[data-settings-panel]").forEach((panel) => {
      panel.classList.toggle("hidden", panel.dataset.settingsPanel !== tab);
    });
  }

  function isSectionVisible(page) {
    if (!sectionKeys.length || !preferences) return true;
    const section = [...document.querySelectorAll("[data-role-section]")].find((item) => item.id === page);
    return !section || preferences.sections.includes(section.dataset.roleSection);
  }

  function showDashboard() {
    const firstSection = [...document.querySelectorAll("[data-role-section]")]
      .find((section) => !sectionKeys.length || preferences.sections.includes(section.dataset.roleSection));
    if (firstSection) show(firstSection.id);
  }

  window.RoleSettings = { initialize, reset, isSectionVisible, showDashboard, selectTab };

  function initialize() {
    preferences = readPreferences();
    notificationPreferences = readNotificationPreferences();
    apply();
    renderNotifications();
    selectTab("appearance");

    document.addEventListener("change", update);
    document.querySelector("[data-role-deadline-days]")?.addEventListener("change", updateDeadline);
    document.querySelectorAll("[data-role-settings-reset]").forEach((button) => button.addEventListener("click", reset));
    document.addEventListener("profile-preferences-saved", (event) => {
      notificationPreferences = { ...notificationPreferences, ...event.detail };
      notificationRevision++;
      renderNotifications();
      try {
        localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-notification-settings", JSON.stringify(notificationPreferences));
      } catch (error) {
        console.error("Could not save notification settings to local storage.", error);
      }
    });
    retryButtons.forEach((button) => button.addEventListener("click", () => {
      setStatus("Wijzigingen opnieuw opslaan...", "pending");
      scheduleSettingsSave();
      scheduleNotificationSave();
    }));

    const colorScheme = window.matchMedia("(prefers-color-scheme: dark)");
    colorScheme.addEventListener?.("change", () => {
      if (preferences.theme === "system") apply();
    });
  }
})();
