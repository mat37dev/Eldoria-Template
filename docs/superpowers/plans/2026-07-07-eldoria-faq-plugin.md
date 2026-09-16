# Eldoria — Support du plugin FAQ — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Habiller le plugin officiel Azuriom FAQ aux couleurs du thème (accordéon Eldoria au lieu du rendu Bootstrap par défaut), et peupler l'installation de test locale avec du contenu de démo.

**Architecture:** Un seul fichier de vue à surcharger (`views/vendor/faq/index.blade.php`), le plugin ne passant qu'une variable (`$questions`, liste plate ordonnée, pas de catégories). Accordéon géré en Alpine.js (déjà une dépendance du thème), un seul élément ouvert à la fois — pas de nouvelle dépendance JS.

**Tech Stack:** Laravel/Blade, Tailwind CSS v3 (+ plugin Typography déjà installé), Alpine.js v3.

## Global Constraints

- Mobile-first : CSS pour `< 640px` en premier, desktop via `min-width`
- Taille tactile minimale des boutons : 48px
- Toutes les nouvelles chaînes d'interface passent par `theme::theme.faq.*` (FR + EN) — sauf le message d'état vide, qui réutilise la traduction déjà fournie par le plugin (`trans('faq::messages.empty')`), pour éviter de dupliquer une chaîne déjà traduite par le plugin lui-même
- `prefers-reduced-motion` : la rotation du chevron et le fondu d'ouverture/fermeture de chaque réponse sont désactivés dans ce mode
- Le contenu de démo (lien navbar + questions) est créé uniquement dans la base de données de l'installation de test locale (`local/azuriom-test`), jamais commité dans `eldoria/`

---

## Carte des fichiers

```
eldoria/
├── assets/css/app.css                ← MODIFY — désactive les transitions FAQ sous prefers-reduced-motion
├── views/vendor/faq/index.blade.php  ← NEW — surcharge du rendu du plugin FAQ
└── lang/fr/theme.php, lang/en/theme.php ← MODIFY — clés faq.*
```

---

### Task 39 : Vue FAQ stylée + i18n

**Files:**
- Create: `eldoria/views/vendor/faq/index.blade.php`
- Modify: `eldoria/assets/css/app.css`
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes: `$questions` (collection Eloquent de `Azuriom\Plugin\FAQ\Models\Question`, triée par `position`, champs `id`, `name` (question), `answer` (HTML libre)), passée par le contrôleur réel `Azuriom\Plugin\FAQ\Controllers\QuestionController::index()` via la route `faq.index`, vue `faq::index` surchargée par le chemin theme `views/vendor/faq/index.blade.php`

- [ ] **Step 1 : Créer `eldoria/views/vendor/faq/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', __('theme::theme.faq.title'))

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.location.hash) {
                return;
            }

            const target = document.getElementById(window.location.hash.substring(1));
            if (!target) {
                return;
            }

            const button = target.querySelector('[data-faq-toggle]');
            if (button) {
                button.click();
            }
        });
    </script>
@endpush

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.faq.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ __('theme::theme.faq.title') }}</h1>
    </div>

    <div class="max-w-3xl mx-auto">
        @if($questions->isEmpty())
            <p class="text-text-secondary text-sm text-center">{{ trans('faq::messages.empty') }}</p>
        @else
            <div class="space-y-4" x-data="{ open: {{ $questions->first()->id }} }">
                @foreach($questions as $question)
                    <div class="card-eldoria overflow-hidden" id="{{ \Illuminate\Support\Str::slug($question->name) }}">
                        <button type="button" data-faq-toggle
                                @click="open = (open === {{ $question->id }} ? null : {{ $question->id }})"
                                class="w-full flex items-center justify-between gap-4 p-6 min-h-[48px] text-left">
                            <span class="font-display text-text-primary font-semibold">{{ $question->name }}</span>
                            <svg class="faq-chevron w-5 h-5 text-accent flex-shrink-0"
                                 :class="open === {{ $question->id }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $question->id }}"
                             x-transition:enter="faq-answer-transition"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="faq-answer-transition"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="px-6 pb-6 prose prose-invert prose-a:text-accent max-w-none text-text-secondary text-sm">
                            {!! $question->answer !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
```

> Le script de saut d'ancre (`#slug-de-la-question`) est réécrit en vanilla JS, sans Bootstrap `Collapse` (absent de ce thème) : il clique simplement le bouton de l'accordéon correspondant, ce qui met à jour l'état Alpine `open` de la même façon qu'un clic utilisateur.

> Le chevron pivote via `rotate-180` (Tailwind), la classe `faq-chevron` sert uniquement d'accroche CSS pour la Step 2. La transition d'ouverture/fermeture de la réponse utilise une classe nommée `faq-answer-transition` plutôt que les utilitaires Tailwind `transition ease-out duration-200` inline, pour permettre à la Step 2 de la neutraliser proprement sous `prefers-reduced-motion` sans avoir à répéter les mêmes classes Tailwind dans une media query.

- [ ] **Step 2 : Ajouter les classes de transition et leur neutralisation sous `prefers-reduced-motion` dans `eldoria/assets/css/app.css`**

Ajouter à la fin du fichier :
```css

/* Accordéon FAQ : transition de la réponse + rotation du chevron,
   désactivées si l'utilisateur préfère moins de mouvement. */
.faq-answer-transition {
    transition: opacity 200ms ease-out;
}

.faq-chevron {
    transition: transform 200ms ease-out;
}

@media (prefers-reduced-motion: reduce) {
    .faq-answer-transition,
    .faq-chevron {
        transition: none;
    }
}
```

- [ ] **Step 3 : Ajouter les clés de traduction dans `eldoria/lang/fr/theme.php`**

