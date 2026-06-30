# Eldoria — Azuriom Theme Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Créer le thème Azuriom "Eldoria" — thème RPG médiéval pour serveurs Minecraft, avec customizer visuel live, animations immersives et expérience mobile-first, vendable sur market.azuriom.com.

**Architecture:** Thème Laravel/Blade standard Azuriom, avec Tailwind CSS pour le design system basé sur des CSS custom properties, Alpine.js pour les interactions (customizer, drawer), et GSAP+AOS pour les animations. Le customizer front-end écrit en AJAX vers le système de settings Azuriom et injecte les CSS variables dynamiquement.

**Tech Stack:** Laravel/Blade, Tailwind CSS v3, Alpine.js v3, GSAP 3, AOS, Vite, Vanilla JS Canvas (particles)

## Global Constraints

- Compatibilité Azuriom >= 1.0 (API azuriom_api: "1.0")
- Plugins supportés v1 : Shop, Vote, Forum (plugins officiels Azuriom)
- Mobile-first : CSS écrit pour < 640px en premier, desktop via `min-width`
- Toutes les animations respectent `prefers-reduced-motion: reduce`
- Particules et parallaxe désactivées sur mobile (< 640px)
- CSS custom properties nommées `--color-bg-primary`, `--color-bg-secondary`, `--color-accent`, `--color-accent-secondary`, `--color-text-primary`, `--color-text-secondary`
- Police titres : Cinzel (Google Fonts), Corps : Inter (Google Fonts)
- Le bouton "Personnaliser" du customizer est rendu uniquement pour les administrateurs Azuriom
- Prix cible market : 9€

---

## Carte des fichiers

```
eldoria/                          ← racine du thème
├── theme.json                    ← métadonnées Azuriom (nom, version, auteur, azuriom_api)
├── package.json                  ← dépendances JS (Tailwind, GSAP, AOS, Alpine)
├── vite.config.js                ← config Vite (entrée: assets/js/app.js + assets/css/app.css)
├── tailwind.config.js            ← tokens design (couleurs via CSS vars, fonts Cinzel+Inter)
├── config/
│   └── theme.json                ← déclaration des settings pour le customizer Azuriom
├── assets/
│   ├── css/
│   │   └── app.css               ← Tailwind directives + CSS custom properties globales
│   ├── js/
│   │   ├── app.js                ← entrée principale (import Alpine, AOS init)
│   │   ├── customizer.js         ← logic du drawer customizer (Alpine component)
│   │   ├── particles.js          ← canvas particles vanilla JS
│   │   └── animations.js         ← GSAP parallax, compteurs animés, pulse bouton
│   └── images/
│       └── hero-default.jpg      ← image hero par défaut (paysage fantasy sombre)
├── views/
│   ├── layouts/
│   │   └── app.blade.php         ← layout principal (head, navbar, slot, footer, scripts)
│   ├── partials/
│   │   ├── navbar.blade.php      ← navigation responsive (burger mobile)
│   │   ├── footer.blade.php      ← footer avec IP, réseaux sociaux, liens
│   │   ├── customizer.blade.php  ← drawer customizer (rendu si admin)
│   │   └── particles.blade.php   ← canvas tag + init conditionnel
│   ├── home.blade.php            ← page d'accueil (hero + stats + shop + vote + forum)
│   ├── errors/
│   │   ├── 404.blade.php
│   │   ├── 403.blade.php
│   │   └── 500.blade.php
│   └── vendor/
│       ├── shop/                 ← vues override plugin Shop
│       │   ├── index.blade.php   ← liste catégories
│       │   ├── show.blade.php    ← page produit
│       │   ├── cart.blade.php    ← panier
│       │   └── checkout.blade.php
│       ├── vote/
│       │   └── index.blade.php   ← liste sites de vote
│       └── forum/
│           ├── index.blade.php   ← liste catégories forum
│           ├── show.blade.php    ← liste sujets d'une catégorie
│           └── topic.blade.php   ← page discussion
```

---

## Phase 1 — Foundation

### Task 1 : Scaffold du projet

**Files:**
- Create: `eldoria/theme.json`
- Create: `eldoria/package.json`
- Create: `eldoria/vite.config.js`
- Create: `eldoria/tailwind.config.js`

**Interfaces:**
- Produces: structure de dossiers du thème, config Vite/Tailwind prête à builder

- [ ] **Step 1 : Créer le répertoire du thème**

Dans le dossier `themes/` de ton installation Azuriom (ou dans ce repo de travail) :

```bash
mkdir -p eldoria/assets/css
mkdir -p eldoria/assets/js
mkdir -p eldoria/assets/images
mkdir -p eldoria/views/layouts
mkdir -p eldoria/views/partials
mkdir -p eldoria/views/errors
mkdir -p eldoria/views/vendor/shop
mkdir -p eldoria/views/vendor/vote
mkdir -p eldoria/views/vendor/forum
mkdir -p eldoria/config
```

- [ ] **Step 2 : Créer `eldoria/theme.json`**

```json
{
    "id": "eldoria",
    "name": "Eldoria",
    "description": "Thème RPG médiéval premium pour serveurs Minecraft. Customizer live, animations immersives, mobile-first.",
    "version": "1.0.0",
    "url": "https://market.azuriom.com",
    "authors": ["TonPseudo"],
    "azuriom_api": "1.0"
}
```

- [ ] **Step 3 : Créer `eldoria/package.json`**

```json
{
    "name": "eldoria",
    "private": true,
    "version": "1.0.0",
    "scripts": {
        "dev": "vite",
        "build": "vite build"
    },
    "devDependencies": {
        "autoprefixer": "^10.4.0",
        "postcss": "^8.4.0",
        "tailwindcss": "^3.4.0",
        "vite": "^5.0.0"
    },
    "dependencies": {
        "@alpinejs/persist": "^3.13.0",
        "alpinejs": "^3.13.0",
        "aos": "^2.3.4",
        "gsap": "^3.12.0"
    }
}
```

- [ ] **Step 4 : Créer `eldoria/vite.config.js`**

```js
import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
    build: {
        outDir: 'assets/dist',
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'assets/js/app.js'),
            },
        },
    },
})
```

- [ ] **Step 5 : Créer `eldoria/tailwind.config.js`**

```js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'bg-primary': 'var(--color-bg-primary)',
                'bg-secondary': 'var(--color-bg-secondary)',
                'accent': 'var(--color-accent)',
                'accent-secondary': 'var(--color-accent-secondary)',
                'text-primary': 'var(--color-text-primary)',
                'text-secondary': 'var(--color-text-secondary)',
            },
            fontFamily: {
                'display': ['Cinzel', 'serif'],
                'body': ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
```

- [ ] **Step 6 : Installer les dépendances et vérifier le build**

```bash
cd eldoria
npm install
npm run build
```

Attendu : dossier `assets/dist/` créé sans erreurs.

- [ ] **Step 7 : Commit**

```bash
git add eldoria/
git commit -m "feat(eldoria): scaffold projet — theme.json, Vite, Tailwind"
```

---

### Task 2 : CSS Foundation & Custom Properties

**Files:**
- Create: `eldoria/assets/css/app.css`
- Create: `eldoria/config/theme.json`

**Interfaces:**
- Produces: `--color-*` CSS variables disponibles globalement, classes Tailwind utilisables dans tous les Blade

- [ ] **Step 1 : Créer `eldoria/assets/css/app.css`**

```css
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700&display=swap');

@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
    --color-bg-primary: #0F0D0A;
    --color-bg-secondary: #1A1612;
    --color-accent: #C9A84C;
    --color-accent-secondary: #7B3F2E;
    --color-text-primary: #E8DCC8;
    --color-text-secondary: #8A7A62;
}

@layer base {
    body {
        @apply bg-bg-primary text-text-primary font-body;
    }

    h1, h2, h3, h4, h5, h6 {
        @apply font-display text-text-primary;
    }

    a {
        @apply transition-colors duration-200;
    }
}

@layer components {
    .btn-primary {
        @apply inline-flex items-center px-6 py-3 bg-accent text-bg-primary font-display font-semibold
               rounded-sm border border-accent hover:bg-accent/90 transition-all duration-300
               tracking-wider uppercase text-sm;
    }

    .card-eldoria {
        @apply bg-bg-secondary border border-accent/20 rounded-sm
               hover:border-accent/60 transition-all duration-300;
    }

    .section-title {
        @apply font-display text-3xl font-bold text-accent tracking-widest uppercase
               text-center mb-2;
    }

    .section-subtitle {
        @apply text-text-secondary text-center mb-12 tracking-wide;
    }
}

/* Grain overlay subtil */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 1;
    opacity: 0.4;
}
```

