<section class="py-24 px-4 max-w-5xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="join_steps" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ $sectionData['title'] ?: __('theme::theme.home.join_steps_title') }}</h2>
    <p class="section-subtitle">{{ $sectionData['subtitle'] ?: __('theme::theme.home.join_steps_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="0">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">1</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][0]['title'] ?: __('theme::theme.home.join_step1_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][0]['text'] ?: __('theme::theme.home.join_step1_text') }}</p>
        </div>
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">2</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][1]['title'] ?: __('theme::theme.home.join_step2_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][1]['text'] ?: __('theme::theme.home.join_step2_text') }}</p>
        </div>
        <div class="card-eldoria p-6 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-accent/10 border border-accent/30 flex items-center justify-center font-display text-accent font-bold">3</div>
            <h3 class="font-display text-text-primary font-semibold mb-2">{{ $sectionData['steps'][2]['title'] ?: __('theme::theme.home.join_step3_title') }}</h3>
            <p class="text-text-secondary text-sm">{{ $sectionData['steps'][2]['text'] ?: __('theme::theme.home.join_step3_text') }}</p>
        </div>
    </div>
</section>
