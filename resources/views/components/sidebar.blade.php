<aside class="side">
  <div class="sidebar-brand-row">
    <div class="brand">Ontwikkeling</div>
    @if ($frontendUser['role'] === 'coach')
      <button
        class="coach-mobile-menu-button"
        id="coachMobileMenuButton"
        type="button"
        aria-label="Menu"
        aria-expanded="false"
        aria-controls="nav"
      >
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </button>
    @endif
  </div>
  <div class="dim small" id="roleLabel"></div>
  <hr>
  <nav id="nav" aria-label="Hoofdnavigatie"></nav>

  <div class="foot">
    <button
      id="sidebarAccountButton"
      class="sidebar-account"
      type="button"
      aria-expanded="false"
      aria-controls="sidebarAccountDetails"
    >
      <span id="sidebarAvatar" class="sidebar-avatar" aria-hidden="true">ST</span>
      <span class="sidebar-account-copy"><b id="actor"></b><small id="sidebarRole">{{ ['coach' => 'Docent', 'student' => 'Student', 'beheerder' => 'Beheerder', 'teamleider' => 'Teamleider'][$frontendUser['role']] ?? ucfirst($frontendUser['role']) }}</small></span>
      <span class="sidebar-account-arrow" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m9 18 6-6-6-6"></path>
        </svg>
      </span>
    </button>
    <form action="{{ route('logout') }}" method="post">
      @csrf
      <button class="nav sidebar-logout" type="submit"><span class="nav-icon" data-icon="logout" aria-hidden="true"></span><span>Uitloggen</span></button>
    </form>
    <p class="product-credit">
      <span>Product of</span>
      <strong>Technical Works Leeuwarden</strong>
    </p>
  </div>
</aside>

<div id="sidebarAccountDetails" class="account-modal hidden" aria-hidden="true">
  <button class="account-modal-backdrop" type="button" tabindex="-1" data-account-close aria-label="Profielvenster sluiten"></button>
  <section class="account-modal-dialog profile-redesign {{ in_array($frontendUser['role'], ['beheerder', 'teamleider', 'coach'], true) ? 'account-modal-dialog--settings' : '' }}" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
    <header class="account-modal-header">
      <div>
        <span class="account-modal-eyebrow">Mijn gegevens</span>
        <h2 id="accountModalTitle">Profiel</h2>
      </div>
      <button id="accountModalClose" class="account-modal-close" type="button" data-account-close aria-label="Profielvenster sluiten">×</button>
    </header>

    @php($profileSettings = $user->appState?->gegevens['profile-settings'] ?? [])
    <form id="profileSettingsForm" action="{{ route('profile.update') }}" method="post">
    @csrf
    @method('PATCH')
    <div class="profile-grid">
    <section class="profile-card"><header><span aria-hidden="true">♙</span><div><h3>Persoonlijke gegevens</h3><p>Je basisgegevens en accountinformatie.</p></div></header>
    <dl class="account-modal-data">
      <div>
        <dt>Naam</dt>
        <dd>{{ $user->naam }}</dd>
      </div>
      <div>
        <dt>E-mailadres</dt>
        <dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd>
      </div>
      <div>
        <dt>Accounttype</dt>
        <dd>{{ ['coach' => 'Docent', 'student' => 'Student', 'beheerder' => 'Beheerder', 'teamleider' => 'Teamleider'][$frontendUser['role']] ?? ucfirst($frontendUser['role']) }}</dd>
      </div>
    </dl>
    </section>
    @if (in_array($frontendUser['role'], ['beheerder', 'teamleider', 'coach'], true))
      <section class="profile-card profile-dashboard-settings">
        @include('components.account-dashboard-settings')
      </section>
    @endif
    <section class="profile-card"><header><span aria-hidden="true">♧</span><div><h3>Accountbeveiliging</h3><p>Beheer je wachtwoord en accounttoegang.</p></div></header><div class="profile-card-body">
      <p id="profilePasswordChanged" class="profile-password-date">◷ {{ isset($profileSettings['password_changed_at']) ? 'Laatste wijziging: '.\Carbon\Carbon::parse($profileSettings['password_changed_at'])->locale('nl')->diffForHumans() : 'Nog geen wachtwoordwijziging geregistreerd' }}</p>
      <details id="profilePasswordEditor"><summary class="profile-action">⚿ <strong>Wachtwoord wijzigen</strong><span>›</span></summary><div class="profile-password-fields">
        <label>Huidig wachtwoord<input class="input" type="password" name="current_password" autocomplete="current-password"></label>
        <label>Nieuw wachtwoord<input class="input" type="password" name="password" minlength="8" autocomplete="new-password"></label>
        <label>Herhaal nieuw wachtwoord<input class="input" type="password" name="password_confirmation" autocomplete="new-password"></label>
        <small>Minimaal 8 tekens, met hoofdletters, kleine letters en cijfers.</small>
      </div></details>
      <button class="profile-action" type="button" id="profileResetPassword">⟳ <strong>Wachtwoord opnieuw instellen</strong><span>›</span></button>
    </div></section>
    <section class="profile-card"><header><span aria-hidden="true">♧</span><div><h3>Inlog en beveiliging</h3><p>Extra beveiliging voor je account.</p></div></header><div class="profile-card-body">
      <label class="profile-option"><span class="profile-option-icon" aria-hidden="true">▯</span><span><strong>Tweestapsverificatie</strong><small>Nog niet beschikbaar voor dit platform.</small></span><input class="profile-switch" type="checkbox" disabled aria-label="Tweestapsverificatie nog niet beschikbaar"></label>
      <details class="profile-device"><summary class="profile-option"><span class="profile-option-icon" aria-hidden="true">▱</span><span><strong>Ingelogde apparaten</strong><small>Bekijk je huidige sessie.</small></span><span>›</span></summary><p>Dit apparaat · huidige sessie<br><small>Andere sessies zijn nog niet beschikbaar in dit overzicht.</small></p></details>
    </div></section>
    <section class="profile-card"><header><span aria-hidden="true">♧</span><div><h3>Meldingen</h3><p>Bepaal welke meldingen je wilt ontvangen.</p></div></header><div class="profile-card-body">
      <label class="profile-option"><span class="profile-option-icon" aria-hidden="true">✉</span><span><strong>E-mailmeldingen</strong><small>Je voorkeur voor updates per e-mail.</small></span><input class="profile-switch" name="email_notifications" type="checkbox" @checked($profileSettings['email_notifications'] ?? true)></label>
      <label class="profile-option"><span class="profile-option-icon" aria-hidden="true">♧</span><span><strong>Platformmeldingen</strong><small>Je voorkeur voor meldingen in het platform.</small></span><input class="profile-switch" name="platform_notifications" type="checkbox" @checked($profileSettings['platform_notifications'] ?? true)></label>
    </div></section>
    </div>
    <footer class="profile-footer"><p id="profileSaveMessage" role="status"></p><button class="button dark" type="submit">✓ Wijzigingen opslaan</button><button class="button" type="button" data-account-close>Annuleren</button></footer>
    </form>
  </section>
</div>
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/js/profile.js') }}"></script>
@endpush
@endonce
