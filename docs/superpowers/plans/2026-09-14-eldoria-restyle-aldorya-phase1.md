# Eldoria — Reskin visuel "Aldorya" — Phase 1 — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remplacer le langage visuel du thème (gothique sombre parchemin/or → MMORPG cartoon lumineux façon "Aldorya") sur le socle CSS, la navbar, le footer et la page d'accueil, en conservant strictement le mécanisme du customizer et la structure des pages.

**Architecture:** Un socle de couleurs/composants CSS (Task 49) propage automatiquement le nouveau style à la quasi-totalité des vues existantes (elles n'utilisent que `.card-eldoria`/`.btn-primary`/`.section-title`/les 6 CSS custom properties). Seules la navbar, le footer et le hero de l'accueil ont besoin d'ajustements de marquage dédiés, car ils reposent sur des panneaux à fond fixe (pas la couleur de fond de page) nécessitant un texte clair codé en dur, à l'image de la référence.

**Tech Stack:** Tailwind CSS v3 (classes utilitaires + `@layer components`), CSS `color-mix()` (déjà utilisé dans le thème), Alpine.js (customizer, inchangé), Vite (build du thème).

**Spec:** [`docs/superpowers/specs/2026-09-14-eldoria-restyle-aldorya-phase1-design.md`](../specs/2026-09-14-eldoria-restyle-aldorya-phase1-design.md)

## Global Constraints

- Le mécanisme du customizer reste strictement identique : 2 propriétés CSS personnalisables (`--color-accent`, `--color-accent-secondary`), même `config.json`/`config/rules.php`, aucun nouveau champ admin.
- Les 6 CSS custom properties existantes restent les seules variables de couleur du thème — seules leurs valeurs par défaut changent.
- La structure des sections de la page d'accueil reste identique et dans le même ordre (hero → stats → présentation → trailer → actus → boutique → vote → équipe → discord).
- La navbar reste une barre fixe pleine largeur ; le footer reste une grille 3 colonnes — pas de changement de structure/layout.
- Le drag-and-drop de réorganisation des sections (drawer admin) n'est pas touché par ce plan.
- Mobile-first, taille tactile minimale 48px, `prefers-reduced-motion: reduce` respecté pour toute nouvelle transition/animation.
- Aucune nouvelle dépendance JS. Polices (Cinzel/Inter) déjà chargées, aucun changement à l'import Google Fonts.

---

## Carte des fichiers

```
eldoria/
├── assets/css/app.css                  ← MODIFY — nouvelles valeurs de couleurs, nouveaux composants, suppression du grain
├── assets/js/customizer.js             ← MODIFY — nouvelles palettes prédéfinies
├── config.json                         ← MODIFY — nouvelles valeurs par défaut color_accent/color_accent_secondary
├── views/layouts/app.blade.php         ← MODIFY — nouvelles valeurs de fallback theme_config()
├── views/partials/navbar.blade.php     ← MODIFY — panneau bleu, texte clair codé en dur
├── views/partials/footer.blade.php     ← MODIFY — panneau bois, texte clair codé en dur
├── views/home.blade.php                ← MODIFY — section hero uniquement (fond illustré + texte clair)
└── assets/images/hero-aldorya.webp     ← déjà présent (copié pendant la conception, voir Task 51)
```

Aucun autre fichier de vue n'est modifié dans ce plan : toutes les autres sections de l'accueil (stats, présentation, actus, boutique, vote, équipe, discord) n'utilisent que les classes `.card-eldoria`/`.btn-primary`/`.section-title`/`text-accent`/`text-text-secondary`/`bg-bg-secondary` et héritent donc automatiquement du nouveau style dès la Task 49 — vérifié en lisant `eldoria/views/home.blade.php` intégralement pendant la conception (aucune couleur codée en dur en dehors de `bg-green-500` sur le point de statut serveur, qui est une convention universelle "en ligne" indépendante du thème et reste inchangée).

---

### Task 49 : Socle de couleurs et composants

**Files:**
- Modify: `eldoria/assets/css/app.css`
- Modify: `eldoria/assets/js/customizer.js`
- Modify: `eldoria/config.json`
- Modify: `eldoria/views/layouts/app.blade.php`

**Interfaces:**
- Consumes: rien (fondation)
- Produces: les 6 CSS custom properties avec leurs nouvelles valeurs par défaut, les classes `.btn-primary`/`.card-eldoria`/`.section-title` réhabillées — consommées par absolument toutes les vues existantes du thème (aucune modification requise ailleurs pour cette propagation), et par les Tasks 50/51 pour la navbar/footer/hero qui ajoutent des styles complémentaires par-dessus.

- [ ] **Step 1 : Préparer l'environnement de test local**

Ce thème doit être prévisualisé dans l'installation Azuriom locale (`local/azuriom-test`), qui pointe actuellement vers le worktree `v1.1-additions` (travail plugins en cours, à ne pas perturber). Le temps de ce chantier de reskin, re-pointer le lien symbolique vers ce worktree :

```bash
cd local/azuriom-test/resources/themes
rm eldoria
ln -s "/c/Users/mathi/Documents/Eldoria-Template/.claude/worktrees/style-aldorya/eldoria" eldoria
```

C'est un lien symbolique local, non versionné (le `.gitignore` du dossier `resources/themes/` l'exclut déjà, comme le montre le fichier `.gitignore` déjà présent dans ce dossier) — le re-pointer n'affecte aucun commit. Pour revenir tester le travail plugins plus tard, refaire la même manipulation en pointant vers `v1.1-additions/eldoria` à la place.

