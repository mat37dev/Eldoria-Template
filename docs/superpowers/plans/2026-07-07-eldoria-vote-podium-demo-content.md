# Eldoria — Podium de vote 3D, polish boutique, style des pages, contenu de démo — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un podium 3D sur la page Vote, améliorer le style des cartes boutique, créer un rendu de thème pour les pages Azuriom personnalisées, et peupler l'installation de test locale avec du contenu de démonstration pour les captures d'écran de la fiche market.

**Architecture:** Le podium réutilise `skinview3d` (déjà en place pour le profil) via un second point d'entrée Vite dédié à la page Vote. Le polish boutique est un ajustement CSS/markup sans changement de données. Le nouveau template `pages/show.blade.php` habille le rendu Azuriom par défaut des pages personnalisées. Le contenu de démo (catégories boutique, articles, liens navbar, page CGU) est créé une seule fois via `php artisan tinker` dans l'installation de test locale — il ne fait **pas** partie du thème livré (un thème Azuriom ne peut pas exécuter de code d'installation), seules les icônes SVG et le code d'affichage le sont.

**Tech Stack:** Laravel/Blade, Tailwind CSS v3, Vite, skinview3d (déjà une dépendance du projet).

## Global Constraints

- Mobile-first : CSS pour `< 640px` en premier, desktop via `min-width`
- `prefers-reduced-motion` : la rotation automatique des skins du podium est désactivée dans ce mode, comme pour le skin du profil
- Toutes les nouvelles chaînes d'interface passent par `theme::theme.*` (FR + EN)
- Le nouveau bundle `vote-podium.js` n'est jamais chargé ailleurs que sur la page Vote (même principe que `profile.js`)
- Le contenu de démo (catégories/packages/articles/navbar/page CGU) est créé uniquement dans la base de données de l'installation de test locale (`local/azuriom-test`), jamais commité dans `eldoria/`
- Les icônes SVG livrées avec le thème utilisent les couleurs par défaut d'Eldoria (`#C9A84C`, `#7B3F2E`) codées en dur dans le fichier — ce sont des fichiers image statiques référencés par une colonne de base de données (`image`), pas du Blade, donc ils ne peuvent pas lire les CSS custom properties du site

---

## Carte des fichiers

```
eldoria/
├── vite.config.js                              ← MODIFY — +point d'entrée "vote-podium"
├── assets/
│   ├── js/vote-podium.js                       ← NEW — init des 3 SkinViewer du podium
│   └── images/
│       ├── skin-placeholder.png                ← NEW — texture noire 64×64 (asset binaire)
│       └── shop/
│           ├── ruby.svg                        ← NEW
│           ├── diamond.svg                     ← NEW
│           ├── crown.svg                       ← NEW
│           └── coin.svg                        ← NEW
├── views/
│   ├── vendor/
│   │   ├── vote/index.blade.php                ← MODIFY — +podium
│   │   └── shop/
│   │       ├── categories/show.blade.php       ← MODIFY — polish visuel
│   │       └── packages/show.blade.php         ← MODIFY — polish visuel
│   └── pages/show.blade.php                    ← NEW — style Eldoria pour les pages Azuriom
└── lang/fr/theme.php, lang/en/theme.php        ← MODIFY — clés vote.podium_title, pages.*
```

---

### Task 34 : Podium 3D + repositionnement du classement (page Vote)

**Files:**
- Modify: `eldoria/vite.config.js`
- Create: `eldoria/assets/js/vote-podium.js`
- Create: `eldoria/assets/images/skin-placeholder.png` (asset binaire, généré via script PHP)
- Modify: `eldoria/views/vendor/vote/index.blade.php`
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes: `$votes` (collection Laravel déjà fournie par le contrôleur Vote réel, clés 1/2/3.../N, chaque entrée a `->user` (modèle `User`, potentiellement `->user->game_id`) et `->votes` (int))
- Produces: `.podium-skin-canvas` (classe CSS lue par `vote-podium.js`), `initVotePodium()` (fonction auto-exécutée sur `DOMContentLoaded`, pas exportée/consommée ailleurs — même principe que `profile.js`)

- [ ] **Step 1 : Générer la texture de repli `eldoria/assets/images/skin-placeholder.png`**

Depuis le dossier `eldoria/`, exécuter :
```bash
php -r "
\$img = imagecreatetruecolor(64, 64);
\$black = imagecolorallocate(\$img, 24, 24, 24);
imagefill(\$img, 0, 0, \$black);
imagepng(\$img, 'assets/images/skin-placeholder.png');
imagedestroy(\$img);
echo 'Généré : assets/images/skin-placeholder.png' . PHP_EOL;
"
```
Attendu : le fichier `eldoria/assets/images/skin-placeholder.png` existe, 64×64, une seule couleur unie foncée (texture Minecraft valide — un skin peut être une image unie, elle affichera simplement un personnage d'une seule couleur).

- [ ] **Step 2 : Ajouter le point d'entrée Vite dans `eldoria/vite.config.js`**

Remplacer :
```js
            input: {
                app: resolve(__dirname, 'assets/js/app.js'),
                style: resolve(__dirname, 'assets/css/app.css'),
                profile: resolve(__dirname, 'assets/js/profile.js'),
            },
```
par :
```js
            input: {
                app: resolve(__dirname, 'assets/js/app.js'),
                style: resolve(__dirname, 'assets/css/app.css'),
                profile: resolve(__dirname, 'assets/js/profile.js'),
                'vote-podium': resolve(__dirname, 'assets/js/vote-podium.js'),
            },
```

- [ ] **Step 3 : Créer `eldoria/assets/js/vote-podium.js`**

```js
import { SkinViewer, IdleAnimation } from 'skinview3d'

function initPodiumViewer(canvas) {
    const viewer = new SkinViewer({
        canvas,
        width: 160,
        height: 220,
        skin: canvas.dataset.skinUrl,
    })

    viewer.controls.enableZoom = false

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) {
        viewer.autoRotate = false
        viewer.animation = null
    } else {
        viewer.autoRotate = true
        viewer.animation = new IdleAnimation()
    }
}

function initVotePodium() {
    document.querySelectorAll('.podium-skin-canvas').forEach(initPodiumViewer)
}

document.addEventListener('DOMContentLoaded', initVotePodium)
```

> Même principe que `assets/js/profile.js` (déjà existant) : point d'entrée Vite autonome, jamais importé par `app.js`, auto-exécuté sur `DOMContentLoaded`. La seule différence est la boucle sur plusieurs canvases au lieu d'un seul.

- [ ] **Step 4 : Ajouter le podium dans `eldoria/views/vendor/vote/index.blade.php`**

Remplacer :
```blade
        {{-- ======= TOP VOTEURS ======= --}}
```
par :
```blade
        {{-- ======= PODIUM DES 3 MEILLEURS VOTANTS ======= --}}
        <?php
            $podiumFallbackSkin = theme_asset('images/skin-placeholder.png');
            $podiumEntries = [
                1 => $votes->get(1),
                2 => $votes->get(2),
                3 => $votes->get(3),
            ];
        ?>
        <div class="card-eldoria p-6 sm:p-8" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-8 text-center">{{ __('theme::theme.vote.podium_title') }}</h2>

            <div class="flex flex-col sm:flex-row sm:items-end justify-center gap-6 sm:gap-8">
                @foreach($podiumEntries as $position => $entry)
                    <div class="flex flex-col items-center {{ $position === 1 ? 'sm:order-2' : ($position === 2 ? 'sm:order-1' : 'sm:order-3') }}">
                        <div class="font-display text-text-primary text-sm font-semibold mb-2 text-center max-w-[140px] truncate">
                            {{ $entry->user->name ?? '—' }}
                        </div>

                        <div class="relative w-32 sm:w-36 aspect-[4/5] bg-bg-primary/40 rounded-sm overflow-hidden border border-accent/20">
                            <canvas class="podium-skin-canvas w-full h-full"
                                    data-skin-url="{{ $entry ? 'https://mc-heads.net/skin/' . ($entry->user->game_id ?? 'c06f8906-4c8a-4911-9c29-ea1dbd1aab82') : $podiumFallbackSkin }}"></canvas>
                            @unless($entry)
                                <span class="absolute inset-0 flex items-center justify-center text-accent/40 font-display text-5xl">?</span>
                            @endunless
                        </div>

                        <div class="mt-3 w-24 sm:w-28 flex items-center justify-center font-display text-2xl font-bold text-bg-primary rounded-t-sm
                                    {{ $position === 1 ? 'h-20 sm:h-24' : ($position === 2 ? 'h-14 sm:h-16' : 'h-10 sm:h-12') }}"
                             style="background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-secondary) 100%)">
                            {{ $position }}
                        </div>

                        @if($entry)
                            <span class="text-accent/70 text-xs font-mono mt-2">{{ $entry->votes }} {{ __('theme::theme.vote.votes_suffix') }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ======= TOP VOTEURS ======= --}}
```

> Le classement top 10 existant (bloc `{{-- ======= TOP VOTEURS ======= --}}` et tout son contenu) n'est **pas modifié** — cette étape insère uniquement le nouveau bloc podium juste avant lui, ce qui le repositionne visuellement en dessous sans toucher à sa logique.

> `c06f8906-4c8a-4911-9c29-ea1dbd1aab82` est l'UUID de repli MHF_Steve déjà utilisé ailleurs dans le thème (page profil) pour un compte sans `game_id` — cohérence déjà établie.

- [ ] **Step 5 : Ajouter la clé de traduction `podium_title`**

Dans `eldoria/lang/fr/theme.php`, remplacer :
```php
        'top_voters_title' => 'Top Voters of the Month',
```
par :
```php
        'podium_title' => 'Le podium du mois',
        'top_voters_title' => 'Top Voters of the Month',
```

> Attention, la clé `top_voters_title` existante contient déjà (par erreur historique) un texte en anglais dans le fichier FR — ne pas la corriger dans le cadre de cette tâche, ce n'est pas son objet ; ajouter uniquement la nouvelle clé `podium_title` juste avant, en français.

Dans `eldoria/lang/en/theme.php`, remplacer :
```php
        'top_voters_title' => 'Top Voters of the Month',
```
par :
```php
        'podium_title' => "This month's podium",
        'top_voters_title' => 'Top Voters of the Month',
```

- [ ] **Step 6 : Ajouter le script du podium dans `eldoria/views/vendor/vote/index.blade.php`**

Remplacer :
```blade
@auth
<script>window.eldoriaVoteUsername = @json(auth()->user()->name);</script>
@endauth

@endsection
```
par :
```blade
@auth
<script>window.eldoriaVoteUsername = @json(auth()->user()->name);</script>
@endauth

@endsection

@push('scripts')
<script type="module" src="{{ theme_asset('dist/vote-podium.js') }}" defer></script>
@endpush
```

- [ ] **Step 7 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Aller sur `/vote` : le podium doit apparaître au-dessus du classement top 10 existant (inchangé), avec 3 socles de hauteurs différentes (1er au centre, plus haut). S'il y a moins de 3 votants ce mois-ci sur l'installation de test, les places vides doivent afficher un personnage noir uni avec un "?" superposé. Vérifier dans l'onglet Réseau que `dist/vote-podium.js` n'est chargé que sur `/vote` (absent de `/` et `/profile`). Émuler `prefers-reduced-motion: reduce` et confirmer que les skins arrêtent de tourner. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 8 : Commit**

```bash
git add eldoria/vite.config.js eldoria/assets/js/vote-podium.js eldoria/assets/images/skin-placeholder.png eldoria/views/vendor/vote/index.blade.php eldoria/lang/fr/theme.php eldoria/lang/en/theme.php
git commit -m "feat(eldoria): podium 3D des 3 meilleurs votants sur la page Vote"
```

---

### Task 35 : Polish visuel des cartes boutique

**Files:**
- Modify: `eldoria/views/vendor/shop/categories/show.blade.php`
- Modify: `eldoria/views/vendor/shop/packages/show.blade.php`

**Interfaces:**
- Aucune interface nouvelle — ajustement CSS/markup pur, aucune donnée/variable nouvelle

- [ ] **Step 1 : Retravailler la carte produit dans `eldoria/views/vendor/shop/categories/show.blade.php`**

Remplacer :
```blade
                @forelse($category->packages as $package)
                    <div class="card-eldoria p-6 group flex flex-col" data-aos="fade-up">
                        @if($package->hasImage())
                            <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                                 class="w-full h-40 object-cover rounded-sm mb-4 group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-40 bg-bg-primary/50 rounded-sm mb-4 flex items-center justify-center">
                                <span class="text-accent/30 text-4xl font-display">✦</span>
                            </div>
                        @endif

                        <h3 class="font-display text-text-primary font-semibold mb-2">{{ $package->name }}</h3>
                        <p class="text-text-secondary text-sm mb-4 line-clamp-2 flex-1">{{ $package->short_description }}</p>

                        <div class="flex items-center justify-between">
                            <span class="text-accent font-display font-bold text-lg">
                                @if($package->isDiscounted())
                                    <del class="text-text-secondary text-sm font-normal">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                                @endif
                                {{ shop_format_amount($package->getPrice()) }}
                            </span>
                            <a href="{{ route('shop.packages.show', $package) }}" class="btn-primary text-xs py-2 px-4">
                                {{ __('theme::theme.shop.view') }}
                            </a>
                        </div>
                    </div>
                @empty
```
par :
```blade
                @forelse($category->packages as $package)
                    <div class="card-eldoria p-6 group flex flex-col hover:-translate-y-1 hover:shadow-lg hover:shadow-accent/10 transition-all duration-300" data-aos="fade-up">
                        <div class="relative w-full h-40 rounded-sm overflow-hidden mb-4">
                            @if($package->hasImage())
                                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-accent/10 to-accent-secondary/10 flex items-center justify-center">
                                    <span class="text-accent/40 text-4xl font-display">✦</span>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-bg-secondary/60 to-transparent pointer-events-none"></div>
                        </div>

                        <h3 class="font-display text-text-primary font-semibold mb-2">{{ $package->name }}</h3>
                        <p class="text-text-secondary text-sm mb-4 line-clamp-2 flex-1">{{ $package->short_description }}</p>

                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-baseline gap-2 px-3 py-1.5 rounded-full bg-accent/10 border border-accent/20">
                                @if($package->isDiscounted())
                                    <del class="text-text-secondary text-xs font-normal">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                                @endif
                                <span class="text-accent font-display font-bold text-lg">{{ shop_format_amount($package->getPrice()) }}</span>
                            </span>
                            <a href="{{ route('shop.packages.show', $package) }}" class="btn-primary text-xs py-2 px-4">
                                {{ __('theme::theme.shop.view') }}
                            </a>
                        </div>
                    </div>
                @empty
```

- [ ] **Step 2 : Retravailler l'image et le prix dans `eldoria/views/vendor/shop/packages/show.blade.php`**

Remplacer :
```blade
    <div class="card-eldoria p-8">
        @if($package->hasImage())
            <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                 class="w-full h-56 object-cover rounded-sm mb-6">
        @endif

        <div class="prose prose-invert text-text-secondary text-sm max-w-none mb-6">
            {!! $package->description !!}
        </div>

        <div class="flex items-center justify-between pt-6 border-t border-accent/10">
            <span class="text-accent font-display font-bold text-2xl">
                @if($package->isDiscounted())
                    <del class="text-text-secondary text-base font-normal block">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                @endif
                {{ shop_format_amount($package->getPrice()) }}
            </span>
```
par :
```blade
    <div class="card-eldoria p-8">
        @if($package->hasImage())
            <div class="w-full h-56 rounded-sm overflow-hidden mb-6 border border-accent/10">
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="prose prose-invert text-text-secondary text-sm max-w-none mb-6">
            {!! $package->description !!}
        </div>

        <div class="flex items-center justify-between pt-6 border-t border-accent/10">
            <span class="inline-flex flex-col items-start gap-1 px-4 py-2 rounded-full bg-accent/10 border border-accent/20">
                @if($package->isDiscounted())
                    <del class="text-text-secondary text-sm font-normal">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                @endif
                <span class="text-accent font-display font-bold text-2xl">{{ shop_format_amount($package->getPrice()) }}</span>
            </span>
```

- [ ] **Step 3 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Aller sur une page de catégorie boutique (`/shop/{category}`) — s'il n'y a pas encore de catégorie créée, ce n'est pas bloquant pour cette tâche (le contenu de démo est créé en Task 38) ; vérifier au minimum que la vue compile sans erreur PHP (`storage/logs/laravel-*.log`) et que le HTML généré contient bien les nouvelles classes (`hover:-translate-y-1`, le pill de prix `rounded-full bg-accent/10`). Si une catégorie/package existe déjà sur l'installation de test, confirmer visuellement : la carte se soulève légèrement au survol, l'image a un léger dégradé sombre en bas, le prix est dans un badge arrondi.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/vendor/shop/categories/show.blade.php eldoria/views/vendor/shop/packages/show.blade.php
git commit -m "feat(eldoria): polish visuel des cartes boutique (survol, image, prix)"
```

---

### Task 36 : Style des pages Azuriom personnalisées

**Files:**
- Create: `eldoria/views/pages/show.blade.php`

**Interfaces:**
- Consumes: `$page` (modèle Azuriom `Page` réel, passé par `Azuriom\Http\Controllers\PageController::show()` — attributs `title`, `description`, `content` déjà confirmés)

- [ ] **Step 1 : Créer `eldoria/views/pages/show.blade.php`**

```blade
@extends('layouts.app')

@section('title', $page->title)
@section('description', $page->description)

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ site_name() }} ✦</p>
        <h1 class="section-title">{{ $page->title }}</h1>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="card-eldoria p-8 prose prose-invert max-w-none
                    prose-headings:font-display prose-headings:text-accent
                    prose-a:text-accent prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-text-primary
                    text-text-secondary text-sm leading-relaxed">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection
