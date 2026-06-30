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

        {{-- Barre de progression globale du mois --}}
        @if(isset($monthlyVotes))
        <div class="card-eldoria p-6 mb-8" data-aos="fade-up">
            <div class="flex justify-between items-center mb-3">
                <span class="font-display text-text-primary text-sm tracking-widest uppercase">Votes ce mois</span>
                <span class="text-accent font-display font-bold text-xl">{{ $monthlyVotes }}</span>
            </div>
            <div class="w-full bg-bg-primary rounded-full h-2 overflow-hidden">
                <div class="h-full bg-accent rounded-full transition-all duration-1000 ease-out"
                     id="vote-progress-bar"
                     style="width: 0%"
                     data-target="{{ min(100, ($monthlyVotes / max(1, $monthlyGoal ?? 1000)) * 100) }}">
                </div>
            </div>
            @if(isset($monthlyGoal))
            <p class="text-text-secondary text-xs mt-2 text-right">Objectif : {{ $monthlyGoal }} votes</p>
            @endif
        </div>
        @endif

        {{-- Liste des sites de vote --}}
        @foreach($sites ?? [] as $site)
        @php $hasVoted = isset($votes) && in_array($site->id, $votes); @endphp
        <div class="card-eldoria p-5 flex items-center justify-between gap-4"
             data-aos="fade-right" data-aos-delay="{{ $loop->index * 75 }}">

            <div class="flex items-center gap-4">
                {{-- Numéro de quête --}}
                <div class="w-10 h-10 flex items-center justify-center border border-accent/30 rounded-sm
                            font-display font-bold text-sm {{ $hasVoted ? 'text-accent bg-accent/10 border-accent' : 'text-text-secondary' }}">
                    {{ $hasVoted ? '✓' : $loop->iteration }}
                </div>

                <div>
                    <div class="font-display text-text-primary font-semibold">{{ $site->name }}</div>
                    <div class="text-text-secondary text-xs mt-0.5">
                        @if($hasVoted)
                            <span class="text-accent">Vote effectué aujourd'hui</span>
                        @else
                            Disponible — vote pour une récompense
                        @endif
                    </div>
                </div>
            </div>

            @if($hasVoted)
                <span class="text-accent/60 text-xs font-display uppercase tracking-widest whitespace-nowrap">
                    ✓ Voté
                </span>
            @else
                <a href="{{ route('vote.vote', $site) }}" target="_blank" rel="noopener"
                   class="btn-primary text-xs py-2 px-4 whitespace-nowrap min-h-[44px]">
                    Voter
                </a>
            @endif
        </div>
        @endforeach

        {{-- Top voteurs --}}
        @if(isset($topVoters) && $topVoters->count() > 0)
        <div class="card-eldoria p-6 mt-8" data-aos="fade-up">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6">Top Voteurs du Mois</h2>
            <div class="space-y-3">
                @foreach($topVoters->take(5) as $voter)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-display text-text-secondary text-sm w-5">{{ $loop->iteration }}.</span>
                        <span class="text-text-primary text-sm">{{ $voter->user->name ?? 'Inconnu' }}</span>
                    </div>
                    <span class="text-accent font-display font-bold text-sm">{{ $voter->votes }} votes</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('vote-progress-bar')
    if (bar) {
        setTimeout(() => {
            bar.style.width = bar.dataset.target + '%'
        }, 300)
    }
})
</script>
@endpush

@endsection
