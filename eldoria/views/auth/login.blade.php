@extends('layouts.app')

@section('title', __('theme::theme.auth.login_title'))

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-24">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.auth.login_eyebrow') }} ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">{{ __('theme::theme.auth.login_title') }}</h1>
        </div>

        <div class="card-eldoria p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">
                        {{ __('theme::theme.auth.username_or_email') }}
                    </label>
                    <input type="text" name="email" value="{{ old('email') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]"
                           placeholder="{{ __('theme::theme.auth.email_placeholder') }}">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.auth.password') }}</label>
                    <input type="password" name="password" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]"
                           placeholder="••••••••">
                    @error('password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-text-secondary cursor-pointer">
                        <input type="checkbox" name="remember" class="accent-[var(--color-accent)]">
                        {{ __('theme::theme.auth.remember_me') }}
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-accent/70 hover:text-accent transition-colors text-xs">
                        {{ __('theme::theme.auth.forgot_password') }}
                    </a>
                    @endif
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-4 min-h-[48px]">
                    {{ __('theme::theme.auth.login_submit') }}
                </button>
            </form>

            <p class="text-center text-text-secondary text-sm mt-6">
                {{ __('theme::theme.auth.no_account') }}
                <a href="{{ route('register') }}" class="text-accent hover:text-accent/80 transition-colors">{{ __('theme::theme.auth.register_link') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