```

> `prose`/`prose-invert` viennent du plugin Tailwind Typography — déjà utilisé ailleurs dans le thème (`packages/show.blade.php`) pour afficher du HTML libre de façon lisible sans que l'admin ait à écrire ses propres classes Tailwind. Les variantes `prose-headings:*`/`prose-a:*`/`prose-strong:*` recolorent uniquement les éléments générés par le HTML de la page (titres, liens, gras) pour rester cohérents avec la charte du thème plutôt que le gris par défaut du plugin.

- [ ] **Step 2 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Créer une page de test via l'admin Azuriom (Contenu → Pages → Ajouter, titre "Test", contenu avec un titre `<h2>`, un paragraphe, une liste et un lien), puis la visiter. Confirmer : l'en-tête façon Hero (eyebrow + titre) s'affiche, le contenu est dans une carte cohérente avec le reste du thème, les titres/liens à l'intérieur du contenu HTML libre sont bien colorés en doré plutôt qu'en gris par défaut. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 3 : Commit**

```bash
git add eldoria/views/pages/show.blade.php
git commit -m "feat(eldoria): style Eldoria pour les pages Azuriom personnalisées (CGU etc.)"
```

---

### Task 37 : Icônes SVG boutique

**Files:**
- Create: `eldoria/assets/images/shop/ruby.svg`
- Create: `eldoria/assets/images/shop/diamond.svg`
- Create: `eldoria/assets/images/shop/crown.svg`
- Create: `eldoria/assets/images/shop/coin.svg`

**Interfaces:**
- Produces: 4 fichiers image statiques, référencés par leur chemin (`eldoria/assets/images/shop/{nom}.svg`) — consommés par la Task 38 pour peupler les packages de démo

- [ ] **Step 1 : Créer `eldoria/assets/images/shop/ruby.svg`**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <polygon points="32,4 52,22 44,58 20,58 12,22" fill="#7B3F2E"/>
  <polygon points="32,4 52,22 32,30 12,22" fill="#C9A84C"/>
  <polygon points="20,58 32,30 44,58" fill="#5A2E20"/>
  <polygon points="12,22 32,30 20,58" fill="#9C5A44"/>
  <polygon points="52,22 32,30 44,58" fill="#9C5A44"/>
</svg>
```

