<div class="text-center py-16 px-4">
    <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.wiki.eyebrow') }} ✦</p>
    <h1 class="section-title">{{ $title }}</h1>

    <form action="{{ route('wiki.search') }}" method="GET" role="search" class="max-w-md mx-auto mt-8 flex gap-2">
        <input type="search" name="q" value="{{ $search ?? '' }}" required
               placeholder="{{ __('theme::theme.wiki.search_placeholder') }}"
               class="flex-1 bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]
                      focus:outline-none focus:border-accent/60">
        <button type="submit" class="btn-primary min-h-[48px] px-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>
    </form>
</div>