- [ ] **Step 2 : Créer `eldoria/config/theme.json`**

Ce fichier déclare les settings que le customizer expose. Azuriom lit ce fichier pour savoir quelles valeurs persister.

```json
{
    "settings": [
        {
            "name": "color_accent",
            "default": "#C9A84C"
        },
        {
            "name": "color_accent_secondary",
            "default": "#7B3F2E"
        },
        {
            "name": "hero_slogan",
            "default": "Bienvenue dans le royaume de..."
        },
        {
            "name": "hero_image",
            "default": ""
        },
        {
            "name": "show_section_shop",
            "default": "1"
        },
        {
            "name": "show_section_vote",
            "default": "1"
        },
        {
            "name": "show_section_forum",
            "default": "1"
        },
        {
            "name": "footer_discord",
            "default": ""
        },
        {
            "name": "footer_twitter",
            "default": ""
        }
    ]
}
```

> **Note :** Vérifier dans la documentation Azuriom la syntaxe exacte de ce fichier — elle peut varier selon la version. La structure ci-dessus est basée sur l'API Azuriom 1.0.

- [ ] **Step 3 : Vérifier le build CSS**

```bash
npm run build
```

Attendu : `assets/dist/app.css` généré, contient les classes Tailwind et les CSS variables.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/assets/css/app.css eldoria/config/theme.json
git commit -m "feat(eldoria): CSS foundation, custom properties, composants de base"
```

---

### Task 3 : Layout principal & injection des settings

**Files:**
- Create: `eldoria/assets/js/app.js`
- Create: `eldoria/views/layouts/app.blade.php`

**Interfaces:**
- Consumes: `assets/dist/app.css` (Task 2), settings Azuriom (`theme_setting('color_accent')` etc.)
- Produces: layout Blade avec injection CSS vars, Alpine.js, AOS initialisé

- [ ] **Step 1 : Créer `eldoria/assets/js/app.js`**

```js
import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { initAnimations } from './animations.js'
import { initParticles } from './particles.js'

window.Alpine = Alpine
Alpine.plugin(persist)
Alpine.start()

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 700,
        easing: 'ease-out-cubic',
        once: true,
        offset: 60,
    })

    const isMobile = window.innerWidth < 640
    if (!isMobile) {
        initParticles()
    }

    initAnimations()
})
```

- [ ] **Step 2 : Créer `eldoria/views/layouts/app.blade.php`**

```blade
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
            --color-accent: {{ theme_setting('color_accent', '#C9A84C') }};
            --color-accent-secondary: {{ theme_setting('color_accent_secondary', '#7B3F2E') }};
        }
    </style>

    @vite(['assets/js/app.js'])

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
```

> **Note :** `theme_setting()` est un helper Azuriom pour lire les settings de thème. Vérifier son nom exact dans la doc Azuriom — peut être `setting()` ou `theme_config()` selon la version.

- [ ] **Step 3 : Vérifier en installant le thème dans Azuriom**

Copier le dossier `eldoria/` dans `resources/themes/` de l'installation Azuriom de test. Activer depuis le panel admin. Vérifier que la page charge sans erreur 500.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/assets/js/app.js eldoria/views/layouts/app.blade.php
git commit -m "feat(eldoria): layout principal avec injection CSS vars et Alpine.js"
```

---

### Task 4 : Navbar & Footer

**Files:**
- Create: `eldoria/views/partials/navbar.blade.php`
- Create: `eldoria/views/partials/footer.blade.php`

**Interfaces:**
- Consumes: layout `app.blade.php` (Task 3)
- Produces: navigation responsive avec burger mobile, footer avec IP et réseaux sociaux

- [ ] **Step 1 : Créer `eldoria/views/partials/navbar.blade.php`**

```blade
<header class="fixed top-0 left-0 right-0 z-50 bg-bg-primary/90 backdrop-blur-sm border-b border-accent/10"
        x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="font-display font-bold text-xl text-accent tracking-widest uppercase">
                @if(config('app.logo'))
                    <img src="{{ config('app.logo') }}" alt="{{ site_name() }}" class="h-8">
                @else
                    {{ site_name() }}
                @endif
            </a>

            {{-- Navigation desktop --}}
            <nav class="hidden md:flex items-center gap-8">
                @foreach(navbar_elements() as $element)
                    <a href="{{ $element->link() }}"
                       class="text-text-secondary hover:text-accent text-sm tracking-widest uppercase font-medium transition-colors">
                        {{ $element->name }}
                    </a>
                @endforeach
            </nav>

            {{-- Actions desktop --}}
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('profile.index') }}"
                       class="text-text-secondary hover:text-accent text-sm transition-colors">
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-text-secondary hover:text-accent text-sm transition-colors">
                            {{ trans('auth.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-text-secondary hover:text-accent text-sm tracking-widest uppercase transition-colors">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4">
                        S'inscrire
                    </a>
                @endauth
            </div>

            {{-- Burger mobile --}}
            <button @click="open = !open" class="md:hidden p-2 text-text-secondary hover:text-accent"
                    aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Drawer mobile --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="md:hidden bg-bg-secondary border-t border-accent/10 px-4 py-6 space-y-4">
        @foreach(navbar_elements() as $element)
            <a href="{{ $element->link() }}"
               class="block text-text-secondary hover:text-accent text-sm tracking-widest uppercase py-2 transition-colors">
                {{ $element->name }}
            </a>
        @endforeach
        <div class="pt-4 border-t border-accent/10 space-y-3">
            @auth
                <a href="{{ route('profile.index') }}" class="block text-text-secondary hover:text-accent text-sm transition-colors">
                    {{ auth()->user()->name }}
                </a>
            @else
                <a href="{{ route('login') }}" class="block text-text-secondary hover:text-accent text-sm uppercase tracking-widest transition-colors">Connexion</a>
                <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4 inline-block">S'inscrire</a>
            @endauth
        </div>
    </div>
</header>
```

- [ ] **Step 2 : Créer `eldoria/views/partials/footer.blade.php`**

```blade
<footer class="bg-bg-secondary border-t border-accent/10 mt-24">
    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

            {{-- Brand --}}
            <div>
                <h3 class="font-display text-accent text-lg tracking-widest uppercase mb-4">
                    {{ site_name() }}
                </h3>
                <p class="text-text-secondary text-sm leading-relaxed">
                    {{ theme_setting('hero_slogan', '') }}
                </p>
                {{-- IP serveur --}}
                @if(config('app.server_ip'))
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-text-secondary text-xs uppercase tracking-wider">IP :</span>
                        <button onclick="navigator.clipboard.writeText('{{ config('app.server_ip') }}')"
                                class="text-accent font-mono text-sm hover:text-accent/80 transition-colors"
                                title="Copier l'IP">
                            {{ config('app.server_ip') }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Navigation --}}
            <div>
                <h4 class="font-display text-text-primary text-sm tracking-widest uppercase mb-4">Navigation</h4>
                <nav class="space-y-2">
                    @foreach(navbar_elements() as $element)
                        <a href="{{ $element->link() }}"
                           class="block text-text-secondary hover:text-accent text-sm transition-colors">
                            {{ $element->name }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Réseaux sociaux --}}
            <div>
                <h4 class="font-display text-text-primary text-sm tracking-widest uppercase mb-4">Communauté</h4>
                <div class="flex gap-4">
                    @if(theme_setting('footer_discord'))
                        <a href="{{ theme_setting('footer_discord') }}" target="_blank" rel="noopener"
                           class="text-text-secondary hover:text-accent transition-colors" aria-label="Discord">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
                            </svg>
                        </a>
                    @endif
                    @if(theme_setting('footer_twitter'))
                        <a href="{{ theme_setting('footer_twitter') }}" target="_blank" rel="noopener"
                           class="text-text-secondary hover:text-accent transition-colors" aria-label="Twitter/X">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-accent/10 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-text-secondary text-xs">
                &copy; {{ date('Y') }} {{ site_name() }}. Tous droits réservés.
            </p>
            <p class="text-text-secondary text-xs">
                Propulsé par <a href="https://azuriom.com" class="text-accent/70 hover:text-accent transition-colors">Azuriom</a>
                · Thème <span class="text-accent/70">Eldoria</span>
            </p>
        </div>
    </div>
</footer>
```

