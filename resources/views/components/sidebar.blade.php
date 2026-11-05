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
  <section class="account-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
    <header class="account-modal-header">
      <div>
        <span class="account-modal-eyebrow">Mijn gegevens</span>
        <h2 id="accountModalTitle">Profiel</h2>
      </div>
      <button id="accountModalClose" class="account-modal-close" type="button" data-account-close aria-label="Profielvenster sluiten">×</button>
    </header>

    <div class="account-modal-person">
      <span class="account-modal-avatar" aria-hidden="true">{{ $frontendUser['initials'] }}</span>
      <span>
        <strong>{{ $user->naam }}</strong>
        <small>{{ ['coach' => 'Docent', 'student' => 'Student', 'beheerder' => 'Beheerder', 'teamleider' => 'Teamleider'][$frontendUser['role']] ?? ucfirst($frontendUser['role']) }}</small>
      </span>
    </div>

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
</div>
