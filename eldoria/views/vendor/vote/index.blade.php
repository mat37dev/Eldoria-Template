@extends('layouts.app')

@section('title', __('theme::theme.vote.title'))

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.vote.hero_eyebrow') }} ✦</p>
        <h1 class="section-title">{{ __('theme::theme.vote.title') }}</h1>
        <p class="section-subtitle">{{ __('theme::theme.vote.subtitle') }}</p>
    </div>

    <div class="max-w-3xl mx-auto px-4 space-y-6">

        {{-- ======= CARTE DE VOTE (étapes JS) ======= --}}
        <div class="card-eldoria p-6 relative" id="vote-card">
            <div id="vote-status-message"></div>

            {{-- Étape 1 : identification (invités uniquement) --}}
            <div class="{{ auth()->check() ? 'hidden' : '' }}" data-vote-step="1">
                @if($authRequired)
                    <div class="text-center py-6">
                        <p class="text-text-secondary text-sm mb-4">{{ __('theme::theme.vote.auth_required') }}</p>
                        <a href="{{ route('login') }}" class="btn-primary">{{ __('theme::theme.vote.login') }}</a>
                    </div>
                @else
                    <form id="voteNameForm"
                          data-verify-url-template="{{ route('vote.verify-user', ['user' => '__USER__']) }}"
                          class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <input type="text" id="stepNameInput" name="name" value="{{ $name }}" required
                               placeholder="{{ __('theme::theme.vote.username_placeholder') }}"
                               class="w-full sm:w-64 bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                        <button type="submit" class="btn-primary min-h-[48px] whitespace-nowrap">
                            {{ __('theme::theme.vote.continue') }}
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
                        {{ __('theme::theme.vote.no_sites') }}
                    </div>
                @endforelse
            </div>

            {{-- Étape 3 (résultat, injecté dynamiquement dans #vote-status-message) --}}
            <div class="hidden" data-vote-step="3"></div>

            {{-- Étape "choix du serveur" (votes multi-serveurs) --}}
            <div class="hidden" data-vote-step="server">
                <p class="text-text-secondary text-sm text-center mb-4">{{ __('theme::theme.vote.server_select_prompt') }}</p>
                <div id="vote-server-select" class="space-y-2"></div>
            </div>
        </div>

        {{-- ======= OBJECTIF DU MOIS ======= --}}
        @if($goalEnabled)
        <div class="card-eldoria p-6" id="vote-goal" data-aos="fade-up">
            <div class="flex justify-between items-center mb-3">
                <span class="font-display text-text-primary text-sm tracking-widest uppercase">{{ __('theme::theme.vote.goal_title') }}</span>
                <span class="text-accent font-display font-bold text-xl">{{ $goalProgress }} / {{ $goalTarget }}</span>
            </div>
            <div class="w-full bg-bg-primary rounded-full h-2 overflow-hidden">
                <div class="h-full bg-accent rounded-full transition-all duration-1000 ease-out"
                     data-goal-bar style="width: {{ min($goalPercentage, 100) }}%"></div>
            </div>
            <p class="text-text-secondary text-xs mt-2 text-right" id="vote-goal-text">{{ $goalProgress }} / {{ $goalTarget }} {{ __('theme::theme.vote.votes_suffix') }}</p>
        </div>
        @endif

        {{-- ======= TOP VOTEURS ======= --}}
        <div class="card-eldoria p-6" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6">{{ __('theme::theme.vote.top_voters_title') }}</h2>

            @if($votes->isEmpty())
                <p class="text-text-secondary text-sm">{{ __('theme::theme.vote.no_votes_yet') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($votes as $vote)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="font-display text-text-secondary text-sm w-5">{{ $loop->iteration }}.</span>
                                <span class="text-text-primary text-sm">{{ $vote->user->name ?? __('theme::theme.vote.unknown_user') }}</span>
                            </div>
                            <span class="text-accent font-display font-bold text-sm">{{ $vote->votes }} {{ __('theme::theme.vote.votes_suffix') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @auth
                @if($userVotes >= 0)
                    <p class="text-text-secondary text-xs mt-6 pt-6 border-t border-accent/10">
                        {{ __('theme::theme.vote.user_votes_count', ['count' => $userVotes]) }}
                    </p>
                @endif
            @endauth
        </div>

        {{-- ======= RÉCOMPENSES ======= --}}
        @if($displayRewards && $rewards->isNotEmpty())
        <div class="card-eldoria p-6" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6">{{ __('theme::theme.vote.rewards_title') }}</h2>
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
