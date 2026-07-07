# Eldoria — Support du plugin Wiki — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Habiller le plugin officiel Azuriom Wiki aux couleurs du thème (grille de catégories, page d'article avec sidebar, recherche), et peupler l'installation de test locale avec du contenu de démo.

**Architecture:** 4 fichiers de vue à surcharger (`views/vendor/wiki/...`), navigation classique (liens réels, pas de bascule JS type SPA — simplification actée en spec, voir §2), réutilisation de la vue de pagination Tailwind native de Laravel (`pagination::tailwind`) pour la recherche.

**Tech Stack:** Laravel/Blade, Tailwind CSS v3 (+ plugin Typography déjà installé).

## Global Constraints

- Mobile-first : la sidebar de la page d'article s'empile au-dessus du contenu sur mobile (`< 640px`), passe en grille 2 colonnes (1 sidebar + 3 contenu) à partir de `lg:`
- Taille tactile minimale des boutons/liens : 48px
- Toutes les nouvelles chaînes d'interface passent par `theme::theme.wiki.*` (FR + EN), sauf les messages déjà fournis par le plugin (`wiki::messages.title`, `.back`, `.search.results`, `.search.empty`), réutilisés tels quels
- Le champ `icon` (Bootstrap Icons) des catégories est ignoré — une icône SVG Eldoria unique (parchemin) est utilisée pour toutes les catégories, pas de nouvelle dépendance de police d'icônes
- Pas de nouvelle dépendance JS (navigation classique, pas de bascule type SPA)
- Le contenu de démo (lien navbar + catégories/pages) est créé uniquement dans la base de données de l'installation de test locale (`local/azuriom-test`), jamais commité dans `eldoria/`

---

## Carte des fichiers

```
eldoria/
├── views/vendor/wiki/
│   ├── partials/_header.blade.php   ← NEW — en-tête + recherche, partagé par les 3 autres vues
│   ├── categories/index.blade.php   ← NEW — grille de catégories
│   └── pages/
│       ├── show.blade.php           ← NEW — page d'article + sidebar
│       └── search.blade.php         ← NEW — résultats de recherche paginés
└── lang/fr/theme.php, lang/en/theme.php ← MODIFY — clés wiki.*
```

---

### Task 41 : En-tête partagé + grille de catégories

**Files:**
- Create: `eldoria/views/vendor/wiki/partials/_header.blade.php`
- Create: `eldoria/views/vendor/wiki/categories/index.blade.php`
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes: `$title` (string), `$search` (string, optionnel — valeur courante du champ de recherche) — variables attendues par le partial `_header`, passées explicitement par chaque vue qui l'inclut
- Produces: le partial theme `eldoria/views/vendor/wiki/partials/_header.blade.php`, inclus via `@include('wiki::partials._header', [...])` (nom de vue Azuriom résolu par le plugin, surchargé par ce fichier) — consommé par `categories/index.blade.php` (même task) et par les deux vues de la Task 42

- [ ] **Step 1 : Créer `eldoria/views/vendor/wiki/partials/_header.blade.php`**

```blade
<div class="text-center py-16 px-4">
    <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.wiki.eyebrow') }} ✦</p>
    <h1 class="section-title">{{ $title }}</h1>

    <form action="{{ route('wiki.search') }}" method="GET" role="search" class="max-w-md mx-auto mt-8 flex gap-2">
        <input type="search" name="q" value="{{ $search ?? '' }}" required
               placeholder="{{ __('theme::theme.wiki.search_placeholder') }}"
               class="flex-1 bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]
                      focus:outline-none focus:border-accent/60">
        <button type="submit" class="btn-primary min-h-[48px] px-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>
    </form>
</div>
```

- [ ] **Step 2 : Créer `eldoria/views/vendor/wiki/categories/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', trans('wiki::messages.title'))

@section('content')
<div class="pt-24 pb-16">
    @include('wiki::partials._header', ['title' => trans('wiki::messages.title')])

    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $category)
                <a href="{{ route('wiki.show', $category) }}"
                   class="card-eldoria p-6 flex flex-col items-center text-center gap-3 hover:-translate-y-1 transition-transform duration-300 min-h-[48px]">
                    <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                    <h2 class="font-display text-text-primary font-semibold">{{ $category->name }}</h2>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
```

