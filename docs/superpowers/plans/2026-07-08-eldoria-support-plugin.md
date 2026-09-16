# Eldoria — Support du plugin Support — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Habiller le plugin officiel Azuriom Support aux couleurs du thème (choix de catégorie, création de ticket avec champs dynamiques, mes tickets, fil de discussion), corriger un bug réel du layout qui empêche l'éditeur Markdown de fonctionner, et peupler l'installation de test locale avec du contenu de démo.

**Architecture:** Un correctif de layout + une surcharge d'élément core partagé (l'éditeur Markdown EasyMDE) forment la fondation consommée par les formulaires de création et de réponse. 4 fichiers de vue plugin à créer (`views/vendor/support/...`) plus un partial de badge de statut partagé.

**Tech Stack:** Laravel/Blade, Tailwind CSS v3 (+ plugin Typography déjà installé), EasyMDE (livré par le core Azuriom, `vendor/easymde/`), Bootstrap Icons (livré par le core Azuriom, `vendor/bootstrap-icons/`).

## Global Constraints

- Mobile-first, taille tactile minimale des boutons/liens : 48px
- Toutes les nouvelles chaînes d'interface passent par `theme::theme.support.*` (FR + EN), sauf les messages déjà fournis par le plugin (`support::messages.*`, `messages.fields.*`, `messages.actions.*`, `messages.comments.*`), réutilisés tels quels
- Le champ `icon` (Bootstrap Icons) des catégories est ignoré — une icône SVG Eldoria unique est utilisée pour toutes les catégories, pas de nouvelle dépendance de police d'icônes pour ce champ
- Les badges de statut de ticket restent dans le système à 2 accents personnalisables du thème (`--color-accent` / `--color-accent-secondary` / `--color-text-secondary`), jamais de vert/rouge Bootstrap fixe
- Aucune nouvelle dépendance JS. Bootstrap Icons est la seule police ajoutée, et uniquement sur les pages qui chargent l'éditeur Markdown (déjà livrée par le core Azuriom, zéro installation pour l'acheteur du thème)
- Le contenu de démo (lien navbar + catégories/champs/tickets) est créé uniquement dans la base de données de l'installation de test locale (`local/azuriom-test`), jamais commité dans `eldoria/`
- Pas de vue de modération/admin à styliser (liste globale, assignation) — hors scope

---

## Carte des fichiers

```
eldoria/
├── views/layouts/app.blade.php                 ← MODIFY — ajout de @stack('styles') et @stack('footer-scripts')
├── views/elements/markdown-editor.blade.php    ← NEW — surcharge de l'élément core partagé, réhabillé Eldoria
├── views/vendor/support/
│   ├── tickets/categories.blade.php            ← NEW — choix de catégorie
│   ├── tickets/create.blade.php                ← NEW — formulaire de création (éditeur ou champs dynamiques)
│   ├── tickets/index.blade.php                 ← NEW — mes tickets
│   ├── tickets/show.blade.php                  ← NEW — fil de discussion
│   └── partials/_status-badge.blade.php        ← NEW — badge de statut, partagé par index et show
└── lang/fr/theme.php, lang/en/theme.php        ← MODIFY — clés support.*
```

---

### Task 44 : Installer le plugin Support + corriger le bug des stacks + réhabiller l'éditeur Markdown

**Files:**
- Modify: `eldoria/views/layouts/app.blade.php`
- Create: `eldoria/views/elements/markdown-editor.blade.php`

**Interfaces:**
- Consumes: rien (fondation)
- Produces: les stacks `styles` et `footer-scripts` existent désormais dans le layout — toute vue ou élément qui `@push` dessus sera effectivement rendu. La surcharge `elements/markdown-editor.blade.php` s'active automatiquement dès qu'une vue fait `@include('elements.markdown-editor', ['imagesUploadUrl' => ..., 'autosaveId' => ...])` ou `@include('elements.markdown-editor', ['autosaveId' => ...])` (sans upload) — consommé par les Tasks 46 et 47.

- [ ] **Step 1 : Installer le plugin Support dans l'installation de test locale (si pas déjà fait)**

Depuis `local/azuriom-test` :
```bash
ls plugins/ | grep support
```
S'il est absent, l'installer depuis la source déjà clonée pendant la conception (`local/azuriom-plugin-support`) :
```bash
cp -r ../azuriom-plugin-support plugins/support
php artisan plugin:enable support
```
(`plugin:enable`, pas `migrate` — c'est la commande qui enregistre correctement le plugin dans `plugins.json` et exécute ses migrations ; correction déjà notée lors de l'installation du plugin Wiki.) Vérifier que le plugin apparaît activé dans l'admin avant de continuer.

- [ ] **Step 2 : Corriger `eldoria/views/layouts/app.blade.php` — ajouter les stacks manquantes**

Le fichier fait actuellement 47 lignes. Remplacer ce bloc (fin du `<head>`) :

