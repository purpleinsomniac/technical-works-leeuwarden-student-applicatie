<header class="topbar">
  <div class="topbar-context">
    <span class="topbar-mark" aria-hidden="true">◈</span>
    <div class="topbar-title">
      <span class="topbar-project">IT Innovation Gateway</span>
      <strong>Mijn leeromgeving</strong>
    </div>
  </div>

  <div class="topbar-actions">
    <time class="topbar-date" datetime="{{ now()->toDateString() }}">
      <span class="topbar-date-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4M16 3v4M3 10h18"></path>
        </svg>
      </span>
      <span>{{ ucfirst(now()->locale('nl')->translatedFormat('l j F Y')) }}</span>
    </time>
    <button
      id="processingNotification"
      class="processing-notification {{ $frontendUser['role'] === 'student' ? '' : 'hidden' }}"
      type="button"
      @if ($frontendUser['role'] === 'student') onclick="openHoursApprovalModal()" @endif
      aria-label="Nieuwe uren bekijken"
      title="Nieuwe urenverwerking"
    >
      <span class="processing-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path>
        </svg>
      </span>
      <span class="processing-badge">0</span>
    </button>
  </div>
</header>