- [ ] **Step 2 : Créer `eldoria/assets/images/shop/diamond.svg`**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <polygon points="32,4 50,20 32,60 14,20" fill="#C9A84C"/>
  <polygon points="32,4 50,20 32,28 14,20" fill="#E8DCC8"/>
  <polygon points="14,20 32,28 32,60" fill="#B08F3E"/>
  <polygon points="50,20 32,28 32,60" fill="#D4B860"/>
</svg>
```

- [ ] **Step 3 : Créer `eldoria/assets/images/shop/crown.svg`**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <path d="M8 46 L14 20 L26 34 L32 14 L38 34 L50 20 L56 46 Z" fill="#C9A84C"/>
  <rect x="8" y="46" width="48" height="8" rx="2" fill="#7B3F2E"/>
  <circle cx="14" cy="18" r="4" fill="#C9A84C"/>
  <circle cx="32" cy="12" r="4" fill="#C9A84C"/>
  <circle cx="50" cy="18" r="4" fill="#C9A84C"/>
</svg>
```

- [ ] **Step 4 : Créer `eldoria/assets/images/shop/coin.svg`**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <circle cx="32" cy="32" r="28" fill="#C9A84C"/>
  <circle cx="32" cy="32" r="28" fill="none" stroke="#7B3F2E" stroke-width="3"/>
  <circle cx="32" cy="32" r="20" fill="none" stroke="#7B3F2E" stroke-width="2"/>
  <text x="32" y="40" font-family="serif" font-size="24" font-weight="bold" fill="#7B3F2E" text-anchor="middle">E</text>
