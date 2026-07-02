<section class="relative z-10 bg-bg-secondary border-y border-accent/20 py-8 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="stats" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-center items-center gap-8 sm:gap-16">

            @php
                $onlinePlayers = \Azuriom\Models\Server::where('home_display', true)->get()
                    ->sum(fn ($server) => $server->getOnlinePlayers());

                $monthlyVotes = class_exists('\Azuriom\Plugin\Vote\Models\Vote')
                    ? \Azuriom\Plugin\Vote\Models\Vote::where('created_at', '>', now()->startOfMonth())->count()
                    : 0;
            @endphp

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-online"
                     data-target="{{ $onlinePlayers }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">{{ __('theme::theme.home.stats_online') }}</div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-accent/20"></div>

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-votes"
                     data-target="{{ $monthlyVotes }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">{{ __('theme::theme.home.stats_votes') }}</div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-accent/20"></div>

            <div class="text-center">
                <div class="font-display text-4xl font-bold text-accent" id="counter-members"
                     data-target="{{ \Azuriom\Models\User::count() }}">0</div>
                <div class="text-text-secondary text-xs tracking-widest uppercase mt-1">{{ __('theme::theme.home.stats_members') }}</div>
            </div>

        </div>
    </div>
</section>
