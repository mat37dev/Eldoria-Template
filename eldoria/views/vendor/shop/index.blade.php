@extends('layouts.app')

@section('title', trans('shop::shop.shop'))

@section('content')
<div class="pt-24 pb-16">
    {{-- Header page --}}
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Boutique ✦</p>
        <h1 class="section-title">Nos Offres</h1>
        <p class="section-subtitle">Soutiens le serveur et obtiens des avantages exclusifs</p>
    </div>

    <div class="max-w-7xl mx-auto px-4">
        {{-- Filtres catégories --}}
        @if(isset($categories) && $categories->count() > 1)
        <div class="flex flex-wrap gap-3 justify-center mb-12">
            <a href="{{ route('shop.packages.index') }}"
               class="px-4 py-2 text-xs font-display tracking-widest uppercase border rounded-sm
                      transition-all duration-200 text-text-secondary hover:text-accent"
               style="border-color: color-mix(in srgb, var(--color-accent) 30%, transparent)">
                Tout
            </a>
            @foreach($categories as $category)
            <a href="{{ route('shop.categories.show', $category) }}"
               class="px-4 py-2 text-xs font-display tracking-widest uppercase border rounded-sm
                      transition-all duration-200 text-text-secondary hover:text-accent"
               style="border-color: color-mix(in srgb, var(--color-accent) 30%, transparent)">
                {{ $category->name }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Grille de produits --}}
        @if(isset($packages) && $packages->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($packages as $package)
            <a href="{{ route('shop.packages.show', $package) }}"
               class="card-eldoria group overflow-hidden" data-aos="fade-up"
               data-aos-delay="{{ ($loop->index % 4) * 100 }}">
                @if($package->image)
                    <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                         class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-44 bg-bg-primary flex items-center justify-center">
                        <span class="text-5xl font-display" style="color: color-mix(in srgb, var(--color-accent) 20%, transparent)">✦</span>
                    </div>
                @endif
                <div class="p-5">
                    <h3 class="font-display text-text-primary font-semibold mb-2 group-hover:text-accent transition-colors">
                        {{ $package->name }}
                    </h3>
                    <p class="text-text-secondary text-sm mb-4 line-clamp-2">{{ strip_tags($package->description) }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-accent font-display font-bold text-xl">{{ $package->formatPrice() }}</span>
                        <span class="text-xs text-text-secondary uppercase tracking-widest font-display">Voir →</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-24">
            <div class="text-8xl font-display mb-6" style="color: color-mix(in srgb, var(--color-accent) 20%, transparent)">✦</div>
            <p class="text-text-secondary">Aucune offre disponible pour le moment.</p>
        </div>
        @endif
    </div>
</div>
@endsection