- [ ] **Step 3 : Vérifier visuellement**

Ouvrir le site Azuriom de test. Vérifier :
- Navbar visible, logo affiché
- Liens de navigation fonctionnels
- Burger menu fonctionnel sur mobile (< 640px)
- Footer affiché avec IP copiable

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/partials/
git commit -m "feat(eldoria): navbar responsive et footer avec IP et réseaux sociaux"
```

---

## Phase 2 — Page d'accueil

### Task 5 : Hero section

**Files:**
- Create: `eldoria/views/home.blade.php` (section Hero uniquement pour l'instant)

**Interfaces:**
- Consumes: layout `app.blade.php` (Task 3), settings `hero_slogan`, `hero_image`
- Produces: hero plein écran avec parallaxe, IP, bouton rejoindre, pulse animation

- [ ] **Step 1 : Créer `eldoria/views/home.blade.php` avec le hero**

```blade
@extends('layouts.app')

@section('title', 'Accueil')

@section('content')

{{-- ======= HERO ======= --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden" id="hero">

    {{-- Background image --}}
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" id="hero-bg"
         style="background-image: url('{{ theme_setting('hero_image') ?: asset('themes/eldoria/assets/images/hero-default.jpg') }}')">
    </div>

    {{-- Overlay dégradé --}}
    <div class="absolute inset-0 bg-gradient-to-b from-bg-primary/60 via-bg-primary/40 to-bg-primary"></div>

    {{-- Contenu hero --}}
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto pt-16">
        <p class="text-accent text-sm font-display tracking-[0.4em] uppercase mb-4 opacity-80">
            ✦ Serveur Minecraft ✦
        </p>

        <h1 class="font-display text-5xl md:text-7xl font-black text-text-primary leading-tight mb-6"
            style="text-shadow: 0 2px 30px rgba(0,0,0,0.8)">
            {{ site_name() }}
        </h1>

        <p class="text-text-secondary text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
            {{ theme_setting('hero_slogan', 'Bienvenue dans le royaume. Rejoignez l\'aventure.') }}
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            {{-- Bouton Rejoindre avec pulse --}}
            @if(config('app.server_ip'))
                <button onclick="navigator.clipboard.writeText('{{ config('app.server_ip') }}')"
                        class="btn-primary relative group min-w-[180px] min-h-[48px]" id="btn-join">
                    <span class="absolute inset-0 rounded-sm animate-ping opacity-30 bg-accent"></span>
                    <span class="relative">Rejoindre</span>
                    <span class="relative ml-2 text-xs font-mono opacity-70">{{ config('app.server_ip') }}</span>
                </button>
            @endif

            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center px-6 py-3 min-h-[48px] border border-accent/40
                      text-text-primary font-display text-sm tracking-widest uppercase
                      hover:border-accent hover:text-accent transition-all duration-300 rounded-sm">
                S'inscrire
            </a>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-5 h-5 text-accent/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- Les autres sections seront ajoutées dans les tasks suivantes --}}

@endsection
```

- [ ] **Step 2 : Ajouter l'image hero par défaut**

Placer une image de paysage fantasy sombre (1920×1080 minimum) dans `eldoria/assets/images/hero-default.jpg`. Image libre de droits depuis Unsplash (recherche "fantasy landscape dark minecraft").

- [ ] **Step 3 : Vérifier visuellement**

- Hero plein écran visible
- Image de fond + overlay correct
- Bouton pulse animé
- Responsive sur mobile (texte lisible, boutons 48px minimum)

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/home.blade.php eldoria/assets/images/
git commit -m "feat(eldoria): hero section plein écran avec pulse et IP copiable"
```

---

### Task 6 : Stats band & sections homepage

**Files:**
- Modify: `eldoria/views/home.blade.php` (ajouter sections Stats, Shop preview, Vote, Forum)

**Interfaces:**
- Consumes: layout (Task 3), helpers Azuriom (`players_online()`, etc.)
- Produces: page d'accueil complète avec toutes les sections, data-aos sur chaque section

- [ ] **Step 1 : Ajouter la section Stats dans `home.blade.php` après le hero**

```blade
{{-- ======= STATS BAND ======= --}}
<section class="relative z-10 bg-bg-secondary border-y border-accent/20 py-8" data-aos="fade-up">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-center items-center gap-8 sm:gap-16">

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-online"
                     data-target="{{ players_online() ?? 0 }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">Joueurs en ligne</div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-accent/20"></div>

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-votes"
                     data-target="{{ monthly_votes() ?? 0 }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">Votes ce mois</div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-accent/20"></div>

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-members"
                     data-target="{{ total_users() ?? 0 }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">Membres</div>
            </div>

        </div>
    </div>
</section>
```

> **Note :** Vérifier les helpers Azuriom exacts pour joueurs en ligne (`players_online()`), votes mensuels (`monthly_votes()`), et total membres (`total_users()`). Ces fonctions peuvent varier selon les plugins installés.

- [ ] **Step 2 : Ajouter la section Shop preview**

```blade
{{-- ======= SHOP PREVIEW ======= --}}
@if(theme_setting('show_section_shop', '1') === '1' && class_exists('\Azuriom\Plugin\Shop\Models\Package'))
<section class="py-24 px-4 max-w-7xl mx-auto" data-aos="fade-up">
    <h2 class="section-title">Boutique</h2>
    <p class="section-subtitle">Soutiens le serveur et obtiens des avantages exclusifs</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach(\Azuriom\Plugin\Shop\Models\Package::with('category')->where('is_enabled', true)->orderBy('position')->take(3)->get() as $package)
        <div class="card-eldoria p-6 group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            @if($package->image)
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                     class="w-full h-40 object-cover rounded-sm mb-4 group-hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-40 bg-bg-primary/50 rounded-sm mb-4 flex items-center justify-center">
                    <span class="text-accent/30 text-4xl font-display">✦</span>
                </div>
            @endif
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $package->name }}</h3>
            <p class="text-text-secondary text-sm mb-4 line-clamp-2">{{ $package->description }}</p>
            <div class="flex items-center justify-between">
                <span class="text-accent font-display font-bold text-lg">{{ $package->formatPrice() }}</span>
                <a href="{{ route('shop.packages.show', $package) }}" class="btn-primary text-xs py-2 px-4">
                    Acheter
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center">
        <a href="{{ route('shop.packages.index') }}" class="btn-primary">
            Voir toute la boutique
        </a>
    </div>
</section>
@endif
```

- [ ] **Step 3 : Ajouter la section Vote (style quêtes)**

```blade
{{-- ======= VOTE ======= --}}
@if(theme_setting('show_section_vote', '1') === '1' && class_exists('\Azuriom\Plugin\Vote\Models\Site'))
<section class="py-24 bg-bg-secondary border-y border-accent/10" data-aos="fade-up">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="section-title">Soutiens-nous</h2>
        <p class="section-subtitle">Vote chaque jour pour nous aider à grandir — chaque vote compte</p>

        <div class="space-y-4">
            @foreach(\Azuriom\Plugin\Vote\Models\Site::where('is_enabled', true)->orderBy('position')->get() as $site)
            <div class="card-eldoria p-4 flex items-center justify-between gap-4" data-aos="fade-right" data-aos-delay="{{ $loop->index * 75 }}">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 flex items-center justify-center text-accent/40 font-display font-bold">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <div class="font-display text-text-primary text-sm font-semibold">{{ $site->name }}</div>
                        <div class="text-text-secondary text-xs">Récompense : {{ $site->vote_command ?? 'Vote pour une récompense' }}</div>
                    </div>
                </div>
                <a href="{{ route('vote.site', $site) }}" target="_blank" rel="noopener"
                   class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[40px]">
                    ✦ Voter
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
```

- [ ] **Step 4 : Ajouter la section Forum preview**