- [ ] **Step 2 : Remplacer les valeurs de couleur dans `eldoria/assets/css/app.css`**

Remplacer le bloc `:root` actuel :

```css
:root {
    --color-bg-primary: #0F0D0A;
    --color-bg-secondary: #1A1612;
    --color-accent: #C9A84C;
    --color-accent-secondary: #7B3F2E;
    --color-text-primary: #E8DCC8;
    --color-text-secondary: #8A7A62;
}
```

par :

```css
:root {
    --color-bg-primary: #7BC4E8;
    --color-bg-secondary: #FFF4D7;
    --color-accent: #E9A62D;
    --color-accent-secondary: #9D5C38;
    --color-text-primary: #402314;
    --color-text-secondary: #765338;
}
```

- [ ] **Step 3 : Remplacer les composants dans `eldoria/assets/css/app.css`**

Remplacer le bloc `@layer components` actuel :

```css
@layer components {
    .btn-primary {
        @apply inline-flex items-center px-6 py-3 bg-accent text-bg-primary font-display font-semibold
               rounded-sm border border-accent transition-all duration-300
               tracking-wider uppercase text-sm;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .card-eldoria {
        @apply bg-bg-secondary rounded-sm transition-all duration-300;
        border: 1px solid color-mix(in srgb, var(--color-accent) 20%, transparent);
    }

    .card-eldoria:hover {
        border-color: color-mix(in srgb, var(--color-accent) 60%, transparent);
    }

    .section-title {
        @apply font-display text-3xl font-bold text-accent tracking-widest uppercase
               text-center mb-2;
    }

    .section-subtitle {
        @apply text-text-secondary text-center mb-12 tracking-wide;
    }
}
```

par :

```css
@layer components {
    .btn-primary {
        @apply inline-flex items-center justify-center gap-2 px-6 py-3 min-h-[48px]
               font-display font-bold tracking-wider uppercase text-sm rounded-[10px]
               transition-all duration-200;
        color: var(--color-text-primary);
        background: linear-gradient(180deg, var(--color-accent) 0%, color-mix(in srgb, var(--color-accent) 70%, var(--color-accent-secondary) 30%) 100%);
        box-shadow:
            inset 0 -4px color-mix(in srgb, var(--color-accent-secondary) 70%, black 25%),
            0 4px 0 color-mix(in srgb, var(--color-accent-secondary) 50%, black 15%);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        filter: brightness(1.06);
    }

    .btn-primary:active {
        transform: translateY(0);
        filter: brightness(0.97);
    }

    .card-eldoria {
        @apply rounded-xl transition-all duration-300;
        background: color-mix(in srgb, var(--color-bg-secondary) 92%, white 8%);
        border: 3px solid color-mix(in srgb, var(--color-accent) 55%, transparent);
        box-shadow: 0 4px 0 color-mix(in srgb, var(--color-accent-secondary) 35%, transparent);
    }

    .card-eldoria:hover {
        transform: translateY(-2px);
        border-color: var(--color-accent);
    }

    .section-title {
        @apply font-display text-3xl font-bold text-accent tracking-widest uppercase
               text-center mb-2;
    }

    .section-subtitle {
        @apply text-text-secondary text-center mb-12 tracking-wide;
    }
}
```

