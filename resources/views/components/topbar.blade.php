<header class="topbar">
  <div class="topbar-context">
    <span class="topbar-mark" aria-hidden="true">◈</span>
    <div class="topbar-title">
      <span class="topbar-project">IT Innovation Gateway</span>
      <strong>Mijn leeromgeving</strong>
    </div>
  </div>

  <time class="topbar-date" datetime="{{ now()->toDateString() }}">
    {{ ucfirst(now()->locale('nl')->translatedFormat('l j F Y')) }}
  </time>

  <div class="profile">
    <button
      id="processingNotification"
      class="processing-notification {{ $frontendUser['role'] === 'student' ? '' : 'hidden' }}"
      type="button"
      @if ($frontendUser['role'] === 'student') onclick="openHoursApprovalModal()" @endif
      aria-label="Nieuwe uren bekijken"
      title="Nieuwe urenverwerking"
    >
      <span class="processing-icon">✓</span><span class="processing-badge">0</span>
    </button>
    <span class="avatar" aria-hidden="true">{{ $frontendUser['initials'] }}</span>
    <span class="profile-copy">
      <strong class="profile-name">{{ $frontendUser['name'] }}</strong>
      <small>Ingelogd</small>
    </span>
  </div>
</header>