```blade
{{-- ======= FORUM PREVIEW ======= --}}
@if(theme_setting('show_section_forum', '1') === '1' && class_exists('\Azuriom\Plugin\Forum\Models\Post'))
<section class="py-24 px-4 max-w-7xl mx-auto" data-aos="fade-up">
    <h2 class="section-title">Communauté</h2>
    <p class="section-subtitle">Rejoins les discussions, partage tes aventures</p>

    <div class="space-y-3 mb-10">
        @foreach(\Azuriom\Plugin\Forum\Models\Post::with('category', 'author')->latest()->take(3)->get() as $post)
        <a href="{{ route('forum.posts.show', $post) }}"
           class="card-eldoria p-4 flex items-center justify-between gap-4 hover:translate-x-1 transition-transform duration-200 block"
           data-aos="fade-left" data-aos-delay="{{ $loop->index * 75 }}">
            <div class="min-w-0">
                <div class="font-display text-text-primary text-sm font-semibold truncate">{{ $post->name }}</div>
                <div class="text-text-secondary text-xs mt-1">
                    {{ $post->author->name ?? 'Inconnu' }} · {{ $post->created_at->diffForHumans() }}
                </div>
            </div>
            <svg class="w-4 h-4 text-accent/40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endforeach
    </div>

    <div class="text-center">
        <a href="{{ route('forum.posts.index') }}" class="btn-primary">
            Voir le forum
        </a>
    </div>
</section>
@endif
```

- [ ] **Step 5 : Vérifier visuellement**

- Toutes les sections s'affichent dans l'ordre (hero → stats → shop → vote → forum)
- Les sections conditionnelles (`show_section_*`) se masquent quand désactivées
- AOS : scroll reveal fonctionnel sur chaque section
- Les compteurs de stats affichent des valeurs (même 0 si pas de plugin)

- [ ] **Step 6 : Commit**

```bash
git add eldoria/views/home.blade.php
git commit -m "feat(eldoria): page d'accueil complète (stats, shop, vote, forum)"
```

---

## Phase 3 — Customizer

### Task 7 : Drawer customizer front-end

**Files:**
- Create: `eldoria/views/partials/customizer.blade.php`
- Modify: `eldoria/assets/js/app.js` (importer customizer)
- Create: `eldoria/assets/js/customizer.js`

**Interfaces:**
- Consumes: settings Azuriom (Task 2), layout (Task 3)
- Produces: drawer glissant admin-only avec palettes, pickers, toggles, sauvegarde AJAX

- [ ] **Step 1 : Créer `eldoria/assets/js/customizer.js`**

```js
const PALETTES = [
    { name: 'Eldoria',       accent: '#C9A84C', secondary: '#7B3F2E' },
    { name: 'Forêt Sombre',  accent: '#4A7C59', secondary: '#2D4A1E' },
    { name: 'Abysses',       accent: '#3A6EA8', secondary: '#1A3A5C' },
    { name: 'Volcan',        accent: '#C0392B', secondary: '#7D2B1A' },
    { name: 'Givre',         accent: '#7EC8D8', secondary: '#2A5A6E' },
]

export function customizerComponent() {
    return {
        open: false,
        saving: false,
        saved: false,
        activeTab: 'colors',
        accent: document.documentElement.style.getPropertyValue('--color-accent').trim() || '#C9A84C',
        accentSecondary: document.documentElement.style.getPropertyValue('--color-accent-secondary').trim() || '#7B3F2E',
        palettes: PALETTES,

        applyColors(accent, secondary) {
            this.accent = accent
            this.accentSecondary = secondary
            document.documentElement.style.setProperty('--color-accent', accent)
            document.documentElement.style.setProperty('--color-accent-secondary', secondary)
        },

        applyPalette(palette) {
            this.applyColors(palette.accent, palette.secondary)
        },

        async save() {
            this.saving = true
            try {
                const formData = new FormData()
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content)
                formData.append('color_accent', this.accent)
                formData.append('color_accent_secondary', this.accentSecondary)

                // Récupérer les valeurs des toggles de sections
                const toggles = this.$root.querySelectorAll('[data-setting]')
                toggles.forEach(toggle => {
                    formData.append(toggle.dataset.setting, toggle.checked ? '1' : '0')
                })

                // Récupérer le slogan
                const sloganInput = this.$root.querySelector('[data-setting-slogan]')
                if (sloganInput) {
                    formData.append('hero_slogan', sloganInput.value)
                }

                const response = await fetch('/theme/settings', {
                    method: 'POST',
                    body: formData,
                })

                if (!response.ok) throw new Error('Save failed')

                this.saved = true
                setTimeout(() => { this.saved = false }, 3000)
            } catch (e) {
                console.error('Customizer save error:', e)
            } finally {
                this.saving = false
            }
        },

        cancel() {
            // Recharger la page pour revenir à l'état sauvegardé
            window.location.reload()
        }
    }
}
```

> **Note :** La route `/theme/settings` est à vérifier dans la documentation Azuriom. Elle peut être `/admin/theme` ou exposée différemment. Consulter le code source Azuriom pour la route exacte de sauvegarde des settings de thème.

- [ ] **Step 2 : Mettre à jour `eldoria/assets/js/app.js`**

```js
import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { initAnimations } from './animations.js'
import { initParticles } from './particles.js'
import { customizerComponent } from './customizer.js'

window.Alpine = Alpine
Alpine.plugin(persist)
Alpine.data('customizer', customizerComponent)
Alpine.start()

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 700,
        easing: 'ease-out-cubic',
        once: true,
        offset: 60,
    })

    const isMobile = window.innerWidth < 640
    if (!isMobile) {
        initParticles()
    }

    initAnimations()
})
```

- [ ] **Step 3 : Créer `eldoria/views/partials/customizer.blade.php`**

```blade
{{-- Bouton flottant --}}
<button @click="$dispatch('open-customizer')"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-accent text-bg-primary rounded-full
               flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
        title="Personnaliser le thème">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
    </svg>
</button>

{{-- Drawer customizer --}}
<div x-data="customizer()"
     @open-customizer.window="open = true"
     class="fixed inset-0 z-[100]"
     x-show="open"
     x-cloak>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>

    {{-- Panel (desktop: côté droit | mobile: bas) --}}
    <div class="absolute right-0 top-0 bottom-0 w-full sm:w-96 bg-bg-secondary border-l border-accent/20
                flex flex-col overflow-hidden
                sm:right-0 sm:top-0 sm:bottom-0
                max-sm:bottom-0 max-sm:left-0 max-sm:right-0 max-sm:top-auto max-sm:h-[80vh] max-sm:rounded-t-2xl"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full max-sm:translate-x-0 max-sm:translate-y-full"
         x-transition:enter-end="translate-x-0 max-sm:translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 max-sm:translate-y-0"
         x-transition:leave-end="translate-x-full max-sm:translate-x-0 max-sm:translate-y-full">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-accent/20">
            <h3 class="font-display text-accent tracking-widest uppercase text-sm">Personnaliser</h3>
            <button @click="open = false" class="text-text-secondary hover:text-text-primary transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-accent/10">
            <button @click="activeTab = 'colors'"
                    :class="activeTab === 'colors' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                Couleurs
            </button>
            <button @click="activeTab = 'content'"
                    :class="activeTab === 'content' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                Contenu
            </button>
        </div>

        {{-- Body scrollable --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-8">

            {{-- TAB COULEURS --}}
            <div x-show="activeTab === 'colors'" class="space-y-6">

                {{-- Palettes prédéfinies --}}
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-3">Palettes</label>
                    <div class="grid grid-cols-3 gap-3">
                        <template x-for="palette in palettes" :key="palette.name">
                            <button @click="applyPalette(palette)"
                                    class="relative p-3 rounded-sm border border-accent/20 hover:border-accent/60 transition-all text-center">
                                <div class="flex gap-1 justify-center mb-2">
                                    <div class="w-5 h-5 rounded-full border border-white/10"
                                         :style="'background-color: ' + palette.accent"></div>
                                    <div class="w-5 h-5 rounded-full border border-white/10"
                                         :style="'background-color: ' + palette.secondary"></div>
                                </div>
                                <span class="text-text-secondary text-xs" x-text="palette.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Pickers couleur libre --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Accent principal</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="accent" @input="applyColors(accent, accentSecondary)"
                                   class="w-10 h-10 rounded cursor-pointer border border-accent/20 bg-transparent">
                            <span class="text-text-secondary text-sm font-mono" x-text="accent"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Accent secondaire</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="accentSecondary" @input="applyColors(accent, accentSecondary)"
                                   class="w-10 h-10 rounded cursor-pointer border border-accent/20 bg-transparent">
                            <span class="text-text-secondary text-sm font-mono" x-text="accentSecondary"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB CONTENU --}}
            <div x-show="activeTab === 'content'" class="space-y-6">

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Slogan hero</label>
                    <textarea data-setting-slogan
                              class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm
                                     focus:outline-none focus:border-accent/60 resize-none"
                              rows="3"
                              placeholder="Bienvenue dans le royaume de...">{{ theme_setting('hero_slogan', '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-3">Sections visibles</label>
                    <div class="space-y-3">
                        @foreach([
                            ['key' => 'show_section_shop', 'label' => 'Boutique'],
                            ['key' => 'show_section_vote', 'label' => 'Vote'],
                            ['key' => 'show_section_forum', 'label' => 'Forum'],
                        ] as $toggle)
                        <div class="flex items-center justify-between">
                            <span class="text-text-primary text-sm">{{ $toggle['label'] }}</span>
                            <input type="checkbox"
                                   data-setting="{{ $toggle['key'] }}"
                                   {{ theme_setting($toggle['key'], '1') === '1' ? 'checked' : '' }}
                                   class="w-4 h-4 accent-[var(--color-accent)]">
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer actions --}}
        <div class="px-6 py-4 border-t border-accent/20 flex gap-3">
            <button @click="cancel()"
                    class="flex-1 py-2 border border-accent/30 text-text-secondary hover:text-text-primary
                           text-sm font-display tracking-widest uppercase rounded-sm transition-colors">
                Annuler
            </button>
            <button @click="save()"
                    :disabled="saving"
                    class="flex-1 py-2 bg-accent text-bg-primary font-display text-sm tracking-widest uppercase
                           rounded-sm hover:bg-accent/90 transition-all disabled:opacity-50">
                <span x-show="!saving && !saved">Enregistrer</span>
                <span x-show="saving">Sauvegarde...</span>
                <span x-show="saved">✓ Sauvegardé</span>
            </button>
        </div>
    </div>
</div>
```