(`.section-title`/`.section-subtitle` sont recopiés à l'identique : leur couleur suit déjà `var(--color-accent)`/`var(--color-text-secondary)` sans aucun changement de code nécessaire — reproduits ici uniquement pour que le bloc `@layer components` reste complet et cohérent dans le fichier.)

**Respect de `prefers-reduced-motion`** : le thème neutralise déjà les animations sensibles via un bloc `@media (prefers-reduced-motion: reduce)` en fin de fichier (actuellement dédié au hero vidéo). Ajouter le hover de `.btn-primary`/`.card-eldoria` à ce bloc, à la suite du bloc existant :

```css
@media (prefers-reduced-motion: reduce) {
    #hero-video-container {
        display: none !important;
    }

    #hero-bg {
        display: block !important;
    }

    .btn-primary,
    .card-eldoria {
        transition: none !important;
    }

    .btn-primary:hover,
    .card-eldoria:hover {
        transform: none !important;
    }
}
```

- [ ] **Step 4 : Supprimer l'overlay de grain dans `eldoria/assets/css/app.css`**

Supprimer entièrement ce bloc (situé en fin de fichier, après `@layer components`) :

```css
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

- [ ] **Step 5 : Remplacer les palettes prédéfinies dans `eldoria/assets/js/customizer.js`**

Remplacer les lignes 1-7 :

```js
const PALETTES = [
    { name: 'Eldoria',       accent: '#C9A84C', secondary: '#7B3F2E' },
    { name: 'Forêt Sombre',  accent: '#4A7C59', secondary: '#2D4A1E' },
    { name: 'Abysses',       accent: '#3A6EA8', secondary: '#1A3A5C' },
    { name: 'Volcan',        accent: '#C0392B', secondary: '#7D2B1A' },
    { name: 'Givre',         accent: '#7EC8D8', secondary: '#2A5A6E' },
]
```

par :

```js
const PALETTES = [
    { name: 'Eldoria',  accent: '#E9A62D', secondary: '#9D5C38' },
    { name: 'Prairie',  accent: '#6FAF52', secondary: '#3E7A34' },
    { name: 'Océan',    accent: '#3AA0D8', secondary: '#1E6FA8' },
    { name: 'Braise',   accent: '#E2683A', secondary: '#A8431F' },
    { name: 'Givre',    accent: '#7EC8D8', secondary: '#3E7A8A' },
]
```

Le reste du fichier (logique Alpine, sauvegarde, live-preview) n'est pas touché — il lit déjà `PALETTES` par référence.

- [ ] **Step 6 : Remplacer les valeurs par défaut dans `eldoria/config.json`**

Remplacer :

```json
    "color_accent": "#C9A84C",
    "color_accent_secondary": "#7B3F2E",
```

par :

```json
    "color_accent": "#E9A62D",
    "color_accent_secondary": "#9D5C38",
```

(Le reste du fichier — `hero_slogan`, `show_section_shop`, etc. — n'est pas touché.)

- [ ] **Step 7 : Remplacer les valeurs de fallback dans `eldoria/views/layouts/app.blade.php`**

Remplacer :

```blade
    <style>
        :root {
            --color-accent: {{ theme_config('color_accent', '#C9A84C') }};
            --color-accent-secondary: {{ theme_config('color_accent_secondary', '#7B3F2E') }};
        }
    </style>
```

par :

```blade
    <style>
        :root {
            --color-accent: {{ theme_config('color_accent', '#E9A62D') }};
            --color-accent-secondary: {{ theme_config('color_accent_secondary', '#9D5C38') }};
        }
    </style>
```

- [ ] **Step 8 : Build et vérification manuelle**

```bash
cd eldoria && npm run build
```

Depuis `local/azuriom-test`, démarrer le serveur (`php artisan serve`, sur un port libre — vérifier qu'aucune autre instance ne tourne déjà avant, cf. incidents précédents dans ce projet) et visiter la page de connexion (`/login`), qui utilise déjà `.btn-primary`/`.card-eldoria` sans dépendre du reste de ce plan. Confirmer :
- Le bouton de connexion a un dégradé doré avec un effet de bordure basse plus sombre (pas un simple aplati doré) et se soulève légèrement au survol
- La carte du formulaire a une bordure épaisse dorée et un fond parchemin clair (pas sombre)
- Aucun grain/texture de bruit visible sur le fond de page
- Le fond de page est bleu ciel, pas noir

Puis visiter `/` (accueil) et confirmer que les sections stats/présentation/actus/boutique/vote/équipe (celles qui n'utilisent que les classes communes) suivent déjà la nouvelle palette, même si le hero n'est pas encore retouché (Task 51) et la navbar/footer pas encore retouchés (Task 50).

- [ ] **Step 9 : Commit**

```bash
git add eldoria/assets/css/app.css eldoria/assets/js/customizer.js eldoria/config.json eldoria/views/layouts/app.blade.php
git commit -m "feat(eldoria): socle du reskin visuel Aldorya — couleurs et composants"
```

---

### Task 50 : Navbar et footer

**Files:**
- Modify: `eldoria/views/partials/navbar.blade.php`
- Modify: `eldoria/views/partials/footer.blade.php`

**Interfaces:**
- Consumes: `.btn-primary` (Task 49, pour le bouton d'inscription) et les 6 CSS custom properties (Task 49)
- Produces: rien de consommé par une task ultérieure

**Contexte** : contrairement au reste du thème, la navbar et le footer ont des fonds fixes distincts du fond de page (bleu saturé pour la navbar, bois brun pour le footer — même logique que `.portal-nav`/les panneaux bois de la référence). Le texte `--color-text-primary`/`--color-text-secondary` (désormais brun foncé, pensé pour un fond clair) n'y est pas assez contrasté : ces deux fichiers utilisent donc du blanc/quasi-blanc codé en dur pour leur texte, à l'image de la référence — c'est une exception scoping à ces deux panneaux fixes, pas un changement du système de couleurs global.

- [ ] **Step 1 : Remplacer `eldoria/views/partials/navbar.blade.php`**

Remplacer entièrement le fichier :

```blade
<?php
    $navbarElements = \Azuriom\Models\NavbarElement::orderBy('position')->with('roles')->get()
        ->filter(fn ($element) => $element->hasPermission())
        ->whereNull('parent_id');
?>
<header class="fixed top-0 left-0 right-0 z-50 border-b-4"
        style="background: linear-gradient(180deg, color-mix(in srgb, var(--color-accent) 25%, #3AA0D8) 0%, #2B8FCB 100%); border-color: color-mix(in srgb, white 40%, #2B8FCB 60%);"
        x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="font-display font-black text-xl text-white tracking-widest uppercase"
               style="text-shadow: 0 2px 0 rgba(31,91,121,.4);">
                @if(setting('logo'))
                    <img src="{{ site_logo() }}" alt="{{ site_name() }}" class="h-8">
                @else
                    {{ site_name() }}
                @endif
            </a>

            {{-- Navigation desktop --}}
            <nav class="hidden md:flex items-center gap-8">
                @foreach($navbarElements as $element)
                    @if(!$element->isDropdown())
                        <a href="{{ $element->getLink() }}"
                           class="text-white/90 hover:text-accent text-sm tracking-widest uppercase font-bold transition-colors"
                           style="text-shadow: 0 2px 0 rgba(31,91,121,.3);">
                            {{ $element->name }}
                        </a>
                    @endif
                @endforeach
            </nav>

            {{-- Actions desktop --}}
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('profile.index') }}"
                       class="text-white/90 hover:text-accent text-sm font-bold transition-colors">
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white/90 hover:text-accent text-sm font-bold transition-colors">
                            {{ trans('auth.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-white/90 hover:text-accent text-sm tracking-widest uppercase font-bold transition-colors">
                        {{ __('theme::theme.nav.login') }}
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4">
                        {{ __('theme::theme.nav.register') }}
                    </a>
                @endauth
            </div>

            {{-- Burger mobile --}}
            <button @click="open = !open" class="md:hidden p-3 text-white hover:text-accent"
                    aria-label="{{ __('theme::theme.nav.menu') }}">
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
         class="md:hidden border-t-4 px-4 py-6 space-y-4"
         style="background: #2B8FCB; border-color: color-mix(in srgb, white 40%, #2B8FCB 60%);">
        @foreach($navbarElements as $element)
            @if(!$element->isDropdown())
                <a href="{{ $element->getLink() }}"
                   class="block text-white/90 hover:text-accent text-sm tracking-widest uppercase font-bold py-2 transition-colors">
                    {{ $element->name }}
                </a>
            @endif
        @endforeach
        <div class="pt-4 border-t border-white/20 space-y-3">
            @auth
                <a href="{{ route('profile.index') }}" class="block text-white/90 hover:text-accent text-sm font-bold transition-colors">
                    {{ auth()->user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="block w-full text-left text-white/90 hover:text-accent text-sm font-bold transition-colors py-1">
                        {{ trans('auth.logout') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-white/90 hover:text-accent text-sm uppercase tracking-widest font-bold transition-colors">{{ __('theme::theme.nav.login') }}</a>
                <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4 inline-block">{{ __('theme::theme.nav.register') }}</a>
            @endauth
        </div>
    </div>
</header>
```

- [ ] **Step 2 : Remplacer `eldoria/views/partials/footer.blade.php`**

Remplacer entièrement le fichier :

```blade
<?php
    $homeServer = \Azuriom\Models\Server::where('home_display', true)->first();
    $navbarElements = \Azuriom\Models\NavbarElement::orderBy('position')->with('roles')->get()
        ->filter(fn ($element) => $element->hasPermission())
        ->whereNull('parent_id');
?>
<footer class="mt-24 border-t-4"
        style="background: linear-gradient(180deg, color-mix(in srgb, var(--color-accent-secondary) 90%, #b9794c 10%) 0%, var(--color-accent-secondary) 100%); border-color: color-mix(in srgb, white 30%, var(--color-accent-secondary) 70%);">
    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

            {{-- Brand --}}
            <div>
                <h3 class="font-display text-white text-lg tracking-widest uppercase mb-4">
                    {{ site_name() }}
                </h3>
                <p class="text-white/80 text-sm leading-relaxed" data-live="hero_slogan">
                    {{ theme_config('hero_slogan', '') }}
                </p>
                {{-- IP serveur --}}
                @if($homeServer)
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-white/70 text-xs uppercase tracking-wider">{{ __('theme::theme.footer.ip_label') }}</span>
                        <button onclick="navigator.clipboard.writeText('{{ $homeServer->fullAddress() }}')"
                                class="text-accent font-mono text-sm hover:text-white transition-colors"
                                title="{{ __('theme::theme.footer.copy_ip') }}">
                            {{ $homeServer->fullAddress() }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Navigation --}}
            <div>
                <h4 class="font-display text-white text-sm tracking-widest uppercase mb-4">{{ __('theme::theme.footer.navigation') }}</h4>
                <nav class="space-y-2">
                    @foreach($navbarElements as $element)
                        @if(!$element->isDropdown())
                            <a href="{{ $element->getLink() }}"
                               class="block text-white/80 hover:text-accent text-sm transition-colors">
                                {{ $element->name }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Réseaux sociaux --}}
            <div>
                <h4 class="font-display text-white text-sm tracking-widest uppercase mb-4">{{ __('theme::theme.footer.community') }}</h4>
                <div class="flex gap-4">
                    {{-- Toujours rendus (masqués si vides) pour permettre la mise à jour live du customizer --}}
                        <a href="{{ theme_config('footer_discord') ?: '#' }}" target="_blank" rel="noopener"
                           data-live-href="footer_discord"
                           class="text-white/80 hover:text-accent transition-colors {{ theme_config('footer_discord') ? '' : 'hidden' }}"
                           aria-label="Discord">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
                            </svg>
                        </a>
                        <a href="{{ theme_config('footer_twitter') ?: '#' }}" target="_blank" rel="noopener"
                           data-live-href="footer_twitter"
                           class="text-white/80 hover:text-accent transition-colors {{ theme_config('footer_twitter') ? '' : 'hidden' }}"
                           aria-label="Twitter/X">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                </div>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-white/20 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-white/70 text-xs">
                &copy; {{ date('Y') }} {{ site_name() }}. {{ __('theme::theme.footer.rights') }}
            </p>
            <p class="text-white/70 text-xs">
                {{ __('theme::theme.footer.powered_by') }} <a href="https://azuriom.com" class="text-accent hover:text-white transition-colors">Azuriom</a>
                · {{ __('theme::theme.footer.theme_label') }} <span class="text-accent">Eldoria</span>
            </p>
        </div>
    </div>
</footer>
```

- [ ] **Step 3 : Vérification manuelle**

```bash
cd eldoria && npm run build
```

Visiter `/` et confirmer :
- La navbar est une bande bleue saturée pleine largeur (pas transparente/sombre), avec une bordure basse claire, le texte des liens en blanc, le survol passe en doré
- Le footer est un panneau brun/bois pleine largeur avec une bordure haute claire, tout le texte est lisible (blanc/blanc atténué), le lien Azuriom/le nom du thème sont en doré
- Sur mobile (< 768px, redimensionner la fenêtre ou utiliser les outils de dev) : le menu burger s'ouvre toujours correctement, le drawer mobile garde le même fond bleu que la navbar
- Aucune régression sur le lien de copie d'IP serveur (le clic copie toujours l'adresse)

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/partials/navbar.blade.php eldoria/views/partials/footer.blade.php
git commit -m "feat(eldoria): reskin visuel Aldorya — navbar et footer"
```

---

### Task 51 : Page d'accueil — Hero

**Files:**
- Modify: `eldoria/views/home.blade.php` (section hero uniquement, lignes 15-88 de la version actuelle)

**Interfaces:**
- Consumes: `.btn-primary` (Task 49), l'image `eldoria/assets/images/hero-aldorya.webp` (déjà copiée pendant la conception depuis le projet de référence, dont l'utilisateur a confirmé détenir les droits — fichier déjà présent dans le worktree, aucune copie à refaire)
- Produces: rien de consommé par une task ultérieure

**Contexte** : le fond du hero est une illustration pleine page (pas une couleur unie) — comme dans la référence, le texte du hero (eyebrow, titre, sous-titre) reste en blanc/quasi-blanc codé en dur pour rester lisible sur l'image, même si le reste de la page utilise désormais du texte brun foncé sur fond clair. C'est la même exception scoping que la navbar/footer (Task 50), appliquée ici uniquement à la zone hero.

- [ ] **Step 1 : Remplacer l'image de fond par défaut du hero**

Dans `eldoria/views/home.blade.php`, remplacer :

```blade
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat {{ $heroVideoEnabled ? 'hidden' : '' }}" id="hero-bg"
         style="background-image: url('{{ theme_config('hero_image') ?: theme_asset('images/hero-default.svg') }}')">
    </div>
```

par :

```blade
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat {{ $heroVideoEnabled ? 'hidden' : '' }}" id="hero-bg"
         style="background-image: url('{{ theme_config('hero_image') ?: theme_asset('images/hero-aldorya.webp') }}')">
    </div>
```

- [ ] **Step 2 : Ajuster l'overlay dégradé**

Remplacer :

```blade
    {{-- Overlay dégradé --}}
    <div class="absolute inset-0 bg-gradient-to-b from-bg-primary/60 via-bg-primary/40 to-bg-primary"></div>
```

par :

```blade
    {{-- Overlay dégradé : assombrit le bas de l'image pour la lisibilité du texte,
         puis se fond dans le bleu ciel du reste de la page --}}
    <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/5 to-bg-primary"></div>
```

(Un assombrissement noir léger en haut/milieu au lieu de `bg-primary` : `bg-primary` est désormais un bleu ciel clair, l'utiliser en overlay sur toute la hauteur delavait le contraste du texte blanc du hero contre une image lumineuse — un voile noir léger fonctionne quelle que soit l'image de fond, avant de se fondre dans `to-bg-primary` en bas comme actuellement, pour une transition propre vers le reste de la page.)

- [ ] **Step 3 : Passer le texte du hero en blanc et ajuster l'ombre du titre**

Remplacer :

```blade
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto pt-16">
        <p class="text-accent text-sm font-display tracking-[0.4em] uppercase mb-4 opacity-80">
            ✦ {{ __('theme::theme.home.hero_eyebrow') }} ✦
        </p>

        <h1 class="font-display text-5xl md:text-7xl font-black text-text-primary leading-tight mb-6"
            style="text-shadow: 0 2px 30px rgba(0,0,0,0.8)">
            {{ site_name() }}
        </h1>

        <p class="text-text-secondary text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed" data-live="hero_slogan">
            {{ theme_config('hero_slogan', 'Bienvenue dans le royaume. Rejoignez l\'aventure.') }}
        </p>
```

par :

```blade
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto pt-16">
        <p class="text-accent text-sm font-display tracking-[0.4em] uppercase mb-4"
           style="text-shadow: 0 2px 0 rgba(64,35,20,.45);">
            ✦ {{ __('theme::theme.home.hero_eyebrow') }} ✦
        </p>

        <h1 class="font-display text-5xl md:text-7xl font-black text-white leading-tight mb-6"
            style="text-shadow: 0 4px 0 var(--color-accent-secondary), 0 8px 18px rgba(64,35,20,.35);">
            {{ site_name() }}
        </h1>

        <p class="text-white text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed font-semibold"
           style="text-shadow: 0 2px 0 rgba(64,35,20,.3);" data-live="hero_slogan">
            {{ theme_config('hero_slogan', 'Bienvenue dans le royaume. Rejoignez l\'aventure.') }}
        </p>
```

(L'eyebrow reste en `text-accent` doré, qui ressort bien sur l'image et reste cohérent avec le reste du site ; seuls le titre et le sous-titre passent en blanc, à l'image de `.hero-copy h1`/`.hero-copy p` dans la référence.)

- [ ] **Step 4 : Ajuster le bouton d'inscription outline du hero**

Ce bouton (à la différence du bouton "Rejoindre", qui utilise déjà `.btn-primary`) a son propre style outline codé en ligne. Remplacer :

```blade
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center px-6 py-3 min-h-[48px] border border-accent/40
                      text-text-primary font-display text-sm tracking-widest uppercase
                      hover:border-accent hover:text-accent transition-all duration-300 rounded-sm">
                {{ __('theme::theme.home.register') }}
            </a>
```

par :

```blade
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center px-6 py-3 min-h-[48px] border-2 border-white/70
                      text-white font-display text-sm tracking-widest uppercase rounded-[10px]
                      hover:border-accent hover:text-accent transition-all duration-300">
                {{ __('theme::theme.home.register') }}
            </a>
```

- [ ] **Step 5 : Vérification manuelle**

```bash
cd eldoria && npm run build
```

Visiter `/` et confirmer :
- Le fond du hero affiche la nouvelle illustration (paysage), pas l'ancien SVG doré/sombre
- Le titre du site et le slogan sont bien lisibles en blanc sur l'image, avec l'ombre "gravée" visible (pas juste un flou noir)
- Le bouton "Rejoindre" garde son style `.btn-primary` doré 3D (hérité de la Task 49, aucune modification ici)
- Le bouton "S'inscrire" en contour est visible en blanc sur l'image et passe en doré au survol
- Le bas du hero se fond proprement dans le bleu ciel de la section suivante (pas de bande noire ou de coupure nette)
- Sur mobile (largeur réduite), le hero reste lisible et les boutons s'empilent correctement (comportement déjà existant, non modifié par ce plan)

- [ ] **Step 6 : Commit**

```bash
git add eldoria/views/home.blade.php
git commit -m "feat(eldoria): reskin visuel Aldorya — hero de l'accueil"
```

---

### Task 52 : Vérification complète et captures avant/après

**Files:**
- Aucun fichier modifié attendu — vérification uniquement. Si un détail visuel ne suit pas correctement la nouvelle palette (contraste insuffisant, couleur oubliée codée en dur), corriger le fichier concerné et commiter séparément avec un message décrivant le correctif.

**Interfaces:**
- Consumes: le rendu des Tasks 49 à 51

- [ ] **Step 1 : Parcours complet de la page d'accueil**

Visiter `/` de haut en bas et vérifier chaque section listée dans la carte des fichiers (stats, présentation "comment nous rejoindre", trailer si configuré, actus si des articles existent, aperçu boutique si le plugin Shop est installé, aperçu vote si le plugin Vote est installé, équipe si des membres sont configurés, discord si un ID est configuré) : confirmer que chacune affiche la nouvelle palette (fond clair pour les cartes, bordures dorées épaisses, boutons 3D) sans texte illisible ni couleur restée sombre par erreur.

- [ ] **Step 2 : Vérification du customizer**

Se connecter en admin, ouvrir le drawer de personnalisation (bouton "Personnaliser"), confirmer que les 5 nouvelles palettes s'affichent avec les bons noms/couleurs, qu'appliquer une palette différente (ex. "Océan") change bien en live la couleur des boutons/titres/particules sur la page, et que la sauvegarde fonctionne sans erreur.

- [ ] **Step 3 : Vérification des animations existantes**

Confirmer que les particules ambiantes (canvas) sont désormais dorées (elles suivent déjà `--color-accent` sans changement de code, cf. `eldoria/assets/js/particles.js`), que les compteurs animés de la barre de stats s'incrémentent toujours correctement, et qu'aucune animation ne s'exécute lorsque `prefers-reduced-motion: reduce` est activé (émulation navigateur ou réglage OS).

- [ ] **Step 4 : Captures avant/après**

Prendre une capture d'écran de la page d'accueil (desktop + mobile) dans son état actuel, à joindre au rapport de cette task pour que l'utilisateur puisse juger le résultat global avant de décider de poursuivre sur les autres pages/plugins.

- [ ] **Step 5 : Commit (si correctifs)**

Si des correctifs ont été nécessaires à l'étape 1, ils ont déjà été commités individuellement à cette étape. Sinon, aucun commit n'est attendu pour cette task.

---

## Notes pour l'implémentation

1. **Ordre d'exécution strict** : Task 49 doit précéder toutes les autres (fournit les couleurs/composants consommés partout ailleurs). Tasks 50 et 51 sont indépendantes entre elles et peuvent être faites dans n'importe quel ordre, mais toutes deux après la Task 49. Task 52 doit être la dernière.
2. **Aucun test automatisé** n'existe pour ce thème (convention déjà établie) — vérification manuelle uniquement, via l'installation Azuriom locale.
3. **Isolation** : ce plan s'exécute entièrement sur la branche `worktree-style-aldorya`, indépendante du travail plugins (`v1.1-additions`). Ne jamais committer sur cette dernière depuis ce plan.
4. **Symlink local partagé** : `local/azuriom-test/resources/themes/eldoria` est un lien symbolique non versionné, re-pointé en Task 49 Step 1 vers ce worktree. Si une autre session doit tester le travail plugins en parallèle, elle devra re-pointer ce lien vers `v1.1-additions/eldoria` — signaler ce point si un conflit d'usage du même dossier `local/azuriom-test` est détecté pendant l'exécution.