> `$category->icon` (classe Bootstrap Icons) n'est délibérément pas utilisé — voir la contrainte globale sur les icônes. L'icône SVG (parchemin) est fixe, identique pour toutes les catégories.

- [ ] **Step 3 : Ajouter les clés de traduction dans `eldoria/lang/fr/theme.php`**

Remplacer :
```php
    'faq' => [
        'eyebrow' => 'Aide',
        'title' => 'Foire aux questions',
    ],

    'auth' => [
```
par :
```php
    'faq' => [
        'eyebrow' => 'Aide',
        'title' => 'Foire aux questions',
    ],

    'wiki' => [
        'eyebrow' => 'Documentation',
        'search_placeholder' => 'Rechercher dans le wiki...',
    ],

    'auth' => [
```

- [ ] **Step 4 : Ajouter les clés de traduction dans `eldoria/lang/en/theme.php`**

Remplacer :
```php
    'faq' => [
        'eyebrow' => 'Help',
        'title' => 'Frequently Asked Questions',
    ],

    'auth' => [
```
par :
```php
    'faq' => [
        'eyebrow' => 'Help',
        'title' => 'Frequently Asked Questions',
    ],

    'wiki' => [
        'eyebrow' => 'Documentation',
        'search_placeholder' => 'Search the wiki...',
    ],

    'auth' => [
```

- [ ] **Step 5 : Installer le plugin Wiki dans l'installation de test locale (si pas déjà fait)**

Depuis `local/azuriom-test` :
```bash
ls plugins/ | grep wiki
```
S'il est absent, l'installer depuis la source déjà clonée pendant la conception (`local/azuriom-plugin-wiki`) :
```bash
cp -r ../azuriom-plugin-wiki plugins/wiki
php artisan migrate
```
Vérifier que le plugin apparaît activé dans l'admin avant de continuer.

- [ ] **Step 6 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Créer 1 catégorie de test via l'admin Azuriom (ou `php artisan tinker` avec `\Azuriom\Plugin\Wiki\Models\Category::create(['name' => 'Test', 'slug' => 'test', 'is_enabled' => true, 'position' => 0])`), puis visiter `/wiki` (confirmer la route réelle via `php artisan route:list | grep wiki`). Confirmer : en-tête façon Hero avec champ de recherche, la catégorie de test s'affiche en carte avec l'icône parchemin. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 7 : Commit**

```bash
git add eldoria/views/vendor/wiki/partials/_header.blade.php eldoria/views/vendor/wiki/categories/index.blade.php eldoria/lang/fr/theme.php eldoria/lang/en/theme.php
git commit -m "feat(eldoria): support du plugin Wiki — en-tête et grille de catégories"
```

---

### Task 42 : Page d'article + recherche

**Files:**
- Create: `eldoria/views/vendor/wiki/pages/show.blade.php`
- Create: `eldoria/views/vendor/wiki/pages/search.blade.php`

**Interfaces:**
- Consumes: le partial `wiki::partials._header` (Task 41) ; `$page` (modèle `Azuriom\Plugin\Wiki\Models\Page`, avec `$page->category->categories` [sous-catégories activées] et `$page->category->pages` [pages sœurs, triées par position] déjà chargées par le contrôleur réel) pour `pages/show.blade.php` ; `$pages` (collection paginée de `Page`, `category` déjà chargée) et `$search` (string) pour `pages/search.blade.php`

- [ ] **Step 1 : Créer `eldoria/views/vendor/wiki/pages/show.blade.php`**