- [ ] **Step 4 : Tester le customizer**

- Se connecter en tant qu'admin sur l'installation Azuriom de test
- Le bouton flottant doit apparaître en bas à droite
- Cliquer ouvre le drawer (desktop : depuis la droite, mobile : depuis le bas)
- Changer une palette : la couleur d'accent change instantanément sur la page
- Enregistrer : les couleurs persistent après rafraîchissement de la page

- [ ] **Step 5 : Commit**

```bash
git add eldoria/views/partials/customizer.blade.php eldoria/assets/js/customizer.js eldoria/assets/js/app.js
git commit -m "feat(eldoria): customizer live avec palettes, pickers et sauvegarde AJAX"
```

---

## Phase 4 — Animations

### Task 8 : Particles & Animations GSAP

**Files:**
- Create: `eldoria/assets/js/particles.js`
- Create: `eldoria/assets/js/animations.js`
- Create: `eldoria/views/partials/particles.blade.php`

**Interfaces:**
- Consumes: `app.js` (Task 3), hero section (Task 5)
- Produces: particules canvas, parallaxe hero, compteurs animés, pulse bouton

- [ ] **Step 1 : Créer `eldoria/assets/js/particles.js`**

```js
export function initParticles() {
    const canvas = document.getElementById('particles-canvas')
    if (!canvas) return

    const ctx = canvas.getContext('2d')
    let particles = []
    let animationId

    function resize() {
        canvas.width = window.innerWidth
        canvas.height = window.innerHeight
    }

    function getAccentColor() {
        return getComputedStyle(document.documentElement)
            .getPropertyValue('--color-accent').trim() || '#C9A84C'
    }

    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 201, g: 168, b: 76 }
    }

    function createParticle() {
        return {
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 2 + 0.5,
            speedX: (Math.random() - 0.5) * 0.3,
            speedY: -Math.random() * 0.5 - 0.2,
            opacity: Math.random() * 0.5 + 0.1,
            life: 0,
            maxLife: Math.random() * 300 + 200,
        }
    }

    function init() {
        resize()
        particles = Array.from({ length: 60 }, createParticle)
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        const accent = hexToRgb(getAccentColor())

        particles.forEach((p, i) => {
            p.x += p.speedX
            p.y += p.speedY
            p.life++

            const lifeRatio = p.life / p.maxLife
            const currentOpacity = p.opacity * (1 - lifeRatio)

            ctx.beginPath()
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2)
            ctx.fillStyle = `rgba(${accent.r}, ${accent.g}, ${accent.b}, ${currentOpacity})`
            ctx.fill()

            if (p.life >= p.maxLife || p.y < -10) {
                particles[i] = createParticle()
                particles[i].y = canvas.height + 10
            }
        })

        animationId = requestAnimationFrame(draw)
    }

    // Respecter prefers-reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

    init()
    draw()
    window.addEventListener('resize', resize)
}
```

- [ ] **Step 2 : Créer `eldoria/assets/js/animations.js`**

```js
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
const isMobile = window.innerWidth < 640

export function initAnimations() {
    if (prefersReducedMotion) return

    initParallax()
    initCounters()
}

function initParallax() {
    if (isMobile) return

    const heroBg = document.getElementById('hero-bg')
    if (!heroBg) return

    gsap.to(heroBg, {
        yPercent: 30,
        ease: 'none',
        scrollTrigger: {
            trigger: '#hero',
            start: 'top top',
            end: 'bottom top',
            scrub: true,
        }
    })
}

function initCounters() {
    const counters = document.querySelectorAll('[id^="counter-"]')
    if (!counters.length) return

    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target) || 0

        ScrollTrigger.create({
            trigger: counter,
            start: 'top 90%',
            once: true,
            onEnter: () => {
                gsap.fromTo(
                    { val: 0 },
                    { val: target, duration: 1.5, ease: 'power2.out',
                      onUpdate: function() {
                          counter.textContent = Math.round(this.targets()[0].val).toLocaleString()
                      }
                    }
                )
            }
        })
    })
}
```

- [ ] **Step 3 : Créer `eldoria/views/partials/particles.blade.php`**

```blade
@php $isMobile = request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone/i', request()->header('User-Agent')); @endphp
@if(!$isMobile)
<canvas id="particles-canvas"
        class="fixed inset-0 pointer-events-none z-0 opacity-40"
        aria-hidden="true">
</canvas>
@endif
```

- [ ] **Step 4 : Vérifier visuellement**

- Particules dorées flottantes visibles sur desktop (pas sur mobile)
- Scrolling du hero : image de fond se déplace plus lentement que le contenu
- Compteurs de stats s'animent de 0 à la valeur réelle quand on entre dans le viewport
- Tester avec `prefers-reduced-motion: reduce` dans DevTools → rien ne s'anime

- [ ] **Step 5 : Commit**

```bash
git add eldoria/assets/js/particles.js eldoria/assets/js/animations.js eldoria/views/partials/particles.blade.php
git commit -m "feat(eldoria): particles canvas, parallaxe GSAP, compteurs animés"
```

---

## Phase 5 — Pages Plugin Shop

### Task 9 : Vues Shop (catégories, produit, panier, checkout)

**Files:**
- Create: `eldoria/views/vendor/shop/index.blade.php`
- Create: `eldoria/views/vendor/shop/show.blade.php`
- Create: `eldoria/views/vendor/shop/cart.blade.php`
- Create: `eldoria/views/vendor/shop/checkout.blade.php`

**Interfaces:**
- Consumes: layout (Task 3), variables Blade injectées par le plugin Shop Azuriom
- Produces: boutique entièrement rethemée dans l'univers Eldoria

> **Important :** Les variables disponibles dans les vues Shop (ex: `$categories`, `$packages`, `$package`, `$cart`) dépendent du plugin Shop Azuriom. Consulter le code source du plugin Shop à `plugins/shop/src/Http/Controllers/` pour connaître les variables exactement injectées dans chaque vue.

