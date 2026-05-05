<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ludiweb')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header>
        <h1><a href="{{ url('/') }}">Ludiweb</a></h1>
        <nav>
            <a href="{{ url('/categories') }}">Categorías</a>
            <a href="{{ url('/questions') }}">Preguntas</a>
            <a href="{{ url('/games') }}">Partidas</a>
            <span id="auth-area"></span>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <script>
        window.API_BASE_URL = "{{ config('services.bankapi.url') }}";
    </script>
    <script src="{{ asset('js/apiClient.js') }}"></script>
    <script src="{{ asset('js/auth-ui.js') }}"></script>
    @stack('scripts')
</body>
</html>
