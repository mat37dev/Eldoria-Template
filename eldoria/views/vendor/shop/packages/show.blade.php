@extends('layouts.app')

@section('title', $package->name)

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">

    <div class="mb-8">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">
            {{ $package->category->name ?? __('theme::theme.shop.category_fallback') }}
        </p>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-text-primary">{{ $package->name }}</h1>
    </div>

    <div class="card-eldoria p-8">
        @if($package->hasImage())
            <div class="w-full h-56 rounded-sm overflow-hidden mb-6 border border-accent/10 bg-bg-primary/30 flex items-center justify-center">
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="w-full h-full object-contain p-8">
            </div>
        @endif

        <div class="prose prose-invert text-text-secondary text-sm max-w-none mb-6">
            {!! $package->description !!}
        </div>

        <div class="flex items-center justify-between pt-6 border-t border-accent/10">
            <span class="inline-flex flex-col items-start gap-1 px-4 py-2 rounded-full bg-accent/10 border border-accent/20">
                @if($package->isDiscounted())
                    <del class="text-text-secondary text-sm font-normal">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                @endif
                <span class="text-accent font-display font-bold text-2xl">{{ shop_format_amount($package->getPrice()) }}</span>
            </span>

            @if($shopUser === null)
                <a href="{{ route('shop.login') }}" class="btn-primary min-h-[48px]">{{ __('theme::theme.shop.login_to_buy') }}</a>
            @elseif($package->isSubscription())
                @if($package->isUserSubscribed($shopUser))
                    <a href="{{ route('shop.profile') }}" class="btn-primary min-h-[48px]">{{ __('theme::theme.shop.manage_subscription') }}</a>
                @else
                    <form action="{{ route('shop.subscriptions.select', $package) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary min-h-[48px]">{{ __('theme::theme.shop.subscribe') }}</button>
                    </form>
                @endif
            @elseif($package->isInCart())
                <form action="{{ route('shop.cart.remove', $package) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary min-h-[48px]">{{ __('theme::theme.shop.remove_from_cart') }}</button>
                </form>
            @elseif($package->global_limit === 0)
                <span class="text-text-secondary text-sm">{{ __('theme::theme.shop.unavailable') }}</span>
            @elseif($package->getMaxQuantity() < 1)
                <span class="text-text-secondary text-sm">{{ __('theme::theme.shop.limit_reached') }}</span>
            @elseif(! $package->hasBoughtRequirements())
                <span class="text-text-secondary text-sm">{{ __('theme::theme.shop.requirements_needed') }}</span>
            @else
                <form action="{{ route('shop.packages.buy', $package) }}" method="POST" class="flex items-center gap-3">
                    @csrf

                    @if($package->custom_price)
                        <input type="number" step="0.01" min="{{ $package->getPrice() }}" name="price"
                               value="{{ $package->getPrice() }}"
                               class="w-24 bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[48px]">
                    @endif

                    @if($package->has_quantity)
                        <input type="number" min="1" max="{{ $package->getMaxQuantity() }}" name="quantity" value="1" required
                               class="w-20 bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm min-h-[48px]">
                    @endif

                    <button type="submit" class="btn-primary min-h-[48px]">{{ __('theme::theme.shop.add_to_cart') }}</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
