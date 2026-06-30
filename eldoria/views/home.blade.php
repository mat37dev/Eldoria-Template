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
