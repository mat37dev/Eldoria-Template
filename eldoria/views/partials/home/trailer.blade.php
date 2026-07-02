<section class="py-24 px-4 max-w-5xl mx-auto {{ ($trailerId && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="trailer" data-live-section="trailer" data-aos="fade-up">
    <h2 class="section-title">{{ $sectionData['title'] ?: __('theme::theme.home.trailer_title') }}</h2>
    <p class="section-subtitle">{{ $sectionData['subtitle'] ?: __('theme::theme.home.trailer_subtitle') }}</p>

    <div class="card-eldoria overflow-hidden aspect-video">
        <iframe data-trailer-iframe
                src="{{ $trailerId ? 'https://www.youtube-nocookie.com/embed/'.$trailerId : '' }}"
                title="{{ __('theme::theme.home.trailer_iframe_title') }}"
                class="w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"></iframe>
    </div>
</section>
