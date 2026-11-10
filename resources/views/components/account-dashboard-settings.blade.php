<section class="account-dashboard-settings" aria-labelledby="accountDashboardSettingsTitle">
  <div class="account-dashboard-settings-heading">
    <span class="settings-card-icon" aria-hidden="true">⚙</span>
    <div>
      <h3 id="accountDashboardSettingsTitle">Dashboard instellen</h3>
      <p>Persoonlijke voorkeuren voor jouw account.</p>
    </div>
  </div>

  <div class="account-dashboard-setting-group">
    <h4>Weergave</h4>
    <label class="account-dashboard-setting">
      <span>Compacte weergave</span>
      <input type="checkbox" data-role-setting="compact">
    </label>
    <label class="account-dashboard-setting">
      <span>Donkere modus</span>
      <input type="checkbox" data-role-setting="darkMode">
    </label>
  </div>

  <div class="account-dashboard-setting-group">
    <h4>Zichtbare onderdelen</h4>
    @if ($frontendUser['role'] === 'beheerder')
      @foreach ([
        'overview' => 'Overzicht',
        'users' => 'Gebruikers',
        'learningOutcomes' => 'Leeruitkomsten',
        'applicationSettings' => 'Applicatie-instellingen',
      ] as $key => $label)
        <label class="account-dashboard-setting">
          <span>{{ $label }}</span>
          <input type="checkbox" data-role-section-setting="{{ $key }}">
        </label>
      @endforeach
    @elseif ($frontendUser['role'] === 'teamleider')
      @foreach (['groups' => 'Groepsoverzicht', 'reports' => 'Rapportages'] as $key => $label)
        <label class="account-dashboard-setting">
          <span>{{ $label }}</span>
          <input type="checkbox" data-role-section-setting="{{ $key }}">
        </label>
      @endforeach
    @elseif ($frontendUser['role'] === 'coach')
      @foreach ([
        'coachOverview' => 'Overzicht',
        'coachGroups' => 'Groepen',
        'coachActivities' => 'Leeractiviteiten',
        'coachGoals' => 'Leerdoelen',
        'coachProjects' => 'Projecten',
      ] as $key => $label)
        <label class="account-dashboard-setting">
          <span>{{ $label }}</span>
          <input type="checkbox" data-role-section-setting="{{ $key }}">
        </label>
      @endforeach
    @endif
  </div>

  <div class="account-dashboard-settings-footer">
    <small data-role-settings-status role="status">Wijzigingen worden automatisch opgeslagen.</small>
    <button class="button" type="button" data-role-settings-reset>Standaard herstellen</button>
  </div>
</section>
