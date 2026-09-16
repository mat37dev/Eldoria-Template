<?php
    $navbarElements = \Azuriom\Models\NavbarElement::orderBy('position')->with('roles')->get()
        ->filter(fn ($element) => $element->hasPermission())
        ->whereNull('parent_id');
?>
<header class="fixed top-0 left-0 right-0 z-50 border-b-4"
        style="background: linear-gradient(180deg, color-mix(in srgb, var(--color-accent) 25%, #3AA0D8) 0%, #2B8FCB 100%); border-color: color-mix(in srgb, white 40%, #2B8FCB 60%);"
        x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="font-display font-black text-xl text-white tracking-widest uppercase"
               style="text-shadow: 0 2px 0 rgba(31,91,121,.4);">
                @if(setting('logo'))
                    <img src="{{ site_logo() }}" alt="{{ site_name() }}" class="h-8">
                @else
                    {{ site_name() }}
                @endif
            </a>

            {{-- Navigation desktop --}}
            <nav class="hidden md:flex items-center gap-8">
                @foreach($navbarElements as $element)
                    @if(!$element->isDropdown())
                        <a href="{{ $element->getLink() }}"
                           class="text-white/90 hover:text-accent text-sm tracking-widest uppercase font-bold transition-colors"
                           style="text-shadow: 0 2px 0 rgba(31,91,121,.3);">
                            {{ $element->name }}
                        </a>
                    @endif
                @endforeach
            </nav>

            {{-- Actions desktop --}}
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <span class="flex items-center gap-1.5 text-accent font-display font-semibold text-sm">
                        <img src="{{ theme_asset('images/coin-rubis.png') }}" alt="" class="w-4 h-4">
                        {{ format_money(auth()->user()->money) }}
                    </span>
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-2 text-white/90 hover:text-accent text-sm font-bold transition-colors">
                        @include('partials._avatar', ['user' => auth()->user(), 'size' => 32, 'class' => 'w-6 h-6 rounded-sm flex-shrink-0'])
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white/90 hover:text-accent text-sm font-bold transition-colors">
                            {{ trans('auth.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-white/90 hover:text-accent text-sm tracking-widest uppercase font-bold transition-colors">
                        {{ __('theme::theme.nav.login') }}
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4">
                        {{ __('theme::theme.nav.register') }}
                    </a>
                @endauth
            </div>

            {{-- Burger mobile --}}
            <button @click="open = !open" class="md:hidden p-3 text-white hover:text-accent"
                    aria-label="{{ __('theme::theme.nav.menu') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Drawer mobile --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="md:hidden border-t-4 px-4 py-6 space-y-4"
         style="background: #2B8FCB; border-color: color-mix(in srgb, white 40%, #2B8FCB 60%);">
        @foreach($navbarElements as $element)
            @if(!$element->isDropdown())
                <a href="{{ $element->getLink() }}"
                   class="block text-white/90 hover:text-accent text-sm tracking-widest uppercase font-bold py-2 transition-colors">
                    {{ $element->name }}
                </a>
            @endif
        @endforeach
        <div class="pt-4 border-t border-white/20 space-y-3">
            @auth
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.index') }}" class="flex items-center gap-2 text-white/90 hover:text-accent text-sm font-bold transition-colors">
                        @include('partials._avatar', ['user' => auth()->user(), 'size' => 32, 'class' => 'w-6 h-6 rounded-sm flex-shrink-0'])
                        {{ auth()->user()->name }}
                    </a>
                    <span class="flex items-center gap-1.5 text-accent font-display font-semibold text-sm">
                        <img src="{{ theme_asset('images/coin-rubis.png') }}" alt="" class="w-4 h-4">
                        {{ format_money(auth()->user()->money) }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="block w-full text-left text-white/90 hover:text-accent text-sm font-bold transition-colors py-1">
                        {{ trans('auth.logout') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-white/90 hover:text-accent text-sm uppercase tracking-widest font-bold transition-colors">{{ __('theme::theme.nav.login') }}</a>
                <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4 inline-block">{{ __('theme::theme.nav.register') }}</a>
            @endauth
        </div>
    </div>
</header>
