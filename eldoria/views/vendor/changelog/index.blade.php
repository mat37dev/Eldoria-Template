@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="section-eyebrow">✦ {{ __('theme::theme.changelog.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ $title }}</h1>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="card-eldoria p-6 text-center text-text-secondary text-sm">
            {{ trans('changelog::messages.empty') }}
        </div>
    </div>
</div>
@endsection