</svg>
```

- [ ] **Step 5 : Vérification**

Ouvrir chacun des 4 fichiers dans un navigateur (ou via un visualiseur d'image) pour confirmer qu'ils s'affichent correctement (pas de XML malformé, formes bien visibles, couleurs cohérentes avec la palette dorée/rouille du thème).

- [ ] **Step 6 : Commit**

```bash
git add eldoria/assets/images/shop/
git commit -m "feat(eldoria): icônes SVG boutique (rubis, diamant, couronne, pièce)"
```

---

### Task 38 : Traductions finales + contenu de démo (base de test locale) + revue finale

**Files:**
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes : les 4 icônes SVG de la Task 37, le template `pages/show.blade.php` de la Task 36, le podium de la Task 34, le polish boutique de la Task 35

- [ ] **Step 1 : Vérifier qu'aucune clé i18n n'est manquante**

```bash
cd eldoria && grep -n "podium_title" lang/fr/theme.php lang/en/theme.php
```
Attendu : la clé `podium_title` (ajoutée en Task 34) est présente dans les deux fichiers. Aucune autre nouvelle clé n'a été introduite par les Tasks 35-37 (polish CSS pur, template de page utilisant `$page->title`/`$page->content` sans nouvelle chaîne d'interface, fichiers SVG sans texte). Si une clé manquante est trouvée, l'ajouter dans la section appropriée du fichier, dans le même style que les clés voisines.

- [ ] **Step 2 : Créer le contenu de démo dans l'installation de test locale**

Depuis `local/azuriom-test`, exécuter (installe les catégories/packages boutique, les articles, les liens navbar, et une page CGU — **uniquement dans cette base de données locale**, rien de ceci n'est commité dans `eldoria/`) :

```bash
php artisan tinker --execute="
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Models\Post;
use Azuriom\Models\NavbarElement;
use Azuriom\Models\Page;
use Illuminate\Support\Facades\Storage;

