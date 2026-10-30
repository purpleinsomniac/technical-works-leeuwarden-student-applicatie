@extends('layouts.app')

@section('title', 'Ontwikkeling | Nieuw wachtwoord')

@section('content')
  <section class="login" id="password-reset">
    <div class="intro">
      <div class="brand">Ontwikkeling</div>
      <h1>Kies een nieuw wachtwoord</h1>
      <p>Gebruik een sterk wachtwoord dat u niet voor andere accounts gebruikt.</p>
    </div>
    <div class="form">
      <div>
        <a class="auth-back-link" href="{{ route('login') }}" aria-label="Terug naar inloggen">← Terug naar inloggen</a>
        <h1>Wachtwoord herstellen</h1>
        <p class="dim">Vul tweemaal hetzelfde nieuwe wachtwoord in.</p>

        @if ($errors->any())
          <div class="auth-message error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
          @csrf
          <input name="token" type="hidden" value="{{ $token }}">

          <label class="label" for="email">E-mailadres</label>
          <input class="input" id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">

          <label class="label" for="password">Nieuw wachtwoord</label>
          <input class="input" id="password" name="password" type="password" required autocomplete="new-password">

          <label class="label" for="password_confirmation">Herhaal nieuw wachtwoord</label>
          <input class="input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">

          <button class="button dark" type="submit">Wachtwoord aanpassen →</button>
        </form>
        <p class="auth-help">Gebruik minimaal acht tekens met hoofdletters, kleine letters en cijfers.</p>
      </div>
    </div>
  </section>
@endsection