- [ ] **Step 1 : Créer `eldoria/views/vendor/shop/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', trans('shop::shop.shop'))

@section('content')
<div class="pt-24 pb-16">
    {{-- Header page --}}
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Boutique ✦</p>
        <h1 class="section-title">Nos Offres</h1>
        <p class="section-subtitle">Soutiens le serveur et obtiens des avantages exclusifs</p>
    </div>

    <div class="max-w-7xl mx-auto px-4">
        {{-- Filtres catégories --}}
        @if(isset($categories) && $categories->count() > 1)
        <div class="flex flex-wrap gap-3 justify-center mb-12">
            <a href="{{ route('shop.packages.index') }}"
               class="px-4 py-2 text-xs font-display tracking-widest uppercase border border-accent/30
                      hover:border-accent text-text-secondary hover:text-accent transition-all rounded-sm">
                Tout
            </a>
            @foreach($categories as $category)
            <a href="{{ route('shop.categories.show', $category) }}"
               class="px-4 py-2 text-xs font-display tracking-widest uppercase border border-accent/30
                      hover:border-accent text-text-secondary hover:text-accent transition-all rounded-sm">
                {{ $category->name }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Grille de produits --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($packages ?? [] as $package)
            <a href="{{ route('shop.packages.show', $package) }}"
               class="card-eldoria group overflow-hidden" data-aos="fade-up">
                @if($package->image)
                    <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                         class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-44 bg-bg-primary flex items-center justify-center">
                        <span class="text-accent/20 text-5xl font-display">✦</span>
                    </div>
                @endif
                <div class="p-5">
                    <h3 class="font-display text-text-primary font-semibold mb-2 group-hover:text-accent transition-colors">
                        {{ $package->name }}
                    </h3>
                    <p class="text-text-secondary text-sm mb-4 line-clamp-2">{{ strip_tags($package->description) }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-accent font-display font-bold text-xl">{{ $package->formatPrice() }}</span>
                        <span class="text-xs text-text-secondary uppercase tracking-widest font-display">Voir →</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2 : Créer `eldoria/views/vendor/shop/show.blade.php`**

```blade
@extends('layouts.app')

@section('title', $package->name)

@section('content')
<div class="pt-24 pb-16 max-w-5xl mx-auto px-4">

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

        {{-- Image produit --}}
        <div class="card-eldoria overflow-hidden">
            @if($package->image)
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="w-full h-80 object-cover">
            @else
                <div class="w-full h-80 bg-bg-primary flex items-center justify-center">
                    <span class="text-accent/20 text-8xl font-display">✦</span>
                </div>
            @endif
        </div>

        {{-- Infos produit --}}
        <div>
            <p class="text-accent text-xs font-display tracking-widest uppercase mb-2">
                {{ $package->category->name ?? 'Boutique' }}
            </p>
            <h1 class="font-display text-3xl font-bold text-text-primary mb-4">{{ $package->name }}</h1>

            <div class="text-accent font-display text-4xl font-black mb-6">
                {{ $package->formatPrice() }}
            </div>

            <div class="prose prose-invert text-text-secondary text-sm mb-8 max-w-none">
                {!! $package->description !!}
            </div>

            <form action="{{ route('shop.cart.add', $package) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary w-full justify-center py-4 text-base">
                    Ajouter au panier
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 3 : Créer `eldoria/views/vendor/shop/cart.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Panier')

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">
    <h1 class="section-title mt-8 mb-12">Votre Panier</h1>

    @if(isset($cart) && count($cart) > 0)
        <div class="space-y-4 mb-8">
            @foreach($cart as $item)
            <div class="card-eldoria p-4 flex items-center gap-4">
                @if($item->package->image)
                    <img src="{{ $item->package->imageUrl() }}" class="w-16 h-16 object-cover rounded-sm flex-shrink-0">
                @endif
                <div class="flex-1 min-w-0">
                    <div class="font-display text-text-primary font-semibold truncate">{{ $item->package->name }}</div>
                    <div class="text-text-secondary text-sm">Qté : {{ $item->quantity }}</div>
                </div>
                <div class="text-accent font-display font-bold">{{ $item->package->formatPrice() }}</div>
                <form action="{{ route('shop.cart.remove', $item->package) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-text-secondary hover:text-red-400 transition-colors ml-2">✕</button>
                </form>
            </div>
            @endforeach
        </div>

        <div class="card-eldoria p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="font-display text-text-primary">
                Total : <span class="text-accent text-2xl font-black">{{ $total ?? '' }}</span>
            </div>
            <a href="{{ route('shop.checkout') }}" class="btn-primary min-h-[48px]">
                Procéder au paiement
            </a>
        </div>
    @else
        <div class="text-center py-24">
            <div class="text-accent/20 text-8xl font-display mb-6">✦</div>
            <p class="text-text-secondary mb-6">Votre panier est vide.</p>
            <a href="{{ route('shop.packages.index') }}" class="btn-primary">Voir la boutique</a>
        </div>
    @endif
</div>
@endsection
```

- [ ] **Step 4 : Créer `eldoria/views/vendor/shop/checkout.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Paiement')

@section('content')
<div class="pt-24 pb-16 max-w-2xl mx-auto px-4">
    <h1 class="section-title mt-8 mb-12">Paiement</h1>

    <div class="card-eldoria p-8">
        @if(isset($gateways) && count($gateways) > 0)
            <p class="text-text-secondary text-sm mb-6 text-center">Choisissez votre méthode de paiement :</p>
            <div class="grid grid-cols-2 gap-4">
                @foreach($gateways as $gateway)
                <a href="{{ route('shop.checkout.pay', $gateway) }}"
                   class="card-eldoria p-4 text-center hover:border-accent/60 transition-all group">
                    @if($gateway->image)
                        <img src="{{ $gateway->imageUrl() }}" alt="{{ $gateway->name }}" class="h-8 mx-auto mb-2">
                    @endif
                    <div class="font-display text-text-primary text-sm group-hover:text-accent transition-colors">
                        {{ $gateway->name }}
                    </div>
                </a>
                @endforeach
            </div>
        @else
            <p class="text-text-secondary text-center">Aucune méthode de paiement disponible.</p>
        @endif
    </div>
</div>
@endsection
```

- [ ] **Step 5 : Vérifier visuellement**

- Naviguer vers `/shop` sur l'installation de test
- Vérifier la grille de produits, les filtres de catégories
- Cliquer sur un produit → page produit avec image et bouton "Ajouter au panier"
- Panier et checkout : design cohérent avec le thème

- [ ] **Step 6 : Commit**

```bash
git add eldoria/views/vendor/shop/
git commit -m "feat(eldoria): vues plugin Shop (catégories, produit, panier, checkout)"
```

---

## Phase 6 — Pages Plugin Vote

### Task 10 : Vue Vote

**Files:**
- Create: `eldoria/views/vendor/vote/index.blade.php`

**Interfaces:**
- Consumes: layout (Task 3), variables injectées par plugin Vote (`$sites`, `$votes`)
- Produces: page vote avec liste de sites, statut voté/non-voté, barre de progression

> **Important :** Vérifier les variables exactes dans `plugins/vote/src/Http/Controllers/VoteController.php`.