// --- Icônes boutique : copier les SVG du thème vers le disque public 'packages' ---
\$themeIconsPath = base_path('resources/themes/eldoria/assets/images/shop');
foreach (['ruby.svg', 'diamond.svg', 'crown.svg', 'coin.svg'] as \$icon) {
    Storage::disk('public')->put('packages/'.\$icon, file_get_contents(\$themeIconsPath.'/'.\$icon));
}

// --- Boutique : catégorie Monnaie ---
\$monnaie = Category::create(['name' => 'Monnaie', 'slug' => 'monnaie', 'description' => '', 'position' => 1, 'is_enabled' => true]);
Package::create(['category_id' => \$monnaie->id, 'name' => '500 Rubis', 'short_description' => 'Un pack de départ pour bien commencer.', 'image' => 'ruby.svg', 'price' => 5, 'position' => 1, 'is_enabled' => true]);
Package::create(['category_id' => \$monnaie->id, 'name' => '1200 Rubis', 'short_description' => 'Notre pack le plus populaire.', 'image' => 'ruby.svg', 'price' => 10, 'position' => 2, 'is_enabled' => true]);
Package::create(['category_id' => \$monnaie->id, 'name' => '3000 Rubis', 'short_description' => 'Le plus gros pack, pour les vrais aventuriers.', 'image' => 'diamond.svg', 'price' => 20, 'position' => 3, 'is_enabled' => true]);

