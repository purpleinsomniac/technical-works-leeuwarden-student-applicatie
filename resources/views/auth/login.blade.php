@extends('layouts.app')

@section('title', 'Ontwikkeling | Inloggen')

@section('content')
  <section class="login" id="login">
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
@endsection
