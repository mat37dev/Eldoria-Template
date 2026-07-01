@extends('layouts.app')

@section('title', 'Voter')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Soutiens-nous ✦</p>
        <h1 class="section-title">Votes</h1>
        <p class="section-subtitle">Chaque vote aide le serveur à grandir — merci pour ton soutien !</p>
    </div>

    <div class="max-w-3xl mx-auto px-4 space-y-6">

        {{-- ======= CARTE DE VOTE (étapes JS) ======= --}}
        <div class="card-eldoria p-6 relative" id="vote-card">
            <div id="vote-status-message"></div>

            {{-- Étape 1 : identification (invités uniquement) --}}
            <div class="{{ auth()->check() ? 'hidden' : '' }}" data-vote-step="1">
                @if($authRequired)
                    <div class="text-center py-6">
                        <p class="text-text-secondary text-sm mb-4">Tu dois être connecté pour voter.</p>
                        <a href="{{ route('login') }}" class="btn-primary">Se connecter</a>
                    </div>
                @else
                    <form id="voteNameForm"
                          data-verify-url-template="{{ route('vote.verify-user', ['user' => '__USER__']) }}"
                          class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <input type="text" id="stepNameInput" name="name" value="{{ $name }}" required
                               placeholder="Ton pseudo Minecraft"
                               class="w-full sm:w-64 bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                        <button type="submit" class="btn-primary min-h-[48px] whitespace-nowrap">
                            Continuer
                            <span class="hidden vote-load-spinner ml-2">…</span>
                        </button>
                    </form>
                @endif
            </div>

            {{-- Étape 2 : liste des sites --}}
            <div class="{{ auth()->check() ? '' : 'hidden' }} space-y-3" data-vote-step="2">
                @forelse($sites as $site)
                    <a href="{{ $site->url }}" target="_blank" rel="noopener noreferrer"
                       data-vote-id="{{ $site->id }}"
                       data-vote-url="{{ route('vote.vote', $site) }}"
                       @auth data-vote-time="{{ $site->getNextVoteTime($user, $request)?->valueOf() }}" @endauth
                       class="card-eldoria p-4 flex items-center justify-between gap-4 hover:translate-x-1 transition-transform duration-200">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 flex items-center justify-center border border-accent/30 rounded-sm font-display font-bold text-sm text-accent">
                                ✦
                            </div>
                            <div class="font-display text-text-primary font-semibold">{{ $site->name }}</div>
                        </div>
                        <span class="vote-timer text-accent/70 text-xs font-mono whitespace-nowrap"></span>
                    </a>
                @empty
                    <div class="text-center py-8 text-text-secondary">
                        Aucun site de vote disponible pour le moment.
                    </div>
                @endforelse
            </div>

            {{-- Étape 3 (résultat, injecté dynamiquement dans #vote-status-message) --}}
            <div class="hidden" data-vote-step="3"></div>

            {{-- Étape "choix du serveur" (votes multi-serveurs) --}}
            <div class="hidden" data-vote-step="server">
                <p class="text-text-secondary text-sm text-center mb-4">Sur quel serveur veux-tu recevoir ta récompense ?</p>
                <div id="vote-server-select" class="space-y-2"></div>
            </div>
        </div>

        {{-- ======= OBJECTIF DU MOIS ======= --}}
        @if($goalEnabled)
        <div class="card-eldoria p-6" id="vote-goal" data-aos="fade-up">
            <div class="flex justify-between items-center mb-3">
                <span class="font-display text-text-primary text-sm tracking-widest uppercase">Objectif du mois</span>
                <span class="text-accent font-display font-bold text-xl">{{ $goalProgress }} / {{ $goalTarget }}</span>
            </div>
            <div class="w-full bg-bg-primary rounded-full h-2 overflow-hidden">
                <div class="h-full bg-accent rounded-full transition-all duration-1000 ease-out"
                     data-goal-bar style="width: {{ min($goalPercentage, 100) }}%"></div>
            </div>
            <p class="text-text-secondary text-xs mt-2 text-right" id="vote-goal-text">{{ $goalProgress }} / {{ $goalTarget }} votes</p>
        </div>
        @endif

        {{-- ======= TOP VOTEURS ======= --}}
        <div class="card-eldoria p-6" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6">Top Voteurs du Mois</h2>

            @if($votes->isEmpty())
                <p class="text-text-secondary text-sm">Personne n'a encore voté ce mois-ci — sois le premier !</p>
            @else
                <div class="space-y-3">
                    @foreach($votes as $vote)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="font-display text-text-secondary text-sm w-5">{{ $loop->iteration }}.</span>
                                <span class="text-text-primary text-sm">{{ $vote->user->name ?? 'Inconnu' }}</span>
                            </div>
                            <span class="text-accent font-display font-bold text-sm">{{ $vote->votes }} votes</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @auth
                @if($userVotes >= 0)
                    <p class="text-text-secondary text-xs mt-6 pt-6 border-t border-accent/10">
                        Tu as voté {{ $userVotes }} fois ce mois-ci.
                    </p>
                @endif
            @endauth
        </div>

        {{-- ======= RÉCOMPENSES ======= --}}
        @if($displayRewards && $rewards->isNotEmpty())
        <div class="card-eldoria p-6" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6">Récompenses possibles</h2>
            <div class="space-y-3">
                @foreach($rewards as $reward)
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @if($reward->image)
                                <img src="{{ $reward->imageUrl() }}" alt="{{ $reward->name }}" class="w-8 h-8 rounded-sm object-cover">
                            @endif
                            <span class="text-text-primary text-sm">{{ $reward->name }}</span>
                        </div>
                        <span class="text-accent/70 text-xs font-mono">{{ $reward->chances }} %</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@auth
<script>window.eldoriaVoteUsername = @json(auth()->user()->name);</script>
@endauth

@endsection
