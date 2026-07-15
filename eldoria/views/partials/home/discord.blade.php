@php $discordServerId = theme_config('discord_server_id', '') ?? ''; @endphp
<section class="py-24 px-4 {{ ($discordServerId !== '' && $sectionData['visible']) ? '' : 'hidden' }}"
         data-section-key="discord" data-live-section="discord" data-aos="fade-up">
    @auth
        @if(auth()->user()->isAdmin())
            @include('partials.home._reorder-toolbar')
        @endif
    @endauth
    <h2 class="section-title">{{ __('theme::theme.home.discord_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.discord_subtitle') }}</p>

    <div class="max-w-md mx-auto card-eldoria p-4">
        <iframe data-discord-iframe
                src="{{ $discordServerId !== '' ? 'https://discord.com/widget?id='.$discordServerId.'&theme=dark' : '' }}"
                title="{{ __('theme::theme.home.discord_iframe_title') }}"
                width="100%" height="420"
                frameborder="0"
                sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                loading="lazy"></iframe>
    </div>
</section>