```blade
@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="pt-24 pb-16">
    @include('wiki::partials._header', ['title' => $page->category->name])

    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1 space-y-2">
            @if(! $page->category->categories->isEmpty())
                @foreach($page->category->categories as $subCategory)
                    @can('view', $subCategory)
                        <a href="{{ route('wiki.show', $subCategory) }}"
                           class="card-eldoria p-4 flex items-center gap-2 text-text-secondary hover:text-accent text-sm min-h-[48px]">
                            {{ $subCategory->name }}
                        </a>
                    @endcan
                @endforeach
            @endif

            @foreach($page->category->pages as $catPage)
                <a href="{{ route('wiki.pages.show', [$page->category, $catPage]) }}"
                   class="card-eldoria p-4 flex items-center gap-2 text-sm min-h-[48px] {{ $page->is($catPage) ? 'border-accent text-accent' : 'text-text-secondary hover:text-accent' }}">
                    {{ $catPage->title }}
                </a>
            @endforeach

            <a href="{{ $page->category->parent !== null ? route('wiki.show', $page->category->parent) : route('wiki.index') }}"
               class="flex items-center gap-2 text-text-secondary hover:text-accent text-sm min-h-[48px] px-4">
                ← {{ trans('wiki::messages.back') }}
            </a>
        </div>

        <div class="lg:col-span-3">
            <div class="card-eldoria p-8 prose prose-invert prose-headings:font-display prose-headings:text-accent prose-a:text-accent max-w-none">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</div>
@endsection
```

> Pas de bascule JS entre pages sœurs (simplification actée en spec §2) : chaque lien de la sidebar est une vraie route Laravel, la navigation se fait par rechargement de page classique. `$page->is($catPage)` (méthode native Eloquent de comparaison de modèles) détermine la mise en évidence de la page active dans la sidebar.

- [ ] **Step 2 : Créer `eldoria/views/vendor/wiki/pages/search.blade.php`**

```blade
@extends('layouts.app')

@section('title', trans('wiki::messages.search.results'))

@section('content')
<div class="pt-24 pb-16">
    @include('wiki::partials._header', ['title' => trans('wiki::messages.search.results'), 'search' => $search])

    <div class="max-w-3xl mx-auto px-4 space-y-4">
        @forelse($pages as $page)
            @can('view', $page->category)
                <div class="card-eldoria p-6">
                    <h2 class="font-display text-text-primary font-semibold mb-2">
                        <a href="{{ route('wiki.pages.show', [$page->category, $page]) }}" class="hover:text-accent transition-colors">
                            {{ $page->title }}
                        </a>
                    </h2>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-accent/10 border border-accent/20 text-accent mb-3">
                        {{ $page->category->name }}
                    </span>
                    <p class="text-text-secondary text-sm">{{ \Illuminate\Support\Str::limit(strip_tags($page->content), 300) }}</p>
                </div>
            @endcan
        @empty
            <p class="text-text-secondary text-sm text-center">{{ trans('wiki::messages.search.empty') }}</p>
        @endforelse

        <div class="pt-4">
            {{ $pages->withQueryString()->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
```

