@extends('layouts.app')

@section('title', 'Panier')

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">
    <h1 class="section-title mt-8 mb-12">Votre Panier</h1>

    @if(isset($cart) && count($cart) > 0)
        <div class="space-y-4 mb-8">
            @foreach($cart as $item)
            <div class="card-eldoria p-4 flex items-center gap-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
                @if($item->package->image)
                    <img src="{{ $item->package->imageUrl() }}" alt="{{ $item->package->name }}"
                         class="w-16 h-16 object-cover rounded-sm flex-shrink-0">
                @else
                    <div class="w-16 h-16 bg-bg-primary rounded-sm flex-shrink-0 flex items-center justify-center">
                        <span class="text-accent/30 font-display">✦</span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="font-display text-text-primary font-semibold truncate">{{ $item->package->name }}</div>
                    <div class="text-text-secondary text-sm">Qté : {{ $item->quantity }}</div>
                </div>
                <div class="text-accent font-display font-bold whitespace-nowrap">{{ $item->package->formatPrice() }}</div>
                <form action="{{ route('shop.cart.remove', $item->package) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-8 h-8 flex items-center justify-center text-text-secondary hover:text-red-400 transition-colors rounded-sm"
                            title="Retirer">✕</button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Résumé total + checkout --}}
        <div class="card-eldoria p-6 flex flex-col sm:flex-row items-center justify-between gap-4" data-aos="fade-up">
            <div class="font-display text-text-primary text-center sm:text-left">
                Total :
                @if(isset($total))
                    <span class="text-accent text-2xl font-black ml-2">{{ $total }}</span>
                @endif
            </div>
            <a href="{{ route('shop.checkout.index') }}" class="btn-primary min-h-[48px] justify-center">
                Procéder au paiement
            </a>
        </div>

        {{-- Continuer les achats --}}
        <div class="text-center mt-6">
            <a href="{{ route('shop.packages.index') }}"
               class="text-text-secondary text-xs tracking-widest uppercase font-display hover:text-accent transition-colors">
                ← Continuer mes achats
            </a>
        </div>
    @else
        <div class="text-center py-24" data-aos="fade-up">
            <div class="text-8xl font-display mb-6" style="color: color-mix(in srgb, var(--color-accent) 20%, transparent)">✦</div>
            <p class="text-text-secondary mb-8 text-lg">Votre panier est vide.</p>
            <a href="{{ route('shop.packages.index') }}" class="btn-primary min-h-[48px]">
                Voir la boutique
            </a>
        </div>
    @endif
</div>
@endsection
