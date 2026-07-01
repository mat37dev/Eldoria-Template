<div class="space-y-6">

    {{-- Catégories --}}
    <div class="card-eldoria overflow-hidden">
        @if($displayHome)
            <a href="{{ route('shop.home') }}"
               class="block px-4 py-3 text-sm border-b border-accent/10 last:border-b-0 transition-colors
                      {{ $category === null ? 'bg-accent/10 text-accent font-semibold' : 'text-text-secondary hover:text-text-primary' }}">
                Accueil boutique
            </a>
        @endif

        @foreach($categories as $subCategory)
            <a href="{{ route('shop.categories.show', $subCategory) }}"
               class="flex items-center gap-2 px-4 py-3 text-sm border-b border-accent/10 last:border-b-0 transition-colors
                      {{ $subCategory->is($category) ? 'bg-accent/10 text-accent font-semibold' : 'text-text-secondary hover:text-text-primary' }}">
                @if($subCategory->icon)
                    <i class="{{ $subCategory->icon }}"></i>
                @endif
                {{ $subCategory->name }}
            </a>

            @foreach($subCategory->categories as $cat)
                <a href="{{ route('shop.categories.show', $cat) }}"
                   class="flex items-center gap-2 pl-8 pr-4 py-3 text-sm border-b border-accent/10 last:border-b-0 transition-colors
                          {{ $cat->is($category) ? 'bg-accent/10 text-accent font-semibold' : 'text-text-secondary hover:text-text-primary' }}">
                    @if($cat->icon)
                        <i class="{{ $cat->icon }}"></i>
                    @endif
                    {{ $cat->name }}
                </a>
            @endforeach
        @endforeach
    </div>

    {{-- Compte --}}
    @if($shopUser !== null)
        <div class="card-eldoria p-4 space-y-3">
            <div class="flex items-center gap-3">
                <img src="{{ $shopUser->getAvatar(48) }}" alt="{{ $shopUser->name }}" class="w-10 h-10 rounded-sm">
                <div>
                    <div class="font-display text-text-primary text-sm font-semibold">{{ $shopUser->name }}</div>
                    @if(use_site_money())
                        <div class="text-text-secondary text-xs">{{ format_money($shopUser->money) }}</div>
                    @endif
                </div>
            </div>

            @if(use_site_money())
                <a href="{{ route('shop.offers.select') }}" class="btn-primary w-full justify-center text-xs py-2">
                    Recharger le solde
                </a>
            @endif

            <a href="{{ route('shop.cart.index') }}"
               class="block text-center py-2 border border-accent/30 text-text-secondary hover:text-text-primary
                      text-xs font-display tracking-widest uppercase rounded-sm transition-colors">
                Mon panier
            </a>

            @if($userHasPayments)
                <a href="{{ route('shop.profile') }}"
                   class="block text-center py-2 border border-accent/30 text-text-secondary hover:text-text-primary
                          text-xs font-display tracking-widest uppercase rounded-sm transition-colors">
                    Mes achats
                </a>
            @endif
        </div>
    @else
        <a href="{{ route('shop.login') }}" class="btn-primary w-full justify-center block text-center">
            Se connecter pour acheter
        </a>
    @endif

    {{-- Objectif du mois --}}
    @if($goal >= 0)
        <div class="card-eldoria p-4">
            <div class="text-accent text-xs font-display tracking-widest uppercase mb-3">Objectif du mois</div>
            <div class="w-full bg-bg-primary rounded-full h-2 overflow-hidden mb-2">
                <div class="h-full bg-accent rounded-full" style="width: {{ min($goal, 100) }}%"></div>
            </div>
            <p class="text-text-secondary text-xs text-center">{{ $goal }}% atteint</p>
        </div>
    @endif

    {{-- Meilleur acheteur --}}
    @if($topCustomer !== null)
        <div class="card-eldoria p-4">
            <div class="text-accent text-xs font-display tracking-widest uppercase mb-3">Meilleur acheteur</div>
            <div class="flex items-center gap-3">
                <img src="{{ $topCustomer->user->getAvatar(48) }}" alt="{{ $topCustomer->user->name }}" class="w-10 h-10 rounded-sm">
                <div>
                    <div class="text-text-primary text-sm font-semibold">{{ $topCustomer->user->name }}</div>
                    @if($displaySidebarAmount)
                        <div class="text-text-secondary text-xs">{{ shop_format_amount($topCustomer->price) }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Achats récents --}}
    @if($recentPayments !== null)
        <div class="card-eldoria overflow-hidden">
            <div class="text-accent text-xs font-display tracking-widest uppercase px-4 pt-4 pb-2">Achats récents</div>
            @forelse($recentPayments as $payment)
                <div class="flex items-center gap-3 px-4 py-2 border-t border-accent/10">
                    <img src="{{ $payment->user->getAvatar(32) }}" alt="{{ $payment->user->name }}" class="w-6 h-6 rounded-sm">
                    <div class="text-xs text-text-secondary">
                        <span class="text-text-primary">{{ $payment->user->name }}</span>
                        @if($displaySidebarAmount)
                            · {{ shop_format_amount($payment->price) }}
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-3 text-xs text-text-secondary border-t border-accent/10">Aucun achat récent.</div>
            @endforelse
        </div>
    @endif
</div>
