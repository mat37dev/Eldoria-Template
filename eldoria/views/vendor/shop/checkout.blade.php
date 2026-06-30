@extends('layouts.app')

@section('title', 'Paiement')

@section('content')
<div class="pt-24 pb-16 max-w-2xl mx-auto px-4">
    <h1 class="section-title mt-8 mb-4">Paiement</h1>
    <p class="section-subtitle">Choisissez votre méthode de paiement</p>

    <div class="card-eldoria p-8" data-aos="fade-up">
        @if(isset($gateways) && count($gateways) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($gateways as $gateway)
                <a href="{{ route('shop.checkout.pay', $gateway) }}"
                   class="card-eldoria p-6 text-center transition-all duration-200 group flex flex-col items-center gap-3">
                    @if($gateway->image)
                        <img src="{{ $gateway->imageUrl() }}" alt="{{ $gateway->name }}"
                             class="h-8 mx-auto object-contain">
                    @else
                        <div class="text-accent/40 text-2xl font-display">✦</div>
                    @endif
                    <div class="font-display text-text-primary text-sm tracking-wide group-hover:text-accent transition-colors">
                        {{ $gateway->name }}
                    </div>
                </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-5xl font-display mb-4" style="color: color-mix(in srgb, var(--color-accent) 20%, transparent)">✦</div>
                <p class="text-text-secondary">Aucune méthode de paiement disponible.</p>
                <p class="text-text-secondary text-xs mt-2">Veuillez contacter un administrateur.</p>
            </div>
        @endif
    </div>

    {{-- Retour panier --}}
    <div class="text-center mt-6">
        <a href="{{ route('shop.cart.index') }}"
           class="text-text-secondary text-xs tracking-widest uppercase font-display hover:text-accent transition-colors">
            ← Retour au panier
        </a>
    </div>

    {{-- Sécurité (reassurance) --}}
    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-6 text-text-secondary text-xs tracking-widest uppercase font-display" data-aos="fade-up">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-accent/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Paiement sécurisé
        </span>
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-accent/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Livraison instantanée
        </span>
    </div>
</div>
@endsection
