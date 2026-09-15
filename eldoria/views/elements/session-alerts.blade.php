{{-- Override de l'élément core (resources/views/elements/session-alerts.blade.php) : la
     version core utilise des classes Bootstrap (alert, bi bi-*, data-bs-dismiss) que le
     thème n'inclut pas. Sans cet override, aucune action à effet de bord du cœur Azuriom
     (profil, formulaires plugins, etc.) n'affichait le moindre retour visuel. --}}
@if(session('success') || session('error'))
    <div class="fixed top-24 inset-x-0 z-50 flex flex-col items-center gap-3 px-4 pointer-events-none">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto flex items-center gap-3 max-w-md w-full border-2 rounded-sm px-4 py-3 text-sm shadow-lg
                        bg-bg-secondary border-accent/60 text-text-primary">
                <span aria-hidden="true">✓</span>
                <span class="flex-1">{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-lg leading-none opacity-60 hover:opacity-100" aria-label="{{ __('theme::theme.common.close') }}">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto flex items-center gap-3 max-w-md w-full border-2 rounded-sm px-4 py-3 text-sm shadow-lg
                        bg-bg-secondary border-red-500/60 text-red-700">
                <span aria-hidden="true">✕</span>
                <span class="flex-1">{{ session('error') }}</span>
                <button type="button" @click="show = false" class="text-lg leading-none opacity-60 hover:opacity-100" aria-label="{{ __('theme::theme.common.close') }}">&times;</button>
            </div>
        @endif
    </div>
@endif