Remplacer :
```php
        'rewards_title' => 'Récompenses possibles',
    ],

    'auth' => [
```
par :
```php
        'rewards_title' => 'Récompenses possibles',
    ],

    'faq' => [
        'eyebrow' => 'Aide',
        'title' => 'Foire aux questions',
    ],

    'auth' => [
```

- [ ] **Step 4 : Ajouter les clés de traduction dans `eldoria/lang/en/theme.php`**

Remplacer :
```php
        'rewards_title' => 'Possible rewards',
    ],

    'auth' => [
```
par :
```php
        'rewards_title' => 'Possible rewards',
    ],

    'faq' => [
        'eyebrow' => 'Help',
        'title' => 'Frequently Asked Questions',
    ],

    'auth' => [
```

- [ ] **Step 5 : Installer le plugin FAQ dans l'installation de test locale (si pas déjà fait)**

Depuis `local/azuriom-test`, vérifier si le plugin est déjà présent :
```bash
ls plugins/ | grep faq
```
S'il est absent, l'installer (le code source réel est déjà disponible localement dans `local/azuriom-plugin-faq`, cloné pendant la phase de conception) — copier ce dossier dans `plugins/faq/` de l'installation de test, puis exécuter les migrations :
```bash
cp -r ../azuriom-plugin-faq plugins/faq
php artisan migrate
```
Vérifier que le plugin apparaît activé dans `/admin/plugins` (ou équivalent) avant de continuer.

- [ ] **Step 6 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Créer 2-3 questions de test via l'admin Azuriom (ou `php artisan tinker` avec `\Azuriom\Plugin\FAQ\Models\Question::create(['name' => '...', 'answer' => '<p>...</p>', 'position' => 0])`), puis visiter `/faq` (ou la route réelle confirmée par `php artisan route:list | grep faq`). Confirmer : en-tête façon Hero, chaque question dans une carte `card-eldoria`, cliquer sur une question l'ouvre (chevron pivote, réponse apparaît en fondu), cliquer sur une autre question ferme la première (un seul élément ouvert à la fois). Émuler `prefers-reduced-motion: reduce` et confirmer que le chevron ne pivote plus progressivement et que la réponse apparaît/disparaît instantanément (sans fondu). Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 7 : Commit**

```bash
git add eldoria/views/vendor/faq/index.blade.php eldoria/assets/css/app.css eldoria/lang/fr/theme.php eldoria/lang/en/theme.php
git commit -m "feat(eldoria): support du plugin FAQ (accordéon Eldoria)"
```

---

### Task 40 : Contenu de démo (base de test locale) + revue finale

**Files:**
- Aucun fichier du thème modifié — cette tâche ne touche que la base de données de l'installation de test locale

**Interfaces:**
- Consumes: le rendu de la Task 39

- [ ] **Step 1 : Créer le contenu de démo**

Depuis `local/azuriom-test` :
```bash
php artisan tinker --execute="
use Azuriom\Models\NavbarElement;
use Azuriom\Plugin\FAQ\Models\Question;

NavbarElement::create(['name' => 'FAQ', 'type' => 'plugin', 'value' => 'faq.index', 'position' => 4]);

Question::create(['name' => 'Comment rejoindre le serveur ?', 'answer' => '<p>Copie l\'adresse IP affichée sur la page d\'accueil, ouvre Minecraft, va dans Multijoueur puis Ajouter un serveur.</p>', 'position' => 0]);
Question::create(['name' => 'Comment fonctionne la boutique ?', 'answer' => '<p>La boutique te permet d\'acheter des rangs et de la monnaie in-game via un paiement sécurisé. Les achats sont livrés automatiquement à la connexion.</p>', 'position' => 1]);
Question::create(['name' => 'Puis-je récupérer mon pseudo si je change de compte Minecraft ?', 'answer' => '<p>Contacte le staff via Discord avec une preuve de propriété de l\'ancien compte.</p>', 'position' => 2]);
Question::create(['name' => 'Comment signaler un joueur ?', 'answer' => '<p>Utilise la commande <code>/report</code> en jeu ou ouvre un ticket sur notre Discord avec des preuves (captures d\'écran, vidéos).</p>', 'position' => 3]);

echo 'Contenu FAQ de démo créé.' . PHP_EOL;
"
```

- [ ] **Step 2 : Vérification manuelle complète**

```bash
php artisan view:clear
```

Visiter `/` : la navbar doit maintenant afficher un lien "FAQ" en plus des liens déjà présents (Boutique/Vote/Actus). Cliquer dessus (ou visiter directement la route FAQ) : les 4 questions doivent s'afficher, triées dans l'ordre de création, avec le style Eldoria de la Task 39. Cliquer sur chaque question pour confirmer l'accordéon fonctionne. Vérifier qu'aucune clé `theme::theme.*` brute n'apparaît. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 3 : Commit**

Aucun commit attendu si aucun fichier du thème n'a été modifié (le contenu de démo n'est jamais commité). Si la vérification révèle un problème dans la Task 39 nécessitant une correction, corriger `eldoria/views/vendor/faq/index.blade.php` (ou les fichiers associés) et commiter séparément avec un message décrivant la correction.

---

## Notes pour l'implémentation

1. **Ordre d'exécution** : Task 39 doit précéder la Task 40 (le contenu de démo n'a de sens qu'une fois la vue stylée).
2. **Aucun test automatisé** n'existe pour ce thème (convention déjà établie sur tout le projet) — vérification manuelle uniquement.
3. Si le plugin FAQ s'avère déjà installé sur l'installation de test locale au moment de l'exécution (installé par un utilisateur entre-temps), la Step 5 de la Task 39 est un no-op — vérifier simplement sa présence avant de copier quoi que ce soit.
