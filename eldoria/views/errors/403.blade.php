@extends('layouts.app')
@section('title', __('theme::theme.errors.403_title'))
@section('content')
<div class="min-h-screen flex items-center justify-center text-center px-4">
    <div>
        <div class="font-display text-accent text-[8rem] font-black leading-none mb-4 opacity-20">403</div>
        <h1 class="font-display text-3xl text-text-primary mb-4">{{ __('theme::theme.errors.403_title') }}</h1>
        <p class="text-text-secondary mb-8">{{ __('theme::theme.errors.403_text') }}</p>
        <a href="{{ route('home') }}" class="btn-primary">{{ __('theme::theme.errors.back_home') }}</a>
    </div>
</div>
@endsection
