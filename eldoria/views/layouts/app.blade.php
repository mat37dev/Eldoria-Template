<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ site_name() }} — @yield('title', __('theme::theme.nav.home')) </title>

    {{-- favicon() gère déjà l'icône configurée en Admin > Général (avec repli sur
         le logo Azuriom par défaut) — même logique que site_logo() dans la navbar. --}}
    <link rel="icon" href="{{ favicon() }}">

    {{-- Injection des CSS custom properties depuis les settings sauvegardés.
         Le triplet RGB (--color-accent-rgb) est calculé à côté du hex : c'est lui
         que Tailwind utilise pour moduler l'opacité (bg-accent/10, etc., voir
         tailwind.config.js) — un var() pointant vers une chaîne hex ne le permet pas. --}}
    <?php
        $accentHex = ltrim(theme_config('color_accent', '#E9A62D'), '#');
        $accentSecondaryHex = ltrim(theme_config('color_accent_secondary', '#9D5C38'), '#');
        $toRgbTriple = fn (string $hex) => strlen($hex) === 6
            ? implode(' ', array_map('hexdec', str_split($hex, 2)))
            : '0 0 0';

        // Image d'ambiance partagée par toutes les pages sauf l'accueil (qui a son
        // propre hero plein écran) : même ordre de priorité que le hero d'accueil
        // (image du customizer > image d'arrière-plan de l'admin général > image
        // par défaut du thème), pour rester cohérent avec un seul réglage à la fois.
        $siteBackgroundImage = theme_config('hero_image')
            ?: (setting('background') ? image_url(setting('background')) : theme_asset('images/hero-aldorya.webp'));
    ?>
    <style>
        :root {
            --color-accent: #{{ $accentHex }};
            --color-accent-rgb: {{ $toRgbTriple($accentHex) }};
            --color-accent-secondary: #{{ $accentSecondaryHex }};
            --color-accent-secondary-rgb: {{ $toRgbTriple($accentSecondaryHex) }};
            --site-bg-image: url('{{ $siteBackgroundImage }}');
        }
    </style>

    {{-- Le thème a son propre build Vite, indépendant de celui de l'application
         (qui utilise public/build/manifest.json) : les assets sont donc servis
         directement via theme_asset() plutôt que la directive @vite(). --}}
    <link rel="stylesheet" href="{{ theme_asset('dist/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('dist/app.css') }}">
    <script type="module" src="{{ theme_asset('dist/app.js') }}"></script>

    @stack('styles')
    @stack('head')
</head>
<body class="bg-bg-primary text-text-primary font-body antialiased{{ request()->routeIs('home') ? '' : ' site-bg' }}">

    @include('partials.navbar')

    @include('elements.session-alerts')

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
    @stack('footer-scripts')

</body>
</html>
