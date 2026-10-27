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
        </div>
      </aside>
      <main class="main">
        <header class="topbar">
          <span>IT Innovation Gateway <span class="separator">/</span> Mijn leeromgeving</span>
          <span class="profile">
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
            <span class="avatar">SV</span><span class="profile-name">Sam de Vries</span></span
          >
        </header>
        {{-- Studentweergaven: dashboard, leeruitkomsten, uren, reflecties en feedback. --}}
        @if ($roleView)
          @include($roleView)
        @endif


        <div id="hint" class="warning hidden" role="status"></div>
        <p class="printlabel">Mock-up · ontwerp met demogegevens · definitieve rekenregels en rechten nog vast te stellen</p>
      </main>
    </div>

    @endif
@endsection

@push('scripts')
    @if ($user)
    <script>
      window.LARAVEL_USER = {{ Illuminate\Support\Js::from($frontendUser) }};
      window.LARAVEL_STATE = {{ Illuminate\Support\Js::from($frontendState) }};
      window.LARAVEL_SAVE_URL = {{ Illuminate\Support\Js::from(route('state.save')) }};
      window.LARAVEL_CSRF = {{ Illuminate\Support\Js::from(csrf_token()) }};
    </script>
    @foreach ($roleAssets['scripts'] as $script)
      <script src="{{ asset('assets/'.$script) }}"></script>
    @endforeach
    @endif
@endpush

