@if(class_exists('\Azuriom\Plugin\Vote\Models\Site'))
<section class="py-24 bg-bg-secondary border-y border-accent/10 {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="vote" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="section-title">{{ __('theme::theme.home.vote_title') }}</h2>
        <p class="section-subtitle">{{ __('theme::theme.home.vote_subtitle') }}</p>

        <div class="space-y-4">
            @foreach(\Azuriom\Plugin\Vote\Models\Site::enabled()->get() as $site)
            <div class="card-eldoria p-4 flex items-center justify-between gap-4" data-aos="fade-right" data-aos-delay="{{ $loop->index * 75 }}">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 flex items-center justify-center text-accent/40 font-display font-bold">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <div class="font-display text-text-primary text-sm font-semibold">{{ $site->name }}</div>
                        <div class="text-text-secondary text-xs">{{ __('theme::theme.home.vote_reward_generic') }}</div>
                    </div>
                </div>
                @auth
                    <a href="{{ $site->url }}" target="_blank" rel="noopener noreferrer"
                       data-vote-id="{{ $site->id }}"
                       data-vote-url="{{ route('vote.vote', $site) }}"
                       data-vote-time="{{ $site->getNextVoteTime(auth()->user(), request())?->valueOf() }}"
                       class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[40px] flex items-center gap-1.5">
                        ✦ {{ __('theme::theme.home.vote_cta') }}
                        <span class="vote-timer font-mono"></span>
                    </a>
                @else
                    <a href="{{ route('vote.home') }}"
                       class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[40px]">
                        ✦ {{ __('theme::theme.home.vote_cta') }}
                    </a>
                @endauth
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
