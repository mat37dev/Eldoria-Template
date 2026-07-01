@extends('layouts.app')

@section('title', 'Panier')

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">

    <div class="text-center py-12">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Boutique ✦</p>
        <h1 class="section-title">Mon panier</h1>
    </div>

    @if($cart->isEmpty())
        <div class="card-eldoria p-8 text-center text-text-secondary">
            Ton panier est vide.
            <div class="mt-4">
                <a href="{{ route('shop.home') }}" class="btn-primary">Retourner à la boutique</a>
            </div>
        </div>
    @else
        <form action="{{ route('shop.cart.update') }}" method="POST" class="space-y-4 mb-8">
            @csrf

            @foreach($cart->content() as $cartItem)
                <div class="card-eldoria p-4 flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[140px]">
                        <div class="font-display text-text-primary font-semibold text-sm">{{ $cartItem->name() }}</div>
                        <div class="text-text-secondary text-xs mt-1">
                            {{ shop_format_amount($cartItem->price()) }} × {{ $cartItem->quantity }}
                            = <span class="text-accent">{{ shop_format_amount($cartItem->total()) }}</span>
                        </div>
                    </div>

                    <input type="number" min="0" max="{{ $cartItem->maxQuantity() }}"
                           name="quantities[{{ $cartItem->itemId }}]" value="{{ $cartItem->quantity }}"
                           required @if(!$cartItem->hasQuantity()) readonly @endif
                           class="w-20 bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[48px]">

                    <a href="{{ route('shop.cart.remove', $cartItem->id) }}"
                       class="min-w-[48px] min-h-[48px] flex items-center justify-center text-text-secondary hover:text-red-400 transition-colors"
                       title="Retirer">
                        ✕
                    </a>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="btn-primary text-xs py-2 px-4">Mettre à jour</button>
            </div>
        </form>

        <form method="POST" action="{{ route('shop.cart.clear') }}" class="text-right mb-8">
            @csrf
            <button type="submit" class="text-text-secondary hover:text-red-400 text-xs uppercase tracking-widest transition-colors">
                Vider le panier
            </button>
        </form>

        {{-- Coupons --}}
        <div class="card-eldoria p-6 mb-8">
            <h3 class="font-display text-accent text-sm tracking-widest uppercase mb-4">Code promo</h3>

            <form action="{{ route('shop.cart.coupons.add') }}" method="POST" class="flex gap-3 mb-4">
                @csrf
                <input type="text" name="coupon" value="{{ old('coupon') }}" placeholder="Code"
                       required
                       class="flex-1 bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                <button type="submit" class="btn-primary min-h-[48px]">Appliquer</button>
            </form>
            @error('coupon')
                <p class="text-red-400 text-xs">{{ $message }}</p>
            @enderror

            @if(!$cart->coupons()->isEmpty())
                <div class="space-y-2">
                    @foreach($cart->coupons() as $coupon)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-text-primary">
                                {{ $coupon->code }}
                                <span class="text-accent">— {{ $coupon->is_fixed ? shop_format_amount($coupon->discount) : $coupon->discount.' %' }}</span>
                            </span>
                            <form action="{{ route('shop.cart.coupons.remove', $coupon) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-text-secondary hover:text-red-400 text-xs">Retirer</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Total --}}
        <div class="card-eldoria p-6 mb-8 space-y-2">
            <div class="flex justify-between text-text-secondary text-sm">
                <span>Total</span>
                <span>{{ shop_format_amount($cart->total()) }}</span>
            </div>
            @if($cart->total() !== $cart->payableTotal())
                <div class="flex justify-between text-accent font-display font-bold">
                    <span>À payer</span>
                    <span>{{ shop_format_amount($cart->payableTotal()) }}</span>
                </div>
            @endif
        </div>

        {{-- Checkout --}}
        @if(use_site_money())
            <form method="POST" action="{{ route('shop.cart.payment') }}" class="space-y-4">
                @csrf

                @if(!empty($terms))
                    <label class="flex items-start gap-2 text-text-secondary text-sm cursor-pointer">
                        <input type="checkbox" name="terms" required @checked(old('terms')) class="mt-1 accent-[var(--color-accent)]">
                        <span>{{ $terms }}</span>
                    </label>
                    @error('terms')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                @endif

                <div class="flex items-center justify-between">
                    <a href="{{ route('shop.home') }}" class="text-text-secondary hover:text-text-primary text-sm">
                        ← Retour à la boutique
                    </a>
                    <button type="submit" class="btn-primary min-h-[48px]">Valider l'achat</button>
                </div>
            </form>
        @else
            <form action="{{ route('shop.payments.payment') }}" method="GET" class="space-y-4">
                @if($emailRequired)
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                    @error('email')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                @endif

                @if(!empty($terms))
                    <label class="flex items-start gap-2 text-text-secondary text-sm cursor-pointer">
                        <input type="checkbox" name="terms" required @checked(old('terms')) class="mt-1 accent-[var(--color-accent)]">
                        <span>{{ $terms }}</span>
                    </label>
                    @error('terms')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                @endif

                <div class="flex items-center justify-between">
                    <a href="{{ route('shop.home') }}" class="text-text-secondary hover:text-text-primary text-sm">
                        ← Retour à la boutique
                    </a>
                    <button type="submit" class="btn-primary min-h-[48px]">Passer au paiement</button>
                </div>
            </form>
        @endif
    @endif
</div>
@endsection
