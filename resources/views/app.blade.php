@extends('layouts.app')

@section('title', 'Ontwikkeling | Jouw leeromgeving')

@if ($roleAssets['style'])
  @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/'.$roleAssets['style']) }}">
  @endpush
@endif

@section('content')
    {{-- Inlogscherm voor bezoekers zonder actieve sessie. --}}
    <section class="login {{ $user ? 'hidden' : '' }}" id="login">
      <div class="intro">
        <div class="brand">Ontwikkeling</div>
        <h1>Persoonlijke ontwikkelomgeving</h1>
        <p>Ontwikkeling per WRO-fase, leeruitkomsten, uren en de opbouw over tijd.</p>
      </div>
      <div class="form">
        <div>
          <h1>Inloggen</h1>
          <p class="dim">Gebruik je persoonlijke account.</p>
          @if ($errors->any())
            <div class="warning" role="alert">{{ $errors->first() }}</div>
          @endif
          <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label class="label" for="email">E-mailadres</label>
            <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">

            <label class="label" for="password">Wachtwoord</label>
            <input class="input" id="password" name="password" type="password" required autocomplete="current-password">

            <button class="button dark" type="submit">Inloggen →</button>
          </form>
        </div>
      </div>
    </section>
    @if ($user)
    <div class="app" id="app">
      <aside class="side">
        <div class="brand">Ontwikkeling</div>
        <div class="dim small" id="roleLabel"></div>
        <hr style="width: 100%; border: 0; border-top: 1px solid #ddd" />
        <div id="nav"></div>
        <div class="foot">
          <b id="actor"> </b>
          <p class="small dim">Voorbeeldgegevens</p>
          <form action="{{ route('logout') }}" method="post">
            @csrf
            <button class="nav" type="submit">Uitloggen</button>
          </form>
          <p class="product-credit">
            <span>Product of</span>
            <strong>Technical Works Leeuwarden</strong>
          </p>
        </div>
      </aside>
      <main class="main">
        {{-- Gedeelde header voor alle rollen. --}}
        <header class="topbar">
          <div class="topbar-context">
            <span class="topbar-mark" aria-hidden="true">◈</span>
            <div class="topbar-title">
              <span class="topbar-project">IT Innovation Gateway</span>
              <strong>Mijn leeromgeving</strong>
            </div>
          </div>
          <time class="topbar-date" datetime="{{ now()->toDateString() }}">{{ ucfirst(now()->locale('nl')->translatedFormat('l j F Y')) }}</time>
          <div class="profile">
            <button
              id="processingNotification"
              class="processing-notification"
              type="button"
              onclick="openHoursApprovalModal()"
              aria-label="42 nieuwe uren bekijken"
              title="Nieuwe urenverwerking"
            >
              <span class="processing-icon">✓</span><span class="processing-badge">42</span>
            </button>
            <span class="avatar" aria-hidden="true">{{ $frontendUser['initials'] }}</span>
            <span class="profile-copy">
              <strong class="profile-name">{{ $frontendUser['name'] }}</strong>
              <small>Ingelogd</small>
            </span>
          </div>
        </header>
        {{-- Rolweergaven met onder andere dashboard, opdrachten en leeruitkomsten. --}}
        @if ($roleView)
          @include($roleView)
        @endif


        <div id="hint" class="warning hidden" role="status"></div>
        {{-- Gedeelde footer voor alle rollen. --}}
        <footer class="app-footer">
          <div class="footer-main">
            <div class="footer-identity">
              <span class="footer-mark" aria-hidden="true">◈</span>
              <div class="footer-brand">
                <strong>Ontwikkeling</strong>
                <span>IT Innovation Gateway</span>
              </div>
            </div>
            <div class="footer-meta">
              <span>Persoonlijke leeromgeving</span>
              <span>© {{ date('Y') }} Technical Works Leeuwarden</span>
            </div>
          </div>
          <div class="footer-notice">
            <span class="footer-notice-icon" aria-hidden="true">i</span>
            <p class="printlabel">Deze applicatie gebruikt momenteel demogegevens. Definitieve rekenregels en gebruikersrechten worden nog vastgesteld.</p>
          </div>
        </footer>
      </main>
    </div>

    @endif
@endsection

@push('scripts')
    @if ($user)
    <script>
      window.LARAVEL_USER = {{ Illuminate\Support\Js::from($frontendUser) }};
      window.LARAVEL_STATE = {{ Illuminate\Support\Js::from($frontendState) }};
      window.LARAVEL_DATA = {{ Illuminate\Support\Js::from($frontendData) }};
      window.LARAVEL_SAVE_URL = {{ Illuminate\Support\Js::from(route('state.save')) }};
      window.LARAVEL_API = {{ Illuminate\Support\Js::from([
          'reflectionStore' => route('reflections.store'),
          'reflectionDelete' => route('reflections.destroy', ['reflection' => '__ID__']),
          'feedbackStore' => route('feedback.store'),
          'feedbackDelete' => route('feedback.destroy', ['feedback' => '__ID__']),
          'goalStore' => route('goals.store'),
          'assignmentSubmit' => route('assignments.submit', ['assignment' => '__ID__']),
      ]) }};
      window.LARAVEL_CSRF = {{ Illuminate\Support\Js::from(csrf_token()) }};
    </script>
    @foreach ($roleAssets['scripts'] as $script)
      <script src="{{ asset('assets/'.$script) }}"></script>
    @endforeach
    @endif
@endpush

