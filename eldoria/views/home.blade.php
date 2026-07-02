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
