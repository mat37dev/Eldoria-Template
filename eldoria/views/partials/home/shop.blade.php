@if(class_exists('\Azuriom\Plugin\Shop\Models\Package'))
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="shop" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
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
