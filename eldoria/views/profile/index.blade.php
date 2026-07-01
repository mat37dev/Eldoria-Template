@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="min-h-screen px-4 py-24">
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Compte ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">Mon Profil</h1>
        </div>

        <div class="card-eldoria p-8">
            <div class="flex items-center gap-4 mb-8 pb-8 border-b border-accent/10">
                <div class="w-16 h-16 rounded-full bg-accent/20 flex items-center justify-center font-display text-accent font-bold text-2xl flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-display text-text-primary text-lg font-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-text-secondary text-sm">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="space-y-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">Membre depuis</span>
                    <span class="text-text-primary">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-accent/10 flex flex-col sm:flex-row gap-3">
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="flex-1 text-center py-3 border border-accent/30 text-text-secondary hover:text-text-primary
                          text-sm font-display tracking-widest uppercase rounded-sm transition-colors min-h-[48px]
                          flex items-center justify-center">
                    Changer le mot de passe
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-accent text-bg-primary font-display text-sm tracking-widest uppercase
                                   rounded-sm hover:bg-accent/90 transition-all min-h-[48px]">
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
