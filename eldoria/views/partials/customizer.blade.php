{{-- Bouton flottant --}}
<button @click="$dispatch('open-customizer')"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-accent text-bg-primary rounded-full
               flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
        title="Personnaliser le thème">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
    </svg>
</button>

{{-- Drawer customizer --}}
<div x-data="customizer()"
     data-save-url="{{ route('admin.themes.config', 'eldoria') }}"
     @open-customizer.window="open = true"
     class="fixed inset-0 z-[100]"
     x-show="open"
     x-cloak>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>

    {{-- Panel (desktop: côté droit | mobile: bas) --}}
    <div class="absolute right-0 top-0 bottom-0 w-full sm:w-96 bg-bg-secondary border-l border-accent/20
                flex flex-col overflow-hidden
                sm:right-0 sm:top-0 sm:bottom-0
                max-sm:bottom-0 max-sm:left-0 max-sm:right-0 max-sm:top-auto max-sm:h-[80vh] max-sm:rounded-t-2xl"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-accent/20">
            <h3 class="font-display text-accent tracking-widest uppercase text-sm">Personnaliser</h3>
            <button @click="open = false" class="text-text-secondary hover:text-text-primary transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-accent/10">
            <button @click="activeTab = 'colors'"
                    :class="activeTab === 'colors' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                Couleurs
            </button>
            <button @click="activeTab = 'content'"
                    :class="activeTab === 'content' ? 'border-b-2 border-accent text-accent' : 'text-text-secondary hover:text-text-primary'"
                    class="flex-1 py-3 text-xs font-display tracking-widest uppercase transition-colors">
                Contenu
            </button>
        </div>

        {{-- Body scrollable --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-8">

            {{-- TAB COULEURS --}}
            <div x-show="activeTab === 'colors'" class="space-y-6">

                {{-- Palettes prédéfinies --}}
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-3">Palettes</label>
                    <div class="grid grid-cols-3 gap-3">
                        <template x-for="palette in palettes" :key="palette.name">
                            <button @click="applyPalette(palette)"
                                    class="relative p-3 rounded-sm border border-accent/20 hover:border-accent/60 transition-all text-center">
                                <div class="flex gap-1 justify-center mb-2">
                                    <div class="w-5 h-5 rounded-full border border-white/10"
                                         :style="'background-color: ' + palette.accent"></div>
                                    <div class="w-5 h-5 rounded-full border border-white/10"
                                         :style="'background-color: ' + palette.secondary"></div>
                                </div>
                                <span class="text-text-secondary text-xs" x-text="palette.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Pickers couleur libre --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Accent principal</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="accent" @input="applyColors(accent, accentSecondary)"
                                   class="w-10 h-10 rounded cursor-pointer border border-accent/20 bg-transparent">
                            <span class="text-text-secondary text-sm font-mono" x-text="accent"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Accent secondaire</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="accentSecondary" @input="applyColors(accent, accentSecondary)"
                                   class="w-10 h-10 rounded cursor-pointer border border-accent/20 bg-transparent">
                            <span class="text-text-secondary text-sm font-mono" x-text="accentSecondary"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB CONTENU --}}
            <div x-show="activeTab === 'content'" class="space-y-6">

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">Slogan hero</label>
                    <textarea data-setting-slogan
                              class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm
                                     focus:outline-none focus:border-accent/60 resize-none"
                              rows="3"
                              placeholder="Bienvenue dans le royaume de...">{{ theme_config('hero_slogan', '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-3">Sections visibles</label>
                    <div class="space-y-3">
                        @foreach([
                            ['key' => 'show_section_shop', 'label' => 'Boutique'],
                            ['key' => 'show_section_vote', 'label' => 'Vote'],
                            ['key' => 'show_section_forum', 'label' => 'Forum'],
                        ] as $toggle)
                        <div class="flex items-center justify-between">
                            <span class="text-text-primary text-sm">{{ $toggle['label'] }}</span>
                            <input type="checkbox"
                                   data-setting="{{ $toggle['key'] }}"
                                   {{ theme_config($toggle['key'], '1') === '1' ? 'checked' : '' }}
                                   class="w-4 h-4 accent-[var(--color-accent)]">
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer actions --}}
        <div class="px-6 py-4 border-t border-accent/20 flex gap-3">
            <button @click="cancel()"
                    class="flex-1 py-2 border border-accent/30 text-text-secondary hover:text-text-primary
                           text-sm font-display tracking-widest uppercase rounded-sm transition-colors">
                Annuler
            </button>
            <button @click="save()"
                    :disabled="saving"
                    class="flex-1 py-2 bg-accent text-bg-primary font-display text-sm tracking-widest uppercase
                           rounded-sm hover:bg-accent/90 transition-all disabled:opacity-50">
                <span x-show="!saving && !saved && !saveError">Enregistrer</span>
                <span x-show="saving">Sauvegarde...</span>
                <span x-show="saved">✓ Sauvegardé</span>
                <span x-show="saveError" class="text-red-600">✕ Erreur</span>
            </button>
        </div>
    </div>
</div>
