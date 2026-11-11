<!doctype html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Ontwikkeling')</title>

        <link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
        @stack('styles')
    </head>
    <body>
        @yield('content')

        @stack('scripts')
    </body>
</html>
