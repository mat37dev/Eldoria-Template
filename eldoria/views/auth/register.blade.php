@extends('layouts.app')

@section('title', __('theme::theme.auth.register_title'))

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-24">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.auth.register_eyebrow') }} ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">{{ __('theme::theme.auth.register_title') }}</h1>
        </div>

        <div class="card-eldoria p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.auth.username') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.auth.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.auth.password') }}</label>
                    <input type="password" name="password" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.auth.password_confirm') }}</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-4 min-h-[48px]">
                    {{ __('theme::theme.auth.register_submit') }}
                </button>
            </form>

            <p class="text-center text-text-secondary text-sm mt-6">
                {{ __('theme::theme.auth.already_registered') }}
                <a href="{{ route('login') }}" class="text-accent hover:text-accent/80 transition-colors">{{ __('theme::theme.auth.login_link') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
