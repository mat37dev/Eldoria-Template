@extends('layouts.app')

@section('title', 'Accueil')

@section('content')

{{-- ======= HERO ======= --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden" id="hero">

    {{-- Background image --}}
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" id="hero-bg"
         style="background-image: url('{{ theme_setting('hero_image') ?: asset('themes/eldoria/assets/images/hero-default.svg') }}')">
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

@endsection
