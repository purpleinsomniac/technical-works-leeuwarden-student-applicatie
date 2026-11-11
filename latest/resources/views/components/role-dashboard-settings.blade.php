<section id="{{ $preferencesPageId }}" class="view hidden dashboard-settings-page role-dashboard-settings role-preferences-page" aria-labelledby="{{ $preferencesPageId }}Title">
  <div class="settings-page-head">
    <button class="back-link" type="button" onclick="RoleSettings.showDashboard()">← Terug naar dashboard</button>
    <p class="eyebrow">PERSOONLIJKE VOORKEUREN</p>
    <h1 id="{{ $preferencesPageId }}Title">Instellingen</h1>
    <p class="dim">Pas jouw omgeving aan. Kies je voorkeuren voor uiterlijk en meldingen.</p>
  </div>

  <div class="settings-account-banner">
    <span class="settings-account-avatar" aria-hidden="true">{{ $frontendUser['initials'] }}</span>
    <div><strong>{{ $user->naam }}</strong><small>◇ {{ ucfirst($frontendUser['role']) }}</small></div>
    <span class="settings-account-email">✉ {{ $user->email }}</span>
    <div class="settings-account-security"><span aria-hidden="true">♧</span><div><strong>Accountbeveiliging</strong><small>2FA nog niet beschikbaar</small></div></div>
  </div>

  <div class="settings-workspace">
    <nav class="settings-menu" aria-label="Instellingsonderdelen">
      <button type="button" data-settings-tab="appearance" aria-pressed="true" onclick="RoleSettings.selectTab('appearance')"><span aria-hidden="true">◉</span><span><strong>Uiterlijk</strong><small>Thema, kleuren en weergave</small></span><b aria-hidden="true">›</b></button>
      <button type="button" data-settings-tab="notifications" aria-pressed="false" onclick="RoleSettings.selectTab('notifications')"><span aria-hidden="true">♧</span><span><strong>Meldingen</strong><small>E-mails en platformmeldingen</small></span><b aria-hidden="true">›</b></button>
      <button type="button" data-settings-tab="account" aria-pressed="false" onclick="RoleSettings.selectTab('account')"><span aria-hidden="true">⚙</span><span><strong>Account</strong><small>Profiel en beveiliging</small></span><b aria-hidden="true">›</b></button>
    </nav>

    <div class="settings-content">
      <section class="settings-panel" data-settings-panel="appearance">
        <header><span aria-hidden="true">◉</span><div><h2>Uiterlijk</h2><p>Pas het thema, de accentkleur en de weergave aan.</p></div></header>
        <fieldset><legend>Thema</legend><p>Kies de algemene weergave van de omgeving.</p><div data-role-theme-options class="settings-choice-grid"></div></fieldset>
        <fieldset><legend>Accentkleur</legend><p>Bepaal de hoofdkleur van knoppen, actieve elementen en accenten.</p><div data-role-accent-options class="settings-color-options"></div></fieldset>
        <fieldset><legend>Weergavedichtheid</legend><p>Bepaal hoeveel ruimte er tussen onderdelen zit.</p><div data-role-density-options class="settings-choice-grid"></div></fieldset>
      </section>

      <section class="settings-panel hidden" data-settings-panel="notifications">
        <header><span aria-hidden="true">♧</span><div><h2>Meldingen</h2><p>Beheer je voorkeuren voor e-mail- en platformmeldingen.</p></div></header>
        <fieldset><legend>Meldingen per onderdeel</legend><p>Kies waarvoor je meldingen wilt ontvangen.</p><div data-role-notification-topics></div></fieldset>
        <fieldset><legend>Ontvangstmethode</legend><p>Bepaal via welke kanalen je meldingen ontvangt.</p><div data-role-notification-channels></div></fieldset>
        <fieldset><legend>Herinneringen</legend><p>Stel in hoe en wanneer je herinneringen wilt ontvangen.</p>
          <label class="notification-setting-row"><span class="notification-setting-icon" aria-hidden="true">▦</span><strong>Deadlineherinnering</strong><small>Wanneer wil je een herinnering ontvangen?</small><select class="input" data-role-deadline-days><option value="0">Op de dag zelf</option><option value="1">1 dag van tevoren</option><option value="2">2 dagen van tevoren</option><option value="7">1 week van tevoren</option></select></label>
          <div data-role-notification-quiet></div>
        </fieldset>
        <p class="notification-delivery-note">E-mailoverzichten en automatische herinneringen worden pas verstuurd zodra de bijbehorende verzendservice is ingericht.</p>
      </section>

      <section class="settings-panel hidden" data-settings-panel="account">
        <header><span aria-hidden="true">⚙</span><div><h2>Account</h2><p>Je persoonlijke gegevens en accountbeveiliging.</p></div></header>
        <p>Bekijk je profielgegevens of wijzig je wachtwoord via je profiel.</p>
        <button class="button dark" type="button" onclick="setSidebarAccountOpen(true)">Profiel en beveiliging openen →</button>
      </section>
    </div>
  </div>

  <div class="settings-footer" data-save-status="saved">
    <div class="settings-save-copy"><span class="settings-save-check" aria-hidden="true">✓</span><div><strong>Wijzigingen worden automatisch opgeslagen.</strong><small data-role-settings-status class="settings-saved" role="status">Je voorkeuren worden per account bewaard.</small></div></div>
    <button class="button hidden" type="button" data-role-settings-retry>Opnieuw proberen</button>
    <button class="button" type="button" data-role-settings-reset>Standaardinstellingen herstellen</button>
  </div>
</section>

@once
  @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/student-preferences.css') }}">
  @endpush
@endonce
