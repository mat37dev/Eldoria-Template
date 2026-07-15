@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.shop.hero_eyebrow') }} ✦</p>
        <h1 class="section-title">{{ $category->name }}</h1>
    </div>

    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1">
            @include('shop::categories._sidebar')
        </div>

        <div class="lg:col-span-3 space-y-6">
            @if($category->description)
                <div class="card-eldoria p-6 text-text-secondary text-sm leading-relaxed">
                    {!! $category->description !!}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @forelse($category->packages as $package)
                    <div class="card-eldoria p-6 group flex flex-col hover:-translate-y-1 hover:shadow-lg hover:shadow-accent/10 transition-all duration-300" data-aos="fade-up">
                        <div class="relative w-full h-40 rounded-sm overflow-hidden mb-4 bg-bg-primary/30 flex items-center justify-center">
                            @if($package->hasImage())
                                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}"
                                     class="w-full h-full object-contain p-6 group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-accent/10 to-accent-secondary/10 flex items-center justify-center">
                                    <span class="text-accent/40 text-4xl font-display">✦</span>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-bg-secondary/60 to-transparent pointer-events-none"></div>
                        </div>

                        <h3 class="font-display text-text-primary font-semibold mb-2">{{ $package->name }}</h3>
                        <p class="text-text-secondary text-sm mb-4 line-clamp-2 flex-1">{{ $package->short_description }}</p>

                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-baseline gap-2 px-3 py-1.5 rounded-full bg-accent/10 border border-accent/20">
                                @if($package->isDiscounted())
                                    <del class="text-text-secondary text-xs font-normal">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                                @endif
                                <span class="text-accent font-display font-bold text-lg">{{ shop_format_amount($package->getPrice()) }}</span>
                            </span>
                            <a href="{{ route('shop.packages.show', $package) }}" class="btn-primary text-xs py-2 px-4">
                                {{ __('theme::theme.shop.view') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 text-center py-12 text-text-secondary">
                        {{ __('theme::theme.shop.no_products') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
