@extends('layouts.app')

@section('title', 'Ontwikkeling | Wachtwoord vergeten')

@section('content')
  <section class="login" id="password-request">
    <div class="intro">
      <div class="brand">Ontwikkeling</div>
      <h1>Veilig toegang herstellen</h1>
      <p>Vraag een persoonlijke herstellink aan en kies daarna een nieuw wachtwoord.</p>
    </div>
    <div class="form">
      <div>
        <a class="auth-back-link" href="{{ route('login') }}" aria-label="Terug naar inloggen">← Terug naar inloggen</a>
        <h1>Wachtwoord vergeten?</h1>
        <p class="dim">Vul uw e-mailadres in. U ontvangt een veilige link waarmee u een nieuw wachtwoord kunt instellen.</p>

        @if (session('status'))
          <div class="auth-message success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
          <div class="auth-message error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
          @csrf
          <label class="label" for="email">E-mailadres</label>
          <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
          <button class="button dark" type="submit">Herstellink versturen →</button>
        </form>
        <p class="auth-help">Geen e-mail ontvangen? Controleer uw map met ongewenste e-mail of probeer het na een minuut opnieuw.</p>
      </div>
    </div>
  </section>
@endsection
