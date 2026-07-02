@php
    $staffMembers = collect(range(1, 8))
        ->map(fn ($i) => [
            'name' => theme_config("staff_{$i}_name", ''),
            'role' => theme_config("staff_{$i}_role", ''),
            'link' => theme_config("staff_{$i}_link", ''),
        ])
        ->filter(fn ($member) => trim($member['name']) !== '');
@endphp
@if($staffMembers->isNotEmpty())
<section class="py-24 px-4 max-w-6xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="staff" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ __('theme::theme.home.staff_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.staff_subtitle') }}</p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($staffMembers as $member)
        <div class="card-eldoria p-4 text-center group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
            <div class="w-16 h-16 mx-auto mb-3 overflow-hidden rounded-sm">
                <img src="https://minotar.net/avatar/{{ urlencode($member['name']) }}/128"
                     alt="{{ $member['name'] }}"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
            </div>
            <div class="font-display text-text-primary text-sm font-semibold flex items-center justify-center gap-1.5">
                {{ $member['name'] }}
                @if($member['link'] !== '')
                    <a href="{{ $member['link'] }}" target="_blank" rel="noopener"
                       class="text-accent/60 hover:text-accent transition-colors"
                       title="{{ __('theme::theme.home.staff_link_title', ['name' => $member['name']]) }}">
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif
            </div>
            @if($member['role'] !== '')
                <div class="text-accent text-xs uppercase tracking-widest mt-1">{{ $member['role'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif
