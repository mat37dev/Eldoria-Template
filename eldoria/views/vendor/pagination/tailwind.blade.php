{{--
    Override du thème pour la pagination par défaut de Laravel (pagination::tailwind,
    utilisée par ->links() partout où un plugin pagine une liste — recherche Wiki,
    Changelog, etc.). La vue core utilise des gris Tailwind génériques qui ne
    correspondent à aucune couleur du thème ; celle-ci reprend les tokens Eldoria.
--}}
@if($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center gap-2 flex-wrap">
        @if($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-sm border border-accent/20 text-text-secondary/40 cursor-not-allowed" aria-hidden="true">
                &laquo;
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="inline-flex items-center justify-center w-10 h-10 rounded-sm border border-accent/30 text-text-secondary hover:text-text-primary hover:border-accent transition-colors"
               aria-label="{{ __('pagination.previous') }}">
                &laquo;
            </a>
        @endif

        @foreach($elements as $element)
            @if(is_string($element))
                <span class="inline-flex items-center justify-center w-10 h-10 text-text-secondary/60" aria-disabled="true">{{ $element }}</span>
            @endif

            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="inline-flex items-center justify-center w-10 h-10 rounded-sm font-display font-bold text-sm bg-accent text-text-primary">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-10 h-10 rounded-sm border border-accent/30 text-text-secondary hover:text-text-primary hover:border-accent transition-colors text-sm"
                           aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="inline-flex items-center justify-center w-10 h-10 rounded-sm border border-accent/30 text-text-secondary hover:text-text-primary hover:border-accent transition-colors"
               aria-label="{{ __('pagination.next') }}">
                &raquo;
            </a>
        @else
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-sm border border-accent/20 text-text-secondary/40 cursor-not-allowed" aria-hidden="true">
                &raquo;
            </span>
        @endif
    </nav>
@endif