```blade
    <link rel="stylesheet" href="{{ theme_asset('dist/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('dist/app.css') }}">
    <script type="module" src="{{ theme_asset('dist/app.js') }}"></script>

    @stack('head')
</head>
```

par :

```blade
    <link rel="stylesheet" href="{{ theme_asset('dist/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('dist/app.css') }}">
    <script type="module" src="{{ theme_asset('dist/app.js') }}"></script>

    @stack('styles')
    @stack('head')
</head>
```

Et remplacer ce bloc (fin du `<body>`) :

```blade
    @include('partials.particles')

    @stack('scripts')

</body>
```

par :

```blade
    @include('partials.particles')

    @stack('scripts')
    @stack('footer-scripts')

</body>
```

**Pourquoi** : l'élément core partagé `elements/markdown-editor.blade.php` (utilisé par les Tasks 46 et 47) pousse son CSS via `@push('styles')` et son JS d'initialisation via `@push('footer-scripts')`. Sans ces deux stacks dans le layout, tout contenu poussé dessus est silencieusement perdu — l'éditeur ne s'initialiserait même pas.

- [ ] **Step 3 : Créer `eldoria/views/elements/markdown-editor.blade.php`**

Ce fichier surcharge l'élément core partagé (pas un fichier du plugin Support — convention de surcharge identique à celle des vues plugin, mais pour un élément core). Le JS d'initialisation EasyMDE reste identique à 100% à l'original (upload d'image, autosave, textes traduits) : seul le bloc `<style>` change pour remplacer les variables Bootstrap par les couleurs Eldoria, et Bootstrap Icons est ajouté explicitement (le core le charge pour l'admin mais pas pour le site public).

```blade
@push('styles')
    <link href="{{ asset('vendor/easymde/easymde.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        .editor-toolbar {
            border-top-left-radius: 0.125rem;
            border-top-right-radius: 0.125rem;
            border: 1px solid color-mix(in srgb, var(--color-accent) 20%, transparent);
            border-bottom: 0;
            background: var(--color-bg-secondary);
        }

        .EasyMDEContainer .CodeMirror {
            border-bottom-left-radius: 0.125rem;
            border-bottom-right-radius: 0.125rem;
            border: 1px solid color-mix(in srgb, var(--color-accent) 20%, transparent);
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
        }

        .editor-toolbar .table {
            width: unset;
        }

        .CodeMirror.cm-s-easymde,
        .CodeMirror-fullscreen,
        .editor-preview,
        .editor-toolbar.fullscreen {
            color: var(--color-text-primary);
            background: var(--color-bg-primary);
        }

        .editor-toolbar button.active,
        .editor-toolbar button:hover {
            background: var(--color-bg-primary);
            border-color: var(--color-accent);
        }

        .editor-toolbar button i,
        .editor-toolbar .easymde-dropdown {
            color: var(--color-text-secondary);
        }

        .editor-toolbar button:hover i,
        .editor-toolbar button.active i {
            color: var(--color-accent);
        }

        .CodeMirror-cursor {
            border-color: var(--color-text-primary);
        }

        .editor-toolbar .easymde-dropdown,
        .editor-toolbar button {
            font-size: 24px;
            line-height: 1;
            margin: 0 1px;
            padding: 0;
        }

        .editor-statusbar {
            color: var(--color-text-secondary);
        }
    </style>
@endpush

@push('footer-scripts')
    <script src="{{ asset('vendor/easymde/easymde.min.js') }}"></script>
    <script>
        const easyMdeIcons = {
            'bold': 'bi bi-type-bold',
            'italic': 'bi bi-type-italic',
            'strikethrough': 'bi bi-type-strikethrough',
            'heading': 'bi bi-type-h1',
            'heading-smaller': 'bi bi-type-h1',
            'heading-bigger': 'bi bi-type-h1',
            'heading-1': 'bi bi-type-h1',
            'heading-2': 'bi bi-type-h2',
            'heading-3': 'bi bi-type-h3',
            'code': 'bi bi-code-slash',
            'quote': 'bi bi-quote',
            'ordered-list': 'bi bi-list-ol',
            'unordered-list': 'bi bi-list-ul',
            'clean-block': 'bi bi-eraser',
            'link': 'bi bi-link-45deg',
            'image': 'bi bi-image',
            'upload-image': 'bi bi-image',
            'table': 'bi bi-table',
            'horizontal-rule': 'bi bi-dash-lg',
            'preview': 'bi bi-eye',
            'side-by-side': 'bi bi-layout-split',
            'fullscreen': 'bi bi-fullscreen',
            'guide': 'bi bi-question-circle',
            'undo': 'bi bi-arrow-counterclockwise',
            'redo': 'bi bi-arrow-clockwise',
        };

        const easyMde = new EasyMDE({
            element: document.querySelector('.markdown-editor'),

            promptURLs: true,
            spellChecker: false,
            status: ['upload-image'],

            showIcons: ['strikethrough', 'code', '{{ isset($imagesUploadUrl) ? 'upload-image' : 'image' }}', 'table', 'horizontal-rule', 'undo', 'redo'],
            iconClassMap: easyMdeIcons,
            autoDownloadFontAwesome: false,

            @isset($autosaveId)
            autosave: {
                enabled: true,
                uniqueId: '{{ $autosaveId }}',
            },
            @endisset

            @isset($imagesUploadUrl)
            hideIcons: ['image'],
            uploadImage: true,
            imageAccept: '.jpg,.jpeg,.jpe,.png,.gif,.bmp,.svg,.webp',
            imageUploadFunction: function (file, onSuccess, onError) {
                if (file.size > easyMde.options.imageMaxSize) {
                    onError(easyMde.options.errorMessages.fileTooLarge);
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                axios.post('{{ $imagesUploadUrl }}', formData, {
                    onUploadProgress: function (progressEvent) {
                        if (progressEvent.lengthComputable) {
                            const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total) + '';
                            easyMde.updateStatusBar('upload-image', easyMde.options.imageTexts.sbProgress.replace('#file_name#', file.name).replace('#progress#', progress));
                        }
                    }
                }).then(function (response) {
                    onSuccess(response.data.location);
                }).catch(function (error) {
                    if (error.response) {
                        onError(error.response.data.message);
                        return;
                    }

                    onError(easyMde.options.errorMessages.importError);

                    console.error('Image upload error: ' + error);
                });
            },
            @endisset

            imageTexts: {
                sbInit: '{{ trans('messages.markdown.init') }}',
                sbOnDragEnter: '{{ trans('messages.markdown.drag') }}',
                sbOnDrop: '{{ trans('messages.markdown.drop') }}',
                sbProgress: '{{ trans('messages.markdown.progress') }}',
                sbOnUploaded: '{{ trans('messages.markdown.uploaded') }}',
            },

            errorMessages: {
                fileTooLarge: '{{ trans('messages.markdown.size') }}',
                importError: '{{ trans('messages.markdown.error') }}',
            },
        });

        // Fix for https://github.com/Ionaru/easy-markdown-editor/pull/501
        for (const button of easyMde.toolbar) {
            const icon = easyMdeIcons[button.name];
            const element = easyMde.gui.toolbar.querySelector('.' + button.name + ' .fa');

            if (icon && element) {
                element.setAttribute('class', icon);
                button.className = icon;
            }
        }
    </script>
@endpush
```

- [ ] **Step 4 : Vérification manuelle**

Créer une catégorie de test minimale (sans champs) pour pouvoir atteindre le formulaire de création — la vue stock du plugin (`tickets/create.blade.php`, pas encore surchargée) sera utilisée ici, seul l'élément `markdown-editor` est sous test :

```bash
cd local/azuriom-test
php artisan tinker --execute="
use Azuriom\Plugin\Support\Models\Category;
Category::create(['name' => 'Test temporaire']);
echo 'Catégorie de test créée.' . PHP_EOL;
"
php artisan view:clear
```

Builder les assets du thème (`cd eldoria && npm run build`), puis visiter `/support/tickets/create` (connecté), cliquer sur « Test temporaire ». Confirmer :
- La barre d'outils de l'éditeur s'affiche avec un fond sombre, une bordure dorée, et des icônes visibles (pas de carrés vides — Bootstrap Icons chargé correctement)
- La zone de texte de l'éditeur (CodeMirror) a un fond sombre et du texte clair, pas de boîte blanche
- Dans les requêtes réseau : `vendor/easymde/easymde.min.css`, `vendor/easymde/easymde.min.js`, `vendor/bootstrap-icons/bootstrap-icons.css` répondent tous 200
- Cliquer dans la zone de texte et taper du texte fonctionne normalement

Supprimer la catégorie de test avant de continuer (elle sera recréée proprement en Task 48) :
```bash
php artisan tinker --execute="
use Azuriom\Plugin\Support\Models\Category;
Category::where('name', 'Test temporaire')->delete();
"
```

- [ ] **Step 5 : Commit**

```bash
git add eldoria/views/layouts/app.blade.php eldoria/views/elements/markdown-editor.blade.php
git commit -m "fix(eldoria): stacks CSS/JS manquantes + réhabillage éditeur Markdown pour le plugin Support"
```

---

### Task 45 : Choix de catégorie (`tickets/categories.blade.php`)

**Files:**
- Create: `eldoria/views/vendor/support/tickets/categories.blade.php`
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes: `$categories` (Collection de `Category`, avec `name`, `description`), `$infoText` (`HtmlString|null`) — variables fournies par `TicketController::create()`
- Produces: la clé `theme::theme.support.eyebrow` — consommée aussi par les Tasks 47 (index) pour le même en-tête de section

- [ ] **Step 1 : Créer `eldoria/views/vendor/support/tickets/categories.blade.php`**

```blade
@extends('layouts.app')

@section('title', trans('support::messages.tickets.open'))

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ trans('support::messages.tickets.open') }}</h1>
    </div>

    @if($infoText !== null)
        <div class="max-w-3xl mx-auto mb-8">
            <div class="card-eldoria p-6 prose prose-invert prose-a:text-accent max-w-none text-text-secondary text-sm">
                {{ $infoText }}
            </div>
        </div>
    @endif

    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach($categories as $category)
                <div class="card-eldoria p-6 flex flex-col gap-4">
                    <div class="flex items-start gap-4">
                        <svg class="w-10 h-10 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 17.25h.007v.008H12v-.008z" />
                        </svg>
                        <div>
                            <h2 class="font-display text-text-primary font-semibold">{{ $category->name }}</h2>
                            @if($category->description)
                                <p class="text-text-secondary text-sm mt-1">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('support.category.tickets.create', $category) }}"
                       class="btn-primary justify-center min-h-[48px] mt-auto">
                        {{ trans('support::messages.actions.create') }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2 : Ajouter les clés de traduction dans `eldoria/lang/fr/theme.php`**

Insérer entre le tableau `'wiki'` (déjà présent) et `'auth'` :

```php
    'support' => [
        'eyebrow' => 'Assistance',
        'no_tickets' => "Vous n'avez pas encore ouvert de ticket.",
    ],

```

- [ ] **Step 3 : Ajouter les clés de traduction dans `eldoria/lang/en/theme.php`**

Insérer entre le tableau `'wiki'` (déjà présent) et `'auth'` :

```php
    'support' => [
        'eyebrow' => 'Support',
        'no_tickets' => "You haven't opened a ticket yet.",
    ],

```

(`no_tickets` n'est pas encore utilisée par cette vue — elle sera consommée par `tickets/index.blade.php` en Task 47. L'ajouter maintenant regroupe les deux clés `support.*` en un seul endroit du fichier.)

- [ ] **Step 4 : Vérification manuelle**

```bash
cd eldoria && npm run build
```

Depuis `local/azuriom-test`, avec au moins une catégorie de test (réutiliser la catégorie « Test temporaire » créée puis supprimée en Task 44, ou en recréer une via tinker), visiter `/support/tickets/create`. Confirmer : eyebrow + titre dorés centrés, grille de cartes avec icône, nom, description (si présente) et bouton « Ouvrir un ticket », aucune clé `theme::theme.*` brute affichée.

- [ ] **Step 5 : Commit**

```bash
git add eldoria/views/vendor/support/tickets/categories.blade.php eldoria/lang/fr/theme.php eldoria/lang/en/theme.php
git commit -m "feat(eldoria): support du plugin Support — choix de catégorie"
```

---

### Task 46 : Formulaire de création (`tickets/create.blade.php`)

**Files:**
- Create: `eldoria/views/vendor/support/tickets/create.blade.php`

**Interfaces:**
- Consumes: `$category` (`Category`, avec `name`, `fields` — Collection de `Field` ayant `type`, `name`, `description`, `is_required`, `options`, et la méthode `inputName()`), `$pendingId` (string, UUID) — variables fournies par `CategoryTicketController::create()`. Consomme l'élément `elements/markdown-editor` surchargé en Task 44.
- Produces: rien de consommé par une task ultérieure — le formulaire soumet en POST vers `support.category.tickets.store`, géré entièrement par le plugin

- [ ] **Step 1 : Créer `eldoria/views/vendor/support/tickets/create.blade.php`**

```blade
@extends('layouts.app')

@section('title', trans('support::messages.tickets.open'))

@if($category->fields->isEmpty())
    @include('elements.markdown-editor', [
        'imagesUploadUrl' => route('support.comments.attachments.pending', $pendingId),
        'autosaveId' => 'support_ticket',
    ])
@endif

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ $category->name }}</h1>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card-eldoria p-8">
            <form action="{{ route('support.category.tickets.store', $category) }}" method="POST" class="space-y-6">
                @csrf

                <input type="hidden" name="pending_id" value="{{ $pendingId }}">

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="subjectInput">
                        {{ trans('support::messages.fields.subject') }}
                    </label>
                    <input type="text" id="subjectInput" name="subject" value="{{ old('subject') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('subject')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($category->fields->isEmpty())
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="contentInput">
                            {{ trans('messages.fields.content') }}
                        </label>
                        <textarea id="contentInput" name="content" rows="6"
                                  class="markdown-editor w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    @foreach($category->fields as $field)
                        <div>
                            @if($field->type === 'checkbox')
                                <label class="flex items-center gap-2 text-text-secondary cursor-pointer">
                                    <input type="checkbox" name="{{ $field->inputName() }}" id="{{ $field->inputName() }}"
                                           class="accent-[var(--color-accent)]"
                                           @required($field->is_required) @checked(old($field->inputName()))>
                                    {{ $field->name }}
                                    @if($field->is_required)
                                        <span class="text-accent">*</span>
                                    @endif
                                </label>
                            @else
                                <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="{{ $field->inputName() }}">
                                    {{ $field->name }}
                                    @if($field->is_required)
                                        <span class="text-accent">*</span>
                                    @endif
                                </label>

                                @if($field->type === 'dropdown')
                                    <select name="{{ $field->inputName() }}" id="{{ $field->inputName() }}"
                                            @required($field->is_required)
                                            class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                                   focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                                        @foreach($field->options as $option)
                                            <option @selected(old($field->inputName()) === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field->type === 'textarea')
                                    <textarea name="{{ $field->inputName() }}" id="{{ $field->inputName() }}" rows="4"
                                              @required($field->is_required)
                                              class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                                     focus:outline-none focus:border-accent/60 transition-colors">{{ old($field->inputName()) }}</textarea>
                                @else
                                    <input type="{{ $field->type }}" name="{{ $field->inputName() }}" id="{{ $field->inputName() }}"
                                           value="{{ old($field->inputName()) }}"
                                           @required($field->is_required)
                                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                                @endif
                            @endif

                            @error($field->inputName())
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            @if($field->description)
                                <p class="text-text-secondary text-xs mt-1">{{ $field->description }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif

                <button type="submit" class="btn-primary w-full justify-center py-4 min-h-[48px]">
                    {{ trans('messages.actions.send') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2 : Vérification manuelle**

```bash
cd eldoria && npm run build
```

Créer deux catégories de test via tinker : une sans champ, une avec un champ `text` obligatoire et un `dropdown` avec 2 options :

```bash
cd local/azuriom-test
php artisan tinker --execute="
use Azuriom\Plugin\Support\Models\Category;
use Azuriom\Plugin\Support\Models\Field;

\$simple = Category::create(['name' => 'Test simple']);

\$champs = Category::create(['name' => 'Test champs']);
\$champs->fields()->create(['name' => 'Résumé', 'description' => 'Résume le problème en une phrase.', 'type' => 'text', 'is_required' => true]);
\$champs->fields()->create(['name' => 'Plateforme', 'description' => 'Java ou Bedrock ?', 'type' => 'dropdown', 'options' => ['Java', 'Bedrock'], 'is_required' => true]);

echo 'Catégories de test créées: ' . \$simple->id . ', ' . \$champs->id . PHP_EOL;
"
```

Visiter `/support/tickets/create`, cliquer sur « Test simple » : confirmer que l'éditeur Markdown réhabillé s'affiche (sujet + éditeur). Retour, cliquer sur « Test champs » : confirmer que le champ texte et le dropdown s'affichent avec le style Eldoria (bordure dorée au focus), pas d'éditeur Markdown. Soumettre le formulaire « Test champs » sans remplir le champ obligatoire : confirmer qu'une erreur de validation s'affiche sous le bon champ. Soumettre avec des valeurs valides : confirmer la redirection vers le ticket créé (`support.tickets.show`) — la vue `show.blade.php` n'existe pas encore, une erreur "vue introuvable" est attendue ici et sera résolue par la Task 47 ; c'est le comportement du routing qui est sous test, pas le rendu de la page suivante.

Supprimer les catégories de test avant de continuer :
```bash
php artisan tinker --execute="
use Azuriom\Plugin\Support\Models\Category;
Category::whereIn('name', ['Test simple', 'Test champs'])->delete();
"
```

- [ ] **Step 3 : Commit**

```bash
git add eldoria/views/vendor/support/tickets/create.blade.php
git commit -m "feat(eldoria): support du plugin Support — formulaire de création avec champs dynamiques"
```

---

### Task 47 : Mes tickets + Fil de discussion (`tickets/index.blade.php`, `tickets/show.blade.php`, badge de statut)

**Files:**
- Create: `eldoria/views/vendor/support/partials/_status-badge.blade.php`
- Create: `eldoria/views/vendor/support/tickets/index.blade.php`
- Create: `eldoria/views/vendor/support/tickets/show.blade.php`
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes (index) : `$tickets` (Collection de `Ticket`, avec `category`, `subject`, `created_at`), `$infoText` (`HtmlString|null`) — fournies par `TicketController::index()`
- Consumes (show) : `$ticket` (`Ticket`, avec `category`, `author`, `comments.author` chargés), `$pendingId` (string), `$canReopen` (bool) — fournies par `TicketController::show()`. Consomme l'élément `elements/markdown-editor` surchargé en Task 44.
- Consumes (badge) : `$ticket` (`Ticket`) — variable passée explicitement par chaque vue qui inclut le partial
- Produces: le partial `eldoria/views/vendor/support/partials/_status-badge.blade.php`, inclus via `@include('support::partials._status-badge', ['ticket' => $ticket])` — consommé par `index.blade.php` et `show.blade.php` (même task). La clé `theme::theme.support.no_tickets`.

- [ ] **Step 1 : Créer `eldoria/views/vendor/support/partials/_status-badge.blade.php`**

```blade
@php
    $status = $ticket->status();
    $statusClasses = match($status) {
        'open' => 'bg-accent/10 border-accent/40 text-accent',
        'replied' => 'bg-accent-secondary/10 border-accent-secondary/40 text-accent-secondary',
        'closed' => 'bg-bg-primary border-text-secondary/30 text-text-secondary',
    };
@endphp
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs border {{ $statusClasses }}">
    {{ $ticket->statusMessage() }}
</span>
```

(`$ticket->status()` sans argument, comme le fait la vue stock du plugin sur les pages utilisateur — l'état `replied` n'apparaît que côté admin dans le comportement réel du plugin, hors scope ici, mais le badge le gère correctement si jamais réutilisé plus tard.)

- [ ] **Step 2 : Créer `eldoria/views/vendor/support/tickets/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', trans('support::messages.title'))

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ trans('support::messages.title') }}</h1>
    </div>

    @if($infoText !== null)
        <div class="max-w-3xl mx-auto mb-8">
            <div class="card-eldoria p-6 prose prose-invert prose-a:text-accent max-w-none text-text-secondary text-sm">
                {{ $infoText }}
            </div>
        </div>
    @endif

    <div class="max-w-3xl mx-auto space-y-4">
        @forelse($tickets as $ticket)
            <a href="{{ route('support.tickets.show', $ticket) }}"
               class="card-eldoria p-6 flex items-center justify-between gap-4 min-h-[48px] hover:-translate-y-0.5 transition-transform duration-300">
                <div>
                    <h2 class="font-display text-text-primary font-semibold">{{ $ticket->subject }}</h2>
                    <p class="text-text-secondary text-xs mt-1">
                        {{ $ticket->category->name }} — {{ format_date_compact($ticket->created_at) }}
                    </p>
                </div>

                @include('support::partials._status-badge', ['ticket' => $ticket])
            </a>
        @empty
            <p class="text-text-secondary text-sm text-center">{{ __('theme::theme.support.no_tickets') }}</p>
        @endforelse

        <a href="{{ route('support.tickets.create') }}" class="btn-primary justify-center min-h-[48px] w-full sm:w-auto">
            {{ trans('support::messages.actions.create') }}
        </a>
    </div>
</div>
@endsection
```

- [ ] **Step 3 : Créer `eldoria/views/vendor/support/tickets/show.blade.php`**

```blade
@extends('layouts.app')

@section('title', $ticket->subject)

@if(! $ticket->isClosed())
    @include('elements.markdown-editor', [
        'imagesUploadUrl' => route('support.comments.attachments.pending', $pendingId),
        'autosaveId' => 'support_ticket_'.$ticket->id,
    ])
@endif

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="text-center py-12">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-text-primary mb-4">{{ $ticket->subject }}</h1>
            @include('support::partials._status-badge', ['ticket' => $ticket])
            <p class="text-text-secondary text-sm mt-4">
                @lang('support::messages.tickets.info', ['author' => e($ticket->author->name), 'category' => e($ticket->category->name), 'date' => format_date($ticket->created_at)])
            </p>
        </div>

        <div class="space-y-4">
            @foreach($ticket->comments as $comment)
                <div class="card-eldoria p-6 flex gap-4">
                    <img src="{{ $comment->author->getAvatar(48) }}" alt="{{ $comment->author->name }}" class="w-12 h-12 rounded-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-text-secondary text-xs mb-2">
                            @lang('messages.comments.author', ['user' => e($comment->author->name), 'date' => format_date($comment->created_at, true)])
                        </p>
                        <div class="prose prose-invert prose-a:text-accent max-w-none text-text-primary text-sm">
                            {{ $comment->parseContent() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($ticket->isClosed())
            <div class="card-eldoria p-6 mt-4 text-center">
                <p class="text-text-secondary text-sm">{{ trans('support::messages.tickets.closed') }}</p>

                @if($canReopen)
                    <form action="{{ route('support.tickets.open', $ticket) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-primary min-h-[48px]">
                            {{ trans('support::messages.actions.reopen') }}
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="card-eldoria p-6 mt-4 space-y-4">
                <form action="{{ route('support.tickets.comments.store', $ticket) }}" method="POST">
                    @csrf

                    <input type="hidden" name="pending_id" value="{{ $pendingId }}">

                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="content">
                        {{ trans('support::messages.fields.comment') }}
                    </label>
                    <textarea id="content" name="content" rows="4"
                              class="markdown-editor w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-primary min-h-[48px] mt-4">
                        {{ trans('messages.actions.comment') }}
                    </button>
                </form>

                <form action="{{ route('support.tickets.close', $ticket) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 min-h-[48px] border border-accent/40 text-text-secondary font-display text-sm tracking-widest uppercase hover:border-accent hover:text-accent transition-all duration-300 rounded-sm">
                        {{ trans('messages.actions.close') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
```

- [ ] **Step 4 : Ajouter la clé de traduction manquante**

Vérifier que `theme::theme.support.no_tickets` existe déjà dans `eldoria/lang/fr/theme.php` et `eldoria/lang/en/theme.php` (ajoutée en Task 45 Steps 2-3). Si pour une raison quelconque elle est absente, l'ajouter dans le tableau `'support'` existant :

FR : `'no_tickets' => "Vous n'avez pas encore ouvert de ticket.",`
EN : `'no_tickets' => "You haven't opened a ticket yet.",`

- [ ] **Step 5 : Vérification manuelle**

```bash
cd eldoria && npm run build
```

Recréer une catégorie de test simple, ouvrir un ticket via `/support/tickets/create` → « Test simple » → soumettre avec un sujet et un contenu via l'éditeur Markdown. Confirmer :
- Redirection vers `/support/tickets/{id}` : sujet en titre, badge « Ouvert » en accent doré, info auteur/catégorie/date, le commentaire initial affiché avec avatar
- Le formulaire de réponse (éditeur Markdown réhabillé) et le bouton fermer sont visibles
- Répondre au ticket via le formulaire : le nouveau commentaire apparaît dans le fil
- Cliquer sur fermer : le badge passe en `text-secondary` neutre, le message "ce ticket est fermé" s'affiche, plus de formulaire de réponse
- Visiter `/support/tickets` : le ticket apparaît dans « Mes tickets » avec son badge de statut à jour
- Vérifier `storage/logs/laravel-*.log` : aucune nouvelle erreur

Supprimer la catégorie et le ticket de test avant de continuer (Task 48 crée le contenu de démo définitif) :
```bash
cd local/azuriom-test
php artisan tinker --execute="
use Azuriom\Plugin\Support\Models\Category;
Category::where('name', 'Test simple')->delete();
"
```
(La suppression de la catégorie entraîne bien la suppression en cascade du ticket puis de ses commentaires — les clés étrangères `support_tickets.category_id` et `support_comments.ticket_id` sont toutes deux déclarées `cascadeOnDelete()` dans les migrations réelles du plugin, vérifié dans `local/azuriom-plugin-support/database/migrations/`.)

- [ ] **Step 6 : Commit**

```bash
git add eldoria/views/vendor/support/partials/_status-badge.blade.php eldoria/views/vendor/support/tickets/index.blade.php eldoria/views/vendor/support/tickets/show.blade.php eldoria/lang/fr/theme.php eldoria/lang/en/theme.php
git commit -m "feat(eldoria): support du plugin Support — mes tickets et fil de discussion"
```

---

### Task 48 : Contenu de démo (base de test locale) + revue finale

**Files:**
- Aucun fichier du thème modifié — cette tâche ne touche que la base de données de l'installation de test locale

**Interfaces:**
- Consumes: le rendu des Tasks 44 à 47

- [ ] **Step 1 : Créer le contenu de démo**

Depuis `local/azuriom-test` :
```bash
php artisan tinker --execute="
use Azuriom\Models\NavbarElement;
use Azuriom\Models\User;
use Azuriom\Plugin\Support\Models\Category;
use Azuriom\Plugin\Support\Models\Field;
use Illuminate\Support\Facades\Auth;

NavbarElement::create(['name' => 'Support', 'type' => 'plugin', 'value' => 'support.tickets.create', 'position' => 6]);

\$general = Category::create(['name' => 'Question générale', 'description' => 'Pour toute question ne concernant pas un bug ou un problème technique.']);

\$bug = Category::create(['name' => 'Signaler un bug', 'description' => 'Décris le problème rencontré en jeu ou sur le site.']);
\$bug->fields()->create(['name' => 'Résumé du bug', 'description' => 'En une phrase, décris ce qui ne fonctionne pas.', 'type' => 'text', 'is_required' => true]);
\$bug->fields()->create(['name' => 'Plateforme', 'description' => 'Sur quelle version joues-tu ?', 'type' => 'dropdown', 'options' => ['Java', 'Bedrock'], 'is_required' => true]);

\$user = User::first();
Auth::login(\$user);

\$ticket1 = \$general->tickets()->create(['subject' => 'Comment rejoindre le serveur Discord ?']);
\$ticket1->comments()->create(['content' => 'Bonjour, je ne trouve pas le lien vers le Discord du serveur, pouvez-vous m\'aider ?']);
\$ticket1->comments()->create(['content' => 'Bonjour ! Le lien est disponible en bas de la page d\'accueil, dans le pied de page.']);

\$ticket2 = \$bug->tickets()->create(['subject' => 'Chute au sol qui traverse le décor']);
\$ticket2->comments()->create(['content' => \"## Résumé du bug\n\nJe tombe à travers le sol près du spawn.\n\n## Plateforme\n\nJava\"]);
\$ticket2->comments()->create(['content' => 'Merci pour le signalement, nous investiguons.']);

\$ticket3 = \$general->tickets()->create(['subject' => 'Question résolue']);
\$ticket3->comments()->create(['content' => 'Ma question a été résolue, merci !']);
\$ticket3->closed_at = now()->subDay();
\$ticket3->save();

echo 'Contenu Support de démo créé.' . PHP_EOL;
"
```

**Pourquoi `Auth::login(\$user)` est nécessaire** : `Ticket` et `Comment` utilisent le trait `HasUser`, qui renseigne `author_id` depuis `Auth::id()` dans un événement `creating` — sans session active (le cas par défaut dans une session `tinker` brute), `author_id` resterait `null`. `author_id` et `closed_at` ne sont volontairement pas passés dans les tableaux `create()` : ils ne font pas partie du `$fillable` de leurs modèles respectifs et seraient silencieusement ignorés — `closed_at` est donc assigné directement sur l'instance puis sauvegardé.

- [ ] **Step 2 : Vérification manuelle complète**

```bash
php artisan view:clear
```

Visiter `/` : la navbar affiche un lien « Support » en plus des liens déjà présents. Visiter `/support/tickets/create` : les 2 catégories de démo s'affichent avec leurs descriptions. Visiter `/support/tickets` (connecté avec l'utilisateur `\$user` utilisé ci-dessus) : les 3 tickets de démo s'affichent avec les bons badges (2 ouverts en accent doré, 1 fermé en neutre). Ouvrir le ticket fermé : confirmer le message "ce ticket est fermé" et l'absence de formulaire de réponse. Ouvrir un ticket ouvert : confirmer le fil de discussion et le formulaire de réponse. Vérifier qu'aucune clé `theme::theme.*` brute n'apparaît. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 3 : Commit**

Aucun commit attendu si aucun fichier du thème n'a été modifié. Si la vérification révèle un problème dans les Tasks 44-47 nécessitant une correction, corriger le(s) fichier(s) concerné(s) et commiter séparément avec un message décrivant la correction.

---

## Notes pour l'implémentation

1. **Ordre d'exécution strict** : Task 44 doit précéder toutes les autres (fournit le layout corrigé et l'éditeur Markdown réhabillé, consommés par les Tasks 46 et 47). Task 45 doit précéder la Task 47 (partage la clé `theme::theme.support.eyebrow`, bien qu'ajoutée dès la Task 45). Task 48 doit être la dernière.
2. **Aucun test automatisé** n'existe pour ce thème (convention déjà établie) — vérification manuelle uniquement.
3. Chaque task de vérification (44, 46, 47) crée des catégories/tickets de test **temporaires** via tinker, supprimés avant le commit de la task — seule la Task 48 crée le contenu de démo définitif qui reste en base. Ce n'est pas une omission si une task précédente laisse des traces : la Task 48 Step 1 crée son propre contenu indépendamment, et sa Step 2 est la vérification de référence sur l'état final.
4. Si le plugin Support s'avère déjà installé sur l'installation de test locale au moment de l'exécution, la Step 1 de la Task 44 est un no-op — vérifier simplement sa présence avant de copier quoi que ce soit.
5. **Schéma réel de `Category` et `Field`** (vérifié contre les migrations réelles du plugin, `local/azuriom-plugin-support/database/migrations/`) : `support_categories` n'a **pas** de colonne `is_enabled` ni `position` (contrairement au plugin Wiki) — ne jamais les passer à `Category::create()`. `support_fields` n'a pas non plus de colonne `position`, et sa colonne `description` est **`NOT NULL`** (pas nullable malgré le docblock du modèle `Field`) — toujours fournir une valeur non vide à `description` dans `Field::create()`, sous peine d'erreur SQL.
6. **`category_id` n'est pas fillable sur `Field`** (`$fillable = ['name', 'description', 'type', 'is_required', 'options']`, confirmé en Task 46) : créer toujours les champs de test/démo via la relation — `\$category->fields()->create([...])` (sans la clé `category_id` dans le tableau) — jamais via `Field::create(['category_id' => ..., ...])`, qui laisserait `category_id` silencieusement non renseigné.
6. **`Auth::login()` requis pour toute création de `Ticket`/`Comment` en tinker** : ces deux modèles utilisent le trait `HasUser`, qui renseigne `author_id` depuis `Auth::id()` dans un événement `creating` — sans session active, la création échouerait silencieusement (author_id null). `author_id` et `closed_at` ne font pas partie du `$fillable` de `Ticket` et doivent donc être renseignés respectivement via `Auth::login()` (avant création) et assignation directe + `save()` (après création), jamais via le tableau `create()`.
