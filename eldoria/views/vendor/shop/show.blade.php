@extends('layouts.app')

@section('title', $package->name)

@section('content')
<div class="pt-24 pb-16 max-w-5xl mx-auto px-4">

    {{-- Breadcrumb --}}
    <nav class="mt-8 mb-6 flex items-center gap-2 text-xs text-text-secondary font-display tracking-widest uppercase">
        <a href="{{ route('shop.packages.index') }}" class="hover:text-accent transition-colors">Boutique</a>
        <span class="text-accent/40">›</span>
        @if(isset($package->category))
        <a href="{{ route('shop.categories.show', $package->category) }}" class="hover:text-accent transition-colors">
            {{ $package->category->name }}
        </a>
        <span class="text-accent/40">›</span>
        @endif
        <span class="text-text-primary">{{ $package->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

        {{-- Image produit --}}
        <div class="card-eldoria overflow-hidden" data-aos="fade-right">
            @if($package->image)
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="w-full h-80 object-cover">
            @else
                <div class="w-full h-80 bg-bg-primary flex items-center justify-center">
                    <span class="text-8xl font-display" style="color: color-mix(in srgb, var(--color-accent) 20%, transparent)">✦</span>
                </div>
            @endif
        </div>

        {{-- Infos produit --}}
        <div data-aos="fade-left">
            <p class="text-accent text-xs font-display tracking-widest uppercase mb-2">
                {{ $package->category->name ?? 'Boutique' }}
            </p>
            <h1 class="font-display text-3xl font-bold text-text-primary mb-4">{{ $package->name }}</h1>

            <div class="text-accent font-display text-4xl font-black mb-6">
                {{ $package->formatPrice() }}
            </div>

            @if($package->description)
            <div class="text-text-secondary text-sm mb-8 leading-relaxed space-y-2">
                {!! $package->description !!}
            </div>
            @endif

            <form action="{{ route('shop.cart.add', $package) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary w-full justify-center py-4 text-base min-h-[48px]">
                    Ajouter au panier
                </button>
            </form>

            <a href="{{ route('shop.packages.index') }}"
               class="mt-4 inline-block text-text-secondary text-xs tracking-widest uppercase font-display hover:text-accent transition-colors">
                ← Retour à la boutique
            </a>
        </div>
    </div>
</div>
@endsection
