<?php
    $homeServer = \Azuriom\Models\Server::where('home_display', true)->first();
    $navbarElements = \Azuriom\Models\NavbarElement::orderBy('position')->with('roles')->get()
        ->filter(fn ($element) => $element->hasPermission())
        ->whereNull('parent_id');
?>
<footer class="bg-bg-secondary border-t border-accent/10 mt-24">
    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

            {{-- Brand --}}
            <div>
                <h3 class="font-display text-accent text-lg tracking-widest uppercase mb-4">
                    {{ site_name() }}
                </h3>
                <p class="text-text-secondary text-sm leading-relaxed" data-live="hero_slogan">
                    {{ theme_config('hero_slogan', '') }}
                </p>
                {{-- IP serveur --}}
                @if($homeServer)
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-text-secondary text-xs uppercase tracking-wider">IP :</span>
                        <button onclick="navigator.clipboard.writeText('{{ $homeServer->fullAddress() }}')"
                                class="text-accent font-mono text-sm hover:text-accent/80 transition-colors"
                                title="Copier l'IP">
                            {{ $homeServer->fullAddress() }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Navigation --}}
            <div>
                <h4 class="font-display text-text-primary text-sm tracking-widest uppercase mb-4">Navigation</h4>
                <nav class="space-y-2">
                    @foreach($navbarElements as $element)
                        @if(!$element->isDropdown())
                            <a href="{{ $element->getLink() }}"
                               class="block text-text-secondary hover:text-accent text-sm transition-colors">
                                {{ $element->name }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Réseaux sociaux --}}
            <div>
                <h4 class="font-display text-text-primary text-sm tracking-widest uppercase mb-4">Communauté</h4>
                <div class="flex gap-4">
                    {{-- Toujours rendus (masqués si vides) pour permettre la mise à jour live du customizer --}}
                        <a href="{{ theme_config('footer_discord') ?: '#' }}" target="_blank" rel="noopener"
                           data-live-href="footer_discord"
                           class="text-text-secondary hover:text-accent transition-colors {{ theme_config('footer_discord') ? '' : 'hidden' }}"
                           aria-label="Discord">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
                            </svg>
                        </a>
                        <a href="{{ theme_config('footer_twitter') ?: '#' }}" target="_blank" rel="noopener"
                           data-live-href="footer_twitter"
                           class="text-text-secondary hover:text-accent transition-colors {{ theme_config('footer_twitter') ? '' : 'hidden' }}"
                           aria-label="Twitter/X">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                </div>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-accent/10 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-text-secondary text-xs">
                &copy; {{ date('Y') }} {{ site_name() }}. Tous droits réservés.
            </p>
            <p class="text-text-secondary text-xs">
                Propulsé par <a href="https://azuriom.com" class="text-accent/70 hover:text-accent transition-colors">Azuriom</a>
                · Thème <span class="text-accent/70">Eldoria</span>
            </p>
        </div>
    </div>
</footer>