- [ ] **Step 1 : Créer `eldoria/views/vendor/vote/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Voter')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Soutiens-nous ✦</p>
        <h1 class="section-title">Votes</h1>
        <p class="section-subtitle">Chaque vote aide le serveur à grandir — merci pour ton soutien !</p>
    </div>

    <div class="max-w-3xl mx-auto px-4 space-y-6">

        {{-- Barre de progression globale du mois --}}
        @if(isset($monthlyVotes))
        <div class="card-eldoria p-6 mb-8" data-aos="fade-up">
            <div class="flex justify-between items-center mb-3">
                <span class="font-display text-text-primary text-sm tracking-widest uppercase">Votes ce mois</span>
                <span class="text-accent font-display font-bold text-xl">{{ $monthlyVotes }}</span>
            </div>
            <div class="w-full bg-bg-primary rounded-full h-2 overflow-hidden">
                <div class="h-full bg-accent rounded-full transition-all duration-1000 ease-out"
                     id="vote-progress-bar"
                     style="width: 0%"
                     data-target="{{ min(100, ($monthlyVotes / max(1, $monthlyGoal ?? 1000)) * 100) }}">
                </div>
            </div>
            @if(isset($monthlyGoal))
            <p class="text-text-secondary text-xs mt-2 text-right">Objectif : {{ $monthlyGoal }} votes</p>
            @endif
        </div>
        @endif

        {{-- Liste des sites de vote --}}
        @foreach($sites ?? [] as $site)
        @php $hasVoted = isset($votes) && in_array($site->id, $votes); @endphp
        <div class="card-eldoria p-5 flex items-center justify-between gap-4"
             data-aos="fade-right" data-aos-delay="{{ $loop->index * 75 }}">

            <div class="flex items-center gap-4">
                {{-- Numéro de quête --}}
                <div class="w-10 h-10 flex items-center justify-center border border-accent/30 rounded-sm
                            font-display font-bold text-sm {{ $hasVoted ? 'text-accent bg-accent/10 border-accent' : 'text-text-secondary' }}">
                    {{ $hasVoted ? '✓' : $loop->iteration }}
                </div>

                <div>
                    <div class="font-display text-text-primary font-semibold">{{ $site->name }}</div>
                    <div class="text-text-secondary text-xs mt-0.5">
                        @if($hasVoted)
                            <span class="text-accent">Vote effectué aujourd'hui</span>
                        @else
                            Disponible — vote pour une récompense
                        @endif
                    </div>
                </div>
            </div>

            @if($hasVoted)
                <span class="text-accent/60 text-xs font-display uppercase tracking-widest whitespace-nowrap">
                    ✓ Voté
                </span>
            @else
                <a href="{{ route('vote.vote', $site) }}" target="_blank" rel="noopener"
                   class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[44px]">
                    Voter
                </a>
            @endif
        </div>
        @endforeach

        {{-- Top voteurs --}}
        @if(isset($topVoters) && $topVoters->count() > 0)
        <div class="card-eldoria p-6 mt-8" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6">Top Voteurs du Mois</h2>
            <div class="space-y-3">
                @foreach($topVoters->take(5) as $voter)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-display text-text-secondary text-sm w-5">{{ $loop->iteration }}.</span>
                        <span class="text-text-primary text-sm">{{ $voter->user->name ?? 'Inconnu' }}</span>
                    </div>
                    <span class="text-accent font-display font-bold text-sm">{{ $voter->votes }} votes</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('vote-progress-bar')
    if (bar) {
        setTimeout(() => {
            bar.style.width = bar.dataset.target + '%'
        }, 300)
    }
})
</script>
@endpush

@endsection
```

- [ ] **Step 2 : Vérifier visuellement**

- Page `/vote` : liste des sites de vote dans le style quêtes
- Statut voté/non-voté correct
- Barre de progression s'anime au chargement
- Top voteurs affichés si le plugin le fournit

- [ ] **Step 3 : Commit**

```bash
git add eldoria/views/vendor/vote/
git commit -m "feat(eldoria): vue plugin Vote avec barre de progression et top voteurs"
```

---

## Phase 7 — Pages Plugin Forum

### Task 11 : Vues Forum

**Files:**
- Create: `eldoria/views/vendor/forum/index.blade.php`
- Create: `eldoria/views/vendor/forum/show.blade.php`
- Create: `eldoria/views/vendor/forum/topic.blade.php`

**Interfaces:**
- Consumes: layout (Task 3), variables plugin Forum (`$categories`, `$category`, `$posts`, `$post`)
- Produces: forum rethemé, lisible sur mobile

> **Important :** Vérifier les variables exactes dans `plugins/forum/src/Http/Controllers/`. Les noms de variables et routes peuvent différer.

- [ ] **Step 1 : Créer `eldoria/views/vendor/forum/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Forum')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Communauté ✦</p>
        <h1 class="section-title">Forum</h1>
    </div>

    <div class="max-w-4xl mx-auto px-4 space-y-4">
        @foreach($categories ?? [] as $category)
        <a href="{{ route('forum.categories.show', $category) }}"
           class="card-eldoria p-5 flex items-center gap-4 hover:translate-x-1 transition-transform duration-200 block"
           data-aos="fade-up">
            <div class="w-10 h-10 flex items-center justify-center text-accent border border-accent/30 rounded-sm flex-shrink-0 font-display font-bold text-sm">
                {{ $loop->iteration }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-display text-text-primary font-semibold">{{ $category->name }}</div>
                <div class="text-text-secondary text-xs mt-0.5 truncate">{{ $category->description }}</div>
            </div>
            <div class="text-right flex-shrink-0 hidden sm:block">
                <div class="text-accent font-display text-sm font-bold">{{ $category->posts_count ?? 0 }}</div>
                <div class="text-text-secondary text-xs uppercase tracking-widest">Sujets</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
```

- [ ] **Step 2 : Créer `eldoria/views/vendor/forum/show.blade.php`**

```blade
@extends('layouts.app')

@section('title', $category->name ?? 'Forum')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-12 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">Forum</p>
        <h1 class="section-title">{{ $category->name ?? '' }}</h1>
    </div>

    <div class="max-w-4xl mx-auto px-4">

        @auth
        <div class="mb-6 text-right">
            <a href="{{ route('forum.posts.create', ['category' => $category->id ?? '']) }}" class="btn-primary text-sm py-2 px-4">
                + Nouveau sujet
            </a>
        </div>
        @endauth

        <div class="space-y-3">
            @forelse($posts ?? [] as $post)
            <a href="{{ route('forum.posts.show', $post) }}"
               class="card-eldoria p-4 flex items-center gap-4 hover:translate-x-1 transition-transform duration-200 block">
                <div class="flex-1 min-w-0">
                    <div class="font-display text-text-primary font-semibold truncate">{{ $post->name }}</div>
                    <div class="text-text-secondary text-xs mt-1">
                        {{ $post->author->name ?? 'Inconnu' }} · {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="text-right flex-shrink-0 hidden sm:block">
                    <div class="text-accent/70 text-sm font-display">{{ $post->comments_count ?? 0 }}</div>
                    <div class="text-text-secondary text-xs uppercase tracking-widest">Réponses</div>
                </div>
            </a>
            @empty
            <div class="text-center py-12 text-text-secondary">
                Aucun sujet dans cette catégorie.
            </div>
            @endforelse
        </div>

        @if(isset($posts) && method_exists($posts, 'links'))
        <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection
```

- [ ] **Step 3 : Créer `eldoria/views/vendor/forum/topic.blade.php`**

