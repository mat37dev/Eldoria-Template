# Eldoria — Éditeur de disposition de la homepage — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un éditeur en direct sur la homepage permettant à l'admin de réorganiser (glisser-déposer), masquer, et éditer le texte de 8 sections de l'accueil, inspiré du thème commercial "Deluxe".

**Architecture:** Extraction des 8 sections réorganisables de `home.blade.php` en partials individuels pilotés par une nouvelle clé de config `home_layout` (JSON : ordre + visibilité + surcharges de texte pour 2 sections). Un 3ᵉ onglet "Disposition" dans le drawer customizer existant active un mode réorganisation avec overlays sur la page (poignée SortableJS, bascule visibilité, édition de texte), sauvegardé via le même mécanisme AJAX que le reste du customizer.

**Tech Stack:** Laravel/Blade, Alpine.js v3, SortableJS (nouvelle dépendance), Tailwind CSS v3.

## Global Constraints

- Mobile-first : CSS écrit pour `< 640px` en premier, desktop via `min-width`
- Toutes les animations automatiques respectent `prefers-reduced-motion: reduce` (le glisser-déposer est une manipulation directe de l'utilisateur, non concerné par cette règle — voir spec §7)
- Taille tactile minimale des boutons : 48px
- Couleurs uniquement via les CSS custom properties existantes — aucune couleur codée en dur
- i18n : tout nouveau texte d'interface passe par `theme::theme.*` (fr/en)
- Une installation fraîche du thème doit se comporter **exactement comme avant ce plan** tant que l'admin n'a pas utilisé l'éditeur (repli par défaut = ordre/visibilité actuels)
- Pas de blocs custom créés à la volée — uniquement réorganiser/masquer/éditer les 8 sections existantes
- Le Hero reste fixe, non réorganisable

---

## Carte des fichiers

```
eldoria/
├── package.json                          ← MODIFY — +sortablejs
├── config.json                           ← MODIFY — +home_layout (défaut JSON)
├── config/rules.php                      ← MODIFY — règle validation home_layout
├── lang/fr/theme.php, lang/en/theme.php  ← MODIFY — nouvelles clés éditeur
├── assets/js/customizer.js               ← MODIFY — état + méthodes du mode réorganisation
├── assets/css/app.css                    ← MODIFY — CSS overlay mode réorganisation
├── views/
│   ├── home.blade.php                    ← MODIFY — Hero (inchangé) + boucle sur home_layout
│   └── partials/
│       ├── customizer.blade.php          ← MODIFY — 3ᵉ onglet "Disposition"
│       └── home/                         ← NEW dossier
│           ├── stats.blade.php
│           ├── join-steps.blade.php
│           ├── trailer.blade.php
│           ├── news.blade.php
│           ├── shop.blade.php
│           ├── vote.blade.php
│           ├── staff.blade.php
│           └── discord.blade.php
```

---

### Task 22 : Extraction en partials + modèle de données `home_layout`

**Files:**
- Create: `eldoria/views/partials/home/stats.blade.php`
- Create: `eldoria/views/partials/home/join-steps.blade.php`
- Create: `eldoria/views/partials/home/trailer.blade.php`
- Create: `eldoria/views/partials/home/news.blade.php`
- Create: `eldoria/views/partials/home/shop.blade.php`
- Create: `eldoria/views/partials/home/vote.blade.php`
- Create: `eldoria/views/partials/home/staff.blade.php`
- Create: `eldoria/views/partials/home/discord.blade.php`
- Modify: `eldoria/views/home.blade.php`
- Modify: `eldoria/config.json`
- Modify: `eldoria/config/rules.php`

**Interfaces:**
- Produces: chaque partial reçoit `$sectionData` (array avec au moins `key` et `visible`, plus `title`/`subtitle`/`steps` pour `join_steps` et `trailer`). `home.blade.php` calcule `$homeLayout` (array de 8 entrées, triées, avec repli par défaut) et `$trailerId`/`$homeServer` restent calculés une seule fois en tête du fichier (consommés par le Hero ET par `partials/home/trailer.blade.php` via héritage de portée Blade).

- [ ] **Step 1 : Créer `eldoria/views/partials/home/stats.blade.php`**

```blade
<section class="relative z-10 bg-bg-secondary border-y border-accent/20 py-8 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="stats" data-aos="fade-up">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-center items-center gap-8 sm:gap-16">

            @php
                $onlinePlayers = \Azuriom\Models\Server::where('home_display', true)->get()
                    ->sum(fn ($server) => $server->getOnlinePlayers());

                $monthlyVotes = class_exists('\Azuriom\Plugin\Vote\Models\Vote')
                    ? \Azuriom\Plugin\Vote\Models\Vote::where('created_at', '>', now()->startOfMonth())->count()
                    : 0;
            @endphp

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-online"
                     data-target="{{ $onlinePlayers }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">{{ __('theme::theme.home.stats_online') }}</div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-accent/20"></div>

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-votes"
                     data-target="{{ $monthlyVotes }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">{{ __('theme::theme.home.stats_votes') }}</div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-accent/20"></div>

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-members"
                     data-target="{{ \Azuriom\Models\User::count() }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">{{ __('theme::theme.home.stats_members') }}</div>
            </div>

        </div>
    </div>
</section>
```

- [ ] **Step 2 : Créer `eldoria/views/partials/home/join-steps.blade.php`**

```blade
<section class="py-24 px-4 max-w-5xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="join_steps" data-aos="fade-up">
    <h2 class="section-title">{{ $sectionData['title'] ?: __('theme::theme.home.join_steps_title') }}</h2>
    <p class="section-subtitle">{{ $sectionData['subtitle'] ?: __('theme::theme.home.join_steps_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="0">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">1</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][0]['title'] ?: __('theme::theme.home.join_step1_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][0]['text'] ?: __('theme::theme.home.join_step1_text') }}</p>
        </div>
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">2</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][1]['title'] ?: __('theme::theme.home.join_step2_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][1]['text'] ?: __('theme::theme.home.join_step2_text') }}</p>
        </div>
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">3</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][2]['title'] ?: __('theme::theme.home.join_step3_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][2]['text'] ?: __('theme::theme.home.join_step3_text') }}</p>
        </div>
    </div>
</section>
```

- [ ] **Step 3 : Créer `eldoria/views/partials/home/trailer.blade.php`**

```blade
<section class="py-24 px-4 max-w-5xl mx-auto {{ ($trailerId && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="trailer" data-live-section="trailer" data-aos="fade-up">
    <h2 class="section-title">{{ $sectionData['title'] ?: __('theme::theme.home.trailer_title') }}</h2>
    <p class="section-subtitle">{{ $sectionData['subtitle'] ?: __('theme::theme.home.trailer_subtitle') }}</p>

    <div class="card-eldoria overflow-hidden aspect-video">
        <iframe data-trailer-iframe
                src="{{ $trailerId ? 'https://www.youtube-nocookie.com/embed/'.$trailerId : '' }}"
                title="{{ __('theme::theme.home.trailer_iframe_title') }}"
                class="w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"></iframe>
    </div>
</section>
```

> `data-live-section="trailer"` reste nécessaire : c'est un mécanisme indépendant déjà en place (`liveTrailer()` dans `customizer.js`, onglet "Contenu") qui bascule la classe `hidden` pendant la saisie d'une URL de trailer, avant toute sauvegarde. Les deux mécanismes (celui-ci et le nouveau `$sectionData['visible']`) coexistent sans conflit : les deux ne font qu'ajouter/retirer la même classe `hidden`.

- [ ] **Step 4 : Créer `eldoria/views/partials/home/news.blade.php`**

```blade
@php $latestPosts = \Azuriom\Models\Post::published()->with('author')->latest('published_at')->take(3)->get(); @endphp
@if($latestPosts->isNotEmpty())
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="news" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.news_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.news_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach($latestPosts as $post)
        <div class="card-eldoria overflow-hidden flex flex-col group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            @if($post->hasImage())
                <div class="overflow-hidden">
                    <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}"
                         class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
            @endif
            <div class="p-6 flex flex-col flex-1">
                <h3 class="font-display text-text-primary font-semibold mb-2">
                    <a href="{{ route('posts.show', $post) }}" class="hover:text-accent transition-colors">{{ $post->title }}</a>
                </h3>
                <p class="text-text-secondary text-sm mb-4 flex-1 line-clamp-2">{{ Str::limit(strip_tags($post->content), 120) }}</p>
                <a href="{{ route('posts.show', $post) }}" class="btn-primary text-xs py-2 px-4 self-start">
                    {{ __('theme::theme.posts.read_more') }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center">
        <a href="{{ route('posts.index') }}" class="btn-primary">
            {{ __('theme::theme.home.news_see_all') }}
        </a>
    </div>
</section>
@endif
```

> `@if($latestPosts->isNotEmpty())` reste la garde englobante (déjà en v1.1) : sans article publié, la section n'existe pas du tout dans le DOM, donc n'apparaît pas non plus dans la liste réorganisable du mode édition (rien à afficher = rien à réorganiser). Ce n'est pas une régression, c'est la même règle qu'avant ce plan.

- [ ] **Step 5 : Créer `eldoria/views/partials/home/shop.blade.php`**

```blade
@if(class_exists('\Azuriom\Plugin\Shop\Models\Package'))
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="shop" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.shop_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.shop_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach(\Azuriom\Plugin\Shop\Models\Package::enabled()->with('category')->take(3)->get() as $package)
        <div class="card-eldoria p-6 group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            @if($package->hasImage())
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                     class="w-full h-40 object-cover rounded-sm mb-4 group-hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-40 bg-bg-primary/50 rounded-sm mb-4 flex items-center justify-center">
                    <span class="text-accent/30 text-4xl font-display">✦</span>
                </div>
            @endif
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $package->name }}</h3>
            <p class="text-text-secondary text-sm mb-4 line-clamp-2">{{ $package->short_description }}</p>
            <div class="flex items-center justify-between">
                <span class="text-accent font-display font-bold text-lg">{{ format_money($package->getPrice()) }}</span>
                <a href="{{ route('shop.packages.show', $package) }}" class="btn-primary text-xs py-2 px-4">
                    {{ __('theme::theme.home.buy') }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center">
        <a href="{{ route('shop.home') }}" class="btn-primary">
            {{ __('theme::theme.home.shop_see_all') }}
        </a>
    </div>
</section>
@endif
```

> L'ancien attribut `data-live-section="shop"` et la classe conditionnelle basée sur `theme_config('show_section_shop', ...)` sont retirés ici — `home_layout` les remplace entièrement (voir Task 25 pour le nettoyage des anciens contrôles correspondants dans le drawer).

- [ ] **Step 6 : Créer `eldoria/views/partials/home/vote.blade.php`**

```blade
@if(class_exists('\Azuriom\Plugin\Vote\Models\Site'))
<section class="py-24 bg-bg-secondary border-y border-accent/10 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="vote" data-aos="fade-up">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="section-title">{{ __('theme::theme.home.vote_title') }}</h2>
        <p class="section-subtitle">{{ __('theme::theme.home.vote_subtitle') }}</p>

        <div class="space-y-4">
            @foreach(\Azuriom\Plugin\Vote\Models\Site::enabled()->get() as $site)
            <div class="card-eldoria p-4 flex items-center justify-between gap-4" data-aos="fade-right" data-aos-delay="{{ $loop->index * 75 }}">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 flex items-center justify-center text-accent/40 font-display font-bold">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <div class="font-display text-text-primary text-sm font-semibold">{{ $site->name }}</div>
                        <div class="text-text-secondary text-xs">{{ __('theme::theme.home.vote_reward_generic') }}</div>
                    </div>
                </div>
                @auth
                    <a href="{{ $site->url }}" target="_blank" rel="noopener noreferrer"
                       data-vote-id="{{ $site->id }}"
                       data-vote-url="{{ route('vote.vote', $site) }}"
                       data-vote-time="{{ $site->getNextVoteTime(auth()->user(), request())?->valueOf() }}"
                       class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[40px] flex items-center gap-1.5">
                        ✦ {{ __('theme::theme.home.vote_cta') }}
                        <span class="vote-timer font-mono"></span>
                    </a>
                @else
                    <a href="{{ route('vote.home') }}"
                       class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[40px]">
                        ✦ {{ __('theme::theme.home.vote_cta') }}
                    </a>
                @endauth
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
```

- [ ] **Step 7 : Créer `eldoria/views/partials/home/staff.blade.php`**

```blade
@php
    $staffMembers = collect(range(1, 8))
        ->map(fn ($i) => [
            'name' => theme_config("staff_{$i}_name", ''),
            'role' => theme_config("staff_{$i}_role", ''),
            'link' => theme_config("staff_{$i}_link", ''),
        ])
        ->filter(fn ($member) => trim($member['name']) !== '');
@endphp
@if($staffMembers->isNotEmpty())
<section class="py-24 px-4 max-w-6xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="staff" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.staff_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.staff_subtitle') }}</p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($staffMembers as $member)
        <div class="card-eldoria p-4 text-center group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
            <div class="w-16 h-16 mx-auto mb-3 overflow-hidden rounded-sm">
                <img src="https://minotar.net/avatar/{{ urlencode($member['name']) }}/128"
                     alt="{{ $member['name'] }}"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
            </div>
            <div class="font-display text-text-primary text-sm font-semibold flex items-center justify-center gap-1.5">
                {{ $member['name'] }}
                @if($member['link'] !== '')
                    <a href="{{ $member['link'] }}" target="_blank" rel="noopener"
                       class="text-accent/60 hover:text-accent transition-colors"
                       title="{{ __('theme::theme.home.staff_link_title', ['name' => $member['name']]) }}">
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif
            </div>
            @if($member['role'] !== '')
                <div class="text-accent text-xs uppercase tracking-widest mt-1">{{ $member['role'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif
```

- [ ] **Step 8 : Créer `eldoria/views/partials/home/discord.blade.php`**

```blade
@php $discordServerId = theme_config('discord_server_id', '') ?? ''; @endphp
<section class="py-24 px-4 {{ ($discordServerId !== '' && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="discord" data-live-section="discord" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.discord_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.discord_subtitle') }}</p>

    <div class="max-w-md mx-auto card-eldoria p-4">
        <iframe data-discord-iframe
                src="{{ $discordServerId !== '' ? 'https://discord.com/widget?id='.$discordServerId.'&theme=dark' : '' }}"
                title="{{ __('theme::theme.home.discord_iframe_title') }}"
                width="100%" height="420"
                frameborder="0"
                sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                loading="lazy"></iframe>
    </div>
</section>
```

- [ ] **Step 9 : Réécrire `eldoria/views/home.blade.php`**

Remplacer tout le contenu du fichier par :
```blade
@extends('layouts.app')

@section('title', __('theme::theme.nav.home'))

@section('content')

<?php
    $homeServer = \Azuriom\Models\Server::where('home_display', true)->first();

    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/',
        theme_config('trailer_url', '') ?? '', $trailerMatch);
    $trailerId = $trailerMatch[1] ?? null;

    $defaultHomeLayout = [
        ['key' => 'stats', 'visible' => true],
        ['key' => 'join_steps', 'visible' => true, 'title' => '', 'subtitle' => '', 'steps' => [
            ['title' => '', 'text' => ''],
            ['title' => '', 'text' => ''],
            ['title' => '', 'text' => ''],
        ]],
        ['key' => 'trailer', 'visible' => true, 'title' => '', 'subtitle' => ''],
        ['key' => 'news', 'visible' => true],
        ['key' => 'shop', 'visible' => true],
        ['key' => 'vote', 'visible' => true],
        ['key' => 'staff', 'visible' => true],
        ['key' => 'discord', 'visible' => true],
    ];

    $expectedKeys = ['stats', 'join_steps', 'trailer', 'news', 'shop', 'vote', 'staff', 'discord'];
    $decodedLayout = json_decode(theme_config('home_layout', '') ?? '', true);

    $homeLayout = $defaultHomeLayout;
    if (is_array($decodedLayout)) {
        $decodedKeys = array_column($decodedLayout, 'key');
        sort($decodedKeys);
        $sortedExpected = $expectedKeys;
        sort($sortedExpected);
        if ($decodedKeys === $sortedExpected) {
            $homeLayout = $decodedLayout;
        }
    }
?>

{{-- ======= HERO ======= --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden" id="hero">

    <?php
        $heroVideoEnabled = theme_config('hero_video_enabled', '0') === '1' && $trailerId !== null;
    ?>

    {{-- Background image --}}
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat {{ $heroVideoEnabled ? 'hidden' : '' }}" id="hero-bg"
         data-default-image="{{ theme_asset('images/hero-default.svg') }}"
         style="background-image: url('{{ theme_config('hero_image') ?: theme_asset('images/hero-default.svg') }}')">
    </div>

    {{-- Fond vidéo (trailer YouTube, autoplay muet en boucle) --}}
    <div id="hero-video-container" class="absolute inset-0 overflow-hidden pointer-events-none {{ $heroVideoEnabled ? '' : 'hidden' }}">
        <iframe src="{{ $heroVideoEnabled ? 'https://www.youtube-nocookie.com/embed/'.$trailerId.'?autoplay=1&mute=1&loop=1&controls=0&playlist='.$trailerId.'&modestbranding=1&playsinline=1' : '' }}"
                title="{{ __('theme::theme.home.trailer_iframe_title') }}"
                class="absolute top-1/2 left-1/2 w-[177.78vh] min-w-full h-[56.25vw] min-h-full -translate-x-1/2 -translate-y-1/2"
                frameborder="0"
                allow="autoplay; encrypted-media"
                loading="lazy"></iframe>
    </div>

    {{-- Overlay dégradé --}}
    <div class="absolute inset-0 bg-gradient-to-b from-bg-primary/60 via-bg-primary/40 to-bg-primary"></div>

    {{-- Contenu hero --}}
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

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            {{-- Bouton Rejoindre avec pulse --}}
            @if($homeServer)
                <div class="flex items-center gap-2">
                    <span id="server-status-dot"
                          data-online-label="{{ __('theme::theme.home.server_online') }}"
                          data-offline-label="{{ __('theme::theme.home.server_offline') }}"
                          title="{{ __('theme::theme.home.server_online') }}"
                          class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                    <button onclick="navigator.clipboard.writeText('{{ $homeServer->fullAddress() }}')"
                            class="btn-primary relative group min-w-[180px] min-h-[48px]" id="btn-join">
                        <span class="absolute inset-0 rounded-sm animate-ping opacity-30 bg-accent"></span>
                        <span class="relative">{{ __('theme::theme.home.join') }}</span>
                        <span class="relative ml-2 text-xs font-mono opacity-70">{{ $homeServer->fullAddress() }}</span>
                    </button>
                </div>
            @endif

            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center px-6 py-3 min-h-[48px] border border-accent/40
                      text-text-primary font-display text-sm tracking-widest uppercase
                      hover:border-accent hover:text-accent transition-all duration-300 rounded-sm">
                {{ __('theme::theme.home.register') }}
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

{{-- ======= SECTIONS RÉORGANISABLES ======= --}}
@foreach($homeLayout as $sectionData)
    @include('partials.home.' . str_replace('_', '-', $sectionData['key']), ['sectionData' => $sectionData])
@endforeach

@auth
<script>window.eldoriaVoteUsername = @json(auth()->user()->name);</script>
@endauth

@endsection
```

> **Note sur le repli JSON invalide :** la comparaison `$decodedKeys === $sortedExpected` (deux tableaux triés comparés strictement) vérifie que le JSON décodé contient exactement les 8 clés attendues, ni plus ni moins, sans imposer leur ordre initial dans le JSON (puisque c'est justement l'ordre du tableau — pas des clés triées — qui porte l'ordre d'affichage). Si la structure ne correspond pas (JSON corrompu, clé manquante, thème mis à jour avec une 9ᵉ section future), `$homeLayout` retombe intégralement sur `$defaultHomeLayout`.

- [ ] **Step 10 : Ajouter `home_layout` à `eldoria/config.json`**

Ajouter avant la clé `staff_1_name` (ou n'importe où dans l'objet racine) :
```json
    "home_layout": "",
```

- [ ] **Step 11 : Ajouter la règle de validation dans `eldoria/config/rules.php`**

Ajouter avant le `];` final :
```php
    'home_layout' => ['nullable', 'json', 'max:5000'],
```

- [ ] **Step 12 : Build + vérification**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Ouvrir `http://127.0.0.1:8000/` : la page doit s'afficher **à l'identique** de son état avant ce plan (même ordre de sections, mêmes titres, aucune section masquée). C'est le test de non-régression le plus important de cette tâche — vérifier chaque section (Stats, Comment nous rejoindre, Trailer si configuré, Actus si articles, Boutique, Vote, Staff si configuré, Discord si configuré) apparaît dans le même ordre qu'avant. Vérifier `storage/logs/laravel-*.log` pour toute erreur PHP (variable indéfinie, etc. — les 8 partials utilisent tous `$sectionData`, qui doit être passé par chaque `@include`).

- [ ] **Step 13 : Commit**

```bash
git add eldoria/views/partials/home/ eldoria/views/home.blade.php eldoria/config.json eldoria/config/rules.php
git commit -m "feat(eldoria): extraction des sections homepage en partials + modèle home_layout"
```

---

### Task 23 : SortableJS + mode réorganisation (glisser-déposer, montrer/masquer)

**Files:**
- Modify: `eldoria/package.json`
- Modify: `eldoria/assets/js/customizer.js`
- Modify: `eldoria/assets/css/app.css`
- Modify: `eldoria/views/partials/customizer.blade.php`

**Interfaces:**
- Consumes: `[data-section-key]` sur chaque section (Task 22)
- Produces: état Alpine `activeTab === 'layout'` active un mode réorganisation ; `homeLayout` (array JS, source de vérité pour l'ordre/visibilité/texte) devient disponible pour la Task 24 (édition de texte) et la Task 25 (sauvegarde)

- [ ] **Step 1 : Ajouter la dépendance dans `eldoria/package.json`**

Dans la section `"dependencies"`, ajouter après `"gsap"` (ordre alphabétique non requis, juste une ligne de plus) :
```json
    "sortablejs": "^1.15.2",
```

- [ ] **Step 2 : Installer et vérifier**

```bash
cd eldoria && npm install
```
Attendu : `node_modules/sortablejs` créé, `package-lock.json` mis à jour.

- [ ] **Step 3 : Ajouter le CSS du mode réorganisation dans `eldoria/assets/css/app.css`**

Ajouter à la fin du fichier (après la règle `@media (prefers-reduced-motion: reduce)` existante) :
```css

/* Mode réorganisation de la homepage (admin) — overlay affiché uniquement
   quand body.reorder-mode est actif (voir customizer.js, onglet Disposition). */
[data-section-key] {
    position: relative;
}

.reorder-overlay {
    display: none;
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    z-index: 40;
    gap: 0.5rem;
}

body.reorder-mode .reorder-overlay {
    display: flex;
}

body.reorder-mode [data-section-key] {
    outline: 1px dashed color-mix(in srgb, var(--color-accent) 40%, transparent);
    outline-offset: 4px;
}

.reorder-overlay button {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-accent);
    border-radius: 0.25rem;
    color: var(--color-accent);
    cursor: pointer;
}

.reorder-overlay .drag-handle {
    cursor: grab;
}

.reorder-overlay .drag-handle:active {
    cursor: grabbing;
}

.reorder-overlay button.section-hidden-indicator {
    opacity: 0.5;
}

.sortable-ghost {
    opacity: 0.4;
}
```

- [ ] **Step 4 : Ajouter la barre d'outils overlay à chacun des 8 partials**

Dans **chacun** des 8 fichiers créés en Task 22 (`stats.blade.php`, `join-steps.blade.php`, `trailer.blade.php`, `news.blade.php`, `shop.blade.php`, `vote.blade.php`, `staff.blade.php`, `discord.blade.php`), ajouter la barre d'outils juste après la balise `<section ...>` ouvrante (donc comme premier enfant de chaque section). Exemple pour `stats.blade.php` — remplacer :
```blade
<section class="relative z-10 bg-bg-secondary border-y border-accent/20 py-8 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="stats" data-aos="fade-up">
    <div class="max-w-7xl mx-auto px-4">
```
par :
```blade
<section class="relative z-10 bg-bg-secondary border-y border-accent/20 py-8 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="stats" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <div class="max-w-7xl mx-auto px-4">
```

Appliquer la même insertion (le même bloc `@auth @if(auth()->user()->isAdmin()) @include(...) @endif @endauth`, inséré juste après la ligne `<section ...>` et avant le premier contenu existant) dans chacun des 7 autres partials :

`join-steps.blade.php` — remplacer :
```blade
<section class="py-24 px-4 max-w-5xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="join_steps" data-aos="fade-up">
    <h2 class="section-title">
```
par :
```blade
<section class="py-24 px-4 max-w-5xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="join_steps" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">
```

`trailer.blade.php` — remplacer :
```blade
<section class="py-24 px-4 max-w-5xl mx-auto {{ ($trailerId && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="trailer" data-live-section="trailer" data-aos="fade-up">
    <h2 class="section-title">
```
par :
```blade
<section class="py-24 px-4 max-w-5xl mx-auto {{ ($trailerId && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="trailer" data-live-section="trailer" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">
```

`news.blade.php` — remplacer :
```blade
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="news" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.news_title') }}</h2>
```
par :
```blade
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="news" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ __('theme::theme.home.news_title') }}</h2>
```

`shop.blade.php` — remplacer :
```blade
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="shop" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.shop_title') }}</h2>
```
par :
```blade
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="shop" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ __('theme::theme.home.shop_title') }}</h2>
```

`vote.blade.php` — remplacer :
```blade
<section class="py-24 bg-bg-secondary border-y border-accent/10 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="vote" data-aos="fade-up">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="section-title">{{ __('theme::theme.home.vote_title') }}</h2>
```
par :
```blade
<section class="py-24 bg-bg-secondary border-y border-accent/10 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="vote" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="section-title">{{ __('theme::theme.home.vote_title') }}</h2>
```

`staff.blade.php` — remplacer :
```blade
<section class="py-24 px-4 max-w-6xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="staff" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.staff_title') }}</h2>
```
par :
```blade
<section class="py-24 px-4 max-w-6xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="staff" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ __('theme::theme.home.staff_title') }}</h2>
```

`discord.blade.php` — remplacer :
```blade
<section class="py-24 px-4 {{ ($discordServerId !== '' && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="discord" data-live-section="discord" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.discord_title') }}</h2>
```
par :
```blade
<section class="py-24 px-4 {{ ($discordServerId !== '' && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="discord" data-live-section="discord" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ __('theme::theme.home.discord_title') }}</h2>
```

Créer le fichier partagé `eldoria/views/partials/home/_reorder-toolbar.blade.php` :
```blade
<div class="reorder-overlay">
    <button type="button" class="drag-handle" title="{{ __('theme::theme.customizer.layout_drag_title') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
        </svg>
    </button>
    <button type="button" class="section-visibility-toggle" title="{{ __('theme::theme.customizer.layout_toggle_title') }}">
        <svg class="w-5 h-5 eye-visible" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <svg class="w-5 h-5 eye-hidden hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 012.132-3.411m3.712-2.107A9.981 9.981 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.132 5.411M3 3l18 18"/>
        </svg>
    </button>
</div>
```

> Ce partial partagé n'a pas besoin de connaître la clé de la section : le JS (Step 6) parcourt chaque `[data-section-key]` du conteneur et cherche ses boutons `.section-visibility-toggle`/`.edit-text-toggle` comme descendants (`section.querySelector(...)`) — jamais l'inverse, donc aucun `closest()` n'est nécessaire.

- [ ] **Step 5 : Ajouter l'onglet "Disposition" dans `eldoria/views/partials/customizer.blade.php`**

Remplacer :
```blade
        {{-- Tabs --}}
        <div class="flex border-b border-accent/10">
            <button @click="activeTab = 'colors'"
                    :class="activeTab === 'colors' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                {{ __('theme::theme.customizer.tab_colors') }}
            </button>
            <button @click="activeTab = 'content'"
                    :class="activeTab === 'content' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                {{ __('theme::theme.customizer.tab_content') }}
            </button>
        </div>
```
par :
```blade
        {{-- Tabs --}}
        <div class="flex border-b border-accent/10">
            <button @click="activeTab = 'colors'"
                    :class="activeTab === 'colors' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                {{ __('theme::theme.customizer.tab_colors') }}
            </button>
            <button @click="activeTab = 'content'"
                    :class="activeTab === 'content' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                {{ __('theme::theme.customizer.tab_content') }}
            </button>
            <button @click="enterLayoutTab()"
                    :class="activeTab === 'layout' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                {{ __('theme::theme.customizer.tab_layout') }}
            </button>
        </div>
```

Ajouter le corps de l'onglet "Disposition", juste après la fermeture du `</div>` du bloc `{{-- TAB CONTENU --}}` (avant le `</div>` qui ferme `{{-- Body scrollable --}}`) :
```blade
            {{-- TAB DISPOSITION --}}
            <div x-show="activeTab === 'layout'" class="space-y-6">
                <p class="text-text-secondary text-sm leading-relaxed">
                    {{ __('theme::theme.customizer.layout_instructions') }}
                </p>
            </div>
```

- [ ] **Step 6 : Ajouter l'état et les méthodes du mode réorganisation dans `eldoria/assets/js/customizer.js`**

Remplacer :
```js
export function customizerComponent(initial = {}) {
    return {
        open: false,
        saving: false,
        saved: false,
        saveError: false,
        saveErrorMessage: '',
        activeTab: 'colors',
```
par :
```js
export function customizerComponent(initial = {}) {
    return {
        open: false,
        saving: false,
        saved: false,
        saveError: false,
        saveErrorMessage: '',
        activeTab: 'colors',
        sortableInstance: null,
```

Ajouter les nouvelles méthodes juste après la méthode `cancel()` existante, avant la fermeture finale de l'objet retourné (avant le dernier `}` qui ferme le `return { ... }`). Remplacer :
```js
        cancel() {
            // Recharger la page pour revenir à l'état sauvegardé
            window.location.reload()
        }
    }
}
```
par :
```js
        cancel() {
            // Recharger la page pour revenir à l'état sauvegardé
            window.location.reload()
        },

        // ===== Mode réorganisation de la homepage (onglet Disposition) =====

        init() {
            // body.reorder-mode ne doit exister QUE pendant que l'onglet Disposition
            // est actif ET le drawer ouvert — jamais laissé "collé" après un changement
            // d'onglet ou une fermeture du drawer.
            this.$watch('activeTab', (value) => {
                document.body.classList.toggle('reorder-mode', value === 'layout')
            })
            this.$watch('open', (value) => {
                if (!value) document.body.classList.remove('reorder-mode')
            })
        },

        enterLayoutTab() {
            this.activeTab = 'layout'
            this.$nextTick(() => this.initSortable())
        },

        initSortable() {
            const container = this.findSectionsContainer()
            if (!container || this.sortableInstance) return

            this.sortableInstance = Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: () => {},
            })

            container.querySelectorAll('[data-section-key]').forEach((section) => {
                const toggle = section.querySelector('.section-visibility-toggle')
                if (!toggle || toggle.dataset.bound) return
                toggle.dataset.bound = 'true'

                toggle.addEventListener('click', () => {
                    const isHidden = section.classList.toggle('section-manually-hidden')
                    toggle.querySelector('.eye-visible').classList.toggle('hidden', isHidden)
                    toggle.querySelector('.eye-hidden').classList.toggle('hidden', !isHidden)
                })
            })
        },

        findSectionsContainer() {
            const firstSection = document.querySelector('[data-section-key]')
            return firstSection ? firstSection.parentElement : null
        },
    }
}
```

> **`section-manually-hidden` vs `hidden` :** le mode réorganisation utilise une classe distincte (`section-manually-hidden`) plutôt que de toucher directement à `hidden` — car `hidden` peut déjà être positionnée par des règles serveur indépendantes (pas de posts, plugin absent, pas d'ID Discord — voir Task 22 Step 4/8/9). Basculer `section-manually-hidden` en overlay ne doit jamais RÉVÉLER une section qui n'a de toute façon aucun contenu à afficher. La Task 24 (sauvegarde) lira `section-manually-hidden` comme la valeur de `visible` à envoyer au serveur (absent = visible, présent = masqué) — indépendamment de l'état de `hidden` piloté par le contenu.

- [ ] **Step 7 : Ajouter l'import de SortableJS en tête de `eldoria/assets/js/customizer.js`**

Remplacer la première ligne du fichier :
```js
const PALETTES = [
```
par :
```js
import Sortable from 'sortablejs'

const PALETTES = [
```

- [ ] **Step 8 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Se connecter en admin, ouvrir le customizer, cliquer sur l'onglet "Disposition" : les 8 sections de la page doivent afficher une petite barre d'outils (poignée + œil) en haut à droite, avec un contour pointillé doré autour de chaque section. Glisser une section : elle doit changer de position visuellement. Cliquer sur l'œil d'une section : l'icône doit basculer œil-barré, et si on clique une seconde fois, refermer le mode Disposition puis le rouvrir doit montrer l'état correct (persistance en mémoire tant que la page n'est pas rechargée). Vérifier `storage/logs/laravel-*.log` pour toute erreur.

- [ ] **Step 9 : Commit**

```bash
git add eldoria/package.json eldoria/package-lock.json eldoria/assets/js/customizer.js eldoria/assets/css/app.css eldoria/views/partials/customizer.blade.php eldoria/views/partials/home/
git commit -m "feat(eldoria): glisser-déposer et bascule visibilité des sections homepage (SortableJS)"
```

---

### Task 24 : Édition de texte en direct (Comment-nous-rejoindre et Trailer)

**Files:**
- Modify: `eldoria/assets/js/customizer.js`
- Modify: `eldoria/views/partials/home/_reorder-toolbar.blade.php`
- Modify: `eldoria/views/partials/customizer.blade.php`

**Interfaces:**
- Consumes: mode réorganisation actif (Task 23)
- Produces: état Alpine `editingSection` (`'join_steps' | 'trailer' | null`) et `sectionTextOverrides` (objet contenant les surcharges de texte en cours d'édition), consommés par la Task 25 (sauvegarde)

- [ ] **Step 1 : Ajouter le bouton crayon conditionnel dans `_reorder-toolbar.blade.php`**

Remplacer tout le contenu du fichier par :
```blade
<div class="reorder-overlay">
    @if(in_array($sectionData['key'] ?? '', ['join_steps', 'trailer']))
        <button type="button" class="edit-text-toggle" title="{{ __('theme::theme.customizer.layout_edit_title') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </button>
    @endif
    <button type="button" class="drag-handle" title="{{ __('theme::theme.customizer.layout_drag_title') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
        </svg>
    </button>
    <button type="button" class="section-visibility-toggle" title="{{ __('theme::theme.customizer.layout_toggle_title') }}">
        <svg class="w-5 h-5 eye-visible" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <svg class="w-5 h-5 eye-hidden hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 012.132-3.411m3.712-2.107A9.981 9.981 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.132 5.411M3 3l18 18"/>
        </svg>
    </button>
</div>
```

> `$sectionData['key']` est déjà disponible dans ce partial car Blade passe le contexte du fichier appelant (`stats.blade.php`, etc.) aux `@include` qu'il contient lui-même — chaque partial parent a déjà `$sectionData` dans sa propre portée au moment où il inclut `_reorder-toolbar.blade.php`.

- [ ] **Step 2 : Ajouter l'état et les méthodes d'édition de texte dans `eldoria/assets/js/customizer.js`**

Remplacer :
```js
        // Contenu éditable — initialisé depuis la config serveur (voir customizer.blade.php)
        slogan: initial.slogan ?? '',
```
par :
```js
        // Contenu éditable — initialisé depuis la config serveur (voir customizer.blade.php)
        editingSection: null,
        sectionTextOverrides: initial.sectionTextOverrides ?? {
            join_steps: { title: '', subtitle: '', steps: [{ title: '', text: '' }, { title: '', text: '' }, { title: '', text: '' }] },
            trailer: { title: '', subtitle: '' },
        },
        slogan: initial.slogan ?? '',
```

Ajouter les méthodes d'édition juste après `initSortable()` (avant `findSectionsContainer()`). Remplacer :
```js
            container.querySelectorAll('[data-section-key]').forEach((section) => {
                const toggle = section.querySelector('.section-visibility-toggle')
                if (!toggle || toggle.dataset.bound) return
                toggle.dataset.bound = 'true'

                toggle.addEventListener('click', () => {
                    const isHidden = section.classList.toggle('section-manually-hidden')
                    toggle.querySelector('.eye-visible').classList.toggle('hidden', isHidden)
                    toggle.querySelector('.eye-hidden').classList.toggle('hidden', !isHidden)
                })
            })
        },
```
par :
```js
            container.querySelectorAll('[data-section-key]').forEach((section) => {
                const toggle = section.querySelector('.section-visibility-toggle')
                if (toggle && !toggle.dataset.bound) {
                    toggle.dataset.bound = 'true'
                    toggle.addEventListener('click', () => {
                        const isHidden = section.classList.toggle('section-manually-hidden')
                        toggle.querySelector('.eye-visible').classList.toggle('hidden', isHidden)
                        toggle.querySelector('.eye-hidden').classList.toggle('hidden', !isHidden)
                    })
                }

                const editBtn = section.querySelector('.edit-text-toggle')
                if (editBtn && !editBtn.dataset.bound) {
                    editBtn.dataset.bound = 'true'
                    editBtn.addEventListener('click', () => {
                        this.editingSection = section.dataset.sectionKey
                    })
                }
            })
        },

        backToLayoutList() {
            this.editingSection = null
        },

        liveJoinStepsText() {
            const o = this.sectionTextOverrides.join_steps
            const section = document.querySelector('[data-section-key="join_steps"]')
            if (!section) return

            const titleEl = section.querySelector('.section-title')
            const subtitleEl = section.querySelector('.section-subtitle')
            if (titleEl) titleEl.textContent = o.title || titleEl.dataset.defaultText
            if (subtitleEl) subtitleEl.textContent = o.subtitle || subtitleEl.dataset.defaultText

            const stepCards = section.querySelectorAll('.card-eldoria')
            o.steps.forEach((step, i) => {
                const card = stepCards[i]
                if (!card) return
                const stepTitleEl = card.querySelector('h3')
                const stepTextEl = card.querySelector('p')
                if (stepTitleEl) stepTitleEl.textContent = step.title || stepTitleEl.dataset.defaultText
                if (stepTextEl) stepTextEl.textContent = step.text || stepTextEl.dataset.defaultText
            })
        },

        liveTrailerSectionText() {
            const o = this.sectionTextOverrides.trailer
            const section = document.querySelector('[data-section-key="trailer"]')
            if (!section) return

            const titleEl = section.querySelector('.section-title')
            const subtitleEl = section.querySelector('.section-subtitle')
            if (titleEl) titleEl.textContent = o.title || titleEl.dataset.defaultText
            if (subtitleEl) subtitleEl.textContent = o.subtitle || subtitleEl.dataset.defaultText
        },
```

- [ ] **Step 3 : Stocker le texte par défaut sur chaque élément pour permettre le repli live**

Modifier `eldoria/views/partials/home/join-steps.blade.php` : remplacer chaque titre/texte pour y ajouter `data-default-text` avec la valeur i18n brute (nécessaire pour que `liveJoinStepsText()` puisse revenir au texte par défaut quand un champ est vidé). Remplacer :
```blade
    <h2 class="section-title">{{ $sectionData['title'] ?: __('theme::theme.home.join_steps_title') }}</h2>
    <p class="section-subtitle">{{ $sectionData['subtitle'] ?: __('theme::theme.home.join_steps_subtitle') }}</p>
```
par :
```blade
    <h2 class="section-title" data-default-text="{{ __('theme::theme.home.join_steps_title') }}">{{ $sectionData['title'] ?: __('theme::theme.home.join_steps_title') }}</h2>
    <p class="section-subtitle" data-default-text="{{ __('theme::theme.home.join_steps_subtitle') }}">{{ $sectionData['subtitle'] ?: __('theme::theme.home.join_steps_subtitle') }}</p>
```

Et pour chacune des 3 étapes, remplacer (exemple étape 1) :
```blade
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][0]['title'] ?: __('theme::theme.home.join_step1_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][0]['text'] ?: __('theme::theme.home.join_step1_text') }}</p>
```
par :
```blade
            <h3 class="font-display text-text-primary font-semibold mb-2" data-default-text="{{ __('theme::theme.home.join_step1_title') }}">{{ $sectionData['steps'][0]['title'] ?: __('theme::theme.home.join_step1_title') }}</h3>
            <p class="text-text-secondary text-sm" data-default-text="{{ __('theme::theme.home.join_step1_text') }}">{{ $sectionData['steps'][0]['text'] ?: __('theme::theme.home.join_step1_text') }}</p>
```
(idem pour les étapes 2 et 3 avec leurs clés `join_step2_*`/`join_step3_*` respectives).

Modifier `eldoria/views/partials/home/trailer.blade.php` de la même façon. Remplacer :
```blade
    <h2 class="section-title">{{ $sectionData['title'] ?: __('theme::theme.home.trailer_title') }}</h2>
    <p class="section-subtitle">{{ $sectionData['subtitle'] ?: __('theme::theme.home.trailer_subtitle') }}</p>
```
par :
```blade
    <h2 class="section-title" data-default-text="{{ __('theme::theme.home.trailer_title') }}">{{ $sectionData['title'] ?: __('theme::theme.home.trailer_title') }}</h2>
    <p class="section-subtitle" data-default-text="{{ __('theme::theme.home.trailer_subtitle') }}">{{ $sectionData['subtitle'] ?: __('theme::theme.home.trailer_subtitle') }}</p>
```

- [ ] **Step 4 : Ajouter les sous-panneaux d'édition dans `eldoria/views/partials/customizer.blade.php`**

Remplacer :
```blade
            {{-- TAB DISPOSITION --}}
            <div x-show="activeTab === 'layout'" class="space-y-6">
                <p class="text-text-secondary text-sm leading-relaxed">
                    {{ __('theme::theme.customizer.layout_instructions') }}
                </p>
            </div>
```
par :
```blade
            {{-- TAB DISPOSITION --}}
            <div x-show="activeTab === 'layout' && !editingSection" class="space-y-6">
                <p class="text-text-secondary text-sm leading-relaxed">
                    {{ __('theme::theme.customizer.layout_instructions') }}
                </p>
            </div>

            {{-- SOUS-PANNEAU ÉDITION : COMMENT NOUS REJOINDRE --}}
            <div x-show="activeTab === 'layout' && editingSection === 'join_steps'" class="space-y-4">
                <button @click="backToLayoutList()" class="text-accent text-xs uppercase tracking-widest mb-2">
                    ← {{ __('theme::theme.customizer.layout_back') }}
                </button>
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.layout_field_title') }}</label>
                    <input type="text" x-model="sectionTextOverrides.join_steps.title" @input="liveJoinStepsText()"
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[40px]">
                </div>
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.layout_field_subtitle') }}</label>
                    <input type="text" x-model="sectionTextOverrides.join_steps.subtitle" @input="liveJoinStepsText()"
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[40px]">
                </div>
                <template x-for="(step, index) in sectionTextOverrides.join_steps.steps" :key="index">
                    <div class="border-t border-accent/10 pt-4">
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" x-text="'{{ __('theme::theme.customizer.layout_field_step') }} ' + (index + 1)"></label>
                        <input type="text" x-model="step.title" @input="liveJoinStepsText()"
                               class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[40px] mb-2">
                        <textarea x-model="step.text" @input="liveJoinStepsText()" rows="2"
                                  class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm resize-none"></textarea>
                    </div>
                </template>
            </div>

            {{-- SOUS-PANNEAU ÉDITION : TRAILER --}}
            <div x-show="activeTab === 'layout' && editingSection === 'trailer'" class="space-y-4">
                <button @click="backToLayoutList()" class="text-accent text-xs uppercase tracking-widest mb-2">
                    ← {{ __('theme::theme.customizer.layout_back') }}
                </button>
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.layout_field_title') }}</label>
                    <input type="text" x-model="sectionTextOverrides.trailer.title" @input="liveTrailerSectionText()"
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[40px]">
                </div>
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.layout_field_subtitle') }}</label>
                    <input type="text" x-model="sectionTextOverrides.trailer.subtitle" @input="liveTrailerSectionText()"
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[40px]">
                </div>
            </div>
```

- [ ] **Step 5 : Initialiser `sectionTextOverrides` depuis la config serveur**

Modifier l'initialisation du composant Alpine dans `eldoria/views/partials/customizer.blade.php`. Remplacer :
```blade
<div x-data="customizer({
        slogan: @js(theme_config('hero_slogan', '')),
```
par :
```blade
<?php
    $decodedHomeLayoutForJs = json_decode(theme_config('home_layout', '') ?? '', true);
    $joinStepsData = collect($decodedHomeLayoutForJs ?? [])->firstWhere('key', 'join_steps');
    $trailerSectionData = collect($decodedHomeLayoutForJs ?? [])->firstWhere('key', 'trailer');
?>
<div x-data="customizer({
        sectionTextOverrides: {
            join_steps: @js([
                'title' => $joinStepsData['title'] ?? '',
                'subtitle' => $joinStepsData['subtitle'] ?? '',
                'steps' => $joinStepsData['steps'] ?? [['title' => '', 'text' => ''], ['title' => '', 'text' => ''], ['title' => '', 'text' => '']],
            ]),
            trailer: @js([
                'title' => $trailerSectionData['title'] ?? '',
                'subtitle' => $trailerSectionData['subtitle'] ?? '',
            ]),
        },
        slogan: @js(theme_config('hero_slogan', '')),
```

- [ ] **Step 6 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

En admin, ouvrir le customizer → "Disposition" → cliquer sur le crayon de "Comment nous rejoindre" : le drawer doit basculer vers le formulaire à 8 champs. Taper un nouveau titre : le titre de la section sur la page doit changer en direct, sans recharger. Vider le champ : le titre par défaut traduit doit réapparaître. Cliquer "← Retour" : revient à la liste "Disposition". Répéter pour "Trailer" (2 champs). Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 7 : Commit**

```bash
git add eldoria/assets/js/customizer.js eldoria/views/partials/customizer.blade.php eldoria/views/partials/home/
git commit -m "feat(eldoria): édition de texte en direct pour comment-nous-rejoindre et trailer"
```

---

### Task 25 : Sauvegarde de `home_layout` + nettoyage des anciens toggles

**Files:**
- Modify: `eldoria/assets/js/customizer.js`
- Modify: `eldoria/views/partials/customizer.blade.php`

**Interfaces:**
- Consumes: état DOM du mode réorganisation (Task 23), `sectionTextOverrides` (Task 24)
- Produces: `home_layout` persisté côté serveur via `save()`

- [ ] **Step 1 : Retirer les anciens toggles "Sections visibles" (remplacés par `home_layout`)**

Dans `eldoria/views/partials/customizer.blade.php`, supprimer entièrement le bloc (dans l'onglet Contenu) :
```blade
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-3">{{ __('theme::theme.customizer.sections_visible') }}</label>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-text-primary text-sm">{{ __('theme::theme.customizer.section_shop') }}</span>
                            <input type="checkbox" x-model="showShop" @change="liveSection('shop', showShop)"
                                   class="w-4 h-4 accent-[var(--color-accent)]">
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-primary text-sm">{{ __('theme::theme.customizer.section_vote') }}</span>
                            <input type="checkbox" x-model="showVote" @change="liveSection('vote', showVote)"
                                   class="w-4 h-4 accent-[var(--color-accent)]">
                        </div>
                    </div>
                </div>

```
(supprimer intégralement, y compris la ligne vide qui suit).

Dans `eldoria/assets/js/customizer.js`, retirer `showShop`/`showVote` de l'état initial et des méthodes désormais inutiles. Remplacer :
```js
        showShop: initial.showShop ?? true,
        showVote: initial.showVote ?? true,
        trailerUrl: initial.trailerUrl ?? '',
```
par :
```js
        trailerUrl: initial.trailerUrl ?? '',
```

> **Ne pas toucher à la méthode `liveSection()` elle-même** (celle qui fait `document.querySelectorAll('[data-live-section=...]')...`) — elle reste utilisée par `liveTrailer()` et `liveDiscord()` (via `this.liveSection('trailer', ...)`/`this.liveSection('discord', ...)`). Le seul changement de cette étape est la suppression des lignes `showShop`/`showVote` de l'état initial ci-dessus — les deux appels `@change="liveSection('shop'/'vote', ...)"` disparaissent déjà avec la suppression du bloc HTML "Sections visibles" (voir plus haut), aucune autre édition JS n'est nécessaire à cet endroit.

Dans `customizer.blade.php`, retirer aussi les deux clés d'initialisation devenues inutiles. Remplacer :
```blade
        heroVideoEnabled: @js(theme_config('hero_video_enabled', '0') === '1'),
        showShop: @js(theme_config('show_section_shop', '1') === '1'),
        showVote: @js(theme_config('show_section_vote', '1') === '1'),
        trailerUrl: @js(theme_config('trailer_url', '') ?? ''),
```
par :
```blade
        heroVideoEnabled: @js(theme_config('hero_video_enabled', '0') === '1'),
        trailerUrl: @js(theme_config('trailer_url', '') ?? ''),
```

- [ ] **Step 2 : Retirer l'envoi des anciens champs dans `save()`**

Dans `eldoria/assets/js/customizer.js`, remplacer :
```js
                formData.append('hero_image', this.heroImage)
                formData.append('show_section_shop', this.showShop ? '1' : '0')
                formData.append('show_section_vote', this.showVote ? '1' : '0')
                formData.append('trailer_url', this.trailerUrl)
```
par :
```js
                formData.append('hero_image', this.heroImage)
                formData.append('trailer_url', this.trailerUrl)
```

> Les clés `show_section_shop`/`show_section_vote` restent valides dans `config/rules.php` (`required|in:0,1`) pour la compatibilité ascendante d'une install existante déjà sauvegardée avec ces clés — mais puisque le formulaire ne les envoie plus et que la sauvegarde utilise le mode `append` (fusion), leur valeur existante en base est simplement conservée telle quelle sans jamais être mise à jour ni lue. Aucune action corrective nécessaire.

- [ ] **Step 3 : Construire et envoyer `home_layout` dans `save()`**

Remplacer :
```js
        async save() {
            this.saving = true
            try {
                const formData = new FormData()
```
par :
```js
        buildHomeLayoutPayload() {
            const container = this.findSectionsContainer()
            if (!container) return null

            return Array.from(container.querySelectorAll('[data-section-key]')).map((section) => {
                const key = section.dataset.sectionKey
                const visible = !section.classList.contains('section-manually-hidden')
                const entry = { key, visible }

                if (key === 'join_steps') {
                    entry.title = this.sectionTextOverrides.join_steps.title
                    entry.subtitle = this.sectionTextOverrides.join_steps.subtitle
                    entry.steps = this.sectionTextOverrides.join_steps.steps
                }

                if (key === 'trailer') {
                    entry.title = this.sectionTextOverrides.trailer.title
                    entry.subtitle = this.sectionTextOverrides.trailer.subtitle
                }

                return entry
            })
        },

        async save() {
            this.saving = true
            try {
                const formData = new FormData()
```

Remplacer :
```js
                formData.append('footer_discord', this.footerDiscord)
                formData.append('footer_twitter', this.footerTwitter)

                const response = await fetch(this.$root.dataset.saveUrl, {
```
par :
```js
                formData.append('footer_discord', this.footerDiscord)
                formData.append('footer_twitter', this.footerTwitter)

                const homeLayoutPayload = this.buildHomeLayoutPayload()
                if (homeLayoutPayload !== null) {
                    formData.append('home_layout', JSON.stringify(homeLayoutPayload))
                }

                const response = await fetch(this.$root.dataset.saveUrl, {
```

> **Pourquoi `home_layout` n'est envoyé que si les sections existent dans le DOM (`homeLayoutPayload !== null`) :** sur une page qui n'est pas la homepage (si jamais le drawer customizer était un jour affiché ailleurs), `[data-section-key]` n'existe pas — dans ce cas, ne pas envoyer `home_layout` du tout, pour ne jamais écraser la disposition déjà enregistrée avec un JSON vide par erreur. Le mode `append` déjà en place fait que les clés absentes du formulaire restent inchangées côté serveur.

- [ ] **Step 4 : Build + vérification manuelle complète (bout en bout)**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

En admin :
1. Ouvrir le customizer → "Disposition".
2. Glisser la section "Boutique" pour la faire passer avant "Comment nous rejoindre".
3. Masquer la section "Discord" (icône œil).
4. Cliquer le crayon de "Comment nous rejoindre", changer le titre en "Rejoins-nous vite", revenir à la liste.
5. Cliquer "Enregistrer" — doit afficher "✓ Sauvegardé" (pas de message d'erreur).
6. Recharger la page (F5) : l'ordre doit refléter le changement (Boutique avant Comment nous rejoindre), Discord doit être masqué, le titre de "Comment nous rejoindre" doit afficher "Rejoins-nous vite".
7. Rouvrir le customizer → Disposition → remettre Discord visible, remettre l'ordre d'origine, vider le titre personnalisé de "Comment nous rejoindre" → Enregistrer → recharger → confirmer le retour à l'état par défaut (texte traduit, ordre initial, Discord visible si un ID est configuré).

Vérifier `storage/logs/laravel-*.log` à chaque étape.

- [ ] **Step 5 : Commit**

```bash
git add eldoria/assets/js/customizer.js eldoria/views/partials/customizer.blade.php
git commit -m "feat(eldoria): sauvegarde de la disposition homepage + nettoyage anciens toggles"
```

---

### Task 26 : Traductions + revue finale

**Files:**
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes: toutes les clés `theme::theme.customizer.layout_*` introduites en Tasks 23-24

- [ ] **Step 1 : Ajouter les clés dans `eldoria/lang/fr/theme.php`**

Dans la section `customizer`, ajouter après `'admin_link' => "Aller à l'admin",` (juste avant le `],` qui ferme le tableau `customizer`) :
```php
        'tab_layout' => 'Disposition',
        'layout_instructions' => 'Survole les sections de la page pour les réorganiser, les masquer, ou éditer leur texte (crayon).',
        'layout_drag_title' => 'Glisser pour réorganiser',
        'layout_toggle_title' => 'Afficher / masquer cette section',
        'layout_edit_title' => 'Éditer le texte de cette section',
        'layout_back' => 'Retour',
        'layout_field_title' => 'Titre',
        'layout_field_subtitle' => 'Sous-titre',
        'layout_field_step' => 'Étape',
```

- [ ] **Step 2 : Ajouter les clés dans `eldoria/lang/en/theme.php`**

Dans la section `customizer`, ajouter après `'admin_link' => 'Go to admin panel',` :
```php
        'tab_layout' => 'Layout',
        'layout_instructions' => 'Hover the page sections to reorder, hide, or edit their text (pencil icon).',
        'layout_drag_title' => 'Drag to reorder',
        'layout_toggle_title' => 'Show / hide this section',
        'layout_edit_title' => 'Edit this section\'s text',
        'layout_back' => 'Back',
        'layout_field_title' => 'Title',
        'layout_field_subtitle' => 'Subtitle',
        'layout_field_step' => 'Step',
```

- [ ] **Step 3 : Build final + vérification complète**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Ouvrir `http://127.0.0.1:8000/` en français puis en anglais (basculer via `php artisan tinker --execute="\Azuriom\Models\Setting::updateSettings('locale', 'en');"` puis `php artisan config:clear`, remettre `'fr'` ensuite) — confirmer que tous les nouveaux libellés du drawer ("Disposition", instructions, titres des champs) sont bien traduits dans les deux langues, sans clé brute `theme::theme.*` visible à l'écran.

Refaire le scénario complet de la Task 25 Step 4 une dernière fois pour confirmer que rien n'a régressé après l'ajout des traductions. Tester aussi en tant que **visiteur non-admin** (session déconnectée) : la page doit s'afficher normalement, sans aucune barre d'outils overlay visible (les gardes `@auth @if(auth()->user()->isAdmin())` doivent empêcher tout rendu de `_reorder-toolbar.blade.php` pour un visiteur).

- [ ] **Step 4 : Commit**

```bash
git add eldoria/lang/
git commit -m "feat(eldoria): traductions FR/EN de l'éditeur de disposition homepage"
```

---

## Notes pour l'implémentation

1. **Ordre d'exécution strict.** Chaque tâche dépend de la précédente (Task 23 a besoin des partials de la Task 22 ; Task 24 a besoin du mode réorganisation de la Task 23 ; Task 25 a besoin de l'édition de texte de la Task 24). Ne pas paralléliser.
2. **`liveSection()` ne doit pas être supprimée** en Task 25 (seuls ses appels depuis les cases à cocher retirées le sont) — elle reste utilisée par `liveTrailer()` et `liveDiscord()`.
3. **Le Hero n'est jamais dans `[data-section-key]`** — il reste un bloc fixe en tête de `home.blade.php`, non affecté par SortableJS ni par la boucle `@foreach($homeLayout as ...)`.
4. **Test de non-régression prioritaire** : à la fin de la Task 22 (avant même que le mode réorganisation existe), la page doit être visuellement identique à son état juste avant ce plan. Toute différence à ce stade est un bug d'extraction, pas une fonctionnalité manquante des tâches suivantes.
