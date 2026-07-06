@extends('layouts.app')

@section('title', __('theme::theme.profile.title'))

@section('content')
<div class="min-h-screen px-4 py-24">
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.profile.eyebrow') }} ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">{{ __('theme::theme.profile.title') }}</h1>
        </div>

        <div class="card-eldoria p-8">
            <div class="flex items-center gap-4 mb-8 pb-8 border-b border-accent/10">
                <img src="{{ auth()->user()->getAvatar(64) }}" alt="{{ auth()->user()->name }}"
                     class="w-16 h-16 rounded-sm flex-shrink-0">
                <div>
                    <div class="font-display text-text-primary text-lg font-semibold flex items-center gap-2 flex-wrap">
                        {{ auth()->user()->name }}
                        @if(auth()->user()->role)
                            <span class="px-2 py-0.5 rounded-sm text-xs font-display uppercase tracking-wide"
                                  style="{{ auth()->user()->role->getBadgeStyle() }}">
                                {{ auth()->user()->role->name }}
                            </span>
                        @endif
                    </div>
                    <div class="text-text-secondary text-sm">{{ auth()->user()->email }}</div>
                    @if(auth()->user()->email_verified_at === null)
                        <div class="text-red-400 text-xs mt-1">{{ __('theme::theme.profile.email_unverified') }}</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.member_since') }}</span>
                    <span class="text-text-primary">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.last_login_label') }}</span>
                    <span class="text-text-primary">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y à H:i') : __('theme::theme.profile.last_login_never') }}</span>
                </div>
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.balance_label') }}</span>
                    <span class="text-accent font-display font-bold">{{ format_money(auth()->user()->money) }}</span>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-accent/10 flex flex-col sm:flex-row gap-3">
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="flex-1 text-center py-3 border border-accent/30 text-text-secondary hover:text-text-primary
                          text-sm font-display tracking-widest uppercase rounded-sm transition-colors min-h-[48px]
                          flex items-center justify-center">
                    {{ __('theme::theme.profile.change_password') }}
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-accent text-bg-primary font-display text-sm tracking-widest uppercase
                                   rounded-sm hover:bg-accent/90 transition-all min-h-[48px]">
                        {{ __('theme::theme.profile.logout') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