> `pagination::tailwind` est une vue de pagination fournie nativement par le framework Laravel (`illuminate/pagination`), déjà cohérente visuellement avec un thème Tailwind — aucune vue de pagination custom à créer. `\Illuminate\Support\Str::limit` est utilisé en forme pleinement qualifiée, cohérent avec la convention déjà en place dans ce thème (pas d'import `use` dans les fichiers Blade).

- [ ] **Step 3 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Sur la catégorie de test créée en Task 41, ajouter 2 pages via l'admin (ou `php artisan tinker` avec `\Azuriom\Plugin\Wiki\Models\Page::create(['title' => '...', 'slug' => '...', 'content' => '<p>...</p>', 'category_id' => $category->id, 'position' => 0])`). Visiter une page : confirmer la sidebar (pages sœurs, page active mise en évidence), le contenu dans une `card-eldoria` avec typographie stylée. Cliquer sur l'autre page sœur : navigation classique (rechargement), la sidebar reflète bien la nouvelle page active. Tester la recherche (`/wiki/search?q=...` ou le formulaire de l'en-tête) avec un terme présent dans une page : le résultat doit apparaître avec extrait et badge de catégorie. Tester une recherche sans résultat : le message vide du plugin doit s'afficher. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/vendor/wiki/pages/show.blade.php eldoria/views/vendor/wiki/pages/search.blade.php
git commit -m "feat(eldoria): support du plugin Wiki — page d'article et recherche"
```

---

### Task 43 : Contenu de démo (base de test locale) + revue finale

**Files:**
- Aucun fichier du thème modifié — cette tâche ne touche que la base de données de l'installation de test locale

**Interfaces:**
- Consumes: le rendu des Tasks 41 et 42

- [ ] **Step 1 : Créer le contenu de démo**

Depuis `local/azuriom-test` :
```bash
php artisan tinker --execute="
use Azuriom\Models\NavbarElement;
use Azuriom\Plugin\Wiki\Models\Category;
use Azuriom\Plugin\Wiki\Models\Page;

NavbarElement::create(['name' => 'Wiki', 'type' => 'plugin', 'value' => 'wiki.index', 'position' => 5]);

\$reglement = Category::create(['name' => 'Règlement', 'slug' => 'reglement', 'is_enabled' => true, 'position' => 0]);
Page::create(['title' => 'Règles générales', 'slug' => 'regles-generales', 'category_id' => \$reglement->id, 'position' => 0, 'content' => '<h2>Comportement</h2><p>Le respect entre joueurs est obligatoire. Toute forme de harcèlement, discrimination ou toxicité entraîne une sanction.</p><ul><li>Pas de triche</li><li>Pas de griefing</li><li>Pas de spam</li></ul>']);
Page::create(['title' => 'Sanctions', 'slug' => 'sanctions', 'category_id' => \$reglement->id, 'position' => 1, 'content' => '<p>Les sanctions vont de l\'avertissement au bannissement définitif selon la gravité et la récidive.</p>']);

\$guide = Category::create(['name' => 'Guide de démarrage', 'slug' => 'guide-demarrage', 'is_enabled' => true, 'position' => 1]);
Page::create(['title' => 'Premiers pas', 'slug' => 'premiers-pas', 'category_id' => \$guide->id, 'position' => 0, 'content' => '<p>Bienvenue sur le serveur ! Voici comment bien démarrer ton aventure.</p><h2>Étape 1</h2><p>Rejoins le serveur avec l\'adresse affichée sur la page d\'accueil.</p>']);
Page::create(['title' => 'Économie du serveur', 'slug' => 'economie', 'category_id' => \$guide->id, 'position' => 1, 'content' => '<p>Gagne de la monnaie en jouant, ou achète-en directement via la boutique.</p>']);

echo 'Contenu Wiki de démo créé.' . PHP_EOL;
"
```

- [ ] **Step 2 : Vérification manuelle complète**

```bash
php artisan view:clear
```

Visiter `/` : la navbar doit afficher un lien "Wiki" en plus des liens déjà présents. Visiter `/wiki` : les 2 catégories de démo doivent s'afficher. Cliquer sur "Règlement" : redirection vers la première page ("Règles générales"), sidebar affichant les 2 pages de la catégorie. Naviguer vers "Sanctions" : contenu mis à jour, sidebar reflète la nouvelle page active. Tester la recherche avec le terme "monnaie" (présent dans "Économie du serveur") : le résultat doit apparaître. Vérifier qu'aucune clé `theme::theme.*` brute n'apparaît. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 3 : Commit**

Aucun commit attendu si aucun fichier du thème n'a été modifié. Si la vérification révèle un problème dans les Tasks 41/42 nécessitant une correction, corriger le(s) fichier(s) concerné(s) et commiter séparément avec un message décrivant la correction.

---

## Notes pour l'implémentation

1. **Ordre d'exécution** : Task 41 doit précéder la Task 42 (le partial `_header` créé en Task 41 est inclus par les deux vues de la Task 42). Task 43 doit être la dernière.
2. **Aucun test automatisé** n'existe pour ce thème (convention déjà établie) — vérification manuelle uniquement.
3. Si le plugin Wiki s'avère déjà installé sur l'installation de test locale au moment de l'exécution, la Step 5 de la Task 41 est un no-op — vérifier simplement sa présence avant de copier quoi que ce soit.