// --- Boutique : catégorie Rangs ---
\$rangs = Category::create(['name' => 'Rangs', 'slug' => 'rangs', 'description' => '', 'position' => 2, 'is_enabled' => true]);
Package::create(['category_id' => \$rangs->id, 'name' => 'VIP', 'short_description' => 'Accès à des commandes exclusives et un préfixe doré.', 'image' => 'crown.svg', 'price' => 8, 'position' => 1, 'is_enabled' => true]);
Package::create(['category_id' => \$rangs->id, 'name' => 'VIP+', 'short_description' => 'Tous les avantages VIP, plus des kits hebdomadaires.', 'image' => 'crown.svg', 'price' => 15, 'position' => 2, 'is_enabled' => true]);
Package::create(['category_id' => \$rangs->id, 'name' => 'Légende', 'short_description' => 'Le rang ultime : tous les avantages, aucune limite.', 'image' => 'crown.svg', 'price' => 30, 'position' => 3, 'is_enabled' => true]);

// --- Articles ---
\$admin = \Azuriom\Models\User::where('email', 'admin@eldoria.test')->first();
Post::create(['title' => 'Bienvenue sur Eldoria', 'slug' => 'bienvenue-sur-eldoria', 'description' => 'Le serveur ouvre officiellement ses portes.', 'content' => '<p>Bienvenue aventuriers ! Le royaume d\'Eldoria vous ouvre ses portes. Rejoignez-nous dès maintenant pour commencer votre aventure.</p>', 'author_id' => \$admin->id, 'published_at' => now()]);
Post::create(['title' => 'Mise à jour 1.21', 'slug' => 'mise-a-jour-1-21', 'description' => 'Le serveur est maintenant à jour.', 'content' => '<p>Le serveur a été mis à jour vers la version 1.21. De nouvelles fonctionnalités vous attendent !</p>', 'author_id' => \$admin->id, 'published_at' => now()]);

