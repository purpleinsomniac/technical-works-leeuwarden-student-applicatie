<!doctype html>
<html lang="nl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Onderhoud | Ontwikkeling</title>
    <link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
    <style>
      main { max-width: 620px; margin: 12vh auto; padding: 32px; }
      p { color: #647789; line-height: 1.7; }
    </style>
  </head>
  <body>
    <main>
      <p class="eyebrow">Tijdelijk niet beschikbaar</p>
      <h1>We voeren onderhoud uit</h1>
      <p>De leeromgeving is tijdelijk niet beschikbaar. Probeer het later opnieuw.</p>
      @auth
        <form action="{{ route('logout') }}" method="post">
          @csrf
          <button class="button dark" type="submit">Uitloggen</button>
        </form>
      @else
        <a class="button dark" href="{{ route('login') }}">Inloggen</a>
      @endauth
    </main>
  </body>
</html>
