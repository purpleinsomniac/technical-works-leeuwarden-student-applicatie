// Gedeelde Laravel-opslag en beveiligde API-aanroepen.

// Laravel synchronisatie: opgeslagen gebruikersgegevens worden bij het openen ingeladen.
const persistentKeys = [
  "archived-learning-outcomes",
  "hours-processing-latest-read",
  "ontwikkeling-dashboard-instellingen",
];
if (window.LARAVEL_USER) {
  for (const [key, value] of Object.entries(window.LARAVEL_STATE || {})) {
    if (typeof key === "string" && (persistentKeys.includes(key) || key.includes("processing"))) {
      localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-" + key, JSON.stringify(value));
    }
  }
}

// Verstuur onderwijsgegevens via beveiligde Laravel-routes naar MySQL.
async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    credentials: "same-origin",
    ...options,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": window.LARAVEL_CSRF,
      ...(options.headers || {}),
    },
  });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const validationMessage = Object.values(body.errors || {}).flat()[0];
    throw new Error(validationMessage || body.message || "Opslaan is mislukt.");
  }
  return body;
}

function apiUrl(template, id) {
  return template.replace("__ID__", String(id));
}
function loadLocalState(key) {
  if (!window.LARAVEL_USER) return localStorage.getItem(key);
  return localStorage.getItem("laravel-" + window.LARAVEL_USER.id + "-" + key);
}
let syncTimer;
const pendingStateChanges = new Map();

function parseStoredState(value) {
  try {
    return JSON.parse(value);
  } catch {
    return value;
  }
}

async function syncLocalState() {
  if (!window.LARAVEL_SAVE_URL || pendingStateChanges.size === 0) return;

  const changes = Object.fromEntries(pendingStateChanges);

  try {
    await apiRequest(window.LARAVEL_SAVE_URL, {
      method: "PUT",
      body: JSON.stringify({ data: changes }),
    });

    // Verwijder alleen waarden die tijdens het verzoek niet opnieuw zijn gewijzigd.
    for (const [key, savedValue] of Object.entries(changes)) {
      const pendingValue = pendingStateChanges.get(key);
      if (JSON.stringify(pendingValue) === JSON.stringify(savedValue)) {
        pendingStateChanges.delete(key);
      }
    }

    document.dispatchEvent(
      new CustomEvent("student-state-saved", { detail: { keys: Object.keys(changes) } }),
    );

    // Een wijziging die tijdens het verzoek binnenkwam, wordt direct daarna opgeslagen.
    if (pendingStateChanges.size > 0) {
      clearTimeout(syncTimer);
      syncTimer = setTimeout(syncLocalState, 250);
    }
  } catch (error) {
    document.dispatchEvent(
      new CustomEvent("student-state-save-failed", { detail: { error } }),
    );
    hint(error.message || "Opslaan op de server is mislukt. Probeer het opnieuw.");
  }
}

function saveLocalState(key, value) {
  if (!window.LARAVEL_USER) return;
  localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-" + key, value);
  pendingStateChanges.set(key, parseStoredState(value));
  clearTimeout(syncTimer);
  syncTimer = setTimeout(syncLocalState, 250);
}