// --- Navbar ---
NavbarElement::create(['name' => 'Boutique', 'type' => 'plugin', 'value' => 'shop.home', 'position' => 1]);
NavbarElement::create(['name' => 'Vote', 'type' => 'plugin', 'value' => 'vote.home', 'position' => 2]);
NavbarElement::create(['name' => 'Actus', 'type' => 'posts', 'value' => '', 'position' => 3]);

// --- Page CGU de démo ---
Page::create(['title' => 'Conditions générales', 'slug' => 'cgu', 'description' => 'Conditions générales d\'utilisation du serveur.', 'content' => '<h2>1. Acceptation des règles</h2><p>En rejoignant ce serveur, vous acceptez de respecter le règlement en vigueur.</p><h2>2. Comportement</h2><p>Tout comportement toxique, triche ou usage de logiciels non autorisés entraînera une sanction.</p><ul><li>Respect entre joueurs</li><li>Pas de triche</li><li>Pas de spam</li></ul><p>Pour toute question, contactez un membre du <a href=\"#\">staff</a>.</p>', 'is_enabled' => true]);

echo 'Contenu de démo créé.' . PHP_EOL;
"
```

> Adapter `base_path('resources/themes/eldoria/...')` si le chemin réel du thème symlinké diffère sur la machine d'exécution (vérifier via `ls resources/themes/eldoria` avant de lancer le script si un doute existe).

- [ ] **Step 3 : Vérification manuelle complète**

```bash
cd ../local/azuriom-test && php artisan view:clear
```

Visiter dans l'ordre :
- `/` : la navbar doit maintenant afficher Boutique / Vote / Actus.
- `/shop` puis une catégorie : les 2 catégories et leurs 3 articles chacune doivent s'afficher avec leurs icônes SVG, prix, et le nouveau style de carte (Task 35).
- `/posts` (ou la route Actus) : les 2 articles de démo doivent apparaître.
- `/vote` : podium + classement doivent maintenant afficher au moins un votant si un vote de test a été enregistré (sinon les 3 places du podium doivent afficher le repli noir avec "?" — comportement normal, pas un bug).
- `/pages/cgu` (ou l'URL générée par `route('pages.show', 'cgu')`) : la page CGU doit s'afficher avec le nouveau style (Task 36).

Vérifier `storage/logs/laravel-*.log` à chaque étape. Confirmer qu'aucune clé `theme::theme.*` brute n'apparaît nulle part.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/lang/
git commit -m "docs(eldoria): vérification finale i18n podium vote + contenu de démo local"
```

> Si le Step 1 n'a trouvé aucune clé manquante, ce commit peut être vide de changement — dans ce cas, ne rien commiter et le signaler dans le rapport (pas de commit vide).

---

## Notes pour l'implémentation

1. **Ordre d'exécution** : Task 34 (podium) et Task 35 (polish boutique) sont indépendantes entre elles. Task 36 (style des pages) est indépendante des deux premières. Task 37 (icônes SVG) doit précéder la Task 38 (le script de contenu de démo copie ces fichiers). Task 38 doit être la dernière (elle vérifie l'ensemble et crée le contenu qui donne à voir le résultat final des 4 tâches précédentes).
2. **Aucun test automatisé** n'existe pour ce thème Blade/JS (convention déjà établie sur tout le projet) — la vérification se fait uniquement par build + test manuel décrits dans chaque tâche.
3. **Le contenu de démo (Task 38 Step 2) n'est jamais commité dans `eldoria/`** — seul le script (documenté dans ce plan) et son exécution locale comptent ; la base de données de `local/azuriom-test` n'est pas versionnée avec le thème.
