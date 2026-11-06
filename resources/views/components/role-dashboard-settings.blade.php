<section id="{{ $settingsPageId }}" class="view hidden dashboard-settings-page role-dashboard-settings">
  <div class="settings-page-head">
    <button class="back-link" type="button" onclick="RoleSettings.showDashboard()">← Terug naar dashboard</button>
    <p class="eyebrow">PERSOONLIJKE VOORKEUREN</p>
    <h1>Dashboard instellen</h1>
    <p class="dim">Pas de weergave van jouw dashboard aan. Deze voorkeuren worden alleen voor jouw account bewaard.</p>
  </div>

  <div class="settings-layout">
    <section class="card settings-card">
      <div class="settings-card-head">
        <span class="settings-card-icon" aria-hidden="true">◈</span>
        <div><h2>Weergave</h2><p>Maak het dashboard compacter of kies een rustiger kleurthema.</p></div>
      </div>
      <label class="settings-option">
        <span><strong>Compacte weergave</strong><small>Minder witruimte op het dashboard.</small></span>
        <input type="checkbox" data-role-setting="compact">
      </label>
      <label class="settings-option">
        <span><strong>Donkere modus</strong><small>Gebruik een donker kleurthema.</small></span>
        <input type="checkbox" data-role-setting="darkMode">
      </label>
    </section>

    <section class="card settings-card">
      <div class="settings-card-head">
        <span class="settings-card-icon" aria-hidden="true">☷</span>
        <div><h2>Zichtbare onderdelen</h2><p>Kies welke onderdelen in de navigatie en op het dashboard zichtbaar zijn.</p></div>
      </div>
      <div class="settings-options">
        @foreach ($dashboardSections as $section)
          <label class="settings-option">
            <span><strong>{{ $section['label'] }}</strong><small>{{ $section['description'] }}</small></span>
            <input type="checkbox" data-role-section-setting="{{ $section['key'] }}">
          </label>
        @endforeach
      </div>
    </section>
  </div>

  <div class="settings-footer">
    <div class="settings-save-copy">
      <span class="settings-save-check" aria-hidden="true">✓</span>
      <div><strong>Wijzigingen worden alleen voor jouw account bewaard.</strong><small id="{{ $settingsPageId }}Saved" class="settings-saved visible" role="status">Wijzigingen worden automatisch opgeslagen.</small></div>
    </div>
    <button class="button" type="button" onclick="RoleSettings.reset()">Standaardinstellingen herstellen</button>
    <button class="button dark" type="button" data-role-settings-save>✓ Wijzigingen opslaan</button>
  </div>
</section>
