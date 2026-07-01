<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ site_name() }} — @yield('title', 'Accueil') </title>

    {{-- Injection des CSS custom properties depuis les settings sauvegardés --}}
    <style>
        :root {
            --color-accent: {{ theme_config('color_accent', '#C9A84C') }};
            --color-accent-secondary: {{ theme_config('color_accent_secondary', '#7B3F2E') }};
        }
    </style>

    {{-- Le thème a son propre build Vite, indépendant de celui de l'application
         (qui utilise public/build/manifest.json) : les assets sont donc servis
         directement via theme_asset() plutôt que la directive @vite(). --}}
    <link rel="stylesheet" href="{{ theme_asset('dist/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('dist/app.css') }}">
    <script type="module" src="{{ theme_asset('dist/app.js') }}"></script>

    @stack('head')
</head>
<body class="bg-bg-primary text-text-primary font-body antialiased">

    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.customizer')
        @endif
    @endauth

    @include('partials.particles')

    @stack('scripts')

</body>
</html>