```blade
@extends('layouts.app')

@section('title', $post->name ?? 'Discussion')

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">

    <div class="mt-8 mb-6">
        <p class="text-accent text-xs font-display uppercase tracking-widest mb-2">
            {{ $post->category->name ?? 'Forum' }}
        </p>
        <h1 class="font-display text-2xl md:text-3xl font-bold text-text-primary">{{ $post->name ?? '' }}</h1>
    </div>

    <div class="space-y-4">
        {{-- Post original --}}
        <div class="card-eldoria p-6">
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-accent/10">
                <div class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center font-display text-accent font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($post->author->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div class="font-display text-text-primary text-sm font-semibold">{{ $post->author->name ?? 'Inconnu' }}</div>
                    <div class="text-text-secondary text-xs">{{ $post->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
            <div class="prose prose-invert text-text-secondary text-sm max-w-none">
                {!! $post->content !!}
            </div>
        </div>

        {{-- Commentaires --}}
        @foreach($post->comments ?? [] as $comment)
        <div class="card-eldoria p-6 ml-4">
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-accent/10">
                <div class="w-8 h-8 rounded-full bg-accent/10 flex items-center justify-center font-display text-accent/70 font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr($comment->author->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div class="font-display text-text-primary text-sm font-semibold">{{ $comment->author->name ?? 'Inconnu' }}</div>
                    <div class="text-text-secondary text-xs">{{ $comment->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
            <div class="text-text-secondary text-sm">
                {!! $comment->content !!}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Formulaire de réponse --}}
    @auth
    <div class="card-eldoria p-6 mt-8">
        <h3 class="font-display text-accent text-sm tracking-widest uppercase mb-4">Répondre</h3>
        <form action="{{ route('forum.comments.store', $post) }}" method="POST">
            @csrf
            <textarea name="content"
                      class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                             focus:outline-none focus:border-accent/60 resize-none mb-4"
                      rows="4"
                      placeholder="Votre réponse..." required></textarea>
            <button type="submit" class="btn-primary">Publier</button>
        </form>
    </div>
    @else
    <div class="text-center py-8">
        <a href="{{ route('login') }}" class="btn-primary">Connectez-vous pour répondre</a>
    </div>
    @endauth

</div>
@endsection
```

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/vendor/forum/
git commit -m "feat(eldoria): vues plugin Forum (catégories, sujets, discussion)"
```

---

## Phase 8 — Pages Standards

### Task 12 : Auth, Profil & Erreurs

**Files:**
- Create: `eldoria/views/auth/login.blade.php`
- Create: `eldoria/views/auth/register.blade.php`
- Create: `eldoria/views/profile/index.blade.php`
- Create: `eldoria/views/errors/404.blade.php`
- Create: `eldoria/views/errors/403.blade.php`
- Create: `eldoria/views/errors/500.blade.php`

**Interfaces:**
- Consumes: layout (Task 3)
- Produces: pages auth et erreurs rethemées dans l'univers Eldoria

> **Note :** Les routes et vues auth d'Azuriom sont dans `resources/views/auth/`. Vérifier si le thème peut les overrider via `views/auth/` ou si une autre approche est nécessaire.

- [ ] **Step 1 : Créer `eldoria/views/auth/login.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-24">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Portail ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">Connexion</h1>
        </div>

        <div class="card-eldoria p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">
                        Nom d'utilisateur ou email
                    </label>
                    <input type="text" name="email" value="{{ old('email') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]"
                           placeholder="votre@email.com">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]"
                           placeholder="••••••••">
                    @error('password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-text-secondary cursor-pointer">
                        <input type="checkbox" name="remember" class="accent-[var(--color-accent)]">
                        Se souvenir de moi
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-accent/70 hover:text-accent transition-colors text-xs">
                        Mot de passe oublié ?
                    </a>
                    @endif
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-4 min-h-[48px]">
                    Se connecter
                </button>
            </form>

            <p class="text-center text-text-secondary text-sm mt-6">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="text-accent hover:text-accent/80 transition-colors">S'inscrire</a>
            </p>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2 : Créer `eldoria/views/auth/register.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-24">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Rejoins-nous ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">Créer un compte</h1>
        </div>

        <div class="card-eldoria p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Nom d'utilisateur</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-4 min-h-[48px]">
                    Créer mon compte
                </button>
            </form>

            <p class="text-center text-text-secondary text-sm mt-6">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="text-accent hover:text-accent/80 transition-colors">Se connecter</a>
            </p>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 3 : Créer les pages d'erreur**

`eldoria/views/errors/404.blade.php` :
```blade
@extends('layouts.app')
@section('title', '404 — Page introuvable')
@section('content')
<div class="min-h-screen flex items-center justify-center text-center px-4">
    <div>
        <div class="font-display text-accent text-[8rem] font-black leading-none mb-4 opacity-20">404</div>
        <h1 class="font-display text-3xl text-text-primary mb-4">Cette page n'existe pas</h1>
        <p class="text-text-secondary mb-8">La page que tu cherches s'est perdue dans les brumes du royaume.</p>
        <a href="{{ route('home') }}" class="btn-primary">Retourner à l'accueil</a>
    </div>
</div>
@endsection
```

`eldoria/views/errors/403.blade.php` :
```blade
@extends('layouts.app')
@section('title', '403 — Accès refusé')
@section('content')
<div class="min-h-screen flex items-center justify-center text-center px-4">
    <div>
        <div class="font-display text-accent text-[8rem] font-black leading-none mb-4 opacity-20">403</div>
        <h1 class="font-display text-3xl text-text-primary mb-4">Accès refusé</h1>
        <p class="text-text-secondary mb-8">Tu n'as pas les permissions pour accéder à cette zone.</p>
        <a href="{{ route('home') }}" class="btn-primary">Retourner à l'accueil</a>
    </div>
</div>
@endsection
```

`eldoria/views/errors/500.blade.php` :
```blade
@extends('layouts.app')
@section('title', '500 — Erreur serveur')
@section('content')
<div class="min-h-screen flex items-center justify-center text-center px-4">
    <div>
        <div class="font-display text-accent text-[8rem] font-black leading-none mb-4 opacity-20">500</div>
        <h1 class="font-display text-3xl text-text-primary mb-4">Erreur du serveur</h1>
        <p class="text-text-secondary mb-8">Quelque chose s'est mal passé de notre côté. Réessaie dans quelques instants.</p>
        <a href="{{ route('home') }}" class="btn-primary">Retourner à l'accueil</a>
    </div>
</div>
@endsection
```

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/auth/ eldoria/views/errors/
git commit -m "feat(eldoria): pages auth (login, register) et pages d'erreur"
```

---

## Phase 9 — Polish & Market

### Task 13 : Vérifications finales & market prep

**Files:**
- Modify: `eldoria/theme.json` (métadonnées complètes)

**Interfaces:**
- Consumes: tout le thème précédent
- Produces: thème prêt pour soumission sur market.azuriom.com

- [ ] **Step 1 : Checklist de compatibilité**

Tester sur une installation Azuriom fraîche avec PHP 8.1+ :

```
[ ] Page d'accueil charge sans erreur
[ ] Navbar responsive (desktop et mobile)
[ ] Footer avec IP copiable
[ ] Customizer visible et fonctionnel en tant qu'admin
[ ] Changement de palette → couleurs changent en live
[ ] Sauvegarde des settings → persiste après refresh
[ ] Plugin Shop : catégories, produit, panier, checkout
[ ] Plugin Vote : liste des sites, barre progression
[ ] Plugin Forum : catégories, sujets, discussion, réponse
[ ] Login et Register fonctionnels
[ ] Pages d'erreur 404/403/500
[ ] Mobile (375px) : tout est lisible et utilisable
[ ] Desktop (1920px) : aucun débordement
[ ] prefers-reduced-motion : animations désactivées
[ ] Build Vite sans warnings critiques
```

- [ ] **Step 2 : Mettre à jour `eldoria/theme.json` avec les métadonnées finales**

```json
{
    "id": "eldoria",
    "name": "Eldoria",
    "description": "Thème RPG médiéval premium pour serveurs Minecraft. Customizer live (5 palettes + picker libre), animations immersives, mobile-first. Compatible Shop, Vote, Forum.",
    "version": "1.0.0",
    "url": "https://market.azuriom.com/resources/TODO_ID",
    "authors": ["TonPseudo"],
    "azuriom_api": "1.0",
    "minecraft": true
}
```

- [ ] **Step 3 : Build de production**

```bash
npm run build
```

Vérifier que `assets/dist/` contient des fichiers minifiés.

- [ ] **Step 4 : Préparer les screenshots**

Captures à réaliser pour la page market (dimensions recommandées : 1280×720 minimum) :
1. Page d'accueil desktop — palette Eldoria (or)
2. Page d'accueil mobile — portrait 375px
3. Customizer ouvert avec la palette Volcan (rouge)
4. Page Shop — grille de produits
5. Page Vote — liste style quêtes
6. 2-3 palettes côte à côte (montage)

- [ ] **Step 5 : Commit final**

```bash
git add .
git commit -m "feat(eldoria): v1.0.0 — thème complet, prêt pour soumission market"
```

---

## Notes importantes pour l'implémentation

1. **Helpers Azuriom** : Les fonctions `theme_setting()`, `site_name()`, `navbar_elements()`, `players_online()` sont des helpers Azuriom. Certains peuvent ne pas exister ou avoir un nom différent selon la version. Toujours vérifier dans la documentation officielle ou le code source.

2. **Override des vues plugin** : Le chemin `views/vendor/{plugin-id}/` est la convention standard Laravel pour overrider les vues de packages. Vérifier que c'est bien la convention suivie par Azuriom pour les thèmes.

3. **Route de sauvegarde customizer** : La route AJAX pour sauvegarder les settings de thème doit être vérifiée dans le code source Azuriom avant implémentation.

4. **Image hero par défaut** : Utiliser une image libre de droits (Unsplash, CC0). Ne pas inclure d'images sous copyright dans le thème vendu.
