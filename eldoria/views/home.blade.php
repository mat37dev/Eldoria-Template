@extends('layouts.app')

@section('title', __('theme::theme.nav.home'))

@section('content')

<?php
    $homeServer = \Azuriom\Models\Server::where('home_display', true)->first();

    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/',
        theme_config('trailer_url', '') ?? '', $trailerMatch);
    $trailerId = $trailerMatch[1] ?? null;
?>

{{-- ======= HERO ======= --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden" id="hero">

    <?php
        $heroVideoEnabled = theme_config('hero_video_enabled', '0') === '1' && $trailerId !== null;
    ?>

    {{-- Background image --}}
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat {{ $heroVideoEnabled ? 'hidden' : '' }}" id="hero-bg"
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

{{-- ======= STATS BAND ======= --}}
<section class="relative z-10 bg-bg-secondary border-y border-accent/20 py-8" data-aos="fade-up">
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

{{-- ======= COMMENT NOUS REJOINDRE ======= --}}
<section class="py-24 px-4 max-w-5xl mx-auto" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.join_steps_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.join_steps_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="0">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">1</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ __('theme::theme.home.join_step1_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ __('theme::theme.home.join_step1_text') }}</p>
        </div>
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">2</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ __('theme::theme.home.join_step2_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ __('theme::theme.home.join_step2_text') }}</p>
        </div>
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">3</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ __('theme::theme.home.join_step3_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ __('theme::theme.home.join_step3_text') }}</p>
        </div>
    </div>
</section>

{{-- ======= TRAILER ======= --}}
<section class="py-24 px-4 max-w-5xl mx-auto {{ $trailerId ? '' : 'hidden' }}"
         data-live-section="trailer" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.trailer_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.trailer_subtitle') }}</p>

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

{{-- ======= ACTUS ======= --}}
@php $latestPosts = \Azuriom\Models\Post::published()->with('author')->latest('published_at')->take(3)->get(); @endphp
@if($latestPosts->isNotEmpty())
<section class="py-24 px-4 max-w-7xl mx-auto" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.news_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.news_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach($latestPosts as $post)
        <div class="card-eldoria overflow-hidden flex flex-col" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            @if($post->hasImage())
                <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="w-full h-32 object-cover">
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

{{-- ======= SHOP PREVIEW ======= --}}
@if(class_exists('\Azuriom\Plugin\Shop\Models\Package'))
<section class="py-24 px-4 max-w-7xl mx-auto {{ theme_config('show_section_shop', '1') === '1' ? '' : 'hidden' }}"
         data-live-section="shop" data-aos="fade-up">
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

{{-- ======= VOTE ======= --}}
@if(class_exists('\Azuriom\Plugin\Vote\Models\Site'))
<section class="py-24 bg-bg-secondary border-y border-accent/10 {{ theme_config('show_section_vote', '1') === '1' ? '' : 'hidden' }}"
         data-live-section="vote" data-aos="fade-up">
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
                {{-- Le vrai flux de vote (redirection + vérification) est géré sur la page /vote --}}
                <a href="{{ route('vote.home') }}"
                   class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[40px]">
                    ✦ {{ __('theme::theme.home.vote_cta') }}
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ======= ÉQUIPE ======= --}}
@php
    $staffMembers = collect(range(1, 8))
        ->map(fn ($i) => [
            'name' => theme_config("staff_{$i}_name", ''),
            'role' => theme_config("staff_{$i}_role", ''),
        ])
        ->filter(fn ($member) => trim($member['name']) !== '');
@endphp
@if($staffMembers->isNotEmpty())
<section class="py-24 px-4 max-w-6xl mx-auto" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.staff_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.staff_subtitle') }}</p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($staffMembers as $member)
        <div class="card-eldoria p-4 text-center" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
            <img src="https://minotar.net/avatar/{{ urlencode($member['name']) }}/128"
                 alt="{{ $member['name'] }}"
                 class="w-16 h-16 mx-auto rounded-sm mb-3">
            <div class="font-display text-text-primary text-sm font-semibold">{{ $member['name'] }}</div>
            @if($member['role'] !== '')
                <div class="text-accent text-xs uppercase tracking-widest mt-1">{{ $member['role'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ======= WIDGET DISCORD ======= --}}
@php $discordServerId = theme_config('discord_server_id', '') ?? ''; @endphp
<section class="py-24 px-4 {{ $discordServerId !== '' ? '' : 'hidden' }}"
         data-live-section="discord" data-aos="fade-up">
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

@endsection
